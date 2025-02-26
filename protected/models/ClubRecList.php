<?php

class ClubRecList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'rec_year'=>Yii::t('club','year'),
			'month_type'=>Yii::t('club','month type'),
			'code'=>Yii::t('club','staff code'),
			'name'=>Yii::t('club','staff name'),
			'city_name'=>Yii::t('club','staff city'),
			'entry_time'=>Yii::t('club','entry date'),
			'dept_name'=>Yii::t('club','dept name'),
			'rec_user'=>Yii::t('club','referees user'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select a.*,b.code,b.name,city.name as city_name,b.entry_time,f.name as dept_name
				from sal_club_rec a 
				LEFT JOIN hr{$suffix}.hr_employee b ON a.employee_id=b.id 
				LEFT JOIN hr{$suffix}.hr_dept f ON b.position=f.id 
				LEFT JOIN security{$suffix}.sec_city city ON b.city=city.code 
				where 1=1 
			";
		$sql2 = "select count(a.id) 
				from sal_club_rec a 
				LEFT JOIN hr{$suffix}.hr_employee b ON a.employee_id=b.id 
				LEFT JOIN hr{$suffix}.hr_dept f ON b.position=f.id 
				LEFT JOIN security{$suffix}.sec_city city ON b.city=city.code 
				where 1=1 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'city_name':
					$clause .= General::getSqlConditionClause('city.name',$svalue);
					break;
				case 'code':
					$clause .= General::getSqlConditionClause('b.code',$svalue);
					break;
				case 'name':
					$clause .= General::getSqlConditionClause('b.name',$svalue);
					break;
				case 'rec_user':
					$clause .= General::getSqlConditionClause('a.rec_user',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
            $order .= " order by {$this->orderField} ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by id desc ";
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
						'rec_year'=>$record['rec_year'].Yii::t("club","year"),
						'rec_user'=>$record['rec_user'],
						'code'=>$record['code'],
						'name'=>$record['name'],
						'city_name'=>$record['city_name'],
						'entry_time'=>$record['entry_time'],
						'dept_name'=>$record['dept_name'],
                        'month_type'=>$record['month_type']==1?Yii::t("club","first half year"):Yii::t("club","second half year"),
					);
			}
		}
		$session = Yii::app()->session;
		$session['clubRec_c01'] = $this->getCriteria();
		return true;
	}

}
