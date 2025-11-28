<?php

class VisitList extends CListPageModel
{
    public $shift=null;
    public $sign_odds="";

	public function attributeLabels()
	{
		return array(	
			'visit_dt'=>Yii::t('sales','Visit Date'),
//			'status_dt'=>Yii::t('sales','Actual Visit Date'),
			'city_name'=>Yii::t('sales','City'),
			'cust_name'=>Yii::t('sales','Customer Name'),
			'district'=>Yii::t('sales','District'),
			'quote'=>Yii::t('sales','Quote'),
			'visit_type'=>Yii::t('sales','Type'),
			'visit_obj'=>Yii::t('sales','Objective'),
			'cust_type'=>Yii::t('sales','Customer Type'),
			'staff'=>Yii::t('sales','Staff'),
			'cust_vip'=>Yii::t('sales','VIP').'('.Yii::t('sales','Star').')',
            'visitdoc'=>Yii::t('misc','Attachment'),
            'quotation'=>Yii::t('sales','Quotation judgment'),
            'sign_odds'=>Yii::t('ka','sign odds'),
		);
	}

    public function rules()
    {
        return array(
            array('shift,sign_odds, attr, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType, filter, dateRangeValue','safe',),
        );
    }

	public function searchColumns() {
		$suffix = Yii::app()->params['envSuffix'];
		$search = array(
			'cust_vip'=>"i.cust_vip='Y'",
			'visit_dt'=>"date_format(a.visit_dt,'%Y/%m/%d')",
//			'status_dt'=>"date_format(a.status_dt,'%Y/%m/%d')",
			'cust_name'=>'a.cust_name',
			'sign_odds'=>'a.sign_odds',
			'visit_type'=>'(select d.name from sal_visit_type d where a.visit_type = d.id)',
			'visit_obj'=>'VisitObjDesc(a.visit_obj)',
			'cust_type'=>'g.name',
			'district'=>'h.name',
			//'street'=>'a.street',
            'quotation'=>'a.quotation',
		);
		if (!Yii::app()->user->isSingleCity()) $search['city_name'] = 'b.name';
		if (VisitForm::isReadAll()) {
			$search['staff'] = "concat(f.code,' - ',f.name)";
		}
		return $search;
	}
	
	public function staticSearchColumns() {
		return array('cust_vip');
	}

	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$user = Yii::app()->user->id;
		$sql1 = "select a.id,a.lcd,a.username,a.visit_dt,a.status,a.city,a.shift_user,a.cust_name,a.shift,a.sign_odds,
                f.code as staff_code,f.name as staff_name, b.name as city_name, concat(f.code,' - ',f.name) as staff, f.staff_status, 
				d.name as visit_type_name,
				g.name as cust_type_name,
				a.doc_count as visitdoc,
				h.name as district_name,
				 a.visit_obj_name,
				 a.visit_info_text,
				  i.cust_vip
				from sal_visit a
				inner join sal_visit_type d on a.visit_type = d.id 
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.city in ($citylist)
			";
		$sql2 = "select count(a.id)
				from sal_visit a 
				inner join sal_visit_type d on a.visit_type = d.id 
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.city in ($citylist)
			";
		if (!(VisitForm::isReadAll())) {
			$x = " and (a.username='$user' or a.shift_user='$user') ";
			$sql1 .= $x;
			$sql2 .= $x;
		} else {
			$x = " and a.username is not null ";
			$sql1 .= $x;
			$sql2 .= $x;
		}
		if(!empty($this->shift)){
            if($this->shift=='Z'){//转移拜访
                $x = " and a.shift='Z' ";
            }else{//非转移拜访
                $x = " and (a.shift<>'Z' or a.shift is null) ";
            }
            $sql1 .= $x;
            $sql2 .= $x;
        }
		$clause = "";
		if($this->sign_odds!==""){
            $this->sign_odds = is_numeric($this->sign_odds)?$this->sign_odds:0;
		    $clause.=" and a.sign_odds='{$this->sign_odds}' ";
        }
		$static = $this->staticSearchColumns();
		$columns = $this->searchColumns();
		if (!empty($this->searchField) && (!empty($this->searchValue) || in_array($this->searchField, $static) || $this->isAdvancedSearch())) {
			if ($this->isAdvancedSearch()) {
				$clause = $this->buildSQLCriteria();
			} elseif (in_array($this->searchField, $static)) {
				$clause .= 'and '.$columns[$this->searchField];
			} else {
				$svalue = str_replace("'","\'",$this->searchValue);
				$clause .= General::getSqlConditionClause($columns[$this->searchField],$svalue);
			}
		}
		$clause .= $this->getDateRangeCondition('a.visit_dt');

		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'city_name': $orderf = 'b.name'; break;
				case 'cust_name': $orderf = 'a.cust_name'; break;
				case 'staff': $orderf = '(case a.shift when "Z" then a.shift_user else a.username end)'; break;
				case 'visit_dt': $orderf = 'a.visit_dt'; break;
//				case 'status_dt': $orderf = 'a.status_dt'; break;
				case 'district': $orderf = 'h.name'; break;
				case 'street': $orderf = 'a.street'; break;
				case 'visit_type': $orderf = 'visit_type_name'; break;
				case 'visit_obj': $orderf = 'a.visit_obj_name'; break;
				case 'quote': $orderf = 'a.visit_info_text'; break;
				case 'visitdoc': $orderf = 'a.doc_count'; break;
				case 'cust_type': $orderf = 'g.name'; break;
				default: $orderf = $this->orderField; break;
			}
			$order .= " order by ".$orderf." ";
			if ($this->orderType=='D') $order .= "desc ";
		} else {
//			$order = Yii::app()->user->isSingleCity()
//					? " order by a.visit_dt desc, f.code"
//					: " order by a.visit_dt desc, b.name, f.code";
			$order = " order by a.visit_dt desc, a.city, a.username";
		}

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();

		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
//echo $sql;
//Yii::app()->end();
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		$list = array();

		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
//                print_r("<pre>");
//                print_r($quote);
				$this->attr[] = array(
					'id'=>$record['id'],
					'lcd'=>$record['lcd'],
					'username'=>$record['username'],
					'visit_dt'=>General::toDate($record['visit_dt']),
//					'status_dt'=>($record['status']=='Y' ? General::toDate($record['status_dt']) : ''),
					'status'=>$record['status'],
					'city_name'=>$record['city_name'],
					'city'=>$record['city'],
					'staff'=>$record['staff_code']."-".$record['staff_name'],
					'district'=>$record['district_name'],
					'visit_info_text'=>$record['visit_info_text'],
					'visit_type'=>$record['visit_type_name'],
					'visit_obj'=>$record['visit_obj_name'],
					'cust_type'=>$record['cust_type_name'],
					'cust_name'=>$record['cust_name'],
					'cust_vip'=>$record['cust_vip'],
                    'shift'=>$record['staff_status']==-1&&$record['shift']!="Z"?"Y":$record['shift'],
                    'shift_user'=>$record['shift_user'],
                    'visitdoc'=>$record['visitdoc'],
                    'sign_odds'=>VisitForm::getSignOddsStrByKey($record['sign_odds']),
				);
			}
		}
		$session = Yii::app()->session;
		$session[$this->criteriaName()] = $this->getCriteria();
		return true;
	}

    public function retrieveDataByPage_visit($pageNum=1,$arr)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $citylist = Yii::app()->user->city_allow();
        $user = Yii::app()->user->id;
        $end=str_replace("/","-",$arr['end']);
        $start=str_replace("/","-",$arr['start']);
        $sql1 = "select a.*,f.code as staff_code,f.name as staff_name, b.name as city_name, concat(f.code,' - ',f.name) as staff,  
				(select d.name from sal_visit_type d where a.visit_type = d.id) as visit_type_name,
				g.name as cust_type_name,
				docman$suffix.countdoc('visit',a.id) as visitdoc,
				h.name as district_name, VisitObjDesc(a.visit_obj) as visit_obj_name, i.cust_vip
				from sal_visit a
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
			where b.name ='".$arr['city']."' and a.visit_dt <='".$end."' and  a.visit_dt >='".$start."' and a.visit_obj like '%10%' and f.name='".$arr['sales']."'
			";
        $sql2 = "select count(a.id)
				from sal_visit a
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where b.name ='".$arr['city']."' and a.visit_dt <='".$end."' and  a.visit_dt >='".$start."' and a.visit_obj like '%10%' and f.name='".$arr['sales']."'
			";
        if (!(VisitForm::isReadAll())) {
            $x = " and a.username='$user' ";
            $sql1 .= $x;
            $sql2 .= $x;
        } else {
            $x = " and a.username is not null ";
            $sql1 .= $x;
            $sql2 .= $x;
        }
        if(!empty($this->shift)){
            if($this->shift=='Z'){//转移拜访
                $x = " and a.shift='Z' ";
            }else{//非转移拜访
                $x = " and (a.shift<>'Z' or a.shift is null) ";
            }
            $sql1 .= $x;
            $sql2 .= $x;
        }
        $clause = "";
        if($this->sign_odds!==""){
            $this->sign_odds = is_numeric($this->sign_odds)?$this->sign_odds:0;
            $clause.=" and a.sign_odds='{$this->sign_odds}' ";
        }
        $static = $this->staticSearchColumns();
        $columns = $this->searchColumns();
        if (!empty($this->searchField) && (!empty($this->searchValue) || in_array($this->searchField, $static) || $this->isAdvancedSearch())) {
            if ($this->isAdvancedSearch()) {
                $clause = $this->buildSQLCriteria();
            } elseif (in_array($this->searchField, $static)) {
                $clause .= 'and '.$columns[$this->searchField];
            } else {
                $svalue = str_replace("'","\'",$this->searchValue);
                $clause .= General::getSqlConditionClause($columns[$this->searchField],$svalue);
            }
        }
		$clause .= $this->getDateRangeCondition('a.visit_dt');

        $order = "";
        if (!empty($this->orderField)) {
            switch ($this->orderField) {
                case 'city_name': $orderf = 'b.name'; break;
                case 'cust_name': $orderf = 'a.cust_name'; break;
                case 'staff': $orderf = '(case a.shift when "Z" then a.shift_user else a.username end)'; break;
                case 'visit_dt': $orderf = 'a.visit_dt'; break;
//				case 'status_dt': $orderf = 'a.status_dt'; break;
                case 'district': $orderf = 'h.name'; break;
                case 'street': $orderf = 'a.street'; break;
                case 'visit_type': $orderf = 'visit_type_name'; break;
                case 'visit_obj': $orderf = 'visit_obj_name'; break;
                case 'cust_type': $orderf = 'g.name'; break;
                default: $orderf = $this->orderField; break;
            }
            $order .= " order by ".$orderf." ";
            if ($this->orderType=='D') $order .= "desc ";
        } else {
//			$order = Yii::app()->user->isSingleCity()
//					? " order by a.visit_dt desc, f.code"
//					: " order by a.visit_dt desc, b.name, f.code";
            $order = " order by a.visit_dt desc, a.city, a.username";
        }

        $sql = $sql2.$clause;
        $this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();

        $sql = $sql1.$clause.$order;
        $sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
//echo $sql;
//Yii::app()->end();
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        $list = array();
        $this->attr = array();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                $sql = "select field_id, field_value from sal_visit_info where field_id in ('svc_A','svc_B','svc_C','svc_D','svc_E','svc_F4','svc_G3','svc_H') and visit_id = '".$record['id']."'";
                $rows = Yii::app()->db->createCommand($sql)->queryAll();
                foreach ($rows as $a) {
                    for ($i = 0; $i < count($rows); $i++) {
                        $list[$a['field_id']]=$a['field_value'];
                    }
                }
                $quote ="";
                if(!empty($list['svc_A'])){
                    $quote.=$list['svc_A']."(清洁) / -";
                }
                if(!empty($list['svc_B'])){
                    $quote.=$list['svc_B']."(机器) / -";
                }
                if(!empty($list['svc_C'])){
                    $quote.=$list['svc_C']."(灭虫) / -";
                }
                if(!empty($list['svc_D'])){
                    $quote.=$list['svc_D']."(飘盈香) / -";
                }
                if(!empty($list['svc_H'])){
                    $quote.=$list['svc_H']."(蔚诺空气业务) / -";
                }
                if(!empty($list['svc_E'])){
                    $quote.=$list['svc_E']."(甲醛) / -";
                }
                if(!empty($list['svc_F4'])){
                    $quote.=$list['svc_F4']."(纸品) / -";
                }
                if(!empty($list['svc_G3'])){
                    $quote.=$list['svc_G3']."(一次性售卖) / -";
                }
                $quote = substr($quote,0,strlen($quote)-3);
                $quote = explode("-", $quote);
//                print_r("<pre>");
//                print_r($quote);
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'lcd'=>$record['lcd'],
                    'username'=>$record['username'],
                    'visit_dt'=>General::toDate($record['visit_dt']),
//					'status_dt'=>($record['status']=='Y' ? General::toDate($record['status_dt']) : ''),
                    'status'=>$record['status'],
                    'city_name'=>$record['city_name'],
                    'city'=>$record['city'],
                    'staff'=>$record['staff_code']."-".$record['staff_name'],
                    'district'=>$record['district_name'],
                    'quote'=>$quote,
                    'visit_type'=>$record['visit_type_name'],
                    'visit_obj'=>$record['visit_obj_name'],
                    'cust_type'=>$record['cust_type_name'],
                    'cust_name'=>$record['cust_name'],
                    'cust_vip'=>$record['cust_vip'],
                    'shift'=>$record['shift'],
                    'visitdoc'=>$record['visitdoc'],
                    'sign_odds'=>VisitForm::getSignOddsStrByKey($record['sign_odds']),
                );
            }
        }
        $session = Yii::app()->session;
        $session[$this->criteriaName()] = $this->getCriteria();
        return true;
    }

    public function retrieveDataTotal()
    {
        $suffix = Yii::app()->params['envSuffix'];
        $citylist = Yii::app()->user->city_allow();
        $user = Yii::app()->user->id;
        $sql2 = "select count(a.id)
				from sal_visit a 
				inner join sal_visit_type d on a.visit_type = d.id 
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.city in ($citylist)
			";
        if (!(VisitForm::isReadAll())) {
            $x = " and (a.username='$user' or a.shift_user='$user') ";
            $sql2 .= $x;
        } else {
            $x = " and a.username is not null ";
            $sql2 .= $x;
        }
        if(!empty($this->shift)){
            if($this->shift=='Z'){//转移拜访
                $x = " and a.shift='Z' ";
            }else{//非转移拜访
                $x = " and (a.shift<>'Z' or a.shift is null) ";
            }
            $sql2 .= $x;
        }
        $clause = "";
        $static = $this->staticSearchColumns();
        $columns = $this->searchColumns();
        if (!empty($this->searchField) && (!empty($this->searchValue) || in_array($this->searchField, $static) || $this->isAdvancedSearch())) {
            if ($this->isAdvancedSearch()) {
                $clause = $this->buildSQLCriteria();
            } elseif (in_array($this->searchField, $static)) {
                $clause .= 'and '.$columns[$this->searchField];
            } else {
                $svalue = str_replace("'","\'",$this->searchValue);
                $clause .= General::getSqlConditionClause($columns[$this->searchField],$svalue);
            }
        }
        $clause .= $this->getDateRangeCondition('a.visit_dt');

        $order = "";
        if (!empty($this->orderField)) {
            switch ($this->orderField) {
                case 'city_name': $orderf = 'b.name'; break;
                case 'cust_name': $orderf = 'a.cust_name'; break;
                case 'staff': $orderf = '(case a.shift when "Z" then a.shift_user else a.username end)'; break;
                case 'visit_dt': $orderf = 'a.visit_dt'; break;
//				case 'status_dt': $orderf = 'a.status_dt'; break;
                case 'district': $orderf = 'h.name'; break;
                case 'street': $orderf = 'a.street'; break;
                case 'visit_type': $orderf = 'visit_type_name'; break;
                case 'visit_obj': $orderf = 'a.visit_obj_name'; break;
                case 'quote': $orderf = 'a.visit_info_text'; break;
                case 'visitdoc': $orderf = 'a.doc_count'; break;
                case 'cust_type': $orderf = 'g.name'; break;
                default: $orderf = $this->orderField; break;
            }
            $order .= " order by ".$orderf." ";
            if ($this->orderType=='D') $order .= "desc ";
        } else {
//			$order = Yii::app()->user->isSingleCity()
//					? " order by a.visit_dt desc, f.code"
//					: " order by a.visit_dt desc, b.name, f.code";
            $order = " order by a.visit_dt desc, a.city, a.username";
        }

        $sql = $sql2.$clause;
        $this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
        return $this->totalRow;
    }

    public function getCriteria() {
        return array(
            'sign_odds'=>$this->sign_odds,
            'shift'=>$this->shift,
            'searchField'=>$this->searchField,
            'searchValue'=>$this->searchValue,
            'orderField'=>$this->orderField,
            'orderType'=>$this->orderType,
            'noOfItem'=>$this->noOfItem,
            'pageNum'=>$this->pageNum,
            'filter'=>$this->filter,
            'dateRangeValue'=>$this->dateRangeValue,
        );
    }

/*
    public function getCriteria() {
        return array(
            'searchField'=>$this->searchField,
            'searchValue'=>$this->searchValue,
            'orderField'=>$this->orderField,
            'orderType'=>$this->orderType,
            'noOfItem'=>$this->noOfItem,
            'pageNum'=>$this->pageNum,
            'filter'=>$this->filter,
			'dateRangeValue'=>$this->dateRangeValue,
        );
    }
*/
	
	public function submitReport() {
		$session = Yii::app()->session;
		$criteria = (isset($session[$this->criteriaName()]) && !empty($session[$this->criteriaName()]))
					? $session[$this->criteriaName()]
					: '';
		$columns = $this->searchColumns();
		$static = $this->staticSearchColumns();
		$this->retrieveDataTotal();
		$pageTotal = $this->totalRow;
		$pageCount = RptVisitList::$pageCount;
		$pageNum = ceil($pageTotal/$pageCount);
		$uid = Yii::app()->user->id;
		$now = date("Y-m-d H:i:s");
		$rptdesc = Yii::t('app','Sales Visit List');
		for($i=0;$i<$pageNum;$i++){
            $rptName = $pageNum>1?$rptdesc."_".($i+1):$rptdesc;
            $data = array(
                'RPT_ID'=>'RptVisitList',
                'RPT_NAME'=>$rptName,
                'CITY'=>Yii::app()->user->city(),
                'CITY_ALLOW'=>Yii::app()->user->city_allow(),
                'LANGUAGE'=>Yii::app()->language,
                'CITY_NAME'=>Yii::app()->user->city_name(),
                'CRITERIA'=>json_encode($criteria),
                'SEARCH_COL'=>json_encode($columns),
                'STATIC_COL'=>json_encode($static),
                'PAGE_NUM'=>$i,
            );
            $format = 'EXCEL';

            $connection = Yii::app()->db;
            $transaction=$connection->beginTransaction();
            try {
                $sql = "insert into sal_queue (rpt_desc, req_dt, username, status, rpt_type)
						values(:rpt_desc, :req_dt, :username, 'P', :rpt_type)
					";
                $command=$connection->createCommand($sql);
                if (strpos($sql,':rpt_desc')!==false)
                    $command->bindParam(':rpt_desc',$rptName,PDO::PARAM_STR);
                if (strpos($sql,':req_dt')!==false)
                    $command->bindParam(':req_dt',$now,PDO::PARAM_STR);
                if (strpos($sql,':username')!==false)
                    $command->bindParam(':username',$uid,PDO::PARAM_STR);
                if (strpos($sql,':rpt_type')!==false)
                    $command->bindParam(':rpt_type',$format,PDO::PARAM_STR);
                $command->execute();
                $qid = Yii::app()->db->getLastInsertID();

                $sql = "insert into sal_queue_param (queue_id, param_field, param_value)
						values(:queue_id, :param_field, :param_value)
					";
                foreach ($data as $key=>$value) {
                    $command=$connection->createCommand($sql);
                    if (strpos($sql,':queue_id')!==false)
                        $command->bindParam(':queue_id',$qid,PDO::PARAM_INT);
                    if (strpos($sql,':param_field')!==false)
                        $command->bindParam(':param_field',$key,PDO::PARAM_STR);
                    if (strpos($sql,':param_value')!==false)
                        $command->bindParam(':param_value',$value,PDO::PARAM_STR);
                    $command->execute();
                }

                $transaction->commit();
            }
            catch(Exception $e) {
                $transaction->rollback();
                throw new CHttpException(404,'Cannot update.'.$e->getMessage());
            }
        }
	}

	public static function getShiftList(){
	    $list = array(
            ""=>Yii::t("sales","-- all --"),
            "Z"=>Yii::t("sales","shift staff"),
            "A"=>Yii::t("sales","not shift staff")
        );
	    return $list;
    }
}
