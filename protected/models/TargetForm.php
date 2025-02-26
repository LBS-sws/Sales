<?php

class TargetForm extends CFormModel
{
	public $id;
	public $employee_name;
	public $sale_day;

	
	public function attributeLabels()
	{
		return array(
            'sale_day'=>Yii::t('code','Sale_day'),
            'employee_name'=>Yii::t('sales','Employee_name'),
		);
	}

	public function rules()
	{
        return array(
            array('','required'),
            array('id,sale_day,','safe'),
        );
	}


	public function retrieveData($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select a.*	,c.name	
				from sal_integral a 
				left outer join hr$suffix.hr_binding b on a.username=b.user_id 
					inner join  hr$suffix.hr_employee c on b.employee_id = c.id  
				where a.id=$index";
		$rows = Yii::app()->db->createCommand($sql)->queryRow();
		if (count($rows) > 0)
		{
				$this->id = $rows['id'];
				$this->employee_name = $rows['name'];
				$this->sale_day = $rows['sale_day'];
		}
		return true;
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveTrans($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	
	protected function saveTrans(&$connection) {
		$sql = '';
		switch ($this->scenario) {
//			case 'delete':
//				$sql = "update sal_integral set
//						sal_day = :sal_day,
//						luu = :luu
//						where id = :id and city = :city
//					";
//				break;
//			case 'new':
//				$sql = "insert into acc_trans(
//						trans_dt, trans_type_code, acct_id,	trans_desc, amount,	status, city, luu, lcu) values (
//						:trans_dt, :trans_type_code, :acct_id, :trans_desc, :amount, 'A', :city, :luu, :lcu)";
//				break;
			case 'edit':
				$sql = "update sal_integral set 
						sale_day = :sale_day	  				  
						where id = :id 
					";
				break;
		}

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':sale_day')!==false)
			$command->bindParam(':sale_day',$this->sale_day,PDO::PARAM_INT);
		$command->execute();
		return true;
	}


	


	public function adjustRight() {
		return Yii::app()->user->validFunction('HK05');
	}
	
	public function voidRight() {
		return Yii::app()->user->validFunction('HK05');
	}

	public function isReadOnly() {
//		return ($this->scenario=='view'||$this->status=='V'||$this->posted||!empty($this->req_ref_no)||!empty($this->t3_doc_no));
		return ($this->scenario!='new'||$this->status=='V'||$this->posted||!empty($this->req_ref_no)||!empty($this->t3_doc_no));
	}
}
