<?php

class ContProSSEForm extends ContSSEForm
{
    protected $table_name = "sal_contpro_sse";
	public function retrieveData($index){
		$sql = "select a.* from sal_contpro_sse a where a.id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
            $this->clue_id = $row['clue_id'];
            $this->group_id = $row['group_id'];
            $this->clue_service_id = $row['clue_service_id'];
            $this->clue_store_id = $row['clue_store_id'];
            $this->create_staff = $row['create_staff'];
            $this->store_amt = $row['store_amt'];
            $this->service_sum = $row['service_sum'];
            $this->remark = $row['remark'];
            $this->update_bool = $row['update_bool'];
            $this->busine_id = empty($row['busine_id'])?array():explode(",",$row['busine_id']);
            $this->busine_id_text = $row['busine_id_text'];
            $this->detail_json = empty($row['detail_json'])?array():json_decode($row['detail_json'],true);
            $this->service = $this->detail_json;

            return true;
		}else{
		    return false;
        }
	}

	public function isReadonly() {
		return $this->getScenario()=='view';
	}
}
