<?php

class ShiftList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(	
			'visit_dt'=>Yii::t('sales','Visit Date'),
//			'status_dt'=>Yii::t('sales','Actual Visit Date'),
			'city_name'=>Yii::t('sales','City'),
			'cust_name'=>Yii::t('sales','Customer Name'),
			'district'=>Yii::t('sales','District'),
			'street'=>Yii::t('sales','Street'),
			'visit_type'=>Yii::t('sales','Type'),
			'visit_obj'=>Yii::t('sales','Objective'),
			'cust_type'=>Yii::t('sales','Customer Type'),
			'staff'=>Yii::t('sales','Staff'),
			'cust_vip'=>Yii::t('sales','VIP').'('.Yii::t('sales','Star').')',
		);
	}

	public function searchColumns() {
		$suffix = Yii::app()->params['envSuffix'];
		$search = array(
			'cust_vip'=>"i.cust_vip='Y'",
			'visit_dt'=>"date_format(a.visit_dt,'%Y/%m/%d')",
//			'status_dt'=>"date_format(a.status_dt,'%Y/%m/%d')",
			'cust_name'=>'a.cust_name',
			'visit_type'=>'d.name',
			'visit_obj'=>'VisitObjDesc(a.visit_obj)',
			'cust_type'=>'g.name',
			'district'=>'h.name',
			'street'=>'a.street',
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
		$sql1 = "select a.*, b.name as city_name, concat(f.code,' - ',f.name) as staff,  
				d.name as visit_type_name, g.name as cust_type_name,
				h.name as district_name, VisitObjDesc(a.visit_obj) as visit_obj_name, i.cust_vip
				from sal_visit a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_visit_type d on a.visit_type = d.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.city in ($citylist) and a.shift_bool=1 and a.shift!='Z' and f.staff_status=-1 and a.shift_user is NULL 
			";
		$sql2 = "select count(a.id)
				from sal_visit a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_visit_type d on a.visit_type = d.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.city in ($citylist) and a.shift_bool=1 and a.shift!='Z' and f.staff_status=-1 and a.shift_user is NULL 
			";
		if (!(VisitForm::isReadAll())) {
			$x = " and a.username='$user' ";
			$sql1 .= $x;
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

		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'city_name': $orderf = 'b.name'; break;
				case 'cust_name': $orderf = 'a.cust_name'; break;
				case 'staff': $orderf = 'staff'; break;
				case 'visit_dt': $orderf = 'a.visit_dt'; break;
//				case 'status_dt': $orderf = 'a.status_dt'; break;
				case 'district': $orderf = 'h.name'; break;
				case 'street': $orderf = 'a.street'; break;
				case 'visit_type': $orderf = 'd.name'; break;
				case 'visit_obj': $orderf = 'visit_obj_name'; break;
				case 'cust_type': $orderf = 'g.name'; break;
				default: $orderf = $this->orderField; break;
			}
			$order .= " order by ".$orderf." ";
			if ($this->orderType=='D') $order .= "desc ";
		} else {
			$order = Yii::app()->user->isSingleCity()
					? " order by a.visit_dt desc, f.code"
					: " order by a.visit_dt desc, b.name, f.code";
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
				$this->attr[] = array(
					'id'=>$record['id'],
					'username'=>$record['username'],
					'visit_dt'=>General::toDate($record['visit_dt']),
//					'status_dt'=>($record['status']=='Y' ? General::toDate($record['status_dt']) : ''),
					'status'=>$record['status'],
					'city_name'=>$record['city_name'],
					'city'=>$record['city'],
					'staff'=>$record['staff'],
					'district'=>$record['district_name'],
					'street'=>$record['street'],
					'visit_type'=>$record['visit_type_name'],
					'visit_obj'=>$record['visit_obj_name'],
					'cust_type'=>$record['cust_type_name'],
					'cust_name'=>$record['cust_name'],
					'cust_vip'=>$record['cust_vip'],
                    'shift'=>'Y',
				);
			}
		}
		$session = Yii::app()->session;
		$session[$this->criteriaName()] = $this->getCriteria();
		return true;
	}

    public function saleman(){
        $suffix = Yii::app()->params['envSuffix'];
        $city=Yii::app()->user->city();
        $sql="select code,name,id from hr$suffix.hr_employee WHERE  position in (SELECT id FROM hr$suffix.hr_dept where dept_class='sales') AND staff_status = 0 AND city='".$city."'";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
//        $records=array_merge($records,$name);
        //print_r($name);
        return $records;
    }
}
