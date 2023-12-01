<?php

class MarketRejectForm extends MarketCompanyForm
{
    public function validateID($attribute, $params) {
        $this->addError($attribute, "无意向的资料无法修改，请退回至未分配！");
    }

    private function setMarketReady(){
        if(empty($this->ready_bool)){
            $uid = Yii::app()->user->id;
            Yii::app()->db->createCommand()->update("sal_market", array(
                "luu"=>$uid,
                "ready_bool"=>1,
            ), "id=:id and status_type=8", array(":id" => $this->id));

            $update_html=array();
            $update_html[]="<span><b>已读</b></span>";
            $update_html = implode("<br/>",$update_html);
            $backHisSQL = array(
                "market_id" => $this->id,
                "lcu" => $uid,
                "update_type" => 1,
                "update_html" => $update_html
            );
            Yii::app()->db->createCommand()->insert("sal_market_history", $backHisSQL);
        }
    }

	public function retrieveData($index){
        $index = is_numeric($index)?intval($index):0;
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = " and a.status_type=8";
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

            $this->ready_bool = $row["ready_bool"];
            $this->setMarketReady();//设置已读
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
        $whereSql = " and a.status_type=8";
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
        return 0;
    }

    public function saveBack(){
        $uid = Yii::app()->user->id;
        Yii::app()->db->createCommand()->update("sal_market", array(
            "luu"=>$uid,
            "status_type"=>0,
            "z_index"=>0,
        ), "id=:id", array(":id" => $this->id));

        $update_html=array();
        $update_html[]="<span><b>拒绝退回至未分配</b></span>";
        $update_html = implode("<br/>",$update_html);
        $backHisSQL = array(
            "market_id" => $this->id,
            "lcu" => $uid,
            "update_type" => 1,
            "update_html" => $update_html
        );
        Yii::app()->db->createCommand()->insert("sal_market_history", $backHisSQL);
    }

	public function isOccupied(){
        $this->setModelData($this->id);
        if(empty($this->id)||$this->status_type!=8){
            $this->addError("back_note", "数据异常，请刷新重试");
            return true;
        }
	    return false;
    }

    public function validateReadyAll(){
        $bool = true;
        $assign_id = key_exists("assign_id", $_POST) ? $_POST["assign_id"] : "";

        $ids=array();
        $assign_list = explode(",",$assign_id);
        foreach ($assign_list as $id){
            $id = is_numeric($id)?$id:0;
            $this->setModelData($id);
            //未分配，或者被退回
            if(!empty($this->id)&&$this->status_type==8){
                $ids[]=$id;
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
            'list_id' => $ids
        );
        return array("bool" => $bool, "data" => $update_arr);
    }

    public function saveReadyAll($update_arr)
    {
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try {
            $this->updateReadyAll($connection,$update_arr);
            $transaction->commit();
        }catch(Exception $e) {
            $transaction->rollback();
            throw new CHttpException(404,$e->getMessage());
        }
    }

    protected function updateReadyAll(&$db,$update_arr){
        foreach ($update_arr["list_id"] as $id) {
            $this->id = $id;
            $this->setMarketReady();
        }
    }

    public function isReadOnly(){
	    return true;
    }
}
