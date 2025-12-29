<?php
/**
 * 主合同合并异步任务模型
 */
class ContMergeTask extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'sal_contract_merge_task';
    }
    
    /**
     * 创建合并任务
     */
    public static function createTask($target_cont_id, $source_cont_ids, $clue_id)
    {
        $uid = Yii::app()->user->id;
        $taskId = Yii::app()->db->createCommand()->insert('sal_contract_merge_task', array(
            'target_cont_id' => $target_cont_id,
            'source_cont_ids' => is_array($source_cont_ids) ? implode(',', $source_cont_ids) : $source_cont_ids,
            'clue_id' => $clue_id,
            'status' => 'pending', // pending, processing, completed, failed
            'progress' => 0,
            'current_step' => '等待处理',
            'logs' => '',
            'created_by' => $uid,
            'created_at' => date('Y-m-d H:i:s'),
        ));
        return Yii::app()->db->getLastInsertID();
    }
    
    /**
     * 更新任务状态
     */
    public static function updateTask($taskId, $data)
    {
        Yii::app()->db->createCommand()->update('sal_contract_merge_task', array_merge($data, array(
            'updated_at' => date('Y-m-d H:i:s'),
        )), 'id=:id', array(':id'=>$taskId));
    }
    
    /**
     * 获取任务信息
     */
    public static function getTask($taskId)
    {
        return Yii::app()->db->createCommand()
            ->select('*')
            ->from('sal_contract_merge_task')
            ->where('id=:id', array(':id'=>$taskId))
            ->queryRow();
    }
    
    /**
     * 执行合并任务
     */
    public static function executeTask($taskId)
    {
        $task = self::getTask($taskId);
        if (!$task) {
            return false;
        }
        
        // 更新为处理中
        self::updateTask($taskId, array(
            'status' => 'processing',
            'current_step' => '开始处理',
            'progress' => 5,
        ));
        
        try {
            $model = new ContMergeForm();
            $model->target_cont_id = $task['target_cont_id'];
            $model->source_cont_ids = explode(',', $task['source_cont_ids']);
            $model->clue_id = $task['clue_id'];
            $model->taskId = $taskId; // 传递任务ID，用于更新进度
            
            // 执行合并
            if ($model->batchMergeSave()) {
                // 成功
                self::updateTask($taskId, array(
                    'status' => 'completed',
                    'progress' => 100,
                    'current_step' => '完成',
                    'logs' => json_encode($model->operationLogs, JSON_UNESCAPED_UNICODE),
                    'completed_at' => date('Y-m-d H:i:s'),
                ));
                return true;
            } else {
                // 失败
                $errors = $model->getErrors();
                self::updateTask($taskId, array(
                    'status' => 'failed',
                    'progress' => 0,
                    'current_step' => '失败',
                    'logs' => json_encode(array('errors'=>$errors, 'logs'=>$model->operationLogs), JSON_UNESCAPED_UNICODE),
                    'error_message' => json_encode($errors, JSON_UNESCAPED_UNICODE),
                ));
                return false;
            }
        } catch (Exception $e) {
            // 异常
            self::updateTask($taskId, array(
                'status' => 'failed',
                'progress' => 0,
                'current_step' => '异常',
                'error_message' => $e->getMessage(),
            ));
            return false;
        }
    }
}

