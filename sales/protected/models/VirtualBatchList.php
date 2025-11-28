<?php

class VirtualBatchList extends CListPageModel
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
            'pro_code'=>Yii::t('clue','pro code'),//
            'pro_type'=>Yii::t('clue','pro type'),//
            'pro_date'=>Yii::t('clue','pro date'),//
            'pro_status'=>Yii::t('clue','pro status'),//
            'create_staff'=>Yii::t('clue','update employee'),//
            'vir_code_text'=>Yii::t('clue','and virtual code'),//虚拟合同编号
            'busine_id_text'=>Yii::t('clue','and service obj'),//服务项目
            'stop_month_amt'=>Yii::t('clue','and month amt'),//涉及月金额
            'stop_year_amt'=>Yii::t('clue','and year amt'),//涉及年金额
            'city'=>Yii::t('clue','city manger'),//城市
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
            'pro_code'=>"batch.pro_code",
            'pro_type'=>"batch.pro_type",
            'pro_date'=>"batch.pro_date",
            'busine_id_text'=>"batch.busine_id_text",
            'vir_code_text'=>"batch.vir_code_text",
            'stop_month_amt'=>"batch.stop_month_amt",
            'stop_year_amt'=>"batch.stop_year_amt",
            'create_staff'=>"concat(f.code,' (',f.name,')')",
            //'mh_remark'=>"batch.mh_remark",
            'mh_id'=>"batch.mh_id",
        );
        if (!Yii::app()->user->isSingleCity()) $search['city'] = 'b.name';
        return $search;
    }

    public function retrieveDataByPage($pageNum=1)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $sql1 = "select batch.*,
                b.name as city_name,concat(f.name,' (',f.code,')') as employee_name
				from sal_virtual_batch batch
				LEFT JOIN security{$suffix}.sec_city b ON batch.city=b.code
				LEFT JOIN hr{$suffix}.hr_employee f ON batch.create_staff=f.id
				where batch.pro_status!=30 ";
        $sql2 = "select count(batch.id)
				from sal_virtual_batch batch
				LEFT JOIN security{$suffix}.sec_city b ON batch.city=b.code
				LEFT JOIN hr{$suffix}.hr_employee f ON batch.create_staff=f.id
				where batch.pro_status!=30 ";
        $clause = "";
        if(ClueHeadList::isReadAll()){
            $citylist = Yii::app()->user->city_allow();
            $clause.=" and batch.city in ({$citylist}) ";
        }else{
            $staff_id = CGetName::getEmployeeIDByMy();
            $groupIdStr = CGetName::getGroupStaffIDByStaffID($staff_id);
            $groupIdStr = implode(",",$groupIdStr);
            $clause.=" and batch.create_staff in ({$groupIdStr})";
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
            $order .= " order by batch.id desc ";
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
                    'pro_code'=>$record['pro_code'],
                    'pro_date'=>$record['pro_date'],
                    'pro_type'=>CGetName::getProTypeStrByKey($record['pro_type']),
                    'busine_id_text'=>CGetName::getBusineStrByText($record['busine_id_text']),
                    'vir_code_text'=>str_replace(",","<br/>",$record['vir_code_text']),
                    'stop_month_amt'=>$record['stop_month_amt'],
                    'stop_year_amt'=>$record['stop_year_amt'],
                    'create_staff'=>$record['employee_name'],
                    'city'=>$record['city_name'],
                    'pro_status'=>CGetName::getContTopStatusStrByKey($record['pro_status']),
                    'lcd'=>$record['lcd'],
                    'lud'=>$record['lud'],
                    'luu'=>$record['luu'],
                    'lcu'=>$record['lcu'],
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_VirtualBatchList'] = $this->getCriteria();
        return true;
    }

}
