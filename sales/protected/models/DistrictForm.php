<?php

class DistrictForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $name;
	public $city;
	public $nal_tree_names;
	public $nal_id;
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
			'name'=>Yii::t('code','Description'),
			'city'=>Yii::t('sales','City'),
            'nal_id'=>"行政区域",
            'z_index'=>Yii::t('customer','z_index'),
            'display'=>Yii::t('customer','display'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,name,nal_id,nal_tree_names,city,z_index,display','safe'),
            array('name,city','required'),
            array('z_index,display','numerical','allowEmpty'=>false,'integerOnly'=>true),
		);
	}

	public function retrieveData($index)
	{
		$sql = "select * from sal_cust_district where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->name = $row['name'];
			$this->nal_id = $row['nal_id'];
			$this->nal_tree_names = $row['nal_tree_names'];
			$this->city = $row['city'];
            $this->display = $row['display'];
            $this->z_index = $row['z_index'];
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
				$sql = "delete from sal_cust_district where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_cust_district(
						name, city, nal_id, nal_tree_names, display, z_index, lcu, luu) values (
						:name, :city, :nal_id, :nal_tree_names, :display, :z_index, :lcu, :luu)";
				break;
			case 'edit':
				$sql = "update sal_cust_district set 
					name = :name, 
					city = :city,
					nal_id = :nal_id,
					nal_tree_names = :nal_tree_names,
					display = :display,
					z_index = :z_index,
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
		if (strpos($sql,':nal_tree_names')!==false)
			$command->bindParam(':nal_tree_names',$this->nal_tree_names,PDO::PARAM_STR);
        if (strpos($sql,':z_index')!==false)
            $command->bindParam(':z_index',$this->z_index,PDO::PARAM_INT);
        if (strpos($sql,':nal_id')!==false){
            $nal_id=empty($this->nal_id)?null:$this->nal_id;
            $command->bindParam(':nal_id',$nal_id,PDO::PARAM_INT);
        }
        if (strpos($sql,':display')!==false)
            $command->bindParam(':display',$this->display,PDO::PARAM_INT);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$this->city,PDO::PARAM_STR);
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
		$rtn = array();
		foreach ($rows as $row) {
			$rtn[$row['code']] = $row['name'];
		}
		return $rtn;
	}
	
	public function isOccupied($index) {
		$rtn = false;
		$sql = "select a.id from sal_visit a where a.district=".$index." limit 1";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		$rtn = ($row !== false);
		return $rtn;
	}

	public function resetNal(){
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select a.id,a.name,b.name as city_name from sal_cust_district a
          LEFT JOIN security$suffix.sec_city b ON a.city=b.code
          where nal_id is null ";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if($rows){
            foreach ($rows as $row){
                $areaRow = Yii::app()->db->createCommand()->select("id,parent_ids,tree_names,area_name")->from("sal_national_area")
                    ->where("tree_names like '%{$row['city_name']}%' and tree_names like '%{$row['name']}%' and status=1 and type=3")
                    ->order("listsort asc,id asc")->queryRow();
                if($areaRow){
                    Yii::app()->db->createCommand()->update("sal_cust_district",array(
                        "nal_id"=>$areaRow["id"],
                        "nal_tree_names"=>$areaRow["tree_names"],
                    ),"id=".$row["id"]);
                }
            }
        }
    }
}
