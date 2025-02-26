<?php

class TargetList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(
            'year'=>Yii::t('code','Year'),
            'month'=>Yii::t('code','Month'),
            'city'=>Yii::t('sales','City'),
            'employee_name'=>Yii::t('sales','Employee_name'),
            'sale_day'=>Yii::t('code','Sale_day'),
		);
	}

	public function searchColumns() {
		$search = array(
				'year'=>"a.year",
				'month'=>'a.month',
				'city'=>'b.name',
				'name'=>'c.employee_name',
		);
		return $search;
	}

	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $citylist = Yii::app()->user->city_allow();
        $user = Yii::app()->user->id;
        $sql1 = "select a.* ,b.name as city_name,d.name as name
				from sal_integral	a
				left outer join security$suffix.sec_city b on a.city=b.code
				inner join  hr$suffix.hr_binding c on a.username = c.user_id		  
				inner join  hr$suffix.hr_employee d on c.employee_id = d.id  
					where a.city in ($citylist)";
        $sql2 = "select count(a.id)
				from sal_integral	a
				left outer join security$suffix.sec_city b on a.city=b.code	
				inner join hr$suffix.hr_binding c on a.username = c.user_id	  
				inner join  hr$suffix.hr_employee d on c.employee_id = d.id  
			   	where a.city in ($citylist)";
		$clause = "";
        if (!(IntegralForm::isReadAll())) {
            $x = " and a.username='$user' ";
            $sql1 .= $x;
            $sql2 .= $x;
        } else {
            $x = " and a.username is not null ";
            $sql1 .= $x;
            $sql2 .= $x;
        }
		if (!empty($this->searchField) && (!empty($this->searchValue) || $this->isAdvancedSearch())) {
			if ($this->isAdvancedSearch()) {
				$clause = $this->buildSQLCriteria();
			} else {
				$svalue = str_replace("'","\'",$this->searchValue);
				$columns = $this->searchColumns();
				$clause .= General::getSqlConditionClause($columns[$this->searchField],$svalue);
			}
		}
		$clause .= $this->getDateRangeCondition('a.lcd');

		$order = "";
        if (!empty($this->orderField)) {
            switch ($this->orderField) {
                case 'year':
                    $order .= " order by a.year ";
                    break;
                case 'month':
                    $order .= " order by a.month ";
                    break;
                case 'city':
                    $order .= " order by  b.name ";
                    break;
                case 'name':
                    $order .= " order by d.name ";
                    break;
            }
            if ($this->orderType=='D') $order .= "desc ";
        } else {
            $order = " order by year desc, month desc";
        }

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();

		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();

		$list = array();
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$this->attr[] = array(
					'id'=>$record['id'],
					'year'=>$record['year'],
					'month'=>$record['month'],
					'city'=>$record['city_name'],
                    'employee_name'=>$record['name'],
                    'sale_day'=>$record['sale_day'],

				);
			}
		}
		$session = Yii::app()->session;
		$session[$this->criteriaName()] = $this->getCriteria();
		return true;
	}

}
