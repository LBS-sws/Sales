<?php

class FivestepList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'rec_dt'=>Yii::t('sales','Record Date'),
			'city_name'=>Yii::t('sales','City'),
			'step'=>Yii::t('sales','5 Steps'),
			'staff_code'=>Yii::t('sales','Staff Code'),
			'staff_name'=>Yii::t('sales','Staff Name'),
			'sup_score'=>Yii::t('sales','Supervisor Score'),
			'mgr_score'=>Yii::t('sales','Manager Score'),
			'dir_score'=>Yii::t('sales','Director Score'),
			'filename'=>Yii::t('sales','File Name'),
		);
	}

	public function searchColumns() {
		$suffix = Yii::app()->params['envSuffix'];
		$stepname = FivestepForm::getStepList();
		$search = array(
			'rec_dt'=>"date_format(a.rec_dt,'%Y/%m/%d')",
			'staff_name'=>'f.name',
			'staff_code'=>'f.code',
			'step'=>"(select case a.step when '1' then '".$stepname['1']."' 
							when '2' then '".$stepname['2']."'
							when '3' then '".$stepname['3']."'
							when '4' then '".$stepname['4']."'
							when '5' then '".$stepname['5']."'
						end) ",
			'sup_score'=>'g.field_value',
			'mgr_score'=>'d.field_value',
			'dir_score'=>'e.field_value',
		);
		if (!Yii::app()->user->isSingleCity()) $search['city_name'] = 'b.name';
		return $search;
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$user = Yii::app()->user->id;
		$sql1 = "select a.*, b.name as city_name, f.name as staff_name, f.code as staff_code,f.staff_status, 
				d.field_value as mgr_score, e.field_value as dir_score, g.field_value as sup_score
				from sal_fivestep a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_fivestep_info d on a.id=d.five_id and d.field_id='mgr_score'
				left outer join sal_fivestep_info e on a.id=e.five_id and e.field_id='dir_score'
				left outer join sal_fivestep_info g on a.id=g.five_id and g.field_id='sup_score'
				where a.city in ($citylist)
			";
		$sql2 = "select count(a.id)
				from sal_fivestep a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_fivestep_info d on a.id=d.five_id and d.field_id='mgr_score'
				left outer join sal_fivestep_info e on a.id=e.five_id and e.field_id='dir_score'
				left outer join sal_fivestep_info g on a.id=g.five_id and g.field_id='sup_score'
				where a.city in ($citylist)
			";
		if (!(FivestepForm::isManagerRight() || FivestepForm::isDirectorRight() || FivestepForm::isSuperRight())) {
			$x = " and a.username='$user' ";
			$sql1 .= $x;
			$sql2 .= $x;
		}
		$clause = "";
		if (!empty($this->searchField) && (!empty($this->searchValue) || $this->isAdvancedSearch())) {
			if ($this->isAdvancedSearch()) {
				$clause = $this->buildSQLCriteria();
			} else {
				$svalue = str_replace("'","\'",$this->searchValue);
				$columns = $this->searchColumns();
				$clause .= General::getSqlConditionClause($columns[$this->searchField],$svalue);
			}
		}

		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'city_name': $orderf = 'b.name'; break;
				case 'staff_name': $orderf = 'f.name'; break;
				case 'staff_code': $orderf = 'f.code'; break;
				case 'rec_dt': $orderf = 'a.rec_dt'; break;
				case 'step': $orderf = 'a.step'; break;
				case 'mgr_score': $orderf = 'd.field_value'; break;
				case 'dir_score': $orderf = 'e.field_value'; break;
				case 'sup_score': $orderf = 'g.field_value'; break;
				default: $orderf = $this->orderField; break;
			}
			$order .= " order by ".$orderf." ";
			if ($this->orderType=='D') $order .= "desc ";
		} else {
			$order = Yii::app()->user->isSingleCity()
					? " order by a.lcd desc, f.code, a.step"
					: " order by a.lcd desc, b.name, f.code, a.step";
		}

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		
		$list = array();
		$this->attr = array();
		if (count($records) > 0) {
			$stepname = FivestepForm::getStepList();
			foreach ($records as $k=>$record) {
			    $staffName = $record['staff_status']==-1?$record['staff_name'].Yii::t("app","(Resign)"):$record['staff_name'];
				$this->attr[] = array(
					'id'=>$record['id'],
					'username'=>$record['username'],
					'staff_code'=>$record['staff_code'],
					'staff_name'=>$staffName,
					'filename'=>$record['filename'],
					'status'=>$record['status'],
//					'status'=>$this->getStatusDesc(record['status']),
					'city_name'=>$record['city_name'],
					'city'=>$record['city'],
					'rec_dt'=>General::toDate($record['rec_dt']),
					'step'=>(isset($stepname[$record['step']]) ? $stepname[$record['step']] : ''),
					'mgr_score'=>$record['mgr_score'],
					'dir_score'=>$record['dir_score'],
					'sup_score'=>$record['sup_score'],
				);
			}
		}
		$session = Yii::app()->session;
		$session[$this->criteriaName()] = $this->getCriteria();
		return true;
	}

	public function getStatusDesc($code) {
		switch ($code) {
			case 'P': $rtn = Yii::t('sales','Pending'); break;
			case 'D': $rtn = Yii::t('sales','Draft'); break;
			case 'F': $rtn = Yii::t('sales','Fail'); break; 
			case 'C': $rtn = Yii::t('sales','Success'); break;
			default: $rtn = '';
		}
		return $rtn;
	}
}
