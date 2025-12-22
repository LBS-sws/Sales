<?php

class ClueHeadForm extends ClueForm
{
    public $clue_service_id=0;//当前商机id
    public $clueServiceRow=array();//当前商机


    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        $list = array();
        $list[]=array('id,full_name,end_date,table_type,last_date,rec_type,rec_employee_id,latitude,longitude,yewudalei,group_bool,cust_vip,clue_remark,clue_level_id,clue_tag_ids','safe');
        $list[]=array('clue_type,city,entry_date,cust_name,service_type,cust_class_group,cust_class','required','on'=>array('new','edit'));
        $list[]=array('clue_status,clue_code,street,address,clue_source,area,cust_person,cust_tel,cust_email,cust_address,cust_person_role','safe');
        if($this->clue_type==1){//地推
            $list[]=array('district,yewudalei','required','on'=>array('new','edit'));
        }else{
            $list[]=array('talk_city_id,cont_person,cont_tel,cont_email,cont_person_role,support_user,busine_id,district','safe');
            $list[]=array('cust_level,yewudalei,cust_type,cust_ka_class','required','on'=>array('new','edit'));
        }
        $list[]=array('id','validateID');
        $list[]=array('clue_type','validateClueType');
        $list[]=array('fileJson','validateFileJson');
        $listEx = $this->rulesEx();
        if(!empty($listEx)){
            $list = array_merge($list,$listEx);
        }
        return $list;
    }

    protected function retrieveSqlEx(){
        $staff_id = CGetName::getEmployeeIDByMy();
        $this->login_employee_id = $staff_id;
        $whereSql = " and a.rec_type=1 ";
        if(ClueHeadList::isReadAll()){
            $citylist = Yii::app()->user->city_allow();
            $whereSql.=" and a.city in ({$citylist}) ";
        }else{
            $user_id = Yii::app()->user->id;
            $groupIdStr = CGetName::getGroupStaffIDByStaffID($staff_id);
            $groupIdStr = implode(",",$groupIdStr);
            $whereSql.=" and (a.rec_employee_id in ({$groupIdStr}) or FIND_IN_SET('{$user_id}',a.extra_user)) ";
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
            "yewudalei"=>CGetName::getNumberNull($this->yewudalei),
            "group_bool"=>$this->clue_type==1?$this->group_bool:"Y",
            "cust_vip"=>$this->cust_vip,
            "cust_name"=>$this->cust_name,
            "full_name"=>$this->full_name,
            "service_type"=>empty($this->service_type)?null:json_encode($this->service_type),
            "clue_remark"=>$this->clue_remark,
            "clue_level_id"=>CGetName::getNumberNull($this->clue_level_id),
        );
        //clue_code
        if($this->getScenario()=="new"){
            $list["clue_status"]=0;
            $list["clue_type"]=$this->clue_type;
            $list["city"]=$this->city;
        }
        if($this->clue_type==1){//地推
            $list["district"]=CGetName::getNumberNull($this->district);
        }else{
            $list["district"]=CGetName::getNumberNull($this->district);
            $list["support_user"]=CGetName::getNumberNull($this->support_user);
            $list["cust_type"]=CGetName::getNumberNull($this->cust_type);
            $list["cust_level"]=CGetName::getNumberNull($this->cust_level);
            $list["cust_ka_class"]=CGetName::getNumberNull($this->cust_ka_class);
            $list["cont_person"]=$this->cont_person;
            $list["cont_tel"]=$this->cont_tel;
            $list["cont_email"]=$this->cont_email;
            $list["cont_person_role"]=$this->cont_person_role;
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
                $this->addClueStore();
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
                    "history_html"=>"<span>退回线索池</span>",
                    "lcu"=>$uid,
                ));
				break;
		}
		$this->sendFlow();
		return true;
	}

	protected function addClueStore(){
        $clueStoreModel = new ClueStoreForm("new");
        $clueStoreModel->clue_id=$this->id;
        $clueStoreModel->clue_type=$this->clue_type;
        $clueStoreModel->city=$this->city;
        $clueStoreModel->clueHeadRow = $this->getAttributes();
        $clueStoreModel->fastDataByClueHead();
        if($clueStoreModel->validate()){
            $clueStoreModel->computeSaveStore();
        }
    }

    protected function sendFlow(){
        switch ($this->getScenario()){
            case "back":
                $clueRow = Yii::app()->db->createCommand()->select("*")->from("sal_clue")
                    ->where("id=:id",array(":id"=>$this->id))->queryRow();
                $flowModel = new CNoticeFlowModel("CM02",$clueRow["id"]);
                $flowModel->note_type=2;//通知流程
                $flowModel->setMB_PC_Url("clueBox/edit",array("index"=>$clueRow["id"]));
                $subject="线索:".$clueRow['cust_name'].",已被退回请重新分配";
                $flowModel->setSubject($subject);
                $flowModel->setMessage($subject);
                $flowModel->addEmailToPrefixAndCity("CM01",$this->city);
                $flowModel->saveNoticeAll();
                break;
        }
    }

    public function ajaxChangeCustName($cust_name){
        $data=array("state"=>1,"html"=>"");
        $cust_name = str_replace("'","\'",$cust_name);
        $whereSql = "";
        if(ClueHeadList::isReadAll()){
            $citylist = Yii::app()->user->city_allow();
            $whereSql=" and city in ({$citylist})";
        }else{
            $user_id = Yii::app()->user->id;
            $staff_id = CGetName::getEmployeeIDByMy();
            $groupIdStr = CGetName::getGroupStaffIDByStaffID($staff_id);
            $groupIdStr = implode(",",$groupIdStr);
            $whereSql.=" and (rec_employee_id in ({$groupIdStr}) or FIND_IN_SET('{$user_id}',extra_user)) ";
        }
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_clue")
            ->where("rec_type=1 and cust_name like '%$cust_name%' {$whereSql}")
            ->order("LENGTH(cust_name) asc")->queryAll();
        if($rows){
            $data["state"]=1;
            $data["html"]='<div class="list-group">';
            foreach ($rows as $row){
                if($row["table_type"]==1){//线索
                    $link=Yii::app()->createUrl('clueHead/view',array("index"=>$row["id"]));
                }else{//客户
                    $link=Yii::app()->createUrl('clientHead/view',array("index"=>$row["id"]));
                }
                $data["html"].=TbHtml::link($row["cust_name"],$link,array("class"=>"list-group-item"));
            }
            $data["html"].='</div>';
        }
        return $data;
    }

    public function ajaxBlurCustName($city,$cust_name){
        $data=array("state"=>0,"html"=>"");
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_clue")
            ->where("cust_name=:cust_name",array(":cust_name"=>$cust_name))->queryRow();
        if($row){
            $data["state"]=1;
            if($row["rec_type"]==1){//线索已分配
                if($row["table_type"]==1){//线索
                    $link=Yii::app()->createUrl('clueHead/view',array("index"=>$row["id"]));
                }else{//客户
                    $link=Yii::app()->createUrl('clientHead/view',array("index"=>$row["id"]));
                }
                if(ClueHeadList::isReadAll()){
                    $citylist = Yii::app()->user->city_allow();
                    if (strpos($citylist,"'{$row['city']}'")!==false){
                        $data["html"]="客户名称已存在,可以点击“";
                        $data["html"].=TbHtml::link("去跟进拜访",$link,array("target"=>"_blank"));
                        $data["html"].="”查看详情";
                    }else{
                        $cityName = General::getCityName($row["city"]);
                        $staffName = CGetName::getEmployeeNameByKey($row["rec_employee_id"]);
                        $data["html"]="该客户已被{$cityName}城市的销售（{$staffName}）跟进，线索编号：{$row["clue_code"]}，可更换名称后创建";
                    }
                }else{
                    $staff_id = CGetName::getEmployeeIDByMy();
                    $groupIdStr = CGetName::getGroupStaffIDByStaffID($staff_id);
                    $groupIdStr = implode(",",$groupIdStr);
                    if (strpos(",{$groupIdStr},",",{$row['rec_employee_id']},")!==false){
                        $data["html"]="客户名称已存在,可以点击“";
                        $data["html"].=TbHtml::link("去跟进拜访",$link,array("target"=>"_blank"));
                        $data["html"].="”查看详情";
                    }else{
                        //$city = Yii::app()->user->city();
                        $staffName = CGetName::getEmployeeNameByKey($row["rec_employee_id"]);
                        if($row["city"]==$city){//同城市
                            $link.="&addStaff=1";
                            $data["html"]="该客户已被销售（{$staffName}）跟进，线索编号：{$row["clue_code"]}，可以点击“";
                            $data["html"].=TbHtml::link("去跟进拜访",$link,array("target"=>"_blank"));
                            $data["html"].="”查看详情跟进";
                        }else{
                            $cityName = General::getCityName($row["city"]);
                            $data["html"]="该客户已被{$cityName}城市的销售（{$staffName}）跟进，线索编号：{$row["clue_code"]}，可更换名称后创建";
                        }
                    }
                }
            }else{//线索在线索池内
                $cityName = General::getCityName($row["city"]);
                $data["html"]="该客户在城市({$cityName})的线索池内，线索编号：{$row["clue_code"]}，可联系销售经理、城市总分配或更换名称后创建";
            }
        }
        return $data;
    }

    public function isOccupied($index) {
        $rtn = true;//默认不允许删除
        if($this->retrieveData($index)){
            $uid = Yii::app()->user->id;
            if($this->lcu==$uid){ //线索内的删除必须是本人新增的才允许删除
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
}
