<?php

/**
 * 数据迁移表单模型
 * 负责从派单系统获取数据、验证数据、同步数据到CRM系统
 * 
 * 🔄 重构说明：
 * - 辅助工具方法已迁移至 DataMigrationHelper 类
 * - 使用 DataMigrationHelper::方法名() 调用辅助方法
 * 
 * @see DataMigrationHelper 辅助工具类
 */
class DataMigrationForm extends CFormModel
{
    public $id; // 迁移记录ID
    public $migration_type; // 迁移类型: client/clientStore/cont/vir
    public $api_url; // 派单系统API地址
    public $api_config; // API配置信息（JSON格式）
    public $filter_params; // 筛选参数（JSON格式）
    public $username; // 操作用户
    public $req_dt; // 请求时间
    
    // 缓存：员工编号 => 员工ID（避免重复查询）
    protected static $employeeCache = array();
    
    // 缓存：业务大类名称 => ID（避免重复查询）
    protected static $yewudaleiCache = array();
    
    // 缓存：城市名称 => 城市代码（避免重复查询）
    protected static $cityCodeCache = array();
    
    // 缓存：主体公司名称 => ID（避免重复查询）
    protected static $lbsMainCache = array();
    
    // 缓存：服务项目名称 => 项目信息（避免重复查询）
    protected static $serviceTypeCache = array();
    
    // 缓存：行业类别名称 => 类别信息（避免重复查询）
    protected static $custClassCache = array();
    
    /**
     * 验证规则
     */
    public function rules()
    {
        return array(
            array('migration_type, api_url', 'required'),
            array('api_config, filter_params', 'safe'),
        );
    }
    
    /**
     * 字段标签
     */
    public function attributeLabels()
    {
        return array(
            'migration_type' => '迁移类型',
            'api_url' => 'API地址',
            'api_config' => 'API配置',
            'filter_params' => '筛选参数',
        );
    }
    
    /**
     * 调用派单系统API获取数据
     * @return array 返回结果包含status、message、log_id、total_count、headers
     */
    public function fetchPaidanData()
    {
        try {
            // 解析配置参数
            $apiConfig = is_string($this->api_config) ? json_decode($this->api_config, true) : $this->api_config;
            $filterParams = is_string($this->filter_params) ? json_decode($this->filter_params, true) : $this->filter_params;
            
            if (empty($apiConfig)) {
                $apiConfig = array();
            }
            if (empty($filterParams)) {
                $filterParams = array();
            }
            
            // 创建API客户端
            $client = new PaidanApiClient();
            $client->apiBaseUrl = $this->api_url;
            
            // 设置API Token
            if (!empty($apiConfig) && isset($apiConfig['token'])) {
                $client->apiToken = $apiConfig['token'];
            }
            
            // 根据迁移类型调用对应的API
            $result = null;
            $headers = array();
            
            switch ($this->migration_type) {
                case 'client':
                    $result = $client->fetchCustomers($filterParams);
                    break;
                case 'clientStore':
                    $result = $client->fetchStores($filterParams);
                    break;
                case 'cont':
                    $result = $client->fetchContracts($filterParams);
                    break;
                case 'vir':
                    $result = $client->fetchVirtualContracts($filterParams);
                    break;
                default:
                    throw new Exception('不支持的迁移类型：' . $this->migration_type);
            }
            
            // 检查API返回结果
            if (empty($result) || !isset($result['status'])) {
                throw new Exception('API返回数据格式错误');
            }
            
            if ($result['status'] != 1) {
                throw new Exception('API返回错误：' . (isset($result['message']) ? $result['message'] : '未知错误'));
            }
            
            // 提取数据
            $data = isset($result['data']) ? $result['data'] : array();
            $headers = isset($data['headers']) ? $data['headers'] : array();
            $rows = isset($data['rows']) ? $data['rows'] : array();
            
            if (empty($rows)) {
                return array(
                    'status' => 0,
                    'message' => '未获取到任何数据',
                );
            }
            
            // 保存迁移日志
            $logId = $this->saveMigrationLog(array(
                'total_count' => count($rows),
                'status' => 'P', // P-处理中
            ));
            
            // 保存迁移详情
            $this->saveMigrationDetails($logId, $rows);
            
            return array(
                'status' => 1,
                'message' => '获取数据成功',
                'log_id' => $logId,
                'total_count' => count($rows),
                'headers' => $headers,
            );
            
        } catch (Exception $e) {
            $errorMsg = '获取派单系统数据失败：' . $e->getMessage();
            Yii::log($errorMsg . "\n" . $e->getTraceAsString(), 'error', 'DataMigration');
            throw new Exception($errorMsg);
        }
    }
    
    /**
     * 保存迁移日志
     */
    protected function saveMigrationLog($params)
    {
        $connection = Yii::app()->db;
        
        // 确保 report_id 从 5001 开始（避免与之前的导入表ID重复）
        $maxId = $connection->createCommand()
            ->select('MAX(id) as max_id')
            ->from('sal_data_migration_log')
            ->queryScalar();
        
        if (empty($maxId) || $maxId < 5000) {
            // 如果表为空或ID小于5000，设置 AUTO_INCREMENT 从 5000 开始
            try {
                $connection->createCommand("ALTER TABLE sal_data_migration_log AUTO_INCREMENT = 5001")->execute();
            } catch (Exception $e) {
                // 忽略错误（可能已经设置过）
                Yii::log('设置 AUTO_INCREMENT 失败（可能已设置）: ' . $e->getMessage(), 'warning', 'DataMigration');
            }
        }
        
        // 兼容后台命令行环境（没有web session）
        $currentUser = 'system'; // 默认系统用户
        if (Yii::app() instanceof CWebApplication && !Yii::app()->user->isGuest) {
            $currentUser = Yii::app()->user->id;
        }
        
        $data = array(
            'migration_type' => $this->migration_type,
            'api_url' => $this->api_url,
            'api_config' => is_string($this->api_config) ? $this->api_config : json_encode($this->api_config, JSON_UNESCAPED_UNICODE),
            'filter_params' => is_string($this->filter_params) ? $this->filter_params : json_encode($this->filter_params, JSON_UNESCAPED_UNICODE),
            'total_count' => isset($params['total_count']) ? $params['total_count'] : 0,
            'status' => 'P', // P-处理中
            'start_time' => date('Y-m-d H:i:s'),
            'create_user' => $currentUser,
            'lcu' => $currentUser,
            'lcd' => date('Y-m-d H:i:s'),
        );
        
        $connection->createCommand()->insert('sal_data_migration_log', $data);
        return $connection->getLastInsertID();
    }
    
    /**
     * 保存迁移详情（带去重检查）
     */
    protected function saveMigrationDetails($logId, $rows)
    {
        $connection = Yii::app()->db;
        $insertCount = 0;
        $skipCount = 0;
        
        // 获取当前用户
        $currentUser = DataMigrationHelper::getCurrentUserId($this->username);
        
        foreach ($rows as $index => $row) {
            // 提取派单系统ID（用于去重）
            $uId = $this->extractUId($row);
            
            // 检查是否已存在相同派单系统ID的待处理记录
            if (!empty($uId) && $this->detailRecordExists($logId, $uId)) {
                $skipCount++;
                continue; // 跳过重复记录
            }
            
            $data = array(
                'log_id' => $logId,
                'row_index' => $index + 1,
                'u_id' => $uId, // 存储派单系统ID，便于查询和去重
                'source_data' => json_encode($row, JSON_UNESCAPED_UNICODE), // 中文不转义，便于查询
                'status' => 'P', // P-待处理，S-成功，E-失败，K-跳过
                'error_message' => null,
                'lcu' => $currentUser,
                'lcd' => date('Y-m-d H:i:s'),
            );
            
            $connection->createCommand()->insert('sal_data_migration_detail', $data);
            $insertCount++;
        }
        
        // 记录日志
        if ($skipCount > 0) {
            Yii::log("保存Detail记录：新增{$insertCount}条，跳过重复{$skipCount}条", 'info', 'DataMigration');
        }
    }
    
    /**
     * 提取派单系统ID
     */
    private function extractUId($row)
    {
        // 根据不同类型提取对应的ID字段
        if (isset($row['派单系统id'])) {
            return $row['派单系统id'];
        } elseif (isset($row['派单系统客户id'])) {
            return $row['派单系统客户id'];
        } elseif (isset($row['派单系统门店id'])) {
            return $row['派单系统门店id'];
        } elseif (isset($row['派单系统合约id'])) {
            return $row['派单系统合约id'];
        }
        return null;
    }
    
    /**
     * 检查Detail表中是否已存在相同派单系统ID的记录（使用索引字段查询）
     */
    private function detailRecordExists($logId, $uId)
    {
        $connection = Yii::app()->db;
        
        // 直接查询u_id字段（有索引，查询快）
        $count = $connection->createCommand()
            ->select('COUNT(*)')
            ->from('sal_data_migration_detail')
            ->where('log_id=:log_id AND u_id=:u_id AND status=:status', array(
                ':log_id' => $logId,
                ':u_id' => $uId,
                ':status' => 'P', // 只检查待处理的记录
            ))
            ->queryScalar();
        
        return $count > 0;
    }
    
    /**
     * 验证数据
     * @return array 返回验证统计结果
     * 
     * 注意：API导入不使用Excel的ImportForm验证逻辑
     * 而是在导入时直接进行容错处理
     */
    public function validateData()
    {
        $connection = Yii::app()->db;
        
        // 获取待验证的数据
        $details = $connection->createCommand()
            ->select('*')
            ->from('sal_data_migration_detail')
            ->where('log_id=:log_id AND status=:status', array(
                ':log_id' => $this->id,
                ':status' => 'P' // P-待处理
            ))
            ->queryAll();
        
        if (empty($details)) {
            return array(
                'total' => 0,
                'success' => 0,
                'error' => 0,
            );
        }
        
        $successCount = 0;
        $errorCount = 0;
        
        // 逐行进行基础验证（不使用ImportForm）
        foreach ($details as $detail) {
            $rowData = json_decode($detail['source_data'], true);
            
            // 基础数据验证
            $result = $this->validateBasicData($rowData);
            
            // 更新验证结果
            $updateData = array(
                'status' => $result['status'] === 'S' ? 'S' : 'E', // S-成功，E-失败
                'error_message' => $result['message'],
                'lcu' => DataMigrationHelper::getCurrentUserId($this->username),
                'lcd' => date('Y-m-d H:i:s'),
            );
            
            $connection->createCommand()->update(
                'sal_data_migration_detail',
                $updateData,
                'id=:id',
                array(':id' => $detail['id'])
            );
            
            if ($result['status'] === 'S') {
                $successCount++;
            } else {
                $errorCount++;
            }
        }
        
        // 更新日志统计
        $connection->createCommand()->update(
            'sal_data_migration_log',
            array(
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'lcu' => DataMigrationHelper::getCurrentUserId($this->username),
                'lcd' => date('Y-m-d H:i:s'),
            ),
            'id=:id',
            array(':id' => $this->id)
        );
        
        return array(
            'total_count' => count($details),
            'valid_count' => $successCount,
            'error_count' => $errorCount,
        );
    }
    
    /**
     * 基础数据验证（不依赖ImportForm）
     */
    protected function validateBasicData($data)
    {
        try {
            // 检查必需字段
            if (empty($data)) {
                return array('status' => 'E', 'message' => '数据为空');
            }
            
            // 根据类型进行基础验证
            switch ($this->migration_type) {
                case 'client':
                    if (empty($data['客户名称'])) {
                        return array('status' => 'E', 'message' => '客户名称不能为空');
                    }
                    break;
                case 'clientStore':
                    if (empty($data['门店名称'])) {
                        return array('status' => 'E', 'message' => '门店名称不能为空');
                    }
                    if (empty($data['客户名称'])) {
                        return array('status' => 'E', 'message' => '客户名称不能为空');
                    }
                    break;
                case 'cont':
                    if (empty($data['主合同编号'])) {
                        return array('status' => 'E', 'message' => '主合同编号不能为空');
                    }
                    break;
                case 'vir':
                    if (empty($data['虚拟合同编号'])) {
                        return array('status' => 'E', 'message' => '虚拟合同编号不能为空');
                    }
                    break;
            }
            
            return array('status' => 'S', 'message' => null);
        } catch (Exception $e) {
            return array('status' => 'E', 'message' => '验证出错：' . $e->getMessage());
        }
    }
    
    /**
     * 预览数据（分页）
     */
    public function previewData($logId, $page = 1, $pageSize = 50, $search = '', $status = '')
    {
        $connection = Yii::app()->db;
        
        // 构建查询条件
        $where = 'log_id=:log_id';
        $params = array(':log_id' => $logId);
        
        if (!empty($status)) {
            $where .= ' AND status=:status';
            $params[':status'] = $status;
        }
        
        if (!empty($search)) {
            $where .= ' AND source_data LIKE :search';
            $params[':search'] = '%' . $search . '%';
        }
        
        // 获取总数
        $totalCount = $connection->createCommand()
            ->select('COUNT(*)')
            ->from('sal_data_migration_detail')
            ->where($where, $params)
            ->queryScalar();
        
        // 获取分页数据
        $offset = ($page - 1) * $pageSize;
        $details = $connection->createCommand()
            ->select('*')
            ->from('sal_data_migration_detail')
            ->where($where, $params)
            ->order('row_index ASC')
            ->limit($pageSize)
            ->offset($offset)
            ->queryAll();
        
        // 获取表头（从第一条数据中提取）
        $headers = array();
        if (!empty($details)) {
            $firstRow = json_decode($details[0]['source_data'], true);
            if (!empty($firstRow)) {
                $headers = array_keys($firstRow);
            }
        }
        
        // 解析行数据
        $rows = array();
        foreach ($details as $detail) {
            $rows[] = array(
                'id' => $detail['id'],
                'row_index' => $detail['row_index'],
                'data' => json_decode($detail['source_data'], true),
                'status' => $detail['status'],
                'error_message' => isset($detail['error_message']) ? $detail['error_message'] : '',
            );
        }
        
        // 计算总页数，至少为1页（即使没有数据也显示空表格）
        $totalPages = $totalCount > 0 ? ceil($totalCount / $pageSize) : 1;
        
        return array(
            'headers' => $headers,
            'rows' => $rows,
            'total_count' => intval($totalCount),
            'page' => intval($page),
            'page_size' => intval($pageSize),
            'total_pages' => intval($totalPages),
        );
    }
    
    /**
     * 同步数据到CRM系统
     * @param string $importMode 导入模式: 'all'=全部, 'selected'=选中的, 'failed'=失败的
     * @param array $selectedRows 选中的行ID数组
     * @param int $batchSize 批处理大小
     * @param bool $retryFailed 是否重试失败记录（默认false）
     */
    public function syncData($importMode = 'all', $selectedRows = array(), $batchSize = 100, $retryFailed = false)
    {
        $connection = Yii::app()->db;
        
        // 获取待导入的数据
        // 默认导入: P-待处理, S-验证成功
        // 重试模式: 包括 E-失败的记录
        if ($importMode === 'failed' || $retryFailed) {
            // 重试失败记录模式：只导入失败的
            $where = 'log_id=:log_id AND status=:status_error';
            $params = array(
                ':log_id' => $this->id,
                ':status_error' => 'E', // E-失败
            );
        } else {
            // 正常导入模式：导入待处理和验证成功的
        $where = 'log_id=:log_id AND status IN (:status1, :status2)';
        $params = array(
            ':log_id' => $this->id,
            ':status1' => 'P', // P-待处理
            ':status2' => 'S'  // S-验证成功（如果有验证过的也包括）
        );
        }
        
        if ($importMode === 'selected' && !empty($selectedRows)) {
            $where .= ' AND id IN (' . implode(',', array_map('intval', $selectedRows)) . ')';
        }
        
        $details = $connection->createCommand()
            ->select('*')
            ->from('sal_data_migration_detail')
            ->where($where, $params)
            ->order('row_index ASC')
            ->queryAll();
        
        if (empty($details)) {
            return array(
                'status' => 0,
                'message' => '没有待导入的数据',
            );
        }
        
        // 初始化导入配置（不使用ImportForm）
        // 兼容后台命令行环境（没有web session）
        if (Yii::app() instanceof CWebApplication && !Yii::app()->user->isGuest) {
            $this->username = Yii::app()->user->id;
        } else {
            // 后台命令行环境，使用系统用户或已设置的username
            $this->username = empty($this->username) ? 'system' : $this->username;
        }
        $this->req_dt = date("Y-m-d H:i:s");
        
        $successCount = 0;
        $errorCount = 0;
        
        // 批量导入（跳过验证，直接导入并容错处理）
        foreach ($details as $detail) {
            $rowData = json_decode($detail['source_data'], true);
            
            try {
                // 检查JSON解析是否成功
                if (empty($rowData)) {
                    throw new Exception('数据格式错误：无法解析JSON');
                }
                
                // 检查是否已存在（根据u_id）
                if ($this->recordExists($rowData)) {
                    // 更新现有记录
                    $this->updateExistingData($rowData);
                    $status = 'S'; // S-成功
                    $message = '更新成功';
                } else {
                    // 新增记录（使用DataMigrationForm自己的导入逻辑）
                    $this->insertNewData($rowData);
                    $status = 'S'; // S-成功
                    $message = '导入成功';
                }
                
                $successCount++;
                
            } catch (Exception $e) {
                $status = 'E'; // E-失败
                $message = '导入失败：' . $e->getMessage();
                $errorCount++;
                
                // 记录详细错误日志
                Yii::log(
                    '数据导入失败 [行' . $detail['row_index'] . ']: ' . $e->getMessage() . 
                    "\n数据: " . json_encode($rowData, JSON_UNESCAPED_UNICODE) .
                    "\n错误堆栈: " . $e->getTraceAsString(),
                    'error',
                    'DataMigration'
                );
            }
            
            // 更新详情状态
            $connection->createCommand()->update(
                'sal_data_migration_detail',
                array(
                    'status' => $status,
                    'error_message' => $message,
                    'import_time' => date('Y-m-d H:i:s'),
                    'lcu' => DataMigrationHelper::getCurrentUserId($this->username),
                    'lcd' => date('Y-m-d H:i:s'),
                ),
                'id=:id',
                array(':id' => $detail['id'])
            );
        }
        
        // 更新日志
        $connection->createCommand()->update(
            'sal_data_migration_log',
            array(
                'status' => 'S', // S-成功
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'end_time' => date('Y-m-d H:i:s'),
                'lcu' => DataMigrationHelper::getCurrentUserId($this->username),
                'lcd' => date('Y-m-d H:i:s'),
            ),
            'id=:id',
            array(':id' => $this->id)
        );
        
        return array(
            'status' => 1,
            'message' => '导入完成',
            'success_count' => $successCount,
            'error_count' => $errorCount,
        );
    }
    
    /**
     * 重新执行失败的记录
     * @param array $detailIds 明细记录ID数组（可选，为空则重试所有失败记录）
     * @return array 返回结果
     */
    public function retryFailedRecords($detailIds = array())
    {
        // 将失败记录的状态重置为待处理，然后调用 syncData 重新导入
        $connection = Yii::app()->db;
        
        $where = 'log_id=:log_id AND status=:status';
        $params = array(
            ':log_id' => $this->id,
            ':status' => 'E' // E-失败
        );
        
        if (!empty($detailIds)) {
            $where .= ' AND id IN (' . implode(',', array_map('intval', $detailIds)) . ')';
        }
        
        // 获取失败记录数量
        $failedCount = $connection->createCommand()
            ->select('COUNT(*)')
            ->from('sal_data_migration_detail')
            ->where($where, $params)
            ->queryScalar();
        
        if ($failedCount == 0) {
            return array(
                'status' => 0,
                'message' => '没有找到失败的记录',
            );
        }
        
        // 使用 syncData 的 'failed' 模式重新导入失败记录
        $importMode = !empty($detailIds) ? 'selected' : 'failed';
        $result = $this->syncData($importMode, $detailIds, 100, true);
        
        return array(
            'status' => 1,
            'message' => '重新执行完成，共处理 ' . $failedCount . ' 条失败记录',
            'failed_count' => $failedCount,
            'success_count' => isset($result['success_count']) ? $result['success_count'] : 0,
            'error_count' => isset($result['error_count']) ? $result['error_count'] : 0,
        );
    }
    
    /**
     * 更新明细数据（用于用户编辑失败记录后保存）
     * @param int $detailId 明细记录ID
     * @param array $newData 新数据
     * @return array 返回结果
     */
    public function updateDetailData($detailId, $newData)
    {
        $connection = Yii::app()->db;
        
        // 检查记录是否存在
        $detail = $connection->createCommand()
            ->select('*')
            ->from('sal_data_migration_detail')
            ->where('id=:id AND log_id=:log_id', array(
                ':id' => $detailId,
                ':log_id' => $this->id
            ))
            ->queryRow();
        
        if (!$detail) {
            return array(
                'status' => 0,
                'message' => '记录不存在',
            );
        }
        
        // 更新 source_data 字段
        $updateData = array(
            'source_data' => json_encode($newData, JSON_UNESCAPED_UNICODE),
            'status' => 'P', // 重置为待处理状态，允许重新导入
            'error_message' => null, // 清空错误信息
            'lcu' => DataMigrationHelper::getCurrentUserId($this->username),
            'lcd' => date('Y-m-d H:i:s'),
        );
        
        $connection->createCommand()->update(
            'sal_data_migration_detail',
            $updateData,
            'id=:id',
            array(':id' => $detailId)
        );
        
        return array(
            'status' => 1,
            'message' => '数据已更新，状态已重置为待处理',
        );
    }
    
    /**
     * 批量重置失败记录状态为待处理
     * @param array $detailIds 明细记录ID数组（可选）
     * @return array 返回结果
     */
    public function resetFailedRecords($detailIds = array())
    {
        $connection = Yii::app()->db;
        
        $where = 'log_id=:log_id AND status=:status';
        $params = array(
            ':log_id' => $this->id,
            ':status' => 'E' // E-失败
        );
        
        if (!empty($detailIds)) {
            $where .= ' AND id IN (' . implode(',', array_map('intval', $detailIds)) . ')';
        }
        
        // 更新状态
        $affectedRows = $connection->createCommand()->update(
            'sal_data_migration_detail',
            array(
                'status' => 'P', // 重置为待处理
                'error_message' => null, // 清空错误信息
                'lcu' => DataMigrationHelper::getCurrentUserId($this->username),
                'lcd' => date('Y-m-d H:i:s'),
            ),
            $where,
            $params
        );
        
        return array(
            'status' => 1,
            'message' => '已重置 ' . $affectedRows . ' 条失败记录的状态',
            'affected_rows' => $affectedRows,
        );
    }
    
    /**
     * 检查记录是否已存在
     * 对于客户类型，需要同时检查u_id和业务大类（同一客户在不同业务大类下可能存在多次）
     */
    protected function recordExists($data)
    {
        if (empty($data['派单系统id']) && empty($data['u_id'])) {
            return false;
        }
        
        $uId = !empty($data['派单系统id']) ? $data['派单系统id'] : $data['u_id'];
        $connection = Yii::app()->db;
        
        switch ($this->migration_type) {
            case 'client':
                // 客户需要同时检查 u_id 和 业务大类
                // 同一个客户在不同业务大类下应该是不同的记录
                $yewudalei = isset($data['业务大类']) ? $data['业务大类'] : (isset($data['yewudalei']) ? $data['yewudalei'] : '');
                
                if (!empty($yewudalei)) {
                    // 如果有业务大类，同时检查 u_id 和 业务大类
                    $exists = $connection->createCommand()
                        ->select('id')
                        ->from('sal_clue')
                        ->where('u_id=:u_id AND yewudalei=:yewudalei', array(
                            ':u_id' => $uId,
                            ':yewudalei' => $yewudalei
                        ))
                        ->queryScalar();
                } else {
                    // 如果没有业务大类，只检查 u_id
                $exists = $connection->createCommand()
                    ->select('id')
                    ->from('sal_clue')
                    ->where('u_id=:u_id', array(':u_id' => $uId))
                    ->queryScalar();
                }
                return !empty($exists);
                
            case 'clientStore':
                $exists = $connection->createCommand()
                    ->select('id')
                    ->from('sal_clue_store')
                    ->where('u_id=:u_id', array(':u_id' => $uId))
                    ->queryScalar();
                return !empty($exists);
                
            case 'cont':
                $exists = $connection->createCommand()
                    ->select('id')
                    ->from('sal_contract')
                    ->where('u_id=:u_id', array(':u_id' => $uId))
                    ->queryScalar();
                return !empty($exists);
                
            case 'vir':
                $exists = $connection->createCommand()
                    ->select('id')
                    ->from('sal_contract_virtual')
                    ->where('u_id=:u_id', array(':u_id' => $uId))
                    ->queryScalar();
                return !empty($exists);
                
            default:
                return false;
        }
    }
    
    /**
     * 自动提取税号
     * 从开票备注、开票地址等字段中智能识别并提取税号
     * 
     * 中国统一社会信用代码（税号）格式：
     * - 18位字符（数字或大写字母）
     * - 常见格式：91或92开头
     * - 示例：91110000MA001234XX
     * 
     * @param array $data 门店数据
     * @return array 处理后的数据
     */
    protected function autoExtractTaxId($data)
    {
        // 如果已经有税号且不为空，跳过提取
        if (!empty($data['tax_id']) && trim($data['tax_id']) !== '') {
            return $data;
        }
        
        // 定义需要检查的字段（按优先级排序）
        $fieldsToCheck = array(
            'invoice_rmk',      // 开票备注
            'invoice_address',  // 开票地址
            'store_remark',     // 门店备注
        );
        
        // 统一社会信用代码正则表达式
        // 格式：18位，由数字和大写字母组成，常见以91或92开头
        $patterns = array(
            '/[9][12][0-9A-Z]{16}/',           // 标准格式：91或92开头的18位
            '/\b[0-9A-Z]{18}\b/',              // 通用格式：任意18位字母数字组合
            '/税号[：:]\s*([0-9A-Z]{15,18})/', // 带"税号："前缀
            '/纳税人识别号[：:]\s*([0-9A-Z]{15,18})/', // 带"纳税人识别号："前缀
            '/统一社会信用代码[：:]\s*([0-9A-Z]{15,18})/', // 带"统一社会信用代码："前缀
        );
        
        $extractedTaxId = null;
        $sourceField = null;
        $matchedText = null;
        
        // 遍历字段查找税号
        foreach ($fieldsToCheck as $field) {
            if (empty($data[$field])) {
                continue;
            }
            
            $text = trim($data[$field]);
            if (empty($text)) {
                continue;
            }
            
            // 尝试用各种模式匹配
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $text, $matches)) {
                    // 获取匹配到的税号（可能在捕获组1中，也可能在匹配组0中）
                    $taxId = isset($matches[1]) ? $matches[1] : $matches[0];
                    $taxId = trim($taxId);
                    
                    // 验证税号格式（15-18位字母数字组合）
                    if (preg_match('/^[0-9A-Z]{15,18}$/', $taxId)) {
                        $extractedTaxId = $taxId;
                        $sourceField = $field;
                        $matchedText = $matches[0];
                        break 2; // 找到后退出所有循环
                    }
                }
            }
        }
        
        // 如果找到税号，填充到 tax_id 字段
        if ($extractedTaxId) {
            $data['tax_id'] = $extractedTaxId;
            
            // 从源字段中移除税号文本（可选，避免重复）
            if ($sourceField && isset($data[$sourceField])) {
                $originalText = $data[$sourceField];
                // 移除匹配到的税号文本及其前后的常见前缀/后缀
                $cleanedText = preg_replace(
                    array(
                        '/税号[：:]\s*' . preg_quote($matchedText, '/') . '/u',
                        '/纳税人识别号[：:]\s*' . preg_quote($matchedText, '/') . '/u',
                        '/统一社会信用代码[：:]\s*' . preg_quote($matchedText, '/') . '/u',
                        '/' . preg_quote($matchedText, '/') . '/u',
                    ),
                    '',
                    $originalText
                );
                // 清理多余的空格、逗号、分号
                $cleanedText = preg_replace('/[,;，；]\s*[,;，；]+/', ',', $cleanedText);
                $cleanedText = preg_replace('/^\s*[,;，；]\s*|\s*[,;，；]\s*$/', '', $cleanedText);
                $cleanedText = trim($cleanedText);
                
                $data[$sourceField] = $cleanedText;
            }
            
            // 记录日志
            Yii::log(
                '自动提取税号成功：' . $extractedTaxId . 
                '（来源：' . $sourceField . '）', 
                'info', 
                'DataMigration'
            );
        }
        
        return $data;
    }
    
    /**
     * 新增数据
     */
    /**
     * 新增数据（参考 ImportForm::saveOneData 逻辑重新实现）
     */
    protected function insertNewData($data)
    {
        // 数据预处理和转换
        $processedData = $this->preprocessData($data);
        
        // 根据类型调用对应的插入逻辑（参考 ImportForm 的实现，但完全独立）
        switch ($this->migration_type) {
            case 'client':
                $this->insertClientData($processedData);
                break;
            case 'clientStore':
                $this->insertStoreData($processedData);
                break;
            case 'cont':
                $this->insertContractData($processedData);
                break;
            case 'vir':
                $this->insertVirtualContractData($processedData);
                break;
            default:
                throw new Exception('不支持的导入类型：' . $this->migration_type);
        }
    }
    
    /**
     * 数据预处理：中文字段名 → 英文字段名 + 数据转换
     * 参考 ImportForm 的验证逻辑，但针对派单系统数据进行优化
     */
    protected function preprocessData($data)
    {
        $connection = Yii::app()->db;
        
        switch ($this->migration_type) {
            case 'client':
                return $this->preprocessClientData($data, $connection);
            case 'clientStore':
                return $this->preprocessStoreData($data, $connection);
            case 'cont':
                return $this->preprocessContractData($data, $connection);
            case 'vir':
                return $this->preprocessVirtualContractData($data, $connection);
            default:
                return $data;
        }
    }
    
    /**
     * 客户数据预处理（中文字段名 → 英文字段名 + 数据转换）
     * 参考 ImportClientForm 的 eveList 字段定义
     */
    protected function preprocessClientData($data, $connection)
    {
        $processed = array();
        
        // 1. 基本字段映射（直接对应）
        $fieldMap = array(
            '客户编号' => 'clue_code',
            '客户名称' => 'cust_name',
            '客户状态' => 'clue_status',  // 新增：客户状态
            '客户简称' => 'full_name',
            '客户录入时间' => 'entry_date',
            '客户类别' => 'clue_type',
            '服务类型' => 'service_type',
            '业务大类' => 'yewudalei',
            '是否集团客户' => 'group_bool',
            '重点客户' => 'cust_vip',
            '行业类别' => 'cust_class',
            '城市' => 'city',
            '区域' => 'district',
            '街道' => 'street',
            '详细地址' => 'address',
            '经度' => 'longitude',
            '纬度' => 'latitude',
            '联系人编号' => 'person_code',
            '联系人名称' => 'cust_person',
            '联系人电话' => 'cust_tel',
            '联系人邮箱' => 'cust_email',
            '联系人职务' => 'cust_person_role',
            '联系人地址' => 'cust_address',
            '面积' => 'area',
            '客户备注' => 'clue_remark',
            '派单系统客户id' => 'u_id',
            '派单系统客户关联城市id' => 'u_area_id',
            '派单系统客户关联主要负责人id' => 'u_staff_id',
            '派单系统客户关联联系人id' => 'u_person_id',
            '派单系统客户关联联系人分组id' => 'u_group_id',
        );
        
        foreach ($fieldMap as $chineseKey => $englishKey) {
            if (isset($data[$chineseKey])) {
                $processed[$englishKey] = $data[$chineseKey];
            }
        }
        
        // 2. 客户类别转换（参考 ImportForm::valClueType）
        if (isset($processed['clue_type'])) {
            $clueTypeMap = array('地推' => 1, 'KA' => 2);
            if (isset($clueTypeMap[$processed['clue_type']])) {
                $processed['clue_type'] = $clueTypeMap[$processed['clue_type']];
            } elseif (!is_numeric($processed['clue_type'])) {
                // 有客户编号（project_code）= KA客户，无客户编号 = 地推客户
                $processed['clue_type'] = !empty($processed['clue_code']) ? 2 : 1;
            }
        } else {
            // 如果没有提供clue_type，根据客户编号智能判断
            // 有客户编号（project_code）= KA客户，无客户编号 = 地推客户
            $processed['clue_type'] = !empty($processed['clue_code']) ? 2 : 1;
        }
        
        // 2.5 客户状态转换（派单系统状态 → CRM状态）
        // 派单系统 project_status：0=未开始(映射为服务中)，1=进行中，2=已暂停(映射为已终止)，3=已完成(映射为已结束)
        // CRM系统 clue_status：0=未生效，1=服务中，2=已停止，3=未知，10=进行中，30=进行中，40=已暂停，50=已终止
        if (isset($processed['clue_status'])) {
            $clueStatusMap = array(
                '服务中' => 1,    // 进行中 & 未开始 → 服务中
                '已终止' => 50,   // 已暂停 → 已终止
                '已结束' => 50,   // 已完成 → 已结束(终止)
                '未生效' => 0,
                '已停止' => 2,
                '其他' => 3,
            );
            if (isset($clueStatusMap[$processed['clue_status']])) {
                $processed['clue_status'] = $clueStatusMap[$processed['clue_status']];
            } elseif (!is_numeric($processed['clue_status'])) {
                // 如果不是数字且不在映射表中，默认为服务中
                $processed['clue_status'] = 1;
            }
        } else {
            // 如果没有提供状态，默认为服务中
            $processed['clue_status'] = 1;
        }
        
        // 3. 业务大类转换（参考 ImportForm::valYewudalei）
        if (isset($processed['yewudalei'])) {
            $yewudalei = $processed['yewudalei'];
            $clueType = isset($processed['clue_type']) ? $processed['clue_type'] : 1;
            
            // 根据客户类别调整业务大类名称
            if ($clueType == 1) {
                // 地推客户，业务大类固定为"地推"
                $yewudalei = '地推';
            } elseif ($yewudalei == '地推') {
                // KA客户，如果业务大类是"地推"，改为"KA"
                $yewudalei = 'KA';
            }
            
            // 从 sal_yewudalei 表查询ID
            if (!is_numeric($yewudalei)) {
                $yewudaleiId = DataMigrationHelper::getYewudaleiIdByName($yewudalei, $connection);
                if ($yewudaleiId) {
                    $processed['yewudalei'] = $yewudaleiId;
                } else {
                    // 如果没找到，使用默认值
                    $defaultName = ($clueType == 1) ? '地推' : 'KA';
                    $yewudaleiId = DataMigrationHelper::getYewudaleiIdByName($defaultName, $connection);
                    $processed['yewudalei'] = $yewudaleiId ?: null;
                }
            }
        }
        
        // 4. 员工编号转ID
        if (isset($data['跟进销售的员工编号'])) {
            $empCode = $data['跟进销售的员工编号'];
            $empId = DataMigrationHelper::getEmployeeIdByCode($empCode, $connection);
            if ($empId) {
                $processed['rec_employee_id'] = $empId;
            } else {
                throw new Exception('跟进销售的员工编号不存在：' . $empCode);
            }
        }
        
        // 5. 城市名称转代码（"全国"改为"中国"）
        if (isset($processed['city'])) {
            // 将"全国"统一改为"中国"
            if ($processed['city'] === '全国') {
                $processed['city'] = '中国';
            }
            // 如果不是城市代码格式，转换为城市代码
            if (!preg_match('/^[A-Z]{2,3}$/', $processed['city'])) {
                $cityCode = DataMigrationHelper::getCityCodeByName($processed['city'], $connection);
                if ($cityCode) {
                    $processed['city'] = $cityCode;
                } else {
                    throw new Exception('城市不存在：' . $processed['city']);
                }
            }
        }
        
        // 5.1 服务类型转换（参考 ImportForm::valServiceType）
        if (isset($processed['service_type']) && !empty($processed['service_type'])) {
            $serviceName = $processed['service_type'];
            if (!is_numeric($serviceName)) {
                $suffix = Yii::app()->params['envSuffix'];
                $serviceList = explode(',', $serviceName);
                $serviceIds = array();
                foreach ($serviceList as $serviceStr) {
                    $serviceStr = trim($serviceStr);
                    if (!empty($serviceStr)) {
                        $serviceId = $connection->createCommand()
                            ->select('id')
                            ->from("swoper{$suffix}.swo_customer_type")
                            ->where('description=:description', array(':description' => $serviceStr))
                            ->queryScalar();
                        if ($serviceId) {
                            $serviceIds[] = $serviceId;
                        }
                    }
                }
                if (!empty($serviceIds)) {
                    $processed['service_type'] = $serviceIds;
                }
            } elseif (is_numeric($serviceName)) {
                $processed['service_type'] = array(intval($serviceName));
            }
        }
        
        // 5.2 行业类别转换（参考 ImportForm::valCustClass，使用缓存）
        if (isset($processed['cust_class']) && !empty($processed['cust_class'])) {
            $custClass = $processed['cust_class'];
            if (!is_numeric($custClass)) {
                $row = DataMigrationHelper::getCustClassByName($custClass, $connection);
                if ($row) {
                    $processed['cust_class'] = $row['id'];
                    $processed['cust_class_group'] = $row['nature_id'];
                } else {
                    $processed['cust_class'] = null;
                }
            }
        } else {
            $processed['cust_class'] = null;
        }
        
        // 5.3 是否集团客户转换（参考 ImportForm::valGroupBool）
        $clueType = isset($processed['clue_type']) ? $processed['clue_type'] : 1;
        if ($clueType == 2) {
            // KA客户自动为集团客户
            $processed['group_bool'] = 'Y';
        } else {
            // 地推客户根据输入值判断
            if (isset($processed['group_bool'])) {
                if ($processed['group_bool'] === '是' || $processed['group_bool'] === 'Y' || $processed['group_bool'] === '1' || $processed['group_bool'] === 1) {
                    $processed['group_bool'] = 'Y';
                } else {
                    $processed['group_bool'] = 'N';
                }
            } else {
                $processed['group_bool'] = 'N';
            }
        }
        
        // 5.4 重点客户转换（参考 ImportForm::valVip）
        if (isset($processed['cust_vip'])) {
            if ($processed['cust_vip'] === '是' || $processed['cust_vip'] === 'Y' || $processed['cust_vip'] === '1' || $processed['cust_vip'] === 1) {
                $processed['cust_vip'] = 'Y';
            } else {
                $processed['cust_vip'] = 'N';
            }
        } else {
            $processed['cust_vip'] = 'N';
        }
        
        // 5.5 区域转换（参考 ImportForm::valDistrict）
        if (isset($processed['district']) && !empty($processed['district'])) {
            $districtName = $processed['district'];
            if (!is_numeric($districtName)) {
                $cityName = isset($data['城市']) ? $data['城市'] : '';
                $districtName = str_replace("'", "\\'", $districtName);
                $row = $connection->createCommand()
                    ->select("id, tree_names,
                        (CASE 
                            WHEN area_name='{$districtName}' THEN 10
                            ELSE 0
                        END) as order_one,
                        (CASE 
                            WHEN tree_names LIKE '%{$cityName}%' AND area_name LIKE '%{$districtName}%' THEN 9
                            WHEN tree_names LIKE '%{$cityName}%' THEN 8
                            ELSE 0
                        END) as order_num")
                    ->from('sal_national_area')
                    ->where("type=3 AND tree_names LIKE '%{$districtName}%'")
                    ->order('order_one DESC, order_num DESC')
                    ->queryRow();
                if ($row) {
                    $processed['district'] = $row['id'];
                    if (empty($processed['address'])) {
                        $processed['address'] = $row['tree_names'];
                    }
                } else {
                    $processed['district'] = null;
                }
            }
        } else {
            $processed['district'] = null;
        }
        
        // 6. 处理其它销售
        if (isset($data['其它销售'])) {
            $staffCodes = explode(';', $data['其它销售']);
            $staffIds = array();
            foreach ($staffCodes as $code) {
                $code = trim($code);
                if (!empty($code)) {
                    $staffId = DataMigrationHelper::getEmployeeIdByCode($code, $connection);
                    if ($staffId) {
                        $staffIds[] = $staffId;
                    }
                }
            }
            if (!empty($staffIds)) {
                $processed['u_staff_list'] = implode(';', $staffIds);
            }
        }
        
        // 7. 处理其它城市
        if (isset($data['其它城市'])) {
            $cityNames = explode(';', $data['其它城市']);
            $cityCodes = array();
            foreach ($cityNames as $name) {
                $name = trim($name);
                if (!empty($name)) {
                    // 将"全国"改为"中国"
                    if ($name === '全国') {
                        $name = '中国';
                    }
                    $cityCode = DataMigrationHelper::getCityCodeByName($name, $connection);
                    if ($cityCode) {
                        $cityCodes[] = $cityCode;
                    }
                }
            }
            if (!empty($cityCodes)) {
                $processed['u_area_list'] = implode(';', $cityCodes);
            }
        }
        
        // 8. 日期格式处理
        if (isset($processed['entry_date'])) {
            $timestamp = strtotime($processed['entry_date']);
            if ($timestamp) {
                $processed['entry_date'] = date('Y-m-d', $timestamp);
            }
        }
        
        // 9. 可选整数字段空值处理（空字符串转为null）
        // 注意：u_id 是派单系统客户id，不应该为空，不在此处理
        $optionalIntegerFields = array('area', 'u_group_id', 'u_area_id', 'u_staff_id', 'u_person_id', 'district', 'cust_class', 'cust_class_group');
        foreach ($optionalIntegerFields as $field) {
            if (isset($processed[$field]) && $processed[$field] === '') {
                $processed[$field] = null;
            }
        }
        
        return $processed;
    }
    
    /**
     * 门店数据预处理
     */
    protected function preprocessStoreData($data, $connection)
    {
        $processed = array();
        
        // 基本字段映射
        // 数据结构说明：
        // 派单 lbs_ka_project_management (KA项目) => CRM sal_clue (客户)
        //   - project_code => clue_code (客户编号)
        // 派单 lbs_company_customer (门店) => CRM sal_clue_store (门店)
        //   - ka_id => lbs_ka_project_management.project_id
        //   - 通过 project_code 关联到 CRM sal_clue.clue_code
        //   - customer_code => store_code (门店编号)
        //   - name_zh => store_name (门店名称)
        $fieldMap = array(
            '客户编号' => 'clue_code',            // 派单project_code → CRM客户编号(clue_code)
            '门店编号' => 'store_code',           // 派单customer_code → CRM门店编号
            '客户名称' => 'store_name',           // 派单name_zh → CRM门店名称
            '客户简称' => 'store_full_name',      // 派单简称 → CRM门店简称
            '门店简称' => 'store_full_name',      // 兼容字段
            '客户类别' => 'clue_type',            // 门店类别
            '门店状态' => 'store_status',         // 门店状态
            '跟进销售的员工编号' => 'create_staff',
            '服务类型' => 'service_type',
            '城市' => 'city',
            '办事处' => 'office_id',
            '区域' => 'district',
            '详细地址' => 'address',
            '经度' => 'longitude',
            '纬度' => 'latitude',
            '行业类别' => 'cust_class',
            '业务大类' => 'yewudalei',
            '税号' => 'tax_id',
            '开票地址' => 'invoice_address',
            '开票开户行' => 'invoice_number',
            '开票账号' => 'invoice_user',
            '开票备注' => 'invoice_rmk',
            '开票抬头' => 'invoice_header',
            '联系人编号' => 'person_code',
            '联系人名称' => 'cust_person',
            '联系人电话' => 'cust_tel',
            '联系人邮箱' => 'cust_email',
            '联系人职务' => 'cust_person_role',
            '面积' => 'area',
            '门店备注' => 'store_remark',
            '派单系统门店id' => 'u_id',
            '派单系统门店关联联系人id' => 'u_person_id',
            '其它联系人' => 'u_person_list',
        );
        
        foreach ($fieldMap as $chineseKey => $englishKey) {
            if (isset($data[$chineseKey])) {
                $processed[$englishKey] = $data[$chineseKey];
            }
        }
        
        // 自动提取税号（从开票备注等字段中智能识别税号）
        $processed = $this->autoExtractTaxId($processed);
        
        // 处理其它联系人列表（参考 ImportForm::valUPerson）
        if (isset($processed['u_person_list']) && !empty($processed['u_person_list'])) {
            $personList = explode(';', $processed['u_person_list']);
            $uPersonData = array();
            if (!empty($personList)) {
                foreach ($personList as $personStr) {
                    $personItem = explode(',', $personStr);
                    if (!empty($personItem[1]) && !empty($personItem[2])) {
                        $temp = array(
                            'person_code' => $personItem[0],
                            'cust_person' => $personItem[1],
                            'cust_tel' => trim($personItem[2]),
                            'cust_email' => empty($personItem[3]) ? null : trim($personItem[3]),
                            'cust_person_role' => empty($personItem[4]) ? null : trim($personItem[4]),
                        );
                        if (!empty($personItem[5])) {
                            $personItem[5] = trim($personItem[5]);
                            if (!empty($personItem[5]) && is_numeric($personItem[5])) {
                                $temp['u_id'] = intval($personItem[5]);
                            }
                        }
                        if (!empty($personItem[6])) {
                            $personItem[6] = trim($personItem[6]);
                            if (!empty($personItem[6]) && is_numeric($personItem[6])) {
                                $temp['u_group_id'] = intval($personItem[6]);
                            }
                        }
                        $uPersonData[] = $temp;
                    }
                }
            }
            $processed['uPersonData'] = $uPersonData;
        }
        
        // 门店类别转换（参考 ImportForm::valClueType）
        if (isset($processed['clue_type'])) {
            $clueTypeMap = array('地推' => 1, 'KA' => 2);
            if (isset($clueTypeMap[$processed['clue_type']])) {
                $processed['clue_type'] = $clueTypeMap[$processed['clue_type']];
            } elseif (!is_numeric($processed['clue_type'])) {
                // 如果不是数字且不在映射表中，根据客户编号智能判断
                // 有客户编号（project_code）= KA客户，无客户编号 = 地推客户
                $processed['clue_type'] = !empty($processed['clue_code']) ? 2 : 1;
            }
        } else {
            // 如果没有提供clue_type，根据客户编号智能判断
            // 有客户编号（project_code）= KA客户，无客户编号 = 地推客户
            $processed['clue_type'] = !empty($processed['clue_code']) ? 2 : 1;
        }
        
        // 门店状态转换（派单系统状态 → CRM状态）
        // 派单系统：status=1"服务中"，status=2"已停止"，status=3"其他"
        // CRM系统：0=未生效，1=未服务，2=服务中，3=已停止，4=其他
        if (isset($processed['store_status'])) {
            $storeStatusMap = array(
                '服务中' => 2,
                '已停止' => 3,
                '其他' => 4,
                '未服务' => 1,
                '未生效' => 0,
            );
            if (isset($storeStatusMap[$processed['store_status']])) {
                $processed['store_status'] = $storeStatusMap[$processed['store_status']];
            } elseif (!is_numeric($processed['store_status'])) {
                // 如果不是数字且不在映射表中，默认为服务中
                $processed['store_status'] = 2;
            }
        } else {
            // 如果没有提供状态，默认为服务中
            $processed['store_status'] = 2;
        }
        
        // 员工编号转ID（参考 ImportForm::valEmployee）
        if (isset($processed['create_staff']) && !empty($processed['create_staff'])) {
            $empCode = $processed['create_staff'];
            $empId = DataMigrationHelper::getEmployeeIdByCode($empCode, $connection);
            if ($empId) {
                $processed['create_staff'] = $empId;
            } else {
                throw new Exception('跟进销售的员工编号不存在：' . $empCode);
            }
        }
        
        // 服务类型转换（参考 ImportForm::valServiceType） - 可选字段
        if (isset($processed['service_type']) && !empty($processed['service_type'])) {
            $serviceName = $processed['service_type'];
            if (!is_numeric($serviceName)) {
                $suffix = Yii::app()->params['envSuffix'];
                $serviceList = explode(',', $serviceName);
                $serviceIds = array();
                foreach ($serviceList as $serviceStr) {
                    $serviceStr = trim($serviceStr);
                    if (!empty($serviceStr)) {
                        $row = $connection->createCommand()
                            ->select('id')
                            ->from("swoper{$suffix}.swo_customer_type")
                            ->where('description=:description', array(':description' => $serviceStr))
                            ->queryRow();
                        if ($row) {
                            $serviceIds[] = $row['id'];
                        }
                    }
                }
                if (!empty($serviceIds)) {
                    $processed['service_type'] = json_encode($serviceIds);
                } else {
                    $processed['service_type'] = null;
                }
            }
        } else {
            $processed['service_type'] = null;
        }
        
        // 办事处转换（参考 ImportForm::valOffice） - 可选字段
        if (isset($processed['office_id']) && !empty($processed['office_id'])) {
            $officeName = $processed['office_id'];
            if (!is_numeric($officeName)) {
                $city = isset($processed['city']) ? $processed['city'] : '';
                $suffix = Yii::app()->params['envSuffix'];
                $row = $connection->createCommand()
                    ->select('id')
                    ->from("hr{$suffix}.hr_office")
                    ->where('name=:name AND city=:city', array(':name' => $officeName, ':city' => $city))
                    ->queryRow();
                if ($row) {
                    $processed['office_id'] = $row['id'];
                } else {
                    $processed['office_id'] = 0;
                }
            }
        } else {
            $processed['office_id'] = 0;
        }
        
        // 业务大类转换（参考 ImportForm::valYewudalei）
        if (isset($processed['yewudalei'])) {
            $yewudalei = $processed['yewudalei'];
            $clueType = isset($processed['clue_type']) ? $processed['clue_type'] : 1;
            
            // 根据客户类别调整业务大类名称
            if ($clueType == 1) {
                // 地推客户，业务大类固定为"地推"
                $yewudalei = '地推';
            } elseif ($yewudalei == '地推') {
                // KA客户，如果业务大类是"地推"，改为"KA"
                $yewudalei = 'KA';
            }
            
            // 从 sal_yewudalei 表查询ID
            if (!is_numeric($yewudalei)) {
                $yewudaleiId = DataMigrationHelper::getYewudaleiIdByName($yewudalei, $connection);
                if ($yewudaleiId) {
                    $processed['yewudalei'] = $yewudaleiId;
                } else {
                    // 如果没找到，使用默认值
                    $defaultName = ($clueType == 1) ? '地推' : 'KA';
                    $yewudaleiId = DataMigrationHelper::getYewudaleiIdByName($defaultName, $connection);
                    $processed['yewudalei'] = $yewudaleiId ?: null;
                }
            }
        }
        
        // 员工编号转ID
        if (isset($data['跟进销售的员工编号'])) {
            $empCode = $data['跟进销售的员工编号'];
            $empId = DataMigrationHelper::getEmployeeIdByCode($empCode, $connection);
            if ($empId) {
                $processed['create_staff'] = $empId;
            } else {
                throw new Exception('跟进销售的员工编号不存在：' . $empCode);
            }
        }
        
        // 城市名称转代码（"全国"改为"中国"）
        if (isset($processed['city'])) {
            // 将"全国"统一改为"中国"
            if ($processed['city'] === '全国') {
                $processed['city'] = '中国';
            }
            // 如果不是城市代码格式，转换为城市代码
            if (!preg_match('/^[A-Z]{2,3}$/', $processed['city'])) {
                $cityCode = DataMigrationHelper::getCityCodeByName($processed['city'], $connection);
                if ($cityCode) {
                    $processed['city'] = $cityCode;
                } else {
                    throw new Exception('城市不存在：' . $processed['city']);
                }
            }
        }
        
        // 行业类别转换（参考 ImportForm::valCustClass，使用缓存）
        if (isset($processed['cust_class']) && !empty($processed['cust_class'])) {
            $custClass = $processed['cust_class'];
            if (!is_numeric($custClass)) {
                $row = DataMigrationHelper::getCustClassByName($custClass, $connection);
                if ($row) {
                    $processed['cust_class'] = $row['id'];
                    $processed['cust_class_group'] = $row['nature_id'];
                } else {
                    $processed['cust_class'] = null;
                }
            }
        } else {
            $processed['cust_class'] = null;
        }
        
        // 区域转换（参考 ImportForm::valDistrict）
        if (isset($processed['district']) && !empty($processed['district'])) {
            $districtName = $processed['district'];
            if (!is_numeric($districtName)) {
                $cityName = isset($data['城市']) ? $data['城市'] : '';
                $districtName = str_replace("'", "\\'", $districtName);
                $row = $connection->createCommand()
                    ->select("id, tree_names,
                        (CASE 
                            WHEN area_name='{$districtName}' THEN 10
                            ELSE 0
                        END) as order_one,
                        (CASE 
                            WHEN tree_names LIKE '%{$cityName}%' AND area_name LIKE '%{$districtName}%' THEN 9
                            WHEN tree_names LIKE '%{$cityName}%' THEN 8
                            ELSE 0
                        END) as order_num")
                    ->from('sal_national_area')
                    ->where("type=3 AND tree_names LIKE '%{$districtName}%'")
                    ->order('order_one DESC, order_num DESC')
                    ->queryRow();
                if ($row) {
                    $processed['district'] = $row['id'];
                    if (empty($processed['address'])) {
                        $processed['address'] = $row['tree_names'];
                    }
                } else {
                    $processed['district'] = null;
                }
            }
        } else {
            $processed['district'] = null;
        }
        
        // 可选整数字段空值处理（空字符串转为null）
        // 注意：u_id 是派单系统门店id，不应该为空，不在此处理
        $optionalIntegerFields = array('area', 'u_person_id', 'district', 'cust_class', 'cust_class_group');
        foreach ($optionalIntegerFields as $field) {
            if (isset($processed[$field]) && $processed[$field] === '') {
                $processed[$field] = null;
            }
        }
        
        return $processed;
    }
    
    /**
     * 主合约数据预处理
     */
    protected function preprocessContractData($data, $connection)
    {
        $processed = array();
        
        // 基本字段映射
        $fieldMap = array(
            '主合同编号' => 'cont_code',
            '客户编号' => 'clue_code',
            '主合同状态' => 'cont_status',
            '服务项目' => 'busine_name',
            '签约时间' => 'sign_date',
            '合约开始时间' => 'cont_start_dt',
            '合约结束时间' => 'cont_end_dt',
            '业务大类' => 'yewudalei',
            '主体公司' => 'lbs_main',
            '门店总数量' => 'store_sum',
            '合约总金额' => 'total_amt',
            '服务总次数' => 'total_sum',
            '结算方式' => 'settle_type',
            '付款方式' => 'pay_type',
            '押金备注' => 'deposit_rmk',
            '已收押金' => 'deposit_amt',
            '所需押金' => 'deposit_need',
            '收费方式' => 'fee_type',
            '预付月数' => 'pay_month',
            '起始月' => 'pay_start',
            '是否对账' => 'bill_bool',
            '账单日' => 'bill_day',
            '付款周期' => 'pay_week',
            '服务时长(分钟)' => 'service_timer',
            '是否优先安排服务' => 'prioritize_service',
            '应收期限' => 'receivable_day',
            '剩余次数' => 'surplus_num',
            '剩余金额' => 'surplus_amt',
            '终止或暂停日期' => 'stop_date',
            '派单系统合约id' => 'u_id',
        );
        
        foreach ($fieldMap as $chineseKey => $englishKey) {
            if (isset($data[$chineseKey])) {
                $processed[$englishKey] = $data[$chineseKey];
            }
        }
        
        // 1. 业务大类转换（参考 ImportForm::valYewudalei）
        if (isset($processed['yewudalei']) && !empty($processed['yewudalei'])) {
            $yewudalei = $processed['yewudalei'];
            if (!is_numeric($yewudalei)) {
                $yewudaleiId = DataMigrationHelper::getYewudaleiIdByName($yewudalei, $connection);
                if ($yewudaleiId) {
                    $processed['yewudalei'] = $yewudaleiId;
                } else {
                    $processed['yewudalei'] = null;
                }
            }
        }
        
        // 2. 主体公司转换（从名称查询ID，不存在则自动创建）
        if (isset($processed['lbs_main']) && !empty($processed['lbs_main'])) {
            $lbsMainName = $processed['lbs_main'];
            if (!is_numeric($lbsMainName)) {
                $lbsMainId = DataMigrationHelper::getLbsMainIdByName($lbsMainName, $connection);
                if ($lbsMainId) {
                    $processed['lbs_main'] = $lbsMainId;
                } else {
                    // 主体公司不存在，自动创建
                    // 尝试从关联客户获取城市代号
                    $cityCode = null;
                    if (isset($processed['clue_code']) && !empty($processed['clue_code'])) {
                        $clueCity = $connection->createCommand()
                            ->select('city')
                            ->from('sal_clue')
                            ->where('clue_code=:code', array(':code' => $processed['clue_code']))
                            ->queryScalar();
                        if ($clueCity) {
                            $cityCode = $clueCity;
                        }
                    }
                    $lbsMainId = DataMigrationHelper::createLbsMain($lbsMainName, $connection, $cityCode);
                    $processed['lbs_main'] = $lbsMainId;
                }
            }
        }
        
        // 3. 员工编号转ID
        if (isset($data['销售员工编号'])) {
            $empCode = $data['销售员工编号'];
            $empId = DataMigrationHelper::getEmployeeIdByCode($empCode, $connection);
            if ($empId) {
                $processed['sales_id'] = $empId;
            } else {
                throw new Exception('销售员工编号不存在：' . $empCode);
            }
        }
        
        // 4. 处理服务项目（可能是逗号分隔的多个）
        if (isset($processed['busine_name'])) {
            $busineNames = explode(',', $processed['busine_name']);
            $ids = array();
            $names = array();
            foreach ($busineNames as $name) {
                $name = trim($name);
                if (!empty($name)) {
                    $row = DataMigrationHelper::getServiceTypeByName($name, $connection);
                    if ($row) {
                        $ids[] = $row['id_char'];
                        $names[] = $name;
                    } else {
                        throw new Exception('服务项目不存在：' . $name);
                    }
                }
            }
            $processed['busine_id'] = implode(',', $ids);
            $processed['busine_id_text'] = implode('、', $names);
        }
        
        // 5. 状态转换
        if (isset($processed['cont_status'])) {
            $statusMap = array('生效中' => 30, '暂停' => 40, '终止' => 50);
            if (isset($statusMap[$processed['cont_status']])) {
                $processed['cont_status'] = $statusMap[$processed['cont_status']];
            }
        }
        
        // 6. 日期处理（空字符串转为NULL，避免MySQL日期格式错误）
        $dateFields = array('sign_date', 'cont_start_dt', 'cont_end_dt', 'stop_date');
        foreach ($dateFields as $field) {
            if (isset($processed[$field]) && $processed[$field] !== '') {
                $timestamp = strtotime($processed[$field]);
                if ($timestamp) {
                    $processed[$field] = date('Y-m-d', $timestamp);
                } else {
                    $processed[$field] = null;  // 无效日期设置为NULL
                }
            } else {
                $processed[$field] = null;  // 空字符串设置为NULL
            }
        }
        
        // 7. 整数字段空值处理（空字符串转为NULL，避免MySQL整数格式错误）
        $intFields = array('surplus_num', 'total_sum', 'pay_month', 'pay_start', 'service_timer', 'cont_month_len');
        foreach ($intFields as $field) {
            if (isset($processed[$field]) && $processed[$field] === '') {
                $processed[$field] = null;
            }
        }
        
        // 8. 金额字段处理
        $moneyFields = array('total_amt', 'deposit_amt', 'deposit_need', 'surplus_amt');
        foreach ($moneyFields as $field) {
            if (isset($processed[$field])) {
                $processed[$field] = str_replace(',', '', $processed[$field]);
                if ($processed[$field] === '') {
                    $processed[$field] = 0;
                }
            }
        }
        
        // 9. 布尔值处理
        $boolFields = array('bill_bool', 'prioritize_service');
        foreach ($boolFields as $field) {
            if (isset($processed[$field])) {
                if ($processed[$field] === '是' || $processed[$field] === 'Y' || $processed[$field] === '1') {
                    $processed[$field] = 'Y';
                } else {
                    $processed[$field] = 'N';
                }
            }
        }
        
        // 9. 付款方式转换（文字 → ID，参考 ImportVirForm::valPayType）
        if (isset($processed['pay_type']) && !empty($processed['pay_type'])) {
            $payType = $processed['pay_type'];
            if (!is_numeric($payType)) {
                $list = CGetName::getPayTypeList();
                $key = array_search($payType, $list);
                if ($key !== false) {
                    $processed['pay_type'] = $key;
                } else {
                    // 付款方式不存在，记录日志但继续处理（设为null）
                    Yii::log("付款方式不存在: {$payType}", 'warning', 'DataMigrationForm');
                    $processed['pay_type'] = null;
                }
            }
        }
        
        // 10. 付款周期转换（文字 → ID，参考 ImportVirForm::valPayWeek）
        if (isset($processed['pay_week']) && !empty($processed['pay_week'])) {
            $payWeek = $processed['pay_week'];
            if (!is_numeric($payWeek)) {
                $list = CGetName::getPayWeekList();
                $key = array_search($payWeek, $list);
                if ($key !== false) {
                    $processed['pay_week'] = $key;
                } else {
                    // 付款周期不存在，记录日志但继续处理（设为null）
                    Yii::log("付款周期不存在: {$payWeek}", 'warning', 'DataMigrationForm');
                    $processed['pay_week'] = null;
                }
            }
        }
        
        // 11. 收费方式转换（文字 → ID，参考 ImportVirForm::valFeeType）
        if (isset($processed['fee_type']) && !empty($processed['fee_type'])) {
            $feeType = $processed['fee_type'];
            if (!is_numeric($feeType)) {
                $list = CGetName::getFeeTypeList();
                $key = array_search($feeType, $list);
                if ($key !== false) {
                    $processed['fee_type'] = $key;
                } else {
                    // 收费方式不存在，记录日志但继续处理（设为null）
                    Yii::log("收费方式不存在: {$feeType}", 'warning', 'DataMigrationForm');
                    $processed['fee_type'] = null;
                }
            }
        }
        
        // 12. 结算方式转换（文字 → ID，参考 ImportVirForm::valSettleType）
        if (isset($processed['settle_type']) && !empty($processed['settle_type'])) {
            $settleType = $processed['settle_type'];
            if (!is_numeric($settleType)) {
                $list = CGetName::getSettleTypeList();
                $key = array_search($settleType, $list);
                if ($key !== false) {
                    $processed['settle_type'] = $key;
                } else {
                    // 结算方式不存在，记录日志但继续处理（设为null）
                    Yii::log("结算方式不存在: {$settleType}", 'warning', 'DataMigrationForm');
                    $processed['settle_type'] = null;
                }
            }
        }
        
        // 13. 账单日转换（文字 → ID，参考 ImportVirForm::valBillDay）
        if (isset($processed['bill_day']) && !empty($processed['bill_day'])) {
            $billDay = $processed['bill_day'];
            if (!is_numeric($billDay)) {
                $list = CGetName::getBillDayList();
                $key = array_search($billDay, $list);
                if ($key !== false) {
                    $processed['bill_day'] = $key;
                } else {
                    // 账单日不存在，记录日志但继续处理（设为null）
                    Yii::log("账单日不存在: {$billDay}", 'warning', 'DataMigrationForm');
                    $processed['bill_day'] = null;
                }
            }
        }
        
        // 14. 应收期限转换（文字 → ID，参考 ImportVirForm::valReceivableDay）
        if (isset($processed['receivable_day']) && !empty($processed['receivable_day'])) {
            $receivableDay = $processed['receivable_day'];
            if (!is_numeric($receivableDay)) {
                $list = CGetName::getReceivableDayList();
                $key = array_search($receivableDay, $list);
                if ($key !== false) {
                    $processed['receivable_day'] = $key;
                } else {
                    // 应收期限不存在，记录日志但继续处理（设为null）
                    Yii::log("应收期限不存在: {$receivableDay}", 'warning', 'DataMigrationForm');
                    $processed['receivable_day'] = null;
                }
            }
        }
        
        // 设置可能缺失的默认值（确保InsertContractData不会报错）
        if (!isset($processed['total_sum'])) $processed['total_sum'] = 0;
        if (!isset($processed['stop_date'])) $processed['stop_date'] = null;
        if (!isset($processed['surplus_num'])) $processed['surplus_num'] = null;
        if (!isset($processed['surplus_amt'])) $processed['surplus_amt'] = null;
        if (!isset($processed['prioritize_service'])) $processed['prioritize_service'] = 'N';
        if (!isset($processed['service_timer'])) $processed['service_timer'] = null;
        if (!isset($processed['pay_type'])) $processed['pay_type'] = null;
        if (!isset($processed['pay_week'])) $processed['pay_week'] = null;
        if (!isset($processed['pay_month'])) $processed['pay_month'] = null;
        if (!isset($processed['pay_start'])) $processed['pay_start'] = null;
        if (!isset($processed['deposit_need'])) $processed['deposit_need'] = null;
        if (!isset($processed['deposit_amt'])) $processed['deposit_amt'] = null;
        if (!isset($processed['deposit_rmk'])) $processed['deposit_rmk'] = null;
        if (!isset($processed['fee_type'])) $processed['fee_type'] = null;
        if (!isset($processed['settle_type'])) $processed['settle_type'] = null;
        if (!isset($processed['bill_day'])) $processed['bill_day'] = null;
        if (!isset($processed['bill_bool'])) $processed['bill_bool'] = 'N';
        if (!isset($processed['receivable_day'])) $processed['receivable_day'] = null;
        
        // 可选整数字段空值处理（空字符串转为null）
        // 注意：u_id 是派单系统合约id，不应该为空，不在此处理
        $optionalIntegerFields = array('store_sum', 'total_sum', 'surplus_num', 'pay_month', 'pay_start', 
                                        'bill_day', 'service_timer', 'receivable_day', 'yewudalei', 'lbs_main');
        foreach ($optionalIntegerFields as $field) {
            if (isset($processed[$field]) && $processed[$field] === '') {
                $processed[$field] = null;
            }
        }
        
        return $processed;
    }
    
    /**
     * 虚拟合约数据预处理
     */
    protected function preprocessVirtualContractData($data, $connection)
    {
        $processed = array();
        
        // 基本字段映射
        $fieldMap = array(
            '主合同编号' => 'cont_code',
            '虚拟合同编号' => 'vir_code',
            '服务项目' => 'busine_name',
            '门店编号' => 'store_code',
            '虚拟合同状态' => 'vir_status',
            '签约时间' => 'sign_date',
            '合约开始时间' => 'cont_start_dt',
            '合约结束时间' => 'cont_end_dt',
            '业务大类' => 'yewudalei',
            '主体公司' => 'lbs_main',
            '销售员工编号' => 'sales_code', // 先映射，后面会转换为sales_id
            '销售关联合约的id' => 'sales_u_id',
            '合约月金额' => 'month_amt',
            '合约年金额' => 'year_amt',
            '服务总次数' => 'service_sum',
            '服务频次类型' => 'service_fre_type',
            '服务频次(文字)' => 'u_service_title',
            '服务频次详情' => 'u_service_info',
            '服务项目详情' => 'serviceTypeInfo',
            '结算方式' => 'settle_type',
            '付款方式' => 'pay_type',
            '押金备注' => 'deposit_rmk',
            '已收押金' => 'deposit_amt',
            '所需押金' => 'deposit_need',
            '收费方式' => 'fee_type',
            '预付月数' => 'pay_month',
            '起始月' => 'pay_start',
            '是否对账' => 'bill_bool',
            '账单日' => 'bill_day',
            '付款周期' => 'pay_week',
            '服务时长(分钟)' => 'service_timer',
            '是否优先安排服务' => 'prioritize_service',
            '应收期限' => 'receivable_day',
            '剩余次数' => 'surplus_num',
            '剩余金额' => 'surplus_amt',
            '服务主体' => 'service_main',
            '首次日期' => 'first_date',
            '常规开始日期' => 'fast_date',
            '是否需安装费' => 'need_install',
            '安装金额' => 'amt_install',
            '被跨区业务员' => 'other_sales_code', // 先映射，后面会转换为other_sales_id
            '被跨区业务员关联合约的id' => 'other_sales_u_id',
            '被跨区业务员业务大类' => 'other_yewudalei',
            '首次技术员' => 'first_tech_code', // 先映射，后面会转换为first_tech_id
            '负责技术员' => 'technician_id_str',
            '外部数据来源' => 'external_source',
            // '终止或暂停原因' => 'stop_set_id',  // ❌ 不导入该字段（数据不规范）
            '终止或暂停日期' => 'stop_date',
            '发票金额' => 'invoice_amount',
            '派单系统id' => 'u_id',
        );
        
        foreach ($fieldMap as $chineseKey => $englishKey) {
            if (isset($data[$chineseKey])) {
                $processed[$englishKey] = $data[$chineseKey];
            }
        }
        
        // 1. 业务大类转换（参考 ImportForm::valYewudalei）
        if (isset($processed['yewudalei']) && !empty($processed['yewudalei'])) {
            $yewudalei = $processed['yewudalei'];
            if (!is_numeric($yewudalei)) {
                $yewudaleiId = DataMigrationHelper::getYewudaleiIdByName($yewudalei, $connection);
                if ($yewudaleiId) {
                    $processed['yewudalei'] = $yewudaleiId;
                } else {
                    $processed['yewudalei'] = null;
                }
            }
        }
        
        // 2. 主体公司转换（从名称查询ID，不存在则自动创建）
        if (isset($processed['lbs_main']) && !empty($processed['lbs_main'])) {
            $lbsMainName = $processed['lbs_main'];
            if (!is_numeric($lbsMainName)) {
                $lbsMainId = DataMigrationHelper::getLbsMainIdByName($lbsMainName, $connection);
                if ($lbsMainId) {
                    $processed['lbs_main'] = $lbsMainId;
                } else {
                    // 主体公司不存在，自动创建
                    // 尝试从关联门店获取城市代号
                    $cityCode = null;
                    if (isset($processed['store_code']) && !empty($processed['store_code'])) {
                        $storeCity = $connection->createCommand()
                            ->select('city')
                            ->from('sal_clue_store')
                            ->where('store_code=:code', array(':code' => $processed['store_code']))
                            ->queryScalar();
                        if ($storeCity) {
                            $cityCode = $storeCity;
                        }
                    }
                    $lbsMainId = DataMigrationHelper::createLbsMain($lbsMainName, $connection, $cityCode);
                    $processed['lbs_main'] = $lbsMainId;
                }
            }
        }
        
        // 3. 服务主体转换（从名称查询ID，不存在则自动创建）
        if (isset($processed['service_main']) && !empty($processed['service_main'])) {
            $serviceMainName = $processed['service_main'];
            if (!is_numeric($serviceMainName)) {
                $serviceMainId = DataMigrationHelper::getLbsMainIdByName($serviceMainName, $connection);
                if ($serviceMainId) {
                    $processed['service_main'] = $serviceMainId;
                } else {
                    // 服务主体不存在，自动创建
                    // 使用与主体公司相同的城市（通常服务主体和主体公司在同一城市）
                    $cityCode = null;
                    if (isset($processed['store_code']) && !empty($processed['store_code'])) {
                        $storeCity = $connection->createCommand()
                            ->select('city')
                            ->from('sal_clue_store')
                            ->where('store_code=:code', array(':code' => $processed['store_code']))
                            ->queryScalar();
                        if ($storeCity) {
                            $cityCode = $storeCity;
                        }
                    }
                    $serviceMainId = DataMigrationHelper::createLbsMain($serviceMainName, $connection, $cityCode);
                    $processed['service_main'] = $serviceMainId;
                }
            }
        }
        
        // 4. 员工编号转ID（销售）- 参考 ImportVirForm::valEmployee
        if (isset($processed['sales_code']) && !empty($processed['sales_code'])) {
            $empCode = $processed['sales_code'];
            $empId = DataMigrationHelper::getEmployeeIdByCode($empCode, $connection);
            if ($empId) {
                $processed['sales_id'] = $empId;
            } else {
                throw new Exception('销售员工编号不存在：' . $empCode);
            }
            unset($processed['sales_code']); // 删除临时字段
        }
        
        // 5. 被跨区业务员员工编号转ID - 参考 ImportVirForm::valEmployee
        if (isset($processed['other_sales_code']) && !empty($processed['other_sales_code'])) {
            $empCode = $processed['other_sales_code'];
            $empId = DataMigrationHelper::getEmployeeIdByCode($empCode, $connection);
            if ($empId) {
                $processed['other_sales_id'] = $empId;
            } else {
                // 被跨区业务员可以为空（参考 ImportVirForm，该字段 requite=>false）
                Yii::log("被跨区业务员编号不存在: {$empCode}", 'warning', 'DataMigrationForm');
                $processed['other_sales_id'] = null;
            }
            unset($processed['other_sales_code']); // 删除临时字段
        }
        
        // 6. 被跨区业务员相关字段的交叉验证 - 参考 ImportVirForm::valOtherSalesUID
        // 被跨区业务员关联合约的id填写后，被跨区业务员不能为空
        // 被跨区业务员填写后，被跨区业务员关联合约的id不能为空
        $hasOtherSalesId = isset($processed['other_sales_id']) && !empty($processed['other_sales_id']);
        $hasOtherSalesUId = isset($processed['other_sales_u_id']) && !empty($processed['other_sales_u_id']);
        
        if (!$hasOtherSalesId && $hasOtherSalesUId) {
            throw new Exception('被跨区业务员关联合约的id填写后，被跨区业务员不能为空');
        }
        if ($hasOtherSalesId && !$hasOtherSalesUId) {
            throw new Exception('被跨区业务员填写后，被跨区业务员关联合约的id不能为空');
        }
        
        // 7. 被跨区业务员业务大类转换 - 参考 ImportVirForm::valOtherYewudalei
        if (isset($processed['other_yewudalei']) && !empty($processed['other_yewudalei'])) {
            $otherYewudalei = $processed['other_yewudalei'];
            if (!is_numeric($otherYewudalei)) {
                $otherYewudaleiId = DataMigrationHelper::getYewudaleiIdByName($otherYewudalei, $connection);
                if ($otherYewudaleiId) {
                    $processed['other_yewudalei'] = $otherYewudaleiId;
                } else {
                    $processed['other_yewudalei'] = null;
                }
            }
        }
        
        // 8. 首次技术员编号转ID - 参考 ImportVirForm::valEmployee
        if (isset($processed['first_tech_code']) && !empty($processed['first_tech_code'])) {
            $empCode = $processed['first_tech_code'];
                $empId = DataMigrationHelper::getEmployeeIdByCode($empCode, $connection);
                if ($empId) {
                    $processed['first_tech_id'] = $empId;
            } else {
                // 首次技术员可以为空（参考 ImportVirForm，该字段 requite=>false）
                Yii::log("首次技术员编号不存在: {$empCode}", 'warning', 'DataMigrationForm');
                $processed['first_tech_id'] = null;
            }
            unset($processed['first_tech_code']); // 删除临时字段
        }
        
        // 9. 负责技术员编号转ID - 参考 ImportVirForm::valTechnicianList
        // 注意：ImportVirForm中处理technician_id_str时支持多个技术员（逗号分隔）
        // 这里暂时保持字段映射中的原始值，后续如需扩展可参考ImportVirForm的处理逻辑
        if (isset($processed['technician_id_str']) && !empty($processed['technician_id_str'])) {
            // 如果已经是ID（数字格式），保持不变
            // 如果是员工编号，需要转换（暂时保留原值，待确认派单系统返回格式）
            // TODO: 如需支持多个技术员编号转ID，参考 ImportVirForm::valTechnicianList
        }
        
        // 10. 处理服务项目（单个，使用缓存）
        if (isset($processed['busine_name'])) {
            $row = DataMigrationHelper::getServiceTypeByName($processed['busine_name'], $connection);
            if ($row) {
                $processed['busine_id'] = $row['id_char'];
                $processed['busine_id_int'] = $row['id']; // 用于查询 sal_service_type_info
                $processed['busine_id_text'] = $processed['busine_name'];
                $processed['service_type'] = $row['service_type'];
            } else {
                throw new Exception('服务项目不存在：' . $processed['busine_name']);
            }
        }
        
        // 11. 状态转换
        if (isset($processed['vir_status'])) {
            $statusMap = array('生效中' => 30, '暂停' => 40, '终止' => 50);
            if (isset($statusMap[$processed['vir_status']])) {
                $processed['vir_status'] = $statusMap[$processed['vir_status']];
            }
        }
        
        // 12. 服务频次类型转换
        if (isset($processed['service_fre_type'])) {
            $freTypeMap = array(
                '固定' => 1, 
                '固定每周' => 3,  // 固定频次每周
                '非固定' => 2, 
                '固定非固定金额' => 1,  // 固定频次非固定金额，映射为固定频次
                '呼叫式' => 3
            );
            if (isset($freTypeMap[$processed['service_fre_type']])) {
                $processed['service_fre_type'] = $freTypeMap[$processed['service_fre_type']];
            }
        }
        
        // 12.5 付款方式转换（文字 → ID，参考 ImportVirForm::valPayType）
        if (isset($processed['pay_type']) && !empty($processed['pay_type'])) {
            $payType = $processed['pay_type'];
            if (!is_numeric($payType)) {
                $list = CGetName::getPayTypeList();
                $key = array_search($payType, $list);
                if ($key !== false) {
                    $processed['pay_type'] = $key;
                } else {
                    // 付款方式不存在，记录日志但继续处理（设为null）
                    Yii::log("付款方式不存在: {$payType}", 'warning', 'DataMigrationForm');
                    $processed['pay_type'] = null;
                }
            }
        }
        
        // 12.6 付款周期转换（文字 → ID，参考 ImportVirForm::valPayWeek）
        if (isset($processed['pay_week']) && !empty($processed['pay_week'])) {
            $payWeek = $processed['pay_week'];
            if (!is_numeric($payWeek)) {
                $list = CGetName::getPayWeekList();
                $key = array_search($payWeek, $list);
                if ($key !== false) {
                    $processed['pay_week'] = $key;
                } else {
                    // 付款周期不存在，记录日志但继续处理（设为null）
                    Yii::log("付款周期不存在: {$payWeek}", 'warning', 'DataMigrationForm');
                    $processed['pay_week'] = null;
                }
            }
        }
        
        // 12.7 收费方式转换（文字 → ID，参考 ImportVirForm::valFeeType）
        if (isset($processed['fee_type']) && !empty($processed['fee_type'])) {
            $feeType = $processed['fee_type'];
            if (!is_numeric($feeType)) {
                $list = CGetName::getFeeTypeList();
                $key = array_search($feeType, $list);
                if ($key !== false) {
                    $processed['fee_type'] = $key;
                } else {
                    // 收费方式不存在，记录日志但继续处理（设为null）
                    Yii::log("收费方式不存在: {$feeType}", 'warning', 'DataMigrationForm');
                    $processed['fee_type'] = null;
                }
            }
        }
        
        // 12.8 结算方式转换（文字 → ID，参考 ImportVirForm::valSettleType）
        if (isset($processed['settle_type']) && !empty($processed['settle_type'])) {
            $settleType = $processed['settle_type'];
            if (!is_numeric($settleType)) {
                $list = CGetName::getSettleTypeList();
                $key = array_search($settleType, $list);
                if ($key !== false) {
                    $processed['settle_type'] = $key;
                } else {
                    // 结算方式不存在，记录日志但继续处理（设为null）
                    Yii::log("结算方式不存在: {$settleType}", 'warning', 'DataMigrationForm');
                    $processed['settle_type'] = null;
                }
            }
        }
        
        // 12.9 账单日转换（文字 → ID，参考 ImportVirForm::valBillDay）
        if (isset($processed['bill_day']) && !empty($processed['bill_day'])) {
            $billDay = $processed['bill_day'];
            if (!is_numeric($billDay)) {
                $list = CGetName::getBillDayList();
                $key = array_search($billDay, $list);
                if ($key !== false) {
                    $processed['bill_day'] = $key;
                } else {
                    // 账单日不存在，记录日志但继续处理（设为null）
                    Yii::log("账单日不存在: {$billDay}", 'warning', 'DataMigrationForm');
                    $processed['bill_day'] = null;
                }
            }
        }
        
        // 12.10 应收期限转换（文字 → ID，参考 ImportVirForm::valReceivableDay）
        if (isset($processed['receivable_day']) && !empty($processed['receivable_day'])) {
            $receivableDay = $processed['receivable_day'];
            if (!is_numeric($receivableDay)) {
                $list = CGetName::getReceivableDayList();
                $key = array_search($receivableDay, $list);
                if ($key !== false) {
                    $processed['receivable_day'] = $key;
                } else {
                    // 应收期限不存在，记录日志但继续处理（设为null）
                    Yii::log("应收期限不存在: {$receivableDay}", 'warning', 'DataMigrationForm');
                    $processed['receivable_day'] = null;
                }
            }
        }
        
        // 13. 日期处理（空字符串转为NULL，避免MySQL日期格式错误）
        $dateFields = array('sign_date', 'cont_start_dt', 'cont_end_dt', 'first_date', 'fast_date', 'stop_date');
        foreach ($dateFields as $field) {
            if (isset($processed[$field]) && $processed[$field] !== '') {
                $timestamp = strtotime($processed[$field]);
                if ($timestamp) {
                    $processed[$field] = date('Y-m-d', $timestamp);
                } else {
                    $processed[$field] = null;  // 无效日期设置为NULL
                }
            } else {
                $processed[$field] = null;  // 空字符串设置为NULL
            }
        }
        
        // 14. 整数字段空值处理（空字符串转为NULL，避免MySQL整数格式错误）
        $intFields = array('surplus_num', 'service_sum', 'pay_month', 'pay_start', 'service_timer', 'cont_month_len');
        foreach ($intFields as $field) {
            if (isset($processed[$field]) && $processed[$field] === '') {
                $processed[$field] = null;
            }
        }
        
        // 15. 金额字段处理
        $moneyFields = array('month_amt', 'year_amt', 'deposit_amt', 'deposit_need', 'surplus_amt', 'amt_install');
        foreach ($moneyFields as $field) {
            if (isset($processed[$field])) {
                $processed[$field] = str_replace(',', '', $processed[$field]);
                if ($processed[$field] === '') {
                    $processed[$field] = 0;
                }
            }
        }
        
        // 16. 布尔值处理
        $boolFields = array('bill_bool', 'prioritize_service', 'need_install');
        foreach ($boolFields as $field) {
            if (isset($processed[$field])) {
                if ($processed[$field] === '是' || $processed[$field] === 'Y' || $processed[$field] === '1') {
                    $processed[$field] = 'Y';
                } else {
                    $processed[$field] = 'N';
                }
            }
        }
        
        // 设置服务频次金额和次数（如果没有单独提供）
        if (!isset($processed['service_fre_amt'])) {
            $processed['service_fre_amt'] = isset($processed['year_amt']) ? $processed['year_amt'] : 0;
        }
        if (!isset($processed['service_fre_sum'])) {
            $processed['service_fre_sum'] = isset($processed['service_sum']) ? $processed['service_sum'] : 0;
        }
        
        // 可选整数字段空值处理（空字符串转为null）
        // 注意：u_id 是派单系统虚拟合约id，不应该为空，不在此处理
        $optionalIntegerFields = array('sales_u_id', 'other_sales_u_id', 'service_sum', 'service_fre_type',
                                        'pay_month', 'pay_start', 'bill_day', 'service_timer', 'receivable_day',
                                        'surplus_num', 'yewudalei', 'other_yewudalei', 'lbs_main', 'service_main',
                                        'sales_id', 'other_sales_id', 'first_tech_id', 'busine_id_int');
        foreach ($optionalIntegerFields as $field) {
            if (isset($processed[$field]) && $processed[$field] === '') {
                $processed[$field] = null;
            }
        }
        
        // 16. 根据主合同编号查找主合同（参考 ImportVirForm::valContCode）
        // 如果提供了主合同编号，尝试查找已存在的主合同，并关联相关信息
        if (isset($processed['cont_code']) && !empty($processed['cont_code'])) {
            try {
                $contRow = $connection->createCommand()
                    ->select('*')
                    ->from('sal_contract')
                    ->where('cont_code=:cont_code', array(':cont_code' => $processed['cont_code']))
                    ->queryRow();
                
                if ($contRow) {
                    // 找到主合同，关联相关ID
                    $processed['cont_id'] = $contRow['id'];
                    $processed['clue_service_id'] = $contRow['clue_service_id'];
                    
                    // 查找主合同的第一个进程ID（参考 ImportVirForm::valContCode）
                    $proRow = $connection->createCommand()
                        ->select('id')
                        ->from('sal_contpro')
                        ->where('cont_id=:cont_id', array(':cont_id' => $contRow['id']))
                        ->order('id ASC')
                        ->queryRow();
                    
                    if ($proRow) {
                        $processed['pro_id'] = $proRow['id'];
                    }
                    
                    Yii::log("虚拟合约关联到主合同: cont_code={$processed['cont_code']}, cont_id={$contRow['id']}", 'info', 'DataMigrationForm');
                } else {
                    // 主合同不存在，记录警告（虚拟合约导入时会自动创建主合同）
                    Yii::log("主合同编号不存在，将自动创建: cont_code={$processed['cont_code']}", 'warning', 'DataMigrationForm');
                }
            } catch (Exception $e) {
                Yii::log("查找主合同时出错: cont_code={$processed['cont_code']}, error={$e->getMessage()}", 'error', 'DataMigrationForm');
            }
        }
        
        // 处理服务项目详情（生成 virInfo 和 detail_json）
        $this->processServiceInfo($processed, $connection);
        
        // 处理服务频次详情（生成 u_service_json）
        $this->processServiceFrequency($processed);
        
        return $processed;
    }
    
    /**
     * 处理服务项目详情（参考 ImportVirForm::valServiceInfo）
     */
    protected function processServiceInfo(&$data, $connection)
    {
        $virDetail = array();
        $virInfo = array();
        
        // 基础服务项目信息
        if (isset($data['busine_id']) && isset($data['month_amt'])) {
            $virInfo[] = array(
                'field_id' => 'svc_' . $data['busine_id'], 
                'field_value' => $data['month_amt']
            );
            $virDetail['svc_' . $data['busine_id']] = $data['month_amt'];
        }
        
        // 服务频次相关字段
        $freeStrList = array(
            'FreType' => 'service_fre_type',
            'FreSum' => 'service_fre_sum',
            'FreAmt' => 'service_fre_amt',
            'FreJson' => 'service_fre_json',
            'FreText' => 'service_fre_text',
        );
        
        foreach ($freeStrList as $keyName => $itemName) {
            if (isset($data[$itemName])) {
                $virInfo[] = array(
                    'field_id' => 'svc_' . $data['busine_id'] . $keyName, 
                    'field_value' => $data[$itemName]
                );
                $virDetail['svc_' . $data['busine_id'] . $keyName] = $data[$itemName];
            }
        }
        
        // 查询年金额配置
        if (isset($data['busine_id_int']) && isset($data['year_amt'])) {
            $yearRow = $connection->createCommand()
                ->select('*')
                ->from('sal_service_type_info')
                ->where("type_id=:id and input_type='yearAmount'", array(':id' => $data['busine_id_int']))
                ->queryRow();
            
            if ($yearRow) {
                $virInfo[] = array(
                    'service_type_id' => $yearRow['id'],
                    'field_id' => 'svc_' . $yearRow['id_char'],
                    'field_value' => $data['year_amt']
                );
                $virDetail['svc_' . $yearRow['id_char']] = $data['year_amt'];
            }
        }
        
        // 处理服务项目详情（支持JSON格式、冒号分隔格式、分号分隔格式）
        if (isset($data['serviceTypeInfo']) && !empty($data['serviceTypeInfo']) && isset($data['busine_id_int'])) {
            $serviceText = $data['serviceTypeInfo'];
            
            // 判断是JSON格式还是分号分隔格式
            $serviceItems = json_decode($serviceText, true);
            
            if (is_array($serviceItems) && !empty($serviceItems)) {
                // JSON格式：新的派单系统API格式
                // [{"id":14,"type":3,"name":"老鼠","item1":1,"item2":""},...]
                foreach ($serviceItems as $item) {
                    // 只处理选中的项目
                    // type=3(复选框)且item1=1，或type=1,4(输入框/设备)且有值
                    $shouldInclude = false;
                    $fieldValue = '';
                    
                    switch ($item['type']) {
                        case 1: // 输入框
                            if (!empty($item['item1'])) {
                                $shouldInclude = true;
                                $fieldValue = $item['item1'];
                            }
                            break;
                        case 2: // 文本域
                            if (!empty($item['item2'])) {
                                $shouldInclude = true;
                                $fieldValue = $item['item2'];
                            }
                            break;
                        case 3: // 复选框
                            if ($item['item1'] == 1) {
                                $shouldInclude = true;
                                $fieldValue = 'Y';
                            }
                            break;
                        case 4: // 两个输入框（设备）
                            if (!empty($item['item1']) || !empty($item['item2'])) {
                                $shouldInclude = true;
                                $fieldValue = trim($item['item1'] . '|' . $item['item2'], '|');
                            }
                            break;
                    }
                    
                    if ($shouldInclude) {
                        // 查询CRM端的服务项目配置，通过name匹配
                        $row = $connection->createCommand()
                            ->select('*')
                            ->from('sal_service_type_info')
                            ->where('type_id=:type_id and name=:name', array(
                                ':type_id' => $data['busine_id_int'],
                                ':name' => $item['name']
                            ))
                            ->queryRow();
                        
                        if ($row) {
                            $virInfo[] = array(
                                'service_type_id' => $row['id'],
                                'field_id' => 'svc_' . $row['id_char'],
                                'field_value' => $fieldValue
                            );
                            $virDetail['svc_' . $row['id_char']] = $fieldValue;
                            Yii::log("服务项目详情匹配成功: name={$item['name']}, value={$fieldValue}", 'info', 'DataMigrationForm');
                        } else {
                            Yii::log("服务项目详情未匹配: name={$item['name']}, busine_id_int={$data['busine_id_int']}", 'warning', 'DataMigrationForm');
                        }
                    }
                }
            } elseif (strpos($serviceText, ':') !== false) {
                // 冒号分隔格式：新的派单系统导出格式
                // "老鼠:Y;蚁:N;蟑螂:Y;服务面积:500;鼠饵盒:10,含防尘罩"
                $items = explode(';', $serviceText);
                foreach ($items as $item) {
                    $item = trim($item);
                    if (empty($item)) continue;
                    
                    // 解析 "名称:值" 格式
                    $parts = explode(':', $item, 2);
                    if (count($parts) != 2) continue;
                    
                    $itemName = trim($parts[0]);
                    $itemValue = trim($parts[1]);
                    
                    if (empty($itemName)) continue;
                    
                    // 只处理有效值的项目
                    // Y = 复选框选中
                    // N = 复选框未选中（跳过）
                    // 其他值 = 输入框/文本域/设备（保留）
                    $shouldInclude = false;
                    $fieldValue = '';
                    
                    if ($itemValue === 'Y') {
                        // 复选框选中
                        $shouldInclude = true;
                        $fieldValue = 'Y';
                    } elseif ($itemValue === 'N') {
                        // 复选框未选中，跳过
                        continue;
                    } elseif (!empty($itemValue)) {
                        // 输入框/文本域/设备有值
                        $shouldInclude = true;
                        // 双输入框格式："10,含防尘罩" 转换为 "10|含防尘罩"
                        if (strpos($itemValue, ',') !== false) {
                            $fieldValue = str_replace(',', '|', $itemValue);
                        } else {
                            $fieldValue = $itemValue;
                        }
                    }
                    
                    if ($shouldInclude) {
                        // 查询CRM端的服务项目配置，通过name匹配
                        $row = $connection->createCommand()
                            ->select('*')
                            ->from('sal_service_type_info')
                            ->where('type_id=:type_id and name=:name', array(
                                ':type_id' => $data['busine_id_int'],
                                ':name' => $itemName
                            ))
                            ->queryRow();
                        
                        if ($row) {
                            $virInfo[] = array(
                                'service_type_id' => $row['id'],
                                'field_id' => 'svc_' . $row['id_char'],
                                'field_value' => $fieldValue
                            );
                            $virDetail['svc_' . $row['id_char']] = $fieldValue;
                            Yii::log("服务项目详情匹配成功(冒号格式): name={$itemName}, value={$fieldValue}", 'info', 'DataMigrationForm');
                        } else {
                            Yii::log("服务项目详情未匹配(冒号格式): name={$itemName}, busine_id_int={$data['busine_id_int']}", 'warning', 'DataMigrationForm');
                        }
                    }
                }
            } else {
                // 分号分隔格式：兼容旧的Excel导入格式
                // "蛇;蜈蚣;千足虫;老鼠"
                $serviceText = str_replace("'", "\'", $serviceText);
                $serviceText = "'" . str_replace(";", "','", $serviceText) . "'";
                
                $rows = $connection->createCommand()
                    ->select('*')
                    ->from('sal_service_type_info')
                    ->where("type_id=:id and input_type in ('checkbox','device','method') and name in ({$serviceText})", array(':id' => $data['busine_id_int']))
                    ->queryAll();
                
                if ($rows) {
                    $matchedNames = array();
                    foreach ($rows as $row) {
                        $virInfo[] = array(
                            'service_type_id' => $row['id'],
                            'field_id' => 'svc_' . $row['id_char'],
                            'field_value' => 'Y'
                        );
                        $virDetail['svc_' . $row['id_char']] = 'Y';
                        $matchedNames[] = $row['name'];
                    }
                    Yii::log("服务项目详情匹配成功(分号格式): " . implode(', ', $matchedNames), 'info', 'DataMigrationForm');
                } else {
                    Yii::log("服务项目详情全部未匹配(分号格式): busine_id_int={$data['busine_id_int']}, items={$serviceText}", 'warning', 'DataMigrationForm');
                }
            }
        }
        
        $data['virInfo'] = $virInfo;
        $data['detail_json'] = json_encode($virDetail, JSON_UNESCAPED_UNICODE);
        
        // 记录处理结果
        Yii::log("服务项目详情处理完成: 共生成 " . count($virInfo) . " 条详情记录", 'info', 'DataMigrationForm');
    }
    
    /**
     * 处理服务频次详情（参考 ImportVirForm::valUServiceJson）
     */
    protected function processServiceFrequency(&$data)
    {
        $u_service_title = isset($data['u_service_title']) ? $data['u_service_title'] : '';
        $u_service_json = array('title' => $u_service_title, 'list' => array());
        
        // 如果派单系统提供了服务频次详情（JSON格式）
        if (isset($data['u_service_info']) && !empty($data['u_service_info'])) {
            $freeJson = $data['u_service_info'];
            
            // 如果是字符串，解析为数组
            if (is_string($freeJson)) {
                $freeJson = json_decode($freeJson, true);
            }
            
            if (is_array($freeJson)) {
                foreach ($freeJson as $freeRow) {
                    if (isset($freeRow['month_cycle']) && is_numeric($freeRow['month_cycle']) && 
                        isset($freeRow['unit_price']) && is_numeric($freeRow['unit_price'])) {
                        $temp = array(
                            'month_cycle' => intval($freeRow['month_cycle']),
                            'week_cycle' => isset($freeRow['week_cycle']) ? intval($freeRow['week_cycle']) : null,
                            'day_cycle' => isset($freeRow['day_cycle']) ? intval($freeRow['day_cycle']) : null,
                            'unit_price' => floatval($freeRow['unit_price']),
                            'cycle_text' => isset($freeRow['cycle_text']) ? $freeRow['cycle_text'] : null,
                        );
                        $u_service_json['list'][] = $temp;
                    }
                }
            }
        }
        
        $data['u_service_json'] = $u_service_json;
        
        // 如果没有提供 service_fre_json，使用默认值
        if (!isset($data['service_fre_json']) || empty($data['service_fre_json'])) {
            $data['service_fre_json'] = json_encode(array(
                'fre_amt' => isset($data['year_amt']) ? $data['year_amt'] : 0,
                'fre_month' => isset($data['month_amt']) ? $data['month_amt'] : 0,
                'fre_sum' => isset($data['service_sum']) ? $data['service_sum'] : 0,
                'fre_type' => isset($data['service_fre_type']) ? $data['service_fre_type'] : 1,
                'fre_list' => array(),
            ), JSON_UNESCAPED_UNICODE);
        }
        
        // 如果没有提供 service_fre_text，使用默认值
        if (!isset($data['service_fre_text']) || empty($data['service_fre_text'])) {
            $serviceSum = isset($data['service_sum']) ? $data['service_sum'] : 0;
            $monthAmt = isset($data['month_amt']) ? $data['month_amt'] : 0;
            $data['service_fre_text'] = "每月服务{$serviceSum}次，月金额{$monthAmt}元";
        }
        
        // 设置可能缺失的默认值（确保不会报错）
        if (!isset($data['call_fre_amt'])) $data['call_fre_amt'] = 0;
        if (!isset($data['stop_month_amt'])) $data['stop_month_amt'] = null;
        if (!isset($data['stop_year_amt'])) $data['stop_year_amt'] = null;
        if (!isset($data['invoice_amount'])) $data['invoice_amount'] = null;
        if (!isset($data['technician_id_str'])) $data['technician_id_str'] = null;
        if (!isset($data['technician_id_text'])) $data['technician_id_text'] = null;
        if (!isset($data['external_source'])) $data['external_source'] = null;
    }
    
    /**
     * 根据员工编号获取员工ID（带缓存）
     */
    protected function getEmployeeIdByCode($code, $connection)
    {
        if (empty($code)) {
            return null;
        }
        
        // 检查缓存
        if (isset(self::$employeeCache[$code])) {
            return self::$employeeCache[$code];
        }
        
        // 查询数据库，将员工编号转换为员工ID
        // 注意：使用环境后缀区分UAT和生产环境
        $suffix = Yii::app()->params['envSuffix'];
        $empId = $connection->createCommand()
            ->select('id')
            ->from("hr{$suffix}.hr_employee")
            ->where('code=:code', array(':code' => $code))
            ->order('del_num asc, table_type asc, staff_status desc')
            ->queryScalar();
        
        // 缓存结果（包括null值，避免重复查询不存在的员工）
        self::$employeeCache[$code] = $empId;
        
        return $empId;
    }
    
    /**
     * 获取或验证城市代码（带缓存）
     * 注意：派单系统导出的应该已经是标准城市代码（如 SZ, BJ 等）
     */
    protected function getCityCodeByName($name, $connection)
    {
        if (empty($name)) {
            return null;
        }
        
        // 检查缓存
        if (isset(self::$cityCodeCache[$name])) {
            return self::$cityCodeCache[$name];
        }
        
        // 如果已经是城市代码格式（2-3个大写字母），直接返回并缓存
        if (preg_match('/^[A-Z]{2,3}$/', $name)) {
            self::$cityCodeCache[$name] = $name;
            return $name;
        }
        
        // 如果是小写，转为大写再检查
        $nameUpper = strtoupper($name);
        if (preg_match('/^[A-Z]{2,3}$/', $nameUpper)) {
            self::$cityCodeCache[$name] = $nameUpper;
            return $nameUpper;
        }
        
        // 如果仍然不是代码格式，查询数据库
        $suffix = Yii::app()->params['envSuffix'];
        
        // 优先使用 code 匹配
        $codeResult = $connection->createCommand()
            ->select('code')
            ->from("security{$suffix}.sec_city")
            ->where('code=:code', array(':code' => $nameUpper))
            ->queryScalar();
        if ($codeResult) {
            self::$cityCodeCache[$name] = $codeResult;
            return $codeResult;
        }
        
        // 最后尝试按名字查询
        $result = $connection->createCommand()
            ->select('code')
            ->from("security{$suffix}.sec_city")
            ->where('name=:name', array(':name' => $name))
            ->queryScalar();
        
        // 缓存结果（包括null）
        self::$cityCodeCache[$name] = $result;
        return $result;
    }
    
    /**
     * 根据业务大类名称获取ID（参考 ImportForm::valYewudalei）
     */
    protected function getYewudaleiIdByName($name, $connection)
    {
        if (empty($name)) {
            return null;
        }
        
        // 如果已经是数字ID，直接返回
        if (is_numeric($name)) {
            return intval($name);
        }
        
        // 检查缓存
        if (isset(self::$yewudaleiCache[$name])) {
            return self::$yewudaleiCache[$name];
        }
        
        // 从 sal_yewudalei 表查询ID
        $yewudaleiId = $connection->createCommand()
            ->select('id')
            ->from('sal_yewudalei')
            ->where('name=:name', array(':name' => $name))
            ->queryScalar();
        
        // 缓存结果
        self::$yewudaleiCache[$name] = $yewudaleiId;
        
        return $yewudaleiId;
    }
    
    /**
     * 根据主体公司名称获取ID（带缓存）
     */
    protected function getLbsMainIdByName($name, $connection)
    {
        if (empty($name)) {
            return null;
        }
        
        // 如果已经是数字ID，直接返回
        if (is_numeric($name)) {
            return intval($name);
        }
        
        // 检查缓存
        if (isset(self::$lbsMainCache[$name])) {
            return self::$lbsMainCache[$name];
        }
        
        // 从 sal_main_lbs 表查询ID
        $lbsMainId = $connection->createCommand()
            ->select('id')
            ->from('sal_main_lbs')
            ->where('name=:name', array(':name' => $name))
            ->queryScalar();
        
        // 缓存结果
        self::$lbsMainCache[$name] = $lbsMainId;
        
        return $lbsMainId;
    }
    
    /**
     * 创建主体公司（如果不存在）
     * @param string $entityCode 主体公司名称（如 IC-MKJ）
     * @param CDbConnection $connection 数据库连接
     * @param string $cityCode 城市代号（如 SZ），如果不提供则使用 'CN'（中国）
     * @return int 主体公司ID
     */
    protected function createLbsMain($entityCode, $connection, $cityCode = null)
    {
        if (empty($entityCode)) {
            throw new Exception('主体公司名称不能为空');
        }
        
        // 再次检查是否存在（避免并发创建重复）
        $existingId = $connection->createCommand()
            ->select('id')
            ->from('sal_main_lbs')
            ->where('name=:name', array(':name' => $entityCode))
            ->queryScalar();
        
        if ($existingId) {
            // 更新缓存
            self::$lbsMainCache[$entityCode] = $existingId;
            return $existingId;
        }
        
        // 创建新的主体公司记录
        $currentUser = DataMigrationHelper::getCurrentUserId($this->username);
        
        // 如果未提供城市代号，使用默认值 'CN'（中国/全国）
        if (empty($cityCode)) {
            $cityCode = 'CN';
        }
        
        $connection->createCommand()->insert('sal_main_lbs', array(
            'name' => $entityCode, // 主体公司名称
            'city' => $cityCode, // 使用传入的城市代号或默认值
            'show_type' => 1, // 默认：本地
            'z_display' => 1, // 默认：显示
            'lcu' => $currentUser,
        ));
        
        $newId = $connection->getLastInsertID();
        
        // 保存到缓存
        self::$lbsMainCache[$entityCode] = $newId;
        
        Yii::log('自动创建主体公司：' . $entityCode . ' (城市:' . $cityCode . ', ID:' . $newId . ')', 'info', 'DataMigration');
        
        return $newId;
    }
    
    /**
     * 派单系统到CRM系统的服务项目名称映射表
     * 用于兼容两个系统中服务项目名称不一致的情况
     */
    private static $serviceNameMapping = array(
        // 派单系统 => CRM系统
        '租机服务' => '租赁机器',
        '蝇灯服务' => '灭蝇灯服务',
        '鼠臭跟进' => '臭虫跟进',
        '租机服务 (水机)' => '洁净水租机',
        '厨房油烟清洁服务' => '油烟清洗',
        '灭虫（一次性服务）' => '灭虫（一次性）',
        '洁净（一次性服务）' => '洁净（一次性）',
    );
    
    /**
     * 根据服务项目名称获取信息（带缓存）
     */
    protected function getServiceTypeByName($name, $connection)
    {
        if (empty($name)) {
            return null;
        }
        
        // 检查缓存
        if (isset(self::$serviceTypeCache[$name])) {
            return self::$serviceTypeCache[$name];
        }
        
        // 从 sal_service_type 表查询信息
        $row = $connection->createCommand()
            ->select('id, id_char, service_type, name')
            ->from('sal_service_type')
            ->where('name=:name', array(':name' => $name))
            ->queryRow();
        
        // ✅ 如果直接查询失败，尝试使用映射表转换后再查询
        if (!$row && isset(self::$serviceNameMapping[$name])) {
            $mappedName = self::$serviceNameMapping[$name];
            Yii::log("服务项目名称映射：'{$name}' => '{$mappedName}'", 'info', 'DataMigration');
            
            $row = $connection->createCommand()
                ->select('id, id_char, service_type, name')
                ->from('sal_service_type')
                ->where('name=:name', array(':name' => $mappedName))
                ->queryRow();
        }
        
        // 缓存结果（使用原始名称作为key）
        self::$serviceTypeCache[$name] = $row;
        
        return $row;
    }
    
    /**
     * 根据行业类别名称获取信息（带缓存）
     */
    protected function getCustClassByName($name, $connection)
    {
        if (empty($name)) {
            return null;
        }
        
        // 如果已经是数字ID，直接返回
        if (is_numeric($name)) {
            return array('id' => intval($name), 'nature_id' => null);
        }
        
        // 检查缓存
        if (isset(self::$custClassCache[$name])) {
            return self::$custClassCache[$name];
        }
        
        // 从 swo_nature_type 表查询信息
        $suffix = Yii::app()->params['envSuffix'];
        $row = $connection->createCommand()
            ->select('a.id, a.nature_id')
            ->from("swoper{$suffix}.swo_nature_type a")
            ->where('a.name=:name', array(':name' => $name))
            ->order('z_display desc, id desc')
            ->queryRow();
        
        // 缓存结果
        self::$custClassCache[$name] = $row;
        
        return $row;
    }
    
    /**
     * 插入客户数据（参考 ImportClientForm::saveOneData）
     */
    protected function insertClientData($data)
    {
        $connection = Yii::app()->db;
        $username = DataMigrationHelper::getCurrentUserId($this->username);
        $req_dt = date("Y-m-d H:i:s");
        
        // 检查客户重复性（需要同时考虑客户编号和客户名称，都要结合业务大类）
        // 同一个客户在不同业务大类下可以存在，但在同一业务大类下不能重复
        $clueCode = isset($data['clue_code']) ? $data['clue_code'] : '';
        $custName = isset($data['cust_name']) ? $data['cust_name'] : '';
        $yewudalei = isset($data['yewudalei']) ? $data['yewudalei'] : '';
        
        // 1. 检查客户编号 + 业务大类
        if (!empty($clueCode)) {
            $whereStr = 'clue_code=:clue_code';
            $params = array(':clue_code' => $clueCode);
            
            // 如果有业务大类，加入业务大类条件
            if (!empty($yewudalei)) {
                $whereStr .= ' AND yewudalei=:yewudalei';
                $params[':yewudalei'] = $yewudalei;
            }
            
            $existingClue = $connection->createCommand()
                ->select('clue_code, cust_name, yewudalei')
                ->from('sal_clue')
                ->where($whereStr, $params)
                ->queryRow();
            
            if ($existingClue) {
                throw new Exception("客户编号在该业务大类下已存在（客户编号：{$existingClue['clue_code']}，客户名称：{$existingClue['cust_name']}，业务大类：{$existingClue['yewudalei']}）");
            }
        }
        
        // 2. 检查客户名称 + 业务大类
        if (!empty($custName)) {
            $whereStr = 'cust_name=:cust_name';
            $params = array(':cust_name' => $custName);
            
            // 如果有业务大类，加入业务大类条件
            if (!empty($yewudalei)) {
                $whereStr .= ' AND yewudalei=:yewudalei';
                $params[':yewudalei'] = $yewudalei;
            }
            
            $existingClue = $connection->createCommand()
                ->select('clue_code, cust_name, yewudalei')
                ->from('sal_clue')
                ->where($whereStr, $params)
                ->queryRow();
            
            if ($existingClue) {
                throw new Exception("客户名称在该业务大类下已存在（客户编号：{$existingClue['clue_code']}，客户名称：{$existingClue['cust_name']}，业务大类：{$existingClue['yewudalei']}）");
            }
        }
        
        // 3. 插入客户主表 sal_clue（严格按照 ImportClientForm 的字段列表）
        $saveKey = array(
            'clue_type', 'service_type', 'cust_name', 'full_name', 'clue_code', 'abbr_code', 'entry_date', 
            'rec_employee_id', 'yewudalei', 'group_bool', 'cust_vip', 'cust_class', 'cust_class_group', 
            'city', 'address', 'district', 'street', 'latitude', 'longitude',
            'u_id', 'ka_id', 'u_group_id', 'cust_person', 'cust_tel', 'cust_email', 
            'cust_person_role', 'cust_address', 'area', 'clue_remark',
        );
        $saveList = array();
        foreach ($saveKey as $key) {
            if (isset($data[$key])) {
                $saveList[$key] = $data[$key];
            }
        }
        if (isset($saveList["area"]) && empty($saveList["area"])) {
            $saveList["area"] = null;
        }
        $saveList["report_id"] = $this->id;
        // ✅ 派单导入默认是客户（2），但如果派单明确传了table_type，则使用派单的值
        $saveList["table_type"] = isset($data["table_type"]) ? $data["table_type"] : 2;
        // 使用预处理后的状态值（从派单系统读取），如果没有则默认为服务中
        if (!isset($saveList["clue_status"])) {
            $saveList["clue_status"] = 1;  // 默认：服务中
        }
        $saveList["lcu"] = $username;
        
        $connection->createCommand()->insert("sal_clue", $saveList);
        $clue_id = $connection->getLastInsertID();
        
        // 2. 插入客户历史记录 sal_clue_history
        $connection->createCommand()->insert("sal_clue_history", array(
            "table_id" => $clue_id,
            "table_type" => 1,
            "history_type" => 1,
            "history_html" => "<span>派单数据导入，导入id：{$this->id}</span>",
            "lcu" => $username,
        ));
        
        // 3. 插入客户城市关联 sal_clue_u_area
        $connection->createCommand()->insert("sal_clue_u_area", array(
            "clue_id" => $clue_id,
            "city_code" => $saveList['city'],
            "city_type" => 1,
            "u_id" => !empty($data['u_area_id']) ? $data['u_area_id'] : null,
            "lcu" => $username,
            "lcd" => $req_dt,
        ));
        
        // 4. 插入客户员工关联 sal_clue_u_staff
        $connection->createCommand()->insert("sal_clue_u_staff", array(
            "clue_id" => $clue_id,
            "employee_id" => $saveList['rec_employee_id'],
            "employee_type" => 1,
            "u_id" => !empty($data['u_staff_id']) ? $data['u_staff_id'] : null,
            "lcu" => $username,
            "lcd" => $req_dt,
        ));
        
        // 5. 如果有联系人信息，插入联系人 sal_clue_person
        if (!empty($saveList['cust_person']) && !empty($saveList['cust_tel'])) {
            $connection->createCommand()->insert("sal_clue_person", array(
                "clue_id" => $clue_id,
                "clue_store_id" => 0,
                "person_code" => isset($data['person_code']) ? $data['person_code'] : null,
                "cust_person" => $saveList['cust_person'],
                "cust_tel" => $saveList['cust_tel'],
                "cust_email" => isset($saveList['cust_email']) ? $saveList['cust_email'] : null,
                "cust_person_role" => isset($saveList['cust_person_role']) ? $saveList['cust_person_role'] : null,
                "u_id" => !empty($data['u_person_id']) ? $data['u_person_id'] : null,
                "lcu" => $username,
                "lcd" => $req_dt,
            ));
        }
        
        // 6. 处理其它销售（u_staff_list）
        if (!empty($data['u_staff_list'])) {
            $staffIds = explode(';', $data['u_staff_list']);
            foreach ($staffIds as $staffId) {
                if (!empty($staffId)) {
                    $connection->createCommand()->insert("sal_clue_u_staff", array(
                        "clue_id" => $clue_id,
                        "employee_id" => $staffId,
                        "employee_type" => 2, // 其它销售
                        "u_id" => null,
                        "lcu" => $username,
                        "lcd" => $req_dt,
                    ));
                }
            }
        }
        
        // 7. 处理其它城市（u_area_list）
        if (!empty($data['u_area_list'])) {
            $cityCodes = explode(';', $data['u_area_list']);
            foreach ($cityCodes as $cityCode) {
                if (!empty($cityCode) && $cityCode != $saveList['city']) {
                    $connection->createCommand()->insert("sal_clue_u_area", array(
                        "clue_id" => $clue_id,
                        "city_code" => $cityCode,
                        "city_type" => 2, // 其它城市
                        "u_id" => null,
                        "lcu" => $username,
                        "lcd" => $req_dt,
                    ));
                }
            }
        }
        
        Yii::log('客户数据导入成功：clue_id=' . $clue_id . ', u_id=' . (isset($data['u_id']) ? $data['u_id'] : 'null'), 'info', 'DataMigration');
    }
    
    /**
     * 自动创建客户（参考 ImportClientStoreForm::computeClueID）
     * 
     * 数据映射关系：
     * 派单 lbs_ka_project_management => CRM sal_clue
     * - project_code => clue_code (客户编号) ✅
     * - project_name => cust_name (客户名称，从门店名称派生)
     * 
     * 派单 lbs_company_customer => CRM sal_clue_store
     * - ka_id => lbs_ka_project_management.project_id
     * - 通过 project_code 关联到 CRM sal_clue (clue_code = project_code)
     * - customer_code => store_code (门店编号)
     * - name_zh => store_name (门店名称)
     */
    protected function autoCreateClueForStore($storeData, $connection, $username, $req_dt)
    {
        // 加载拼音扩展（使用 include_once 避免重复加载）
        $phpExcelPath = Yii::getPathOfAlias('ext.pinyin');
        include_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Autoloader.php');
        $pinyin = new Pinyin(); // 默认
        
        // 准备客户数据
        $clueData = array(
            'clue_type' => $storeData['clue_type'],
            'service_type' => isset($storeData['service_type']) ? $storeData['service_type'] : null,
            'cust_name' => $storeData['store_name'],
            'full_name' => !empty($storeData['store_full_name']) ? $storeData['store_full_name'] : $storeData['store_name'],
            'entry_date' => isset($storeData['entry_date']) ? $storeData['entry_date'] : $req_dt,
            'rec_employee_id' => $storeData['create_staff'],
            'yewudalei' => isset($storeData['yewudalei']) ? $storeData['yewudalei'] : null,
            'group_bool' => isset($storeData['group_bool']) ? $storeData['group_bool'] : 'N',
            'cust_vip' => isset($storeData['cust_vip']) ? $storeData['cust_vip'] : null,
            'cust_class' => isset($storeData['cust_class']) ? $storeData['cust_class'] : null,
            'city' => $storeData['city'],
            'address' => isset($storeData['address']) ? $storeData['address'] : null,
            'district' => isset($storeData['district']) ? $storeData['district'] : null,
            'latitude' => isset($storeData['latitude']) ? $storeData['latitude'] : null,
            'longitude' => isset($storeData['longitude']) ? $storeData['longitude'] : null,
            'cust_person' => isset($storeData['cust_person']) ? $storeData['cust_person'] : null,
            'cust_tel' => isset($storeData['cust_tel']) ? $storeData['cust_tel'] : null,
            'cust_email' => isset($storeData['cust_email']) ? $storeData['cust_email'] : null,
            'cust_person_role' => isset($storeData['cust_person_role']) ? $storeData['cust_person_role'] : null,
            'cust_address' => isset($storeData['address']) ? $storeData['address'] : null,
            'clue_remark' => '门店导入自动生成',
            'report_id' => isset($storeData['report_id']) ? $storeData['report_id'] : null,
            // ✅ 派单导入的都是客户（2），不是线索（1）
            // 但如果派单明确传了table_type，则使用派单的值
            'table_type' => isset($storeData['table_type']) ? $storeData['table_type'] : 2,
            'lcu' => $username,
            'luu' => $username,
        );
        
        // 根据门店状态设置客户状态
        // 门店状态：0=未生效，1=未服务，2=服务中，3=已停止，4=其他
        // 客户状态：0=未生效，1=服务中，2=已停止
        $storeStatus = isset($storeData['store_status']) ? $storeData['store_status'] : 2;
        if ($storeStatus == 3) {
            $clueData['clue_status'] = 2;  // 已停止
        } elseif ($storeStatus == 0) {
            $clueData['clue_status'] = 0;  // 未生效
        } elseif ($storeStatus == 4) {
            $clueData['clue_status'] = 3;  // 其他（映射到客户状态的"未知"）
        } else {
            $clueData['clue_status'] = 1;  // 服务中（包括未服务）
        }
        
        // 使用派单提供的客户编号 (project_code)，如果没有则自动生成
        if (!empty($storeData['clue_code'])) {
            $clueData['clue_code'] = $storeData['clue_code'];  // project_code => clue_code
            // 生成简称编码
            $full_name = $clueData['full_name'];
            $computeList = CGetName::computeClueCode($pinyin, $full_name);
            $clueData['abbr_code'] = $computeList['abbr_code'];
        } else {
            // 自动生成客户编号（参考ImportClientStoreForm）
            $full_name = $clueData['full_name'];
            $computeList = CGetName::computeClueCode($pinyin, $full_name);
            $clueData['clue_code'] = $computeList['clue_code'];
            $clueData['abbr_code'] = $computeList['abbr_code'];
        }
        
        // 插入客户记录
        $connection->createCommand()->insert('sal_clue', $clueData);
        $clue_id = $connection->getLastInsertID();
        
        // 插入客户历史记录（参考 ImportClientStoreForm line 132-138）
        $connection->createCommand()->insert('sal_clue_history', array(
            'table_id' => $clue_id,
            'table_type' => 1,
            'history_type' => 1,
            'history_html' => '<span>派单数据导入（门店自动创建客户），导入id：' . $clueData['report_id'] . '</span>',
            'lcu' => $username,
        ));
        
        // 插入客户城市关联（参考 ImportClientStoreForm line 139-146）
        $connection->createCommand()->insert('sal_clue_u_area', array(
            'clue_id' => $clue_id,
            'city_code' => $clueData['city'],
            'city_type' => 1,
            'u_id' => null,
            'lcu' => $username,
            'lcd' => $req_dt,
        ));
        
        // 插入客户销售关联（参考 ImportClientStoreForm line 147-154）
        $connection->createCommand()->insert('sal_clue_u_staff', array(
            'clue_id' => $clue_id,
            'employee_id' => $clueData['rec_employee_id'],
            'employee_type' => 1,
            'u_id' => null,
            'lcu' => $username,
            'lcd' => $req_dt,
        ));
        
        // 插入客户联系人（参考 ImportClientStoreForm line 155-170）
        if (!empty($clueData['cust_person']) && !empty($clueData['cust_tel'])) {
            $connection->createCommand()->insert('sal_clue_person', array(
                'clue_id' => $clue_id,
                'clue_store_id' => 0,
                'cust_person' => $clueData['cust_person'],
                'cust_tel' => $clueData['cust_tel'],
                'cust_email' => isset($clueData['cust_email']) ? $clueData['cust_email'] : null,
                'cust_person_role' => isset($clueData['cust_person_role']) ? $clueData['cust_person_role'] : null,
                'u_id' => null,
                'u_group_id' => null,
                'lcu' => $username,
                'lcd' => $req_dt,
            ));
            $cust_id = $connection->getLastInsertID();
            $connection->createCommand()->update('sal_clue_person', array(
                'person_code' => ClientPersonForm::computeCodeX($clue_id, 0, $cust_id),
            ), 'id=:id', array(':id' => $cust_id));
        }
        
        Yii::log('自动创建客户成功：clue_id=' . $clue_id . ', clue_code=' . $clueData['clue_code'] . ', cust_name=' . $clueData['cust_name'], 'info', 'DataMigration');
        
        return $clue_id;
    }
    
    /**
     * 插入门店数据（完全参考 ImportClientStoreForm::saveOneData）
     * 
     * 数据流向：
     * 1. 派单 lbs_ka_project_management.project_code => CRM sal_clue.clue_code (客户编号) ✅
     * 2. 先根据 clue_code (=project_code) 查找或创建客户记录
     * 3. 派单 lbs_company_customer => CRM sal_clue_store (门店，关联到客户)
     */
    protected function insertStoreData($data)
    {
        $connection = Yii::app()->db;
        $username = DataMigrationHelper::getCurrentUserId($this->username);
        $req_dt = date("Y-m-d H:i:s");
        
        // 1. 确保客户存在 (根据 project_code 查找)
        $clue_id = null;
        if (!empty($data['clue_code'])) {
            // 先查找是否已存在该客户编号的客户 (clue_code = project_code)
            $clueRow = $connection->createCommand()
                ->select('id')
                ->from('sal_clue')
                ->where('clue_code=:code', array(':code' => $data['clue_code']))
                ->queryRow();
            if ($clueRow) {
                $clue_id = $clueRow['id'];
            }
        }
        
        // 如果没有找到客户，自动创建（使用 project_code 作为 clue_code）
        if (empty($clue_id)) {
            $clue_id = $this->autoCreateClueForStore($data, $connection, $username, $req_dt);
        }
        $data['clue_id'] = $clue_id;
        
        // 2. 处理开票信息（参考 ImportClientStoreForm::computeInvoiceID）
        if (!empty($data['clue_id']) && !empty($data['invoice_header'])) {
            // 安全获取门店名称
            $storeName = isset($data['store_name']) && !empty($data['store_name']) ? $data['store_name'] : 'store';
            $invoice_name = $storeName . '_sys_' . time();
            $invoice_type = 2; // 默认专票
            if (empty($data['invoice_address']) || empty($data['tax_id']) || 
                empty($data['invoice_number']) || empty($data['invoice_user'])) {
                $invoice_type = 1; // 普票
            }
            
            $connection->createCommand()->insert("sal_clue_invoice", array(
                "clue_id" => $data['clue_id'],
                "clue_type" => $data['clue_type'],
                "invoice_name" => $invoice_name,
                "city" => $data['city'],
                "invoice_type" => $invoice_type,
                "invoice_header" => $data['invoice_header'],
                "tax_id" => isset($data['tax_id']) ? $data['tax_id'] : null,
                "invoice_address" => isset($data['invoice_address']) ? $data['invoice_address'] : null,
                "invoice_number" => isset($data['invoice_number']) ? $data['invoice_number'] : null,
                "invoice_user" => isset($data['invoice_user']) ? $data['invoice_user'] : null,
                "invoice_rmk" => isset($data['invoice_rmk']) ? $data['invoice_rmk'] : null,
                "lcu" => $username,
                "lcd" => $req_dt,
            ));
            $data['invoice_id'] = $connection->getLastInsertID();
        }
        
        // 3. 生成门店编号（参考 ImportClientStoreForm::computeStoreCode）
        if (empty($data['store_code'])) {
            $row = $connection->createCommand()
                ->select('count(*) as sum')
                ->from('sal_clue_store')
                ->where('clue_id=:clue_id', array(':clue_id' => $data['clue_id']))
                ->queryRow();
            $num = $row && !empty($row['sum']) ? $row['sum'] : 0;
            $charNum = floor($num / 1000) + 65;
            $num = floor($num % 1000);
            $num = '' . (1000 + $num);
            $num = mb_substr($num, 1);
            $store_code = $data['clue_code'] . '-' . chr($charNum) . $num;
            $data['store_code'] = $store_code;
        }
        
        // 4. 插入门店主表 sal_clue_store（完全按照 ImportClientStoreForm 的 saveKey）
        $saveKey = array(
            'clue_id', 'clue_type', 'store_code', 'store_name', 'store_full_name', 'create_staff', 
            'yewudalei', 'cust_class_group', 'cust_class', 'city', 'office_id', 'address', 'district',
            'invoice_id', 'latitude', 'longitude', 'u_id', 'cust_person', 'cust_tel', 'cust_email', 
            'cust_person_role', 'area', 'store_remark'
        );
        $saveList = array();
        foreach ($saveKey as $key) {
            if (key_exists($key, $data)) {
                $saveList[$key] = $data[$key];
            }
        }
        if (key_exists("area", $saveList) && empty($saveList["area"])) {
            $saveList["area"] = null;
        }
        $saveList["report_id"] = $this->id;
        // 使用预处理后的状态值（从派单系统读取），如果没有则默认为服务中
        if (!isset($saveList["store_status"])) {
            $saveList["store_status"] = 2;  // 默认：服务中
        }
        $saveList["lcu"] = $username;
        
        $connection->createCommand()->insert("sal_clue_store", $saveList);
        $clue_store_id = $connection->getLastInsertID();
        
        // 5. 插入门店历史记录
        $connection->createCommand()->insert("sal_clue_history", array(
            "table_id" => $clue_store_id,
            "table_type" => 2,
            "history_type" => 1,
            "history_html" => "<span>派单数据导入，导入id：{$this->id}</span>",
            "lcu" => $username,
        ));
        
        // 6. 如果有联系人信息，插入联系人（完全参考 ImportClientStoreForm）
        if (!empty($saveList['cust_person']) && !empty($saveList['cust_tel'])) {
            $connection->createCommand()->insert("sal_clue_person", array(
                "clue_id" => $data['clue_id'],
                "clue_store_id" => $clue_store_id,
                "person_code" => isset($data['person_code']) ? $data['person_code'] : null,
                "person_pws" => empty($data['u_id']) ? null : 1, // 如果有派单ID，设置为1
                "cust_person" => $saveList['cust_person'],
                "cust_tel" => $saveList['cust_tel'],
                "cust_email" => isset($saveList['cust_email']) ? $saveList['cust_email'] : null,
                "cust_person_role" => isset($saveList['cust_person_role']) ? $saveList['cust_person_role'] : null,
                "u_id" => !empty($data['u_person_id']) ? $data['u_person_id'] : null,
                "u_group_id" => !empty($data['u_group_id']) ? $data['u_group_id'] : null,
                "lcu" => $username,
                "lcd" => $req_dt,
            ));
            $cust_id = $connection->getLastInsertID();
            
            // 如果没有联系人编号，自动生成（参考 ClientPersonForm::computeCodeX）
            if (empty($data['person_code'])) {
                $connection->createCommand()->update("sal_clue_person", array(
                    "person_code" => ClientPersonForm::computeCodeX($data['clue_id'], $clue_store_id, $cust_id),
                ), "id=:id", array(":id" => $cust_id));
            }
        }
        
        // 7. 如果有其它联系人列表，插入（参考 ImportClientStoreForm）
        if (!empty($data['uPersonData'])) {
            foreach ($data['uPersonData'] as $uPerson) {
                $uPerson['clue_id'] = $data['clue_id'];
                $uPerson['clue_store_id'] = $clue_store_id;
                $uPerson['person_pws'] = empty($uPerson['u_id']) ? null : 1;
                $uPerson['lcu'] = $username;
                $uPerson['lcd'] = $req_dt;
                $connection->createCommand()->insert("sal_clue_person", $uPerson);
                $cust_id = $connection->getLastInsertID();
                
                // 如果没有联系人编号，自动生成
                if (empty($uPerson['person_code'])) {
                    $connection->createCommand()->update("sal_clue_person", array(
                        "person_code" => ClientPersonForm::computeCodeX($data['clue_id'], $clue_store_id, $cust_id),
                    ), "id=:id", array(":id" => $cust_id));
                }
            }
        }
        
        Yii::log('门店数据导入成功：store_id=' . $clue_store_id . ', u_id=' . (isset($data['u_id']) ? $data['u_id'] : 'null'), 'info', 'DataMigration');
    }
    
    /**
     * 插入主合约数据（参考 ImportContForm::saveOneData）
     */
    protected function insertContractData($data)
    {
        $connection = Yii::app()->db;
        $username = DataMigrationHelper::getCurrentUserId($this->username);
        
        // 1. 查找客户
        $clueRow = $connection->createCommand()
            ->select('*')
            ->from('sal_clue')
            ->where('clue_code=:code', array(':code' => $data['clue_code']))
            ->queryRow();
        
        if (!$clueRow) {
            throw new Exception('主合约导入失败：找不到对应的客户（clue_code=' . $data['clue_code'] . '）');
        }
        
        // 2. 初始化拜访类型和对象
        $visit_type = $connection->createCommand()
            ->select('id')
            ->from('sal_visit_type')
            ->order('id asc')
            ->queryScalar();
        
        $visit_obj_row = $connection->createCommand()
            ->select('id, name')
            ->from('sal_visit_obj')
            ->where("rpt_type='DEAL'")
            ->queryRow();
        
        // 3. 插入销售回访记录 sal_clue_service
        $connection->createCommand()->insert('sal_clue_service', array(
            'clue_id' => $clueRow['id'],
            'clue_type' => $clueRow['clue_type'],
            'visit_type' => $visit_type,
            'visit_obj' => $visit_obj_row['id'],
            'visit_obj_text' => $visit_obj_row['name'],
            'create_staff' => $data['sales_id'],
            'busine_id' => $data['busine_id'],
            'busine_id_text' => $data['busine_id_text'],
            'sign_odds' => 100,
            'lbs_main' => $data['lbs_main'],
            'predict_date' => $data['sign_date'],
            'predict_amt' => $data['total_amt'],
            'total_amt' => $data['total_amt'],
            'total_num' => 1,
            'service_status' => $data['cont_status'],
            'lcu' => $username,
            'report_id' => $this->id,
        ));
        $clue_service_id = $connection->getLastInsertID();
        
        // 4. 计算合约月数
        $cont_month_len = DataMigrationHelper::computeMonthLen($data['cont_start_dt'], $data['cont_end_dt']);
        
        // 5. 插入主合约 sal_contract（完全按照 ImportContForm::saveOneData，直接使用$data字段）
        $contArr = array(
            'clue_id' => $clueRow['id'],
            'clue_type' => $clueRow['clue_type'],
            'clue_service_id' => $clue_service_id,
            'city' => $clueRow['city'],
            'cont_code' => $data['cont_code'],
            'sales_id' => $data['sales_id'],
            'lbs_main' => $data['lbs_main'],
            'predict_amt' => $data['total_amt'],
            'store_sum' => $data['store_sum'],
            'cont_type' => 1,
            'sign_type' => 1,
            'total_sum' => $data['total_sum'],
            'total_amt' => $data['total_amt'],
            'cont_status' => $data['cont_status'],
            'stop_date' => $data['stop_date'],
            'surplus_num' => $data['surplus_num'],
            'surplus_amt' => $data['surplus_amt'],
            'cont_start_dt' => $data['cont_start_dt'],
            'cont_end_dt' => $data['cont_end_dt'],
            'cont_month_len' => $cont_month_len,
            'sign_date' => $data['sign_date'],
            'area_bool' => 'N',
            'group_bool' => 'N',
            'prioritize_service' => $data['prioritize_service'],
            'service_timer' => $data['service_timer'],
            'pay_type' => $data['pay_type'],
            'pay_week' => $data['pay_week'],
            'pay_month' => $data['pay_month'],
            'pay_start' => $data['pay_start'],
            'deposit_need' => $data['deposit_need'],
            'deposit_amt' => $data['deposit_amt'],
            'deposit_rmk' => $data['deposit_rmk'],
            'fee_type' => $data['fee_type'],
            'settle_type' => $data['settle_type'],
            'bill_day' => $data['bill_day'],
            'bill_bool' => $data['bill_bool'],
            'receivable_day' => $data['receivable_day'],
            'yewudalei' => $data['yewudalei'],
            'busine_id' => $data['busine_id'],
            'busine_id_text' => $data['busine_id_text'],
            'report_id' => $this->id,
            'lcu' => $username,
        );
        $connection->createCommand()->insert('sal_contract', $contArr);
        $cont_id = $connection->getLastInsertID();
        
        // 6. 插入主合约变更记录 sal_contpro
        $contArr['cont_id'] = $cont_id;
        $contArr['pro_code'] = 'PDL-' . $data['cont_code'];
        $contArr['pro_type'] = DataMigrationHelper::proTypeByStatus($data['cont_status']);
        $contArr['pro_date'] = $data['sign_date'];
        $contArr['pro_remark'] = "导入虚拟合约自动生成\n导入id：{$this->id}";
        $contArr['pro_status'] = 30;
        $contArr['pro_change'] = $data['cont_status'] == 30 ? $data['total_amt'] : $data['surplus_amt'];
        $connection->createCommand()->insert('sal_contpro', $contArr);
        
        // 7. 更新客户状态
        $connection->createCommand()->update('sal_clue', array(
            'clue_status' => DataMigrationHelper::getClientStatusByClueID($clueRow['id']),
        ), 'id=:id', array(':id' => $clueRow['id']));
        
        Yii::log('主合约数据导入成功：cont_id=' . $cont_id . ', u_id=' . (isset($data['u_id']) ? $data['u_id'] : 'null'), 'info', 'DataMigration');
    }
    
    /**
     * 插入虚拟合约数据（参考 ImportVirForm::saveOneData）
     */
    protected function insertVirtualContractData($data)
    {
        $connection = Yii::app()->db;
        $username = DataMigrationHelper::getCurrentUserId($this->username);
        
        // 1. 查找门店
        $storeRow = $connection->createCommand()
            ->select('*')
            ->from('sal_clue_store')
            ->where('store_code=:code', array(':code' => $data['store_code']))
            ->queryRow();
        
        if (!$storeRow) {
            throw new Exception('虚拟合约导入失败：找不到对应的门店（store_code=' . $data['store_code'] . '）');
        }
        
        // 验证门店关联的客户是否存在
        if (empty($storeRow['clue_id'])) {
            throw new Exception('虚拟合约导入失败：门店未关联客户（store_code=' . $data['store_code'] . ', clue_id为空）');
        }
        
        $clueExists = $connection->createCommand()
            ->select('count(*)')
            ->from('sal_clue')
            ->where('id=:id', array(':id' => $storeRow['clue_id']))
            ->queryScalar();
        
        if (!$clueExists) {
            Yii::log('门店关联的客户不存在：store_code=' . $data['store_code'] . ', clue_id=' . $storeRow['clue_id'], 'error', 'DataMigrationForm');
            throw new Exception('虚拟合约导入失败：门店关联的客户不存在（store_code=' . $data['store_code'] . ', clue_id=' . $storeRow['clue_id'] . '）');
        }
        
        // 2. 计算合约月数
        $cont_month_len = DataMigrationHelper::computeMonthLen($data['cont_start_dt'], $data['cont_end_dt']);
        
        // 3. 如果没有主合约，需要创建
        $cont_id = isset($data['cont_id']) ? $data['cont_id'] : null;
        $clue_service_id = isset($data['clue_service_id']) ? $data['clue_service_id'] : null;
        $pro_id = isset($data['pro_id']) ? $data['pro_id'] : null;
        
        if (empty($cont_id)) {
            $result = $this->createContractForVirtual($data, $storeRow, $cont_month_len);
            $cont_id = $result['cont_id'];
            $clue_service_id = $result['clue_service_id'];
            $pro_id = $result['pro_id'];
        }
        
        // 4. 生成或更新SSE关联数据
        $sse_id = $this->computeContSSE($cont_id, $pro_id, $clue_service_id, $storeRow['clue_id'], $storeRow['id'], $data);
        
        // 5. 补充数据
        $data['create_staff'] = $data['sales_id'];
        $data['report_id'] = $this->id;
        
        // 6. 插入虚拟合约主表 sal_contract_virtual（完全按照 ImportVirForm::saveOneData 的 saveKey）
        // ✅ 先检查是否已存在相同 u_id 的虚拟合约，如果存在则删除旧数据
        if (!empty($data['u_id'])) {
            $existingVirRow = $connection->createCommand()
                ->select('id')
                ->from('sal_contract_virtual')
                ->where('u_id=:u_id', array(':u_id' => $data['u_id']))
                ->queryRow();
            
            if ($existingVirRow) {
                $oldVirId = $existingVirRow['id'];
                Yii::log('发现已存在的虚拟合约（vir_id=' . $oldVirId . ', u_id=' . $data['u_id'] . '），将删除旧数据后重新导入', 'info', 'DataMigration');
                
                // 删除旧的虚拟合约相关数据
                $connection->createCommand()->delete('sal_contract_vir_info', 'virtual_id=:virtual_id', array(':virtual_id' => $oldVirId));
                $connection->createCommand()->delete('sal_contract_vir_staff', 'vir_id=:vir_id', array(':vir_id' => $oldVirId));
                $connection->createCommand()->delete('sal_contract_vir_week', 'vir_id=:vir_id', array(':vir_id' => $oldVirId));
                $connection->createCommand()->delete('sal_contpro_virtual', 'vir_id=:vir_id', array(':vir_id' => $oldVirId));
                $connection->createCommand()->delete('sal_contract_virtual', 'id=:id', array(':id' => $oldVirId));
            }
        }
        
        $saveKey = array(
            'cont_id', 'sse_id', 'clue_id', 'clue_type', 'clue_service_id', 'clue_store_id', 'vir_code', 'vir_status',
            'city', 'office_id', 'busine_id', 'service_type', 'receivable_day', 'bill_bool', 'bill_day', 'settle_type',
            'fee_type', 'deposit_rmk', 'deposit_amt', 'deposit_need', 'pay_start', 'pay_month', 'pay_type', 'pay_week',
            'service_timer', 'prioritize_service', 'sign_date', 'yewudalei', 'lbs_main', 'service_main', 'busine_id_text',
            'sales_id', 'create_staff', 'month_amt', 'year_amt', 'service_sum', 'surplus_num', 'surplus_amt',
            'call_fre_amt', 'service_fre_amt', 'service_fre_sum', 'service_fre_type', 'service_fre_json', 'service_fre_text',
            'cont_start_dt', 'cont_end_dt', 'cont_month_len', 'fast_date', 'first_date', 'need_install', 'amt_install',
            'other_sales_id', 'other_yewudalei', 'first_tech_id', 'technician_id_str', 'technician_id_text', 'external_source',
            // 'stop_set_id',  // ❌ 不导入终止原因（数据不规范）
            'stop_date', 'stop_month_amt', 'stop_year_amt', 'invoice_amount', 'detail_json', 'u_id', 'u_service_json', 'report_id',
        );
        
        $saveList = array();
        // 先设置必需字段
        $data['cont_id'] = $cont_id;
        $data['sse_id'] = $sse_id;
        $data['clue_id'] = $storeRow['clue_id'];
        $data['clue_type'] = $storeRow['clue_type'];
        $data['clue_service_id'] = $clue_service_id;
        $data['clue_store_id'] = $storeRow['id'];
        $data['city'] = $storeRow['city'];
        $data['office_id'] = $storeRow['office_id'];
        $data['cont_month_len'] = $cont_month_len;
        
        // 按照 ImportVirForm::saveOneData 的逻辑：使用 key_exists，数组转JSON
        foreach ($saveKey as $key) {
            if (key_exists($key, $data)) {
                $saveList[$key] = is_array($data[$key]) ? json_encode($data[$key], JSON_UNESCAPED_UNICODE) : $data[$key];
            }
        }
        
        $saveList['lcu'] = $username;
        $connection->createCommand()->insert('sal_contract_virtual', $saveList);
        $vir_id = $connection->getLastInsertID();
        
        // 7. 插入虚拟合约进程 sal_contpro_virtual
        $saveList['pro_vir_type'] = 1;
        $saveList['cont_id'] = $data['cont_id'];
        $saveList['pro_id'] = $pro_id;
        $saveList['vir_id'] = $vir_id;
        $saveList['pro_code'] = 'VDL-' . $data['vir_code'];
        $saveList['pro_type'] = DataMigrationHelper::proTypeByStatus($data['vir_status']);
        $saveList['pro_date'] = $data['sign_date'];
        $saveList['pro_remark'] = "导入虚拟合约\n导入id：{$this->id}";
        $saveList['pro_status'] = 30;
        $saveList['pro_change'] = $data['vir_status'] == 30 ? $data['year_amt'] : $data['surplus_amt'];
        $saveList['pro_change'] = empty($saveList['pro_change']) ? 0 : $saveList['pro_change'];
        $connection->createCommand()->insert('sal_contpro_virtual', $saveList);
        
        // 8. 插入虚拟合约详细信息 sal_contract_vir_info（服务项目详情）
        if (!empty($data['virInfo'])) {
            foreach ($data['virInfo'] as $virInfo) {
                $virInfo['virtual_id'] = $vir_id;
                $virInfo['lcu'] = $username;
                $connection->createCommand()->insert('sal_contract_vir_info', $virInfo);
            }
        }
        
        // 9. 插入虚拟合约员工关联 sal_contract_vir_staff（销售）
        
        $connection->createCommand()->insert('sal_contract_vir_staff', array(
            'vir_id' => $vir_id,
            'type' => 4,
            'employee_id' => $data['sales_id'],
            'u_yewudalei' => $data['yewudalei'],
            'role' => 1,
            'u_id' => isset($data['sales_u_id']) ? $data['sales_u_id'] : null,
            'lcu' => $username,
        ));
        
        // 10. 如果有跨区业务员（按照 ImportVirForm 的判断条件）
        if (!empty($data['other_sales_u_id'])) {
            $connection->createCommand()->insert('sal_contract_vir_staff', array(
                'vir_id' => $vir_id,
                'type' => 5,
                'employee_id' => $data['other_sales_id'],
                'u_yewudalei' => $data['other_yewudalei'],
                'role' => 0,
                'u_id' => $data['other_sales_u_id'],
                'lcu' => $username,
            ));
        }
        
        // 11. 插入虚拟合约周计划 sal_contract_vir_week（服务频次详情）
        if (!empty($data['u_service_json']['list'])) {
            foreach ($data['u_service_json']['list'] as $weekList) {
                $weekList['vir_id'] = $vir_id;
                $weekList['lcu'] = $username;
                $connection->createCommand()->insert('sal_contract_vir_week', $weekList);
            }
        }
        
        // 12. 更新客户和门店状态
        $connection->createCommand()->update('sal_clue', array(
            'clue_status' => DataMigrationHelper::getClientStatusByClueID($storeRow['clue_id']),
        ), 'id=:id', array(':id' => $storeRow['clue_id']));
        
        $connection->createCommand()->update('sal_clue_store', array(
            'store_status' => DataMigrationHelper::getStoreStatusByStoreID($storeRow['id']),
        ), 'id=:id', array(':id' => $storeRow['id']));
        
        Yii::log('虚拟合约数据导入成功：vir_id=' . $vir_id . ', u_id=' . (isset($data['u_id']) ? $data['u_id'] : 'null'), 'info', 'DataMigration');
    }
    
    /**
     * 为虚拟合约创建主合约（参考 ImportVirForm::computeContID）
     */
    protected function createContractForVirtual($data, $storeRow, $cont_month_len)
    {
        $connection = Yii::app()->db;
        $username = DataMigrationHelper::getCurrentUserId($this->username);
        
        // 初始化拜访类型和对象
        $visit_type = $connection->createCommand()
            ->select('id')
            ->from('sal_visit_type')
            ->order('id asc')
            ->queryScalar();
        
        $visit_obj_row = $connection->createCommand()
            ->select('id, name')
            ->from('sal_visit_obj')
            ->where("rpt_type='DEAL'")
            ->queryRow();
        
        // 1. 创建销售回访记录
        $connection->createCommand()->insert('sal_clue_service', array(
            'clue_id' => $storeRow['clue_id'],
            'clue_type' => $storeRow['clue_type'],
            'visit_type' => $visit_type,
            'visit_obj' => $visit_obj_row['id'],
            'visit_obj_text' => $visit_obj_row['name'],
            'create_staff' => $data['sales_id'],
            'busine_id' => $data['busine_id'],
            'busine_id_text' => $data['busine_id_text'],
            'sign_odds' => 100,
            'lbs_main' => $data['lbs_main'],
            'predict_date' => $data['sign_date'],
            'predict_amt' => $data['year_amt'],
            'total_amt' => $data['year_amt'],
            'total_num' => 1,
            'service_status' => $data['vir_status'],
            'lcu' => $username,
            'report_id' => $this->id,
        ));
        $clue_service_id = $connection->getLastInsertID();
        
        // 2. 创建主合约
        $contArr = array(
            'clue_id' => $storeRow['clue_id'],
            'clue_type' => $storeRow['clue_type'],
            'clue_service_id' => $clue_service_id,
            'city' => $storeRow['city'],
            'cont_code' => 'DL-' . $data['vir_code'],
            'sales_id' => $data['sales_id'],
            'lbs_main' => $data['lbs_main'],
            'predict_amt' => $data['year_amt'],
            'store_sum' => 1,
            'cont_type' => 1,
            'sign_type' => 1,
            'total_sum' => $data['service_sum'],
            'total_amt' => $data['year_amt'],
            'cont_status' => $data['vir_status'],
            'stop_date' => isset($data['stop_date']) ? $data['stop_date'] : null,
            'surplus_num' => isset($data['surplus_num']) ? $data['surplus_num'] : null,
            'surplus_amt' => isset($data['surplus_amt']) ? $data['surplus_amt'] : null,
            'cont_start_dt' => $data['cont_start_dt'],
            'cont_end_dt' => $data['cont_end_dt'],
            'cont_month_len' => $cont_month_len,
            'sign_date' => $data['sign_date'],
            'area_bool' => 'N',
            'group_bool' => 'N',
            'prioritize_service' => isset($data['prioritize_service']) ? $data['prioritize_service'] : 'N',
            'service_timer' => isset($data['service_timer']) ? $data['service_timer'] : null,
            'pay_type' => isset($data['pay_type']) ? $data['pay_type'] : null,
            'pay_week' => isset($data['pay_week']) ? $data['pay_week'] : null,
            'pay_month' => isset($data['pay_month']) ? $data['pay_month'] : null,
            'pay_start' => isset($data['pay_start']) ? $data['pay_start'] : null,
            'deposit_need' => isset($data['deposit_need']) ? $data['deposit_need'] : null,
            'deposit_amt' => isset($data['deposit_amt']) ? $data['deposit_amt'] : null,
            'deposit_rmk' => isset($data['deposit_rmk']) ? $data['deposit_rmk'] : null,
            'fee_type' => isset($data['fee_type']) ? $data['fee_type'] : null,
            'settle_type' => isset($data['settle_type']) ? $data['settle_type'] : null,
            'bill_day' => isset($data['bill_day']) ? $data['bill_day'] : null,
            'bill_bool' => isset($data['bill_bool']) ? $data['bill_bool'] : 'N',
            'receivable_day' => isset($data['receivable_day']) ? $data['receivable_day'] : null,
            'yewudalei' => $data['yewudalei'],
            'other_sales_id' => isset($data['other_sales_id']) ? $data['other_sales_id'] : null,
            'other_yewudalei' => isset($data['other_yewudalei']) ? $data['other_yewudalei'] : null,
            'busine_id' => $data['busine_id'],
            'busine_id_text' => $data['busine_id_text'],
            'report_id' => $this->id,
            'lcu' => $username,
        );
        $connection->createCommand()->insert('sal_contract', $contArr);
        $cont_id = $connection->getLastInsertID();
        
        // 3. 创建主合约变更记录
        $contArr['cont_id'] = $cont_id;
        $contArr['pro_code'] = 'PDL-' . $data['vir_code'];
        $contArr['pro_type'] = DataMigrationHelper::proTypeByStatus($data['vir_status']);
        $contArr['pro_date'] = $data['sign_date'];
        $contArr['pro_remark'] = "派单数据导入自动生成\n导入id：{$this->id}";
        $contArr['pro_status'] = 30;
        $contArr['pro_change'] = $data['vir_status'] == 30 ? $data['year_amt'] : (isset($data['surplus_amt']) ? $data['surplus_amt'] : 0);
        $contArr['pro_change'] = empty($contArr['pro_change']) ? 0 : $contArr['pro_change'];
        $connection->createCommand()->insert('sal_contpro', $contArr);
        $pro_id = $connection->getLastInsertID();
        
        return array(
            'cont_id' => $cont_id,
            'clue_service_id' => $clue_service_id,
            'pro_id' => $pro_id,
        );
    }
    
    /**
     * 计算合约SSE关联
     */
    protected function computeContSSE($cont_id, $pro_id, $clue_service_id, $clue_id, $clue_store_id, $data)
    {
        $connection = Yii::app()->db;
        $username = DataMigrationHelper::getCurrentUserId($this->username);
        
        $detail_json = isset($data['detail_json']) ? $data['detail_json'] : '{}';
        
        $sseArr = array(
            'clue_id' => $clue_id,
            'clue_service_id' => $clue_service_id,
            'clue_store_id' => $clue_store_id,
            'create_staff' => $data['sales_id'],
            'store_amt' => $data['year_amt'],
            'service_sum' => $data['service_sum'],
            'update_bool' => 3,
            'busine_id' => $data['busine_id'],
            'busine_id_text' => $data['busine_id_text'],
            'detail_json' => $detail_json,
            'lcu' => $username,
        );
        
        // 插入或更新 sal_contract_sse
        $contSSE = $connection->createCommand()
            ->select('*')
            ->from('sal_contract_sse')
            ->where('cont_id=:cont_id AND clue_store_id=:clue_store_id', array(
                ':cont_id' => $cont_id,
                ':clue_store_id' => $clue_store_id,
            ))
            ->queryRow();
        
        if ($contSSE) {
            $connection->createCommand()->update('sal_contract_sse', $sseArr, 'id=' . $contSSE['id']);
            $sse_id = $contSSE['id'];
        } else {
            $sseArr['cont_id'] = $cont_id;
            $connection->createCommand()->insert('sal_contract_sse', $sseArr);
            $sse_id = $connection->getLastInsertID();
        }
        
        // 插入或更新 sal_contpro_sse
        $contProSSE = $connection->createCommand()
            ->select('*')
            ->from('sal_contpro_sse')
            ->where('pro_id=:pro_id AND clue_store_id=:clue_store_id', array(
                ':pro_id' => $pro_id,
                ':clue_store_id' => $clue_store_id,
            ))
            ->queryRow();
        
        if ($contProSSE) {
            $connection->createCommand()->update('sal_contpro_sse', $sseArr, 'id=' . $contProSSE['id']);
        } else {
            $sseArr['pro_id'] = $pro_id;
            $connection->createCommand()->insert('sal_contpro_sse', $sseArr);
        }
        
        return $sse_id;
    }
    
    /**
     * 计算合约月数
     */
    protected function computeMonthLen($startDate, $endDate)
    {
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        
        $startYear = date('Y', $start);
        $startMonth = date('m', $start);
        $endYear = date('Y', $end);
        $endMonth = date('m', $end);
        
        return ($endYear - $startYear) * 12 + ($endMonth - $startMonth) + 1;
    }
    
    /**
     * 根据状态获取进程类型
     */
    protected function proTypeByStatus($status)
    {
        switch ($status) {
            case 30:
                return 'N';
            case 40:
                return 'S';
            case 50:
                return 'T';
            default:
                return 'N';
        }
    }
    
    /**
     * 获取客户状态
     */
    protected function getClientStatusByClueID($clue_id)
    {
        $connection = Yii::app()->db;
        $suffix = isset(Yii::app()->params['envSuffix']) ? Yii::app()->params['envSuffix'] : '';
        
        $statusRow = $connection->createCommand()
            ->select('min(a.vir_status) as min_status')
            ->from("sales{$suffix}.sal_contract_virtual a")
            ->where("a.clue_id={$clue_id} and a.vir_status in (10,30,40,50)")
            ->queryRow();
        
        return $statusRow && $statusRow['min_status'] ? $statusRow['min_status'] : 1;
    }
    
    /**
     * 获取门店状态
     */
    protected function getStoreStatusByStoreID($store_id)
    {
        $connection = Yii::app()->db;
        $suffix = isset(Yii::app()->params['envSuffix']) ? Yii::app()->params['envSuffix'] : '';
        
        $statusRow = $connection->createCommand()
            ->select('min(a.vir_status) as min_status')
            ->from("sales{$suffix}.sal_contract_virtual a")
            ->where("a.clue_store_id={$store_id} and a.vir_status in (10,30,40,50)")
            ->queryRow();
        
        return $statusRow && $statusRow['min_status'] ? $statusRow['min_status'] : 1;
    }
    
    
    
    /**
     * 更新现有数据
     */
    protected function updateExistingData($data)
    {
        switch ($this->migration_type) {
            case 'client':
                $this->updateClientData($data);
                break;
            case 'clientStore':
                $this->updateStoreData($data);
                break;
            case 'cont':
                $this->updateContractData($data);
                break;
            case 'vir':
                $this->updateVirtualContractData($data);
                break;
        }
    }
    
    /**
     * 更新客户数据
     */
    protected function updateClientData($data)
    {
        // 这里实现客户数据的更新逻辑
        // 根据u_id查找现有记录并更新
        Yii::log('更新客户数据：u_id=' . $data['派单系统id'], 'info', 'DataMigration');
    }
    
    /**
     * 更新门店数据
     */
    protected function updateStoreData($data)
    {
        Yii::log('更新门店数据：u_id=' . $data['派单系统id'], 'info', 'DataMigration');
    }
    
    /**
     * 更新主合约数据
     */
    protected function updateContractData($data)
    {
        Yii::log('更新主合约数据：u_id=' . $data['派单系统id'], 'info', 'DataMigration');
    }
    
    /**
     * 更新虚拟合约数据
     */
    protected function updateVirtualContractData($data)
    {
        Yii::log('更新虚拟合约数据：u_id=' . $data['派单系统id'], 'info', 'DataMigration');
    }
    
    /**
     * 获取进度信息
     */
    public function getProgress($logId)
    {
        $connection = Yii::app()->db;
        
        $log = $connection->createCommand()
            ->select('*')
            ->from('sal_data_migration_log')
            ->where('id=:id', array(':id' => $logId))
            ->queryRow();
        
        if (!$log) {
            return null;
        }
        
        // 统计各状态的数量
        $stats = $connection->createCommand()
            ->select('status, COUNT(*) as count')
            ->from('sal_data_migration_detail')
            ->where('log_id=:log_id', array(':log_id' => $logId))
            ->group('status')
            ->queryAll();
        
        $statusCount = array();
        foreach ($stats as $stat) {
            $statusCount[$stat['status']] = intval($stat['count']);
        }
        
        $totalCount = intval($log['total_count']);
        $successCount = isset($statusCount['S']) ? intval($statusCount['S']) : 0;
        $errorCount = isset($statusCount['E']) ? intval($statusCount['E']) : 0;
        $skipCount = isset($statusCount['K']) ? intval($statusCount['K']) : 0;
        $processedCount = $successCount + $errorCount + $skipCount;
        
        // 计算进度百分比
        $progress = $totalCount > 0 ? round(($processedCount / $totalCount) * 100) : 0;
        
        // 获取当前正在处理的行
        $currentRow = $connection->createCommand()
            ->select('row_index')
            ->from('sal_data_migration_detail')
            ->where('log_id=:log_id AND status=:status', array(
                ':log_id' => $logId,
                ':status' => 'P'
            ))
            ->order('row_index ASC')
            ->limit(1)
            ->queryScalar();
        
        return array(
            'status' => isset($log['status']) ? $log['status'] : 'P', // P-处理中, S-成功, E-失败
            'progress' => $progress,
            'total_count' => $totalCount,
            'processed_count' => $processedCount,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'skip_count' => $skipCount,
            'current_row' => $currentRow ? '第' . $currentRow . '行' : null,
        );
    }
    
    /**
     * 获取日志列表
     */
    public function getLogs($page = 1, $pageSize = 20)
    {
        $connection = Yii::app()->db;
        
        $totalCount = $connection->createCommand()
            ->select('COUNT(*)')
            ->from('sal_data_migration_log')
            ->queryScalar();
        
        $offset = ($page - 1) * $pageSize;
        $logs = $connection->createCommand()
            ->select('*')
            ->from('sal_data_migration_log')
            ->order('lcd DESC')
            ->limit($pageSize)
            ->offset($offset)
            ->queryAll();
        
        return array(
            'logs' => $logs,
            'total_count' => $totalCount,
            'page' => $page,
            'page_size' => $pageSize,
            'total_pages' => ceil($totalCount / $pageSize),
        );
    }
}
