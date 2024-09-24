<?php

class KAIndicatorList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'employee_name'=>Yii::t('ka','employee name'),
			'effect_date'=>Yii::t('ka','effect date'),
			'indicator_money'=>Yii::t('ka','indicator money'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
        $suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select a.*,b.code,b.name
				from sal_ka_idx a
				LEFT JOIN hr{$suffix}.hr_employee b on a.employee_id=b.id
				where a.id>0 ";
        $sql2 = "select count(a.id)
				from sal_ka_idx a
				LEFT JOIN hr{$suffix}.hr_employee b on a.employee_id=b.id
				where a.id>0 ";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'employee_name':
                    $clause = "and (b.code like '%{$svalue}%' or b.name like '%{$svalue}%') ";
					break;
				case 'effect_date':
					$clause .= General::getSqlConditionClause('a.effect_date',$svalue);
					break;
				case 'indicator_money':
					$clause .= General::getSqlConditionClause('a.indicator_money',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
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
						'employee_name'=>$record['name']." ({$record['code']})",
						'effect_date'=>General::toDate($record['effect_date']),
						'indicator_money'=>floatval($record['indicator_money']),
					);
			}
		}
		$session = Yii::app()->session;
		$session['kAIndicator_c01'] = $this->getCriteria();
		return true;
	}

}
