<?php

class YewudaleiSetForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $name;
	public $u_id;
	public $bs_id;
	public $mh_code;
	public $status=1;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
        return array(
            'name'=>Yii::t('clue','Project Name'),
            'u_id'=>Yii::t('clue','u id'),
            'bs_id'=>"北森id标识",
            'mh_code'=>Yii::t('clue','mh main code'),
            'status'=>Yii::t('clue','z display'),
        );
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,u_id,bs_id,mh_code','safe'),
			array('name,status','required'),
            array('id','validateID'),
		);
	}

    public function validateID($attribute, $param) {
        $scenario = $this->getScenario();
        if($scenario=="new"){
            $row = Yii::app()->db->createCommand()->select("id")
                ->from("sal_yewudalei")
                ->where("name=:name",array(
                    ":name"=>$this->name,
                ))->queryRow();
            if($row){
                $this->addError($attribute, "名称已存在，无法重复添加");
            }
        }else{
            if($scenario=="delete"){
                $clueFlowRow = Yii::app()->db->createCommand()->select("*")
                    ->from("sal_clue")
                    ->where("yewudalei=:id",array(":id"=>$this->id))->queryRow();
                if($clueFlowRow){
                    $this->addError($attribute, "该名称已被使用，无法删除");
                }
            }
        }
    }

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_yewudalei where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->name = $row['name'];
			$this->mh_code = $row['mh_code'];
			$this->u_id = $row['u_id'];
			$this->bs_id = $row['bs_id'];
			$this->status = $row['status'];
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
				$sql = "delete from sal_yewudalei where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_yewudalei(
						name,mh_code,u_id,bs_id, status) values (
						:name,:mh_code,:u_id,:bs_id, :status)";
				break;
			case 'edit':
				$sql = "update sal_yewudalei set 
					name = :name, 
					mh_code = :mh_code,
					u_id = :u_id,
					bs_id = :bs_id,
					status = :status
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':name')!==false)
			$command->bindParam(':name',$this->name,PDO::PARAM_STR);
		if (strpos($sql,':mh_code')!==false)
			$command->bindParam(':mh_code',$this->mh_code,PDO::PARAM_STR);
		if (strpos($sql,':status')!==false)
			$command->bindParam(':status',$this->status,PDO::PARAM_STR);
		if (strpos($sql,':u_id')!==false){
            $this->u_id = empty($this->u_id)?null:$this->u_id;
            $command->bindParam(':u_id',$this->u_id,PDO::PARAM_STR);
        }
		if (strpos($sql,':bs_id')!==false){
            $this->bs_id = empty($this->bs_id)?0:$this->bs_id;
            $command->bindParam(':bs_id',$this->bs_id,PDO::PARAM_STR);
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
