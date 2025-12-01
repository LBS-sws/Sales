<?php

class VirtualHeadList extends CListPageModel
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
            'vir_id'=>Yii::t('clue','virtual id'),//
            'clue_id'=>Yii::t('clue','clue id'),//线索id
            'vir_code'=>Yii::t('clue','virtual code'),//虚拟合同编号
            'busine_id_text'=>Yii::t('clue','service obj'),//服务项目
            'vir_status'=>Yii::t('clue','status'),//状态
            'yewudalei'=>Yii::t('clue','yewudalei'),//业务大类
            'sign_type'=>Yii::t('clue','sign type'),//签约类型
            'city'=>Yii::t('clue','city manger'),//城市
            'year_amt'=>Yii::t('clue','total amt'),//预估成交金额
            'cont_code'=>Yii::t('clue','contract top code'),//主合同编号
            'lbs_main'=>Yii::t('clue','lbs main'),//客户主体
            'sales_id'=>Yii::t('clue','sales'),//
            'store_code'=>Yii::t('clue','store code'),//门店编号
            'store_name'=>Yii::t('clue','store name'),//门店名
            'cont_start_dt'=>Yii::t('clue','contract start date'),//合约开始时间
            'cont_end_dt'=>Yii::t('clue','contract end date'),//合约结束时间
            'clue_type'=>Yii::t('clue','client type'),//
            'mh_id'=>Yii::t('clue','report mh id'),//
            'u_id'=>Yii::t('clue','u id'),//
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
            'vir_code'=>"vir.vir_code",
            'busine_id_text'=>"vir.busine_id_text",
            'cont_code'=>"cont.cont_code",
            'sign_type'=>CGetName::getSignTypeSql("cont.sign_type"),
            'yewudalei'=>"ye.name",
            'clue_type'=>"(case cont.clue_type when 1 then '地推' else 'KA' end)",
            'store_code'=>"a.store_code",
            'store_name'=>"a.store_name",
            'year_amt'=>"vir.year_amt",
            'sales_id'=>"concat(f.code,' (',f.name,')')",
            //'mh_remark'=>"cont.mh_remark",
            'mh_id'=>"cont.mh_id",
            'u_id'=>"vir.u_id",
        );
        if (!Yii::app()->user->isSingleCity()) $search['city'] = 'b.name';
        return $search;
    }

    public function retrieveDataByPage($pageNum=1)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $sql1 = "select vir.*,
                cont.cont_code,ye.name as yewudalei,
                h.name as lbs_main_name ,
                a.store_code,a.store_name,
                b.name as city_name,concat(f.name,' (',f.code,')') as employee_name,
                (case cont.clue_type when 1 then '地推' else 'KA' end) as clue_type_name
				from sal_contract_virtual vir
				LEFT JOIN sal_contract cont ON cont.id=vir.cont_id
				LEFT JOIN sal_clue_store a ON a.id=vir.clue_store_id
				LEFT JOIN security{$suffix}.sec_city b ON a.city=b.code
				LEFT JOIN hr{$suffix}.hr_employee f ON vir.sales_id=f.id
				LEFT JOIN sal_main_lbs h ON cont.lbs_main=h.id
				LEFT JOIN sal_yewudalei ye ON vir.yewudalei=ye.id
				where vir.id>0 ";
        $sql2 = "select count(cont.id)
				from sal_contract_virtual vir
				LEFT JOIN sal_contract cont ON cont.id=vir.cont_id
				LEFT JOIN sal_clue_store a ON a.id=vir.clue_store_id
				LEFT JOIN security{$suffix}.sec_city b ON a.city=b.code
				LEFT JOIN hr{$suffix}.hr_employee f ON vir.sales_id=f.id
				LEFT JOIN sal_main_lbs h ON cont.lbs_main=h.id
				LEFT JOIN sal_yewudalei ye ON vir.yewudalei=ye.id
				where vir.id>0 ";
        $clause = "";
        if(ClueHeadList::isReadAll()){
            $citylist = Yii::app()->user->city_allow();
            $clause.=" and a.city in ({$citylist}) ";
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
        $clause .= $this->getDateRangeCondition('vir.lcd');

        $order = "";
        if (!empty($this->orderField)) {
            $order .= " order by ".$this->orderField." ";
            if ($this->orderType=='D') $order .= "desc ";
        }else{
            $order .= " order by vir.id desc ";
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
                    'vir_code'=>$record['vir_code'],
                    'busine_id_text'=>CGetName::getBusineStrByText($record['busine_id_text']),
                    'cont_code'=>$record['cont_code'],
                    'lbs_main'=>$record['lbs_main_name'],
                    'yewudalei'=>$record['yewudalei'],
                    'sign_type'=>CGetName::getSignTypeStrByKey($record['sign_type']),

                    'year_amt'=>$record['year_amt'],
                    'store_code'=>$record['store_code'],
                    'store_name'=>$record['store_name'],
                    'city'=>$record['city_name'],
                    'cont_start_dt'=>$record['cont_start_dt'],
                    'cont_end_dt'=>$record['cont_end_dt'],
                    'sales_id'=>$record['employee_name'],
                    'check_bool'=>in_array($record['vir_status'],array(10,30,40,50,60))?true:false,
                    'vir_status'=>CGetName::getContVirStatusStrByKey($record['vir_status']),
                    'lcd'=>$record['lcd'],
                    'lud'=>$record['lud'],
                    'luu'=>$record['luu'],
                    'lcu'=>$record['lcu'],
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_VirtualHeadList'] = $this->getCriteria();
        return true;
    }

}
