<?php

class StopNoneList extends CListPageModel
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
        );
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
        $city=Yii::app()->user->city();
        $citylist = Yii::app()->user->city_allow();
        $expr_sql = StopOtherList::getExprSql()." and d.back_date is null";
        $suffix = Yii::app()->params['envSuffix'];
        $sql1 = "select b.code,b.name,f.description,a.id as service_id,a.amt_paid,a.paid_type,
                a.cont_info,a.city,a.status,a.status_dt,h.code as sale_code,h.name as sale_name,a.salesman_id,
                d.id,d.bold_service,d.back_date,d.back_type as type_name,j.name as city_name
				from swoper{$suffix}.swo_service a 
				 LEFT JOIN swoper{$suffix}.swo_company b ON a.company_id=b.id 
				 LEFT JOIN swoper{$suffix}.swo_customer_type f ON a.cust_type=f.id 
				 LEFT JOIN hr{$suffix}.hr_employee h ON a.salesman_id=h.id 
				 LEFT JOIN sal_stop_back d ON a.id=d.service_id 
			     LEFT join security$suffix.sec_city j on a.city=j.code	
				where a.status = 'T' and a.company_id is not NULL and a.city in ($citylist) {$expr_sql}
			";
        $sql2 = "select count(a.id)
				from swoper{$suffix}.swo_service a 
				 LEFT JOIN swoper{$suffix}.swo_company b ON a.company_id=b.id 
				 LEFT JOIN swoper{$suffix}.swo_customer_type f ON a.cust_type=f.id 
				 LEFT JOIN hr{$suffix}.hr_employee h ON a.salesman_id=h.id 
				 LEFT JOIN sal_stop_back d ON a.id=d.service_id 
			     LEFT join security$suffix.sec_city j on a.city=j.code	
				where a.status = 'T' and a.company_id is not NULL and a.city in ($citylist) {$expr_sql}
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
                    if (strpos(Yii::t("customer","No Shift"),$svalue)!==false){
                        $clause .= " and d.back_type is null";
                    }else{
                        $clause .= General::getSqlConditionClause('d.back_type',$svalue);
                    }
                    break;
                case 'status_dt':
                    $svalue = StopBackList::searchDate($svalue);
                    $clause .= General::getSqlConditionClause('a.status_dt',$svalue);
                    break;
                case 'back_date':
                    $svalue = StopBackList::searchDate($svalue);
                    $clause .= General::getSqlConditionClause('d.back_date',$svalue);
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
            $order .= " order by d.back_date desc ";
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
                    'city'=>$record['city_name'],
                    'status_dt'=>General::toDate($record['status_dt']),
                    'back_date'=>empty($record['back_date'])?"":General::toDate($record['back_date']),
                    'amt_paid'=>StopOtherForm::getPaidTypeStr($record['paid_type'])."：".floatval($record['amt_paid']),
                    'status'=>Yii::t("customer","Terminate"),
                    'shiftStatus'=>empty($record['type_name'])?Yii::t("customer","No Shift"):$record['type_name'],
                    'textColor'=>empty($record['back_date'])?"text-danger":"",
                    'description'=>$record['description'],
                    'bold_service'=>empty($record['bold_service'])?0:$record['bold_service'],
                    'company_name'=>$record['code'].$record['name'],
                    'salesman'=>$record['sale_name']."({$record['sale_code']})",
                );
            }
        }
		$session = Yii::app()->session;
		$session['stopNone_c01'] = $this->getCriteria();
		return true;
	}

    public function updateVip($serviceId){
        $city=Yii::app()->user->city();
        $citylist = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
        $expr_sql = StopOtherList::getExprSql();
	    $list = array("status"=>0,"message"=>"");
        $row = Yii::app()->db->createCommand()
            ->select("a.id as service_id,b.id,b.bold_service")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("sal_stop_back b","a.id=b.service_id ")
            ->where("a.id=:id and a.city in ($citylist) {$expr_sql}",array(":id"=>$serviceId))->queryRow();
        if($row){
            $list["status"]=1;
            if(empty($row["bold_service"])){
                $bold_service = 1;
                $list["message"]="fa fa-star";
            }else{
                $bold_service = 0;
                $list["message"]="fa fa-star-o";
            }
            if(!empty($row["id"])){
                Yii::app()->db->createCommand()->update("sal_stop_back",array(
                    "bold_service"=>$bold_service,
                    "luu"=>Yii::app()->user->id
                ),"id=".$row["id"]);
            }else{
                Yii::app()->db->createCommand()->insert("sal_stop_back",array(
                    "service_id"=>$serviceId,
                    "bold_service"=>1,
                    "lcu"=>Yii::app()->user->id
                ));
            }
        }else{
            $list["message"]="服务不存在，请刷新重试";
        }
	    return $list;
    }

    public function countNotify(){
        $city=Yii::app()->user->city();
        $citylist = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
        $expr_sql = StopOtherList::getExprSql();
        $row = Yii::app()->db->createCommand()
            ->select("count(a.id)")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("sal_stop_back b","a.id=b.service_id ")
            ->where("a.status = 'T' and a.company_id is not NULL and a.city in ($citylist) and b.back_date is null {$expr_sql}")->queryScalar();
        return $row;
    }
}
