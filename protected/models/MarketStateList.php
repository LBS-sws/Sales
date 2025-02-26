<?php

class MarketStateList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'state_name'=>Yii::t('market','project name'),
			'state_type'=>Yii::t('market','state type'),
			'state_day'=>Yii::t('market','state day'),
			'z_index'=>Yii::t('market','z index'),
			'z_display'=>Yii::t('market','z display'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$sql1 = "select *
				from sal_market_state
				where 1=1 ";
		$sql2 = "select count(id)
				from sal_market_state
				where 1=1 ";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'state_name':
					$clause .= General::getSqlConditionClause('state_name',$svalue);
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
						'state_name'=>$record['state_name'],
						'state_type'=>MarketFun::getStateNameToType($record['state_type']),
						'state_day'=>empty($record['state_day'])?Yii::t("market","not email"):$record['state_day'].Yii::t("market"," day"),
						'z_display'=>empty($record['z_display'])?Yii::t("market","no"):Yii::t("market","yes"),
						'z_index'=>$record['z_index'],
					);
			}
		}
		$session = Yii::app()->session;
		$session['marketState_c01'] = $this->getCriteria();
		return true;
	}
}
