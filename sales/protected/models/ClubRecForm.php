<?php

class ClubRecForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $rec_year;
	public $month_type=1;
	public $employee_id;
	public $rec_remark;
	public $rec_user;
	public $rec_name;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
        return array(
            'rec_year'=>Yii::t('club','year'),
            'month_type'=>Yii::t('club','month type'),
            'employee_id'=>Yii::t('club','staff name'),
            'code'=>Yii::t('club','staff code'),
            'name'=>Yii::t('club','staff name'),
            'city_name'=>Yii::t('club','staff city'),
            'entry_time'=>Yii::t('club','entry date'),
            'dept_name'=>Yii::t('club','dept name'),
            'rec_user'=>Yii::t('club','referees user'),
            'rec_remark'=>Yii::t('club','referees remark'),
        );
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,rec_year,month_type,employee_id,rec_remark','safe'),
			array('rec_year,month_type,employee_id','required'),
		);
	}

	public function retrieveData($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select * from sal_club_rec where id='".$index."'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->rec_year = $row['rec_year'];
			$this->month_type = $row['month_type'];
			$this->employee_id = $row['employee_id'];
			$this->rec_remark = $row['rec_remark'];
			$this->rec_user = $row['rec_user'];
			$this->rec_name = $row['rec_name'];
            return true;
		}else{
		    return false;
        }
	}

    public static function getClubRecStaffList($id=0,$year=2022,$month_type=1){
        $suffix = Yii::app()->params['envSuffix'];
        $list = array(""=>"");
        $id = is_numeric($id)?$id:0;
	    $model = new ClubSalesList();
	    $model->clubSalesAll($year,$month_type);
	    $employeeList = $model->user_last_rec;
	    if(!in_array($id,$employeeList)){
	        $employeeList[]=$id;
        }
        $employeeSql = implode(",",$employeeList);
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.code,a.name")
            ->from("hr{$suffix}.hr_employee a")
            ->where("a.id in ({$employeeSql})")->queryAll();
        /* 2022-10-28日推薦人改成排行榜的後一名
        $id = is_numeric($id)?$id:0;
        $noCity = ClubSettingForm::$noCity;
        $noCitySql = implode("','",$noCity);
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.code,a.name")
            ->from("hr{$suffix}.hr_employee a")
            ->leftJoin("hr{$suffix}.hr_dept b","a.position=b.id")
            ->leftJoin("hr{$suffix}.hr_binding f","a.id=f.employee_id")
            ->where("a.id = '{$id}' or (f.user_id is not null and b.dept_class='Sales' and a.city not in ('{$noCitySql}') and b.manager_leave=1 and a.staff_status!=-1)")->queryAll();
        */
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = $row["name"]." （".$row["code"]."）";
            }
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
				$sql = "delete from sal_club_rec where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_club_rec(
						rec_year,month_type,employee_id,rec_remark,rec_user,rec_name, lcu, lcd) values (
						:rec_year, :month_type, :employee_id, :rec_remark, :rec_user, :rec_name, :lcu, :lcd)";
				break;
			case 'edit':
				$sql = "update sal_club_rec set 
					rec_year = :rec_year, 
					month_type = :month_type,
					employee_id = :employee_id,
					rec_remark = :rec_remark,
					rec_user = :rec_user,
					rec_name = :rec_name,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
		$uidDisplay = Yii::app()->user->user_display_name();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':rec_year')!==false)
			$command->bindParam(':rec_year',$this->rec_year,PDO::PARAM_INT);
		if (strpos($sql,':month_type')!==false)
			$command->bindParam(':month_type',$this->month_type,PDO::PARAM_INT);
		if (strpos($sql,':employee_id')!==false)
			$command->bindParam(':employee_id',$this->employee_id,PDO::PARAM_INT);
		if (strpos($sql,':rec_remark')!==false)
			$command->bindParam(':rec_remark',$this->rec_remark,PDO::PARAM_STR);
		if (strpos($sql,':rec_user')!==false)
			$command->bindParam(':rec_user',$uid,PDO::PARAM_STR);
		if (strpos($sql,':rec_name')!==false)
			$command->bindParam(':rec_name',$uidDisplay,PDO::PARAM_STR);
//rec_year,month_type,employee_id,rec_remark,rec_user,rec_name
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

        $this->resetClubSales();
		return true;
	}

	//強制刷新排行榜
	private function resetClubSales(){
        $model = new ClubSalesList();
        $model->clubSalesAll($this->rec_year,$this->month_type,true);
    }
}