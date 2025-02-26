<?php

class MarketStateForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $state_name;
	public $state_type;
	public $state_day=0;
	public $z_index=0;
	public $z_display=1;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'state_name'=>Yii::t('market','project name'),
            'state_type'=>Yii::t('market','state type'),
            'state_day'=>Yii::t('market','state day'),
            'z_index'=>Yii::t('market','z index'),
            'z_display'=>Yii::t('market','z display'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('state_name,state_type,state_day,id,z_index,z_display','safe'),
            array('state_name,z_index,z_display','required'),
            array('state_type','validateType'),
		);
	}

    public function validateType($attribute, $params) {
	    if($this->state_type!=1){
	        $this->state_day = 0;
        }
    }

    public static function getMarketStateList($id=''){
        $arr = array();
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("sal_market_state")
            ->where("z_display=1 or id='{$id}'")
            ->order("z_index desc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $stateName = $row["state_name"];
                if($row["state_type"]==1&&!empty($row["state_day"])){
                    $stateName.= " (".$row["state_day"].Yii::t("market"," day").")";
                }
                $arr[$row["id"]] = $stateName;
            }
        }
        return $arr;
    }

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_market_state where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->state_name = $row['state_name'];
			$this->state_type = $row['state_type'];
			$this->state_day = $row['state_day'];
			$this->z_index = $row['z_index'];
			$this->z_display = $row['z_display'];
		}
		return true;
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->save($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function save(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_market_state where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_market_state(
						state_name,state_type,state_day, z_index, z_display, city, lcu) values (
						:state_name,:state_type,:state_day, :z_index, :z_display, :city, :lcu)";
				break;
			case 'edit':
				$sql = "update sal_market_state set 
					state_name = :state_name, 
					state_type = :state_type, 
					state_day = :state_day, 
					z_index = :z_index,
					z_display = :z_display,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':state_name')!==false)
			$command->bindParam(':state_name',$this->state_name,PDO::PARAM_STR);
		if (strpos($sql,':state_type')!==false)
			$command->bindParam(':state_type',$this->state_type,PDO::PARAM_INT);
		if (strpos($sql,':state_day')!==false)
			$command->bindParam(':state_day',$this->state_day,PDO::PARAM_INT);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$city,PDO::PARAM_STR);
		if (strpos($sql,':z_index')!==false)
			$command->bindParam(':z_index',$this->z_index,PDO::PARAM_INT);
		if (strpos($sql,':z_display')!==false)
			$command->bindParam(':z_display',$this->z_display,PDO::PARAM_INT);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}

	public function isOccupied($index) {
		$sql = "select a.id from sal_market_info a where a.state_id=".$index." limit 1";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		$rtn = ($row !== false);
		return $rtn;
	}
}
