<?php

class DistrictList extends CListPageModel
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
			'city'=>Yii::t('sales','City'),
            'display'=>Yii::t('customer','display'),
            'z_index'=>Yii::t('customer','z_index'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $city_allow = Yii::app()->user->city_allow();
		$sql1 = "select a.*, b.name as city_name
				from sal_cust_district a, security$suffix.sec_city b
				where a.city=b.code and a.city in ($city_allow) ";
		$sql2 = "select count(a.id)
				from sal_cust_district a, security$suffix.sec_city b
				where a.city=b.code and a.city in ($city_allow) ";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'name':
					$clause .= General::getSqlConditionClause('a.name',$svalue);
					break;
				case 'city':
					$clause .= General::getSqlConditionClause('b.name',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'name':
					$order .= " order by a.name ";
					break;
				case 'city':
					$order .= " order by b.name ";
					break;
			}
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
						'name'=>$record['name'],
						'city'=>$record['city_name'],
                        'z_index'=>$record['z_index'],
                        'display'=>$record['display']==1?Yii::t("customer","show"):Yii::t("customer","none"),
					);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_hc04'] = $this->getCriteria();
		return true;
	}

}
