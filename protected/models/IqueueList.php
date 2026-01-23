<?php

class IqueueList extends CListPageModel
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
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_import_queue")
            ->where("id=:id",array(":id"=>$index))->queryRow();
        if($row){
            $connection = Yii::app()->db;
            $transaction = $connection->beginTransaction();
            try {
                $message = "";
                switch ($row["import_type"]){
                    case "clientStore":
                        // 检查本次导入的客户数据
                        $checkClientRows = $connection->createCommand()->select("id")->from("sal_clue")
                            ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                        
                        if (!empty($checkClientRows)) {
                            // 有客户数据，调用 removeClient 级联删除所有关联数据
                            $clientCount = $this->removeClient($index, $connection);
                            $message = "已删除客户门店导入记录，共删除 {$clientCount} 条客户及其所有关联数据（包括其他批次导入的门店、合同、虚拟合约）";
                        } else {
                            // 没有客户数据，删除本次导入的门店及其所有关联数据
                            $storeRows = $connection->createCommand()->select("id,clue_id")->from("sal_clue_store")
                                ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                            if($storeRows) {
                                $storeIds = array();
                                $orphanClueIds = array(); // 孤立的客户ID
                                
                                foreach($storeRows as $row) {
                                    $storeIds[] = $row['id'];
                                    // 检查这个门店关联的客户是否还存在
                                    if (!empty($row['clue_id'])) {
                                        $clueExists = $connection->createCommand()
                                            ->select("id")
                                            ->from("sal_clue")
                                            ->where("id=:id", array(":id"=>$row['clue_id']))
                                            ->queryRow();
                                        if (!$clueExists) {
                                            $orphanClueIds[] = $row['clue_id'];
                                        }
                                    }
                                }
                                
                                if (!empty($storeIds)) {
                                    $storeIdsStr = implode(',', $storeIds);
                                    
                                    // 1. 删除虚拟合约（因为虚拟合约依赖主合同和门店）
                                    $virtualRows = $connection->createCommand()
                                        ->select("id")
                                        ->from("sal_contract_virtual")
                                        ->where("clue_store_id IN ({$storeIdsStr})")
                                        ->queryAll();
                                    if (!empty($virtualRows)) {
                                        $virIds = array();
                                        foreach($virtualRows as $vrow) {
                                            $virIds[] = $vrow['id'];
                                        }
                                        $virIdsStr = implode(',', $virIds);
                                        $connection->createCommand("DELETE FROM sal_contract_vir_info WHERE virtual_id IN ({$virIdsStr})")->execute();
                                        $connection->createCommand("DELETE FROM sal_contract_vir_staff WHERE vir_id IN ({$virIdsStr})")->execute();
                                        $connection->createCommand("DELETE FROM sal_contract_vir_week WHERE vir_id IN ({$virIdsStr})")->execute();
                                        $connection->createCommand("DELETE FROM sal_contpro_virtual WHERE vir_id IN ({$virIdsStr})")->execute();
                                        $connection->createCommand("DELETE FROM sal_contract_history WHERE (table_type=7 OR table_type=8) AND table_id IN ({$virIdsStr})")->execute();
                                        $connection->createCommand("DELETE FROM sal_contract_virtual WHERE clue_store_id IN ({$storeIdsStr})")->execute();
                                    }
                                    
                                    // 2. 删除与门店关联的合约门店关系
                                    $connection->createCommand("DELETE FROM sal_contpro_sse WHERE clue_store_id IN ({$storeIdsStr})")->execute();
                                    $connection->createCommand("DELETE FROM sal_contract_sse WHERE clue_store_id IN ({$storeIdsStr})")->execute();
                                    
                                    // 3. 删除门店关联的其他数据
                                    $connection->createCommand("DELETE FROM sal_clue_person WHERE clue_store_id IN ({$storeIdsStr}) AND clue_store_id!=0")->execute();
                                    $connection->createCommand("DELETE FROM sal_clue_sre_soe WHERE clue_store_id IN ({$storeIdsStr})")->execute();
                                    $connection->createCommand("DELETE FROM sal_clue_history WHERE table_type=2 AND table_id IN ({$storeIdsStr})")->execute();
                                }
                                
                                // 4. 删除门店主表
                                $connection->createCommand()->delete("sal_clue_store","report_id=:report_id".$this->removeEpx,array(":report_id"=>$index));
                                
                                $orphanCount = count(array_unique($orphanClueIds));
                                if ($orphanCount > 0) {
                                    $message = "已删除门店导入记录，共删除 " . count($storeRows) . " 条门店及其所有关联数据（包括虚拟合约、合约门店关系等）。其中 {$orphanCount} 个门店的客户数据已不存在，属于孤立数据";
                                } else {
                                    $message = "已删除门店导入记录，共删除 " . count($storeRows) . " 条门店及其所有关联数据（包括虚拟合约、合约门店关系等）";
                                }
                            } else {
                                $message = "未找到要删除的数据（可能已被删除）";
                            }
                        }
                        break;
                    case "client":
                        // 检查本次导入的客户数据
                        $checkClientRows = $connection->createCommand()->select("id")->from("sal_clue")
                            ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                        
                        if (empty($checkClientRows)) {
                            $message = "未找到要删除的客户数据（可能已被删除）";
                        } else {
                            // 删除客户数据（会级联删除所有 clue_id 匹配的门店、合同、虚拟合约）
                            $clientCount = $this->removeClient($index, $connection);
                            $message = "已删除客户导入记录，共删除 {$clientCount} 条客户及其所有关联数据（包括其他批次导入的门店、合同、虚拟合约）";
                        }
                        break;
                    case "cont":
                        // 检查本次导入的客户数据
                        $checkClientRows = $connection->createCommand()->select("id")->from("sal_clue")
                            ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                        
                        if (!empty($checkClientRows)) {
                            // 有客户数据，调用 removeClient 级联删除所有关联数据
                            $clientCount = $this->removeClient($index, $connection);
                            $message = "已删除合约导入记录，共删除 {$clientCount} 条客户及其所有关联数据（包括其他批次导入的门店、合同、虚拟合约）";
                        } else {
                            // 没有客户数据，检查并删除本次导入的合同数据（包括孤立数据）
                            $contractRows = $connection->createCommand()->select("id,clue_id")->from("sal_contract")
                                ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                            if (!empty($contractRows)) {
                                $orphanClueIds = array();
                                foreach($contractRows as $row) {
                                    // 检查合同关联的客户是否还存在
                                    if (!empty($row['clue_id'])) {
                                        $clueExists = $connection->createCommand()
                                            ->select("id")
                                            ->from("sal_clue")
                                            ->where("id=:id", array(":id"=>$row['clue_id']))
                                            ->queryRow();
                                        if (!$clueExists) {
                                            $orphanClueIds[] = $row['clue_id'];
                                        }
                                    }
                                }
                                
                                $contractCount = $this->removeCont($index, $connection);
                                $orphanCount = count(array_unique($orphanClueIds));
                                if ($orphanCount > 0) {
                                    $message = "已删除合约导入记录，共删除 {$contractCount} 条主合同（其中 {$orphanCount} 个合同的客户数据已不存在，属于孤立数据）";
                                } else {
                                    $message = "已删除合约导入记录，共删除 {$contractCount} 条主合同";
                                }
                            } else {
                                $message = "未找到要删除的数据（可能已被删除）";
                            }
                        }
                        break;
                    case "vir":
                        // 检查本次导入的客户数据
                        $checkClientRows = $connection->createCommand()->select("id")->from("sal_clue")
                            ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                        
                        if (!empty($checkClientRows)) {
                            // 有客户数据，调用 removeClient 级联删除所有关联数据
                            $clientCount = $this->removeClient($index, $connection);
                            $message = "已删除虚拟合约导入记录，共删除 {$clientCount} 条客户及其所有关联数据（包括其他批次导入的门店、合同、虚拟合约）";
                        } else {
                            // 没有客户数据，检查并删除本次导入的虚拟合约数据（包括孤立数据）
                            $removeRows = $connection->createCommand()->select("id,cont_id,clue_id,clue_service_id,clue_store_id")->from("sal_contract_virtual")
                                ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                            if($removeRows){
                                $virIds = array();
                                $orphanClueIds = array();
                                
                                foreach($removeRows as $row) {
                                    $virIds[] = $row['id'];
                                    // 检查虚拟合约关联的客户是否还存在
                                    if (!empty($row['clue_id'])) {
                                        $clueExists = $connection->createCommand()
                                            ->select("id")
                                            ->from("sal_clue")
                                            ->where("id=:id", array(":id"=>$row['clue_id']))
                                            ->queryRow();
                                        if (!$clueExists) {
                                            $orphanClueIds[] = $row['clue_id'];
                                        }
                                    }
                                }
                                
                                if (!empty($virIds)) {
                                    $virIdsStr = implode(',', $virIds);
                                    $connection->createCommand("DELETE FROM sal_contract_vir_info WHERE virtual_id IN ({$virIdsStr})")->execute();
                                    $connection->createCommand("DELETE FROM sal_contract_vir_staff WHERE vir_id IN ({$virIdsStr})")->execute();
                                    $connection->createCommand("DELETE FROM sal_contract_vir_week WHERE vir_id IN ({$virIdsStr})")->execute();
                                    $connection->createCommand("DELETE FROM sal_contpro_virtual WHERE vir_id IN ({$virIdsStr})")->execute();
                                    $connection->createCommand("DELETE FROM sal_contract_history WHERE (table_type=7 OR table_type=8) AND table_id IN ({$virIdsStr})")->execute();
                                }
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
                                
                                $orphanCount = count(array_unique($orphanClueIds));
                                if ($orphanCount > 0) {
                                    $message = "已删除虚拟合约导入记录，共删除 " . count($removeRows) . " 条虚拟合约（其中 {$orphanCount} 个虚拟合约的客户数据已不存在，属于孤立数据）";
                                } else {
                                    $message = "已删除虚拟合约导入记录，共删除 " . count($removeRows) . " 条虚拟合约";
                                }
                            } else {
                                $message = "未找到要删除的数据（可能已被删除）";
                            }
                        }
                        break;
                    default:
                        $transaction->rollback();
                        return false;
                }
                // 更新状态为 'N'，这样记录就不会在列表中显示了
                $connection->createCommand()->update("sal_import_queue",array(
                    "status"=>"N",
                    "message"=>"已被删除（{$this->removeEpx}）",
                ),"id=:id",array(":id"=>$index));
                $transaction->commit();
                return $message;
            } catch (Exception $e) {
                $transaction->rollback();
                // 重新抛出异常，让 Controller 能够捕获到具体的错误信息
                throw $e;
            }
        }else{
            throw new Exception("未找到要删除的导入记录（ID: {$index}）");
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
                
                // 【级联删除】删除所有引用这些客户的数据（不管 report_id）
                // 1. 先删除虚拟合约（因为虚拟合约依赖主合同）
                $virtualRows = $connection->createCommand()
                    ->select("id")
                    ->from("sal_contract_virtual")
                    ->where("clue_id IN ({$clueIdsStr})")
                    ->queryAll();
                if (!empty($virtualRows)) {
                    $virIds = array();
                    foreach($virtualRows as $row) {
                        $virIds[] = $row['id'];
                    }
                    $virIdsStr = implode(',', $virIds);
                    // 删除虚拟合约的关联表
                    $connection->createCommand("DELETE FROM sal_contract_vir_info WHERE virtual_id IN ({$virIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contract_vir_staff WHERE vir_id IN ({$virIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contract_vir_week WHERE vir_id IN ({$virIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contpro_virtual WHERE vir_id IN ({$virIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contract_history WHERE (table_type=7 OR table_type=8) AND table_id IN ({$virIdsStr})")->execute();
                    // 删除虚拟合约主表
                    $connection->createCommand("DELETE FROM sal_contract_virtual WHERE clue_id IN ({$clueIdsStr})")->execute();
                }
                
                // 2. 删除主合同
                $contractRows = $connection->createCommand()
                    ->select("id")
                    ->from("sal_contract")
                    ->where("clue_id IN ({$clueIdsStr})")
                    ->queryAll();
                if (!empty($contractRows)) {
                    $contIds = array();
                    foreach($contractRows as $row) {
                        $contIds[] = $row['id'];
                    }
                    $contIdsStr = implode(',', $contIds);
                    // 删除主合同的关联表
                    $connection->createCommand("DELETE FROM sal_contpro WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contpro_virtual WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contract_file WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contpro_file WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contpro_sse WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contract_sse WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contract_call WHERE cont_id IN ({$contIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_contract_history WHERE (table_type=5 OR table_type=6) AND table_id IN ({$contIdsStr})")->execute();
                    // 删除主合同主表
                    $connection->createCommand("DELETE FROM sal_contract WHERE clue_id IN ({$clueIdsStr})")->execute();
                }
                
                // 3. 删除门店
                $storeRows = $connection->createCommand()
                    ->select("id")
                    ->from("sal_clue_store")
                    ->where("clue_id IN ({$clueIdsStr})")
                    ->queryAll();
                if (!empty($storeRows)) {
                    $storeIds = array();
                    foreach($storeRows as $row) {
                        $storeIds[] = $row['id'];
                    }
                    $storeIdsStr = implode(',', $storeIds);
                    // 删除门店的关联表
                    $connection->createCommand("DELETE FROM sal_clue_person WHERE clue_store_id IN ({$storeIdsStr}) AND clue_store_id!=0")->execute();
                    $connection->createCommand("DELETE FROM sal_clue_sre_soe WHERE clue_store_id IN ({$storeIdsStr})")->execute();
                    $connection->createCommand("DELETE FROM sal_clue_history WHERE table_type=2 AND table_id IN ({$storeIdsStr})")->execute();
                    // 删除门店主表
                    $connection->createCommand("DELETE FROM sal_clue_store WHERE clue_id IN ({$clueIdsStr})")->execute();
                }
                
                // 4. 删除商机
                $connection->createCommand("DELETE FROM sal_clue_service WHERE clue_id IN ({$clueIdsStr})")->execute();
                
                // 5. 删除客户的其他关联表数据
                $connection->createCommand("DELETE FROM sal_clue_u_staff WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_u_area WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_person WHERE clue_id IN ({$clueIdsStr}) AND clue_id!=0")->execute();
                $connection->createCommand("DELETE FROM sal_clue_rpt WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_rpt_file WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_file WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_flow WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_invoice WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_sre_soe WHERE clue_id IN ({$clueIdsStr})")->execute();
                $connection->createCommand("DELETE FROM sal_clue_history WHERE table_type=1 AND table_id IN ({$clueIdsStr})")->execute();
                // $connection->createCommand("DELETE FROM sal_clue_tag_map WHERE clue_id IN ({$clueIdsStr})")->execute(); // 表不存在，已注释
            }
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
                // 注意：不能直接通过 cont_id 删除虚拟合约，因为虚拟合约可能有自己的 report_id
                // 虚拟合约应该在外层通过 report_id 删除
                // $connection->createCommand("DELETE FROM sal_contract_virtual WHERE cont_id IN ({$contIdsStr})")->execute();
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
                // 注意：不能直接删除商机，因为商机可能有自己的 report_id
                // 商机应该在外层通过 report_id 删除
                // if (!empty($removeRow["clue_service_id"])) {
                //     $connection->createCommand()->delete("sal_clue_service","id=:id",array(":id"=>$removeRow["clue_service_id"]));
                // }
                if (!empty($removeRow["clue_id"])) {
                    $connection->createCommand()->update("sal_clue",array(
                        "clue_status"=>ClueVirProModel::getClientStatusByClueID($removeRow["clue_id"]),
                    ),"id=:id",array(":id"=>$removeRow["clue_id"]));
                }
            }
        }
        return count($removeRows);
    }
}
