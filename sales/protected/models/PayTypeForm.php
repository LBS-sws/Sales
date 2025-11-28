<?php

class PayTypeForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $name;
	public $zt_code;
	public $u_id;
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
            'zt_code'=>Yii::t('clue','zt main code'),
            'u_id'=>Yii::t('clue','u id'),
            'z_display'=>Yii::t('clue','z display'),
        );
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,u_id,zt_code','safe'),
			array('name,u_id,zt_code,z_display','required'),
            array('id','validateID'),
		);
	}

    public function validateID($attribute, $param) {
        $scenario = $this->getScenario();
        if($scenario=="new"){
            $row = Yii::app()->db->createCommand()->select("id")
                ->from("sal_pay")
                ->where("name=:name",array(
                    ":name"=>$this->name
                ))->queryRow();
            if($row){
                $this->addError($attribute, "印章已存在，无法重复添加");
            }
        }else{
            if($scenario=="delete"){
                $clueFlowRow = Yii::app()->db->createCommand()->select("*")
                    ->from("sal_contract")
                    ->where("payType_type_id=:id",array(":id"=>$this->id))->queryRow();
                if($clueFlowRow){
                    $this->addError($attribute, "该印章已关联合同，无法删除");
                }
            }
        }
    }

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_pay where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->name = $row['name'];
			$this->zt_code = $row['zt_code'];
			$this->u_id = $row['u_id'];
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
				$sql = "delete from sal_pay where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_pay(
						name,u_id,zt_code, z_display, lcu, luu) values (
						:name,:u_id,:zt_code, :z_display, :lcu, :luu)";
				break;
			case 'edit':
				$sql = "update sal_pay set 
					name = :name, 
					u_id = :u_id,
					zt_code = :zt_code,
					z_display = :z_display,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':u_id')!==false)
			$command->bindParam(':u_id',$this->u_id,PDO::PARAM_INT);
		if (strpos($sql,':name')!==false)
			$command->bindParam(':name',$this->name,PDO::PARAM_STR);
		if (strpos($sql,':zt_code')!==false)
			$command->bindParam(':zt_code',$this->zt_code,PDO::PARAM_STR);
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
