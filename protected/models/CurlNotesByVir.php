<?php

class CurlNotesByVir extends CurlNotesModel {
	public $pro_type="N";
	public $update_effective_date="";

    public function sendAllVirByContID($cont_id){//
        $this->sendAllVirByContIDAndNew($cont_id);
        $this->sendAllVirByContIDAndUpdate($cont_id);
    }

    public function sendAllVirByContIDAndNew($cont_id){
        $suffix = Yii::app()->params['envSuffix'];
        $addVirRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
            ->where("cont_id=:id and u_id is null",array(":id"=>$cont_id))->queryAll();
        if($addVirRows){
            $uCurlModel = new CurlNotesModel();
            $uCurlModel->data=array(
                "operation_type"=>"insert",
                "data"=>array(),
            );
            $data=array();
            foreach ($addVirRows as $addVirRow){
                $data[]=$this->getDataByVirRow($addVirRow);
            }
            $uCurlModel->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
            $uCurlModel->sendDataSetByAddContract();
            $uCurlModel->setOutContentByData();
            $uCurlModel->saveCurlToApi();

            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_file",array(
                "sent_bool"=>1,
            ),"cont_id=:cont_id and sent_bool=0",array(":cont_id"=>$cont_id));//将合同内的附件设置为已发送
        }
    }

    public function sendAllVirByContIDAndUpdate($cont_id){
        $suffix = Yii::app()->params['envSuffix'];
        $addVirRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
            ->where("cont_id=:id and u_id is not null",array(":id"=>$cont_id))->queryAll();
        if($addVirRows){
            $uCurlModel = new CurlNotesModel();
            $uCurlModel->data=array(
                "operation_type"=>"update",
                "data"=>array(),
            );
            $data=array();
            foreach ($addVirRows as $addVirRow){
                $data[]=$this->getDataByVirRow($addVirRow);
            }
            $uCurlModel->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
            $uCurlModel->sendDataSetByUpdateContract();
            $uCurlModel->setOutContentByData();
            $uCurlModel->saveCurlToApi();
        }
    }

    protected function getDataByVirRow($virRow){
        $suffix = Yii::app()->params['envSuffix'];
        $storeRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_store")
            ->where("id=:id and u_id is not null",array(":id"=>$virRow["clue_store_id"]))->queryRow();
        $lbsMainMHCode = self::getMhCodeByLbsMainKey($virRow["lbs_main"]);
        $u_service_type = self::getUServiceTypeByIDChar($virRow["busine_id"],"u_code");
        $data = array(
            "customer_id"=>$storeRow["u_id"],//客户id
            "contract_number"=>$virRow["vir_code"],//合约编号
            "contract_type"=>$virRow["clue_type"]==1?0:1,//合约类型 0 普通合约 1 KA合约
            "service_type"=>$u_service_type,//服务类型 传派单的服务类型表id（向松提供数据表）
            "payment_type"=>self::getPayTypeStrByKey($virRow["pay_type"],"u_id"),//付款方式 传派单的数据表id（向松提供数据表）
            "payment_cycle"=>self::getPayWeekStrByKey($virRow["pay_week"],"u_id"),//付款周期 传派单的数据表id（向松提供数据表）
            "first_job"=>0,//首次工作（待完成首次服务） 没有就传0
            "skills"=>null,//技能需求 没有就传NULL
            "month_cycle"=>$virRow["month_cycle"],//月周期
            "week_cycle"=>$virRow["week_cycle"],//周周期 没有就传NULL
            "day_cycle"=>$virRow["day_cycle"],//日周期 没有就传NULL
            "first_date"=>$virRow["first_date"],//首次日期 对应crm合同首次日期
            "begin_date"=>null,//常规开始日期 没有就传NULL
            "end_date"=>$virRow["cont_end_dt"],//结束日期 对应crm合同结束日期
            "pre_pay_flag"=>$virRow["fee_type"]==1?1:0,//是否预付 是预付就传 1 不是就传 0
            "prepay_month"=>$virRow["fee_type"]==1?$virRow["pay_month"]:0,//预付月份 预付几个月就传几个月
            "prepay_start"=>$virRow["fee_type"]==1?$virRow["pay_start"]:0,//预付开始 预付开始月份
            "charge_by_job"=>null,//收费制度 没有就传0
            "deposit"=>empty($virRow["deposit_need"])?0:floatval($virRow["deposit_need"]),//押金 没有就传0
            "deposit_paid"=>empty($virRow["deposit_amt"])?0:floatval($virRow["deposit_amt"]),//已付押金 没有就传0
            "deposit_rmk"=>$virRow["deposit_rmk"],//押金备注 没有就传空
            "one_time_fee"=>0,//一次性费用 没有就传0
            "crm_remarks"=>$virRow["remark"],//备注 没有就传空
            "tech_remarks"=>null,//技术员备注 没有就传空
            "status"=>self::getUStatusByVirStatus($virRow["vir_status"]),//合约状态 1 生效中 2 暂停 3 结束 4 删除 5 暂停生效
            "invoice_mode"=>0,//账单生成模式 没有就传0
            "contract_date"=>$virRow["cont_start_dt"],//合同起始日 没有就传NULL
            "pause_reason"=>$virRow["vir_status"]==40?self::getStopRemarkByKey($virRow["stop_set_id"]):null,//暂停原因 没有就传空
            "pause_date"=>$virRow["vir_status"]==40?$virRow["stop_date"]:null,//暂停日期 没有就传NULL
            "end_contract_reason"=>$virRow["vir_status"]==50?self::getStopRemarkByKey($virRow["stop_set_id"]):null,//结束原因 没有就传空
            "end_contract_date"=>$virRow["vir_status"]==50?$virRow["stop_date"]:null,//结束日期 没有就传NULL
            "entity_base_id"=>$lbsMainMHCode,//签约主体id 传门户网站对应标识
            "entity_service_id"=>$lbsMainMHCode,//服务主体ID 传门户网站对应标识
            "entity_qual_id"=>null,//资质主体ID 没有就传null
            "external_contract_id"=>null,//外部合约id
            "external_contract_number"=>null,//外部合约编号
            "external_source"=>null,//外部数据来源 1 史伟莎 2 中央KA 3 马氏 4 敏捷 5 利比斯
            "business_id"=>self::getYewudaleiStrByKey($virRow["yewudalei"],"u_id"),//所属业务大类id
            "set_frequency"=>self::getUSetFrequency($virRow),//频次设定 默认 1 1 固定频次 2 非固定频次
            "lbs_id"=>$virRow["id"],//合约编号
            "effective_date"=>$virRow["sign_date"],//生效日期 对应CRM的签约时间
            "service_contract_staff"=>$this->getContractStaff($virRow),//合约和员工关联数据
            "service_contract_frequency"=>$this->getContractFree($virRow),//合约和频次关联数据
            "service_contract_skill"=>$this->getContractService($virRow,$u_service_type),//合约和服务项目关联
            "lbs_city_office_code"=>$virRow["city"],//办公室
            "lbs_office_office_id"=>self::getOfficeStrByKey($virRow["office_id"],'u_id'),//办事处
            "service_time"=>$virRow["service_timer"],//服务时长
            "is_call"=>intval($virRow["service_fre_type"])==3?1:0,//是否是呼叫式合约 0 否 1 是
            "contract_file"=>$this->getContFileByVirRow($virRow),//当前合同附件地址
        );
        $data["invoice_amount"]=self::getUInvoiceAmt($data["set_frequency"],$virRow);
        $data["week_invoice_amount"]=$data["set_frequency"]==4?$data["invoice_amount"]:null;
        if(!empty($virRow["u_id"])){
			$staffCode = self::getEmployeeStrByUsername("code",$virRow["lcu"]);
            //$data["update_effective_date"]=$this->update_effective_date;
            $data["update_effective_date"]=empty($virRow["effect_date"])?$this->update_effective_date:$virRow["effect_date"];
            $data["update_by"]=$staffCode;
            $data["update_uid"]=$staffCode;
            $data["update_at"]=$virRow["lud"];
            $data["update_time"]=$virRow["lud"];
            $data["contract_id"]=$virRow["u_id"];
            $data["operation_event"]=self::getUOperationEvent($this->pro_type);//合约变更事件 1 编辑 2续约 3暂停 4终止 5恢复
        }else{
			$staffCode = self::getEmployeeStrByUsername("code",$virRow["lcu"]);
            $data["create_uid"]=$staffCode;
            $data["create_by"]=$staffCode;
            $data["create_at"]=$virRow["lcd"];
            $data["create_time"]=$virRow["lcd"];
        }
        return $data;
    }

    protected function getContFileByVirRow($virRow){
        $list=array();
        if(empty($virRow["u_id"])){//新增时将附件一起传给派单系统
            $suffix = Yii::app()->params['envSuffix'];
            $fileRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_file")
                ->where("cont_id=:cont_id and sent_bool=0 and phy_path_name like 'CRM%'",array(":cont_id"=>$virRow["cont_id"]))->queryAll();
            if($fileRows){
                foreach ($fileRows as $fileRow){
                    $list[]=array(
                        "lbs_id"=>$fileRow["id"],
                        "contract_number"=>$virRow["vir_code"],
                        "contract_file_url"=>$fileRow["phy_path_name"]."/".$fileRow["phy_file_name"],
                        "create_time"=>$fileRow["lcd"],
                        "create_uid"=>self::getEmployeeStrByUsername("code",$fileRow["lcu"]),
                    );
                }
            }
        }
        return empty($list)?null:$list;
    }

    public static function getUInvoiceAmt($set_frequency,$virRow){
        $virRow["year_amt"]=empty($virRow["year_amt"])?0:floatval($virRow["year_amt"]);
        $virRow["month_amt"]=empty($virRow["month_amt"])?0:floatval($virRow["month_amt"]);
        //$invoiceAmt=empty($virRow["invoice_amount"])?0:floatval($virRow["invoice_amount"]);
        $invoiceAmt=0;
        //  set_frequency 是整数类型，避免类型不匹配问题
        $set_frequency = intval($set_frequency);
        switch ($set_frequency){//派单系统的频次类型
            case 1://固定频次
                $invoiceAmt=$virRow["month_amt"];
                break;
            case 2://非固定频次
                $invoiceAmt=$virRow["year_amt"];
                break;
            case 3://固定频次每周（周）
                $invoiceAmt=$virRow["month_amt"];
                break;
            case 4://固定频次非固定金额（周）
                $freJson = json_decode($virRow['service_fre_json'],true);
                if(isset($freJson["fre_list"]) && is_array($freJson["fre_list"]) && !empty($freJson["fre_list"])){
                    $fre_list= current($freJson["fre_list"]);
                    if(isset($fre_list['fre_amt']) && !empty($fre_list['fre_amt'])){
                        $invoiceAmt=round(floatval($fre_list['fre_amt']),2);
                    }
                }
                break;
            default:
                Yii::log("getUInvoiceAmt: 未知的 set_frequency 值: {$set_frequency}, vir_id: ".$virRow["id"], 'warning', 'CurlNotesByVir');
                // 如果数据库中有 invoice_amount 字段，使用它作为后备
                if(isset($virRow["invoice_amount"]) && !empty($virRow["invoice_amount"])){
                    $invoiceAmt = floatval($virRow["invoice_amount"]);
                } else {
                    // 如果数据库中没有，根据 service_fre_type 尝试计算
                    if(isset($virRow["service_fre_type"])){
                        // service_fre_type 是 unsigned zerofill，可能是字符串，需要转换为整数
                        $service_fre_type = intval($virRow["service_fre_type"]);
                        if($service_fre_type == 1 || $service_fre_type == 3){
                            $invoiceAmt = $virRow["month_amt"];
                        } else {
                            $invoiceAmt = $virRow["year_amt"];
                        }
                    }
                }
                break;
        }
        // 如果计算出的金额为0，使用 invoice_amount 字段
        if($invoiceAmt == 0 && isset($virRow["invoice_amount"]) && !empty($virRow["invoice_amount"])){
            $dbInvoiceAmt = floatval($virRow["invoice_amount"]);
            if($dbInvoiceAmt > 0){
                Yii::log("getUInvoiceAmt: 计算金额为0，使用数据库中的 invoice_amount: {$dbInvoiceAmt}, vir_id: ".$virRow["id"].", set_frequency: {$set_frequency}, month_amt: ".$virRow["month_amt"].", year_amt: ".$virRow["year_amt"], 'warning', 'CurlNotesByVir');
                $invoiceAmt = $dbInvoiceAmt;
            }
        }
        return $invoiceAmt;
    }
    public static function getUSetFrequency($virRow){
        // service_fre_type 是 unsigned zerofill，可能是字符串，需要转换为整数
        $service_fre_type = intval($virRow["service_fre_type"]);
        switch ($service_fre_type){
            case 1://固定频次
                $freJson = json_decode($virRow['service_fre_json'],true);
                if(isset($freJson["fre_list"])){
                    $fre_list= current($freJson["fre_list"]);
                    if(isset($fre_list['type_sum'])&&$fre_list['type_sum']==4){
                        return 3;
                    }
                }
                return 1;
            case 4://固定周频次
                return 4;
            default:
                return 2;
        }
    }

	public static function getUOperationEvent($pro_type){
		//合约变更事件 1 编辑 2续约 3暂停 4终止 5恢复
		switch($pro_type){
			case "A":
				return 1;
			case "C":
				return 2;
			case "S":
				return 3;
			case "T":
				return 4;
			case "R":
				return 5;
			
		}
		return 1;
	}

    protected function getContractStaff($virRow){
        $data=array();
        //sal_contract_vir_staff
        $suffix = Yii::app()->params['envSuffix'];
        $staffRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_vir_staff")
            ->where("vir_id=:id",array(":id"=>$virRow["id"]))->queryAll();
        if($staffRows){
            foreach ($staffRows as $staffRow){
                $temp=array(
                    "lbs_id"=>$staffRow["id"],
                    "type"=>$staffRow["type"],
                    "staff_id"=>self::getEmployeeStrByKey("code",$staffRow["employee_id"]),
                    "role"=>$staffRow["role"],
                    "business_id"=>$staffRow["u_yewudalei"],
                    "is_delete"=>$staffRow["is_del"],
                );
                if(!empty($virRow["u_id"])){
                    $temp["contract_id"]=$virRow["u_id"];
                }
                if(empty($staffRow["u_id"])){
                    $temp["created_at"]=$staffRow["lcd"];
                    $temp["created_uid"]=self::getEmployeeStrByUsername("code",$staffRow["lcu"]);
                }else{
                    $temp["id"]=$staffRow["u_id"];
                    $temp["updated_at"]=$staffRow["lcd"];
                    $temp["updated_uid"]=self::getEmployeeStrByUsername("code",$staffRow["lcu"]);
                }
                $data[]=$temp;
            }
        }
        return $data;
    }

    protected function getContractFree($virRow){
        $data=array();
        //sal_contract_vir_week
        $suffix = Yii::app()->params['envSuffix'];
        $freeRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_vir_week")
            ->where("vir_id=:id",array(":id"=>$virRow["id"]))->queryAll();
        if($freeRows){
            foreach ($freeRows as $freeRow){
                $temp=array(
                    "lbs_id"=>$freeRow["id"],
                    "month_cycle"=>$freeRow["month_cycle"],
                    "week_cycle"=>$freeRow["week_cycle"],
                    "day_cycle"=>$freeRow["day_cycle"],
                    "unit_price"=>$freeRow["unit_price"],
                    //"contract_date"=>$freeRow["contract_date"],
                    //"cycle_text"=>$freeRow["cycle_text"],
                    "is_delete"=>$freeRow["is_del"],
                    "update_time"=>null,
                    "update_uid"=>null,
                );
                // 需要判断 $virRow["service_fre_type"]==3 才传year_cycle
                if(intval($virRow["service_fre_type"])==3){
                    $temp["year_cycle"]=$freeRow["year_cycle"];
                }
                if(!empty($virRow["u_id"])){
                    $temp["contract_id"]=$virRow["u_id"];
                }
                $temp["create_time"]=$freeRow["lcd"];
                $temp["create_uid"]=self::getEmployeeStrByUsername("code",$freeRow["lcu"]);
                //$temp["update_time"]=$freeRow["lcd"];
                //$temp["update_uid"]=self::getEmployeeStrByUsername("code",$freeRow["lcu"]);
                $data[]=$temp;
            }
        }
        return $data;
    }

    protected function getContractService($virRow,$u_service_type){
        $data=array("lbs_id"=>$virRow["id"],"service_id"=>$u_service_type,"data"=>array());
        if(!empty($virRow["u_id"])){
            $data["contract_id"]=$virRow["u_id"];
        }
        //sal_contract_vir_staff
        $suffix = Yii::app()->params['envSuffix'];
        $serviceRows = Yii::app()->db->createCommand()
            ->select("b.input_type,b.name,b.u_code,b.u_type,a.field_id,a.field_value")
            ->from("sales{$suffix}.sal_contract_vir_info a")
            ->leftJoin("sal_service_type_info b","a.service_type_id=b.id")
            ->where("a.virtual_id=:id",array(":id"=>$virRow["id"]))
            ->order("b.u_type asc")->queryAll();
        $infoData=array();
        if($serviceRows){
            foreach ($serviceRows as $serviceRow){//device
                $infoData[$serviceRow['field_id']]=$serviceRow;
                if(!empty($serviceRow["u_code"])){
                    if($serviceRow["u_type"]==3){//复选框而且勾选
                        $serviceRow["field_value"] = $serviceRow["field_value"]=="Y"?1:0;
                    }
                    $temp=array(
                        "id"=>$serviceRow["u_code"],
                        "name"=>$serviceRow["name"],
                        "item1"=>in_array($serviceRow["u_type"],array(1,3,4))?$serviceRow["field_value"]:null,
                        "item2"=>$serviceRow["u_type"]==2?$serviceRow["field_value"]:null,
                        "type"=>$serviceRow["u_type"],
                    );
                    if($serviceRow["input_type"]=="device"){
                        $key = "{$serviceRow["field_id"]}_rmk";
                        $temp["item2"]=key_exists($key,$infoData)?$infoData[$key]["field_value"]:"";
                    }
                    $data["data"][]=$temp;
                }
            }
        }
        return array($data);
    }

    //发送（且保存回传id）
    public function sendUByVirData($errorBool=false){
        if(!empty($this->data)){
            if(isset($this->data["data"])) {
                $countArr = is_array($this->data["data"]) ? $this->data["data"] : json_decode($this->data["data"], true);
                $count = count($countArr);
                if ($count > self::$maxCount) {
                    $dataContent = $this->data;
                    $page = ceil($count / self::$maxCount);
                    for ($i = 0; $i < $page; $i++) {
                        $start = $i * self::$maxCount;
                        $data = array_slice($countArr, $start, self::$maxCount);
                        $dataContent["data"] = json_encode($data, JSON_UNESCAPED_UNICODE);
                        $this->data_content = json_encode($dataContent, JSON_UNESCAPED_UNICODE);
                        if($i!=$page-1){//分段
                            $this->sendCurl($errorBool);
                            $this->endData();//保存返回结果
                            $this->insertAPICURL($this->data_content);
                        }
                    }
                }
            }
        }
        $this->sendCurl($errorBool);//发送数据
        $this->endData();//保存返回结果
    }

    public function endData(){
        if($this->status_type=="C"){
            //成功后需要把派单系统的id存入CRM
            $rows = isset($this->outData["data"])?$this->outData["data"]:array();
            if(!empty($rows)){
                foreach ($rows as $row){
                    $updateArr = array(
                        "u_id"=>isset($row["contract_id"])?$row["contract_id"]:null
                    );
                    Yii::app()->db->createCommand()->update("sal_contract_virtual",$updateArr,"id=:id",array(":id"=>$row["lbs_id"]));

                    if(isset($row["service_contract_staff"])){//合约和员工关联
                        foreach ($row["service_contract_staff"] as $item){
                            Yii::app()->db->createCommand()->update("sal_contract_vir_staff",array(
                                "u_id"=>$item["id"]
                            ),"id=:id",array(":id"=>$item["lbs_id"]));
                        }
                    }
                    /*
                    if(isset($row["service_contract_skill"])){//合约和项目关联
                        foreach ($row["service_contract_skill"] as $item){
                            Yii::app()->db->createCommand()->update("sal_contract_vir_info",array(
                                "u_id"=>$item["id"]
                            ),"id=:id",array(":id"=>$item["lbs_id"]));
                        }
                    }
                    if(isset($row["service_contract_frequency"])){//合约和频次关联
                        foreach ($row["service_contract_frequency"] as $item){
                            Yii::app()->db->createCommand()->update("sal_contract_vir_week",array(
                                "u_id"=>$item["id"]
                            ),"id=:id",array(":id"=>$item["lbs_id"]));
                        }
                    }
                    */
                }
            }
        }
    }
}
