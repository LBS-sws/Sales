<?php

class SetMenuList extends CListPageModel
{
    public $set_type;

    public function rules()
    {
        return array(
            array('set_type, attr, pageNum, noOfItem, totalRow,city, searchField, searchValue, orderField, orderType, filter, dateRangeValue','safe',),
        );
    }
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'set_id'=>"配置id",
            'set_name'=>Yii::t('clue','Project Name'),
            'set_type'=>"配置类型",
            'u_code'=>Yii::t('clue','u id'),
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
				from sal_set_menu a 
				where id>0 ";
		$sql2 = "select count(id)
				from sal_set_menu a 
				where id>0 ";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'set_id':
					$clause .= General::getSqlConditionClause('a.name',$svalue);
					break;
				case 'name':
					$clause .= General::getSqlConditionClause('a.name',$svalue);
					break;
				case 'set_type':
					$clause .= General::getSqlConditionClause('a.set_type',$svalue);
					break;
			}
		}
		if(!empty($this->set_type)){
            $set_type = str_replace("'","\'",$this->set_type);
		    $clause.=" and set_type='{$set_type}'";
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
						'set_id'=>$record['set_id'],
						'set_name'=>$record['set_name'],
						'set_type'=>CGetName::getSetMenuStrByKey($record['set_type']),
						'mh_code'=>$record['mh_code'],
						'u_code'=>$record['u_code'],
						'z_display'=>CGetName::getDisplayStrByKey($record['z_display']),
					);
			}
		}
		$session = Yii::app()->session;
		$session['setMenu_c01'] = $this->getCriteria();
		return true;
	}


    public function getCriteria() {
        return array(
            'set_type'=>$this->set_type,
            'searchField'=>$this->searchField,
            'searchValue'=>$this->searchValue,
            'orderField'=>$this->orderField,
            'orderType'=>$this->orderType,
            'noOfItem'=>$this->noOfItem,
            'pageNum'=>$this->pageNum,
            'filter'=>$this->filter,
            'dateRangeValue'=>$this->dateRangeValue,
        );
    }
}
