<?php

class KAAreaForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $pro_name;
	public $city_code;
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
            'pro_name'=>Yii::t('ka','project name'),
            'city_code'=>Yii::t('ka','city code'),
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
            array('pro_name,id,city_code,z_index,z_display','safe'),
            array('pro_name,z_index,z_display','required'),
		);
	}

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_ka_area where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->pro_name = $row['pro_name'];
			$this->z_index = $row['z_index'];
			$this->city_code = $row['city_code'];
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
			throw new CHttpException(404,$e->getMessage());
		}
	}

	protected function save(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_ka_area where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_ka_area(
						pro_name, city_code, z_index, z_display, city, lcu) values (
						:pro_name, :code_city, :z_index, :z_display, :city, :lcu)";
				break;
			case 'edit':
				$sql = "update sal_ka_area set 
					pro_name = :pro_name, 
					city_code = :code_city,
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
		if (strpos($sql,':pro_name')!==false)
			$command->bindParam(':pro_name',$this->pro_name,PDO::PARAM_STR);
		if (strpos($sql,':code_city')!==false)
			$command->bindParam(':code_city',$this->city_code,PDO::PARAM_STR);
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
        if(is_numeric($index)){
            $sql = "select a.id from sal_ka_bot a where a.area_id=".$index." limit 1";
            $row = Yii::app()->db->createCommand($sql)->queryRow();
            $rtn = ($row !== false);
            return $rtn;
        }else{
            return true;
        }
	}

	public static function getCityListForId($id=""){
	    $list = array(""=>"");
        $rows = Yii::app()->db->createCommand()->select("pro_name,id")->from("sal_ka_area")
            ->where("(id>0 and z_display=1) or id=:id",array(":id"=>$id))
            ->order("z_index desc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = $row["pro_name"];
            }
        }
        return $list;
    }

	public static function getAreaNameForId($id){
        $row = Yii::app()->db->createCommand()->select("pro_name,id")->from("sal_ka_area")
            ->where("id=:id",array(":id"=>$id))->queryRow();
        if($row){
            return $row["pro_name"];
        }
        return $id;
    }
}
