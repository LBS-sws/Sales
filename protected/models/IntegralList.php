<?php

class IntegralList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'year'=>Yii::t('code','Year'),
			'month'=>Yii::t('code','Month'),
			'name'=>Yii::t('code','Name'),
			'city'=>Yii::t('sales','City'),
            'all_sum'=>Yii::t('sales','All Sum'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
        $citylist = Yii::app()->user->city_allow();
        $user = Yii::app()->user->id;
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select a.* ,b.name as city_name,d.name as name
				from sal_integral	a
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join   hr$suffix.hr_binding c on a.username = c.user_id		
				left outer join   hr$suffix.hr_employee d on c.employee_id = d.id  
					where a.city in ($citylist)";
		$sql2 = "select count(a.id)
				from sal_integral	a
				left outer join security$suffix.sec_city b on a.city=b.code	
				inner join hr$suffix.hr_binding c on a.username = c.user_id	
				inner join  hr$suffix.hr_employee d on c.employee_id = d.id    
			   	where a.city in ($citylist)";
        if (!(IntegralForm::isReadAll())) {
            $x = " and a.username='$user' ";
            $sql1 .= $x;
            $sql2 .= $x;
        } else {
            $x = " and a.username is not null ";
            $sql1 .= $x;
            $sql2 .= $x;
        }
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'year':
					$clause .= General::getSqlConditionClause('a.year',$svalue);
					break;
                case 'month':
                    $clause .= General::getSqlConditionClause('a.month',$svalue);
                    break;
                case 'city':
                    $clause .= General::getSqlConditionClause('b.name',$svalue);
                    break;
                case 'name':
                    $clause .= General::getSqlConditionClause('c.employee_name',$svalue);
                    break;
                case 'all_sum':
                    $clause .= General::getSqlConditionClause('a.all_sum',$svalue);
                    break;
			}
		}
		
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
                    $order .= " order by b.name ";
                    break;
                case 'name':
                    $order .= " order by c.employee_name ";
                    break;
                case 'all_sum':
                    $order .= " order by a.all_sum ";
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
						'username'=>$record['username'],
                        'month'=>$record['month'],
                        'name'=>$record['name'],
						'city'=>$record['city_name'],
                	    'all_sum'=>$record['all_sum'],
					);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_ha06'] = $this->getCriteria();
		return true;
	}

}
