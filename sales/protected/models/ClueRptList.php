<?php

class ClueRptList extends CListPageModel
{
    public $flow_odds="";
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'clue_id'=>Yii::t('clue','clue id'),//线索id
			'clue_service_id'=>Yii::t('clue','clue service id'),//商机id
			'clue_code'=>Yii::t('clue','clue code'),//线索编号
            'clue_type'=>Yii::t('clue','clue type'),//线索类型
			'cust_name'=>Yii::t('clue','clue name'),//客户名
            'city'=>Yii::t('clue','city manger'),//城市
            'cust_class'=>Yii::t('clue','trade type'),//行业类别
            'cust_level'=>Yii::t('clue','level name'),//客户分级
            'rpt_status'=>Yii::t('clue','status'),//状态
            'total_amt'=>Yii::t('clue','rpt amt'),//预估成交金额
            'mh_remark'=>Yii::t('clue','mh remark'),//
            'lcd'=>Yii::t('clue','create date'),//
            'sales_id'=>Yii::t('clue','sales'),//
            'mh_id'=>Yii::t('clue','report mh id'),//
		);
	}

    public function rules()
    {
        return array(
            array('flow_odds, attr, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType, filter, dateRangeValue','safe',),
        );
    }

    public function getCriteria() {
        return array(
            'flow_odds'=>$this->flow_odds,
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

    public function searchColumns() {
        $suffix = Yii::app()->params['envSuffix'];
        $search = array(
            'clue_id'=>"rpt.clue_id",
            'clue_code'=>"a.clue_code",
            'cust_name'=>"a.cust_name",
            'clue_type'=>"(case rpt.clue_type when 1 then '地推' else 'KA' end)",
            'cust_class'=>'g.name',
            'cust_level'=>"h.pro_name",
            'clue_service_id'=>"rpt.clue_service_id",
            'total_amt'=>"rpt.total_amt",
            'sales_id'=>"concat(f.code,' (',f.name,')')",
            //'mh_remark'=>"rpt.mh_remark",
            'mh_id'=>"rpt.mh_id",
        );
        if (!Yii::app()->user->isSingleCity()) $search['city'] = 'b.name';
        return $search;
    }
	
	public function retrieveDataByPage($pageNum=1)
	{
        $suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select rpt.*,g.name as cust_class_name ,h.pro_name as cust_level_name ,
                a.clue_code,a.cust_name,
                b.name as city_name,concat(f.name,' (',f.code,')') as employee_name,
                (case rpt.clue_type when 1 then '地推' else 'KA' end) as clue_type_name
				from sal_clue_rpt rpt
				LEFT JOIN sal_clue a ON a.id=rpt.clue_id
				LEFT JOIN security{$suffix}.sec_city b ON rpt.city=b.code
				LEFT JOIN hr{$suffix}.hr_employee f ON rpt.sales_id=f.id
				LEFT JOIN swoper{$suffix}.swo_nature_type g ON a.cust_class=g.id
				LEFT JOIN sal_ka_level h ON a.cust_level=h.id
				where rpt.id>0 ";
		$sql2 = "select count(rpt.id)
				from sal_clue_rpt rpt
				LEFT JOIN sal_clue a ON a.id=rpt.clue_id
				LEFT JOIN security{$suffix}.sec_city b ON rpt.city=b.code
				LEFT JOIN hr{$suffix}.hr_employee f ON rpt.sales_id=f.id
				LEFT JOIN swoper{$suffix}.swo_nature_type g ON a.cust_class=g.id
				LEFT JOIN sal_ka_level h ON a.cust_level=h.id
				where rpt.id>0 ";
		$clause = "";
		if(ClueHeadList::isReadAll()){
            $citylist = Yii::app()->user->city_allow();
		    $clause.=" and rpt.city in ({$citylist}) ";
        }else{
            $staff_id = CGetName::getEmployeeIDByMy();
            $groupIdStr = CGetName::getGroupStaffIDByStaffID($staff_id);
            $groupIdStr = implode(",",$groupIdStr);
		    $clause.=" and rpt.sales_id in ({$groupIdStr})";
        }
        $static = $this->staticSearchColumns();
        $columns = $this->searchColumns();
        if (!empty($this->searchField) && (!empty($this->searchValue) || in_array($this->searchField, $static) || $this->isAdvancedSearch())) {
            if ($this->isAdvancedSearch()) {
                $clause.= $this->buildSQLCriteria();
            } elseif (in_array($this->searchField, $static)) {
                $clause .= 'and '.$columns[$this->searchField];
            } else {
                $svalue = str_replace("'","\'",$this->searchValue);
                $clause .= General::getSqlConditionClause($columns[$this->searchField],$svalue);
            }
        }
		
		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by rpt.id desc ";
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
						'clue_service_id'=>$record['clue_service_id'],
						'clue_id'=>$record['clue_id'],
                        'clue_code'=>$record['clue_code'],
                        'cust_name'=>$record['cust_name'],
                        'city'=>$record['city_name'],
                        'clue_type'=>$record['clue_type_name'],
                        'cust_class'=>$record['cust_class_name'],
                        'cust_level'=>$record['cust_level_name'],
                        'total_amt'=>$record['total_amt'],
                        'sales_id'=>$record['employee_name'],
                        'mh_remark'=>$record['mh_remark'],
                        'rpt_status'=>CGetName::getRptStatusStrByKey($record['rpt_status']),
                        'lcd'=>$record['lcd'],
					);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_ClueRptList'] = $this->getCriteria();
		return true;
	}

}
