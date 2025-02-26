<?php

class StopSiteForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $stop_month=6;
	public $month_money=2000;
	public $year_money=24000;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'stop_month'=>Yii::t('customer','stop month'),
            'month_money'=>Yii::t('customer','Monthly Amt'),
            'year_money'=>Yii::t('customer','Year Amt'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,stop_month,month_money,year_money','safe'),
			array('stop_month,month_money,year_money','required'),
            array('stop_month,month_money,year_money','numerical','allowEmpty'=>false,'integerOnly'=>true),
            array('id','validateID','on'=>array("delete")),
		);
	}

    public function validateID($attribute, $params) {
        $this->addError($attribute, "不允许删除");
        return false;
    }

	public function retrieveData($index)
	{
	    //stop_month,month_money,year_money
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select * from sal_stop_site where id='".$index."'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->stop_month = $row['stop_month'];
			$this->month_money = $row['month_money'];
			$this->year_money = $row['year_money'];
            return true;
		}else{
		    return false;
        }
	}

    public static function getStopSiteList(){
        //stop_month,month_money,year_money
        $list = array("stop_month"=>6,"month_money"=>"2000","year_money"=>"24000");
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_stop_site")->queryRow();
        if($row){
            $list["stop_month"] = $row["stop_month"];
            $list["month_money"] = $row["month_money"];
            $list["year_money"] = $row["year_money"];
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
				$sql = "delete from sal_stop_site where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_stop_site(
						stop_month,month_money,year_money, city, lcu, lcd) values (
						:stop_month, :month_money, :year_money, :city, :lcu, :lcd)";
				break;
			case 'edit':
				$sql = "update sal_stop_site set 
					stop_month = :stop_month, 
					month_money = :month_money,
					year_money = :year_money,
					city = :city,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
        //stop_month,month_money,year_money

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':stop_month')!==false)
			$command->bindParam(':stop_month',$this->stop_month,PDO::PARAM_INT);
		if (strpos($sql,':month_money')!==false)
			$command->bindParam(':month_money',$this->month_money,PDO::PARAM_INT);
		if (strpos($sql,':year_money')!==false)
			$command->bindParam(':year_money',$this->year_money,PDO::PARAM_INT);

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