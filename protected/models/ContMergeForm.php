<?php

/**
 * 主合同合并删除表单
 * 用于处理错误主合同的数据迁移和删除
 */
class ContMergeForm extends CFormModel
{
    public $source_cont_id; // 源主合同ID（要删除的，单个）
    public $source_cont_ids; // 源主合同ID数组（要删除的，多个）
    public $target_cont_id; // 目标主合同ID（保留的）
    public $clue_id; // 客户ID
    public $step = 'select'; // 步骤：select-选择, confirm-确认, merge-合并
    
    // 源主合同信息
    public $sourceContRow = array();
    // 目标主合同信息
    public $targetContRow = array();
    // 关联数据统计
    public $relatedData = array();
    // 操作日志（记录每一步的操作）
    public $operationLogs = array();
    // 任务ID（用于异步更新进度）
    public $taskId = null;
    
    public function rules()
    {
        return array(
            array('source_cont_id, source_cont_ids, target_cont_id, clue_id, step', 'safe'),
            array('source_cont_id', 'required', 'on'=>'confirm,merge'),
            array('target_cont_id', 'required', 'on'=>'confirm,merge'),
            array('source_cont_id', 'validateSourceCont', 'on'=>'confirm,merge'),
            array('target_cont_id', 'validateTargetCont', 'on'=>'confirm,merge'),
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'source_cont_id' => '要删除的主合同',
            'target_cont_id' => '目标主合同（第一步必须选择一个要保留的主合同）',
            'clue_id' => '客户ID',
        );
    }
    
    /**
     * 验证源主合同
     */
    public function validateSourceCont($attribute, $params)
    {
        if (empty($this->source_cont_id)) {
            return;
        }
        
        $row = Yii::app()->db->createCommand()
            ->select('*')
            ->from('sal_contract')
            ->where('id=:id', array(':id'=>$this->source_cont_id))
            ->queryRow();
            
        if (!$row) {
            $this->addError($attribute, '主合同不存在');
            return;
        }
        
        // 检查主合同状态，已生效的合同不允许删除
        // 已放开状态限制，允许测试
        /*
        if ($row['cont_status'] >= 10) {
            $this->addError($attribute, '该主合同已生效，不允许删除');
            return;
        }
        */
        
        $this->sourceContRow = $row;
    }
    
    /**
     * 验证目标主合同
     */
    public function validateTargetCont($attribute, $params)
    {
        if (empty($this->target_cont_id)) {
            return;
        }
        
        if ($this->source_cont_id == $this->target_cont_id) {
            $this->addError($attribute, '目标主合同不能与源主合同相同');
            return;
        }
        
        $row = Yii::app()->db->createCommand()
            ->select('*')
            ->from('sal_contract')
            ->where('id=:id', array(':id'=>$this->target_cont_id))
            ->queryRow();
            
        if (!$row) {
            $this->addError($attribute, '目标主合同不存在');
            return;
        }
        
        // 检查是否属于同一客户
        if (!empty($this->sourceContRow) && $row['clue_id'] != $this->sourceContRow['clue_id']) {
            $this->addError($attribute, '目标主合同必须与源主合同属于同一客户');
            return;
        }
        
        $this->targetContRow = $row;
    }
    
    /**
     * 根据客户ID获取所有主合同列表
     */
    public function getContractListByClueId($clue_id)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "SELECT cont.*, 
                a.clue_code, a.cust_name,
                b.name as city_name,
                CONCAT(f.name, ' (', f.code, ')') as employee_name,
                h.name as lbs_main_name,
                ye.name as yewudalei_name,
                (CASE cont.clue_type WHEN 1 THEN '地推' ELSE 'KA' END) as clue_type_name
                FROM sal_contract cont
                LEFT JOIN sal_clue a ON a.id=cont.clue_id
                LEFT JOIN security{$suffix}.sec_city b ON cont.city=b.code
                LEFT JOIN hr{$suffix}.hr_employee f ON cont.sales_id=f.id
                LEFT JOIN sal_main_lbs h ON cont.lbs_main=h.id
                LEFT JOIN sal_yewudalei ye ON cont.yewudalei=ye.id
                WHERE cont.clue_id=:clue_id
                ORDER BY cont.id DESC";
                
        return Yii::app()->db->createCommand($sql)
            ->queryAll(true, array(':clue_id'=>$clue_id));
    }
    
    /**
     * 获取主合同的关联数据统计
     */
    public function getRelatedDataStat($cont_id)
    {
        $data = array();
        
        // 1. 虚拟合同
        $virtualCount = Yii::app()->db->createCommand()
            ->select('COUNT(*) as cnt')
            ->from('sal_contract_virtual')
            ->where('cont_id=:cont_id', array(':cont_id'=>$cont_id))
            ->queryScalar();
        $data['virtual'] = array(
            'count' => $virtualCount,
            'name' => '虚拟合同',
            'table' => 'sal_contract_virtual'
        );
        
        // 2. 关联门店
        $sseCount = Yii::app()->db->createCommand()
            ->select('COUNT(*) as cnt')
            ->from('sal_contract_sse')
            ->where('cont_id=:cont_id', array(':cont_id'=>$cont_id))
            ->queryScalar();
        $data['sse'] = array(
            'count' => $sseCount,
            'name' => '关联门店',
            'table' => 'sal_contract_sse'
        );
        
        // 3. 合同附件
        $fileCount = Yii::app()->db->createCommand()
            ->select('COUNT(*) as cnt')
            ->from('sal_contract_file')
            ->where('cont_id=:cont_id', array(':cont_id'=>$cont_id))
            ->queryScalar();
        $data['file'] = array(
            'count' => $fileCount,
            'name' => '合同附件',
            'table' => 'sal_contract_file'
        );
        
        // 4. 历史记录
        $historyCount = Yii::app()->db->createCommand()
            ->select('COUNT(*) as cnt')
            ->from('sal_contract_history')
            ->where('table_type=5 AND table_id=:table_id', array(':table_id'=>$cont_id))
            ->queryScalar();
        $data['history'] = array(
            'count' => $historyCount,
            'name' => '历史记录',
            'table' => 'sal_contract_history'
        );
        
        // 5. 合同操作记录(sal_contpro)
        $contproCount = Yii::app()->db->createCommand()
            ->select('COUNT(*) as cnt')
            ->from('sal_contpro')
            ->where('cont_id=:cont_id', array(':cont_id'=>$cont_id))
            ->queryScalar();
        $data['contpro'] = array(
            'count' => $contproCount,
            'name' => '合同操作记录',
            'table' => 'sal_contpro'
        );
        
        // 6. 合同操作门店(sal_contpro_sse)
        $contproSseCount = Yii::app()->db->createCommand()
            ->select('COUNT(*) as cnt')
            ->from('sal_contpro_sse')
            ->where('cont_id=:cont_id', array(':cont_id'=>$cont_id))
            ->queryScalar();
        $data['contpro_sse'] = array(
            'count' => $contproSseCount,
            'name' => '合同操作门店',
            'table' => 'sal_contpro_sse'
        );
        
        // 7. 合同操作虚拟合同(sal_contpro_virtual)
        $contproVirCount = Yii::app()->db->createCommand()
            ->select('COUNT(*) as cnt')
            ->from('sal_contpro_virtual')
            ->where('cont_id=:cont_id', array(':cont_id'=>$cont_id))
            ->queryScalar();
        $data['contpro_virtual'] = array(
            'count' => $contproVirCount,
            'name' => '合同操作虚拟合同',
            'table' => 'sal_contpro_virtual'
        );
        
        // 8. 呼叫式服务申请(sal_contract_call)
        $callCount = Yii::app()->db->createCommand()
            ->select('COUNT(*) as cnt')
            ->from('sal_contract_call')
            ->where('cont_id=:cont_id', array(':cont_id'=>$cont_id))
            ->queryScalar();
        $data['call'] = array(
            'count' => $callCount,
            'name' => '呼叫式服务申请',
            'table' => 'sal_contract_call'
        );
        
        return $data;
    }
    
    /**
     * 获取主合同的详细关联数据列表
     */
    public function getRelatedDataDetail($cont_id)
    {
        $data = array();
        
        // 1. 虚拟合同列表
        $data['virtual'] = Yii::app()->db->createCommand()
            ->select('v.*, s.store_name, s.store_code')
            ->from('sal_contract_virtual v')
            ->leftJoin('sal_clue_store s', 'v.clue_store_id=s.id')
            ->where('v.cont_id=:cont_id', array(':cont_id'=>$cont_id))
            ->queryAll();
        
        // 2. 关联门店列表
        $data['sse'] = Yii::app()->db->createCommand()
            ->select('sse.*, s.store_name, s.store_code')
            ->from('sal_contract_sse sse')
            ->leftJoin('sal_clue_store s', 'sse.clue_store_id=s.id')
            ->where('sse.cont_id=:cont_id', array(':cont_id'=>$cont_id))
            ->queryAll();
        
        // 3. 合同附件列表
        $data['file'] = Yii::app()->db->createCommand()
            ->select('*')
            ->from('sal_contract_file')
            ->where('cont_id=:cont_id', array(':cont_id'=>$cont_id))
            ->queryAll();
        
        // 4. 历史记录列表
        $data['history'] = Yii::app()->db->createCommand()
            ->select('*')
            ->from('sal_contract_history')
            ->where('table_type=5 AND table_id=:table_id', array(':table_id'=>$cont_id))
            ->queryAll();
        
        // 5. 合同操作记录
        $data['contpro'] = Yii::app()->db->createCommand()
            ->select('*')
            ->from('sal_contpro')
            ->where('cont_id=:cont_id', array(':cont_id'=>$cont_id))
            ->queryAll();
        
        // 6. 呼叫式服务申请
        $data['call'] = Yii::app()->db->createCommand()
            ->select('*')
            ->from('sal_contract_call')
            ->where('cont_id=:cont_id', array(':cont_id'=>$cont_id))
            ->queryAll();
        
        return $data;
    }
    
    /**
     * 更新任务进度（如果有taskId）
     */
    private function updateTaskProgress($progress, $step)
    {
        if (!empty($this->taskId)) {
            ContMergeTask::updateTask($this->taskId, array(
                'progress' => $progress,
                'current_step' => $step,
                'logs' => json_encode($this->operationLogs, JSON_UNESCAPED_UNICODE),
            ));
        }
    }
    
    /**
     * 执行数据迁移和删除（单个合同调用此方法，自动转为批量处理）
     */
    public function mergeSave()
    {
        // 如果只有单个source_cont_id，转换为数组
        if (!empty($this->source_cont_id) && empty($this->source_cont_ids)) {
            $this->source_cont_ids = array($this->source_cont_id);
        }
        // 调用批量处理方法
        return $this->batchMergeSave();
    }
    
    /**
     * 批量执行数据迁移和删除（处理多个源主合同）
     */
    public function batchMergeSave()
    {
        $connection = Yii::app()->db;
        $transaction = $connection->beginTransaction();
        
        try {
            $uid = Yii::app()->user->id;
            $this->operationLogs = array(); // 初始化日志
            
            if (empty($this->source_cont_ids)) {
                throw new Exception('没有要删除的源主合同');
            }
            
            $sourceContInfos = array(); // 存储所有源主合同信息
            
            // 0. 获取所有源主合同信息
            foreach ($this->source_cont_ids as $source_id) {
                $row = $connection->createCommand()
                    ->select('*')
                    ->from('sal_contract')
                    ->where('id=:id', array(':id'=>$source_id))
                    ->queryRow();
                if ($row) {
                    $sourceContInfos[$source_id] = $row;
                }
            }
            $this->operationLogs[] = array('step'=>'准备阶段', 'count'=>count($sourceContInfos), 'status'=>'success', 'msg'=>'已加载所有源主合同信息');
            $this->updateTaskProgress(10, '准备阶段完成');
            
            // 1. 批量迁移所有源主合同的虚拟合同
            $sourceContIdStr = implode(',', $this->source_cont_ids);
            $virRows = $connection->createCommand()
                ->select('id, vir_code, cont_id')
                ->from('sal_contract_virtual')
                ->where("cont_id IN ({$sourceContIdStr})")
                ->queryAll();
            $virCount = count($virRows);
            
            if ($virCount > 0) {
                $virCodes = array();
                foreach ($virRows as $vir) {
                    $virCodes[] = $vir['vir_code'] . "(cont_id:{$vir['cont_id']})";
                }
                $affectedRows = $connection->createCommand()->update('sal_contract_virtual', array(
                    'cont_id' => $this->target_cont_id,
                    'luu' => $uid,
                ), "cont_id IN ({$sourceContIdStr})");
                $this->operationLogs[] = array('step'=>'批量迁移虚拟合同', 'count'=>$virCount, 'status'=>'success', 'msg'=>"实际更新{$affectedRows}条：" . implode(', ', $virCodes));
            } else {
                $this->operationLogs[] = array('step'=>'批量迁移虚拟合同', 'count'=>0, 'status'=>'success', 'msg'=>'无需迁移');
            }
            
            // 2. 批量迁移所有源主合同的关联门店（需要检查重复）
            $sseRows = $connection->createCommand()
                ->select('*')
                ->from('sal_contract_sse')
                ->where("cont_id IN ({$sourceContIdStr})")
                ->queryAll();
            
            $sseProcessed = 0;
            $sseMerged = 0;
            foreach ($sseRows as $sseRow) {
                // 检查目标主合同是否已有该门店
                $existRow = $connection->createCommand()
                    ->select('id')
                    ->from('sal_contract_sse')
                    ->where('cont_id=:cont_id AND clue_store_id=:clue_store_id', array(
                        ':cont_id'=>$this->target_cont_id,
                        ':clue_store_id'=>$sseRow['clue_store_id']
                    ))
                    ->queryRow();
                
                if ($existRow) {
                    // 如果已存在，更新该记录，然后删除源记录
                    $connection->createCommand()->update('sal_contract_sse', array(
                        'busine_id' => $sseRow['busine_id'],
                        'busine_id_text' => $sseRow['busine_id_text'],
                        'store_amt' => $sseRow['store_amt'],
                        'service_sum' => $sseRow['service_sum'],
                        'detail_json' => $sseRow['detail_json'],
                        'luu' => $uid,
                    ), 'id=:id', array(':id'=>$existRow['id']));
                    
                    // 更新虚拟合同的sse_id
                    $connection->createCommand()->update('sal_contract_virtual', array(
                        'sse_id' => $existRow['id'],
                        'luu' => $uid,
                    ), 'cont_id=:cont_id AND sse_id=:sse_id', array(
                        ':cont_id'=>$this->target_cont_id,
                        ':sse_id'=>$sseRow['id']
                    ));
                    
                    // 删除源记录
                    $connection->createCommand()->delete('sal_contract_sse', 'id=:id', array(':id'=>$sseRow['id']));
                    $sseMerged++;
                } else {
                    // 如果不存在，直接迁移
                    $connection->createCommand()->update('sal_contract_sse', array(
                        'cont_id' => $this->target_cont_id,
                        'luu' => $uid,
                    ), 'id=:id', array(':id'=>$sseRow['id']));
                    $sseProcessed++;
                }
            }
            $this->operationLogs[] = array('step'=>'批量迁移关联门店', 'count'=>count($sseRows), 'status'=>'success', 'msg'=>"直接迁移{$sseProcessed}条，合并{$sseMerged}条");
            $this->updateTaskProgress(30, '关联门店迁移完成');
            
            // 3. 批量迁移所有源主合同的附件
            $fileCount = $connection->createCommand()
                ->select('COUNT(*)')
                ->from('sal_contract_file')
                ->where("cont_id IN ({$sourceContIdStr})")
                ->queryScalar();
            $connection->createCommand()->update('sal_contract_file', array(
                'cont_id' => $this->target_cont_id,
                'luu' => $uid,
            ), "cont_id IN ({$sourceContIdStr})");
            $this->operationLogs[] = array('step'=>'批量迁移合同附件', 'count'=>$fileCount, 'status'=>'success');
            
            // 4. 批量迁移所有源主合同的历史记录
            $historyCount = $connection->createCommand()
                ->select('COUNT(*)')
                ->from('sal_contract_history')
                ->where("table_type=5 AND table_id IN ({$sourceContIdStr})")
                ->queryScalar();
            $connection->createCommand()->update('sal_contract_history', array(
                'table_id' => $this->target_cont_id,
                'luu' => $uid,
            ), "table_type=5 AND table_id IN ({$sourceContIdStr})");
            $this->operationLogs[] = array('step'=>'批量迁移历史记录', 'count'=>$historyCount, 'status'=>'success');
            
            // 5. 批量迁移所有源主合同的操作记录
            $proCount = $connection->createCommand()
                ->select('COUNT(*)')
                ->from('sal_contpro')
                ->where("cont_id IN ({$sourceContIdStr})")
                ->queryScalar();
            $connection->createCommand()->update('sal_contpro', array(
                'cont_id' => $this->target_cont_id,
                'luu' => $uid,
            ), "cont_id IN ({$sourceContIdStr})");
            $this->operationLogs[] = array('step'=>'批量迁移合同操作记录', 'count'=>$proCount, 'status'=>'success');
            
            // 6. 批量迁移所有源主合同的操作门店
            $proSseCount = $connection->createCommand()
                ->select('COUNT(*)')
                ->from('sal_contpro_sse')
                ->where("cont_id IN ({$sourceContIdStr})")
                ->queryScalar();
            $connection->createCommand()->update('sal_contpro_sse', array(
                'cont_id' => $this->target_cont_id,
                'luu' => $uid,
            ), "cont_id IN ({$sourceContIdStr})");
            $this->operationLogs[] = array('step'=>'批量迁移合同操作门店', 'count'=>$proSseCount, 'status'=>'success');
            
            // 7. 批量迁移所有源主合同的操作虚拟合同
            $proVirCount = $connection->createCommand()
                ->select('COUNT(*)')
                ->from('sal_contpro_virtual')
                ->where("cont_id IN ({$sourceContIdStr})")
                ->queryScalar();
            $connection->createCommand()->update('sal_contpro_virtual', array(
                'cont_id' => $this->target_cont_id,
                'luu' => $uid,
            ), "cont_id IN ({$sourceContIdStr})");
            $this->operationLogs[] = array('step'=>'批量迁移合同操作虚拟合同', 'count'=>$proVirCount, 'status'=>'success');
            
            // 8. 批量迁移所有源主合同的呼叫式服务申请
            $callCount = $connection->createCommand()
                ->select('COUNT(*)')
                ->from('sal_contract_call')
                ->where("cont_id IN ({$sourceContIdStr})")
                ->queryScalar();
            $connection->createCommand()->update('sal_contract_call', array(
                'cont_id' => $this->target_cont_id,
                'luu' => $uid,
            ), "cont_id IN ({$sourceContIdStr})");
            $this->operationLogs[] = array('step'=>'批量迁移呼叫式服务申请', 'count'=>$callCount, 'status'=>'success');
            $this->updateTaskProgress(70, '数据迁移完成');
            
            // 10. 确认所有虚拟合同都已迁移（安全检查）
            $remainVirRows = $connection->createCommand()
                ->select('id, vir_code, cont_id')
                ->from('sal_contract_virtual')
                ->where("cont_id IN ({$sourceContIdStr})")
                ->queryAll();
            
            if (!empty($remainVirRows)) {
                $virCodes = array();
                foreach ($remainVirRows as $vir) {
                    $virCodes[] = $vir['vir_code'] . "(ID:{$vir['id']}, cont_id:{$vir['cont_id']})";
                }
                $msg = '虚拟合同迁移失败，仍有' . count($remainVirRows) . '条未迁移：' . implode(', ', $virCodes);
                $this->operationLogs[] = array('step'=>'数据检查', 'count'=>count($remainVirRows), 'status'=>'error', 'msg'=>$msg);
                throw new Exception($msg);
            }
            $this->operationLogs[] = array('step'=>'数据检查', 'count'=>0, 'status'=>'success', 'msg'=>'所有数据已成功迁移');
            
            // 11. 批量删除所有源主合同
            $deletedCount = $connection->createCommand()->delete('sal_contract', "id IN ({$sourceContIdStr})");
            $contCodes = array();
            foreach ($sourceContInfos as $id => $info) {
                $contCodes[] = $info['cont_code'] . "(ID:{$id})";
            }
            $this->operationLogs[] = array('step'=>'批量删除源主合同', 'count'=>$deletedCount, 'status'=>'success', 'msg'=>implode(', ', $contCodes));
            $this->updateTaskProgress(90, '源主合同已删除');
            
            // 12. 添加操作日志到目标主合同
            $historyMsg = "合并删除：从 " . count($this->source_cont_ids) . " 个主合同（" . implode(', ', $contCodes) . "）迁移数据";
            $connection->createCommand()->insert('sal_contract_history', array(
                'table_id' => $this->target_cont_id,
                'table_type' => 5,
                'history_type' => 99,
                'history_html' => "<span>{$historyMsg}</span>",
                'lcu' => $uid,
            ));
            
            $transaction->commit();
            
            // 13. 同步到派单系统（事务提交后）
            $this->syncToDispatchSystem();
            $this->operationLogs[] = array('step'=>'同步派单系统', 'count'=>0, 'status'=>'success');
            
            return true;
        } catch (Exception $e) {
            $transaction->rollback();
            $this->operationLogs[] = array('step'=>'执行失败', 'count'=>0, 'status'=>'error', 'msg'=>$e->getMessage());
            $this->addError('source_cont_id', '数据迁移失败：'.$e->getMessage());
            return false;
        }
    }
    
    /**
     * 同步到派单系统
     * 主合同合并后，需要通知派单系统更新虚拟合同的信息
     */
    private function syncToDispatchSystem()
    {
        try {
            // 获取目标主合同下已同步到派单系统的虚拟合同（有u_id的）
            $suffix = Yii::app()->params['envSuffix'];
            $virRows = Yii::app()->db->createCommand()
                ->select("id, u_id, vir_code")
                ->from("sales{$suffix}.sal_contract_virtual")
                ->where("cont_id=:cont_id AND u_id IS NOT NULL", array(':cont_id' => $this->target_cont_id))
                ->queryAll();

            if (!empty($virRows)) {
                // 使用派单系统同步类更新虚拟合同
                $curlModel = new CurlNotesByVir();
                $curlModel->sendAllVirByContIDAndUpdate($this->target_cont_id);
                
                // 记录同步日志
                Yii::log(
                    "主合同合并-派单系统同步: 目标主合同ID={$this->target_cont_id}, 源主合同ID={$this->source_cont_id}, 虚拟合同数量=" . count($virRows),
                    CLogger::LEVEL_INFO,
                    'contract.merge.sync'
                );
            }
        } catch (Exception $e) {
            // 派单系统同步失败不影响主流程，只记录日志
            Yii::log(
                "主合同合并-派单系统同步失败: " . $e->getMessage(),
                CLogger::LEVEL_ERROR,
                'contract.merge.sync'
            );
            // 不抛出异常，避免影响主流程
        }
    }
}

