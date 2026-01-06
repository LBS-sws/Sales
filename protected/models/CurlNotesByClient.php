<?php

class CurlNotesByClient extends CurlNotesModel {
    //将客户数据保存到api内
    public function putDataByClientID($client_id){
		$data=array();
		if(is_array($client_id)){
			foreach($client_id as $row){
				$data[] = $this->getDataByClientID($row['clue_id']);
			}
		}else{
			$data[] = $this->getDataByClientID($client_id);
		}
        $this->data=array(
            "operation_type"=>$this->operation_type,
            "data"=>array(),
        );
        $this->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
    }

    //获取客户数据
    public function getDataByClientID($client_id){
        $suffix = Yii::app()->params['envSuffix'];
        $data=array();
        $row = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue")
            ->where("id=:id",array(":id"=>$client_id))->queryRow();
        if($row){
            if($row["group_bool"]=="N"&&empty($row["u_id"])){
                $this->status_type="Error";//不是集团客户不发消息
                return false;
            }
            $this->operation_type = empty($row["u_id"])?"insert":"update";
            switch ($row["clue_status"]){
                case 5:
                case 10:
                case 30:
                    $project_status=1;
                    break;
                case 40:
                case 50:
                    $project_status=2;
                    break;
                case 60:
                    $project_status=3;
                    break;
                default:
                    $project_status=3;
            }
            $data = array(
                "lbs_id"=>$row["id"],
                "project_name"=>$row["cust_name"],//名称 必需
                "project_code"=>$row["clue_code"],//编号 必需
                //"lbs_city_office_code"=>$row["city"],//办公室
                //"lbs_office_office_id"=>"",//集团没有办事处
                "project_manager"=>null,//项目负责人
                "city_ids"=>null,//城市代号
                "importance"=>null,//重要程度
                "start_date"=>date_format(date_create($row["lcd"]),"Y-m-d"),//开始日期 必需
                "end_date"=>null,//结束日期
                "remarks"=>null,//备注
                "project_status"=>$project_status,//项目状态 必需 未开始0,进行中1,已暂停2,已完成3
                "business_id"=>self::getYewudaleiStrByKey($row["yewudalei"],"u_id"),//业务大类 必需
                "type"=>$row["clue_type"]==2?1:2,//类型 1 KA项目 2 集团
                "project_region"=>array(),//集团/项目所属区域
                "project_staff"=>array(),//集团/项目负责人
                "project_contact"=>array(),//集团/项目分组联络人
            );
            if(!empty($row["u_id"])){
                $this->sendDataSetByUpdateClient();
                $data["project_id"]=$row["u_id"];
                $data["update_uid"]=self::getEmployeeStrByUsername("code",$row["luu"]);//创建人
                $data["updated_at"]=$row["lud"];//更新时间
            }else{
                $this->sendDataSetByAddClient();
                $data["create_uid"]=self::getEmployeeStrByKey("code",$row["rec_employee_id"]);//创建人
                $data["created_at"]=$row["lcd"];//创建时间
            }
            $areaRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_u_area")
                ->where("clue_id=:id",array(":id"=>$client_id))->queryAll();
            if($areaRows){
                foreach ($areaRows as $areaRow){
                    if($this->operation_type=="insert"&&!empty($areaRow["u_id"])){//如果新增时，有id则跳过
                        continue;
                    }
                    if($this->operation_type=="update"&&empty($areaRow["u_id"])){//如果修改时，没有id则跳过
                        continue;
                    }
                    $temp=array(
                        "lbs_id"=>$areaRow["id"],
                        "city_id"=>$areaRow["city_code"],
                        "create_time"=>$areaRow["lcd"],
                        "create_uid"=>self::getEmployeeStrByUsername("code",$areaRow["lcu"]),
                    );
                    if(!empty($areaRow["u_id"])){
                        $temp["project_id"]=$row["u_id"];
                        $temp["id"]=$areaRow["u_id"];
                        $temp["update_at"]=$areaRow["lud"];
                        $temp["update_uid"]=self::getEmployeeStrByUsername("code",$areaRow["luu"]);
                    }
                    $data["project_region"][]=$temp;
                }
            }
            $staffRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_u_staff")
                ->where("clue_id=:id",array(":id"=>$client_id))->queryAll();
            if($staffRows){
                foreach ($staffRows as $staffRow){
                    if($this->operation_type=="insert"&&!empty($staffRow["u_id"])){//如果新增时，有id则跳过
                        continue;
                    }
                    if($this->operation_type=="update"&&empty($staffRow["u_id"])){//如果修改时，没有id则跳过
                        continue;
                    }
                    $temp=array(
                        "lbs_id"=>$staffRow["id"],
                        "staff_id"=>self::getEmployeeStrByKey("code",$staffRow["employee_id"]),
                        "is_delete"=>0,
                    );
                    if(!empty($staffRow["u_id"])){
                        $temp["project_id"]=$row["u_id"];
                        $temp["id"]=$staffRow["u_id"];
                        //$temp["update_at"]=$staffRow["lud"];
                        //$temp["update_uid"]=self::getEmployeeStrByUsername("code",$staffRow["luu"]);
                    }else{
                        //$temp["create_time"]=$staffRow["lcd"];
                        //$temp["create_uid"]=self::getEmployeeStrByUsername("code",$staffRow["lcu"]);
                    }
                    $data["project_staff"][]=$temp;
                }
            }
            // 只同步未删除的联络人（status!=4）
            $personRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_person")
                ->where("clue_id=:id and clue_store_id=0 and status!=4",array(":id"=>$client_id))->queryAll();
            if($personRows){
                foreach ($personRows as $personRow){
                    if($this->operation_type=="insert"&&!empty($personRow["u_id"])){//如果新增时，有id则跳过
                        continue;
                    }
                    if($this->operation_type=="update"&&empty($personRow["u_id"])){//如果修改时，没有id则跳过
                        continue;
                    }
                    $temp=array(
                        "lbs_id"=>$personRow["id"],
                        //"group_id"=>$personRow["u_id"],//项目分组id
                        "name"=>$personRow["cust_person"],//联络人名称
                        "phone"=>$personRow["cust_tel"],//联络人手机
                        "department"=>$personRow["cust_person_role"],//部门
                        "email"=>$personRow["cust_email"],//电邮
                        "gender"=>self::getUGenderBySex($personRow["sex"]),//性别 1 男 2 女 3 其他
                        "status"=>$personRow["status"],//联络人状态 1 任职中 2 已离职 3 辞退 4 删除
                        "tel"=>null,//
                        "fax"=>null,//
                        "Line"=>null,//
                        "password"=>null,//
                    );
                    if(!empty($personRow["u_id"])){
                        $temp["project_id"]=$row["u_id"];
                        $temp["group_id"]=$row["u_group_id"];
                        $temp["id"]=$personRow["u_id"];
                        $temp["update_at"]=$personRow["lud"];
                        $temp["update_uid"]=self::getEmployeeStrByUsername("code",$personRow["luu"]);
                    }else{
                        $temp["group_id"]=$row["u_group_id"];
                        $temp["create_at"]=$personRow["lcd"];
                        $temp["create_uid"]=self::getEmployeeStrByUsername("code",$personRow["lcu"]);
                    }
                    $data["project_contact"][]=$temp;
                }
            }
        }
        return $data;
    }

    //发送客户资料（且保存客户回传id）
    public function sendUByClientData($errorBool=false){
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
                    //project_group
                    $updateArr = array(
                        "u_id"=>$row["project_id"]
                    );
                    //修改
                    if(!empty($row["project_group"])&&is_array($row["project_group"])){
                        foreach ($row["project_group"] as $infoRow){
                            $updateArr["u_group_id"] = $infoRow["id"];
                            break;
                        }
                    }
                    Yii::app()->db->createCommand()->update("sal_clue",$updateArr,"id=:id",array(":id"=>$row["lbs_id"]));
                    //修改联络人
                    if(!empty($row["project_contact"])&&is_array($row["project_contact"])){
                        foreach ($row["project_contact"] as $infoRow){
                            Yii::app()->db->createCommand()->update("sal_clue_person",array(
                                "u_id"=>$infoRow["id"],
                                "u_group_id"=>$infoRow["group_id"],
                            ),"id=:id and clue_id=:clue_id",array(":id"=>$infoRow["lbs_id"],":clue_id"=>$row["lbs_id"]));
                        }
                    }
                    //修改区域
                    if(!empty($row["project_region"])&&is_array($row["project_region"])){
                        foreach ($row["project_region"] as $infoRow){
                            Yii::app()->db->createCommand()->update("sal_clue_u_area",array(
                                "u_id"=>$infoRow["id"],
                            ),"id=:id and clue_id=:clue_id",array(":id"=>$infoRow["lbs_id"],":clue_id"=>$row["lbs_id"]));
                        }
                    }
                    //修改负责人
                    if(!empty($row["project_staff"])&&is_array($row["project_staff"])){
                        foreach ($row["project_staff"] as $infoRow){
                            Yii::app()->db->createCommand()->update("sal_clue_u_staff",array(
                                "u_id"=>$infoRow["id"],
                            ),"id=:id and clue_id=:clue_id",array(":id"=>$infoRow["lbs_id"],":clue_id"=>$row["lbs_id"]));
                        }
                    }
                }
            }
        }
    }

    //将客户联系人数据保存到api内
    public function putPersonDataByPersonID($person_id,$clientHeadRow,$scenario=null){
        $data = $this->getPersonDataByPersonID($person_id,$clientHeadRow,$scenario);
        // 如果返回空数组，说明无法同步（如删除操作但u_id为空），跳过
        if(empty($data)){
            return;
        }
        $this->data=array(
            "operation_type"=>$this->operation_type,
            "data"=>array(),
        );
        $data=array($data);
        $this->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
    }
    //获取客户联系人数据
    public function getPersonDataByPersonID($person_id,$clientHeadRow,$scenario=null){
        $suffix = Yii::app()->params['envSuffix'];
        $data=array();
        $personRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_person")
            ->where("id=:id",array(":id"=>$person_id))->queryRow();
        if($personRow){
            // 删除操作：必须有u_id才能同步删除
            if($scenario === "delete"){
                if(empty($personRow["u_id"])){
                    // 如果删除时u_id为空，说明未同步到派单系统，不需要同步删除
                    return array();
                }
                $this->operation_type = "update";
            }else{
                // 新增或编辑操作：根据u_id判断
                $this->operation_type = empty($personRow["u_id"])?"insert":"update";
            }
            $data = array(
                "lbs_id"=>$personRow["id"],
                "project_id"=>$clientHeadRow["u_id"],//项目分组id
                "group_id"=>$clientHeadRow["u_group_id"],//项目分组id
                "name"=>$personRow["cust_person"],//联络人名称
                "phone"=>$personRow["cust_tel"],//联络人手机
                "department"=>$personRow["cust_person_role"],//部门
                "email"=>$personRow["cust_email"],//电邮
                "gender"=>self::getUGenderBySex($personRow["sex"]),//
                "status"=>$personRow["status"],//联络人状态 1 任职中 2 已离职 3 辞退 4 删除
                "tel"=>null,//
                "fax"=>null,//
                "Line"=>null,//
                "password"=>null,//
            );
            if(!empty($personRow["u_id"])){
                $this->sendDataSetByUpdateClientPerson();
                $data["id"]=$personRow["u_id"];
                $data["update_at"]=$personRow["lud"];
                $data["update_uid"]=self::getEmployeeStrByUsername("code",$personRow["luu"]);
            }else{
                $this->sendDataSetByAddClientPerson();
                $data["create_at"]=$personRow["lcd"];
                $data["create_uid"]=self::getEmployeeStrByUsername("code",$personRow["lcu"]);
            }
        }
        return $data;
    }
    //发送客户联系人资料（且保存回传id）
    public function sendUByClientPersonData($errorBool=false){
        $this->sendCurl($errorBool);
        if($this->status_type=="C"){
            //成功后需要把派单系统的id存入CRM
            $rows = isset($this->outData["data"])?$this->outData["data"]:array();
            if(!empty($rows)){
                foreach ($rows as $row){
                    Yii::app()->db->createCommand()->update("sal_clue_person",array(
                        "u_id"=>$row["id"],
                        "u_group_id"=>isset($row["group_id"])?$row["group_id"]:null,
                    ),"id=:id",array(":id"=>$row["lbs_id"]));
                }
            }
        }
    }

    //将客户归属区域数据保存到api内
    public function putAreaDataByAreaID($area_id,$clientHeadRow){
        $data = $this->getAreaDataByAreaID($area_id,$clientHeadRow);
        $this->data=array(
            "operation_type"=>$this->operation_type,
            "data"=>array(),
        );
        $data=array($data);
        $this->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
    }
    //获取客户归属区域数据
    public function getAreaDataByAreaID($area_id,$clientHeadRow){
        $suffix = Yii::app()->params['envSuffix'];
        $data=array();
        $personRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_u_area")
            ->where("id=:id",array(":id"=>$area_id))->queryRow();
        if($personRow){
            $this->operation_type = empty($personRow["u_id"])?"insert":"update";
            $data = array(
                "lbs_id"=>$personRow["id"],
                "project_id"=>$clientHeadRow["u_id"],//项目分组id
                "city_id"=>$personRow["city_code"],//
                "create_time"=>$personRow["lcd"],//
                "create_uid"=>self::getEmployeeStrByUsername("code",$personRow["lcu"]),//
            );
            if(!empty($personRow["u_id"])){
                $this->sendDataSetByUpdateClientArea();
                $data["id"]=$personRow["u_id"];
                $data["update_at"]=$personRow["lcd"];
                $data["update_uid"]=self::getEmployeeStrByUsername("code",$personRow["luu"]);
            }
        }
        return $data;
    }
    //发送客户归属区域资料（且保存回传id）
    public function sendUByClientAreaData($errorBool=false){
        $this->sendCurl($errorBool);
        if($this->status_type=="C"){
            //成功后需要把派单系统的id存入CRM
            $rows = isset($this->outData["data"])?$this->outData["data"]:array();
            if(!empty($rows)){
                foreach ($rows as $row){
                    Yii::app()->db->createCommand()->update("sal_clue_u_area",array(
                        "u_id"=>$row["id"],
                    ),"id=:id",array(":id"=>$row["lbs_id"]));
                }
            }
        }
    }

    //将客户负责人数据保存到api内
    public function putStaffDataByStaffID($person_id,$clientHeadRow){
        $data = $this->getStaffDataByStaffID($person_id,$clientHeadRow);
        $this->data=array(
            "operation_type"=>$this->operation_type,
            "data"=>array(),
        );
        $data=array($data);
        $this->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
    }
    //获取客户负责人数据
    public function getStaffDataByStaffID($person_id,$clientHeadRow){
        $suffix = Yii::app()->params['envSuffix'];
        $data=array();
        $personRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_u_staff")
            ->where("id=:id",array(":id"=>$person_id))->queryRow();
        if($personRow){
            $this->operation_type = empty($personRow["u_id"])?"insert":"update";
            $data = array(
                "lbs_id"=>$personRow["id"],
                "project_id"=>$clientHeadRow["u_id"],//项目分组id
                "staff_id"=>self::getEmployeeStrByKey("code",$personRow["employee_id"]),//
                "is_delete"=>0,//
            );
            if(!empty($personRow["u_id"])){
                $this->sendDataSetByUpdateClientStaff();
                $data["id"]=$personRow["u_id"];
                //$data["update_at"]=$personRow["lcd"];
                //$data["update_uid"]=self::getEmployeeStrByUsername("code",$personRow["luu"]);
            }else{
                $this->sendDataSetByAddClientStaff();
                //$data["create_time"]=$personRow["lcd"];
                //$data["create_uid"]=self::getEmployeeStrByUsername("code",$personRow["lcu"]);
            }
        }
        return $data;
    }
    //发送客户负责人资料（且保存回传id）
    public function sendUByClientStaffData($errorBool=false){
        $this->sendCurl($errorBool);
        if($this->status_type=="C"){
            //成功后需要把派单系统的id存入CRM
            $rows = isset($this->outData["data"])?$this->outData["data"]:array();
            if(!empty($rows)){
                foreach ($rows as $row){
                    Yii::app()->db->createCommand()->update("sal_clue_u_staff",array(
                        "u_id"=>$row["id"],
                    ),"id=:id",array(":id"=>$row["lbs_id"]));
                }
            }
        }
    }
}
