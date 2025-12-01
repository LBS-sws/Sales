<?php

class ClueBoxForm extends ClueForm
{
    public $select_assign;


    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        $list = array();
        $list[]=array('id,full_name,end_date,table_type,last_date,rec_type,rec_employee_id,latitude,longitude,yewudalei,group_bool,cust_vip,clue_remark','safe');
        $list[]=array('clue_type,city,entry_date,cust_name,cust_person,cust_tel','required');
        $list[]=array('clue_status,clue_code,street,address,service_type,cust_class_group,cust_class,clue_source,area,cust_person,cust_tel,cust_email,cust_address,cust_person_role,district','safe');
        if($this->clue_type==1){//地推
            //$list[]=array('district','required');
        }else{
            $list[]=array('cust_level,cust_type,cust_ka_class,talk_city_id,cont_person,cont_tel,cont_email,cont_person_role,support_user,busine_id,district','safe');
            //$list[]=array('cust_level,cust_type,cust_ka_class','required');
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

    public function validateSelect($selectStr){
        if(empty($selectStr)){
            $this->addError("id", "请选择需要分配的线索");
            return false;
        }else{
            $this->select_assign = array();
            $list = explode(",",$selectStr);
            if(is_array($list)){
                foreach ($list as $id){
                    if($this->retrieveData($id)){
                        $this->select_assign[]=$id;
                    }
                }
            }
        }
        return true;
    }

    public function assignValidate($select_assign){
        if(!$this->validateSelect($select_assign)){
            return false;
        }
        $assignTypeList = CGetName::getAssignTypeList();
        $assign_type = isset($_GET['assign_type'])?$_GET['assign_type']:0;
        $assign_city = isset($_GET['assign_city'])?$_GET['assign_city']:0;
        $assign_employee = isset($_GET['assign_employee'])?$_GET['assign_employee']:0;
        if(isset($assignTypeList[$assign_type])){
            $this->rec_type = $assign_type;
            switch ($assign_type){
                case 1://员工
                    if(empty($assign_employee)){
                        $this->addError("id", "分配员工不能为空");
                        return false;
                    }
                    break;
                case 2://城市
                    if(empty($assign_city)){
                        $this->addError("id", "分配城市不能为空");
                        return false;
                    }
                    break;
                case 3://抢单
                    if(empty($assign_city)){
                        $this->addError("id", "分配城市不能为空");
                        return false;
                    }
                    break;
            }
            $this->rec_employee_id = $assign_employee;
            $this->city = $assign_city;
        }else{
            $this->addError("id", "分配类型异常");
            return false;
        }
        return true;
    }

    public function saveAssign(){
        $uid = Yii::app()->user->id;
        $saveList = array(
            "rec_type"=>$this->rec_type,
            "luu"=>Yii::app()->user->id,
        );
        $history_html=array("<span>分配</span>");
        switch ($this->rec_type){
            case 1:
                $saveList["rec_employee_id"]=$this->rec_employee_id;
                $history_html[]="<span>分配员工:".CGetName::getEmployeeNameByKey($this->rec_employee_id)."</span>";
                break;
            case 2:
                $saveList["city"]=$this->city;
                $history_html[]="<span>分配城市:".General::getCityName($this->city)."</span>";
                break;
            case 3:
                $saveList["city"]=$this->city;
                $history_html[]="<span>销售抢单:".General::getCityName($this->city)."</span>";
                break;
        }
        if(!empty($this->select_assign)&&is_array($this->select_assign)){
            foreach ($this->select_assign as $id){
                Yii::app()->db->createCommand()->update("sal_clue",$saveList,"id=:id",array(":id"=>$id));
                Yii::app()->db->createCommand()->insert("sal_clue_history",array(
                    "table_id"=>$id,
                    "table_type"=>1,
                    "history_type"=>2,
                    "history_html"=>implode("<br/>",$history_html),
                    "lcu"=>$uid,
                ));
                if($this->rec_type==1){
                    ClueUAreaForm::saveUAreaData($this->id,$this->city);
                    ClueUStaffForm::saveUStaffData($this->id,$this->rec_employee_id);
                }
                $clueRow = Yii::app()->db->createCommand()->select("*")->from("sal_clue")
                    ->where("id=:id",array(":id"=>$id))->queryRow();
                $this->sendFlow($clueRow);
            }
        }
    }

    protected function sendFlow($clueRow){
        switch ($this->rec_type){
            case 1:
                $flowModel = new CNoticeFlowModel("CM02",$clueRow["id"]);
                $flowModel->note_type=2;//通知流程
                $flowModel->setMB_PC_Url("clueHead/view",array("index"=>$clueRow["id"]));
                $subject="线索:".$clueRow["cust_name"].",请及时跟进";
                $flowModel->setSubject($subject);
                $flowModel->setMessage($subject);
                $flowModel->addEmailToStaffId($this->rec_employee_id);
                $flowModel->saveNoticeAll();
                break;
            case 2:
                $flowModel = new CNoticeFlowModel("CM01",$clueRow["id"]);
                $flowModel->note_type=2;//通知流程
                $flowModel->setMB_PC_Url("clueBox/edit",array("index"=>$clueRow["id"]));
                $subject="线索:".$clueRow["cust_name"].",请分配销售";
                $flowModel->setSubject($subject);
                $flowModel->setMessage($subject);
                $flowModel->addEmailToPrefixAndCity("CM01",$this->city);
                $flowModel->saveNoticeAll();
                break;
        }
    }

    public function saveDelete(){
        if(!empty($this->select_assign)&&is_array($this->select_assign)){
            foreach ($this->select_assign as $id){
                Yii::app()->db->createCommand()->delete("sal_clue","id=:id",array(":id"=>$id));
                Yii::app()->db->createCommand()->delete("sal_clue_history","table_id=:id and table_type=1",array(":id"=>$id));
            }
        }
    }

    protected function retrieveSqlEx(){
        if(!Yii::app()->user->validRWFunction('CM01')){//唯读权限
            $sql=" and a.rec_type=3 ";
        }else{
            $sql=" and a.rec_type!=1 ";
        }
        return $sql;
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
            "cust_person_role"=>$this->cust_person_role,
            "latitude"=>$this->latitude,
            "longitude"=>$this->longitude,
            "yewudalei"=>CGetName::getNumberNull($this->yewudalei),
            "cust_name"=>$this->cust_name,
            "full_name"=>$this->full_name,
            "service_type"=>empty($this->service_type)?null:json_encode($this->service_type),
            "clue_remark"=>$this->clue_remark,
            "group_bool"=>$this->clue_type==1?$this->group_bool:"Y",
            "box_type"=>1,
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
                $saveList["rec_type"]=2;
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
                    "history_html"=>"<span>新增</span>",
                    "lcu"=>$uid,
                ));
                ClueUAreaForm::saveUAreaData($this->id,$this->city);
                ClientPersonForm::saveUPersonDataByClueModel($this);
				break;
			case 'edit':
                $saveList["luu"]=$uid;
                $connection->createCommand()->update("sal_clue",$saveList,"id=:id",array(":id"=>$this->id));
                ClueUAreaForm::saveUAreaData($this->id,$this->city);
                ClientPersonForm::saveUPersonDataByClueModel($this);
				break;
		}
		return true;
	}
}
