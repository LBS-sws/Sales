<?php

class IqueueListNew extends CListPageModel
{
    protected $removeEpx="";//删除的额外sql

    public function attributeLabels()
    {
        return array(
            'import_type'=>"导入类型",
            'req_dt'=>Yii::t('import','Req. Date'),
            'fin_dt'=>Yii::t('import','Comp. Date'),
            'status'=>Yii::t('import','Status'),
            'success_num'=>"成功数量",
            'error_num'=>"失败数量",
            'import_name'=>"文件名",
            'message'=>"执行说明",
            'error_file'=>"下载异常",
            'id'=>Yii::t('import','ID'),
            'username'=>"导入用户",
        );
    }

    public function retrieveDataByPage($pageNum=1)
    {
        $uid = Yii::app()->user->id;
        // xiangsong 账号可以看到所有数据
        $whereClause = "a.status<>'N'";
        if ($uid != "xiangsong") {
            $whereClause .= " and a.username='".$uid."'";
        }
        $sql1 = "select a.id,a.import_type,a.req_dt,a.fin_dt,a.status,a.success_num,a.error_num,a.import_name,a.message,a.username
				from sal_import_queue a 
				where ".$whereClause." 
			";
        $sql2 = "select count(a.id)
				from sal_import_queue a 
				where ".$whereClause." 
			";
        $clause = "";
        if (!empty($this->searchField) && !empty($this->searchValue)) {
            $svalue = str_replace("'","\'",$this->searchValue);
            switch ($this->searchField) {
                case 'import_type':
                    $clause .= General::getSqlConditionClause('a.import_type',$svalue);
                    break;
                case 'status':
                    $clause .= General::getSqlConditionClause('a.status',$svalue);
                    break;
                case 'username':
                    $clause .= General::getSqlConditionClause('a.username',$svalue);
                    break;
            }
        }

        $order = "";
        if (!empty($this->orderField)) {
            $order .= " order by ".$this->orderField." ";
            if ($this->orderType=='D') $order .= "desc ";
        } else {
            $order .= " order by a.req_dt desc ";
        }

        $sql = $sql2.$clause;
        $this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();

        $sql = $sql1.$clause.$order;
        $sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
        $records = Yii::app()->db->createCommand($sql)->queryAll();

        $list = array();
        $this->attr = array();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                //a.id,a.import_type,a.req_dt,a.fin_dt,a.status,a.success_num,a.error_num,a.import_name,a.message,a.username
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'import_type'=>self::getImportTypeStrByType($record['import_type']),
                    'req_dt'=>$record['req_dt'],
                    'fin_dt'=>$record['fin_dt'],
                    'status'=>$record['status'],
                    'success_num'=>$record['success_num'],
                    'error_num'=>$record['error_num'],
                    'import_name'=>$record['import_name'],
                    'message'=>$record['message'],
                    'username'=>$record['username'],
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_xf01'] = $this->getCriteria();
        return true;
    }

    public static function getImportTypeStrByType($type) {
        $rtn = array();
        $rtn["client"] = "导入派单客户";
        $rtn["clientStore"] = "导入派单门店";
        $rtn["cont"] = "导入派单主合约";
        $rtn["vir"] = "导入派单虚拟合约";
        $rtn["clueBox"] = "线索池导入客户";
        $rtn["clue"] = "线索导入客户";
        $rtn["clueStore"] = "导入门店";
        if(key_exists($type,$rtn)){
            return $rtn[$type];
        }
        return $type;
    }

    public function remove($index) {
        // 先尝试从 sal_data_migration_log 查询（新的派单数据导入）
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_data_migration_log")
            ->where("id=:id",array(":id"=>$index))->queryRow();
        
        // 如果没找到，再从 sal_import_queue 查询（旧的导入方式）
        if (!$row) {
            $row = Yii::app()->db->createCommand()->select("*")->from("sal_import_queue")
                ->where("id=:id",array(":id"=>$index))->queryRow();
        }
        
        if($row){
            $connection = Yii::app()->db;
            $transaction = $connection->beginTransaction();
            try {
                $message = "";
                switch ($row["migration_type"]){
                    case "clientStore":
                        // 先检查是否有要删除的数据
                        $checkClientRows = $connection->createCommand()->select("id")->from("sal_clue")
                            ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                        $checkStoreRows = $connection->createCommand()->select("id")->from("sal_clue_store")
                            ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                        if (empty($checkClientRows) && empty($checkStoreRows)) {
                            $message = "未找到要删除的客户和门店数据（可能已被删除）";
                        } else {
                            $this->removeClient($index, $connection);
                            // 查询门店记录
                            $storeRows = $connection->createCommand()->select("id")->from("sal_clue_store")
                                ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                            if($storeRows) {
                                $storeIds = array();
                                foreach($storeRows as $row) {
                                    $storeIds[] = $row['id'];
                                }
                                if (!empty($storeIds)) {
                                    $storeIdsStr = implode(',', $storeIds);
                                    // 注意：以下关联表没有 report_id 字段，通过 clue_store_id 关联删除
                                    // 这是合理的，因为门店记录被删除后，其关联数据也应该被删除（级联删除逻辑）
                                    // 删除门店关联的联络人
                                    $connection->createCommand("DELETE FROM sal_clue_person WHERE clue_store_id IN ({$storeIdsStr}) AND clue_store_id!=0")->execute();
                                    // 删除门店关联的商机门店表
                                    $connection->createCommand("DELETE FROM sal_clue_sre_soe WHERE clue_store_id IN ({$storeIdsStr})")->execute();
                                    // 删除门店历史记录（table_type=2:门店记录）
                                    $connection->createCommand("DELETE FROM sal_clue_history WHERE table_type=2 AND table_id IN ({$storeIdsStr})")->execute();
                                }
                                // 删除门店主表
                                $connection->createCommand()->delete("sal_clue_store","report_id=:report_id".$this->removeEpx,array(":report_id"=>$index));
                            }
                            $removeRows = $connection->createCommand()->select("id")->from("sal_clue")
                                ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                            $message = "已删除门店导入记录，共删除 " . count($checkClientRows) . " 条客户记录，" . count($checkStoreRows) . " 条门店记录";
                        }
                        break;
                    case "client":
                        // 先检查是否有要删除的数据
                        $checkRows = $connection->createCommand()->select("id")->from("sal_clue")
                            ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                        if (empty($checkRows)) {
                            $message = "未找到要删除的客户数据（可能已被删除）";
                        } else {
                            $count = $this->removeClient($index, $connection);
                            $message = "已删除客户导入记录，共删除 " . $count . " 条客户记录";
                        }
                        break;
                    case "cont":
                        // 先检查是否有要删除的数据
                        $checkRows = $connection->createCommand()->select("id")->from("sal_contract")
                            ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                        if (empty($checkRows)) {
                            $message = "未找到要删除的合约数据（可能已被删除）";
                        } else {
                            $removeCount = $this->removeCont($index, $connection);
                            $message = "已删除合约导入记录，共删除 " . $removeCount . " 条合约记录";
                        }
                        break;
                    case "vir":
                        $removeCount = $this->removeCont($index, $connection);
                        $removeRows = $connection->createCommand()->select("id,cont_id,clue_id,clue_service_id,clue_store_id")->from("sal_contract_virtual")
                            ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                        if($removeRows){
                            $virIds = array();
                            foreach($removeRows as $row) {
                                $virIds[] = $row['id'];
                            }
                            if (!empty($virIds)) {
                                $virIdsStr = implode(',', $virIds);
                                // 注意：以下关联表没有 report_id 字段，通过 virtual_id/vir_id 关联删除
                                // 这是合理的，因为虚拟合约记录被删除后，其关联数据也应该被删除（级联删除逻辑）
                                // 先删除虚拟合约的关联表
                                $connection->createCommand("DELETE FROM sal_contract_vir_info WHERE virtual_id IN ({$virIdsStr})")->execute();
                                $connection->createCommand("DELETE FROM sal_contract_vir_staff WHERE vir_id IN ({$virIdsStr})")->execute();
                                $connection->createCommand("DELETE FROM sal_contract_vir_week WHERE vir_id IN ({$virIdsStr})")->execute();
                                $connection->createCommand("DELETE FROM sal_contpro_virtual WHERE vir_id IN ({$virIdsStr})")->execute();
                                // 删除虚拟合约历史记录（table_type=7:虚拟合约记录, table_type=8:虚拟合约变更记录）
                                $connection->createCommand("DELETE FROM sal_contract_history WHERE (table_type=7 OR table_type=8) AND table_id IN ({$virIdsStr})")->execute();
                            }
                            // 删除虚拟合约主表
                            $connection->createCommand()->delete("sal_contract_virtual","report_id=:report_id".$this->removeEpx,array(":report_id"=>$index));
                            foreach ($removeRows as $removeRow){
                                $connection->createCommand()->delete("sal_contpro_sse","cont_id=:cont_id and clue_store_id=:clue_store_id",array(":cont_id"=>$removeRow["cont_id"],":clue_store_id"=>$removeRow["clue_store_id"]));
                                $connection->createCommand()->delete("sal_contract_sse","cont_id=:cont_id and clue_store_id=:clue_store_id",array(":cont_id"=>$removeRow["cont_id"],":clue_store_id"=>$removeRow["clue_store_id"]));
                                if (!empty($removeRow["clue_id"])) {
                                    $connection->createCommand()->update("sal_clue",array(
                                        "clue_status"=>ClueVirProModel::getClientStatusByClueID($removeRow["clue_id"]),
                                    ),"id=:id",array(":id"=>$removeRow["clue_id"]));
                                }
                                if (!empty($removeRow["clue_store_id"])) {
                                    $connection->createCommand()->update("sal_clue_store",array(
                                        "store_status"=>ClueVirProModel::getStoreStatusByStoreID($removeRow["clue_store_id"]),
                                    ),"id=:id",array(":id"=>$removeRow["clue_store_id"]));
                                }
                            }
                        }
                        $message = "已删除虚拟合约导入记录，共删除 " . count($removeRows) . " 条记录";
                        break;
                    default:
                        $transaction->rollback();
                        return false;
                }
                // 更新状态为 'N' 或 'C'（已取消），这样记录就不会在列表中显示了
                // 先尝试更新 sal_data_migration_log（新表）
                $updateCount = $connection->createCommand()->update("sal_data_migration_log",array(
                    "status"=>"C",  // C=已取消
                    "message"=>"已被删除（{$this->removeEpx}）",
                    "lcu"=>Yii::app()->user->id,
                    "lud"=>date('Y-m-d H:i:s'),
                ),"id=:id",array(":id"=>$index));
                
                // 如果新表没更新到，再更新旧表 sal_import_queue
                if ($updateCount == 0) {
                    $connection->createCommand()->update("sal_import_queue",array(
                        "status"=>"N",
                        "message"=>"已被删除（{$this->removeEpx}）",
                    ),"id=:id",array(":id"=>$index));
                }
                
                $transaction->commit();
                return $message;
            } catch (Exception $e) {
                $transaction->rollback();
                return false;
            }
        }else{
            return false;
        }
    }

    protected function removeClient($report_id, $connection = null){
        if ($connection === null) {
            $connection = Yii::app()->db;
        }
        // 获取导入队列信息，用于判断关联数据的创建时间范围
        $importQueue = $connection->createCommand()->select("req_dt,fin_dt,username")->from("sal_import_queue")
            ->where("id=:id",array(":id"=>$report_id))->queryRow();
        $importStartTime = $importQueue ? $importQueue['req_dt'] : null;
        $importEndTime = $importQueue ? ($importQueue['fin_dt'] ? $importQueue['fin_dt'] : date('Y-m-d H:i:s')) : null;
        
        // 先查询所有需要删除的记录（包括 group_bool='Y' 和 group_bool!='Y' 的）
        $allRemoveRows = $connection->createCommand()->select("id")->from("sal_clue")
            ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$report_id))->queryAll();
        
        // 先删除关联表数据
        if($allRemoveRows) {
            $clueIds = array();
            foreach($allRemoveRows as $row) {
                $clueIds[] = $row['id'];
            }
            if (!empty($clueIds)) {
                $clueIdsStr = implode(',', $clueIds);
                // 删除关联表数据（这些表没有 report_id 字段，通过 clue_id 关联删除）
                // 注意：这里的 clue_id 都是通过 report_id 查询出来的（第226-227行），所以都是导入的数据
                // 删除这些关联数据是安全的，不会误删其他数据
                // 这是合理的，因为客户记录被删除后，其关联数据也应该被删除（级联删除逻辑）
                $connection->createCommand("DELETE FROM sal_clue_u_staff WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_u_area WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_person WHERE clue_id IN ({$clueIdsStr})")->execute();
                // 注意：sal_clue_store 有 report_id 字段，但不在 removeClient 中删除
                // 原因：1) client 导入类型中，门店可能没有 report_id（手动添加），不应该删除
                //       2) clientStore 导入类型中，门店会在 case 中单独通过 report_id 删除（第141行）
                $connection->createCommand("DELETE FROM sal_clue_rpt WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_rpt_file WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_file WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_flow WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_invoice WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_sre_soe WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_history WHERE table_type=1 AND table_id IN ({$clueIdsStr})")->execute();
                // 删除客户标签映射表（如果存在）
                $connection->createCommand("DELETE FROM sal_clue_tag_map WHERE clue_id IN ({$clueIdsStr})")->execute();
            }
            // 删除商机表（通过 report_id 关联）
            $connection->createCommand()->delete("sal_clue_service","report_id=:report_id".$this->removeEpx,array(":report_id"=>$report_id));
        }
        
        // 最后删除主表数据
        $connection->createCommand()->delete("sal_clue","report_id=:report_id".$this->removeEpx,array(":report_id"=>$report_id));
        
        return count($allRemoveRows);
    }

    protected function removeCont($report_id, $connection = null){
        if ($connection === null) {
            $connection = Yii::app()->db;
        }
        $index=$report_id;
        $removeRows = $connection->createCommand()->select("id,clue_id,clue_service_id")->from("sal_contract")
            ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
        if($removeRows){
            // 先删除关联表数据
            $contIds = array();
            foreach($removeRows as $row) {
                $contIds[] = $row['id'];
            }
            if (!empty($contIds)) {
                $contIdsStr = implode(',', $contIds);
                $connection->createCommand("DELETE FROM sal_contract_virtual WHERE cont_id IN ({$contIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_contpro WHERE cont_id IN ({$contIdsStr})")->execute();
                // 注意：以下关联表没有 report_id 字段，通过 cont_id 关联删除
                // 这是合理的，因为合约记录被删除后，其关联数据也应该被删除（级联删除逻辑）
                $connection->createCommand("DELETE FROM sal_contpro_virtual WHERE cont_id IN ({$contIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_contract_file WHERE cont_id IN ({$contIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_contpro_file WHERE cont_id IN ({$contIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_contpro_sse WHERE cont_id IN ({$contIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_contract_sse WHERE cont_id IN ({$contIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_contract_call WHERE cont_id IN ({$contIdsStr})")->execute();
                // 删除合约历史记录（table_type=5:主合约记录, table_type=6:主合约变更记录）
                $connection->createCommand("DELETE FROM sal_contract_history WHERE (table_type=5 OR table_type=6) AND table_id IN ({$contIdsStr})")->execute();
            }
            // 删除虚拟合约相关数据
            $connection->createCommand("DELETE a FROM sal_contract_vir_info a LEFT JOIN sal_contract_virtual b ON a.virtual_id=b.id WHERE b.id is null;")->execute();
            $connection->createCommand("DELETE a FROM sal_contract_vir_staff a LEFT JOIN sal_contract_virtual b ON a.vir_id=b.id WHERE b.id is null;")->execute();
            $connection->createCommand("DELETE a FROM sal_contract_vir_week a LEFT JOIN sal_contract_virtual b ON a.vir_id=b.id WHERE b.id is null;")->execute();
            // 删除主表数据
            $connection->createCommand()->delete("sal_contract","report_id=:report_id".$this->removeEpx,array(":report_id"=>$index));
            // 更新关联的线索状态
            foreach ($removeRows as $removeRow){
                if (!empty($removeRow["clue_service_id"])) {
                    $connection->createCommand()->delete("sal_clue_service","id=:id",array(":id"=>$removeRow["clue_service_id"]));
                }
                if (!empty($removeRow["clue_id"])) {
                    $connection->createCommand()->update("sal_clue",array(
                        "clue_status"=>ClueVirProModel::getClientStatusByClueID($removeRow["clue_id"]),
                    ),"id=:id",array(":id"=>$removeRow["clue_id"]));
                }
            }
        }
        return count($removeRows);
    }
    
    /**
     * 超级删除：删除所有导入数据（report_id > 5001）
     * ⚠️ 危险操作！仅超级管理员可用
     */
    public function superRemove() {
        $connection = Yii::app()->db;
        $transaction = $connection->beginTransaction();
        
        try {
            $totalDeleted = array(
                'client' => 0,
                'clientStore' => 0,
                'cont' => 0,
                'vir' => 0,
            );
            
            // 1. 删除所有客户数据 (report_id > 5001)
            $clientRows = $connection->createCommand()
                ->select("id")
                ->from("sal_clue")
                ->where("report_id > 5001".$this->removeEpx)
                ->queryAll();
            
            if (!empty($clientRows)) {
                $clueIds = array();
                foreach ($clientRows as $row) {
                    $clueIds[] = $row['id'];
                }
                
                if (!empty($clueIds)) {
                    $clueIdsStr = implode(',', $clueIds);
                    // 删除客户关联数据
                    $connection->createCommand("DELETE FROM sal_clue_u_staff WHERE clue_id IN ({$clueIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_clue_u_area WHERE clue_id IN ({$clueIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_clue_person WHERE clue_id IN ({$clueIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_clue_rpt WHERE clue_id IN ({$clueIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_clue_rpt_file WHERE clue_id IN ({$clueIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_clue_file WHERE clue_id IN ({$clueIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_clue_flow WHERE clue_id IN ({$clueIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_clue_invoice WHERE clue_id IN ({$clueIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_clue_sre_soe WHERE clue_id IN ({$clueIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_clue_history WHERE table_type=1 AND table_id IN ({$clueIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_clue_tag_map WHERE clue_id IN ({$clueIdsStr})")->execute();
                }
                
                // 删除商机
                $connection->createCommand()->delete("sal_clue_service", "report_id > 5001".$this->removeEpx);
                // 删除客户主表
                $connection->createCommand()->delete("sal_clue", "report_id > 5001".$this->removeEpx);
                $totalDeleted['client'] = count($clientRows);
            }
            
            // 2. 删除所有门店数据 (report_id > 5001)
            $storeRows = $connection->createCommand()
                ->select("id")
                ->from("sal_clue_store")
                ->where("report_id > 5001".$this->removeEpx)
                ->queryAll();
            
            if (!empty($storeRows)) {
                $storeIds = array();
                foreach ($storeRows as $row) {
                    $storeIds[] = $row['id'];
                }
                
                if (!empty($storeIds)) {
                    $storeIdsStr = implode(',', $storeIds);
                    // 删除门店关联数据
                    $connection->createCommand("DELETE FROM sal_clue_person WHERE clue_store_id IN ({$storeIdsStr}) AND clue_store_id!=0")->execute();
                    $connection->createCommand("DELETE FROM sal_clue_sre_soe WHERE clue_store_id IN ({$storeIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_clue_history WHERE table_type=2 AND table_id IN ({$storeIdsStr})")->execute();
                }
                
                // 删除门店主表
                $connection->createCommand()->delete("sal_clue_store", "report_id > 5001".$this->removeEpx);
                $totalDeleted['clientStore'] = count($storeRows);
            }
            
            // 3. 删除所有主合约数据 (report_id > 5001)
            $contRows = $connection->createCommand()
                ->select("id,clue_id,clue_service_id")
                ->from("sal_contract")
                ->where("report_id > 5001".$this->removeEpx)
                ->queryAll();
            
            if (!empty($contRows)) {
                $contIds = array();
                foreach ($contRows as $row) {
                    $contIds[] = $row['id'];
                }
                
                if (!empty($contIds)) {
                    $contIdsStr = implode(',', $contIds);
                    // 删除合约关联数据
                    $connection->createCommand("DELETE FROM sal_contract_virtual WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contpro WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contpro_virtual WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contract_file WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contpro_file WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contpro_sse WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contract_sse WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contract_call WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contract_history WHERE (table_type=5 OR table_type=6) AND table_id IN ({$contIdsStr})")->execute();
                }
                
                // 删除孤儿虚拟合约数据
                $connection->createCommand("DELETE a FROM sal_contract_vir_info a LEFT JOIN sal_contract_virtual b ON a.virtual_id=b.id WHERE b.id is null;")->execute();
                $connection->createCommand("DELETE a FROM sal_contract_vir_staff a LEFT JOIN sal_contract_virtual b ON a.vir_id=b.id WHERE b.id is null;")->execute();
                $connection->createCommand("DELETE a FROM sal_contract_vir_week a LEFT JOIN sal_contract_virtual b ON a.vir_id=b.id WHERE b.id is null;")->execute();
                
                // 删除合约主表
                $connection->createCommand()->delete("sal_contract", "report_id > 5001".$this->removeEpx);
                
                // 删除关联的商机
                foreach ($contRows as $row) {
                    if (!empty($row['clue_service_id'])) {
                        $connection->createCommand()->delete("sal_clue_service", "id=:id", array(":id" => $row['clue_service_id']));
                    }
                }
                
                $totalDeleted['cont'] = count($contRows);
            }
            
            // 4. 删除所有虚拟合约数据 (report_id > 5001)
            $virRows = $connection->createCommand()
                ->select("id,cont_id,clue_id,clue_store_id")
                ->from("sal_contract_virtual")
                ->where("report_id > 5001".$this->removeEpx)
                ->queryAll();
            
            if (!empty($virRows)) {
                $virIds = array();
                foreach ($virRows as $row) {
                    $virIds[] = $row['id'];
                }
                
                if (!empty($virIds)) {
                    $virIdsStr = implode(',', $virIds);
                    // 删除虚拟合约关联数据
                    $connection->createCommand("DELETE FROM sal_contract_vir_info WHERE virtual_id IN ({$virIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contract_vir_staff WHERE vir_id IN ({$virIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contract_vir_week WHERE vir_id IN ({$virIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contpro_virtual WHERE vir_id IN ({$virIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contract_history WHERE (table_type=7 OR table_type=8) AND table_id IN ({$virIdsStr})")->execute();
                }
                
                // 删除虚拟合约主表
                $connection->createCommand()->delete("sal_contract_virtual", "report_id > 5001".$this->removeEpx);
                $totalDeleted['vir'] = count($virRows);
            }
            
            // 5. 更新所有导入队列记录状态
            $connection->createCommand()->update(
                "sal_data_migration_log",
                array(
                    "status" => "C",
                    "message" => "已被超级删除（{$this->removeEpx}）",
                    "lcu" => Yii::app()->user->id,
                    "lud" => date('Y-m-d H:i:s'),
                ),
                "id > 0"
            );
            
            $connection->createCommand()->update(
                "sal_import_queue",
                array(
                    "status" => "N",
                    "message" => "已被超级删除（{$this->removeEpx}）",
                ),
                "id > 0"
            );
            
            $transaction->commit();
            
            $message = "超级删除成功！共删除：\n";
            $message .= "客户: {$totalDeleted['client']} 条\n";
            $message .= "门店: {$totalDeleted['clientStore']} 条\n";
            $message .= "主合约: {$totalDeleted['cont']} 条\n";
            $message .= "虚拟合约: {$totalDeleted['vir']} 条";
            
            return $message;
            
        } catch (Exception $e) {
            $transaction->rollback();
            Yii::log("超级删除失败: " . $e->getMessage(), 'error', 'IqueueList');
            return false;
        }
    }
}
