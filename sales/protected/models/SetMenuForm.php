<?php

class SetMenuForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $set_id;
	public $set_name;
	public $set_type;
	public $u_code;
	public $mh_code;
	public $z_display=1;
	public $z_index=1;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
        return array(
            'set_id'=>"配置id",
            'set_name'=>Yii::t('clue','Project Name'),
            'set_type'=>"配置类型",
            'u_code'=>Yii::t('clue','u id'),
            'mh_code'=>Yii::t('clue','mh main code'),
            'z_display'=>Yii::t('clue','z display'),
            'z_index'=>"层级",
        );
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,set_id,set_name,set_type,u_code,mh_code,z_display,z_index','safe'),
			array('set_name,set_type','required'),
            array('id','validateID'),
		);
	}

    public function validateID($attribute, $param) {
        $scenario = $this->getScenario();
        $id = empty($this->id)?0:$this->id;
        $row = Yii::app()->db->createCommand()->select("id")
            ->from("sal_set_menu")
            ->where("id!=:id and set_name=:set_name and set_type=:set_type",array(
                ":id"=>$id,
                ":set_name"=>$this->set_name,
                ":set_type"=>$this->set_type,
            ))->queryRow();
        if($row){
            $this->addError($attribute, "名称已存在({$row['id']})");
        }elseif ($scenario=="new"){
            if(empty($this->set_id)){
                $row = Yii::app()->db->createCommand()->select("max(set_id) as max_id")
                    ->from("sal_set_menu")
                    ->where("set_type=:set_type",array(
                        ":set_type"=>$this->set_type,
                    ))->queryRow();
                $this->set_id = $row?$row["max_id"]:0;
                $this->set_id++;
            }
        }
    }

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_set_menu where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->set_id = $row['set_id'];
			$this->set_name = $row['set_name'];
			$this->set_type = $row['set_type'];
			$this->u_code = $row['u_code'];
			$this->mh_code = $row['mh_code'];
			$this->z_display = $row['z_display'];
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
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function save(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_set_menu where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_set_menu(
						set_id,set_name,set_type,u_code,mh_code,z_display,z_index, lcu) values (
						:set_id,:set_name,:set_type,:u_code,:mh_code,:z_display,:z_index, :lcu)";
				break;
			case 'edit':
				$sql = "update sal_set_menu set 
					set_name = :set_name, 
					set_type = :set_type,
					u_code = :u_code,
					mh_code = :mh_code,
					z_index = :z_index,
					luu = :luu,
					z_display = :z_display
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':set_id')!==false)
			$command->bindParam(':set_id',$this->set_id,PDO::PARAM_INT);
		if (strpos($sql,':set_name')!==false)
			$command->bindParam(':set_name',$this->set_name,PDO::PARAM_STR);
		if (strpos($sql,':set_type')!==false)
			$command->bindParam(':set_type',$this->set_type,PDO::PARAM_STR);
		if (strpos($sql,':u_code')!==false)
			$command->bindParam(':u_code',$this->u_code,PDO::PARAM_STR);
		if (strpos($sql,':mh_code')!==false)
			$command->bindParam(':mh_code',$this->mh_code,PDO::PARAM_STR);
		if (strpos($sql,':z_display')!==false){
            $command->bindParam(':z_display',$this->z_display,PDO::PARAM_STR);
        }
		if (strpos($sql,':z_index')!==false){
            $command->bindParam(':z_index',$this->z_index,PDO::PARAM_STR);
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
}
