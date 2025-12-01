<?php

class ContHeadList extends CListPageModel
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
            'cont_code'=>Yii::t('clue','contract code'),//合约编号
            'cont_type'=>Yii::t('clue','contract type'),//合约类型
            'cont_start_dt'=>Yii::t('clue','contract start date'),//合约开始时间
            'cont_end_dt'=>Yii::t('clue','contract end date'),//合约结束时间
            'busine_id'=>Yii::t('clue','service obj'),//服务项目
            'yewudalei'=>Yii::t('clue','yewudalei'),//业务大类
            'lbs_main'=>Yii::t('clue','lbs main'),//客户主体
            'store_sum'=>Yii::t('clue','store num'),//门店数量
            'sign_type'=>Yii::t('clue','sign type'),//签约类型
            'clue_code'=>Yii::t('clue','client code'),//客户编号
            'cust_name'=>Yii::t('clue','client name'),//客户名
            'city'=>Yii::t('clue','city manger'),//城市
            'cont_status'=>Yii::t('clue','status'),//状态
            'total_amt'=>Yii::t('clue','total amt'),//预估成交金额
            'clue_type'=>Yii::t('clue','client type'),//
            'sales_id'=>Yii::t('clue','sales'),//
            'mh_id'=>Yii::t('clue','report mh id'),//
            'lcd'=>Yii::t('clue','create date'),//
            'lud'=>Yii::t('clue','update date'),//
            'lcu'=>Yii::t('clue','create staff'),//
            'luu'=>Yii::t('clue','update staff'),//
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
            'clue_id'=>"cont.clue_id",
            'cont_code'=>"cont.cont_code",
            'clue_type'=>"(case cont.clue_type when 1 then '地推' else 'KA' end)",
            'clue_code'=>"a.clue_code",
            'cust_name'=>"a.cust_name",
            'yewudalei'=>"ye.name",
            'total_amt'=>"cont.total_amt",
            'sales_id'=>"concat(f.code,' (',f.name,')')",
            //'mh_remark'=>"cont.mh_remark",
            'mh_id'=>"cont.mh_id",
        );
        if (!Yii::app()->user->isSingleCity()) $search['city'] = 'b.name';
        return $search;
    }

    public function retrieveDataByPage($pageNum=1)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $sql1 = "select cont.*,h.name as lbs_main_name ,ye.name as yewudalei,
                a.clue_code,a.cust_name,
                b.name as city_name,concat(f.name,' (',f.code,')') as employee_name,
                (case cont.clue_type when 1 then '地推' else 'KA' end) as clue_type_name
				from sal_contract cont
				LEFT JOIN sal_clue a ON a.id=cont.clue_id
				LEFT JOIN security{$suffix}.sec_city b ON cont.city=b.code
				LEFT JOIN hr{$suffix}.hr_employee f ON cont.sales_id=f.id
				LEFT JOIN sal_main_lbs h ON cont.lbs_main=h.id
				LEFT JOIN sal_yewudalei ye ON cont.yewudalei=ye.id
				where cont.id>0 ";
        $sql2 = "select count(cont.id)
				from sal_contract cont
				LEFT JOIN sal_clue a ON a.id=cont.clue_id
				LEFT JOIN security{$suffix}.sec_city b ON cont.city=b.code
				LEFT JOIN hr{$suffix}.hr_employee f ON cont.sales_id=f.id
				LEFT JOIN sal_main_lbs h ON cont.lbs_main=h.id
				LEFT JOIN sal_yewudalei ye ON cont.yewudalei=ye.id
				where cont.id>0 ";
        $clause = "";
        if(ClueHeadList::isReadAll()){
            $citylist = Yii::app()->user->city_allow();
            $clause.=" and cont.city in ({$citylist}) ";
        }else{
            $staff_id = CGetName::getEmployeeIDByMy();
            $groupIdStr = CGetName::getGroupStaffIDByStaffID($staff_id);
            $groupIdStr = implode(",",$groupIdStr);
            $clause.=" and cont.sales_id in ({$groupIdStr})";
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
        $clause .= $this->getDateRangeCondition('cont.lcd');

        $order = "";
        if (!empty($this->orderField)) {
            $order .= " order by ".$this->orderField." ";
            if ($this->orderType=='D') $order .= "desc ";
        }else{
            $order .= " order by cont.id desc ";
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
                    'cont_code'=>$record['cont_code'],
                    'cont_type'=>CGetName::getContactTypeStrByKey($record['cont_type']),
                    'busine_id_text'=>CGetName::getBusineStrByText($record['busine_id_text']),
                    'sign_type'=>CGetName::getSignTypeStrByKey($record['sign_type']),
                    'yewudalei'=>$record['yewudalei'],
                    'lbs_main'=>$record['lbs_main_name'],
                    'store_sum'=>$record['store_sum'],
                    'total_amt'=>$record['total_amt'],
                    'clue_code'=>$record['clue_code'],
                    'cust_name'=>$record['cust_name'],
                    'city'=>$record['city_name'],
                    'cont_start_dt'=>$record['cont_start_dt'],
                    'cont_end_dt'=>$record['cont_end_dt'],
                    'sales_id'=>$record['employee_name'],
                    'cont_status'=>CGetName::getContTopStatusStrByKey($record['cont_status']),
                    'lcd'=>$record['lcd'],
                    'lud'=>$record['lud'],
                    'luu'=>$record['luu'],
                    'lcu'=>$record['lcu'],
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_ContHeadList'] = $this->getCriteria();
        return true;
    }

}
