<?php

class CusttypeList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'name'=>Yii::t('code','Description'),
			'rpt_type'=>Yii::t('code','Report Category'),
			'type_group'=>Yii::t('code','Type'),
			'rpt_u'=>"派单系统对应id",
			'z_display'=>"是否显示",
			'city'=>Yii::t('sales','City'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select a.*, b.name as city_name
				from sal_cust_type a
				left outer join security$suffix.sec_city b on a.city=b.code
				where 1=1 ";
		$sql2 = "select count(a.id)
				from sal_cust_type a
				left outer join security$suffix.sec_city b on a.city=b.code
				where 1=1 ";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'name':
					$clause .= General::getSqlConditionClause('a.name',$svalue);
					break;
				case 'type_group':
					$t = "(select case a.type_group
							when 1 then '".Yii::t('sales','Catering')."' 
							when 2 then '".Yii::t('sales','Non-catering')."' 
							else 'BLANK'
						end)";
					$clause .= General::getSqlConditionClause($t,$svalue);
					break;
				case 'city':
					$t = "(select case a.city when '99999' then '".Yii::t('sales','All')."' 
							else b.name
						end)";
					$clause .= General::getSqlConditionClause($t,$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'name':
					$order .= " order by a.name ";
					break;
				case 'rpt_u':
					$order .= " order by a.rpt_u ";
					break;
				case 'z_display':
					$order .= " order by a.z_display ";
					break;
				case 'city':
					$order .= " order by b.name ";
					break;
			}
			if ($this->orderType=='D') $order .= "desc ";
		} else {
			$order .= " order by b.name, a.name ";
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
					$city = $record['city']=='99999' ? Yii::t('sales','All') : $record['city_name'];
					$this->attr[] = array(
						'id'=>$record['id'],
						'name'=>$record['name'],
						'rpt_u'=>$record['rpt_u'],
						'z_display'=>$record['z_display']=="1"?"是":"否",
						'type_group'=>($record['type_group']==1
										? Yii::t('sales','Catering') 
										: ($record['type_group']==2 ? Yii::t('sales','Non-catering') : '')
									),
						'city'=>$city,
					);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_hc03'] = $this->getCriteria();
		return true;
	}

}
