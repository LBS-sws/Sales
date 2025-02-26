<?php

class RankHistoryList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(
            'year'=>Yii::t('code','Year'),
            'month'=>Yii::t('code','Month'),
            'city'=>Yii::t('sales','City'),
            'employee_name'=>Yii::t('sales','Employee_name'),
            'season'=>Yii::t('sales','Season'),
            'rank'=>Yii::t('sales','Rank'),
		);
	}

	public function searchColumns() {
		$search = array(
				'city'=>'c.name',
				'employee_name'=>'a.employee_name',
		);
		return $search;
	}

	public function retrieveDataByPage($pageNum=1,$a)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $citylist = Yii::app()->user->city_allow();
        $user = Yii::app()->user->id;
        $sql1 = "select a.id,a.employee_name,c.name  from  hr$suffix.hr_binding a
			inner join  hr$suffix.hr_employee b on a.employee_id = b.id   
			left outer join security$suffix.sec_city c on a.city=c.code	
			inner join  sal_rank d on a.user_id = d.username
			where a.city ='".$a['city']."' ";
        $sql2 = "select count(*) count from (select count(*)
				 from  hr$suffix.hr_binding a
			inner join  hr$suffix.hr_employee b on a.employee_id = b.id   
			left outer join security$suffix.sec_city c on a.city=c.code	
			inner join  sal_rank d on a.user_id = d.username
			 where a.city ='".$a['city']."' ";
        if (!(RankForm::isReadAll())) {
            $x = " and d.username='$user' ";
            $sql1 .= $x;
            $sql2 .= $x;
        } else {
            $x = " and d.username is not null ";
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
                case 'city':
                    $order .= " order by  c.name ";
                    break;
                case 'name':
                    $order .= " order by a.employee_name";
                    break;
            }
            if ($this->orderType=='D') $order .= "desc ";
        } else {
            $order = " order by a.employee_name desc";
        }

		$sql = $sql2.$clause."group by employee_name,a.id,c.name) temp ";;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();

		$sql = $sql1.$clause." group by  a.employee_name,a.id,c.name".$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();

		$list = array();
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {

				$this->attr[] = array(
					'id'=>$record['id'],
				//	'month'=>date("m", strtotime($record['month'])),
					'city'=>$record['name'],
                    'employee_name'=>$record['employee_name'],



                );
			}
		}
		$session = Yii::app()->session;
		$session[$this->criteriaName()] = $this->getCriteria();
		return true;
	}

}
