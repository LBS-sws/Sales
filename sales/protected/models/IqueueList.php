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
        );
    }

    public function retrieveDataByPage($pageNum=1)
    {
        $uid = Yii::app()->user->id;
        $sql1 = "select a.id,a.import_type,a.req_dt,a.fin_dt,a.status,a.success_num,a.error_num,a.import_name,a.message
				from sal_import_queue a 
				where a.status<>'N' and a.username='".$uid."' 
			";
        $sql2 = "select count(a.id)
				from sal_import_queue a 
				where a.status<>'N' and a.username='".$uid."' 
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
                //a.id,a.import_type,a.req_dt,a.fin_dt,a.status,a.success_num,a.error_num,a.import_name,a.message
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
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_xc08'] = $this->getCriteria();
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
            switch ($row["import_type"]){
                case "clientStore":
                    $this->removeClient($index);
                    $removeRows = Yii::app()->db->createCommand()->select("id")->from("sal_clue")
                        ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                    if($removeRows) {
                        Yii::app()->db->createCommand()->delete("sal_clue_store","report_id=:report_id".$this->removeEpx,array(":report_id"=>$index));
                        Yii::app()->db->createCommand("DELETE a FROM sal_clue_person a LEFT JOIN sal_clue_store b ON a.clue_store_id=b.id WHERE a.clue_store_id!=0 AND b.id is null;")->execute();
                        Yii::app()->db->createCommand("DELETE a FROM sal_clue_sre_soe a LEFT JOIN sal_clue_store b ON a.clue_store_id=b.id WHERE b.id is null;")->execute();
                        Yii::app()->db->createCommand("DELETE a FROM sal_clue_history a LEFT JOIN sal_clue_store b ON a.table_type=2 and a.table_id=b.id WHERE b.id is null;")->execute();
                    }
                    echo "remove cont count:".count($removeRows);
                    break;
                case "client":
                    $count = $this->removeClient($index);
                    echo "remove cont count:".$count;
                    break;
                case "cont":
                    $removeCount = $this->removeCont($index);
                    echo "remove cont count:".$removeCount;
                    break;
                case "vir":
                    $removeCount = $this->removeCont($index);
                    $removeRows = Yii::app()->db->createCommand()->select("id,cont_id,clue_id,clue_service_id,clue_store_id")->from("sal_contract_virtual")
                        ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
                    if($removeRows){
                        Yii::app()->db->createCommand()->delete("sal_contract_virtual","report_id=:report_id".$this->removeEpx,array(":report_id"=>$index));
                        Yii::app()->db->createCommand("DELETE a FROM sal_contract_vir_info a LEFT JOIN sal_contract_virtual b ON a.virtual_id=b.id WHERE b.id is null;")->execute();
                        Yii::app()->db->createCommand("DELETE a FROM sal_contract_vir_staff a LEFT JOIN sal_contract_virtual b ON a.vir_id=b.id WHERE b.id is null;")->execute();
                        Yii::app()->db->createCommand("DELETE a FROM sal_contract_vir_week a LEFT JOIN sal_contract_virtual b ON a.vir_id=b.id WHERE b.id is null;")->execute();
                        Yii::app()->db->createCommand("DELETE a FROM sal_contpro_virtual a LEFT JOIN sal_contract_virtual b ON a.vir_id=b.id WHERE b.id is null;")->execute();
                        foreach ($removeRows as $removeRow){
                            Yii::app()->db->createCommand()->delete("sal_contpro_sse","cont_id=:cont_id and clue_store_id=:clue_store_id",array(":cont_id"=>$removeRow["cont_id"],":clue_store_id"=>$removeRow["clue_store_id"]));
                            Yii::app()->db->createCommand()->delete("sal_contract_sse","cont_id=:cont_id and clue_store_id=:clue_store_id",array(":cont_id"=>$removeRow["cont_id"],":clue_store_id"=>$removeRow["clue_store_id"]));
                            Yii::app()->db->createCommand()->update("sal_clue",array(
                                "clue_status"=>ClueVirProModel::getClientStatusByClueID($removeRow["clue_id"]),
                            ),"id=:id",array(":id"=>$removeRow["clue_id"]));
                            Yii::app()->db->createCommand()->update("sal_clue_store",array(
                                "store_status"=>ClueVirProModel::getStoreStatusByStoreID($removeRow["clue_store_id"]),
                            ),"id=:id",array(":id"=>$removeRow["clue_store_id"]));
                        }
                    }
                    echo "remove vir count:".count($removeRows);
                    break;
                default:
                    echo "import_type error";
                    return false;
            }
            Yii::app()->db->createCommand()->update("sal_import_queue",array(
                "message"=>"已被删除（{$this->removeEpx}）",
            ),"id=:id",array(":id"=>$index));
        }else{
            echo "index error";
        }
    }

    protected function removeClient($report_id){
        $removeRows = Yii::app()->db->createCommand()->select("id")->from("sal_clue")
            ->where("report_id=:report_id and group_bool='Y'".$this->removeEpx,array(":report_id"=>$report_id))->queryAll();
        Yii::app()->db->createCommand()->delete("sal_clue","report_id=:report_id".$this->removeEpx,array(":report_id"=>$report_id));
        if($removeRows) {
            Yii::app()->db->createCommand("DELETE a FROM sal_clue_u_staff a LEFT JOIN sal_clue b ON a.clue_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_clue_u_area a LEFT JOIN sal_clue b ON a.clue_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_clue_person a LEFT JOIN sal_clue b ON a.clue_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_clue_store a LEFT JOIN sal_clue b ON a.clue_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_clue_rpt a LEFT JOIN sal_clue b ON a.clue_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_clue_rpt_file a LEFT JOIN sal_clue b ON a.clue_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_clue_file a LEFT JOIN sal_clue b ON a.clue_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_clue_flow a LEFT JOIN sal_clue b ON a.clue_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_clue_invoice a LEFT JOIN sal_clue b ON a.clue_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_clue_sre_soe a LEFT JOIN sal_clue b ON a.clue_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_clue_history a LEFT JOIN sal_clue b ON a.table_type=1 and a.table_id=b.id WHERE b.id is null;")->execute();
        }
        return count($removeRows);
    }

    protected function removeCont($report_id){
        $index=$report_id;
        $removeRows = Yii::app()->db->createCommand()->select("id,clue_id,clue_service_id")->from("sal_contract")
            ->where("report_id=:report_id".$this->removeEpx,array(":report_id"=>$index))->queryAll();
        if($removeRows){
            Yii::app()->db->createCommand()->delete("sal_contract","report_id=:report_id".$this->removeEpx,array(":report_id"=>$index));
            Yii::app()->db->createCommand("DELETE a FROM sal_contract_virtual a LEFT JOIN sal_contract b ON a.cont_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_contpro_virtual a LEFT JOIN sal_contract b ON a.cont_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_contract_file a LEFT JOIN sal_contract b ON a.cont_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_contpro_sse a LEFT JOIN sal_contract b ON a.cont_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_contract_sse a LEFT JOIN sal_contract b ON a.cont_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_contpro a LEFT JOIN sal_contract b ON a.cont_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_contract_call a LEFT JOIN sal_contract b ON a.cont_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_contract_vir_info a LEFT JOIN sal_contract_virtual b ON a.virtual_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_contract_vir_staff a LEFT JOIN sal_contract_virtual b ON a.vir_id=b.id WHERE b.id is null;")->execute();
            Yii::app()->db->createCommand("DELETE a FROM sal_contract_vir_week a LEFT JOIN sal_contract_virtual b ON a.vir_id=b.id WHERE b.id is null;")->execute();
            foreach ($removeRows as $removeRow){
                Yii::app()->db->createCommand()->delete("sal_clue_service","id=:id",array(":id"=>$removeRow["clue_service_id"]));
                Yii::app()->db->createCommand()->update("sal_clue",array(
                    "clue_status"=>ClueVirProModel::getClientStatusByClueID($removeRow["clue_id"]),
                ),"id=:id",array(":id"=>$removeRow["clue_id"]));
            }
        }
        return count($removeRows);
    }
}
