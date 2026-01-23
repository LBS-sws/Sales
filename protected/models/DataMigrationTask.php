<?php
/**
 * 异步数据迁移任务模型
 */
class DataMigrationTask extends CActiveRecord
{
    // 任务状态常量
    const STATUS_PENDING = 0;    // 待处理
    const STATUS_PROCESSING = 1;  // 处理中
    const STATUS_COMPLETED = 2;   // 已完成
    const STATUS_FAILED = 3;      // 失败
    const STATUS_CANCELLED = 4;   // 已取消
    
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public function tableName()
    {
        return 'sal_data_migration_task';
    }
    
    /**
     * 验证规则
     */
    public function rules()
    {
        return array(
            // ✅ 允许所有字段安全赋值（暂时性方案，方便调试）
            array('task_code, migration_type, api_url, task_status, api_config, filter_params, type, priority, total_cities, completed_cities, total_records, success_count, error_count, retry_count, max_retry, current_city, start_time, end_time, error_message, created_by, created_at, updated_at, process_id', 'safe'),
        );
    }
    
    /**
     * 属性标签
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'task_code' => '任务编号',
            'migration_type' => '迁移类型',
            'api_url' => 'API地址',
            'task_status' => '任务状态',
            'created_by' => '创建人',
        );
    }
    
    /**
     * 创建新任务
     * @param array $params 任务参数
     * @return array 返回结果数组
     */
    public static function createTask($params)
    {
        $task = new self();
        $task->task_code = self::generateTaskCode();
        $task->migration_type = $params['migration_type'];
        $task->api_url = $params['api_url'];
        $task->api_config = is_array($params['api_config']) ? json_encode($params['api_config']) : $params['api_config'];
        $task->filter_params = is_array($params['filter_params']) ? json_encode($params['filter_params']) : $params['filter_params'];
        $task->type = isset($params['type']) ? $params['type'] : ''; // ✅ 添加项目类型
        $task->task_status = self::STATUS_PENDING;
        $task->priority = isset($params['priority']) ? $params['priority'] : 5;
        $task->created_by = Yii::app()->user->id;
        
        // 设置时间戳
        $now = date('Y-m-d H:i:s');
        $task->created_at = $now;
        $task->updated_at = $now;
        
        // 初始化默认值
        $task->total_cities = 0;
        $task->completed_cities = 0;
        $task->total_records = 0;
        $task->success_count = 0;
        $task->error_count = 0;
        $task->retry_count = 0;
        $task->max_retry = 3;
        
        // 解析城市列表
        $filterParams = is_array($params['filter_params']) ? $params['filter_params'] : json_decode($params['filter_params'], true);
        
        // ✅ 判断是否是全量导出
        $isFullExport = (isset($filterParams['export_mode']) && $filterParams['export_mode'] === 'type') 
                     || (empty($filterParams['office_code_ids']));
        
        if ($isFullExport) {
            // 全量导出：创建一个虚拟城市
            $task->total_cities = 1;
        } else {
            // 按城市导出：计算城市数量
            if (isset($filterParams['office_code_ids']) && is_array($filterParams['office_code_ids'])) {
                $task->total_cities = count($filterParams['office_code_ids']);
            }
        }
        
        if ($task->save()) {
            // 创建城市明细
            if ($isFullExport) {
                // ✅ 全量导出：创建一个虚拟城市明细（city_code='all'）
                $typeText = isset($params['type']) && $params['type'] == '1' ? 'KA' : '地推';
                $cityName = '全部' . $typeText . '项目';
                self::createTaskDetails($task->id, array('all'), $cityName);
            } elseif (isset($filterParams['office_code_ids']) && is_array($filterParams['office_code_ids'])) {
                // 按城市导出：创建城市明细
                self::createTaskDetails($task->id, $filterParams['office_code_ids']);
            }
            
            return array(
                'status' => true,
                'task_id' => $task->id,
                'task_code' => $task->task_code,
                'total_cities' => $task->total_cities,
                'message' => '任务创建成功'
            );
        }
        
        return array(
            'status' => false,
            'message' => '任务创建失败：' . print_r($task->getErrors(), true)
        );
    }
    
    /**
     * 创建任务明细
     * @param int $taskId 任务ID
     * @param array $cityCodes 城市代码数组
     * @param string $virtualCityName 虚拟城市名称（用于全量导出，可选）
     */
    protected static function createTaskDetails($taskId, $cityCodes, $virtualCityName = null)
    {
        $cityMap = self::getCityMap();
        $now = date('Y-m-d H:i:s');
        
        foreach ($cityCodes as $cityCode) {
            $detail = new DataMigrationTaskDetail();
            $detail->task_id = $taskId;
            $detail->city_code = $cityCode;
            
            // ✅ 如果是虚拟城市（city_code='all'），使用传入的虚拟城市名称
            if ($cityCode === 'all' && !empty($virtualCityName)) {
                $detail->city_name = $virtualCityName;
            } else {
                $detail->city_name = isset($cityMap[$cityCode]) ? $cityMap[$cityCode] : $cityCode;
            }
            
            $detail->status = 0; // 待处理
            $detail->success_count = 0;
            $detail->error_count = 0;
            $detail->created_at = $now;
            $detail->updated_at = $now;
            $detail->save();
        }
    }
    
    /**
     * 获取城市映射
     */
    protected static function getCityMap()
    {
        // 这里应该从配置或数据库获取城市映射
        // 简化处理，返回空数组
        return array();
    }
    
    /**
     * 生成任务编号
     */
    protected static function generateTaskCode()
    {
        return 'TASK_' . date('YmdHis') . '_' . sprintf('%04d', rand(0, 9999));
    }
    
    /**
     * 获取待处理的任务（包括卡住的僵尸任务）
     * @param int $limit 获取数量
     * @return DataMigrationTask[]
     */
    public static function getPendingTasks($limit = 1)
    {
        // 查找待处理的任务，或者处理中但超过30分钟没更新的任务（可能是僵尸进程）
        $condition = 'task_status = :pending OR (task_status = :processing AND updated_at < :timeout)';
        $params = array(
            ':pending' => self::STATUS_PENDING,
            ':processing' => self::STATUS_PROCESSING,
            ':timeout' => date('Y-m-d H:i:s', strtotime('-30 minutes'))
        );
        
        $tasks = self::model()->findAll(array(
            'condition' => $condition,
            'params' => $params,
            'order' => 'priority ASC, created_at ASC',
            'limit' => $limit,
        ));
        
        // 对于僵尸任务，重置其状态
        foreach ($tasks as $task) {
            if ($task->task_status == self::STATUS_PROCESSING) {
                // 记录日志
                Yii::log("检测到僵尸任务 {$task->task_code}，已超过30分钟未更新，重置为待处理", 'warning', 'application.async');
                
                // 重置任务状态为待处理
                $task->task_status = self::STATUS_PENDING;
                $task->process_id = null;
                $task->updated_at = date('Y-m-d H:i:s');
                $task->save(false);
                
                // 同时重置该任务下所有"处理中"的城市明细为"待处理"，避免遗漏
                // 已完成的城市（status=2）保持不变，避免重复导入
                Yii::app()->db->createCommand()->update(
                    'sal_data_migration_task_detail',
                    array(
                        'status' => 0,  // 重置为待处理
                        'start_time' => null,
                        'updated_at' => date('Y-m-d H:i:s')
                    ),
                    'task_id = :task_id AND status = 1',  // 只重置处理中的城市
                    array(':task_id' => $task->id)
                );
                
                Yii::log("已重置任务 {$task->task_code} 下所有处理中的城市明细", 'info', 'application.async');
            }
        }
        
        return $tasks;
    }
    
    /**
     * 开始处理任务
     */
    public function startProcessing()
    {
        $this->task_status = self::STATUS_PROCESSING;
        $this->start_time = date('Y-m-d H:i:s');
        $this->process_id = getmypid();
        $this->updated_at = date('Y-m-d H:i:s');
        return $this->save();
    }
    
    /**
     * 完成任务
     */
    public function complete()
    {
        $this->task_status = self::STATUS_COMPLETED;
        $this->end_time = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        return $this->save();
    }
    
    /**
     * 任务失败
     */
    public function fail($errorMessage)
    {
        $this->task_status = self::STATUS_FAILED;
        $this->end_time = date('Y-m-d H:i:s');
        $this->error_message = $errorMessage;
        $this->retry_count++;
        $this->updated_at = date('Y-m-d H:i:s');
        return $this->save();
    }
    
    /**
     * 更新进度
     */
    public function updateProgress($completedCities, $successCount, $errorCount, $currentCity = null)
    {
        $this->completed_cities = $completedCities;
        $this->success_count = $successCount;
        $this->error_count = $errorCount;
        $this->total_records = $successCount + $errorCount;
        if ($currentCity !== null) {
            $this->current_city = $currentCity;
        }
        $this->updated_at = date('Y-m-d H:i:s');
        return $this->save();
    }
    
    /**
     * 获取任务详情
     */
    public function getDetails()
    {
        return DataMigrationTaskDetail::model()->findAll('task_id = :task_id', array(':task_id' => $this->id));
    }
    
    /**
     * 获取进度百分比
     */
    public function getProgress()
    {
        if ($this->total_cities == 0) {
            return 0;
        }
        return round(($this->completed_cities / $this->total_cities) * 100, 2);
    }
    
    /**
     * 获取状态文本
     */
    public function getStatusText()
    {
        $statusMap = array(
            self::STATUS_PENDING => '待处理',
            self::STATUS_PROCESSING => '处理中',
            self::STATUS_COMPLETED => '已完成',
            self::STATUS_FAILED => '失败',
            self::STATUS_CANCELLED => '已取消',
        );
        return isset($statusMap[$this->task_status]) ? $statusMap[$this->task_status] : '未知';
    }
    
    /**
     * 获取任务状态（静态方法，供控制器调用）
     * @param int $taskId 任务ID
     * @return array
     */
    public static function getTaskStatus($taskId)
    {
        $task = self::model()->findByPk($taskId);
        
        if (!$task) {
            return array(
                'status' => 0,
                'message' => '任务不存在'
            );
        }
        
        // 获取任务详细信息
        $details = Yii::app()->db->createCommand()
            ->select('*')
            ->from('sal_data_migration_task_detail')
            ->where('task_id = :task_id', array(':task_id' => $taskId))
            ->order('id ASC')
            ->queryAll();
        
        // 计算进度
        $totalCities = count($details);
        $completedCities = 0;
        $totalRecords = 0;
        $processedRecords = 0;
        $totalSuccess = 0;
        $totalError = 0;
        
        foreach ($details as $detail) {
            if ($detail['status'] == 2) { // 已完成
                $completedCities++;
            }
            $totalSuccess += intval($detail['success_count']);
            $totalError += intval($detail['error_count']);
            $processedRecords += intval($detail['processed_records']);
        }
        
        $statusText = array(
            self::STATUS_PENDING => '待处理',
            self::STATUS_PROCESSING => '处理中',
            self::STATUS_COMPLETED => '已完成',
            self::STATUS_FAILED => '失败',
            self::STATUS_CANCELLED => '已取消',
        );
        
        // 获取当前正在处理的城市和分页进度
        $currentCity = '';
        $currentProgress = '';
        foreach ($details as $detail) {
            if ($detail['status'] == 1) { // 处理中
                $currentCity = $detail['city_name'];
                // ✅ 获取分页进度（从 error_message 字段读取）
                if (!empty($detail['error_message']) && strpos($detail['error_message'], '正在') !== false) {
                    $currentProgress = $detail['error_message'];
                }
                break;
            }
        }
        
        // ✅ 组合当前城市和分页进度
        $currentCityDisplay = $currentCity;
        if (!empty($currentProgress)) {
            $currentCityDisplay .= ' (' . $currentProgress . ')';
        }
        
        return array(
            'status' => 1,
            'data' => array(
                'task_id' => $task->id,
                'task_status' => $task->task_status,
                'status_text' => isset($statusText[$task->task_status]) ? $statusText[$task->task_status] : '未知',  // ✅ 前端期望的字段名
                'migration_type' => $task->migration_type,
                'total_cities' => $totalCities,
                'completed_cities' => $completedCities,
                'progress' => $totalCities > 0 ? round($completedCities / $totalCities * 100, 2) : 0,
                'success_count' => $totalSuccess,  // ✅ 前端期望的字段名
                'error_count' => $totalError,      // ✅ 前端期望的字段名
                'current_city' => $currentCityDisplay,    // ✅ 当前正在处理的城市（包含分页进度）
                'processed_records' => $processedRecords,
                'created_at' => $task->created_at,
                'start_time' => $task->start_time,
                'end_time' => $task->end_time,
                'error_message' => $task->error_message,
            )
        );
    }
    
    /**
     * 获取任务详情（包含每个城市的处理情况）
     * @param int $taskId 任务ID
     * @return array
     */
    public static function getTaskDetails($taskId)
    {
        $task = self::model()->findByPk($taskId);
        
        if (!$task) {
            return array(
                'status' => 0,
                'message' => '任务不存在'
            );
        }
        
        // 获取任务详细信息
        $details = Yii::app()->db->createCommand()
            ->select('*')
            ->from('sal_data_migration_task_detail')
            ->where('task_id = :task_id', array(':task_id' => $taskId))
            ->order('id ASC')
            ->queryAll();
        
        $statusText = array(
            0 => '待处理',
            1 => '处理中',
            2 => '已完成',
            3 => '失败'
        );
        
        $detailsData = array();
        foreach ($details as $detail) {
            $detailsData[] = array(
                'id' => $detail['id'],
                'city_code' => $detail['city_code'],
                'city_name' => $detail['city_name'],
                'status' => $detail['status'],
                'status_text' => isset($statusText[$detail['status']]) ? $statusText[$detail['status']] : '未知',
                'log_id' => $detail['log_id'],
                'success_count' => $detail['success_count'],
                'error_count' => $detail['error_count'],
                'processed_records' => $detail['processed_records'],
                'error_message' => $detail['error_message'],
                'start_time' => $detail['start_time'],
                'end_time' => $detail['end_time'],
            );
        }
        
        $taskStatusText = array(
            self::STATUS_PENDING => '待处理',
            self::STATUS_PROCESSING => '处理中',
            self::STATUS_COMPLETED => '已完成',
            self::STATUS_FAILED => '失败',
            self::STATUS_CANCELLED => '已取消',
        );
        
        // 计算进度统计
        $totalCities = count($details);
        $completedCities = 0;
        $totalSuccess = 0;
        $totalError = 0;
        
        foreach ($details as $detail) {
            if ($detail['status'] == 2 || $detail['status'] == 3) {
                $completedCities++;
            }
            $totalSuccess += intval($detail['success_count']);
            $totalError += intval($detail['error_count']);
        }
        
        $progress = $totalCities > 0 ? round($completedCities / $totalCities * 100, 2) : 0;
        
        return array(
            'status' => 1,
            'data' => array(
                'task_id' => $task->id,
                'task_code' => $task->task_code,  // ✅ 添加任务编号
                'task_status' => $task->task_status,
                'task_status_text' => isset($taskStatusText[$task->task_status]) ? $taskStatusText[$task->task_status] : '未知',
                'migration_type' => $task->migration_type,
                'api_url' => $task->api_url,
                'total_cities' => $totalCities,  // ✅ 添加总城市数
                'completed_cities' => $completedCities,  // ✅ 添加已完成城市数
                'progress' => $progress,  // ✅ 添加进度百分比
                'success_count' => $totalSuccess,  // ✅ 添加总成功数
                'error_count' => $totalError,  // ✅ 添加总失败数
                'created_at' => $task->created_at,
                'start_time' => $task->start_time,
                'end_time' => $task->end_time,
                'details' => $detailsData
            )
        );
    }
}

/**
 * 任务明细模型
 */
class DataMigrationTaskDetail extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public function tableName()
    {
        return 'sal_data_migration_task_detail';
    }
    
    /**
     * 验证规则
     */
    public function rules()
    {
        return array(
            array('task_id, city_code, city_name, status, created_at, updated_at', 'required'),
            array('task_id, status, success_count, error_count, processed_records', 'numerical', 'integerOnly' => true),
            array('log_id, error_message, start_time, end_time', 'safe'),
        );
    }
    
    /**
     * 开始处理
     */
    public function startProcessing()
    {
        $this->status = 1; // 处理中
        $this->start_time = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        return $this->save();
    }
    
    /**
     * 完成处理
     */
    public function complete($logId, $successCount, $errorCount)
    {
        $this->status = 2; // 成功
        $this->log_id = $logId;
        $this->success_count = $successCount;
        $this->error_count = $errorCount;
        $this->end_time = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        return $this->save();
    }
    
    /**
     * 处理失败
     */
    public function fail($errorMessage)
    {
        $this->status = 3; // 失败
        $this->error_message = $errorMessage;
        $this->end_time = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        return $this->save();
    }
}
