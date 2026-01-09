<?php

class ClientHeadForm extends ClueForm
{

    public function rulesEx(){
        $list = array(
            array('id','validateNew')
        );
        return $list;
    }

    public function validateNew($attribute, $param) {
        if ($this->getScenario()=='new') {
            if(!Yii::app()->user->validRWFunction('CM10')){
                $this->addError($attribute, "您需要开通（派单数据导入）权限");
            }
        }
    }

    public function attributeLabels()
    {
        $list = parent::attributeLabels();
        $list["clue_code"]=Yii::t('clue','client code');
        $list["clue_status"]=Yii::t('clue','client status');
        $list["clue_type"]=Yii::t('clue','type');
        $list["clue_remark"]=Yii::t('clue','client remark');
        return $list;
    }

    public $clue_service_id=0;//当前商机id
    public $clueServiceRow=array();//当前商机

    protected function retrieveSqlEx(){
        $staff_id = CGetName::getEmployeeIDByMy();
        $this->login_employee_id = $staff_id;
        $whereSql = " and a.rec_type=1 ";
        if(ClientHeadList::isReadAll()){
            $citylist = Yii::app()->user->city_allow();
            $whereSql.=" and a.city in ({$citylist}) ";
        }else{
            $user_id = Yii::app()->user->id;
            $groupIdStr = CGetName::getGroupStaffIDByStaffID($staff_id);
            $groupIdStr = implode(",",$groupIdStr);
            // 客户的销售或门店关联客户的销售都能看到该客户数据
            $whereSql.=" and (a.rec_employee_id in ({$groupIdStr}) or FIND_IN_SET('{$user_id}',a.extra_user) or EXISTS(SELECT 1 FROM sal_clue_store s WHERE s.clue_id=a.id AND s.create_staff in ({$groupIdStr}))) ";
        }
        return $whereSql;
    }
    public function setClueServiceID($clue_service_id=0){
        $staff_id = CGetName::getEmployeeIDByMy();
        $canSelectAny = Yii::app()->user->validRWFunction('CM02') || Yii::app()->user->validRWFunction('CM10');
        if(!empty($clue_service_id)&&is_numeric($clue_service_id)){
            $row = Yii::app()->db->createCommand()->select("*")->from("sal_clue_service")
                ->where("id=:id",array(":id"=>$clue_service_id))->queryRow();
            if($row&&$row["clue_id"]==$this->id){
                if(ClientHeadList::isReadAll()){
                    $this->clue_service_id = $row["id"];
                    $this->clueServiceRow = $row;
                    return true;
                }elseif($canSelectAny){
                    $this->clue_service_id = $row["id"];
                    $this->clueServiceRow = $row;
                    return true;
                }else{
                    $groupIdStr = CGetName::getGroupStaffIDByStaffID($staff_id);
                    $groupIdStr[]=$staff_id;
                    if(in_array($row["create_staff"],$groupIdStr)){
                        $this->clue_service_id = $row["id"];
                        $this->clueServiceRow = $row;
                        return true;
                    }
                }
            }
        }
        $whereSql = "";
        if(!ClientHeadList::isReadAll() && !$canSelectAny){
            $groupIds = CGetName::getGroupStaffIDByStaffID($staff_id);
            $groupIds[] = $staff_id;
            $groupIds = array_values(array_unique(array_filter($groupIds)));
            if(!empty($groupIds)){
                $whereSql = " and create_staff in (".implode(",", $groupIds).")";
            }else{
                $whereSql = " and create_staff=".$staff_id;
            }
        }
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_clue_service")
            ->where("clue_id=:id {$whereSql}",array(":id"=>$this->id))
            ->order("id desc")->queryRow();
        if($row){
            $this->clue_service_id = $row["id"];
            $this->clueServiceRow = $row;
            return true;
        }
        // 初始化空的商机数据，避免视图访问时出错
        $this->clue_service_id = 0;
        $this->clueServiceRow = array(
            'id' => 0,
            'service_status' => 0,
            'total_amt' => 0
        );
        return true;
    }

    protected function getSaveList(){
        $list=array(
            "entry_date"=>$this->entry_date,
            "clue_source"=>CGetName::getNumberNull($this->clue_source),
            "cust_class_group"=>CGetName::getNumberNull($this->cust_class_group),
            "cust_class"=>CGetName::getNumberNull($this->cust_class),
            "street"=>$this->street,
            "address"=>$this->address,
            "cust_address"=>$this->cust_address,
            "area"=>$this->area,
            "cust_person"=>$this->cust_person,
            "cust_tel"=>$this->cust_tel,
            "cust_email"=>$this->cust_email,
            "cust_person_role"=>$this->cust_person_role,
            "latitude"=>$this->latitude,
            "longitude"=>$this->longitude,
            "cust_vip"=>$this->cust_vip,
            "cust_ka_class"=>$this->cust_ka_class,
            "yewudalei"=>CGetName::getNumberNull($this->yewudalei),
            "cust_name"=>$this->cust_name,
            "full_name"=>$this->full_name,
            "service_type"=>empty($this->service_type)?null:json_encode($this->service_type),
            "clue_remark"=>$this->clue_remark,
            "group_bool"=>$this->clue_type==1?$this->group_bool:"Y",
            "clue_level_id"=>empty($this->clue_level_id) ? null : $this->clue_level_id,
            "clue_tag"=>is_array($this->clue_tag_ids) ? implode(',', $this->clue_tag_ids) : $this->clue_tag_ids,
            "rec_employee_id"=>CGetName::getNumberNull($this->rec_employee_id),
        );
        //client_code
        if($this->getScenario()=="new"){
            $list["clue_status"]=0;
            $list["clue_type"]=$this->clue_type;
            $list["city"]=$this->city;
            $list["table_type"]=2;
        }
        if($this->clue_type==1){//地推
            $list["district"]=CGetName::getNumberNull($this->district);
        }else{
            $list["district"]=CGetName::getNumberNull($this->district);
            $list["support_user"]=CGetName::getNumberNull($this->support_user);
            $list["cust_type"]=CGetName::getNumberNull($this->cust_type);
            $list["cust_level"]=CGetName::getNumberNull($this->cust_level);
            $list["busine_id"]=empty($this->busine_id)?null:json_encode($this->busine_id);
            $list["talk_city_id"]=empty($this->talk_city_id)?null:json_encode($this->talk_city_id);
        }
        return $list;
    }

	protected function save(&$connection)
	{
		$sql = '';
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city;
        $saveList = $this->getSaveList();
		switch ($this->scenario) {
			case 'delete':
                $connection->createCommand()->delete("sal_clue","id=:id",array(":id"=>$this->id));
                $connection->createCommand()->delete("sal_clue_history","table_id=:id and table_type=1",array(":id"=>$this->id));
                $connection->createCommand()->delete("sal_clue_u_staff","clue_id=:id",array(":id"=>$this->id));
                $connection->createCommand()->delete("sal_clue_u_area","clue_id=:id",array(":id"=>$this->id));
                $connection->createCommand()->delete("sal_clue_person","clue_id=:id",array(":id"=>$this->id));
                $connection->createCommand()->delete("sal_clue_store","clue_id=:id",array(":id"=>$this->id));
                break;
			case 'new':
                $saveList["lcu"]=$uid;
                $saveList["rec_type"]=1;
                $saveList["rec_employee_id"]=CGetName::getEmployeeIDByUserName($uid);
                $connection->createCommand()->insert("sal_clue",$saveList);
                $this->id = Yii::app()->db->getLastInsertID();
                $this->computeClueCode();
                $connection->createCommand()->update("sal_clue",array(
                    "abbr_code"=>$this->abbr_code,
                    "clue_code"=>$this->clue_code,
                ),"id=:id",array(":id"=>$this->id));
                $connection->createCommand()->insert("sal_clue_history",array(
                    "table_id"=>$this->id,
                    "table_type"=>1,
                    "history_type"=>1,
                    "history_html"=>"<span>我的新增</span>",
                    "lcu"=>$uid,
                ));
                ClueUAreaForm::saveUAreaData($this->id,$this->city);
                ClueUStaffForm::saveUStaffData($this->id,$this->rec_employee_id);
                ClientPersonForm::saveUPersonDataByClueModel($this);
				break;
			case 'edit':
                $saveList["luu"]=$uid;
                $connection->createCommand()->update("sal_clue",$saveList,"id=:id",array(":id"=>$this->id));
                ClueUAreaForm::saveUAreaData($this->id,$this->city);
                ClueUStaffForm::saveUStaffData($this->id,$this->rec_employee_id);
                ClientPersonForm::saveUPersonDataByClueModel($this);
				break;
			case 'back':
                $connection->createCommand()->update("sal_clue",array(
                    "rec_type"=>2,
                    "luu"=>$uid
                ),"id=:id",array(":id"=>$this->id));
                $connection->createCommand()->insert("sal_clue_history",array(
                    "table_id"=>$this->id,
                    "table_type"=>1,
                    "history_type"=>2,
                    "history_html"=>"<span>退回客户池</span>",
                    "lcu"=>$uid,
                ));
				break;
		}
		$this->sendDataByU();
		return true;
	}

    public function isOccupied($index) {
        $rtn = true;//默认不允许删除
        if($this->retrieveData($index)){
            $uid = Yii::app()->user->id;
            if($this->lcu==$uid){ //客户内的删除必须是本人新增的才允许删除
                $sql = "select a.id from sal_clue_service a where a.clue_id=".$index." ";
                $row = Yii::app()->db->createCommand($sql)->queryRow();
                $rtn = ($row !== false);
            }
        }
        return $rtn;
    }

    public function isReadonly() {
        return $this->getScenario()=='view';
    }

    //发送数据至派单系统
    public function sendDataByU(){
        if(in_array($this->getScenario(),array("new","edit"))){
            $uClientModel = new CurlNotesByClient();
            $uClientModel->putDataByClientID($this->id);
            $uClientModel->setOutContentByData();
            $uClientModel->saveCurlToApi();

            if($this->update_group_bool&&!empty($this->u_id)){//修改了集团属性
                $uStoreModel = new CurlNotesByStore();
                $uStoreModel->resetAllGroupBool($this->getAttributes());
            }
        }
    }
}
