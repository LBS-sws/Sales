<?php

class MarketCompanyList extends CListPageModel
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
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = " and a.status_type not in (8,10)";
        if($this->status_type!==""){//0:未分配 1：退回 5：已分配 6：跟进中
            $this->status_type = is_numeric($this->status_type)?intval($this->status_type):0;
            if($this->status_type==1){
                $whereSql.=" and a.status_type in (1,2) ";
            }else{
                $whereSql.=" and a.status_type={$this->status_type} ";
            }
        }
		$sql1 = "select a.id,a.number_no,a.z_index,a.person_phone,a.company_name,a.allot_type,a.allot_city,a.allot_employee,
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
                    'z_index'=>$record['z_index'],
                    'allot_type'=>$record['allot_type'],
                    'allot_city'=>$record['allot_city_name'],
                    'employee_name'=>$record['employee_name'],
                    'color'=>MarketFun::getTrColor($record),
                    'status_type'=>$record["status_type"],
                    'status_str'=>MarketFun::getStatusStr($record),
                    'detail'=>$infoRows?$infoRows:array(),
                );
			}
		}
		$session = Yii::app()->session;
		$session['marketCompany_c01'] = $this->getCriteria();
		return true;
	}

    //下载Excel
	public function downExcel($year){
        $rptModel = new RptMarketCompany();
        $criteria=array(
            "city"=>Yii::app()->user->city(),
            "city_allow"=>Yii::app()->user->city_allow(),
            "year"=>$year,
            "employee_id"=>$this->employee_id,
            "sign_odds"=>$this->sign_odds,
            "auto_all"=>Yii::app()->user->validFunction('CN15'),
        );
        $param['RPT_NAME'] = "KA Bot";
        $param['CITY'] = $criteria["city"];
        $param['YEAR'] = $criteria["year"];
        $param['CRITERIA'] = json_encode($criteria);
        $rptModel->criteria = $param;
        $rptModel->downExcel("KA项目({$year})");
    }

    public function countNotify(){
        //手动退回及系统退回
        $row = Yii::app()->db->createCommand()
            ->select("count(a.id)")
            ->from("sal_market a")
            ->where("a.status_type in (1,2) and a.z_index in (1,2)")->queryScalar();
        return $row;
    }
}
