<?php

class CallServiceList extends CListPageModel
{
    public $flow_odds="";
    public $noOfPages = 1;
    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'call_code'=>Yii::t('clue','call code'),//
            'vir_code'=>Yii::t('clue','virtual code'),//
            'store_code'=>Yii::t('clue','store code'),//
            'store_name'=>Yii::t('clue','store name'),//
            'clue_code'=>Yii::t('clue','client code'),//线索编号
            'clue_type'=>Yii::t('clue','clue type'),//线索类型
            'cust_name'=>Yii::t('clue','customer name'),//客户名
            'cont_code'=>Yii::t('clue','contract code'),//合约编号
            'cont_type'=>Yii::t('clue','contract type'),//合约类型
            'yewudalei'=>Yii::t('clue','yewudalei'),//业务大类
            'call_status'=>Yii::t('clue','status'),//状态
            'store_num'=>Yii::t('clue','call store num'),//
            'call_text'=>Yii::t('clue','call free info'),//
            'call_amt'=>Yii::t('clue','call total amt'),//

            'busine_id_text'=>Yii::t('clue','service obj'),//服务项目
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
            //'clue_id'=>"scll.clue_id",
            'call_code'=>"scll.call_code",
            'cont_code'=>"cont.cont_code",
            'clue_type'=>"(case cont.clue_type when 1 then '地推' else 'KA' end)",
            'clue_code'=>"b.clue_code",
            'vir_code'=>"(SELECT GROUP_CONCAT(vir.vir_code) FROM sal_contract_virtual vir WHERE FIND_IN_SET(vir.id,scll.vir_ids))",
            'store_code'=>"(SELECT GROUP_CONCAT(store.store_code) FROM sal_clue_store store WHERE FIND_IN_SET(store.id,scll.store_ids))",
            'store_name'=>"(SELECT GROUP_CONCAT(store2.store_name) FROM sal_clue_store store2 WHERE FIND_IN_SET(store2.id,scll.store_ids))",
            'cust_name'=>"b.cust_name",
            'yewudalei'=>"ye.name",
            'busine_id_text'=>"scll.busine_id_text",
            'call_text'=>"scll.call_text",
            //'mh_remark'=>"cont.mh_remark",
            'mh_id'=>"scll.mh_id",
        );
        if (!Yii::app()->user->isSingleCity()) $search['city'] = "(select city.name from security{$suffix}.sec_city city where city.code=scll.city)";
        return $search;
    }

    public function retrieveDataByPage($pageNum=1)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $sql1 = "select scll.*,
                b.cust_name,b.clue_code,cont.cont_code,cont.cont_type,ye.name as yewudalei_text
				from sal_contract_call scll
				LEFT JOIN sal_clue b ON scll.clue_id=b.id
				LEFT JOIN sal_contract cont ON scll.cont_id=cont.id
				LEFT JOIN sal_yewudalei ye ON cont.yewudalei=ye.id
				where scll.id>0 ";
        $sql2 = "select count(scll.id)
				from sal_contract_call scll
				LEFT JOIN sal_clue b ON scll.clue_id=b.id
				LEFT JOIN sal_contract cont ON scll.cont_id=cont.id
				LEFT JOIN sal_yewudalei ye ON cont.yewudalei=ye.id
				where scll.id>0 ";
        $clause = "";
        if(ClueHeadList::isReadAll()){
            $citylist = Yii::app()->user->city_allow();
            $clause.=" and scll.city in ({$citylist}) ";
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

        $order = "";
        if (!empty($this->orderField)) {
            $order .= " order by ".$this->orderField." ";
            if ($this->orderType=='D') $order .= "desc ";
        }else{
            $order .= " order by scll.id desc ";
        }

        $this->pageNum = $pageNum;

        $sql = $sql2.$clause;
        $this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
        if (empty($this->totalRow)) {
            $this->totalRow = 0;
        }
        if (empty($this->noOfItem)) {
            $this->noOfPages = 1;
        } else {
            $this->noOfPages = $this->totalRow > 0 ? ceil($this->totalRow / $this->noOfItem) : 1;
        }
        if ($this->pageNum > $this->noOfPages) {
            $this->pageNum = $this->noOfPages;
        }
        if ($this->pageNum < 1) {
            $this->pageNum = 1;
        }

        $sql = $sql1.$clause.$order;
        $sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
        $records = Yii::app()->db->createCommand($sql)->queryAll();

        $list = array();
        $this->attr = array();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'call_code'=>$record['call_code'],
                    'cont_code'=>$record['cont_code'],
                    'clue_code'=>$record['clue_code'],
                    'cust_name'=>$record['cust_name'],
                    'yewudalei'=>$record['yewudalei_text'],
                    'store_num'=>$record['store_num'],
                    'call_text'=>$record['call_text'],
                    'call_amt'=>$record['call_amt'],
                    'cont_type'=>CGetName::getContactTypeStrByKey($record['cont_type']),
                    'busine_id_text'=>CGetName::getBusineStrByText($record['busine_id_text']),
                    'call_status'=>CGetName::getContTopStatusStrByKey($record['call_status']),
                    'lcd'=>$record['lcd'],
                    'lud'=>$record['lud'],
                    'luu'=>$record['luu'],
                    'lcu'=>$record['lcu'],
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_CallServiceList'] = $this->getCriteria();
        return true;
    }

}
