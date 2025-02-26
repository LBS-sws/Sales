<?php

class StopAgainList extends CListPageModel
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
            'status_dt'=>Yii::t('service','Terminate Date'),
            'amt_paid'=>Yii::t('service','Paid Amt'),
            'salesman'=>Yii::t('service','Resp. Sales'),
            'city_name'=>Yii::t('misc','City'),
            'shiftStatus'=>Yii::t('customer','Shift Status'),
            'bold_service'=>Yii::t('sales','VIP'),
            'city'=>Yii::t('sales','City'),

            'back_date'=>Yii::t('customer','shift date'),
            'back_name'=>Yii::t('customer','shift detail'),
            'again_end_date'=>Yii::t('customer','again end date'),
        );
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
        $nowDate = date("Y-m-d");
        $city=Yii::app()->user->city();
        $citylist = Yii::app()->user->city_allow();
        $employee_sql ="";
        if(!empty($this->employee_id)){
            $employee_sql =" and (a.salesman_id={$this->employee_id} or d.staff_id={$this->employee_id})";
        }
        $suffix = Yii::app()->params['envSuffix'];
        $sql1 = "select b.code,b.name,f.description,a.id as service_id,a.amt_paid,a.paid_type,
                a.cont_info,a.city,a.status,a.status_dt,h.code as sale_code,h.name as sale_name,a.salesman_id,
                info.id,info.stop_id,d.bold_service,info.back_date,g.type_name,j.name as city_name,
                date_add(info.back_date, interval g.again_day day) as again_end_date
				from sal_stop_back_info info 
				 LEFT JOIN sal_stop_back d on info.stop_id=d.id 
				 LEFT JOIN swoper{$suffix}.swo_service a ON a.id=d.service_id 
				 LEFT JOIN swoper{$suffix}.swo_company b ON a.company_id=b.id 
				 LEFT JOIN swoper{$suffix}.swo_customer_type f ON a.cust_type=f.id 
				 LEFT JOIN hr{$suffix}.hr_employee h ON a.salesman_id=h.id 
				 LEFT JOIN sal_stop_type g ON g.id=info.back_type 
			     LEFT join security$suffix.sec_city j on a.city=j.code	
				where info.end_bool=0 and a.city in ($citylist) {$employee_sql}
			";
        $sql2 = "select count(a.id)
				from sal_stop_back_info info 
				 LEFT JOIN sal_stop_back d on info.stop_id=d.id 
				 LEFT JOIN swoper{$suffix}.swo_service a ON a.id=d.service_id 
				 LEFT JOIN swoper{$suffix}.swo_company b ON a.company_id=b.id 
				 LEFT JOIN swoper{$suffix}.swo_customer_type f ON a.cust_type=f.id 
				 LEFT JOIN hr{$suffix}.hr_employee h ON a.salesman_id=h.id 
				 LEFT JOIN sal_stop_type g ON g.id=info.back_type 
			     LEFT join security$suffix.sec_city j on a.city=j.code	
				where info.end_bool=0 and a.city in ($citylist) {$employee_sql}
			";
        $clause = "";
        if (!empty($this->searchField) && !empty($this->searchValue)) {
            $svalue = str_replace("'","\'",$this->searchValue);
            switch ($this->searchField) {
                case 'company_name':
                    $clause .= " and (b.code like '%$svalue%' or b.name like '%$svalue%')";
                    break;
                case 'description':
                    $clause .= General::getSqlConditionClause('f.description',$svalue);
                    break;
                case 'city':
                    $clause .= General::getSqlConditionClause('j.name',$svalue);
                    break;
                case 'back_name':
                    $clause .= General::getSqlConditionClause('g.type_name',$svalue);
                    break;
                case 'status_dt':
                    $svalue = StopBackList::searchDate($svalue);
                    $clause .= General::getSqlConditionClause('a.status_dt',$svalue);
                    break;
                case 'back_date':
                    $svalue = StopBackList::searchDate($svalue);
                    $clause .= General::getSqlConditionClause('info.back_date',$svalue);
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
            $order .= " order by again_end_date desc ";
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
                    'textColor'=>$record['again_end_date']<=$nowDate?"text-danger":"",
                    'id'=>$record['id'],
                    'stop_id'=>$record['stop_id'],
                    'service_id'=>$record['service_id'],
                    'city'=>$record['city_name'],
                    'status_dt'=>General::toDate($record['status_dt']),
                    'again_end_date'=>General::toDate($record['again_end_date']),
                    'back_date'=>empty($record['back_date'])?"":General::toDate($record['back_date']),
                    'amt_paid'=>StopOtherForm::getPaidTypeStr($record['paid_type'])."：".floatval($record['amt_paid']),
                    'status'=>Yii::t("customer","Terminate"),
                    'shiftStatus'=>empty($record['type_name'])?Yii::t("customer","No Shift"):$record['type_name'],
                    //'textColor'=>empty($record['back_date'])?"text-danger":"",
                    'description'=>$record['description'],
                    'bold_service'=>empty($record['bold_service'])?0:$record['bold_service'],
                    'company_name'=>$record['code'].$record['name'],
                    'salesman'=>$record['sale_name']."({$record['sale_code']})",
                );
            }
        }
		$session = Yii::app()->session;
		$session['stopAgain_c01'] = $this->getCriteria();
		return true;
	}

    public function countNotify(){
        $nowDate = date("Y-m-d");
        $citylist = Yii::app()->user->city_allow();
        $employee_sql ="";
        if(!empty($this->employee_id)){
            $employee_sql =" and (a.salesman_id={$this->employee_id} or b.staff_id={$this->employee_id})";
        }
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("count(info.id)")
            ->from("sal_stop_back_info info")
            ->leftJoin("sal_stop_type f","f.id=info.back_type")
            ->leftJoin("sal_stop_back b","info.stop_id=b.id ")
            ->leftJoin("swoper{$suffix}.swo_service a","a.id=b.service_id ")
            ->where("info.end_bool=0 and f.again_type=1 and date_add(info.back_date, interval f.again_day day)<='{$nowDate}' and a.city in ($citylist) {$employee_sql}")->queryScalar();
        return $row;
    }
}
