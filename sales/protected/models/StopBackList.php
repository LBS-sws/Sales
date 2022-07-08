<?php

class StopBackList extends CListPageModel
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
            'staff_id'=>Yii::t('service','Resp. Sales(back)'),
            'city_name'=>Yii::t('misc','City'),
            'shiftStatus'=>Yii::t('customer','Shift Status'),
            'bold_service'=>Yii::t('sales','VIP'),

            'back_date'=>Yii::t('customer','shift date'),
            'back_name'=>Yii::t('customer','shift detail'),
        );
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
        $city=Yii::app()->user->city();
        $employee_sql ="";
        if(!empty($this->employee_id)){
            $employee_sql =" and ((a.salesman_id={$this->employee_id} and d.staff_id is null) or d.staff_id={$this->employee_id})";
        }
        $expr_sql = StopOtherList::getExprSql();
        $suffix = Yii::app()->params['envSuffix'];
        $sql1 = "select b.code,b.name,f.description,a.id as service_id,a.amt_paid,a.paid_type,
                a.cont_info,a.status,a.status_dt,h.code as sale_code,h.name as sale_name,a.salesman_id,
                d.id,d.bold_service,d.back_date,d.staff_id,g.type_name
				from swoper{$suffix}.swo_service a 
				 LEFT JOIN swoper{$suffix}.swo_company b ON a.company_id=b.id 
				 LEFT JOIN swoper{$suffix}.swo_customer_type f ON a.cust_type=f.id 
				 LEFT JOIN hr{$suffix}.hr_employee h ON a.salesman_id=h.id 
				 LEFT JOIN sal_stop_back d ON a.id=d.service_id 
				 LEFT JOIN sal_stop_type g ON g.id=d.back_type 
				where a.status = 'T' and a.company_id is not NULL and a.city='{$city}' {$employee_sql} {$expr_sql}
			";
        $sql2 = "select count(a.id)
				from swoper{$suffix}.swo_service a 
				 LEFT JOIN swoper{$suffix}.swo_company b ON a.company_id=b.id 
				 LEFT JOIN swoper{$suffix}.swo_customer_type f ON a.cust_type=f.id 
				 LEFT JOIN hr{$suffix}.hr_employee h ON a.salesman_id=h.id 
				 LEFT JOIN sal_stop_back d ON a.id=d.service_id 
				 LEFT JOIN sal_stop_type g ON g.id=d.back_type 
				where a.status = 'T' and a.company_id is not NULL and a.city='{$city}' {$employee_sql} {$expr_sql}
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
                case 'back_name':
                    if (strpos(Yii::t("customer","No Shift"),$svalue)!==false){
                        $clause .= " and d.back_type is null";
                    }else{
                        $clause .= General::getSqlConditionClause('g.type_name',$svalue);
                    }
                    break;
                case 'status_dt':
                    $svalue = self::searchDate($svalue);
                    $clause .= General::getSqlConditionClause('a.status_dt',$svalue);
                    break;
                case 'back_date':
                    $svalue = self::searchDate($svalue);
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
                    'staff_id'=>StopBackList::getEmployeeNameToId($record['staff_id']),
                );
            }
        }
		$session = Yii::app()->session;
		$session['stopBack_c01'] = $this->getCriteria();
		return true;
	}

    public static function getEmployee(&$model,$search=false){
	    if($search){
            $model->employee_id = 0;
	        return true;
        }
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
        //$city = Yii::app()->user->city();
        $row = Yii::app()->db->createCommand()
            ->select("b.id,b.code,b.name")
            ->from("hr{$suffix}.hr_binding a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->where("a.user_id=:id",array(":id"=>$uid))->queryRow();
        if($row){
            $model->employee_id = $row["id"];
            return true;
        }else{
            $model->employee_id = 0;
            return false;
        }

    }

    public static function getEmployeeNameToId($id){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("code,name")
            ->from("hr{$suffix}.hr_employee")
            ->where("id=:id",array(":id"=>$id))->queryRow();
        if($row){
            return $row["name"]."({$row['code']})";
        }else{
            return $id;
        }

    }

    public function updateVip($serviceId){
        $city=Yii::app()->user->city();
        $employee_sql ="";
        if(!empty($this->employee_id)){
            $employee_sql =" and (a.salesman_id={$this->employee_id} or b.staff_id={$this->employee_id})";
        }
        $expr_sql = StopOtherList::getExprSql();
        $suffix = Yii::app()->params['envSuffix'];
	    $list = array("status"=>0,"message"=>"");
        $row = Yii::app()->db->createCommand()
            ->select("a.id as service_id,b.id,b.bold_service")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("sal_stop_back b","a.id=b.service_id ")
            ->where("a.id=:id and a.city='{$city}' {$employee_sql} {$expr_sql}",array(":id"=>$serviceId))->queryRow();
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
        $employee_sql ="";
        if(!empty($this->employee_id)){
            $employee_sql =" and ((a.salesman_id={$this->employee_id} and d.staff_id is null) or b.staff_id={$this->employee_id})";
        }
        $suffix = Yii::app()->params['envSuffix'];
        $expr_sql = StopOtherList::getExprSql();
        $row = Yii::app()->db->createCommand()
            ->select("count(a.id)")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("sal_stop_back b","a.id=b.service_id ")
            ->where("a.status = 'T' and a.company_id is not NULL and a.city='{$city}' and b.back_date is null {$employee_sql} {$expr_sql}")->queryScalar();
        return $row;
    }

    public static function searchDate($str){
        $svalue = str_replace("/","-",$str);
        $list = explode("-",$svalue);
        if(count($list)==2){
            $str = date("Y-m",strtotime($svalue."-01"));
        }elseif (count($list)==3){
            $str = date("Y-m-d",strtotime($svalue));
        }
        return $str;
    }
}
