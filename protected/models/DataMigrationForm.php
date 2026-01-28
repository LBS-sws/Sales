<?php

//  导入业务处理器类（解决 Yii 自动加载问题）
Yii::import('application.models.processors.DataMigrationClientProcessor');
Yii::import('application.models.processors.DataMigrationStoreProcessor');
Yii::import('application.models.processors.DataMigrationContractProcessor');
Yii::import('application.models.processors.DataMigrationVirtualContractProcessor');

// 导入辅助类
Yii::import('application.models.DataMigrationHelper');

// 导入API客户端
Yii::import('application.components.PaidanApiClient');

/**
 * 数据迁移表单模型
 * 负责从派单系统获取数据、验证数据、同步数据到CRM系统
 * 
 * 🔄 重构说明：
 * - 辅助工具方法已迁移至 DataMigrationHelper 类
 * - 业务处理逻辑已拆分到各业务处理器类
 *   - DataMigrationClientProcessor: 客户迁移
 *   - DataMigrationStoreProcessor: 门店迁移
 *   - DataMigrationContractProcessor: 合约迁移
 *   - DataMigrationVirtualContractProcessor: 虚拟合约迁移
 * 
 * @see DataMigrationHelper 辅助工具类
 * @see DataMigrationClientProcessor 客户迁移处理器
 * @see DataMigrationStoreProcessor 门店迁移处理器
 * @see DataMigrationContractProcessor 合约迁移处理器
 * @see DataMigrationVirtualContractProcessor 虚拟合约处理器
 */
class DataMigrationForm extends CFormModel
{
    // ===== 性能优化：批量预加载的缓存 =====
    protected static $clueCache = array();       // 客户缓存 clue_code => row
    protected static $storeCache = array();      // 门店缓存 store_code => row
    protected static $visitTypeId = null;        // 拜访类型ID（常量）
    protected static $visitObjId = null;         // 拜访对象ID（常量）
    protected static $visitObjText = null;       // 拜访对象文本（常量）
    
    public $id; // 迁移记录ID
    public $migration_type; // 迁移类型: client/clientStore/cont/vir
    public $api_url; // 派单系统API地址
    public $api_config; // API配置信息（JSON格式）
    public $filter_params; // 筛选参数（JSON格式）
    public $type; // 项目类型：1=KA, 2=地推, 空=全部
    public $username; // 操作用户
    public $req_dt; // 请求时间
    
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
            
            //  添加项目类型参数
            if (!empty($this->type)) {
                $filterParams['type'] = $this->type;
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
            $totalCount = isset($data['total_count']) ? $data['total_count'] : count($rows);  //  从API获取总数
            $currentCount = count($rows);  // 当前页数量
            
            if (empty($rows)) {
                return array(
                    'status' => 0,
                    'message' => '未获取到任何数据',
                    'total_count' => $totalCount,  //  返回总数，即使当前页为空
                    'count' => 0,
                );
            }
            
            // 保存迁移日志
            $logId = $this->saveMigrationLog(array(
                'total_count' => $currentCount,  // 保存当前批次的数量
                'status' => 'P', // P-处理中
            ));
            
            // 保存迁移详情
            $this->saveMigrationDetails($logId, $rows);
            
            return array(
                'status' => 1,
                'message' => '获取数据成功',
                'log_id' => $logId,
                'total_count' => $totalCount,  //  API返回的总记录数
                'count' => $currentCount,  //  当前页的记录数
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
            'type' => $this->type, //  添加项目类型
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
     * 保存迁移详情（u_id存在时更新，不存在时插入）
     *  保证u_id唯一，同时保留log_id追踪
     */
    protected function saveMigrationDetails($logId, $rows)
    {
        $connection = Yii::app()->db;
        $insertCount = 0;
        $updateCount = 0;
        
        // 获取当前用户
        $currentUser = DataMigrationHelper::getCurrentUserId($this->username);
        
        //  使用事务提高大量数据保存性能
        $transaction = $connection->beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                // 提取派单系统ID（用于去重）
                $uId = $this->extractUId($row);
                
                $data = array(
                    'log_id' => $logId,
                    'row_index' => $index + 1,
                    'u_id' => $uId,
                    'source_data' => json_encode($row, JSON_UNESCAPED_UNICODE),
                    'status' => 'P', // 重置为待处理状态
                    'error_message' => null, // 清空之前的错误
                    'lcu' => $currentUser,
                    'lcd' => date('Y-m-d H:i:s'),
                );
                
                //  检查u_id是否已存在（跨批次）
                $existingId = null;
                if (!empty($uId)) {
                    $existingId = $this->getExistingDetailId($uId);
                }
                
                if ($existingId) {
                    //  u_id已存在 → 更新记录（保留最新批次的log_id）
                    $connection->createCommand()->update(
                        'sal_data_migration_detail',
                        $data,
                        'id=:id',
                        array(':id' => $existingId)
                    );
                    $updateCount++;
                } else {
                    //  u_id不存在 → 插入新记录
                    $connection->createCommand()->insert('sal_data_migration_detail', $data);
                    $insertCount++;
                }
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
            Yii::log("保存迁移详情失败: " . $e->getMessage(), 'error', 'DataMigration');
            throw $e;
        }
        
        // 记录日志
        Yii::log(
            "保存Detail记录：新增{$insertCount}条，更新{$updateCount}条（u_id已存在，已更新为当前批次log_id={$logId}）", 
            'info', 
            'DataMigration'
        );
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
     * 获取已存在的Detail记录ID（用于更新）
     *  跨批次查找，返回记录ID
     */
    private function getExistingDetailId($uId)
    {
        $connection = Yii::app()->db;
        
        // 查找已存在的记录（任何状态都可以更新）
        $id = $connection->createCommand()
            ->select('id')
            ->from('sal_data_migration_detail')
            ->where('u_id=:u_id', array(':u_id' => $uId))
            ->order('id DESC') // 如果有多条，取最新的
            ->limit(1)
            ->queryScalar();
        
        return $id ? intval($id) : null;
    }
    
    /**
     * 检查Detail表中是否已存在相同派单系统ID的记录（跨批次）
     * @deprecated 已由 getExistingDetailId 替代，保留用于兼容
     */
    private function detailRecordExists($logId, $uId)
    {
        return $this->getExistingDetailId($uId) !== null;
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
        
        // 获取待导入的数据ID列表（不直接取数据，避免内存占用）
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
        
        // 只获取ID列表，分批处理
        $detailIds = $connection->createCommand()
            ->select('id')
            ->from('sal_data_migration_detail')
            ->where($where, $params)
            ->order('row_index ASC')
            ->queryColumn();
        
        if (empty($detailIds)) {
            return array(
                'status' => 0,
                'message' => '没有待导入的数据',
            );
        }
        
        // 初始化导入配置
        if (Yii::app() instanceof CWebApplication && !Yii::app()->user->isGuest) {
            $this->username = Yii::app()->user->id;
        } else {
            $this->username = empty($this->username) ? 'system' : $this->username;
        }
        $this->req_dt = date("Y-m-d H:i:s");
        
        // 分批处理
        $totalRecords = count($detailIds);
        $batches = array_chunk($detailIds, $batchSize);
        $totalBatches = count($batches);
        
        // 更新日志：初始化批次信息
        $connection->createCommand()->update(
            'sal_data_migration_log',
            array(
                'batch_size' => $batchSize,
                'total_batches' => $totalBatches,
                'current_batch' => 0,
            ),
            'id=:id',
            array(':id' => $this->id)
        );
        
        $totalSuccessCount = 0;
        $totalErrorCount = 0;
        
        // 逐批处理
        foreach ($batches as $batchIndex => $batchIds) {
            $batchNum = $batchIndex + 1;
            
            // 更新当前批次号
            $connection->createCommand()->update(
                'sal_data_migration_log',
                array(
                    'current_batch' => $batchNum,
                    'current_batch_progress' => "正在处理第 {$batchNum}/{$totalBatches} 批...",
                ),
                'id=:id',
                array(':id' => $this->id)
            );
            
            // 获取当前批次的数据
            $batchDetails = $connection->createCommand()
                ->select('*')
                ->from('sal_data_migration_detail')
                ->where('id IN (' . implode(',', array_map('intval', $batchIds)) . ')')
                ->order('row_index ASC')
                ->queryAll();
            
            // 批量预加载常用数据
            $this->preloadCommonData($batchDetails, $connection);
            
            // 使用事务处理当前批次
            $transaction = $connection->beginTransaction();
            
            try {
                $batchSuccessCount = 0;
                $batchErrorCount = 0;
                
                foreach ($batchDetails as $detail) {
                    $rowData = json_decode($detail['source_data'], true);
                    
                    try {
                        if (empty($rowData)) {
                            throw new Exception('数据格式错误：无法解析JSON');
                        }
                        
                        if ($this->recordExists($rowData)) {
                            $this->updateExistingData($rowData);
                            $status = 'S';
                            $message = '更新成功';
                        } else {
                            $this->insertNewData($rowData);
                            $status = 'S';
                            $message = '导入成功';
                        }
                        
                        $batchSuccessCount++;
                        
                    } catch (Exception $e) {
                        $status = 'E';
                        $message = '导入失败：' . $e->getMessage();
                        $batchErrorCount++;
                        
                        Yii::log(
                            '数据导入失败 [行' . $detail['row_index'] . ']: ' . $e->getMessage(),
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
                
                $transaction->commit();
                
                $totalSuccessCount += $batchSuccessCount;
                $totalErrorCount += $batchErrorCount;
                
                // 更新总计数
                $connection->createCommand()->update(
                    'sal_data_migration_log',
                    array(
                        'success_count' => $totalSuccessCount,
                        'error_count' => $totalErrorCount,
                    ),
                    'id=:id',
                    array(':id' => $this->id)
                );
                
            } catch (Exception $transactionException) {
                $transaction->rollback();
                Yii::log('批次 ' . $batchNum . ' 导入事务失败，已回滚: ' . $transactionException->getMessage(), 'error', 'DataMigration');
                throw $transactionException;
            }
            
            // 避免CPU占用过高
            usleep(50000); // 0.05秒
        }
        
        // 更新日志：完成
        $connection->createCommand()->update(
            'sal_data_migration_log',
            array(
                'status' => 'S',
                'end_time' => date('Y-m-d H:i:s'),
                'current_batch_progress' => '导入完成',
                'lcu' => DataMigrationHelper::getCurrentUserId($this->username),
                'lcd' => date('Y-m-d H:i:s'),
            ),
            'id=:id',
            array(':id' => $this->id)
        );
        
        return array(
            'status' => 1,
            'message' => '导入完成',
            'success_count' => $totalSuccessCount,
            'error_count' => $totalErrorCount,
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
     * 新增数据（使用业务处理器）
     */
    protected function insertNewData($data)
    {
        // 数据预处理和转换
        $processedData = $this->preprocessData($data);
        
        $connection = Yii::app()->db;
        $username = DataMigrationHelper::getCurrentUserId($this->username);
        
        // 根据类型调用对应的处理器插入逻辑
        switch ($this->migration_type) {
            case 'client':
                DataMigrationClientProcessor::insert($processedData, $connection, $username, $this->id);
                break;
            case 'clientStore':
                DataMigrationStoreProcessor::insert($processedData, $connection, $username, $this->id);
                break;
            case 'cont':
                DataMigrationContractProcessor::insert($processedData, $connection, $username, $this->id);
                break;
            case 'vir':
                DataMigrationVirtualContractProcessor::insert($processedData, $connection, $username, $this->id);
                break;
            default:
                throw new Exception('不支持的导入类型：' . $this->migration_type);
        }
    }
    
    /**
     * 数据预处理：中文字段名 → 英文字段名 + 数据转换
     * 使用业务处理器进行预处理
     */
    protected function preprocessData($data)
    {
        $connection = Yii::app()->db;
        
        switch ($this->migration_type) {
            case 'client':
                return DataMigrationClientProcessor::preprocess($data, $connection, $this->id);
            case 'clientStore':
                return DataMigrationStoreProcessor::preprocess($data, $connection, $this->id);
            case 'cont':
                return DataMigrationContractProcessor::preprocess($data, $connection, $this->id);
            case 'vir':
                return DataMigrationVirtualContractProcessor::preprocess($data, $connection, $this->id);
            default:
                return $data;
        }
    }
    
    /**
     * 更新现有数据（使用业务处理器）
     */
    protected function updateExistingData($data)
    {
        // 数据预处理
        $processedData = $this->preprocessData($data);
        
        $connection = Yii::app()->db;
        $username = DataMigrationHelper::getCurrentUserId($this->username);
        
        // 根据类型调用对应的处理器更新逻辑
        switch ($this->migration_type) {
            case 'client':
                DataMigrationClientProcessor::update($processedData, $connection, $username, $this->id);
                break;
            case 'clientStore':
                DataMigrationStoreProcessor::update($processedData, $connection, $username, $this->id);
                break;
            case 'cont':
                DataMigrationContractProcessor::update($processedData, $connection, $username, $this->id);
                break;
            case 'vir':
                DataMigrationVirtualContractProcessor::update($processedData, $connection, $username, $this->id);
                break;
        }
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
            'batch_size' => isset($log['batch_size']) ? intval($log['batch_size']) : 100,
            'current_batch' => isset($log['current_batch']) ? intval($log['current_batch']) : 0,
            'total_batches' => isset($log['total_batches']) ? intval($log['total_batches']) : 0,
            'current_batch_progress' => isset($log['current_batch_progress']) ? $log['current_batch_progress'] : '',
            'total_count' => $totalCount,
            'processed_count' => $processedCount,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'skip_count' => $skipCount,
            'current_row' => $currentRow ? '第' . $currentRow . '行' : null,
        );
    }
    
    /**
     * 获取所有日志列表（分页）
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
    
    /**
     * 批量预加载常用数据到缓存（性能优化）
     * 避免每条记录都查询数据库
     * 
     * @param array $details 待处理的详情数据
     * @param CDbConnection $connection 数据库连接
     */
    protected function preloadCommonData($details, $connection)
    {
        $startTime = microtime(true);
        
        // 1. 预加载常量数据（visit_type, visit_obj）
        if (self::$visitTypeId === null) {
            self::$visitTypeId = $connection->createCommand()
                ->select('id')
                ->from('sal_visit_type')
                ->order('id asc')
                ->queryScalar();
        }
        
        if (self::$visitObjId === null) {
            $visitObjRow = $connection->createCommand()
                ->select('id, name')
                ->from('sal_visit_obj')
                ->order('id asc')
                ->queryRow();
            if ($visitObjRow) {
                self::$visitObjId = $visitObjRow['id'];
                self::$visitObjText = $visitObjRow['name'];
            }
        }
        
        // 2. 收集所有需要的客户编号和门店编号
        $clueCodes = array();
        $storeCodes = array();
        
        foreach ($details as $detail) {
            $rowData = json_decode($detail['source_data'], true);
            if (empty($rowData)) continue;
            
            // 收集客户编号
            if (isset($rowData['客户编号']) && !empty($rowData['客户编号'])) {
                $clueCodes[] = $rowData['客户编号'];
            }
            // 收集门店编号
            if (isset($rowData['门店编号']) && !empty($rowData['门店编号'])) {
                $storeCodes[] = $rowData['门店编号'];
            }
        }
        
        // 3. 批量加载客户数据
        if (!empty($clueCodes)) {
            $clueCodes = array_unique($clueCodes);
            $clues = $connection->createCommand()
                ->select('*')
                ->from('sal_clue')
                ->where(array('in', 'clue_code', $clueCodes))
                ->queryAll();
            
            foreach ($clues as $clue) {
                self::$clueCache[$clue['clue_code']] = $clue;
            }
            
            Yii::log('预加载客户数据：' . count($clues) . ' 条', 'info', 'DataMigration');
        }
        
        // 4. 批量加载门店数据
        if (!empty($storeCodes)) {
            $storeCodes = array_unique($storeCodes);
            $stores = $connection->createCommand()
                ->select('*')
                ->from('sal_clue_store')
                ->where(array('in', 'store_code', $storeCodes))
                ->queryAll();
            
            foreach ($stores as $store) {
                self::$storeCache[$store['store_code']] = $store;
            }
            
            Yii::log('预加载门店数据：' . count($stores) . ' 条', 'info', 'DataMigration');
        }
        
        $elapsed = round((microtime(true) - $startTime) * 1000, 2);
        Yii::log("批量预加载完成，耗时：{$elapsed}ms", 'info', 'DataMigration');
    }
    
    /**
     * 从缓存获取客户数据
     * 
     * @param string $clueCode 客户编号
     * @param CDbConnection $connection 数据库连接
     * @return array|null 客户数据
     */
    public static function getCachedClue($clueCode, $connection)
    {
        if (isset(self::$clueCache[$clueCode])) {
            return self::$clueCache[$clueCode];
        }
        
        // 缓存未命中，查询数据库并缓存
        $clue = $connection->createCommand()
            ->select('*')
            ->from('sal_clue')
            ->where('clue_code=:code', array(':code' => $clueCode))
            ->queryRow();
        
        if ($clue) {
            self::$clueCache[$clueCode] = $clue;
        }
        
        return $clue;
    }
    
    /**
     * 从缓存获取门店数据
     * 
     * @param string $storeCode 门店编号
     * @param CDbConnection $connection 数据库连接
     * @return array|null 门店数据
     */
    public static function getCachedStore($storeCode, $connection)
    {
        if (isset(self::$storeCache[$storeCode])) {
            return self::$storeCache[$storeCode];
        }
        
        // 缓存未命中，查询数据库并缓存
        $store = $connection->createCommand()
            ->select('*')
            ->from('sal_clue_store')
            ->where('store_code=:code', array(':code' => $storeCode))
            ->queryRow();
        
        if ($store) {
            self::$storeCache[$storeCode] = $store;
        }
        
        return $store;
    }
    
    /**
     * 获取缓存的拜访类型ID
     */
    public static function getCachedVisitTypeId()
    {
        return self::$visitTypeId;
    }
    
    /**
     * 获取缓存的拜访对象ID
     */
    public static function getCachedVisitObjId()
    {
        return self::$visitObjId;
    }
    
    /**
     * 获取缓存的拜访对象文本
     */
    public static function getCachedVisitObjText()
    {
        return self::$visitObjText;
    }
}
