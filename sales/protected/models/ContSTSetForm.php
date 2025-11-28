<?php

class ContSTSetForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $name;
	public $str_type;
	public $mh_code;
	public $z_display=1;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
        return array(
            'name'=>Yii::t('clue','Project Name'),
            'str_type'=>"选择范围",
            'z_display'=>Yii::t('clue','z display'),
        );
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,str_type,mh_code','safe'),
			array('name,str_type,z_display','required'),
            array('id','validateID'),
		);
	}

    public function validateID($attribute, $param) {
        $scenario = $this->getScenario();
        if($scenario=="new"){
            $row = Yii::app()->db->createCommand()->select("id")
                ->from("sal_cont_str")
                ->where("name=:name and str_type=:str_type",array(
                    ":name"=>$this->name,
                    ":str_type"=>$this->str_type,
                ))->queryRow();
            if($row){
                $this->addError($attribute, "名称已存在，无法重复添加");
            }
        }else{
            if($scenario=="delete"){
                $clueFlowRow = Yii::app()->db->createCommand()->select("*")
                    ->from("sal_contpro_virtual")
                    ->where("stop_set_id=:id",array(":id"=>$this->id))->queryRow();
                if($clueFlowRow){
                    $this->addError($attribute, "该名称已关联合同，无法删除");
                }
            }
        }
    }

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_cont_str where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->name = $row['name'];
			$this->str_type = $row['str_type'];
			$this->mh_code = $row['mh_code'];
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
				$sql = "delete from sal_cont_str where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_cont_str(
						name,str_type,mh_code, z_display, lcu, luu) values (
						:name,:str_type,:mh_code, :z_display, :lcu, :luu)";
				break;
			case 'edit':
				$sql = "update sal_cont_str set 
					name = :name, 
					str_type = :str_type,
					mh_code = :mh_code,
					z_display = :z_display,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':str_type')!==false)
			$command->bindParam(':str_type',$this->str_type,PDO::PARAM_INT);
		if (strpos($sql,':name')!==false)
			$command->bindParam(':name',$this->name,PDO::PARAM_STR);
		if (strpos($sql,':mh_code')!==false)
			$command->bindParam(':mh_code',$this->mh_code,PDO::PARAM_STR);
		if (strpos($sql,':z_display')!==false)
			$command->bindParam(':z_display',$this->z_display,PDO::PARAM_STR);
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
