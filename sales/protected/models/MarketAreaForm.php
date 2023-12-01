<?php

class MarketAreaForm extends MarketCompanyForm
{
    protected $emailTop=true;//管理员邮件 false：关闭
    protected $emailCity=false;//城市邮件 false：关闭
    protected $emailSales=true;//销售邮件 false：关闭

    public function validateID($attribute, $params) {
        if ($this->getScenario()!='new'){
            $city = Yii::app()->user->city();
            $index = is_numeric($this->id)?$this->id:0;
            $sql = "select a.status_type,a.allot_type,a.z_index from sal_market a where a.id='{$index}' and a.allot_city='{$city}'";
            $row = Yii::app()->db->createCommand($sql)->queryRow();
            if($row){
                $this->status_type = $row["status_type"];
                if(!MarketFun::isAssignForArea($row)){//分配给员工后不允许修改
                    $detail = $this->detail;
                    $this->retrieveData($index);
                    $this->detail = $detail;
                }
            }else{
                $this->addError($attribute, "数据异常，请刷新重试！");
            }
        }
    }

	public function retrieveData($index){
        $index = is_numeric($index)?intval($index):0;
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city();
        $whereSql = " and a.allot_city='{$city}'";
		$sql = "select a.*,docman$suffix.countdoc('market',a.id) as marketcountdoc from sal_market a where a.id='{$index}' {$whereSql}";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
        $this->no_of_attm['market'] = $row['marketcountdoc'];
        $arr = $this->getMyAttr();
		if ($row!==false) {
			foreach ($arr as $key => $type){
			    switch ($type){
                    case 1://原值
                        $this->$key = $row[$key];
                        break;
                    case 2://日期
                        $this->$key = empty($row[$key])?null:General::toDate($row[$key]);
                        break;
                    case 3://数字
                        $this->$key = $row[$key]===null?null:floatval($row[$key]);
                        break;
                    default:
                }
            }

            $infoRows = Yii::app()->db->createCommand()
                ->select("a.id,a.market_id,a.lcu,a.info_date,a.info_text,a.state_id")
                ->from("sal_market_info a")
                ->where("a.market_id={$index} and a.del_bool=0")
                ->order("a.info_date asc")->queryAll();
            if($infoRows){
                $this->detail=array();
                foreach ($infoRows as $infoRow){
                    $temp = array();
                    $temp["id"] = $infoRow["id"];
                    $temp["market_id"] = $infoRow["market_id"];
                    $temp["state_id"] = $infoRow["state_id"];
                    $temp["lcu"] = $infoRow["lcu"];
                    $temp["info_date"] = General::toDate($infoRow["info_date"]);
                    $temp["info_text"] = $infoRow["info_text"];
                    $temp['uflag'] = 'N';
                    $this->detail[$temp["id"]] = $temp;
                }
            }

            $userRows = Yii::app()->db->createCommand()->select("a.*")
                ->from("sal_market_user a")
                ->where("a.market_id={$index} and a.del_bool=0")
                ->order("a.id asc")->queryAll();
            if($userRows){
                $this->userDetail=array();
                foreach ($userRows as $userRow){
                    $temp = array();
                    $temp["id"] = $userRow["id"];
                    $temp["market_id"] = $userRow["market_id"];
                    $temp["user_name"] = $userRow["user_name"];
                    $temp["user_dept"] = $userRow["user_dept"];
                    $temp["user_phone"] = $userRow["user_phone"];
                    $temp["user_email"] = $userRow["user_email"];
                    $temp["user_wechat"] = $userRow["user_wechat"];
                    $temp["user_text"] = $userRow["user_text"];
                    $temp['uflag'] = 'N';
                    $this->userDetail[$temp["id"]] = $temp;
                }
            }
            return true;
		}else{
		    return false;
        }
	}

    public function setModelData($index){
        $city = Yii::app()->user->city();
        $whereSql = " and a.allot_city='{$city}'";
        $sql = "select a.* from sal_market a where a.id='{$index}'".$whereSql;
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        $arr = $this->getMyAttr();
        if ($row!==false) {
            foreach ($arr as $key => $type){
                switch ($type){
                    case 1://原值
                        $this->$key = $row[$key];
                        break;
                    case 2://日期
                        $this->$key = empty($row[$key])?null:General::toDate($row[$key]);
                        break;
                    case 3://数字
                        $this->$key = $row[$key]===null?null:floatval($row[$key]);
                        break;
                    default:
                }
            }
            return true;
        }else{
            return false;
        }
    }

    public function getStaticIndex(){
        return 2;
    }

    public function saveBackAll($data){
        foreach ($data["list_id"] as $row){
            $this->id = $row["id"];
            $this->saveBack();
        }

        $this->emailSales=false;//退回时不需要发送给销售
        $this->sendEmail($data["list_id"],'back');
    }

    public function saveBack(){
        $uid = Yii::app()->user->id;
        Yii::app()->db->createCommand()->update("sal_market", array(
            "luu"=>$uid,
            "status_type"=>2,
            "z_index"=>$this->getStaticIndex(),
            "back_note"=>$this->back_note,
        ), "id=:id", array(":id" => $this->id));

        $update_html=array();
        $update_html[]="<span><b>地区已退回</b></span>";
        $update_html[]="<span>退回原因：".$this->back_note."</span>";
        $update_html = implode("<br/>",$update_html);
        $backHisSQL = array(
            "market_id" => $this->id,
            "lcu" => $uid,
            "update_type" => 1,
            "update_html" => $update_html
        );
        Yii::app()->db->createCommand()->insert("sal_market_history", $backHisSQL);
    }

    public function validateAssign(){
        $bool = true;
        $assign_id = key_exists("assign_id", $_POST) ? $_POST["assign_id"] : "";
        $allot_employee = key_exists("allot_employee", $_POST) ? $_POST["allot_employee"] : "";

        if (empty($allot_employee) || !is_numeric($allot_employee)) {
            $this->addError("city_name", "请选择分配员工!");
            $bool = false;
        }
        $ids=array();
        $assign_list = explode(",",$assign_id);
        foreach ($assign_list as $id){
            $id = is_numeric($id)?$id:0;
            $this->setModelData($id);
            //未分配，或者被退回
            if(MarketFun::isAssignForArea($this)){
                $ids[]=$this->getAttributes();
            }
        }
        if (count($ids)<=0) {
            $this->addError("city_name", "请选择客户资料!");
            $bool = false;
        }
        if(count($ids)>=200){
            $this->addError("city_name", "选择的数量不能大于200!");
            $bool = false;
        }
        $update_arr = array(
            'list_id' => $ids,
            'allot_employee' => $allot_employee
        );
        return array("bool" => $bool, "data" => $update_arr);
    }

    public function validateBack(){
        $bool = true;
        $city = Yii::app()->user->city();
        $assign_id = key_exists("assign_id", $_POST) ? $_POST["assign_id"] : "";
        $typeNum = key_exists("type_num", $_POST) ? $_POST["type_num"] : "";
        $backNote = key_exists("back_note", $_POST) ? $_POST["back_note"] : "";
        if(empty($backNote)){
            $this->addError("reject_note", "退回原因不能为空");
            $bool = false;
        }

        $ids=array();
        $assign_list = explode(",",$assign_id);
        foreach ($assign_list as $id){
            $id = is_numeric($id)?$id:0;
            $this->setModelData($id);
            //未分配，或者被退回
            if(!empty($this->id)&&MarketFun::isAssignForArea($this)){
                $ids[]=$this->getAttributes();
            }
        }
        if (count($ids)<=0) {
            $this->addError("city_name", "请选择客户资料!");
            $bool = false;
        }
        if(count($ids)>=200){
            $this->addError("city_name", "选择的数量不能大于200!");
            $bool = false;
        }
        $this->back_note=$backNote;
        return array("bool"=>$bool,"typeNum"=>$typeNum,"data"=>array("list_id"=>$ids));
    }

    public function saveAssign($update_arr)
    {
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try {
            $this->updateAssign($connection,$update_arr);
            $transaction->commit();

            $this->emailTop=false;//分配时不需要发送给资料人
            $this->sendEmail($update_arr["list_id"],"assign");
        }catch(Exception $e) {
            $transaction->rollback();
            throw new CHttpException(404,$e->getMessage());
        }
    }

    protected function updateAssign(&$db,$update_arr){
        $uid = Yii::app()->user->id;
        $ids = $update_arr["list_id"];
        $update_html=array();
        $update_html[]="<span><b>已分配员工</b></span>";
        if(!empty($update_arr["allot_employee"])){
            $update_html[]="<span>员工：".MarketFun::getEmployeeNameForId($update_arr["allot_employee"])."</span>";
        }
        $update_html = implode("<br/>",$update_html);
        foreach ($ids as $row) {
            $id = $row["id"];
            //修改开始
            $updateSQL = array(
                "allot_date"=>date("Y-m-d"),
                "allot_type"=>3,
                "status_type"=>5,
                "z_index"=>$this->getStaticIndex(),
                "allot_employee"=>$update_arr["allot_employee"],
                "luu"=>$uid,
            );
            $db->createCommand()->update("sal_market", $updateSQL, "id=".$id);
            //修改完成

            //记录开始
            $addSQL = array(
                "allot_type" => 3,
                "market_id" => $id,
                "lcu" => $uid,
                "update_type" => 1,
                "update_json" => json_encode($update_arr),
                "update_html" => $update_html
            );
            $db->createCommand()->insert("sal_market_history", $addSQL);
            //记录完成
        }
    }

    public function systemBackForLongDate($endDate){
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_market")
            ->where("allot_date<'{$endDate}' and ((status_type=1 and allot_type=3)or(status_type=5 and allot_type=2))")
            ->queryAll();
        if($rows){
            $list=array();
            foreach ($rows as $row){
                $this->setModelDataForRow($row);
                $id = $row["id"];
                $list[]=$this->getAttributes();
                //修改开始
                $updateSQL = array(
                    "status_type"=>1,
                    "z_index"=>$this->getStaticIndex(),
                    "back_note"=>"系统自动退回(地区)：".$endDate,
                    "luu"=>"系统",
                );
                Yii::app()->db->createCommand()->update("sal_market", $updateSQL, "id=".$id);
                //修改完成

                //记录开始
                $addSQL = array(
                    "market_id" => $id,
                    "lcu" => "系统",
                    "update_type" => 1,
                    "update_html" => "<span><b>系统自动退回（地区）</b></span>"
                );
                Yii::app()->db->createCommand()->insert("sal_market_history", $addSQL);
                //记录完成
            }
            $this->emailSales=false;//不需要发送给销售
            $this->sendEmailForSystem($list,"systemArea");
        }
    }

	public function isOccupied(){
        $backNote = $this->back_note;
        if(empty($backNote)){
            $this->addError("back_note", "退回原因不能为空");
            return true;
        }
        $this->setModelData($this->id);
        if(empty($this->id)||!(MarketFun::isAssignForArea($this))){
            $this->addError("back_note", "数据异常，请刷新重试");
            return true;
        }
        $this->back_note = $backNote;
        return false;
    }

    public function isReadOnly(){
	    return $this->getScenario()=='view'||!(MarketFun::isAssignForArea($this));
    }
}
