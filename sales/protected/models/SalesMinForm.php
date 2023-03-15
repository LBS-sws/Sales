<?php

class SalesMinForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $start_date;
	public $min_num;
	public $only_type;

    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'min_num'=>Yii::t('sales','min num'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,min_num,start_date,only_type','safe'),
			array('min_num','required'),
		);
	}

	public function retrieveData()
	{
		$row = Yii::app()->db->createCommand()->select("*")->from("sal_sales_min")
            ->where("id>0")->order("id desc")->queryRow();
		if ($row) {
			$this->id = $row['id'];
			$this->min_num = $row['min_num'];
			$this->only_type = $row['only_type'];
			$this->start_date = General::toDate($row['start_date']);
		}else{
            $uid = Yii::app()->user->id;
            $city = Yii::app()->user->city();
            $this->min_num = 0;
            $this->only_type = 1;
            $this->start_date = "2020/01/01";
            Yii::app()->db->createCommand()->insert("sal_sales_min",array(
                "min_num"=>0,
                "start_date"=>"2020/01/01",
                "city"=>$city,
                "lcu"=>$uid
            ));
            $this->id = Yii::app()->db->getLastInsertID();
        }
        return true;
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
        $city = Yii::app()->user->city();
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_sales_min where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_sales_min(
						min_num, start_date, city, only_type, lcu, lcd) values (
						:min_num, :start_date, :city, :only_type, :lcu, :lcd)";
				break;
			case 'edit':
				$sql = "update sal_sales_min set 
					min_num = :min_num,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':min_num')!==false)
			$command->bindParam(':min_num',$this->min_num,PDO::PARAM_INT);

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