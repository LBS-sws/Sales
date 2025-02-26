<?php

class  CoefficientList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(	
			'city_name'=>Yii::t('misc','City'),
			'start_dt'=>Yii::t('sales','Start Date'),
            'name'=>Yii::t('service','Name'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$sql1 = "select * 
				from sal_coefficient_hdr 							
			";
		$sql2 = "select count(id)
				from sal_coefficient_hdr 
		  		  
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'start_dt':
					$clause .= General::getSqlConditionClause('start_dt',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {

			if ($this->orderType=='D') $order .= "desc ";
		} else {
			$order = " order by  start_dt desc";
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
					'start_dt'=>General::toDate($record['start_dt']),
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_hc06'] = $this->getCriteria();
		return true;
	}

}
