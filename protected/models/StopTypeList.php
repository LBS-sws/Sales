<?php

class StopTypeList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'type_name'=>Yii::t('customer','Stop Type Name'),
            'again_type'=>Yii::t('customer','again type'),
			'z_index'=>Yii::t('customer','z_index'),
			'display'=>Yii::t('customer','display'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select * 
				from sal_stop_type 
				where 1=1 
			";
		$sql2 = "select count(id)
				from sal_stop_type 
				where 1=1 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'type_name':
					$clause .= General::getSqlConditionClause('type_name',$svalue);
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
						'type_name'=>$record['type_name'],
						'again_type'=>empty($record['again_type'])?Yii::t("misc","No"):Yii::t("misc","Yes"),
						'z_index'=>$record['z_index'],
                        'display'=>$record['display']==1?Yii::t("customer","show"):Yii::t("customer","none"),
					);
			}
		}
		$session = Yii::app()->session;
		$session['stopType_c01'] = $this->getCriteria();
		return true;
	}

}
