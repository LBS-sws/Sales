<?php

class AttrTypeList extends CListPageModel
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
			'display_num'=>Yii::t('sales','display'),
			'rpt_type'=>Yii::t('sales','type for'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$sql1 = "select *
				from sal_attr_type
				where 1=1 ";
		$sql2 = "select count(id)
				from sal_attr_type
				where 1=1 ";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'name':
					$clause .= General::getSqlConditionClause('name',$svalue);
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
						'name'=>$record['name'],
						'rpt_type'=>AttrTypeForm::getRPTTypeList($record['rpt_type'],true),
						'display_num'=>AttrTypeForm::getDisplayList($record['display_num'],true),
					);
			}
		}
		$session = Yii::app()->session;
		$session['attrType_c01'] = $this->getCriteria();
		return true;
	}

}
