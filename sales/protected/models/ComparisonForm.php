<?php

class ComparisonForm extends CFormModel
{
	/* User Fields */
	public $week_start_date;
	public $start_date;
	public $end_date;
	public $day_num=0;
	public $comparison_year;
    public $month_start_date;
    public $month_end_date;

	public $data=array();
	public $defaultTable="";

	public $th_sum=1;//所有th的个数

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

    public static function resetNetOrGross($num,$day){
        $num = ($num*12/365)*$day;
        $num = round($num,2);
        return $num;
    }

    public function retrieveData() {
        $data = array();
        $suffix = Yii::app()->params['envSuffix'];
        $this->start_date = empty($this->start_date)?date("Y/01/01"):$this->start_date;
        $this->end_date = empty($this->end_date)?date("Y/m/t"):$this->end_date;
        $this->comparison_year = date("Y",strtotime($this->start_date));
        $this->month_start_date = date("m/d",strtotime($this->start_date));
        $this->month_end_date = date("m/d",strtotime($this->end_date));
        ComparisonForm::setDayNum($this->start_date,$this->end_date,$this->day_num);
        $lastStartDate = ($this->comparison_year-1)."/".$this->month_start_date;
        $lastEndDate = ($this->comparison_year-1)."/".$this->month_end_date;
        $where="(a.status_dt BETWEEN '{$this->start_date}' and '{$this->end_date}')";
        $where.="or (a.status_dt BETWEEN '{$lastStartDate}' and '{$lastEndDate}')";
        $rows = Yii::app()->db->createCommand()
            ->select("a.status_dt,a.status,f.rpt_cat,f.single,a.city,g.rpt_cat as nature_rpt_cat,a.nature_type,a.paid_type,a.amt_paid,a.ctrt_period,a.b4_paid_type,a.b4_amt_paid
            ,b.region,b.name as city_name,c.name as region_name")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("swoper{$suffix}.swo_customer_type f","a.cust_type=f.id")
            ->leftJoin("swoper{$suffix}.swo_nature g","a.nature_type=g.id")
            ->leftJoin("security{$suffix}.sec_city b","a.city=b.code")
            ->leftJoin("security{$suffix}.sec_city c","b.region=c.code")
            ->where("a.status in ('N','T') and ({$where})")
            ->order("a.city")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                //rpt_cat='INV' and single=1的客户服务是产品，所以需要筛选出去
                if($row["rpt_cat"]==="INV"&&intval($row["single"])===1){
                    continue;
                }
                $row["amt_paid"] = is_numeric($row["amt_paid"])?floatval($row["amt_paid"]):0;
                $row["ctrt_period"] = is_numeric($row["ctrt_period"])?floatval($row["ctrt_period"]):0;
                $row["b4_amt_paid"] = is_numeric($row["b4_amt_paid"])?floatval($row["b4_amt_paid"]):0;
                $this->insertDataForRow($row,$data);
            }
        }

        $this->insertUData($this->start_date,$this->end_date,$data);
        $this->insertUData($lastStartDate,$lastEndDate,$data);
        $this->data = $data;
        return true;
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

    private function insertDataForRow($row,&$data){
        $suffix = Yii::app()->params['envSuffix'];
	    $year = intval($row["status_dt"]);//服务的年份
        $city = empty($row["city"])?"none":$row["city"];
        if(!key_exists($city,$data)){
            $setRow = Yii::app()->db->createCommand()->select("*")->from("swoper{$suffix}.swo_comparison_set")
                ->where("comparison_year=:year and city=:city",
                    array(":year"=>$this->comparison_year,":city"=>$city)
                )->queryRow();//查询目标金额
            $data[$city]=array(
                "city"=>$city,
                "city_name"=>$row["city_name"],
                "u_sum_last"=>0,//U系统金额(上一年)
                "u_sum"=>0,//U系统金额
                "stopWeekSum"=>0,//本週停單金額（年金額）
                "stopMonthSum"=>0,//本週停單金額（月金額）
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
                "two_net_rate"=>0
            );
        }
        if($row["paid_type"]=="M"){//月金额
            $money = $row["amt_paid"]*$row["ctrt_period"];//年金額
            $monthMoney = $row["ctrt_period"];//月金額
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
                break;
            case "T"://终止
                if(strtotime($this->week_start_date)<=strtotime($row["status_dt"])){
                    $data[$city]["stopWeekSum"] += $money;
                    $data[$city]["stopMonthSum"] += $monthMoney;
                }
                $money *= -1;
                $data[$city][$stopStr] += $money;
                break;
        }
        $data[$city][$netStr] += $money;
    }

    protected function resetTdRow(&$list,$bool=false){
        $list["two_gross"] = $bool?$list["two_gross"]:ComparisonForm::resetNetOrGross($list["two_gross"],$this->day_num);
        $list["two_net"] = $bool?$list["two_net"]:ComparisonForm::resetNetOrGross($list["two_net"],$this->day_num);
        $list["new_rate"] = $this->nowAndLastRate($list["new_sum"],$list["new_sum_last"]);
        $list["stop_rate"] = $this->nowAndLastRate($list["stop_sum"],$list["stop_sum_last"]);
        $list["net_rate"] = $this->nowAndLastRate($list["net_sum"],$list["net_sum_last"]);
        $list["two_gross_rate"] = $this->comparisonRate($list["new_sum"],$list["two_gross"]);
        $list["two_net_rate"] = $this->comparisonRate($list["net_sum"],$list["two_net"]);
    }

    public static function nowAndLastRate($nowNum,$lastNum){
        if(empty($lastNum)){
            return 0;
        }else{
            $rate = $nowNum-$lastNum;
            $lastNum = $lastNum<0?$lastNum*-1:$lastNum;
            $rate = $rate/$lastNum;
            $rate = round($rate,3)*100;
            return $rate."%";
        }
    }

    public static function comparisonRate($num,$numLast){
        if(empty($numLast)){
            return 0;
        }else{
            $rate = ($num/$numLast);
            $rate = round($rate,3)*100;
            return $rate."%";
        }
    }

    public static function showNum($num){
        if (strpos($num,'%')!==false){
            $number = floatval($num);
            $number=sprintf("%.1f",$number)."%";
        }elseif (is_numeric($num)){
            $number = floatval($num);
            $number=sprintf("%.2f",$number);
        }else{
            $number = $num;
        }
        return $number;
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
                $stopSum = $row["stop_sum"]>=0?$row["stop_sum"]:$row["stop_sum"]*-1;//本月終止金額
                $newSum = $row["new_sum"]-$row["u_sum"];//本月新增金額
                //本月停單率
                $htmlList[$row["city"]]["stopRate"]=$this->comparisonRate($stopSum,$newSum);
                //目標金額
                $htmlList[$row["city"]]["twoGross"]=$row["two_gross"];
                //本周停单金额(年金額)
                $htmlList[$row["city"]]["stopWeekSum"]=$row["stopWeekSum"];
                //本周停单金额(月金額)
                $htmlList[$row["city"]]["stopMonthSum"]=$row["stopMonthSum"];
                $htmlList[$row["city"]]["table"]=$table;
                $this->resetTdRow($row);
                $htmlList[$row["city"]]["table"].='<tr>';
                foreach ($bodyKey as $keyStr){
                    $text = key_exists($keyStr,$row)?$row[$keyStr]:"0";
                    $tdClass =(strpos($text,'%')!==false&&floatval($text)>=100)?"color:green":"";
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

    private function getTopArr(){
        $monthStr = "（{$this->month_start_date} ~ {$this->month_end_date}）";
        $topList=array(
            array("name"=>Yii::t("summary","City"),"rowspan"=>2),//城市
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
        $topList[]=array("name"=>Yii::t("summary","Annual target (base case)"),"background"=>"#DCE6F1",
            "colspan"=>array(
                array("name"=>Yii::t("summary","Gross")),//Gross
                array("name"=>Yii::t("summary","Net")),//Net
            )
        );//年金额目标 (base case)
        $topList[]=array("name"=>Yii::t("summary","Goal degree (base case)"),"background"=>"#DCE6F1",
            "colspan"=>array(
                array("name"=>Yii::t("summary","Gross")),//Gross
                array("name"=>Yii::t("summary","Net")),//Net
            )
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
            if(in_array($i,array(3,6,9,18,19,20,21))){
                $width=90;
            }else{
                $width=80;
            }
            $html.="<th style='height: 0px;line-height: 0px;border: none;overflow: hidden' data-width='{$width}' width='{$width}px'>{$i}</th>";
        }
        return $html."</tr>";
    }

    //获取td对应的键名
    private function getDataAllKeyStr(){
        $bodyKey = array(
            "city_name","new_sum_last","new_sum","new_rate","stop_sum_last","stop_sum","stop_rate",
            "net_sum_last","net_sum","net_rate"
        );
        $bodyKey[]="two_gross";
        $bodyKey[]="two_net";
        $bodyKey[]="two_gross_rate";
        $bodyKey[]="two_net_rate";
        return $bodyKey;
    }
}