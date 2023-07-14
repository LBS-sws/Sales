<?php

class ComparisonForm extends CFormModel
{
	/* User Fields */
	public $week_start_date;
	public $start_date;
	public $end_date;
	public $month_type=1;
	public $day_num=0;
	public $comparison_year;
    public $month_start_date;
    public $month_end_date;

	public $data=array();
	public $defaultTable="";

	public $th_sum=2;//所有th的个数

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'start_date'=>Yii::t('summary','start date'),
            'end_date'=>Yii::t('summary','end date'),
            'day_num'=>Yii::t('summary','day num'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('start_date,end_date','safe'),
			array('start_date,end_date','required'),
		);
	}

    public static function setDayNum($startDate,$endDate,&$dayNum){
        $startDate = strtotime($startDate);
        $endDate = strtotime($endDate);
        $timer = 0;
        if($endDate>=$startDate){
            $timer = ($endDate-$startDate)/86400;
            $timer++;//需要算上起始的一天
        }
        $dayNum = $timer;
    }

    public static function resetNetOrGross($num,$day,$type=3){
        switch ($type){
            case 1://季度
                return $num+$num*2*0.8;
            case 2://月度
                return $num;
            case 3://日期
                $num = ($num*12/365)*$day;
                $num = round($num,2);
                return $num;
        }
        return $type;
    }

    public function retrieveData() {
        $data = array();
        $suffix = Yii::app()->params['envSuffix'];
        $this->start_date = empty($this->start_date)?date("Y/01/01"):$this->start_date;
        $this->end_date = empty($this->end_date)?date("Y/m/t"):$this->end_date;
        $this->comparison_year = date("Y",strtotime($this->start_date));
        $this->month_start_date = date("m/d",strtotime($this->start_date));
        $this->month_end_date = date("m/d",strtotime($this->end_date));
        $monthNum = date("n",strtotime($this->start_date));
        $i = ceil($monthNum/3);//向上取整
        $this->month_type = 3*$i-2;
        ComparisonForm::setDayNum($this->start_date,$this->end_date,$this->day_num);
        $lastStartDate = ($this->comparison_year-1)."/".$this->month_start_date;
        $lastEndDate = ($this->comparison_year-1)."/".$this->month_end_date;
        $where="(a.status_dt BETWEEN '{$this->start_date}' and '{$this->end_date}')";
        $where.="or (a.status_dt BETWEEN '{$lastStartDate}' and '{$lastEndDate}')";
        $selectSql="a.status_dt,a.status,a.reason,com.code,com.name,f.rpt_cat,f.description as type_name,f.single,a.city,a.service,a.company_name,g.description as nature_name,g.rpt_cat as nature_rpt_cat,a.nature_type,a.amt_paid,a.ctrt_period,a.b4_amt_paid
            ,b.region,b.name as city_name,c.name as region_name";
        $serviceRows = Yii::app()->db->createCommand()
            ->select("{$selectSql},a.paid_type,a.b4_paid_type,CONCAT('A') as sql_type_name")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("swoper{$suffix}.swo_company com","com.id=a.company_id")
            ->leftJoin("swoper{$suffix}.swo_customer_type f","a.cust_type=f.id")
            ->leftJoin("swoper{$suffix}.swo_nature g","a.nature_type=g.id")
            ->leftJoin("security{$suffix}.sec_city b","a.city=b.code")
            ->leftJoin("security{$suffix}.sec_city c","b.region=c.code")
            ->where("a.status in ('N','T') and a.city not in ('ZY') and ({$where})")
            ->order("a.city")
            ->queryAll();
        //所有需要計算的客戶服務(ID客戶服務)
        $serviceRowsID = Yii::app()->db->createCommand()
            ->select("{$selectSql},CONCAT('M') as paid_type,CONCAT('M') as b4_paid_type,CONCAT('D') as sql_type_name")
            ->from("swoper$suffix.swo_serviceid a")
            ->leftJoin("swoper{$suffix}.swo_company com","com.id=a.company_id")
            ->leftJoin("swoper$suffix.swo_customer_type_id f","a.cust_type=f.id")
            ->leftJoin("swoper{$suffix}.swo_nature g","a.nature_type=g.id")
            ->leftJoin("security{$suffix}.sec_city b","a.city=b.code")
            ->leftJoin("security{$suffix}.sec_city c","b.region=c.code")
            ->where("a.status in ('N','T') and a.city not in ('ZY') and ({$where})")
            ->order("a.city")
            ->queryAll();
        $serviceRows = $serviceRows?$serviceRows:array();
        $serviceRowsID = $serviceRowsID?$serviceRowsID:array();
        $rows = array_merge($serviceRows,$serviceRowsID);
        $citySetList = self::getCitySetList();
        if(!empty($rows)){
            foreach ($rows as $row){
                //rpt_cat='INV' and single=1的客户服务是产品，所以需要筛选出去
                if($row["rpt_cat"]==="INV"&&intval($row["single"])===1){
                    continue;
                }
                $row["amt_paid"] = is_numeric($row["amt_paid"])?floatval($row["amt_paid"]):0;
                $row["ctrt_period"] = is_numeric($row["ctrt_period"])?floatval($row["ctrt_period"]):0;
                $row["b4_amt_paid"] = is_numeric($row["b4_amt_paid"])?floatval($row["b4_amt_paid"]):0;
                $this->insertDataForRow($row,$data,$citySetList);
            }
        }

        $this->insertUData($this->start_date,$this->end_date,$data);
        $this->insertUData($lastStartDate,$lastEndDate,$data);
        //$this->insertUServiceData($this->start_date,$data);//同步U系統的服務金額
        $this->insertUActualMoney($this->start_date,$this->end_date,$data);//服务生意额
        $this->data = $data;
        return true;
    }

    public static function getCitySetList(){
        $list=array();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
            ->select("a.code,a.name as city_name,b.show_type,b.add_type,b.region_code,b.region_code as region,f.name as region_name")
            ->from("swoper$suffix.swo_city_set b")
            ->leftJoin("security$suffix.sec_city a","a.code=b.code")
            ->leftJoin("security$suffix.sec_city f","b.region_code=f.code")
            ->where("b.show_type=1")
            ->order("b.z_index desc,a.name asc")
            ->queryAll();
        if ($rows){
            foreach ($rows as $row){
                $list[$row["code"]] = $row;
            }
        }
        return $list;
    }

    public static function getListForCityCode($city_code,$list){
        if(key_exists($city_code,$list)){
            return $list[$city_code];
        }else{
            return array("code"=>$city_code,"city_name"=>"","show_type"=>0,"add_type"=>0,"region_code"=>"null","region_name"=>"null");
        }
    }

    private function insertUServiceData($startDate,&$data){
        $year = date("Y",strtotime($startDate));
        $month = date("n",strtotime($startDate));
        $json = Invoice::getActualAmount($year,$month);
        if($json["message"]==="Success"){
            $jsonData = $json["data"];
            foreach ($jsonData as $row){
                $city = $row["city"];
                $money = is_numeric($row["actual_amt"])?floatval($row["actual_amt"]):0;
                if($row["service"]!="销货账单"&&key_exists($city,$data)){
                    $data[$city]["uServiceMoney"]+=$money;
                }
            }
        }
    }

    //服务生意额
    private function insertUActualMoney($startDay,$endDay,&$data){
        $list = array();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("b.Text,a.Fee,a.TermCount")
            ->from("service{$suffix}.joborder a")
            ->leftJoin("service{$suffix}.officecity f","a.City = f.City")
            ->leftJoin("service{$suffix}.enums b","f.Office = b.EnumID and b.EnumType=8")
            ->where("a.Status=3 and a.JobDate BETWEEN '{$startDay}' AND '{$endDay}'")
            ->order("b.Text")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $city = $row["Text"];
                $money = empty($row["TermCount"])?0:floatval($row["Fee"])/floatval($row["TermCount"]);
                if(key_exists($city,$data)){
                    $data[$city]["u_actual_money"]+=$money;
                }
            }
        }
        return $list;
    }

    private function insertUData($startDate,$endDate,&$data){
        $year = intval($startDate);//服务的年份
        $json = Invoice::getInvData($startDate,$endDate);
        if($json["message"]==="Success"){
            $jsonData = $json["data"];
            foreach ($jsonData as $row){
                $city = $row["city"];
                $money = is_numeric($row["invoice_amt"])?floatval($row["invoice_amt"]):0;
                if(key_exists($city,$data)){
                    if($year==$this->comparison_year){
                        $data[$city]["u_actual_money"]+=$money;//服务生意额需要加上产品金额
                        $uStr = "u_sum";
                        $newStr = "new_sum";
                        $netStr = "net_sum";
                    }else{
                        $uStr = "u_sum_last";
                        $newStr = "new_sum_last";
                        $netStr = "net_sum_last";
                    }
                    $data[$city][$uStr]+=$money;
                    $data[$city][$newStr]+=$money;
                    $data[$city][$netStr]+=$money;
                }
            }
        }
    }

    private function defMoreCity($city,$city_name){
        $suffix = Yii::app()->params['envSuffix'];
        $startRow = Yii::app()->db->createCommand()->select("*")->from("swoper{$suffix}.swo_comparison_set")
            ->where("comparison_year=:year and month_type=1 and city=:city",
                array(":year"=>$this->comparison_year,":city"=>$city)
            )->queryRow();//年初目标金额
        $setRow = Yii::app()->db->createCommand()->select("*")->from("swoper{$suffix}.swo_comparison_set")
            ->where("comparison_year=:year and month_type=:month_type and city=:city",
                array(":year"=>$this->comparison_year,":month_type"=>$this->month_type,":city"=>$city)
            )->queryRow();//滚动目标金额
        $contractArr = array(
            "city"=>$city,
            "city_name"=>$city_name,
            "u_actual_money"=>0,//服务金额
            "u_sum_last"=>0,//U系统金额(上一年)
            "u_sum"=>0,//U系统金额
            "stopWeekSum"=>0,//本週停單金額（年金額）
            "stopMonthSum"=>0,//本週停單金額（月金額）
            "stopSumOnly"=>0,//本月停單金額（月）
            "stopListOnly"=>array(),//本月停單列表（月金額超過1000的數據）
            "uServiceMoney"=>0,//U系統內的實際服務金額（月）
            "new_sum_last"=>0,//新增(上一年)
            "new_sum"=>0,//新增
            "new_rate"=>0,//新增对比比例
            "stop_sum_last"=>0,//终止（上一年）(年金額)
            "stop_sum"=>0,//终止(年金額)
            "stop_rate"=>0,//终止对比比例
            "net_sum_last"=>0,//总和（上一年）
            "net_sum"=>0,//总和(年金額)
            "net_rate"=>0,//总和对比比例
            "two_gross"=>$setRow?floatval($setRow["two_gross"]):0,
            "two_gross_rate"=>0,
            "two_net"=>$setRow?floatval($setRow["two_net"]):0,
            "two_net_rate"=>0,
            "start_two_gross"=>$startRow?floatval($startRow["two_gross"]):0,
            "start_two_gross_rate"=>0,
            "start_two_net"=>$startRow?floatval($startRow["two_net"]):0,
            "start_two_net_rate"=>0,

            "one_gross"=>$setRow?floatval($setRow["one_gross"]):0,
            "one_gross_rate"=>0,
            "one_net"=>$setRow?floatval($setRow["one_net"]):0,
            "one_net_rate"=>0,
            "start_one_gross"=>$startRow?floatval($startRow["one_gross"]):0,
            "start_one_gross_rate"=>0,
            "start_one_net"=>$startRow?floatval($startRow["one_net"]):0,
            "start_one_net_rate"=>0
        );
        return $contractArr;
    }

    private function insertDataForRow($row,&$data,$citySetList){
        $suffix = Yii::app()->params['envSuffix'];
	    $year = intval($row["status_dt"]);//服务的年份
        $city = empty($row["city"])?"none":$row["city"];
        $citySet = self::getListForCityCode($city,$citySetList);
        if(!key_exists($city,$data)){
            $data[$city]=$this->defMoreCity($city,$row["city_name"]);
        }
        if($citySet["add_type"]==1){//叠加(城市配置的叠加)
            if(!key_exists($citySet["region_code"],$data)){
                $data[$citySet["region_code"]]=$this->defMoreCity($citySet["region_code"],$citySet["region_name"]);
            }
        }
        if($row["paid_type"]=="M"){//月金额
            $money = $row["amt_paid"]*$row["ctrt_period"];//年金額
            $monthMoney = $row["amt_paid"];//月金額
        }else{
            $money = $row["amt_paid"];
            $monthMoney = empty($row["ctrt_period"])?0:$row["amt_paid"]/$row["ctrt_period"];
            $monthMoney = round($monthMoney,2);
        }
        if($year==$this->comparison_year){
            $newStr = "new_sum";
            $stopStr = "stop_sum";
            $netStr = "net_sum";
        }else{
            $newStr = "new_sum_last";
            $stopStr = "stop_sum_last";
            $netStr = "net_sum_last";
        }
        switch ($row["status"]) {
            case "N"://新增
                $data[$city][$newStr] += $money;
                $data[$city][$netStr] += $money;
                if($citySet["add_type"]==1){//叠加(城市配置的叠加)
                    $data[$citySet["region_code"]][$newStr] += $money;
                    $data[$citySet["region_code"]][$netStr] += $money;
                }
                break;
            case "T"://终止
                if(strtotime($this->week_start_date)<=strtotime($row["status_dt"])){
                    $data[$city]["stopWeekSum"] += $money;
                    $data[$city]["stopMonthSum"] += $monthMoney;
                    if($citySet["add_type"]==1){//叠加(城市配置的叠加)
                        $data[$citySet["region_code"]]["stopWeekSum"] += $money;
                        $data[$citySet["region_code"]]["stopMonthSum"] += $monthMoney;
                    }
                }
                if($this->comparison_year==$year){
                    $data[$city]["stopSumOnly"] += $monthMoney;
                    if($citySet["add_type"]==1){//叠加(城市配置的叠加)
                        $data[$citySet["region_code"]]["stopSumOnly"] += $monthMoney;
                    }
                    if($monthMoney>=1000){
                        $stopList = $row;
                        $stopList["stopMoneyForMonth"] = $monthMoney;
                        $stopList["stopMoneyForYear"] = $money;
                        $data[$city]["stopListOnly"][] = $stopList;
                        if($citySet["add_type"]==1){//叠加(城市配置的叠加)
                            $data[$citySet["region_code"]]["stopListOnly"][] = $stopList;
                        }
                    }
                }
                $money *= -1;
                $data[$city][$stopStr] += $money;
                $data[$city][$netStr] += $money;
                if($citySet["add_type"]==1){//叠加(城市配置的叠加)
                    $data[$citySet["region_code"]][$stopStr] += $money;
                    $data[$citySet["region_code"]][$netStr] += $money;
                }
                break;
        }
    }

    protected function resetTdRow(&$list,$bool=false){
        $list["start_one_gross"] = $bool?$list["start_one_gross"]:ComparisonForm::resetNetOrGross($list["start_one_gross"],$this->day_num);
        $list["start_one_net"] = $bool?$list["start_one_net"]:ComparisonForm::resetNetOrGross($list["start_one_net"],$this->day_num);
        $list["start_two_gross"] = $bool?$list["start_two_gross"]:ComparisonForm::resetNetOrGross($list["start_two_gross"],$this->day_num);
        $list["start_two_net"] = $bool?$list["start_two_net"]:ComparisonForm::resetNetOrGross($list["start_two_net"],$this->day_num);
        $list["two_gross"] = $bool?$list["two_gross"]:ComparisonForm::resetNetOrGross($list["two_gross"],$this->day_num);
        $list["two_net"] = $bool?$list["two_net"]:ComparisonForm::resetNetOrGross($list["two_net"],$this->day_num);
        $list["new_rate"] = $this->nowAndLastRate($list["new_sum"],$list["new_sum_last"],true);
        $list["stop_rate"] = $this->nowAndLastRate($list["stop_sum"],$list["stop_sum_last"],true);
        $list["net_rate"] = $this->nowAndLastRate($list["net_sum"],$list["net_sum_last"],true);
        $list["start_two_gross_rate"] = $this->comparisonRate($list["new_sum"],$list["start_two_gross"]);
        $list["start_two_net_rate"] = $this->comparisonRate($list["net_sum"],$list["start_two_net"],"net");
        $list["start_one_gross_rate"] = $this->comparisonRate($list["new_sum"],$list["start_one_gross"]);
        $list["start_one_net_rate"] = $this->comparisonRate($list["net_sum"],$list["start_one_net"],"net");
        //$list["two_gross_rate"] = $this->comparisonRate($list["new_sum"],$list["two_gross"]);
        //$list["two_net_rate"] = $this->comparisonRate($list["net_sum"],$list["two_net"],"net");
    }

    public static function nowAndLastRate($nowNum,$lastNum,$bool=false){
        if(empty($lastNum)){
            return 0;
        }else{
            $rate = $nowNum-$lastNum;
            $lastNum = $lastNum<0?$lastNum*-1:$lastNum;
            $rate = $rate/$lastNum;
            $rate = round($rate,3)*100;
            if($bool&&$rate>0){
                $rate=" +".$rate;
            }
            return $rate."%";
        }
    }

    public static function comparisonRate($num,$numLast,$str=""){
        if(empty($numLast)){
            if($str=="net"){
                if($num>0){
                    return Yii::t("summary","completed");
                }else{
                    return Yii::t("summary","incomplete");
                }
            }else{
                return 0;
            }
        }else{
            $rate = ($num/$numLast);
            $rate = round($rate,3)*100;
            return $rate."%";
        }
    }

    public static function showNum($num){
        $pre="";
        if (strpos($num," +")!==false){
            $pre=" +";
            $num = end(explode(" +",$num));
        }
        if (strpos($num,'%')!==false){
            $number = floatval($num);
            $number=sprintf("%.1f",$number)."%";
        }elseif (is_numeric($num)){
            $number = floatval($num);
            $number=sprintf("%.2f",$number);
        }else{
            $number = $num;
        }
        return $pre.$number;
    }

    public function getDataToHtml(){
        $htmlList = array();
        $bodyKey = $this->getDataAllKeyStr();
        $tableHeader = $this->tableTopHtml();
        $table = "<p><b>{$this->start_date}至{$this->end_date}新增、终止同比分析</b></p>";
        $table.= '<div style="min-height:.01%;overflow-x: auto">';
        $table.= '<table border="1" cellpadding="0" cellspacing="0" style="table-layout:fixed;width: 100%;max-width: 100%;border-collapse:collapse">';
        $table.='<thead>';
        $table.=$this->tableHeaderWidth();
        $table.=$tableHeader;
        $table.='</thead>';
        $table.='<tbody>';
        if(!empty($this->data)){
            foreach ($this->data as $row){
                $stopSum = $row["stopSumOnly"]>=0?$row["stopSumOnly"]:$row["stopSumOnly"]*-1;//本月終止金額
                $uServiceMoney = $row["u_actual_money"];//（2023/5/3改成服务金额）
                //本月停單率
                $htmlList[$row["city"]]["stopRate"]=$this->comparisonRate($stopSum,$uServiceMoney);
                //目標金額
                $htmlList[$row["city"]]["twoGross"]=$row["two_gross"];
                //本周停单金额(年金額)
                $htmlList[$row["city"]]["stopWeekSum"]=$row["stopWeekSum"];
                //本周停单金额(月金額)
                $htmlList[$row["city"]]["stopMonthSum"]=$row["stopMonthSum"];
                //U系統內的實際服務金額(月)- （2023/5/3改成服务金额）
                $htmlList[$row["city"]]["uServiceMoney"]=$uServiceMoney;
                //停單金額超過1000的客戶資料
                $htmlList[$row["city"]]["stopListOnly"]=$row["stopListOnly"];
                $htmlList[$row["city"]]["table"]=$table;
                $this->resetTdRow($row);
                $htmlList[$row["city"]]["table"].='<tr>';
                foreach ($bodyKey as $keyStr){
                    $text = key_exists($keyStr,$row)?$row[$keyStr]:"0";
                    $tdClass = ComparisonForm::getTextColorForKeyStr($text,$keyStr);
                    $text = ComparisonForm::showNum($text);
                    $htmlList[$row["city"]]["table"].="<td style='text-align: center;{$tdClass}'>{$text}</td>";
                }
                $htmlList[$row["city"]]["table"].='</tr>';
                $htmlList[$row["city"]]["table"].='</tbody>';
                $htmlList[$row["city"]]["table"].='</table>';
                $htmlList[$row["city"]]["table"].='</div>';
            }
        }
        $this->defaultTable = $table."<tr>";
        foreach ($bodyKey as $keyStr){
            $text = $keyStr=="city_name"?":city_name:":"0";
            $this->defaultTable.= "<td style='text-align: center;'>{$text}</td>";
        }
        $this->defaultTable.= "</tr></tbody></table></div>";
        return $htmlList;
    }

    //設置百分比顏色
    public static function getTextColorForKeyStr($text,$keyStr){
        $tdClass = "";
        if(strpos($text,'%')!==false){
            if(!in_array($keyStr,array("new_rate","stop_rate","net_rate"))){
                $tdClass =floatval($text)<=60?";color:#a94442;":$tdClass;
            }
            $tdClass =floatval($text)>=100?";color:#00a65a;":$tdClass;
        }elseif (strpos($keyStr,'net')!==false){ //所有淨增長為0時特殊處理
            if(Yii::t("summary","completed")==$text){
                $tdClass=";color:#00a65a;";
            }elseif (Yii::t("summary","incomplete")==$text){
                $tdClass=";color:#a94442;";
            }
        }

        return $tdClass;
    }

    private function getTopArr(){
        $monthStr = "（{$this->month_start_date} ~ {$this->month_end_date}）";
        $topList=array(
            array("name"=>Yii::t("summary","City"),"rowspan"=>2),//城市
            array("name"=>Yii::t("summary","Actual monthly amount"),"rowspan"=>2),//服务生意额
            array("name"=>Yii::t("summary","YTD New").$monthStr,"background"=>"#f7fd9d",
                "colspan"=>array(
                    array("name"=>$this->comparison_year-1),//对比年份
                    array("name"=>$this->comparison_year),//查询年份
                    array("name"=>Yii::t("summary","YoY change")),//YoY change
                )
            ),//YTD新增
            array("name"=>Yii::t("summary","YTD Stop").$monthStr,"exprName"=>$monthStr,"background"=>"#fcd5b4",
                "colspan"=>array(
                    array("name"=>$this->comparison_year-1),//对比年份
                    array("name"=>$this->comparison_year),//查询年份
                    array("name"=>Yii::t("summary","YoY change")),//YoY change
                )
            ),//YTD终止
            array("name"=>Yii::t("summary","YTD Net").$monthStr,"background"=>"#f2dcdb",
                "colspan"=>array(
                    array("name"=>$this->comparison_year-1),//对比年份
                    array("name"=>$this->comparison_year),//查询年份
                    array("name"=>Yii::t("summary","YoY change")),//YoY change
                )
            ),//YTD Net
        );
        $colspan=array(
            array("name"=>Yii::t("summary","Start Gross")),//Start Gross
            array("name"=>Yii::t("summary","Start Net")),//Start Net
            //array("name"=>Yii::t("summary","Gross")),//Gross
            //array("name"=>Yii::t("summary","Net")),//Net
        );
        $topList[]=array("name"=>Yii::t("summary","Annual target (upside case)"),"background"=>"#FDE9D9",
            "colspan"=>$colspan
        );//年金额目标 (upside case)
        $topList[]=array("name"=>Yii::t("summary","Goal degree (upside case)"),"background"=>"#FDE9D9",
            "colspan"=>$colspan
        );//目标完成度 (upside case)
        $topList[]=array("name"=>Yii::t("summary","Annual target (base case)"),"background"=>"#DCE6F1",
            "colspan"=>$colspan
        );//年金额目标 (base case)
        $topList[]=array("name"=>Yii::t("summary","Goal degree (base case)"),"background"=>"#DCE6F1",
            "colspan"=>$colspan
        );//目标完成度 (base case)

        return $topList;
    }

    //顯示提成表的表格內容（表頭）
    private function tableTopHtml(){
        $topList = self::getTopArr();
        $trOne="";
        $trTwo="";
        $html="<thead>";
        foreach ($topList as $list){
            $clickName=$list["name"];
            $colList=key_exists("colspan",$list)?$list['colspan']:array();
            $trOne.="<th";
            if(key_exists("rowspan",$list)){
                $trOne.=" rowspan='{$list["rowspan"]}'";
            }
            if(key_exists("colspan",$list)){
                $colNum=count($colList);
                $trOne.=" colspan='{$colNum}' class='click-th'";
            }
            if(key_exists("background",$list)){
                $trOne.=" style='background:{$list["background"]}'";
            }
            if(key_exists("startKey",$list)){
                $trOne.=" data-key='{$list['startKey']}'";
            }
            $trOne.=" >".$clickName."</th>";
            if(!empty($colList)){
                foreach ($colList as $col){
                    $this->th_sum++;
                    $trTwo.="<th>".$col["name"]."</th>";
                }
            }
        }
        $html.=$this->tableHeaderWidth();//設置表格的單元格寬度
        $html.="<tr>{$trOne}</tr><tr>{$trTwo}</tr>";
        $html.="</thead>";
        return $html;
    }

    //設置表格的單元格寬度
    private function tableHeaderWidth(){
        $html="<tr>";
        for($i=0;$i<$this->th_sum;$i++){
            if(in_array($i,array(1,4,7,10,19,20,21,22))){
                $width=110;
            }else{
                $width=100;
            }
            $html.="<th style='height: 0px;line-height: 0px;border: none;overflow: hidden' data-width='{$width}' width='{$width}px'>{$i}</th>";
        }
        return $html."</tr>";
    }

    //获取td对应的键名
    private function getDataAllKeyStr(){
        $bodyKey = array(
            "city_name","u_actual_money","new_sum_last","new_sum","new_rate","stop_sum_last","stop_sum","stop_rate",
            "net_sum_last","net_sum","net_rate"
        );
        $bodyKey[]="start_one_gross";
        $bodyKey[]="start_one_net";
        $bodyKey[]="start_one_gross_rate";
        $bodyKey[]="start_one_net_rate";

        $bodyKey[]="start_two_gross";
        $bodyKey[]="start_two_net";
        //$bodyKey[]="two_gross";
        //$bodyKey[]="two_net";
        $bodyKey[]="start_two_gross_rate";
        $bodyKey[]="start_two_net_rate";
        //$bodyKey[]="two_gross_rate";
        //$bodyKey[]="two_net_rate";
        return $bodyKey;
    }
}