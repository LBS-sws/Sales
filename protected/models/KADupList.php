<?php

class KADupList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'dup_name'=>Yii::t('ka','dup name'),
			'dup_value'=>Yii::t('ka','dup value'),
			'z_index'=>Yii::t('ka','z index'),
			'z_display'=>Yii::t('ka','z display'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$sql1 = "select *
				from sal_ka_dup
				where z_display=1 ";
		$sql2 = "select count(id)
				from sal_ka_dup
				where z_display=1 ";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'dup_name':
					$clause .= General::getSqlConditionClause('dup_name',$svalue);
					break;
				case 'dup_value':
					$clause .= General::getSqlConditionClause('dup_value',$svalue);
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
						'dup_name'=>$record['dup_name'],
						'dup_value'=>$record['dup_value'],
						'z_display'=>empty($record['z_display'])?Yii::t("ka","no"):Yii::t("ka","yes"),
						'z_index'=>$record['z_index'],
					);
			}
		}
		$session = Yii::app()->session;
		$session['kADup_c01'] = $this->getCriteria();
		return true;
	}

}
