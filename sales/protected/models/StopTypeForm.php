<?php

class StopTypeForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $type_name;
	public $again_type=0;//再次跟進
	public $again_day;//再次跟進間隔
	public $z_index=0;
	public $display=1;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'type_name'=>Yii::t('customer','Stop Type Name'),
            'z_index'=>Yii::t('customer','z_index'),
            'again_type'=>Yii::t('customer','again type'),
            'again_day'=>Yii::t('customer','again day'),
            'display'=>Yii::t('customer','display'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,type_name,z_index,display,again_type,again_day','safe'),
			array('type_name','required'),
            array('z_index,display','numerical','allowEmpty'=>false,'integerOnly'=>true),
            array('id','validateID','on'=>array("delete")),
            array('again_type','validateType'),
		);
	}

    public function validateType($attribute, $params) {
        if(empty($this->again_type)){
            $this->again_type = 0;
            $this->again_day = 0;//解決數據庫不能保存null的問題，默認為0
        }else{
            $this->again_type = 1;
            if(empty($this->again_day)||!is_numeric($this->again_day)){
                $this->addError($attribute, Yii::t('customer','again day')." 不能为空");
            }
        }
    }

    public function validateID($attribute, $params) {
        $id = $this->$attribute;
        $row = Yii::app()->db->createCommand()->select("id")->from("sal_stop_back")
            ->where("back_type=:id",array(":id"=>$id))->queryRow();
        if($row){
            $this->addError($attribute, "这条记录已被使用无法删除");
            return false;
        }
    }

	public function retrieveData($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select * from sal_stop_type where id='".$index."'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->type_name = $row['type_name'];
			$this->display = $row['display'];
			$this->again_type = $row['again_type'];
			$this->again_day = $row['again_day'];
			$this->z_index = $row['z_index'];
            return true;
		}else{
		    return false;
        }
	}

    public static function getStopTypeList($id=0){
        $id = empty($id)?0:$id;
        $list = array(""=>"");
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_stop_type")
            ->where("display=1 or id=:id",array(":id"=>$id))->order("z_index desc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $day = empty($row["again_type"])?"":" - {$row["again_day"]}天";
                $list[$row["id"]] = $row["type_name"].$day;
            }
        }
        return $list;
    }
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveDataForSql($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function saveDataForSql(&$connection)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_stop_type where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_stop_type(
						type_name, z_index, again_day, again_type, display, city, lcu, lcd) values (
						:type_name, :z_index, :again_day, :again_type, :display, :city, :lcu, :lcd)";
				break;
			case 'edit':
				$sql = "update sal_stop_type set 
					type_name = :type_name, 
					again_day = :again_day,
					again_type = :again_type,
					z_index = :z_index,
					display = :display,
					city = :city,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
        //var_dump($this->type_name);die();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':z_index')!==false)
			$command->bindParam(':z_index',$this->z_index,PDO::PARAM_INT);
		if (strpos($sql,':display')!==false)
			$command->bindParam(':display',$this->display,PDO::PARAM_INT);
		if (strpos($sql,':again_day')!==false)
			$command->bindParam(':again_day',$this->again_day,PDO::PARAM_INT);
		if (strpos($sql,':again_type')!==false)
			$command->bindParam(':again_type',$this->again_type,PDO::PARAM_INT);
		if (strpos($sql,':type_name')!==false)
			$command->bindParam(':type_name',$this->type_name,PDO::PARAM_STR);

		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$city,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcd')!==false){
            $date = date("Y-m-d H:i:s");
            $command->bindParam(':lcd',$date,PDO::PARAM_STR);
        }
		$command->execute();

        if ($this->scenario=='new')
            $this->id = Yii::app()->db->getLastInsertID();

		return true;
	}
}