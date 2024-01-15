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
    public $last_month_start_date;
    public $last_month_end_date;

    public static $con_list=array("one_gross","one_net","two_gross","two_net","three_gross","three_net");

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
        $city_allow="";
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

        $this->last_month_start_date = CountSearch::computeLastMonth($this->start_date);
        $this->last_month_end_date = CountSearch::computeLastMonth($this->end_date);

        $citySetList = self::getCitySetList();
        $startDate = $this->start_date;
        $endDate = $this->end_date;
        $monthStartDate = $this->last_month_start_date;
        $monthEndDate = $this->last_month_end_date;
        $lastMonthStartDate = ($this->comparison_year-1)."/".date("m/d",strtotime($monthStartDate));
        $lastMonthEndDate = ($this->comparison_year-1)."/".date("m/d",strtotime($monthEndDate));
        //获取U系统的服务单数据
        $uServiceMoney = CountSearch::getUServiceMoney($startDate,$endDate,$city_allow);
        //获取U系统的服务单数据(上月)
        $uServiceMoneyLast = CountSearch::getUServiceMoney($monthStartDate,$monthEndDate,$city_allow);
        //获取U系统的產品数据
        $uInvMoney = CountSearch::getUInvMoney($startDate,$endDate,$city_allow);
        //获取U系统的產品数据(上一年)
        $lastUInvMoney = CountSearch::getUInvMoney($lastStartDate,$lastEndDate,$city_allow);
        //服务新增（非一次性 和 一次性)
        $serviceAddForNY = CountSearch::getServiceAddForNY($startDate,$endDate,$city_allow);
        //服务新增（非一次性 和 一次性)(上一年)
        $lastServiceAddForNY = CountSearch::getServiceAddForNY($lastStartDate,$lastEndDate,$city_allow);
        //终止服务、暂停服务
        $serviceForST = CountSearch::getServiceForST($startDate,$endDate,$city_allow);
        //终止服务、暂停服务(上一年)
        $lastServiceForST = CountSearch::getServiceForST($lastStartDate,$lastEndDate,$city_allow);
        //恢復服务
        $serviceForR = CountSearch::getServiceForType($startDate,$endDate,$city_allow,"R");
        //恢復服务(上一年)
        $lastServiceForR = CountSearch::getServiceForType($lastStartDate,$lastEndDate,$city_allow,"R");
        //更改服务
        $serviceForA = CountSearch::getServiceForA($startDate,$endDate,$city_allow);
        //更改服务(上一年)
        $lastServiceForA = CountSearch::getServiceForA($lastStartDate,$lastStartDate,$city_allow);
        //服务新增（一次性)(上月)
        $monthServiceAddForY = CountSearch::getServiceAddForY($monthStartDate,$monthEndDate,$city_allow);
        //服务新增（一次性)(上月)(上一年)
        $lastMonthServiceAddForY = CountSearch::getServiceAddForY($lastMonthStartDate,$lastMonthEndDate,$city_allow);
        //获取U系统的產品数据(上月)
        $monthUInvMoney = CountSearch::getUInvMoney($monthStartDate,$monthEndDate,$city_allow);
        //获取U系统的產品数据(上月)(上一年)
        $lastMonthUInvMoney = CountSearch::getUInvMoney($lastMonthStartDate,$lastMonthEndDate,$city_allow);
        //本周停单年金额、本周停单月金额
        $serviceForT = CountSearch::getServiceForT($this->week_start_date,$endDate,$city_allow);
        //月金额超过1000元/月的停单客户
        $serviceRowsForT = CountSearch::getServiceRowsForT($startDate,$endDate,$city_allow);

        //"stopListOnly"=>array(),//月金额超过1000元/月的停单客户
        //"stopWeekSum"=>0,//本周停单年金额
        //"stopMonthSum"=>0,//本周停单月金额
        foreach ($citySetList as $cityRow){
            $city = $cityRow["code"];
            $defMoreList=$this->defMoreCity($city,$cityRow["city_name"]);
            $defMoreList["add_type"] = $cityRow["add_type"];
            self::setComparisonConfig($defMoreList,$this->comparison_year,$this->start_date,$city);
            $defMoreList["u_actual_money"]+=key_exists($city,$uServiceMoney)?$uServiceMoney[$city]:0;
            $defMoreList["u_sum"]+=key_exists($city,$uInvMoney)?$uInvMoney[$city]["sum_money"]:0;
            $defMoreList["u_actual_money"]+=$defMoreList["u_sum"];//生意额需要加上U系统产品金额
            $defMoreList["u_sum_last"]+=key_exists($city,$lastUInvMoney)?$lastUInvMoney[$city]["sum_money"]:0;
            $defMoreList["stopListOnly"]+=key_exists($city,$serviceRowsForT)?$serviceRowsForT[$city]:array();
            if(key_exists($city,$serviceForT)){
                $defMoreList["stopWeekSum"]+=$serviceForT[$city]["sum_amount"];
                $defMoreList["stopMonthSum"]+=$serviceForT[$city]["sum_month"];
            }
            if(key_exists($city,$serviceAddForNY)){
                $defMoreList["new_sum"]+=$serviceAddForNY[$city]["num_new"];
                $defMoreList["new_sum_n"]+=$serviceAddForNY[$city]["num_new_n"];
            }
            $defMoreList["new_sum_n"]+=$defMoreList["u_sum"];//一次性新增需要加上U系统产品金额
            if(key_exists($city,$lastServiceAddForNY)){
                $defMoreList["new_sum_last"]+=$lastServiceAddForNY[$city]["num_new"];
                $defMoreList["new_sum_n_last"]+=$lastServiceAddForNY[$city]["num_new_n"];
            }
            $defMoreList["new_sum_n_last"]+=$defMoreList["u_sum_last"];//一次性新增需要加上U系统产品金额
            //上月一次性服务+新增（产品）
            $defMoreList["new_month_n_last"]+=key_exists($city,$lastMonthServiceAddForY)?-1*$lastMonthServiceAddForY[$city]:0;
            $defMoreList["new_month_n_last"]+=key_exists($city,$lastMonthUInvMoney)?-1*$lastMonthUInvMoney[$city]["sum_money"]:0;
            $defMoreList["new_month_n"]+=key_exists($city,$monthServiceAddForY)?-1*$monthServiceAddForY[$city]:0;
            $defMoreList["new_month_n"]+=key_exists($city,$monthUInvMoney)?-1*$monthUInvMoney[$city]["sum_money"]:0;
            //上月生意额
            $defMoreList["last_u_actual"]+=key_exists($city,$uServiceMoneyLast)?$uServiceMoneyLast[$city]:0;
            $defMoreList["last_u_actual"]+=key_exists($city,$monthUInvMoney)?$monthUInvMoney[$city]["sum_money"]:0;
            //暂停、停止
            if(key_exists($city,$serviceForST)){
                $defMoreList["stop_sum"]+=key_exists($city,$serviceForST)?-1*$serviceForST[$city]["num_stop"]:0;
                $defMoreList["pause_sum"]+=key_exists($city,$serviceForST)?-1*$serviceForST[$city]["num_pause"]:0;
                $defMoreList["stopSumOnly"]+=key_exists($city,$serviceForST)?$serviceForST[$city]["num_month"]:0;
            }
            if(key_exists($city,$lastServiceForST)){
                $defMoreList["stop_sum_last"]+=key_exists($city,$lastServiceForST)?-1*$lastServiceForST[$city]["num_stop"]:0;
                $defMoreList["pause_sum_last"]+=key_exists($city,$lastServiceForST)?-1*$lastServiceForST[$city]["num_pause"]:0;
            }
            //恢复
            $defMoreList["resume_sum_last"]+=key_exists($city,$lastServiceForR)?$lastServiceForR[$city]:0;
            $defMoreList["resume_sum"]+=key_exists($city,$serviceForR)?$serviceForR[$city]:0;
            //更改
            $defMoreList["amend_sum_last"]+=key_exists($city,$lastServiceForA)?$lastServiceForA[$city]:0;
            $defMoreList["amend_sum"]+=key_exists($city,$serviceForA)?$serviceForA[$city]:0;

            self::resetData($data,$cityRow,$citySetList,$defMoreList);
        }

        $this->data = $data;
        return true;
    }

    //設置滾動生意額及年初生意額
    public static function setComparisonConfig(&$arr,$year,$start_date,$city){
        $suffix = Yii::app()->params['envSuffix'];
        $monthNum = date("n",strtotime($start_date));
        $year2024 = false;//2024年的年初生意额 = 滚动生意额
        if($year==2024){
            $year2024=true;
            $monthList = array(
                1=>array("min"=>1,"max"=>3),
                4=>array("min"=>4,"max"=>7),
                7=>array("min"=>8,"max"=>12),
            );
        }else{
            $monthList = array(
                1=>array("min"=>1,"max"=>3),
                4=>array("min"=>4,"max"=>6),
                7=>array("min"=>7,"max"=>9),
                10=>array("min"=>10,"max"=>12),
            );
        }
        $month_type = 1;
        foreach ($monthList as $monthKey=>$monthItem){
            if($monthItem["min"]<=$monthNum&&$monthNum<=$monthItem["max"]){
                $month_type = $monthKey;
                break;
            }
        }
        foreach (self::$con_list as $itemStr){//初始化
            $arr[$itemStr]=0;
            $arr[$itemStr."_rate"]=0;
            $arr["start_".$itemStr]=0;
            $arr["start_".$itemStr."_rate"]=0;
        }
        $rowStart = Yii::app()->db->createCommand()->select("*")->from("swoper$suffix.swo_comparison_set")
            ->where("comparison_year=:year and month_type=1 and city=:city",
                array(":year"=>$year,":city"=>$city)
            )->queryRow();//查询目标金额
        if($rowStart){
            foreach (self::$con_list as $itemStr){//写入年初生意额
                $arr["start_".$itemStr]=empty($rowStart[$itemStr])?0:floatval($rowStart[$itemStr]);
            }
        }
        $setRow = Yii::app()->db->createCommand()->select("*")->from("swoper$suffix.swo_comparison_set")
            ->where("comparison_year=:year and month_type=:month_type and city=:city",
                array(":year"=>$year,":month_type"=>$month_type,":city"=>$city)
            )->queryRow();//查询目标金额
        if($setRow){
            foreach (self::$con_list as $itemStr){//写入滚动生意额
                $arr[$itemStr]=empty($setRow[$itemStr])?0:floatval($setRow[$itemStr]);
                if($year2024){//2024年的年初生意额 = 滚动生意额
                    $arr["start_".$itemStr] = $arr[$itemStr];
                }
            }
        }
    }

    public static function resetData(&$data,$cityRow,$citySet,$defMoreList){
        $notAddList=array("add_type");
        foreach (self::$con_list as $itemStr){
            $notAddList[]=$itemStr;
            $notAddList[]="start_".$itemStr;
        }
        $city = $cityRow["code"];
        $region = $cityRow["region_code"];
        $defMoreList["city"]=$city;
        $defMoreList["city_name"]= $cityRow["city_name"];
        $defMoreList["add_type"]= $cityRow["add_type"];
        if(!key_exists($city,$data)){
            $data[$city]=$defMoreList;
        }else{
            foreach ($defMoreList as $key=>$value){
                if(in_array($key,$notAddList)){
                    $data[$city][$key]=$value;
                }elseif (is_numeric($value)){
                    $data[$city][$key]+=$value;
                }elseif(is_array($value)){
                    $data[$city][$key]=array_merge($value,$data[$city][$key]);
                }else{
                    $data[$city][$key]=$value;
                }
            }
        }

        if($cityRow["add_type"]==1&&key_exists($region,$citySet)){//叠加(城市配置的叠加)
            $regionTwo = $citySet[$region];
            self::resetData($data,$regionTwo,$citySet,$defMoreList);
        }
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

    private function defMoreCity($city,$city_name){
        $arr=array(
            "city"=>$city,
            "city_name"=>$city_name,
            "last_u_actual"=>0,//服务生意额(上月)
            "u_actual_money"=>0,//服务生意额
            "u_sum_last"=>0,//U系统金额(上一年)
            "u_sum"=>0,//U系统金额
            "stopSumOnly"=>0,//本月停單金額（月）
            "monthStopRate"=>0,//月停單率
            "new_sum_last"=>0,//新增(上一年)
            "new_sum"=>0,//新增
            "new_rate"=>0,//新增对比比例
            "stopListOnly"=>array(),//月金额超过1000元/月的停单客户
            "stopWeekSum"=>0,//本周停单年金额
            "stopMonthSum"=>0,//本周停单月金额

            "new_sum_n_last"=>0,//一次性服务+新增（产品） (上一年)
            "new_sum_n"=>0,//一次性服务+新增（产品）
            "new_n_rate"=>0,//一次性服务+新增（产品）对比比例

            "new_month_n_last"=>0,//上月一次性服务+新增（产品） (上一年)
            "new_month_n"=>0,//上月一次性服务+新增（产品）
            "new_month_rate"=>0,//上月一次性服务+新增（产品）对比比例

            "stop_sum_last"=>0,//终止（上一年）
            "stop_sum"=>0,//终止
            "stop_rate"=>0,//终止对比比例

            "resume_sum_last"=>0,//恢复（上一年）
            "resume_sum"=>0,//恢复
            "resume_rate"=>0,//恢复对比比例

            "pause_sum_last"=>0,//暂停（上一年）
            "pause_sum"=>0,//暂停
            "pause_rate"=>0,//暂停对比比例

            "amend_sum_last"=>0,//更改（上一年）
            "amend_sum"=>0,//更改
            "amend_rate"=>0,//更改对比比例

            "net_sum_last"=>0,//总和（上一年）
            "net_sum"=>0,//总和
            "net_rate"=>0,//总和对比比例
        );
        return $arr;
    }

    protected function resetTdRow(&$list,$bool=false){
        $newSum = $list["new_sum"]+$list["new_sum_n"];//所有新增总金额
        //$list["monthStopRate"] = $this->comparisonRate($list["stopSumOnly"],$list["u_actual_money"]);
        //2023年9月改版：月停单率 = (new_sum_n+new_month_n+stop_sum/12)/last_u_actual
        if($bool){
            $list["monthStopRate"] = "-";
        }else{
            $list["monthStopRate"] = $list["new_sum_n"]+$list["new_month_n"]+round($list["stop_sum"]/12,2);
            $list["monthStopRate"] = $this->comparisonRate($list["monthStopRate"],$list["last_u_actual"]);
        }
        $list["net_sum"]=0;
        $list["net_sum"]+=$list["new_sum"]+$list["new_sum_n"]+$list["new_month_n"];
        $list["net_sum"]+=$list["stop_sum"]+$list["resume_sum"]+$list["pause_sum"];
        $list["net_sum"]+=$list["amend_sum"];
        $list["net_sum_last"]=0;
        $list["net_sum_last"]+=$list["new_sum_last"]+$list["new_sum_n_last"]+$list["new_month_n_last"];
        $list["net_sum_last"]+=$list["stop_sum_last"]+$list["resume_sum_last"]+$list["pause_sum_last"];
        $list["net_sum_last"]+=$list["amend_sum_last"];
        $list["new_rate"] = $this->nowAndLastRate($list["new_sum"],$list["new_sum_last"],true);
        $list["new_n_rate"] = $this->nowAndLastRate($list["new_sum_n"],$list["new_sum_n_last"],true);
        $list["new_month_rate"] = $this->nowAndLastRate($list["new_month_n"],$list["new_month_n_last"],true);
        $list["stop_rate"] = $this->nowAndLastRate($list["stop_sum"],$list["stop_sum_last"],true);
        $list["resume_rate"] = $this->nowAndLastRate($list["resume_sum"],$list["resume_sum_last"],true);
        $list["pause_rate"] = $this->nowAndLastRate($list["pause_sum"],$list["pause_sum_last"],true);
        $list["amend_rate"] = $this->nowAndLastRate($list["amend_sum"],$list["amend_sum_last"],true);
        $list["net_rate"] = $this->nowAndLastRate($list["net_sum"],$list["net_sum_last"],true);

        $list["start_one_gross"] = $bool?$list["start_one_gross"]:ComparisonForm::resetNetOrGross($list["start_one_gross"],$this->day_num);
        $list["start_one_net"] = $bool?$list["start_one_net"]:ComparisonForm::resetNetOrGross($list["start_one_net"],$this->day_num);
        $list["start_two_gross"] = $bool?$list["start_two_gross"]:ComparisonForm::resetNetOrGross($list["start_two_gross"],$this->day_num);
        $list["start_two_net"] = $bool?$list["start_two_net"]:ComparisonForm::resetNetOrGross($list["start_two_net"],$this->day_num);
        $list["two_gross"] = $bool?$list["two_gross"]:ComparisonForm::resetNetOrGross($list["two_gross"],$this->day_num);
        $list["two_net"] = $bool?$list["two_net"]:ComparisonForm::resetNetOrGross($list["two_net"],$this->day_num);
        $list["new_rate"] = $this->nowAndLastRate($list["new_sum"],$list["new_sum_last"],true);
        $list["stop_rate"] = $this->nowAndLastRate($list["stop_sum"],$list["stop_sum_last"],true);
        $list["net_rate"] = $this->nowAndLastRate($list["net_sum"],$list["net_sum_last"],true);
        $list["start_two_gross_rate"] = $this->comparisonRate($newSum,$list["start_two_gross"]);
        $list["start_two_net_rate"] = $this->comparisonRate($list["net_sum"],$list["start_two_net"],"net");
        $list["start_one_gross_rate"] = $this->comparisonRate($newSum,$list["start_one_gross"]);
        $list["start_one_net_rate"] = $this->comparisonRate($list["net_sum"],$list["start_one_net"],"net");
        //$list["two_gross_rate"] = $this->comparisonRate($newSum,$list["two_gross"]);
        //$list["two_net_rate"] = $this->comparisonRate($newSum,$list["two_net"],"net");
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
                $this->resetTdRow($row);
                $uServiceMoney = $row["u_actual_money"];//（2023/5/3改成服务金额）
                //本月停單率
                $htmlList[$row["city"]]["stopRate"]=$row["monthStopRate"];
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
        $lastMonthStr = "（".date("m/d",strtotime($this->last_month_start_date))." ~ ".date("m/d",strtotime($this->last_month_end_date))."）";
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
            array("name"=>Yii::t("summary","New(single) + New(INV)").$monthStr,"background"=>"#F7FD9D",
                "colspan"=>array(
                    array("name"=>$this->comparison_year-1),//对比年份
                    array("name"=>$this->comparison_year),//查询年份
                    array("name"=>Yii::t("summary","YoY change")),//YoY change
                )
            ),//一次性服务+新增（产品）
            array("name"=>Yii::t("summary","Last Month Single + New(INV)").$lastMonthStr,"background"=>"#F7FD9D",
                "colspan"=>array(
                    array("name"=>$this->comparison_year-1),//对比年份
                    array("name"=>$this->comparison_year),//查询年份
                    array("name"=>Yii::t("summary","YoY change")),//YoY change
                )
            ),//上月一次性服务+新增产品
            array("name"=>Yii::t("summary","YTD Stop").$monthStr,"exprName"=>$monthStr,"background"=>"#fcd5b4",
                "colspan"=>array(
                    array("name"=>$this->comparison_year-1),//对比年份
                    array("name"=>$this->comparison_year),//查询年份
                    array("name"=>Yii::t("summary","YoY change")),//YoY change
                    array("name"=>Yii::t("summary","Month Stop Rate")),//月停单率
                )
            ),//YTD终止
            array("name"=>Yii::t("summary","YTD Resume").$monthStr,"exprName"=>$monthStr,"background"=>"#C5D9F1",
                "colspan"=>array(
                    array("name"=>$this->comparison_year-1),//对比年份
                    array("name"=>$this->comparison_year),//查询年份
                    array("name"=>Yii::t("summary","YoY change")),//YoY change
                )
            ),//YTD恢复
            array("name"=>Yii::t("summary","YTD Pause").$monthStr,"exprName"=>$monthStr,"background"=>"#D9D9D9",
                "colspan"=>array(
                    array("name"=>$this->comparison_year-1),//对比年份
                    array("name"=>$this->comparison_year),//查询年份
                    array("name"=>Yii::t("summary","YoY change")),//YoY change
                )
            ),//YTD暂停
            array("name"=>Yii::t("summary","YTD Amend").$monthStr,"exprName"=>$monthStr,"background"=>"#EBF1DE",
                "colspan"=>array(
                    array("name"=>$this->comparison_year-1),//对比年份
                    array("name"=>$this->comparison_year),//查询年份
                    array("name"=>Yii::t("summary","YoY change")),//YoY change
                )
            ),//YTD更改
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
            if($i>=10||in_array($i,array(1,4,7))){
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
            "city_name","u_actual_money","new_sum_last","new_sum","new_rate",
            "new_sum_n_last","new_sum_n","new_n_rate",
            "new_month_n_last","new_month_n","new_month_rate",
            "stop_sum_last","stop_sum","stop_rate","monthStopRate",
            "resume_sum_last","resume_sum","resume_rate",
            "pause_sum_last","pause_sum","pause_rate",
            "amend_sum_last","amend_sum","amend_rate",
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