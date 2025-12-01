<?php

class CusttypeForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $name;
	public $rpt_type;
	public $type_group;
	public $city;
	public $rpt_u;
	public $z_display=1;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'name'=>Yii::t('code','Description'),
			'rpt_type'=>Yii::t('code','Report Category'),
			'city'=>Yii::t('sales','City'),
			'type_group'=>Yii::t('code','Type'),
            'rpt_u'=>"派单系统对应id",
            'z_display'=>"是否显示",
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('name,city,type_group','required'),
			array('id,rpt_u,z_display,rpt_type','safe'),
		);
	}

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_cust_type where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->name = $row['name'];
			$this->rpt_type = $row['rpt_type'];
			$this->city = $row['city'];
			$this->type_group = $row['type_group'];
			$this->rpt_u = $row['rpt_u'];
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
				$sql = "delete from sal_cust_type where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_cust_type(
						name, rpt_type, rpt_u, z_display, type_group, city, lcu, luu) values (
						:name, :rpt_type, :rpt_u, :z_display, :type_group, :city, :lcu, :luu)";
				break;
			case 'edit':
				$sql = "update sal_cust_type set 
					name = :name, 
					rpt_type = :rpt_type,
					type_group = :type_group,
					rpt_u = :rpt_u,
					z_display = :z_display,
					city = :city,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':name')!==false)
			$command->bindParam(':name',$this->name,PDO::PARAM_STR);
		if (strpos($sql,':type_group')!==false)
			$command->bindParam(':type_group',$this->type_group,PDO::PARAM_INT);
		if (strpos($sql,':rpt_type')!==false)
			$command->bindParam(':rpt_type',$this->rpt_type,PDO::PARAM_STR);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$this->city,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':rpt_u')!==false){
            $rpt_u = empty($this->rpt_u)?null:$this->rpt_u;
            $command->bindParam(':rpt_u',$rpt_u,PDO::PARAM_INT);
        }
		if (strpos($sql,':z_display')!==false){
            $z_display = empty($this->z_display)?0:$this->z_display;
            $command->bindParam(':z_display',$z_display,PDO::PARAM_INT);
        }
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}

	public function getCityList() {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select code, name from security$suffix.sec_city order by name";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		$rtn = array('99999'=>Yii::t('sales','All'));
		foreach ($rows as $row) {
			$rtn[$row['code']] = $row['name'];
		}
		return $rtn;
	}

	public function isOccupied($index) {
		$rtn = false;
		$sql = "select a.id from sal_visit a where a.cust_type=".$index." limit 1";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		$rtn = ($row !== false);
		return $rtn;
	}
}
