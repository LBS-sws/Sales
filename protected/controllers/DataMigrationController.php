<?php

/**
 * 数据迁移控制器
 * 负责从派单系统导入数据到CRM系统
 */
class DataMigrationController extends Controller
{
    public $function_id = 'XF04';

    public function filters()
    {
        return array(
            'enforceRegisteredStation + index,taskList,debug',
            'enforceSessionExpiration',
            'enforceNoConcurrentLogin',
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'actions' => array('index', 'fetchData', 'validateData', 'previewData', 'importData',
                                   'getProgress', 'createAsyncTask', 'getTaskStatus', 'getTaskDetails',
                                   'getTaskList', 'retryFailed', 'resetFailed', 'clearCache', 'taskList', 'debug'),
                'users' => array('@'),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    /**
     * 显示数据迁移主页面
     */
    public function actionIndex()
    {
        $this->render('index');
    }

    /**
     * 显示任务列表页面
     */
    public function actionTaskList()
    {
        $this->render('taskList');
    }

    /**
     * 显示调试页面
     */
    public function actionDebug()
    {
        $this->render('debug');
    }

    /**
     * 获取派单系统数据
     */
    public function actionFetchData()
    {
        $model = new DataMigrationForm();

        // 设置迁移类型和参数
        $model->migration_type = Yii::app()->request->getPost('migration_type');
        $model->api_url = Yii::app()->request->getPost('api_url');

        try {
            $result = $model->fetchPaidanData(
                Yii::app()->request->getPost('office_code_ids', ''),
                Yii::app()->request->getPost('staff_ids', ''),
                Yii::app()->request->getPost('export_mode', 'city'),
                Yii::app()->request->getPost('search_keyword', ''),
                Yii::app()->request->getPost('last_sync_time', ''),
                Yii::app()->request->getPost('project_type', ''),
                Yii::app()->request->getPost('page', 1),
                Yii::app()->request->getPost('page_size', 1000)
            );

            $this->renderJSON($result);
        } catch (Exception $e) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '获取数据失败：' . $e->getMessage()
            ));
        }
    }

    /**
     * 验证数据
     */
    public function actionValidateData()
    {
        $logId = Yii::app()->request->getPost('log_id');

        if (empty($logId)) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '缺少日志ID'
            ));
            return;
        }

        $model = new DataMigrationForm();
        $result = $model->validateData($logId);

        $this->renderJSON($result);
    }

    /**
     * 预览数据
     */
    public function actionPreviewData()
    {
        $logId = Yii::app()->request->getPost('log_id');
        $page = Yii::app()->request->getPost('page', 1);
        $pageSize = Yii::app()->request->getPost('page_size', 50);

        if (empty($logId)) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '缺少日志ID'
            ));
            return;
        }

        $model = new DataMigrationForm();
        $result = $model->previewData($logId, $page, $pageSize);

        $this->renderJSON($result);
    }

    /**
     * 导入数据
     */
    public function actionImportData()
    {
        $logId = Yii::app()->request->getPost('log_id');

        if (empty($logId)) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '缺少日志ID'
            ));
            return;
        }

        $model = new DataMigrationForm();
        $model->migration_type = Yii::app()->request->getPost('migration_type');

        try {
            $result = $model->importData($logId);
            $this->renderJSON($result);
        } catch (Exception $e) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '导入失败：' . $e->getMessage()
            ));
        }
    }

    /**
     * 获取导入进度
     */
    public function actionGetProgress()
    {
        $logId = Yii::app()->request->getPost('log_id');

        if (empty($logId)) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '缺少日志ID'
            ));
            return;
        }

        $model = new DataMigrationForm();
        $result = $model->getProgress($logId);

        $this->renderJSON($result);
    }

    /**
     * 创建异步导入任务
     */
    public function actionCreateAsyncTask()
    {
        try {
            $migrationType = Yii::app()->request->getPost('migration_type');
            $apiUrl = Yii::app()->request->getPost('api_url');
            $apiConfigStr = Yii::app()->request->getPost('api_config', '{}');
            $filterParamsStr = Yii::app()->request->getPost('filter_params', '{}');
            $priority = Yii::app()->request->getPost('priority', 5);

            // 验证必填参数
            if (empty($migrationType) || empty($apiUrl)) {
                $this->renderJSON(array(
                    'status' => 0,
                    'message' => '缺少必填参数'
                ));
                return;
            }

            // ✅ 解析 JSON 参数
            $apiConfig = json_decode($apiConfigStr, true);
            $filterParams = json_decode($filterParamsStr, true);

            if (!is_array($apiConfig)) {
                $apiConfig = array();
            }
            if (!is_array($filterParams)) {
                $filterParams = array();
            }

            // ✅ 从 filter_params 中提取 type 参数（用于任务表）
            $projectType = isset($filterParams['type']) ? $filterParams['type'] : '';

            $params = array(
                'migration_type' => $migrationType,
                'api_url' => $apiUrl,
                'api_config' => $apiConfig,
                'filter_params' => $filterParams,
                'type' => $projectType,  // ⚠️ 用于任务表的 type 字段
                'priority' => $priority
            );

            // 创建异步任务
            $result = DataMigrationTask::createTask($params);

            if ($result['status']) {
                $this->renderJSON(array(
                    'status' => 1,
                    'message' => '异步任务创建成功',
                    'data' => array(
                        'task_id' => $result['task_id'],
                        'task_code' => $result['task_code'],
                        'total_cities' => $result['total_cities']
                    )
                ));
            } else {
                $this->renderJSON(array(
                    'status' => 0,
                    'message' => $result['message']
                ));
            }
        } catch (Exception $e) {
            Yii::log('创建异步任务失败：' . $e->getMessage(), 'error', 'application.controllers.DataMigrationController');
            $this->renderJSON(array(
                'status' => 0,
                'message' => '创建失败：' . $e->getMessage()
            ));
        }
    }

    /**
     * 获取异步任务状态
     */
    public function actionGetTaskStatus()
    {
        $taskId = Yii::app()->request->getParam('task_id');

        if (empty($taskId)) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '缺少任务ID'
            ));
            return;
        }

        try {
            $result = DataMigrationTask::getTaskStatus($taskId);
            $this->renderJSON($result);
        } catch (Exception $e) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '获取任务状态失败：' . $e->getMessage()
            ));
        }
    }

    /**
     * 获取异步任务详情
     */
    public function actionGetTaskDetails()
    {
        $taskId = Yii::app()->request->getParam('task_id');

        if (empty($taskId)) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '缺少任务ID'
            ));
            return;
        }

        try {
            $result = DataMigrationTask::getTaskDetails($taskId);
            $this->renderJSON($result);
        } catch (Exception $e) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '获取任务详情失败：' . $e->getMessage()
            ));
        }
    }

    /**
     * 获取任务列表
     */
    public function actionGetTaskList()
    {
        try {
            $page = Yii::app()->request->getParam('page', 1);
            $pageSize = Yii::app()->request->getParam('page_size', 20);
            $status = Yii::app()->request->getParam('status', '');
            $type = Yii::app()->request->getParam('type', '');
            $keyword = Yii::app()->request->getParam('keyword', '');

            // 构建查询条件
            $criteria = new CDbCriteria();

            if ($status !== '') {
                $criteria->compare('task_status', $status);
            }

            if ($type !== '') {
                $criteria->compare('migration_type', $type);
            }

            if ($keyword !== '') {
                $criteria->addCondition("task_code LIKE :keyword OR error_message LIKE :keyword");
                $criteria->params[':keyword'] = '%' . $keyword . '%';
            }

            $criteria->order = 'id DESC';

            // 获取总数
            $totalCount = DataMigrationTask::model()->count($criteria);

            // 分页
            $criteria->limit = $pageSize;
            $criteria->offset = ($page - 1) * $pageSize;

            // 获取任务列表
            $tasks = DataMigrationTask::model()->findAll($criteria);

            $statusMap = array(
                0 => '待处理',
                1 => '处理中',
                2 => '已完成',
                3 => '失败',
                4 => '已取消'
            );

            $typeMap = array(
                'client' => '客户',
                'clientStore' => '门店',
                'cont' => '主合约',
                'vir' => '虚拟合约'
            );

            $list = array();
            foreach ($tasks as $task) {
                $list[] = array(
                    'id' => $task->id,
                    'task_code' => $task->task_code,
                    'migration_type' => $task->migration_type,
                    'migration_type_text' => isset($typeMap[$task->migration_type]) ? $typeMap[$task->migration_type] : $task->migration_type,
                    'task_status' => $task->task_status,
                    'task_status_text' => isset($statusMap[$task->task_status]) ? $statusMap[$task->task_status] : '未知',
                    'total_cities' => $task->total_cities,
                    'completed_cities' => $task->completed_cities,
                    'success_count' => $task->success_count,
                    'error_count' => $task->error_count,
                    'created_at' => $task->created_at,
                    'start_time' => $task->start_time,
                    'end_time' => $task->end_time,
                    'error_message' => $task->error_message,
                    'progress' => $task->total_cities > 0 ? round(($task->completed_cities / $task->total_cities) * 100, 2) : 0,
                );
            }

            // 统计信息
            $stats = array(
                'total' => DataMigrationTask::model()->count(),
                'pending' => DataMigrationTask::model()->count('task_status = 0'),
                'processing' => DataMigrationTask::model()->count('task_status = 1'),
                'completed' => DataMigrationTask::model()->count('task_status = 2'),
                'failed' => DataMigrationTask::model()->count('task_status = 3'),
            );

            $this->renderJSON(array(
                'status' => 1,
                'data' => array(
                    'list' => $list,
                    'pagination' => array(
                        'total' => $totalCount,
                        'page' => intval($page),
                        'page_size' => intval($pageSize),
                        'total_pages' => ceil($totalCount / $pageSize)
                    ),
                    'stats' => $stats
                )
            ));
        } catch (Exception $e) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '获取任务列表失败：' . $e->getMessage()
            ));
        }
    }

    /**
     * 重新处理失败记录
     */
    public function actionRetryFailed()
    {
        $logId = Yii::app()->request->getPost('log_id');

        if (empty($logId)) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '缺少日志ID'
            ));
            return;
        }

        try {
            $model = new DataMigrationForm();
            $model->migration_type = Yii::app()->request->getPost('migration_type', '');
            $result = $model->retryFailed($logId);
            $this->renderJSON($result);
        } catch (Exception $e) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '重试失败：' . $e->getMessage()
            ));
        }
    }

    /**
     * 重置失败记录状态
     */
    public function actionResetFailed()
    {
        $logId = Yii::app()->request->getPost('log_id');

        if (empty($logId)) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '缺少日志ID'
            ));
            return;
        }

        try {
            $model = new DataMigrationForm();
            $result = $model->resetFailed($logId);
            $this->renderJSON($result);
        } catch (Exception $e) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '重置失败：' . $e->getMessage()
            ));
        }
    }

    /**
     * 清除PHP缓存
     */
    public function actionClearCache()
    {
        try {
            // 清除OPcache
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }

            // 清除Yii缓存
            if (Yii::app()->cache) {
                Yii::app()->cache->flush();
            }

            $this->renderJSON(array(
                'status' => 1,
                'message' => 'PHP缓存已清除'
            ));
        } catch (Exception $e) {
            $this->renderJSON(array(
                'status' => 0,
                'message' => '清除缓存失败：' . $e->getMessage()
            ));
        }
    }

    /**
     * 渲染JSON响应
     */
    private function renderJSON($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        Yii::app()->end();
    }
}

