<?php

class StopOtherList extends CListPageModel
{
    public $employee_id;
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'company_name'=>Yii::t('service','Customer'),
            'description'=>Yii::t('service','Customer Type'),
            'nature_desc'=>Yii::t('service','Nature'),
            'service'=>Yii::t('service','Service'),
            'cont_info'=>Yii::t('service','Contact'),
            'status'=>Yii::t('service','Record Type'),
            'status_dt'=>Yii::t('service','Record Date'),
            'salesman'=>Yii::t('service','Resp. Sales'),
            'city_name'=>Yii::t('misc','City'),
		);
	}

	public static function getExprSql(){
	    $list = StopSiteForm::getStopSiteList();
	    $date = date("Y/m/d",strtotime(" - {$list['stop_month']} months"));
        $sql = " and date_format(a.status_dt,'%Y')>=2021 and date_format(a.status_dt,'%Y/%m/%d')<='{$date}' and (
            (a.paid_type='M' and a.amt_paid>={$list['month_money']})or
            (a.paid_type='Y' and a.amt_paid>={$list['year_money']})
        )";
	    return $sql;
    }
	
	public function retrieveDataByPage($pageNum=1)
	{
	    $staffSql=" and h.staff_status=-1";
	    if(StopBackList::getEmployee($this)){
	        $staffSql = " and (h.staff_status=-1 or h.id=$this->employee_id)";
        }
	    $expr_sql = self::getExprSql();
        $city=Yii::app()->user->city();
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select b.code,b.name,f.description,a.id as service_id,a.service,
                a.cont_info,a.status,a.status_dt,h.code as sale_code,h.name as sale_name,a.salesman_id,d.id,d.bold_service
				from swoper{$suffix}.swo_service a 
				 LEFT JOIN swoper{$suffix}.swo_company b ON a.company_id=b.id 
				 LEFT JOIN swoper{$suffix}.swo_customer_type f ON a.cust_type=f.id 
				 LEFT JOIN hr{$suffix}.hr_employee h ON a.salesman_id=h.id 
				 LEFT JOIN sal_stop_back d ON a.id=d.service_id 
				where a.status = 'T' and a.company_id is not NULL and a.city='{$city}' {$staffSql} and d.id is NULL {$expr_sql}
			";
		$sql2 = "select count(a.id)
				from swoper{$suffix}.swo_service a 
				 LEFT JOIN swoper{$suffix}.swo_company b ON a.company_id=b.id 
				 LEFT JOIN swoper{$suffix}.swo_customer_type f ON a.cust_type=f.id 
				 LEFT JOIN hr{$suffix}.hr_employee h ON a.salesman_id=h.id 
				 LEFT JOIN sal_stop_back d ON a.id=d.service_id 
				where a.status = 'T' and a.company_id is not NULL and a.city='{$city}' {$staffSql} and d.id is NULL  {$expr_sql}
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'company_name':
					$clause .= " and (b.code like '%$svalue%' or b.name like '%$svalue%')";
					break;
				case 'cont_info':
					$clause .= General::getSqlConditionClause('a.cont_info',$svalue);
					break;
				case 'service':
					$clause .= General::getSqlConditionClause('a.service',$svalue);
					break;
				case 'salesman':
				    $clause .= " and (h.code like '%$svalue%' or h.name like '%$svalue%')";
                    break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
            $order .= " order by {$this->orderField} ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by a.id desc ";
        }

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();

		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
					$this->attr[] = array(
						'id'=>$record['id'],
						'service_id'=>$record['service_id'],
						'status_dt'=>General::toDate($record['status_dt']),
						'cont_info'=>$record['cont_info'],
						'status'=>Yii::t("customer","Terminate"),
						'service'=>$record['service'],
						'description'=>$record['description'],
						'bold_service'=>empty($record['bold_service'])?0:$record['bold_service'],
						'company_name'=>$record['code'].$record['name'],
						'salesman'=>$record['sale_name']."({$record['sale_code']})",
					);
			}
		}
		$session = Yii::app()->session;
		$session['stopOther_c01'] = $this->getCriteria();
		return true;
	}


    public static function saleman(){
        $suffix = Yii::app()->params['envSuffix'];
        $city=Yii::app()->user->city();
        $rows = Yii::app()->db->createCommand()
            ->select("b.id,b.name,b.code")
            ->from("security{$suffix}.sec_user_access a")
            ->leftJoin("hr{$suffix}.hr_binding h","a.username=h.user_id")
            ->leftJoin("hr{$suffix}.hr_employee b","h.employee_id=b.id")
            ->where("a.system_id='sal' and a.a_read_write like '%SC01%' and b.city='{$city}'and b.staff_status=0")->queryAll();
        return $rows?$rows:array();
        //$sql="select code,name,id from hr$suffix.hr_employee WHERE  position in (SELECT id FROM hr$suffix.hr_dept where dept_class='sales') AND staff_status = 0 and city ='{$city}'";
        //$records = Yii::app()->db->createCommand($sql)->queryAll();
        //return $records;
    }

    public function countNotify(){
        $staffSql=" and h.staff_status=-1";
        if(StopBackList::getEmployee($this)){
            $staffSql = " and (h.staff_status=-1 or h.id=$this->employee_id)";
        }
        $suffix = Yii::app()->params['envSuffix'];
        $city=Yii::app()->user->city();
        $expr_sql = StopOtherList::getExprSql();
        $row = Yii::app()->db->createCommand()
            ->select("count(a.id)")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("sal_stop_back d","a.id=d.service_id ")
            ->leftJoin("hr{$suffix}.hr_employee h","a.salesman_id=h.id")
            ->where("a.status = 'T' and a.company_id is not NULL and a.city='{$city}' {$staffSql} and d.id is NULL  {$expr_sql}")->queryScalar();
        return $row;
    }
}
