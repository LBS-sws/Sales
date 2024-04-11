<?php

class KADupForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $dup_name;
	public $dup_value;
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
            'dup_name'=>Yii::t('ka','dup name'),
            'dup_value'=>Yii::t('ka','dup value'),
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
            array('dup_name,id,dup_value,z_index,z_display','safe'),
            array('dup_name,z_index,z_display','required'),
		);
	}

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_ka_dup where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->dup_name = $row['dup_name'];
            $this->dup_value = $row['dup_value'];
			$this->z_index = $row['z_index'];
			$this->z_display = $row['z_display'];
		}
		return true;
	}

	public static function getSearchNameForName($name){
        if(!empty($name)){
            $sql = "select dup_name,dup_value from sal_ka_dup where z_display=1 ORDER BY z_index DESC";
            $rows = Yii::app()->db->createCommand($sql)->queryAll();
            $search=array();
            $replace=array();
            if($rows){
                foreach ($rows as $row){
                    $search[]=$row["dup_name"];
                    $replace[]=empty($row["dup_value"])?"":$row["dup_value"];
                }
            }
            return str_replace($search,$replace,$name);
        }else{
            return $name;
        }
    }

    //重置旧版所有的KA、RA、Ca项目查询名称
	public static function resetAllBotForDup($dupOld,$dupNow){
        $dupOld = str_replace("'","\'",$dupOld);
        $dupNow = str_replace("'","\'",$dupNow);
        $sql = "select dup_name,dup_value from sal_ka_dup where z_display=1 ORDER BY z_index DESC";
        $dupRows = Yii::app()->db->createCommand($sql)->queryAll();
        $search = array();
        $replace = array();
        if ($dupRows) {
            foreach ($dupRows as $dupRow) {
                $search[] = $dupRow["dup_name"];
                $replace[] = empty($dupRow["dup_value"]) ? "" : $dupRow["dup_value"];
            }
        }
        $typeList = array("sal_ca_bot","sal_ra_bot","sal_ka_bot");
        foreach ($typeList as $table){
            $sql = "select id,customer_name from {$table} where customer_name LIKE '%{$dupNow}%'";
            if(!empty($dupOld)){
                $sql.=" or customer_name LIKE '%{$dupOld}%'";
            }
            $rows = Yii::app()->db->createCommand($sql)->queryAll();
            if($rows){
                foreach ($rows as $row){
                    $searchName = str_replace($search,$replace,$row["customer_name"]);
                    Yii::app()->db->createCommand()->update($table,array("search_name"=>$searchName),"id=".$row["id"]);
                }
            }
        }
    }

    //重置旧版所有的KA、RA、Ca项目查询名称
	public static function resetAllBotForSearchName(){
        $sql = "select dup_name,dup_value from sal_ka_dup where z_display=1 ORDER BY z_index DESC";
        $dupRows = Yii::app()->db->createCommand($sql)->queryAll();
        $search=array();
        $replace=array();
        if($dupRows){
            foreach ($dupRows as $dupRow){
                $search[]=$dupRow["dup_name"];
                $replace[]=empty($dupRow["dup_value"])?"":$dupRow["dup_value"];
            }
        }
	    $typeList = array("sal_ca_bot","sal_ra_bot","sal_ka_bot");
	    foreach ($typeList as $table){
            $sql = "select id,customer_name from {$table} where search_name is NULL";
            $rows = Yii::app()->db->createCommand($sql)->queryAll();
            if($rows){
                foreach ($rows as $row){
                    $searchName = str_replace($search,$replace,$row["customer_name"]);
                    Yii::app()->db->createCommand()->update($table,array("search_name"=>$searchName),"id=".$row["id"]);
                }
            }
        }
    }
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
            $oldRow = Yii::app()->db->createCommand()->select("*")->from("sal_ka_dup")
                ->where("id=:id",array(":id"=>$this->id))->queryRow();
			$this->save($connection);
			$transaction->commit();
            $this->resetDupName($oldRow);
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,$e->getMessage());
		}
	}

	protected function resetDupName($oldRow){
	    $dupNameOld = "";
	    $dupValueOld = "";
	    $dupNameNow = $this->dup_name;
	    $dupValueNow = $this->dup_value;
        if($oldRow){
            $dupNameOld = $oldRow["dup_name"];
            $dupValueOld = empty($oldRow["dup_value"])?"":$oldRow["dup_value"];
        }
        if($this->getScenario()=="delete"){
            $dupNameNow=$dupNameOld;
            $dupNameOld="";
        }
        if($dupNameOld!=$dupNameNow||$dupValueOld!=$dupValueNow){
            self::resetAllBotForDup($dupNameOld,$dupNameNow);
        }
    }

	protected function save(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "update sal_ka_dup set 
					z_display = 0,
					luu = :luu
					where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_ka_dup(
						dup_name, dup_value, z_index, city, lcu) values (
						:dup_name, :dup_value, :z_index, :city, :lcu)";
				break;
			case 'edit':
				$sql = "update sal_ka_dup set 
					dup_name = :dup_name, 
					dup_value = :dup_value,
					z_index = :z_index,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':dup_name')!==false)
			$command->bindParam(':dup_name',$this->dup_name,PDO::PARAM_STR);
		if (strpos($sql,':dup_value')!==false)
			$command->bindParam(':dup_value',$this->dup_value,PDO::PARAM_STR);
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
        return false;
	}
}
