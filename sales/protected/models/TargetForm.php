<?php

class TargetForm extends CFormModel
{
	public $id;
	public $employee_name;
	public $sale_day;
    public $ltNowDate=false;//小于当前日期：true

	
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
            array('id','validateID'),
        );
	}

    public function validateID($attribute, $params) {
        $thisDate = PerformanceForm::isVivienne()?"0000/00/00":date("Y/m/01");
        $scenario = $this->getScenario();
        if(in_array($scenario,array("new"))){
            $this->addError($attribute, "无法新增");
        }else{
            $id= empty($this->id)?0:$this->id;
            $row = Yii::app()->db->createCommand()->select("a.*")->from("sal_integral a")
                ->where("a.id=:id",array(":id"=>$id))->queryRow();
            if($row){
                $row["log_dt"] = date("Y/m/d",strtotime($row["year"]."/".$row["month"]."/01"));
                $this->ltNowDate = $row["log_dt"]<$thisDate;
                if($scenario=="delete"){
                    if($row["log_dt"]<$thisDate){
                        $this->addError($attribute, "无法删除({$row["log_dt"]})时间段的数据");
                    }
                }else{
                    $updateBool = $row["log_dt"]<$thisDate;//验证修改前的时间
                    if($updateBool){
                        $notUpdate=self::getNotUpdateList();
                        foreach ($notUpdate as $item){
                            $this->$item = $row[$item];
                        }
                    }
                }
            }else{
                $this->addError($attribute, "数据异常，请刷新重试");
            }
        }
    }

    public static function getNotUpdateList(){
        return array("sale_day");
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
            $thisDate = PerformanceForm::isVivienne()?"0000/00/00":date("Y/m/01");
            $targetDate=date("Y/m/d",strtotime($rows["year"]."/".$rows["month"]."/01"));
            $this->ltNowDate = $targetDate<$thisDate;
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

    public function getReadonly(){
        return $this->scenario=='view'||$this->ltNowDate;
    }
}
