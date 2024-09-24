<?php

class KAIndicatorForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $employee_id;
	public $effect_date;
	public $indicator_money;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
        return array(
            'employee_id'=>Yii::t('ka','employee name'),
            'effect_date'=>Yii::t('ka','effect date'),
            'indicator_money'=>Yii::t('ka','indicator money'),
        );
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('employee_id,id,effect_date,indicator_money','safe'),
            array('employee_id,effect_date,indicator_money','required'),
		);
	}

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_ka_idx where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->employee_id = $row['employee_id'];
			$this->effect_date = General::toDate($row['effect_date']);
			$this->indicator_money = floatval($row['indicator_money']);
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
				$sql = "delete from sal_ka_idx where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_ka_idx(
						employee_id, effect_date, indicator_money, lcu) values (
						:employee_id, :effect_date, :indicator_money, :lcu)";
				break;
			case 'edit':
				$sql = "update sal_ka_idx set 
					employee_id = :employee_id, 
					effect_date = :effect_date,
					indicator_money = :indicator_money,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':employee_id')!==false)
			$command->bindParam(':employee_id',$this->employee_id,PDO::PARAM_STR);
		if (strpos($sql,':effect_date')!==false)
			$command->bindParam(':effect_date',$this->effect_date,PDO::PARAM_INT);
		if (strpos($sql,':indicator_money')!==false)
			$command->bindParam(':indicator_money',$this->indicator_money,PDO::PARAM_INT);
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
		$sql = "select a.id from sal_ka_idx a where a.id=".$index." limit 1";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		$rtn = ($row === false);
		return $rtn;
	}
}
