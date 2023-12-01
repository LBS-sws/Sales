<?php

class MarketRejectList extends CListPageModel
{
    public $employee_id;
    public $employee_code;
    public $employee_name;
    public $status_type="";

    public function rules()
    {
        return array(
            array('status_type,attr, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType, filter, dateRangeValue','safe',),
        );
    }

    public function getCriteria() {
        return array(
            'status_type'=>$this->status_type,
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

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'number_no'=>Yii::t('market','number no'),
			'company_name'=>Yii::t('market','company name'),
			'person_phone'=>Yii::t('market','person phone'),
			'allot_city'=>Yii::t('market','allot city'),
			'employee_name'=>Yii::t('market','allot employee'),
			'start_date'=>Yii::t('market','market start date'),
			'end_date'=>Yii::t('market','market end date'),
			'status_type'=>Yii::t('market','status type'),
			'reject_note'=>Yii::t('market','reject note'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city();
        $whereSql = " and a.status_type=8";
		$sql1 = "select a.id,a.ready_bool,a.reject_note,a.number_no,a.z_index,a.person_phone,a.company_name,a.allot_type,a.allot_city,a.allot_employee,
                a.start_date,a.end_date,a.status_type,
                b.name as allot_city_name,
                CONCAT(h.name,' (',h.code,')') as employee_name 
				from sal_market a
				LEFT JOIN security{$suffix}.sec_city b ON a.allot_city=b.code
				LEFT JOIN hr{$suffix}.hr_employee h ON a.allot_employee=h.id
				where a.id>0 {$whereSql}";
		$sql2 = "select count(a.id)
				from sal_market a
				LEFT JOIN security{$suffix}.sec_city b ON a.allot_city=b.code
				LEFT JOIN hr{$suffix}.hr_employee h ON a.allot_employee=h.id
				where a.id>0 {$whereSql}";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'number_no':
					$clause .= General::getSqlConditionClause('a.number_no',$svalue);
					break;
				case 'company_name':
					$clause .= General::getSqlConditionClause('a.company_name',$svalue);
					break;
				case 'person_phone':
					$clause .= General::getSqlConditionClause('a.person_phone',$svalue);
					break;
				case 'allot_city':
					$clause .= General::getSqlConditionClause('b.name',$svalue);
					break;
                case 'employee_name':
                    $clause .= " and (h.name like'%{$svalue}%' or h.code like '%{$svalue}%')";
                    break;
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
                $infoRows = Yii::app()->db->createCommand()
                    ->select("a.lcu,a.lcd,a.info_date,a.info_text,b.state_name")
                    ->from("sal_market_info a")
                    ->leftJoin("sal_market_state b","a.state_id=b.id")
                    ->where("a.market_id={$record['id']}")
                    ->order("a.info_date desc")->queryAll();
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'start_date'=>General::toDate($record['start_date']),
                    'end_date'=>General::toDate($record['end_date']),
                    'number_no'=>$record['number_no'],
                    'company_name'=>$record['company_name'],
                    'person_phone'=>$record['person_phone'],
                    'allot_city'=>$record['allot_city_name'],
                    'employee_name'=>$record['employee_name'],
                    'ready_bool'=>$record['ready_bool'],
                    'color'=>empty($record['ready_bool'])?"text-yellow":"",
                    'allot_type'=>$record["allot_type"],
                    'status'=>$record["status_type"],
                    'reject_note'=>$record["reject_note"],
                    'status_str'=>MarketFun::getStatusStrForSales($record),
                    'detail'=>$infoRows?$infoRows:array(),
                );
			}
		}
		$session = Yii::app()->session;
		$session['marketReject_c01'] = $this->getCriteria();
		return true;
	}

    public function countNotify(){
        //未读
        $row = Yii::app()->db->createCommand()
            ->select("count(a.id)")
            ->from("sal_market a")
            ->where("a.status_type=8 and a.ready_bool=0")->queryScalar();
        return $row;
    }
}
