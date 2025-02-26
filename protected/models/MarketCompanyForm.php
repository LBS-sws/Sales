<?php

class MarketCompanyForm extends MarketForm
{
    protected $emailTop=false;//管理员邮件 false：关闭
    protected $emailCity=true;//城市邮件 false：关闭
    protected $emailSales=true;//销售邮件 false：关闭

    public function validateID($attribute, $params) {
        if ($this->getScenario()!='new'){
            $index = is_numeric($this->id)?$this->id:0;
            $sql = "select a.status_type from sal_market a where a.id='{$index}'";
            $row = Yii::app()->db->createCommand($sql)->queryRow();
            if($row){
                $this->status_type = $row["status_type"];
                if(!in_array($this->status_type,array(0,1,2))){
                    $detail = $this->detail;
                    $this->retrieveData($index);
                    if(empty($this->id)){
                        $this->addError($attribute, "数据异常，请刷新重试");
                    }
                    $this->detail = $detail;
                }
            }else{
                $this->addError($attribute, "数据不存在，请刷新重试");
            }
        }
    }

	public function retrieveData($index){
        $suffix = Yii::app()->params['envSuffix'];
        $index = is_numeric($index)?intval($index):0;
        $whereSql = " ";
        //$whereSql = " and a.status_type not in (8,10)";
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
        $sql = "select a.* from sal_market a where a.id='{$index}'";
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
        return 0;
    }

    public function validateAssign(){
        $bool = true;
        $assign_id = key_exists("assign_id", $_POST) ? $_POST["assign_id"] : "";
        $allot_type = key_exists("allot_type", $_POST) ? $_POST["allot_type"] : "";
        $allot_city = key_exists("allot_city", $_POST) ? $_POST["allot_city"] : "";
        $allot_employee = key_exists("allot_employee", $_POST) ? $_POST["allot_employee"] : "";

        if (empty($allot_type) || !is_numeric($allot_type)) {
            $this->addError("city_name", "请选择分配类型!");
            $bool = false;
        }
        switch ($allot_type) {
            case 1://ka销售
                $allot_city = null;
                if (empty($allot_employee) || !is_numeric($allot_employee)) {
                    $this->addError("city_name", "请选择KA销售!");
                    $bool = false;
                }
                break;
            case 2://地区
                $allot_employee = null;
                if (empty($allot_city)) {
                    $this->addError("city_name", "请选择分配地区!");
                    $bool = false;
                }
                break;
            default:
                $this->addError("city_name", "数据异常!");
                $bool = false;
        }
        $ids=array();
        $assign_list = explode(",",$assign_id);
        foreach ($assign_list as $id){
            $id = is_numeric($id)?$id:0;
            $this->setModelData($id);
            //未分配，或者被退回
            if(!empty($this->id)&&MarketFun::isAssign($this)){
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
            'allot_type' => $allot_type,
            'allot_city' => $allot_city,
            'allot_employee' => $allot_employee
        );
        return array("bool" => $bool, "data" => $update_arr);
    }

    public function saveAssign($update_arr)
    {
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try {
            $this->updateAssign($connection,$update_arr);
            $transaction->commit();

            $this->sendEmail($update_arr["list_id"],'assign');
        }catch(Exception $e) {
            $transaction->rollback();
            throw new CHttpException(404,$e->getMessage());
        }
    }

    protected function updateAssign(&$db,$update_arr){
        $uid = Yii::app()->user->id;
        $ids = $update_arr["list_id"];
        $update_html=array();
        $update_html[]="<span><b>已分配</b></span>";
        if(!empty($update_arr["allot_type"])){
            $update_html[]="<span>分配类型：".MarketFun::getAllowNameToType($update_arr["allot_type"])."</span>";
        }
        if(!empty($update_arr["allot_city"])){
            $update_html[]="<span>分配城市：".General::getCityName($update_arr["allot_city"])."</span>";
        }
        if(!empty($update_arr["allot_employee"])){
            $update_html[]="<span>销售：".MarketFun::getEmployeeNameForId($update_arr["allot_employee"])."</span>";
        }
        $update_html = implode("<br/>",$update_html);
        foreach ($ids as $row) {
            $id = $row["id"];
            //修改开始
            $updateSQL = array(
                "allot_date"=>date("Y-m-d"),
                "status_type"=>5,
                "z_index"=>$this->getStaticIndex(),
                "allot_type"=>$update_arr["allot_type"],
                "allot_city"=>$update_arr["allot_city"],
                "allot_ka"=>$update_arr["allot_employee"],
                "allot_employee"=>$update_arr["allot_employee"],
                "luu"=>$uid,
            );
            $db->createCommand()->update("sal_market", $updateSQL, "id=".$id);
            //修改完成

            //记录开始
            $addSQL = array(
                "market_id" => $id,
                "allot_type"=>$update_arr["allot_type"],
                "lcu" => $uid,
                "update_type" => 1,
                "update_json" => json_encode($update_arr),
                "update_html" => $update_html
            );
            $db->createCommand()->insert("sal_market_history", $addSQL);
            //记录完成
        }
    }

	public function isOccupied(){
        $this->setModelData($this->id);
        if(in_array($this->status_type,array(0,1,2))){ //草稿、系统退回、手动退回
            $row = Yii::app()->db->createCommand()->select("a.id")
                ->from("sal_market_info a")
                ->leftJoin("sal_market b","a.market_id=b.id")
                ->where("market_id=:market_id and a.lcu!=b.lcu",array(":market_id"=>$this->id))->queryRow();
            if($row){
                return true;//不允许删除
            }
            return false;
        }else{
            return true;//不允许删除
        }
    }

    public function isReadOnly(){
	    return $this->getScenario()=='view'||!MarketFun::isAssign($this);
    }
}
