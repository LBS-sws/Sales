<?php

class CurlNotesByStore extends CurlNotesModel {
    public function putAllStoreByStoreIDs($storeIDs){//
		$idSql = implode(',',$storeIDs);
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("a.*")->from("sales{$suffix}.sal_clue_store a")
            ->where("a.id in ({$idSql})")->queryAll();
        if($rows){
            $data=array();
            foreach ($rows as $row){
				$clientHeadRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue")
					->where("id=:id",array(":id"=>$row['clue_id']))->queryRow();
                $data[]=$this->getDataByStoreID($row["id"],$clientHeadRow);
            }
            $this->data=array(
                "operation_type"=>$this->operation_type,
                "data"=>array(),
            );
            $this->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
        }
    }
    public function putAllStoreByClueID($clue_id){//
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("a.id")->from("sales{$suffix}.sal_clue_store a")
            ->where("clue_id=:id and u_id is null",array(":id"=>$clue_id))->queryAll();
        if($rows){
            $data=array();
            $clientHeadRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue")
                ->where("id=:id",array(":id"=>$clue_id))->queryRow();
            foreach ($rows as $row){
                $data[]=$this->getDataByStoreID($row["id"],$clientHeadRow);
            }
            $this->data=array(
                "operation_type"=>$this->operation_type,
                "data"=>array(),
            );
            $this->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
        }
    }
    public function resetAllGroupBool($clientHeadRow){//刷新门店的集团属性
        $data=array();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("a.id")->from("sales{$suffix}.sal_clue_store a")
            ->where("clue_id=:id and u_id is not null",array(":id"=>$clientHeadRow["id"]))->queryAll();
        if($rows){
            foreach ($rows as $row){
                $data[]=$this->getDataByStoreID($row["id"],$clientHeadRow);
            }
            $this->data=array(
                "operation_type"=>$this->operation_type,
                "data"=>array(),
            );
            $this->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
            $this->setOutContentByData();
            $this->saveCurlToApi();
        }
    }

    public function saveDataByStoreID($storeModel,$clientHeadRow){//保存门店时需要同步保存联系人
        $suffix = Yii::app()->params['envSuffix'];
        $this->putDataByStoreID($storeModel->id,$clientHeadRow);
        $this->setOutContentByData();
        if(empty($storeModel->u_id)&&$this->status_type=="P"){//如果新增需要获取门店对应的id
            $this->setMinUrl($this->min_url);
            $this->sendUByStoreData();
        }
        $this->saveCurlToApi();
        if($this->status_type=="Error"){
            return false;
        }

        $nowStoreRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_store")
            ->where("id=:id and u_id is not null",array(":id"=>$storeModel->id))->queryRow();
        if(!$nowStoreRow){//门店同步失败
            return false;
        }
        $suffix = Yii::app()->params['envSuffix'];
        $personRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_person")
            ->where("clue_store_id=:id and u_id is null",array(":id"=>$storeModel->id))->queryAll();
        if($personRows){//同步门店联系人
            $uStoreModel = new CurlNotesByStore();
            $data=array();
            foreach ($personRows as $personRow){
                $data[]=$uStoreModel->getPersonDataByPersonID($personRow["id"],$nowStoreRow);
            }
            $uStoreModel->data=array(
                "operation_type"=>$uStoreModel->operation_type,
                "data"=>array(),
            );
            $uStoreModel->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
            $uStoreModel->setOutContentByData();
            $uStoreModel->saveCurlToApi();
        }

        $areaRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_u_area")
            ->where("clue_id=:id and u_id is null",array(":id"=>$storeModel->clue_id))->queryAll();
        if($areaRows){//同步门店扩建的区域
            $clientHeadRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue")
                ->where("id=:id and u_id is not null",array(":id"=>$storeModel->clue_id))->queryRow();
            if($clientHeadRow){
                $uClientModel = new CurlNotesByClient();
                $data=array();
                foreach ($areaRows as $areaRow){
                    $data[]=$uClientModel->getAreaDataByAreaID($areaRow["id"],$clientHeadRow);
                }
                $uClientModel->data=array(
                    "operation_type"=>$uClientModel->operation_type,
                    "data"=>array(),
                );
                $uClientModel->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
                $uClientModel->setOutContentByData();
                $uClientModel->saveCurlToApi();
            }
        }
    }

    public function putPersonDataByNewStoreIDs($storeIDs){
        $ids = implode(",",$storeIDs);
        $suffix = Yii::app()->params['envSuffix'];
        $personRows = Yii::app()->db->createCommand()->select("a.id,b.u_id,b.store_code")
            ->from("sales{$suffix}.sal_clue_person a")
            ->leftJoin("sales{$suffix}.sal_clue_store b","a.clue_store_id=b.id")
            ->where("a.clue_store_id in ($ids) and a.u_id is null")->queryAll();
        if($personRows){//同步门店联系人
            $uStoreModel = new CurlNotesByStore();
            $data=array();
            foreach ($personRows as $personRow){
                $data[]=$uStoreModel->getPersonDataByPersonID($personRow["id"],$personRow);
            }
            $uStoreModel->data=array(
                "operation_type"=>$uStoreModel->operation_type,
                "data"=>array(),
            );
            $uStoreModel->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
            $uStoreModel->setOutContentByData();
            $uStoreModel->saveCurlToApi();
        }
    }

    //将客户数据保存到api内
    public function putDataByStoreID($store_id,$clientHeadRow){
        $data = $this->getDataByStoreID($store_id,$clientHeadRow);
        $this->data=array(
            "operation_type"=>$this->operation_type,
            "data"=>array(),
        );
        $data=array($data);
        $this->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
    }

    //获取客户数据
    public function getDataByStoreID($store_id,$clientHeadRow){
        $suffix = Yii::app()->params['envSuffix'];
        $data=array();
        $row = Yii::app()->db->createCommand()->select("a.*,b.invoice_header,b.tax_id,b.invoice_address,b.invoice_rmk,b.invoice_number,b.invoice_user")
            ->from("sales{$suffix}.sal_clue_store a")
            ->leftJoin("sales{$suffix}.sal_clue_invoice b","a.invoice_id=b.id")
            ->where("a.id=:id",array(":id"=>$store_id))->queryRow();
        if($row){
            if($clientHeadRow["table_type"]!=2){
                $this->status_type="E";//不是客户列表内的门店不发消息
            }elseif ($clientHeadRow["group_bool"]=="Y"&&empty($clientHeadRow["u_id"])){
                //是集团客户且集团没有同步
                $uClientModel = new CurlNotesByClient();
                $uClientModel->putDataByClientID($clientHeadRow["id"]);
                if($uClientModel->status_type=="P"){
                    $uClientModel->setMinUrl($uClientModel->min_url);
                    $uClientModel->setOutContentByData();
                    $uClientModel->sendUByClientData();
                }
                $uClientModel->saveCurlToApi();
                $clueUID = Yii::app()->db->createCommand()->select("u_id")->from("sales{$suffix}.sal_clue")
                    ->where("id=:id",array(":id"=>$clientHeadRow["id"]))->queryRow();
                if($clueUID&&!empty($clueUID["u_id"])){
                    $clientHeadRow["u_id"]=$clueUID["u_id"];
                }else{
                    $this->status_type="Error";//客户同步失败
                    return false;
                }
            }
            $this->operation_type = empty($row["u_id"])?"insert":"update";
            $data = $this->getDataByRows($row,$clientHeadRow);
            if(!empty($row["u_id"])){
                $this->sendDataSetByUpdateStore();
            }else{
                $this->sendDataSetByAddStore();
            }
        }
        return $data;
    }
	
	public function getDataByRows($row,$clientHeadRow){
		$districtRow = self::getDistrictStrByKey($row["district"],"*");
		$districtName = "";
		$districtList = array();
		if($districtRow){
			$districtName=$districtRow["area_name"];
			$districtList = explode(",",$districtRow["parent_ids"]);
		}
		$data = array(
			"lbs_id"=>$row["id"],
			"customer_code"=>$row["store_code"],//编号 必需
			"ka_id"=>$clientHeadRow["group_bool"]=="Y"?$clientHeadRow["u_id"]:null,//KA项目/集团ID
			"lbs_city_office_code"=>$row["city"],//办公室
			"lbs_office_office_id"=>self::getOfficeStrByKey($row["office_id"],"u_id"),//办事处
			"billing_id"=>null,//开票信息ID
			"name_zh"=>$row["store_name"],//中文名称
			"name_en"=>null,//中文名称
			"name_ob"=>null,//中文名称
			"name_shop"=>$row["store_name"],//门店名称
			"name_ab"=>$row["store_full_name"],//公司简称
			"addr"=>$row["address"],//地址
			"area"=>$row["area"],//区域
			//"lat"=>$row["latitude"],//纬度
			//"lng"=>$row["longitude"],//经度
			"addr_remarks"=>null,//地址备注
			"name_bill"=>$row["invoice_header"],//开票名称
			"addr_bill"=>$row["invoice_address"],//开票地址
			"addr_bi_remarks"=>$row["tax_id"],//开票地址备注
			"tel"=>$row["cust_tel"],//电话
			"fax"=>null,//传真
			"email"=>$row["cust_email"],//邮箱
			"customer_type"=>self::getCustClassStrByKey($row["cust_class"],"rpt_u"),//客户类型（需要用到派单lbs_enum表enum_type=4中的数据）
			"status"=>self::getUStatusByStoreStatus($row["store_status"]),//状态 1 服务中 2 停止服务 3 其他
			"remarks"=>$row["store_remark"],//备注
			"street"=>$clientHeadRow["street"],//街道
			"sales_rep"=>self::getEmployeeStrByKey("code",$row["create_staff"]),//销售代表 （传员工编号）
			"inv_remarks"=>$row["invoice_rmk"],//发票备注
			"sort"=>0,//排序
			"external_customer_id"=>null,//外部客户ID
			"external_customer_number"=>null,//外部客户编号
			"external_source"=>null,//外部数据来源（1史伟莎 2中央KA 3马氏 4敏捷 5 利比斯）
			"province_id"=>isset($districtList[1])?$districtList[1]:"",//省份ID
			"city_id"=>isset($districtList[2])?$districtList[2]:(isset($districtList[1])?$districtList[1]:""),//市ID
			"county_id"=>isset($districtList[3])?$districtList[3]:"",//区级/县级ID
		);
		if(!empty($row["u_id"])){
			//$this->sendDataSetByUpdateStore();
			$data["customer_id"]=$row["u_id"];
			$data["update_uid"]=self::getEmployeeStrByUsername("code",$row["luu"]);//创建人
			$data["update_time"]=$row["lud"];//更新时间
		}else{
			//$this->sendDataSetByAddStore();
			$data["create_uid"]=self::getEmployeeStrByKey("code",$row["create_staff"]);//创建人
			$data["create_time"]=$row["lcd"];//创建时间
		}
		return $data;
	}

    //发送门店资料（且保存客户回传id）
    public function sendUByStoreData($errorBool=false){
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
        $this->sendCurl($errorBool);
        $this->endData();//保存返回结果
    }

    public function endData(){
        if($this->status_type=="C"){
            //成功后需要把派单系统的id存入CRM
            $rows = isset($this->outData["data"])?$this->outData["data"]:array();
            if(!empty($rows)){
                foreach ($rows as $row){
                    $updateArr = array(
                        "u_id"=>isset($row["customer_id"])?$row["customer_id"]:null
                    );
                    Yii::app()->db->createCommand()->update("sal_clue_store",$updateArr,"id=:id",array(":id"=>$row["lbs_id"]));

                }
            }
        }
    }

    //将门店联系人数据保存到api内
    public function putPersonDataByPersonID($person_id,$clientHeadRow){
        $data = $this->getPersonDataByPersonID($person_id,$clientHeadRow);
        $this->data=array(
            "operation_type"=>$this->operation_type,
            "data"=>array(),
        );
        $data=array($data);
        $this->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
    }
    //获取门店联系人数据
    public function getPersonDataByPersonID($person_id,$clueStoreRow){
        $suffix = Yii::app()->params['envSuffix'];
        $data=array();
        $personRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_person")
            ->where("id=:id",array(":id"=>$person_id))->queryRow();
        if($personRow){
            $this->operation_type = empty($personRow["u_id"])?"insert":"update";
            $data = array(
                "contact_id"=>$personRow["person_code"],
                "customer_id"=>isset($clueStoreRow["u_id"])?$clueStoreRow["u_id"]:null,//项目分组id
                "custome_code"=>$clueStoreRow["store_code"],//客户编号
                "lbs_id"=>$personRow["id"],
                "contact_name"=>$personRow["cust_person"],//联络人名称
                "mobile"=>$personRow["cust_tel"],//联络人手机
                "dept"=>$personRow["cust_person_role"],//部门
                "email"=>$personRow["cust_email"],//电邮
                "gender"=>self::getUGenderBySex($personRow["sex"]),//
                "status"=>$personRow["status"],//联络人状态 1 任职中 2 已离职 3 辞退 4 删除
                "tel"=>null,//
                "fax"=>null,//
                "line"=>null,//
                "password"=>null,//
                "sort"=>null,//
            );
            if(!empty($personRow["u_id"])){
                $this->sendDataSetByUpdateStorePerson();
                $data["id"]=$personRow["u_id"];
                $data["update_time"]=$personRow["lcd"];
                $data["update_uid"]=self::getEmployeeStrByUsername("code",$personRow["luu"]);
            }else{
                $this->sendDataSetByAddStorePerson();
                $data["create_time"]=$personRow["lcd"];
                $data["create_uid"]=self::getEmployeeStrByUsername("code",$personRow["lcu"]);
            }
        }
        return $data;
    }

    //发送门店联系人资料（且保存回传id）
    public function sendUByStorePersonData($errorBool=false){
        $this->sendCurl($errorBool);
        $this->endPersonData();
    }

    public function endPersonData(){
        if($this->status_type=="C"){
            //成功后需要把派单系统的id存入CRM
            $rows = isset($this->outData["data"])?$this->outData["data"]:array();
            if(!empty($rows)){
                foreach ($rows as $row){
                    Yii::app()->db->createCommand()->update("sal_clue_person",array(
                        "u_id"=>$row["id"],
                        "u_group_id"=>isset($row["group_id"])?$row["group_id"]:null,
                        "person_pws"=>1,
                    ),"id=:id",array(":id"=>$row["lbs_id"]));
                }
            }
        }
    }
}
