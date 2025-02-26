<?php

class PerformanceList extends CListPageModel
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
			'type_group'=>Yii::t('code','Type'),
			'city'=>Yii::t('sales','City'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
        $citylist = Yii::app()->user->city_allow();
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select a.* ,b.name as city_name
				from sal_performance	a
				left outer join security$suffix.sec_city b on a.city=b.code		  
					where a.city in ($citylist)";
		$sql2 = "select count(a.id)
				from sal_performance	a
				left outer join security$suffix.sec_city b on a.city=b.code		  
			   	where a.city in ($citylist)";
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
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'year':
					$order .= " order by year ";
					break;
				case 'month':
					$order .= " order by month ";
					break;
                case 'city':
                    $order .= " order by city ";
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
					);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_hc03'] = $this->getCriteria();
		return true;
	}

}
