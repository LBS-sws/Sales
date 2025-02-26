<?php

class RankList extends CListPageModel
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
            'name'=>Yii::t('sales','Staff'),
		);
	}

	public function searchColumns() {
		$search = array(
				'name'=>'d.name',
		);
		return $search;
	}

	public function retrieveDataByPage($pageNum=1,$a)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $citylist = Yii::app()->user->city_allow();
        $user = Yii::app()->user->id;
        $sql1 = "select a.* ,b.name as city_name,d.name as name
				from sal_rank	a
				left outer join security$suffix.sec_city b on a.city=b.code
				inner join  hr$suffix.hr_binding c on a.username = c.user_id		  
				inner join  hr$suffix.hr_employee d on c.employee_id = d.id  
				where a.city ='".$a['city']."' and a.season ='".$a['season']."' ";
        $sql2 = "select count(a.id)
				from sal_rank	a
				left outer join security$suffix.sec_city b on a.city=b.code	
				inner join hr$suffix.hr_binding c on a.username = c.user_id	  
				inner join  hr$suffix.hr_employee d on c.employee_id = d.id  
			 where a.city ='".$a['city']."' and a.season ='".$a['season']."' ";
        if (!(RankForm::isReadAll())) {
            $x = " and a.username='$user' order by month desc";
            $sql1 .= $x;
            $sql2 .= $x;
        } else {
            $x = " and a.username is not null order by month desc";
            $sql1 .= $x;
            $sql2 .= $x;
        }
		$this->totalRow = Yii::app()->db->createCommand($sql2)->queryScalar();
		$sql = $this->sqlWithPageCriteria($sql1, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		$list = array();
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
                $sql_rank_name="select * from sal_level where start_fraction <='".$record['now_score']."' and end_fraction >='".$record['now_score']."'";
                $rank_name= Yii::app()->db->createCommand($sql_rank_name)->queryRow();
				$this->attr[] = array(
					'id'=>$record['id'],
					'month'=>date("m", strtotime($record['month'])),
					'city'=>$record['city_name'],
                    'employee_name'=>$record['name'],
                    'season'=>$record['season'],
                    'new_rank'=>$rank_name['level'],
                    //'new_rank'=>$record['new_rank'],
                );
			}
		}
		$session = Yii::app()->session;
		$session[$this->criteriaName()] = $this->getCriteria();
		return true;
	}

}
