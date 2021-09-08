<?php

class AttrTypeForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $name;
	public $rpt_type;
	public $display_num=1;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'name'=>Yii::t('code','Description'),
            'display_num'=>Yii::t('sales','display'),
            'rpt_type'=>Yii::t('sales','type for'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,rpt_type,display_num','safe'),
			array('name,rpt_type','required'),
		);
	}

	public static function getRPTTypeList($id=0,$bool=false)
	{
	    $arr = array(
	        "H"=>Yii::t('sales','蔚诺空气业务'),
	        "G"=>Yii::t('sales','一次性售卖')
        );
	    if($bool){
	        if(key_exists($id,$arr)){
	            return $arr[$id];
            }else{
	            return $id;
            }
        }
		return $arr;
	}

	public static function getDisplayList($id=0,$bool=false)
	{
	    $arr = array(
	        0=>Yii::t('sales','none'),
	        1=>Yii::t('sales','show')
        );
	    if($bool){
	        if(key_exists($id,$arr)){
	            return $arr[$id];
            }else{
	            return $id;
            }
        }
		return $arr;
	}

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_attr_type where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->name = $row['name'];
			$this->rpt_type = $row['rpt_type'];
			$this->display_num = $row['display_num'];
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
				$sql = "delete from sal_attr_type where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_attr_type(
						name, rpt_type, display_num, lcu, luu) values (
						:name, :rpt_type, :display_num, :lcu, :luu)";
				break;
			case 'edit':
				$sql = "update sal_attr_type set 
					name = :name, 
					rpt_type = :rpt_type,
					display_num = :display_num,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':display_num')!==false)
			$command->bindParam(':display_num',$this->display_num,PDO::PARAM_INT);
		if (strpos($sql,':name')!==false)
			$command->bindParam(':name',$this->name,PDO::PARAM_STR);
		if (strpos($sql,':rpt_type')!==false)
			$command->bindParam(':rpt_type',$this->rpt_type,PDO::PARAM_STR);
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
		$rtn = false;
		$sql = "select a.id from sal_visit_info a where a.field_id in ('svc_H1','svc_G1') and a.field_value=".$index." limit 1";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		$rtn = ($row !== false);
		return $rtn;
	}
}
