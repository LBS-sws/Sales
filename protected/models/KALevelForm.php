<?php

class KALevelForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $pro_name;
	public $ka_type;
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
            'class_name'=>Yii::t('ka','class type name'),
            'pro_name'=>Yii::t('ka','project name'),
            'ka_type'=>Yii::t('ka','KA Type'),
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
            array('pro_name,ka_type,id,z_index,z_display','safe'),
            array('pro_name,ka_type,z_index,z_display','required'),
		);
	}

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_ka_level where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->pro_name = $row['pro_name'];
			$this->ka_type = $row['ka_type'];
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
				$sql = "delete from sal_ka_level where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_ka_level(
						pro_name, ka_type, z_index, z_display, city, lcu) values (
						:pro_name, :ka_type, :z_index, :z_display, :city, :lcu)";
				break;
			case 'edit':
				$sql = "update sal_ka_level set 
					pro_name = :pro_name, 
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
		if (strpos($sql,':pro_name')!==false)
			$command->bindParam(':pro_name',$this->pro_name,PDO::PARAM_STR);
		if (strpos($sql,':ka_type')!==false)
			$command->bindParam(':ka_type',$this->ka_type,PDO::PARAM_STR);
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
            $sql = "select a.id from sal_ka_bot a where a.level_id=".$index." limit 1";
            $row = Yii::app()->db->createCommand($sql)->queryRow();
            $rtn = ($row !== false);
            return $rtn;
        }else{
            return true;
        }
	}

    public static function getLevelListForId($id="",$type="NKA"){
        $list = array(""=>"");
        $rows = Yii::app()->db->createCommand()->select("pro_name,id")->from("sal_ka_level")
            ->where("(id>0 and z_display=1 and ka_type=:type) or id=:id",
                array(":id"=>$id,":type"=>$type)
            )->order("z_index desc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = $row["pro_name"];
            }
        }
        return $list;
    }

    public static function getLevelTypeListForType($type="",$bool=false){
        $list = array("NKA"=>"KA","CKA"=>"地方业务");
        if($bool){
            if(key_exists($type,$list)){
                return $list[$type];
            }else{
                return $type;
            }
        }
        return $list;
    }

    public static function getLevelNameForId($id){
        $row = Yii::app()->db->createCommand()->select("pro_name,id")->from("sal_ka_level")
            ->where("id=:id",array(":id"=>$id))->queryRow();
        if($row){
            return $row["pro_name"];
        }
        return $id;
    }
}
