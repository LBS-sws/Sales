<?php

class ContTypeList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'name'=>Yii::t('clue','project name'),
            'mh_code'=>Yii::t('clue','mh main code'),
			'z_display'=>Yii::t('clue','z display'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
        $city = Yii::app()->user->city();
        $city_allow = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select a.*
				from sal_cont_type a 
				where id>0 ";
		$sql2 = "select count(id)
				from sal_cont_type a 
				where id>0 ";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'name':
					$clause .= General::getSqlConditionClause('a.name',$svalue);
					break;
				case 'mh_code':
					$clause .= General::getSqlConditionClause('a.mh_code',$svalue);
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
						'mh_code'=>$record['mh_code'],
						'z_display'=>CGetName::getDisplayStrByKey($record['z_display']),
					);
			}
		}
		$session = Yii::app()->session;
		$session['contType_c01'] = $this->getCriteria();
		return true;
	}

}
