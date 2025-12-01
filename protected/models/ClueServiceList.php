<?php

class ClueServiceList extends CListPageModel
{
    public $flow_odds="";
    public $toDayNum=0;//今日待跟进数量
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'id'=>Yii::t('clue','clue service id'),//线索编号
			'clue_code'=>Yii::t('clue','clue code'),//线索编号
            'clue_type'=>Yii::t('clue','clue type'),//线索类型
			'cust_name'=>Yii::t('clue','clue name'),//客户名
            'city'=>Yii::t('clue','city manger'),//城市
            'service_status'=>Yii::t('clue','status'),//状态
            'visit_obj_text'=>Yii::t('clue','visit obj'),//拜访目的
            'predict_amt'=>Yii::t('clue','predict amt'),//预估成交金额
            'predict_date'=>Yii::t('clue','predict date'),//预估成交时间
            'sign_odds'=>Yii::t('clue','sign odds'),//签单概率
            'busine_id_text'=>Yii::t('clue','service obj'),//
            'create_staff'=>Yii::t('clue','create staff'),//
            'lcd'=>Yii::t('clue','create date'),//
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
            'id'=>"a.id",
            'clue_code'=>"a.clue_code",
            'cust_name'=>"a.cust_name",
            'clue_type'=>"(case a.clue_type when 1 then '地推' else 'KA' end)",
            'visit_obj_text'=>'service.visit_obj_text',
            'predict_amt'=>"service.predict_amt",
            'predict_date'=>"date_format(service.predict_date,'%Y/%m/%d')",
            'sign_odds'=>'service.sign_odds',
            'busine_id_text'=>'service.busine_id_text',
            'create_staff'=>"concat(f.code,' (',f.name,')')",
        );
        if (!Yii::app()->user->isSingleCity()) $search['city'] = 'b.name';
        return $search;
    }
	
	public function retrieveDataByPage($pageNum=1)
	{
        $suffix = Yii::app()->params['envSuffix'];
        $staff_id = CGetName::getEmployeeIDByMy();
        $groupIdStr = CGetName::getGroupStaffIDByStaffID($staff_id);
        $groupIdStr = implode(",",$groupIdStr);
        $nowDate = date("Y/m/d");
		$sql1 = "select service.*,
                a.clue_code,a.cust_name,a.table_type,
                b.name as city_name,concat(f.name,' (',f.code,')') as employee_name,
                (case a.clue_type when 1 then '地推' else 'KA' end) as clue_type_name
				from sal_clue_service service
				LEFT JOIN sal_clue a ON a.id=service.clue_id
				LEFT JOIN sal_clue_flow flow ON service.end_flow_id=flow.id
				LEFT JOIN security{$suffix}.sec_city b ON a.city=b.code
				LEFT JOIN hr{$suffix}.hr_employee f ON service.create_staff=f.id
				where a.del_num=0 and a.rec_type=1 ";
		$sql2 = "select count(service.id)
				from sal_clue_service service
				LEFT JOIN sal_clue a ON a.id=service.clue_id
				LEFT JOIN sal_clue_flow flow ON service.end_flow_id=flow.id
				LEFT JOIN security{$suffix}.sec_city b ON a.city=b.code
				LEFT JOIN hr{$suffix}.hr_employee f ON service.create_staff=f.id
				where a.del_num=0 and a.rec_type=1 ";
		$clause = "";
		if(ClueHeadList::isReadAll()){
            $citylist = Yii::app()->user->city_allow();
		    $clause.=" and a.city in ({$citylist}) ";
        }else{
		    $clause.=" and service.create_staff in ({$groupIdStr})";
        }
        $this->toDayNum = Yii::app()->db->createCommand()->select("count(a.id)")->from("sal_clue_service service")
            ->leftJoin("sal_clue a","a.id=service.clue_id")
            ->where(" a.del_num=0 and a.rec_type=1 {$clause} and date_format(a.last_date,'%Y/%m/%d')='{$nowDate}'")
            ->queryScalar();
        switch ($this->flow_odds){
            case 1://我负责的
                $clause.=" and a.rec_employee_id={$staff_id} ";
                break;
            case 2://下属负责的
                $clause.=" and a.rec_employee_id!={$staff_id} and a.rec_employee_id in ({$groupIdStr})";
                break;
            case 3://今日待跟进
                $clause.=" and date_format(flow.last_visit_date,'%Y/%m/%d')='{$nowDate}' ";
                break;
            case 4://今日已跟进
                $clause.=" and date_format(flow.visit_date,'%Y/%m/%d')='{$nowDate}' ";
                break;
            case 5://从未跟进的
                $clause.=" and service.end_flow_id is null ";
                break;
            case 10://待发起合同审批
                $clause.=" and service.service_status=6 ";
                break;
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
            $order .= " order by service.id desc ";
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
						'clue_id'=>$record['clue_id'],
						'table_type'=>$record['table_type'],
                        'clue_code'=>$record['clue_code'],
                        'cust_name'=>$record['cust_name'],
                        'city'=>$record['city_name'],
                        'visit_obj_text'=>$record['visit_obj_text'],
                        'clue_type'=>$record['clue_type_name'],
                        'predict_amt'=>$record['predict_amt'],
                        'predict_date'=>$record['predict_date'],
                        'busine_id_text'=>$record['busine_id_text'],
                        'sign_odds'=>CGetName::getSignOddsStrByKey($record['sign_odds']),
                        'create_staff'=>$record['employee_name'],
                        'service_status'=>CGetName::getServiceStatusStrByKey($record['service_status']),
                        'lcd'=>$record['lcd'],
					);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_ClueServiceList'] = $this->getCriteria();
		return true;
	}

}
