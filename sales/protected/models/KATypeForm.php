<?php

class KATypeForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $ka_name;
	public $ka_type=1;
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
            'ka_type'=>Yii::t('ka','type'),
            'ka_name'=>Yii::t('ka','reason'),
            'z_index'=>Yii::t('ka','z index'),
            'z_display'=>Yii::t('ka','z display'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('ka_name,ka_type,id,z_index,z_display','safe'),
            array('ka_name,ka_type,z_index,z_display','required'),
		);
	}

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_ka_type where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->ka_type = $row['ka_type'];
			$this->ka_name = $row['ka_name'];
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
				$sql = "delete from sal_ka_type where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_ka_type(
						ka_name, ka_type, z_index, z_display, lcu) values (
						:ka_name, :ka_type, :z_index, :z_display, :lcu)";
				break;
			case 'edit':
				$sql = "update sal_ka_type set 
					ka_name = :ka_name, 
					ka_type = :ka_type,
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
		if (strpos($sql,':ka_type')!==false)
			$command->bindParam(':ka_type',$this->ka_type,PDO::PARAM_INT);
		if (strpos($sql,':ka_name')!==false)
			$command->bindParam(':ka_name',$this->ka_name,PDO::PARAM_STR);
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
		$sql = "select a.id from sal_ka_service a where a.reject_id=".$index." limit 1";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		$rtn = ($row !== false);
		return $rtn;
	}

	public static function getTypeList($type="",$bool=false){
	    $list = array(
	        1=>Yii::t("ka","stop"),//暫停
	        2=>Yii::t("ka","reject"),//拒絕
        );
	    if($bool){
	        if(key_exists($type,$list)){
	            return $list[$type];
            }else{
	            return $type;
            }
        }else{
	        return $list;
        }
    }
}
