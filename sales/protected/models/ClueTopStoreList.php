<?php

class ClueTopStoreList extends ClueStoreList
{
    public $flow_odds="";
    public $clueList=array();
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
            'store_code'=>"a.store_code",
            'store_name'=>"a.store_name",
            'cust_name'=>"g.cust_name",
            'create_staff'=>" (SELECT CONCAT(staff.name,staff.code) FROM hr{$suffix}.hr_employee staff WHERE staff.id=a.create_staff)",
            'yewudalei'=>"ye.name",
            'cust_class'=>"ifnull(h.name,'')",
            'cust_person'=>"a.cust_person",
            'cust_tel'=>"a.cust_tel",
            'store_status'=>"a.store_status",
            'u_id'=>"a.u_id",
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
		$sql1 = "select a.*,b.name as city_name,g.cust_name,ye.name as yewudalei,
                  f.invoice_header,f.tax_id,f.invoice_address,h.name as cust_class_name 
				from sal_clue_store a
				LEFT JOIN sal_clue g on a.clue_id=g.id
				LEFT JOIN swoper{$suffix}.swo_nature_type h ON g.cust_class=h.id
				LEFT JOIN sal_clue_invoice f on a.invoice_id=f.id
				LEFT JOIN security{$suffix}.sec_city b ON a.city=b.code
				LEFT JOIN sal_yewudalei ye ON a.yewudalei=ye.id
				where g.del_num=0 and g.rec_type=1 ";
		$sql2 = "select count(a.id)
				from sal_clue_store a
				LEFT JOIN sal_clue g on a.clue_id=g.id
				LEFT JOIN swoper{$suffix}.swo_nature_type h ON g.cust_class=h.id
				LEFT JOIN sal_clue_invoice f on a.invoice_id=f.id
				LEFT JOIN security{$suffix}.sec_city b ON a.city=b.code
				LEFT JOIN sal_yewudalei ye ON a.yewudalei=ye.id
				where g.del_num=0 and g.rec_type=1 ";
		$clause = "";
        if(ClueHeadList::isReadAll()){
            $citylist = Yii::app()->user->city_allow();
            $clause.=" and g.city in ({$citylist}) ";
        }else{
            $clause.=" and g.rec_employee_id in ({$groupIdStr})";
        }
        $this->setClueList($groupIdStr);
        switch ($this->flow_odds){
            case 1://我负责的
                $clause.=" and g.rec_employee_id={$staff_id} ";
                break;
            case 2://下属负责的
                $clause.=" and g.rec_employee_id!={$staff_id} and g.rec_employee_id in ({$groupIdStr})";
                break;
            case 3://今日待跟进
                $nowDate = date("Y/m/d");
                $clause.=" and date_format(g.last_date,'%Y/%m/%d')='{$nowDate}' ";
                break;
            case 4://今日已跟进
                $nowDate = date("Y/m/d");
                $clause.=" and date_format(g.end_date,'%Y/%m/%d')='{$nowDate}' ";
                break;
            case 5://从未跟进的
                $clause.=" and g.end_date is null ";
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
            $order .= " order by a.id desc ";
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
                    'store_code'=>$record['store_code'],
                    'store_name'=>$record['store_name'],
                    'city'=>$record['city_name'],
                    'cust_name'=>$record['cust_name'],
                    'yewudalei'=>$record['yewudalei'],
                    'cust_class'=>$record['cust_class_name'],
                    'cust_person'=>$record['cust_person'],
                    'cust_tel'=>$record['cust_tel'],
                    'store_status'=>CGetName::getClueStoreStatusByKey($record['store_status']),
                );
			}
		}
		$session = Yii::app()->session;
		$session['criteria_ClueTopStoreList'] = $this->getCriteria();
		return true;
	}

	protected function setClueList($groupIdStr){
        $this->clueList=array();
        $whereSql = "del_num=0 and rec_type=1";
        if(ClueHeadList::isReadAll()){
            $citylist = Yii::app()->user->city_allow();
            $whereSql.=" and city in ({$citylist}) ";
        }else{
            $whereSql.=" and rec_employee_id in ({$groupIdStr})";
        }
        $rows = Yii::app()->db->createCommand()->select("id,clue_code,cust_name")
            ->from("sal_clue")
            ->where($whereSql)->order("table_type desc,lcd desc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $this->clueList[$row["id"]] = "({$row["clue_code"]}) ".$row["cust_name"];
            }
        }
    }
}
