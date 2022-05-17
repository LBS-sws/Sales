<?php

class StopSiteList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'stop_month'=>Yii::t('customer','stop month'),
			'month_money'=>Yii::t('customer','Monthly Amt'),
			'year_money'=>Yii::t('customer','Year Amt'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select * 
				from sal_stop_site 
				where 1=1 
			";
		$sql2 = "select count(id)
				from sal_stop_site 
				where 1=1 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
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
		    $str = Yii::t("customer","greater than ");
			foreach ($records as $k=>$record) {
					$this->attr[] = array(
						'id'=>$record['id'],
						'stop_month'=>$str.$record['stop_month'].Yii::t("customer"," month"),
						'month_money'=>$str.$record['month_money'],
						'year_money'=>$str.$record['year_money'],
                    );
			}
		}
		$session = Yii::app()->session;
		$session['stopSite_c01'] = $this->getCriteria();
		return true;
	}

}
