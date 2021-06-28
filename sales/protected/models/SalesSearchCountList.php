<?php

class SalesSearchCountList extends CListPageModel
{
    public $year;
    public $month;
    public $sales;
    public $city;

	public function attributeLabels()
	{
		return array(
            'city'=>Yii::t('misc','City'),
            'employee_code'=>Yii::t('sales','Staff Code'),
            'employee_name'=>Yii::t('sales','Staff Name'),
            'search_date'=>Yii::t('sales','sales search date'),
            'search_num'=>Yii::t('sales','sales search count'),
		);
	}

    public function rules()
    {	$rtn1 = parent::rules();
        $rtn2 =  array(
            array('year,month,sales,city','safe',),
        );
        return array_merge($rtn1, $rtn2);
    }

	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$sql1 = "select a.*,b.name from sal_search a 
            LEFT JOIN security$suffix.sec_city b on a.city=b.code
			where a.city in ($city) 
			";
		$sql2 = "select count(*) from sal_search a 
            LEFT JOIN security$suffix.sec_city b on a.city=b.code
			where a.city in ($city) 
			";
		$clause = "";
        if (!empty($this->searchField) && !empty($this->searchValue)) {
            $svalue = str_replace("'","\'",$this->searchValue);
            switch ($this->searchField) {
                case 'employee_code':
                    $clause .= General::getSqlConditionClause('a.employee_code',$svalue);
                    break;
                case 'employee_name':
                    $clause .= General::getSqlConditionClause('a.employee_name',$svalue);
                    break;
                case 'city':
                    $clause .= General::getSqlConditionClause('b.name',$svalue);
                    break;
                case 'search_date':
                    $svalue = date("Y-m-d",strtotime($svalue));
                    $clause .= General::getSqlConditionClause('a.search_date',$svalue);
                    break;
            }
        }
        $clause .= $this->getDateRangeCondition('a.lcu');

		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
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
					'employee_code'=>$record['employee_code'],
					'employee_name'=>$record['employee_name'],
					'city'=>$record['name'],
					'search_date'=>$record['search_date'],
					'search_num'=>$record['search_num'],
				);
			}
		}
		$session = Yii::app()->session;
		$session[$this->criteraName()] = $this->getCriteria();
		return true;
	}

	//单个销售的查询统计
	public function onlySales(){
        $city = Yii::app()->user->city_allow();
        $data = array('labelX'=>array(0),'labelY'=>array(0));
	    if(empty($this->year)){
	        $this->year = date("Y");
        }
	    if(empty($this->month)){
	        $this->month = intval(date("m"));
        }
        $day = $this->year."-".$this->month."-01";
        $day = date("d",strtotime("$day +1 months -1 day"));
        $rows = Yii::app()->db->createCommand()->select("search_num,search_date")
            ->from("sal_search")
            ->where("city in ($city) and DATE_FORMAT(search_date,'%Y-%c')=:date and employee_id=:employee_id",
                array(":date"=>$this->year."-".$this->month,":employee_id"=>$this->sales)
            )->order("search_date asc")->queryAll();
        $rows = $this->resetRows($rows,'search_date');
        for($i = 1;$i<=$day;$i++){
            $data["labelX"][] = $i;
            $data["labelY"][] = key_exists($i,$rows)?$rows[$i]:0;
        }
        $session = Yii::app()->session;
        $session["onlySales_01"] = $this->getCriteria();
        return $data;
    }

	//单个销售的查询统计
	public function allCity(){
        $city = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
        $data = array('labelX'=>array(0),'labelY'=>array(0));
	    if(empty($this->year)){
	        $this->year = date("Y");
        }
	    if(empty($this->month)){
	        $this->month = intval(date("m"));
        }
        $cityRows = Yii::app()->db->createCommand()->select("b.code,b.name")
            ->from("security$suffix.sec_city b")
            ->where("b.code in ($city)")
            ->queryAll();
        $day = $this->year."-".$this->month."-01";
        $day = date("d",strtotime("$day +1 months -1 day"));
        $rows = Yii::app()->db->createCommand()->select("city,sum(search_num) as search_num")
            ->from("sal_search")
            ->where("city in ($city) and DATE_FORMAT(search_date,'%Y-%c')=:date",
                array(":date"=>$this->year."-".$this->month)
            )->group("city")->queryAll();
        $rows = $this->resetRows($rows,'city');
        foreach ($cityRows as $city){
            $key = $city['code'];
            $data["labelX"][] = $city['name'];
            $data["labelY"][] = key_exists($key,$rows)?$rows[$key]["search_num"]:0;
        }
        $session = Yii::app()->session;
        $session["allCity_01"] = $this->getCriteria();
        return $data;
    }

	//地区销售的查询统计
	public function allSales(){
        $systemId = Yii::app()->params['systemId'];
        $suffix = Yii::app()->params['envSuffix'];
        $data = array('labelX'=>array(0),'labelY'=>array(0));
	    if(empty($this->year)){
	        $this->year = date("Y");
        }
	    if(empty($this->month)){
	        $this->month = intval(date("m"));
        }
        $staffRows = Yii::app()->db->createCommand()->select("b.employee_id,c.name")
            ->from("security$suffix.sec_user_access a")
            ->leftJoin("hr$suffix.hr_binding b","a.username = b.user_id")
            ->leftJoin("hr$suffix.hr_employee c","b.employee_id = c.id")
            ->where("a.system_id='$systemId' and c.city=:city and (a.a_read_only like '%HK06%' or a.a_read_write like '%HK06%') and b.employee_id is not null",array(":city"=>$this->city))
            ->queryAll();
        $rows = Yii::app()->db->createCommand()
            ->select("employee_id,employee_code,employee_name,sum(search_num) as search_num")
            ->from("sal_search")
            ->where("city=:city and DATE_FORMAT(search_date,'%Y-%c')=:date",
                array(":date"=>$this->year."-".$this->month,":city"=>$this->city)
            )->group("employee_id,employee_code,employee_name")->queryAll();
        $rows = $this->resetRows($rows,"employee_id");
        if($staffRows){
            foreach ($staffRows as $staff){
                $key = intval($staff['employee_id']);
                if(key_exists($key,$rows)){
                    $data["labelX"][] = $rows[$key]["employee_name"];
                    $data["labelY"][] = $rows[$key]["search_num"];
                }else{
                    $data["labelX"][] = $staff['name'];
                    $data["labelY"][] = 0;
                }
            }
        }
        $session = Yii::app()->session;
        $session["allSales_01"] = $this->getCriteria();
        return $data;
    }

    //数组转换
    private function resetRows($rows,$str=''){
	    $data = array();
	    if($rows){
            switch ($str){
                case "search_date":
                    foreach ($rows as $row){
                        $key = date("d",strtotime($row["search_date"]));
                        $key = intval($key);
                        $data[$key] = $row["search_num"];
                    }
                    break;
                default:
                    foreach ($rows as $row){
                        $key = $row[$str];
                        if(is_numeric($key)){
                            $key = intval($key);
                        }
                        $data[$key] = $row;
                    }
            }
        }
        return $data;
    }

	private function criteraName(){
        return 'criteria_'.get_class($this);
    }

    public static function getYearAll(){
	    $year = date("Y");
	    $arr = array();
	    for($i=$year-4;$i<=$year;$i++){
            $arr[$i] = $i.Yii::t("code","Year");
        }
        return $arr;
    }

    public static function getMonthAll(){
	    $arr = array();
	    for($i=1;$i<=12;$i++){
            $arr[$i] = $i.Yii::t("code","Month");
        }
        return $arr;
    }

    public static function getSalesAll(){
        $systemId = Yii::app()->params['systemId'];
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
	    $arr = array(""=>"--".Yii::t("dialog","Please check the assigned person")."--");

        $rows = Yii::app()->db->createCommand()->select("b.employee_id,c.code,c.name")
            ->from("security$suffix.sec_user_access a")
            ->leftJoin("hr$suffix.hr_binding b","a.username = b.user_id")
            ->leftJoin("hr$suffix.hr_employee c","b.employee_id = c.id")
            ->where("a.system_id='$systemId' and c.city in ($city) and (a.a_read_only like '%HK06%' or a.a_read_write like '%HK06%') and b.employee_id is not null")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $arr[$row["employee_id"]]="(".$row["code"].")".$row["name"];
            }
        }
        return $arr;
    }

    public static function getCityAll(){
        $city = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
	    $arr = array(""=>"--".Yii::t("misc","City")."--");
        $rows = Yii::app()->db->createCommand()->select("b.code,b.name")
            ->from("security$suffix.sec_city b")
            ->where("b.code in ($city)")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $arr[$row["code"]]=$row["name"];
            }
        }
        return $arr;
    }

    public function getCriteria() {
        return array(
            'year'=>$this->year,
            'month'=>$this->month,
            'city'=>$this->city,
            'sales'=>$this->sales,
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
}
