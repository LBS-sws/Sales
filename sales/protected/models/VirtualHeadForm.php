<?php

class VirtualHeadForm extends VirtualForm
{
    public function validateID($attribute, $params){
        $index = empty($this->id)?0:$this->id;
        $sql = "select a.* from sal_contract_virtual a where a.id=".$index." ";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if($row){
            $this->id = $row["id"];
            $this->cont_id = $row["cont_id"];
            $this->clue_store_id = $row["clue_store_id"];
        }else{
            $this->addError($attribute, "信息异常".$index);
        }
    }

	protected function save(&$connection)
	{
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city;
	    switch ($this->getScenario()){
            case "new":
                break;
            case "edit":
                $connection->createCommand()->update("sal_clue_sre_soe",array(
                    "store_amt"=>$this->store_amt,
                    //"service_sum"=>$this->service_sum,
                    "service_fre"=>$this->service_fre,
                    "remark"=>$this->remark,
                    "detail_json"=>json_encode($this->service),
                    "luu"=>$uid,
                ),"id=:id",array(":id"=>$this->id));
                break;
            case "delete":
                $connection->createCommand()->delete("sal_clue_sre_soe","id=:id",array(":id"=>$this->id));

        }
		return true;
	}

	public function isOccupied($index) {
		$rtn = true;//默认不允许删除
		if($this->retrieveData($index)){
            $rtn = false;//允许删除
        }
		return $rtn;
	}

	public function isReadonly() {
		return $this->getScenario()=="view";
	}

	public function isReadonlyAndStatus() {
		return $this->getScenario()=="view"||!in_array($this->vir_status,array(0,9));
	}
}
