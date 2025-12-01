<?php

class ServiceTypeList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'id_char'=>Yii::t('clue','id char'),
            'z_index'=>"层级",
            'service_type'=>"服务类型",
            'type_str'=>"金额类型",
            'class_id'=>"分类汇总",
			'name'=>Yii::t('clue','Project Name'),
            'u_code'=>Yii::t('clue','u main code'),
			'z_display'=>Yii::t('clue','z display'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
        $city = Yii::app()->user->city();
        $city_allow = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select a.*,b.set_name
				from sal_service_type a 
				LEFT JOIN sal_set_menu b ON a.class_id=b.set_id and b.set_type='serviceTypeClass'
				where a.id>0 ";
		$sql2 = "select count(a.id)
				from sal_service_type a 
				LEFT JOIN sal_set_menu b ON a.class_id=b.set_id and b.set_type='serviceTypeClass'
				where a.id>0 ";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'id_char':
					$clause .= General::getSqlConditionClause('a.id_char',$svalue);
					break;
				case 'name':
					$clause .= General::getSqlConditionClause('a.name',$svalue);
					break;
				case 'u_code':
					$clause .= General::getSqlConditionClause('a.u_code',$svalue);
					break;
				case 'class_id':
					$clause .= General::getSqlConditionClause('b.set_name',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by a.id asc ";
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
						'id_char'=>$record['id_char'],
						'name'=>$record['name'],
						'u_code'=>$record['u_code'],
						'z_index'=>$record['z_index'],
						'class_id'=>$record['set_name'],
						'z_display'=>CGetName::getDisplayStrByKey($record['z_display']),
					);
			}
		}
		$session = Yii::app()->session;
		$session['serviceType_c01'] = $this->getCriteria();
		return true;
	}

}
