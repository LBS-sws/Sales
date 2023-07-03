<?php

class KABotList extends CListPageModel
{
    public $employee_id;
    public $employee_code;
    public $employee_name;
    public $sign_odds="";

    public function rules()
    {
        return array(
            array('sign_odds,attr, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType, filter, dateRangeValue','safe',),
        );
    }

    public function getCriteria() {
        return array(
            'sign_odds'=>$this->sign_odds,
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
			'apply_date'=>Yii::t('ka','apply date'),
			'customer_no'=>Yii::t('ka','customer no'),
			'customer_name'=>Yii::t('ka','customer name'),
			'contact_user'=>Yii::t('ka','contact user'),
			'source_id'=>Yii::t('ka','source name'),
			'class_id'=>Yii::t('ka','class name'),
			'kam_id'=>Yii::t('ka','KAM'),
			'link_id'=>Yii::t('ka','link name'),
            'follow_date'=>Yii::t('ka','info date'),
            'sign_odds'=>Yii::t('ka','sign odds'),
            'available_date'=>Yii::t('ka','available date'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
        $suffix = Yii::app()->params['envSuffix'];
        $city_allow = Yii::app()->user->city_allow();
        if(Yii::app()->user->validFunction('CN15')){
            //$whereSql = " and (a.kam_id='{$this->employee_id}' or a.support_user='{$this->employee_id}' or h.city in ({$city_allow}))";
            $whereSql = "";//2023/06/16 改為可以看的所有記錄
        }else{
            $whereSql = " and (a.kam_id='{$this->employee_id}' or a.support_user='{$this->employee_id}')";
        }
        if($this->sign_odds!==""){
            if(empty($this->orderField)){
                $this->orderField = "expr";
            }
            $this->sign_odds = is_numeric($this->sign_odds)?intval($this->sign_odds):0;
            $whereSql.=" and a.sign_odds={$this->sign_odds} ";
        }
		$sql1 = "select a.id,a.available_date,a.sign_odds,a.follow_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,
                b.pro_name as class_name,
                f.pro_name as source_name,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name, 
                CONCAT(h.name,' (',h.code,')') as kam_name 
				from sal_ka_bot a
				LEFT JOIN sal_ka_class b ON a.class_id=b.id
				LEFT JOIN sal_ka_source f ON a.source_id=f.id
				LEFT JOIN sal_ka_link g ON a.link_id=g.id
				LEFT JOIN hr{$suffix}.hr_employee h ON a.kam_id=h.id
				where a.id>0 {$whereSql}";
		$sql2 = "select count(a.id)
				from sal_ka_bot a
				LEFT JOIN sal_ka_class b ON a.class_id=b.id
				LEFT JOIN sal_ka_source f ON a.source_id=f.id
				LEFT JOIN sal_ka_link g ON a.link_id=g.id
				LEFT JOIN hr{$suffix}.hr_employee h ON a.kam_id=h.id
				where a.id>0 {$whereSql}";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'customer_no':
					$clause .= General::getSqlConditionClause('a.customer_no',$svalue);
					break;
				case 'customer_name':
					$clause .= General::getSqlConditionClause('a.customer_name',$svalue);
					break;
				case 'contact_user':
					$clause .= General::getSqlConditionClause('a.contact_user',$svalue);
					break;
				case 'available_date':
					$clause .= General::getSqlConditionClause('a.available_date',$svalue);
					break;
				case 'class_id':
					$clause .= General::getSqlConditionClause('b.pro_name',$svalue);
					break;
				case 'source_id':
					$clause .= General::getSqlConditionClause('f.pro_name',$svalue);
					break;
				case 'link_id':
					$clause .= General::getSqlConditionClause("CONCAT('(',g.rate_num,') ',g.pro_name)",$svalue);
					break;
				case 'kam_id':
					$clause .= General::getSqlConditionClause("CONCAT(h.name,' (',h.code,')')",$svalue);
					break;
				case 'sign_odds':
                    $svalue = is_numeric($svalue)?$svalue:0;
                    $clause .= "and IFNULL(a.sign_odds,0)>={$svalue} ";
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
		    if($this->orderField==="expr"){ //特殊排序
                $beforeDate = date("Y-m-d",strtotime("-90 days"));
                $order .= " order by if(a.follow_date>='$beforeDate','$beforeDate',a.follow_date) asc,a.follow_date desc ";
            }else{
                $order .= " order by ".$this->orderField." ";
                if ($this->orderType=='D') $order .= "desc ";
            }
		}else{
            $order .= " order by a.follow_date desc ";
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
			    $color = $this->getTdColor($record);
                $sql = "select info_date,info_text from sal_ka_bot_info where bot_id=".$record['id']." order by info_date desc";
                $infoRows = Yii::app()->db->createCommand($sql)->queryAll();
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'apply_date'=>General::toDate($record['apply_date']),
                    'available_date'=>General::toDate($record['available_date']),
                    'customer_no'=>$record['customer_no'],
                    'customer_name'=>$record['customer_name'],
                    'contact_user'=>$record['contact_user'],
                    'class_id'=>$record['class_name'],
                    'source_id'=>$record['source_name'],
                    'link_id'=>$record['link_name'],
                    'kam_id'=>$record['kam_name'],
                    'color'=>$color,
                    'sign_odds'=>KABotForm::getSignOddsListForId($record['sign_odds'],true),
                    'follow_date'=>empty($record['follow_date'])?"":General::toDate($record['follow_date']),
                    'detail'=>$infoRows?$infoRows:array(),
                );
			}
		}
		$session = Yii::app()->session;
		$session['kABot_c01'] = $this->getCriteria();
		return true;
	}

	private function getTdColor($row){
	    if(empty($row["available_date"])){
	        return "";
        }
        $sign_odds = empty($row["sign_odds"])?0:floatval($row["sign_odds"]);
	    if($sign_odds>80&&$sign_odds<100&&strtotime($row["available_date"])<=strtotime(date("Y/m/d"))){
	        return "text-red";
        }else{
	        return "";
        }
    }

	public function downExcel($year){
        $rptModel = new RptKABot();
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
}
