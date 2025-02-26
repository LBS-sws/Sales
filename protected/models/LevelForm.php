<?php

class LevelForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $level;
	public $new_level;
    public $start_fraction;
    public $end_fraction;
    public $new_fraction;
    public $reward;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'level'=>Yii::t('code','Level'),
            'new_level'=>Yii::t('code','New Level'),
            'start_fraction'=>Yii::t('code','Start Fraction'),
            'end_fraction'=>Yii::t('code','End Fraction'),
            'new_fraction'=>Yii::t('code','New Fraction'),
            'reward'=>Yii::t('code','Reward'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('level,new_level,start_fraction,end_fraction,new_fraction,reward','required'),
			array('id','safe'), 
		);
	}

	public function retrieveData($index)
	{
		$sql = "select * from sal_level where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
            $this->level = $row['level'];
            $this->new_level = $row['new_level'];
            $this->start_fraction = $row['start_fraction'];
            $this->end_fraction = $row['end_fraction'];
            $this->new_fraction = $row['new_fraction'];
            $this->reward = $row['reward'];
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
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	protected function save(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_level where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_level(
						level, new_level, start_fraction, end_fraction,new_fraction,reward) values (
						:level, :new_level, :start_fraction, :end_fraction, :new_fraction, :reward)";
				break;
			case 'edit':
				$sql = "update sal_level set 
					level = :level, 
					new_level = :new_level,
					start_fraction = :start_fraction,
					end_fraction = :end_fraction, 
					new_fraction = :new_fraction,
					reward = :reward
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':level')!==false)
			$command->bindParam(':level',$this->level,PDO::PARAM_STR);
		if (strpos($sql,':new_level')!==false)
			$command->bindParam(':new_level',$this->new_level,PDO::PARAM_STR);
		if (strpos($sql,':start_fraction')!==false)
			$command->bindParam(':start_fraction',$this->start_fraction,PDO::PARAM_INT);
		if (strpos($sql,':end_fraction')!==false)
			$command->bindParam(':end_fraction',$this->end_fraction,PDO::PARAM_INT);
        if (strpos($sql,':new_fraction')!==false)
            $command->bindParam(':new_fraction',$this->new_fraction,PDO::PARAM_INT);
        if (strpos($sql,':reward')!==false)
            $command->bindParam(':reward',$this->reward,PDO::PARAM_INT);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}

//	public function getCityList() {
//		$suffix = Yii::app()->params['envSuffix'];
//		$sql = "select code, name from security$suffix.sec_city order by name";
//		$rows = Yii::app()->db->createCommand($sql)->queryAll();
//		$rtn = array();
//		foreach ($rows as $row) {
//			$rtn[$row['code']] = $row['name'];
//		}
//		return $rtn;
//	}
	
//	public function isOccupied($index) {
//		$rtn = false;
//		$sql = "select a.id from sal_visit a where a.district=".$index." limit 1";
//		$row = Yii::app()->db->createCommand($sql)->queryRow();
//		$rtn = ($row !== false);
//		return $rtn;
//	}
}
