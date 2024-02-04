<?php

class KAStatisticForm extends CFormModel
{
	/* User Fields */
	public $search_year;
	public $search_month;
	public $start_date;
	public $end_date;
	public $ka_month;
	public $ka_year;

	public $data=array();

	public $th_sum=0;//所有th的个数

    public $employee_id;
    public $employee_code;
    public $employee_name;

    public $downJsonText='';
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'start_date'=>Yii::t('ka','start date'),
            'end_date'=>Yii::t('ka','end date'),
            'search_year'=>Yii::t('ka','search year'),
            'search_month'=>Yii::t('ka','search month'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('search_year,search_month','safe'),
			array('search_year,search_month','required'),
            array('search_month','validateDate'),
		);
	}

    public function validateDate($attribute, $params) {
	    $this->ka_year = $this->search_year;
	    $this->ka_month = $this->search_month;
	    $timer = strtotime($this->search_year."/".$this->search_month."/01");
	    $this->start_date = date("Y/m/d",$timer);
	    $this->end_date = date("Y/m/t",$timer);
	    if($this->start_date<'2023/06/01'){
            $this->addError($attribute, "查询时间不能小于2023年6月");
        }elseif($this->start_date>date("Y/m/d")){
            $this->addError($attribute, "查询时间不能大于".date("Y年n月"));
        }
    }

    public function setCriteria($criteria)
    {
        if (count($criteria) > 0) {
            foreach ($criteria as $k=>$v) {
                $this->$k = $v;
            }
        }
    }

    public function getCriteria() {
        return array(
            'search_year'=>$this->search_year,
            'search_month'=>$this->search_month
        );
    }

    //获取KA所有员工
    private function getKaManForKaBot(){
        $suffix = Yii::app()->params['envSuffix'];
        if(Yii::app()->user->validFunction('CN15')){
            $whereSql= "";//2023/06/16 改為可以看的所有記錄
        }else{
            $whereSql= "a.kam_id='{$this->employee_id}'";
        }
        $rows = Yii::app()->db->createCommand()
            ->select("h.id,h.code,h.name,h.city")
            ->from("sal_ka_bot a")
            ->leftJoin("hr{$suffix}.hr_employee h","a.kam_id=h.id")
            ->where($whereSql)
            ->group("h.id,h.code,h.name,h.city")
            ->queryAll();
        return $rows?$rows:array();
    }

    //获取未来90天的金额及数量
    private function getAmtNumFor90(){
        $list = array();
        $conList = array(
            "sign_90_num"=>0,//未来90天数量
            "sign_90_amt"=>0,//未来90天金额
        );
        $startDate = date("Y-m-d",strtotime($this->start_date));
        $endDate = date("Y-m-d",strtotime($this->start_date." + 3 months - 1 days"));
        $whereSql = "a.available_date BETWEEN '{$startDate}' and '{$endDate}' ";
        if(Yii::app()->user->validFunction('CN15')){
            $whereSql.= "";//2023/06/16 改為可以看的所有記錄
        }else{
            $whereSql.= " and a.kam_id='{$this->employee_id}'";
        }
        $amtSql = "IFNULL(a.available_amt,0)";
        $rows = Yii::app()->db->createCommand()
            ->select("a.kam_id,
                count(a.id) as sign_90_num,
                sum(if(a.sign_odds<=80,{$amtSql}*0.5,0)) as sign_amt_one,
                sum(if(a.sign_odds>80,{$amtSql},0)) as sign_amt_two
            ")->from("sal_ka_bot a")
            ->leftJoin("sal_ka_link b","a.link_id=b.id")
            ->where($whereSql." and b.rate_num<100 and a.sign_odds>50 and a.sign_odds<100")
            ->group("a.kam_id")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $employee_id = $row["kam_id"];
                if(!key_exists($employee_id,$list)){
                    $list[$employee_id]=$conList;
                }
                $list[$employee_id]["sign_90_num"]+= $row["sign_90_num"];
                $list[$employee_id]["sign_90_amt"]+= $row["sign_amt_one"];
                $list[$employee_id]["sign_90_amt"]+= $row["sign_amt_two"];
            }
        }
        return $list;
    }

    //获取YTD、MTD的金额及数量
    private function getAmtNumForYM(){
        $list = array();
        $conList = array(
            "ytd_num"=>0,
            "ytd_amt"=>0,
            "mtd_num"=>0,
            "mtd_amt"=>0,
        );
        $whereSql = "DATE_FORMAT(f.ava_date,'%Y')='{$this->ka_year}' and b.rate_num=100";
        if(Yii::app()->user->validFunction('CN15')){
            $whereSql.= "";//2023/06/16 改為可以看的所有記錄
        }else{
            $whereSql.= " and a.kam_id='{$this->employee_id}'";
        }
        $searchDate = date("Y/m",strtotime($this->start_date));
        $amtSql = "IFNULL(f.ava_fact_amt,0)";
        $sqlText = Yii::app()->db->createCommand()
            ->select("a.id,a.kam_id,
                sum({$amtSql}) as ytd_amt,
            
                sum(if(DATE_FORMAT(f.ava_date,'%Y/%m')='{$searchDate}',1,0)) as mtd_num,
                sum(if(DATE_FORMAT(f.ava_date,'%Y/%m')='{$searchDate}',{$amtSql},0)) as mtd_amt
            ")->from("sal_ka_bot_ava f")
            ->leftJoin("sal_ka_bot a","f.bot_id=a.id")
            ->leftJoin("sal_ka_link b","a.link_id=b.id")
            ->where($whereSql)
            ->group("a.id,a.kam_id")
            ->getText();
        $countRows = Yii::app()->db->createCommand()
            ->select("bot.kam_id,
                count(bot.id) as ytd_num,
                sum(bot.ytd_amt) as ytd_amt,
                
                sum(if(bot.mtd_num>0,1,0)) as mtd_num,
                sum(bot.mtd_amt) as mtd_amt
            ")->from("({$sqlText}) bot")
            ->group("bot.kam_id")
            ->queryAll();
        if($countRows){
            foreach ($countRows as $countRow){
                $employee_id = $countRow["kam_id"];
                if(!key_exists($employee_id,$list)){
                    $list[$employee_id]=$conList;
                }
                $list[$employee_id]["ytd_num"]+= $countRow["ytd_num"];
                $list[$employee_id]["ytd_amt"]+= $countRow["ytd_amt"];
                $list[$employee_id]["mtd_num"]+= $countRow["mtd_num"];
                $list[$employee_id]["mtd_amt"]+= $countRow["mtd_amt"];
            }
        }
        return $list;
    }

    //获取拜访、报价、本月的金额及数量
    private function getAmtNumForVQS(){
	    $list = array();
	    $conList = array(
            "visit_num"=>0,//拜访数量
            "visit_amt"=>0,//拜访金额
            "quota_num"=>0,//报价数量
            "quota_amt"=>0,//报价金额
            "sign_this_num"=>0,//本月数量
            "sign_this_amt"=>0,//本月金额
        );
        $whereSql = "DATE_FORMAT(a.available_date,'%Y')='{$this->ka_year}'";
        if(Yii::app()->user->validFunction('CN15')){
            $whereSql.= "";//2023/06/16 改為可以看的所有記錄
        }else{
            $whereSql.= " and a.kam_id='{$this->employee_id}'";
        }
        $searchDate = $this->start_date;

        $amtSql = "IFNULL(a.available_amt,0)";
        //$dateIFSql = "a.available_date<='{$this->end_date}' and IFNULL(a.available_date,a.apply_date)>='{$this->start_date}'";
        $rows = Yii::app()->db->createCommand()
            ->select("a.kam_id,
                count(a.id) as visit_num,
                sum({$amtSql}) as visit_amt,
                
                sum(if(b.rate_num>=30,1,0)) as quota_num,
                sum(if(b.rate_num>=30,{$amtSql},0)) as quota_amt,
                
                sum(if(b.rate_num<100 and a.sign_odds>80 and a.sign_odds<100 and DATE_FORMAT(a.available_date,'%Y/%m/01')='{$searchDate}',1,0)) as sign_this_num,
                sum(if(b.rate_num<100 and a.sign_odds>80 and a.sign_odds<100 and DATE_FORMAT(a.available_date,'%Y/%m/01')='{$searchDate}',{$amtSql},0)) as sign_this_amt
                
            ")->from("sal_ka_bot a")
            ->leftJoin("sal_ka_link b","a.link_id=b.id")
            ->where($whereSql)
            ->group("a.kam_id")
            ->queryAll();

        if($rows){
            foreach ($rows as $row){
                $employee_id = $row["kam_id"];
                if(!key_exists($employee_id,$list)){
                    $list[$employee_id]=$conList;
                }
                $list[$employee_id]["visit_num"]+= $row["visit_num"];
                $list[$employee_id]["visit_amt"]+= $row["visit_amt"];
                $list[$employee_id]["quota_num"]+= $row["quota_num"];
                $list[$employee_id]["quota_amt"]+= $row["quota_amt"];
                $list[$employee_id]["sign_this_num"]+= $row["sign_this_num"];
                $list[$employee_id]["sign_this_amt"]+= $row["sign_this_amt"];
            }
        }
        return $list;
    }

    public function retrieveData() {
        $this->data=array();
        $listVQS = $this->getAmtNumForVQS();//获取拜访、报价、本月的金额及数量
        $list90 = $this->getAmtNumFor90();//获取未来90天的金额及数量
        $listYM = $this->getAmtNumForYM();//获取YTD、MTD的金额及数量
        $kaManList = $this->getKaManForKaBot();//KA所有员工
        foreach ($kaManList as $row){
            $temp = $this->getTemp();
            $ka_id = $row["id"];
            $city = $row["city"];
            $temp["employee_id"] = $ka_id;
            $temp["kam_name"] = $row["name"]." ({$row["code"]})";
            $this->addTempForList($temp,$listVQS,$ka_id);
            $this->addTempForList($temp,$list90,$ka_id);
            $this->addTempForList($temp,$listYM,$ka_id);

            $this->data[$city][$ka_id] = $temp;
        }

        $session = Yii::app()->session;
        $session['kAStatistic_c01'] = $this->getCriteria();
        return true;
    }

    private function addTempForList(&$temp,$list,$ka_id){
        if(key_exists($ka_id,$list)){
            foreach ($list[$ka_id] as $key=>$item){
                if(key_exists($key,$temp)){
                    $temp[$key] = $item;
                }
            }
        }
    }

    private function getTemp(){
        return array(
            "employee_id"=>"",//KA_id
            "kam_name"=>"",//KA名称
            "visit_num"=>0,//拜访数量
            "visit_amt"=>0,//拜访金额
            "quota_num"=>0,//报价数量
            "quota_amt"=>0,//报价金额
            "quota_rate"=>"",//转化率（报价金额/拜访金额）
            "sign_90_num"=>0,//未来90天数量
            "sign_90_amt"=>0,//未来90天金额
            "sign_this_num"=>0,//本月数量
            "sign_this_amt"=>0,//本月金额
            "this_rate"=>"",//90天转化率（本月金额/90天金额）
            "ytd_num"=>0,//ytd数量
            "ytd_amt"=>0,//ytd金额
            "ytd_amt_rate"=>"",//金额转化率（ytd_amt/拜访金额）
            "ytd_num_rate"=>"",//数量转化率（ytd_num/拜访数量）
            "mtd_num"=>0,//mtd数量
            "mtd_amt"=>0,//mtd金额
        );
    }

    public static function getRateForNumber($number){
        $rate = "";
        if(is_numeric($number)){
            $rate = $number*100;
            $rate = round($rate);
            $rate = "".$rate."%";
        }
        return $rate;
    }

    protected function resetTdRow(&$list,$bool=false){
        $list["quota_rate"]=empty($list["visit_amt"])?0:($list["quota_amt"]/$list["visit_amt"]);
        $list["quota_rate"] = self::getRateForNumber($list["quota_rate"]);
        $list["this_rate"]=empty($list["sign_90_amt"])?0:($list["sign_this_amt"]/$list["sign_90_amt"]);
        $list["this_rate"] = self::getRateForNumber($list["this_rate"]);
        $list["ytd_amt_rate"]=empty($list["visit_amt"])?0:($list["ytd_amt"]/$list["visit_amt"]);
        $list["ytd_amt_rate"] = self::getRateForNumber($list["ytd_amt_rate"]);
        $list["ytd_num_rate"]=empty($list["visit_num"])?0:($list["ytd_num"]/$list["visit_num"]);
        $list["ytd_num_rate"] = self::getRateForNumber($list["ytd_num_rate"]);
    }

    //顯示提成表的表格內容
    public function kAStatisticHtml(){
        $html= '<table id="kAStatistic" class="table table-fixed table-condensed table-bordered table-hover">';
        $html.=$this->tableTopHtml();
        $html.=$this->tableBodyHtml();
        $html.=$this->tableFooterHtml();
        $html.="</table>";
        return $html;
    }

    private function getTopArr(){
        $topList=array(
            array("name"=>Yii::t("ka","KAM"),"background"=>"#305496","color"=>"#ffffff","rowspan"=>3),//ka销售
            array("name"=>Yii::t("ka","Total data"),"background"=>"#305496","color"=>"#ffffff",//全部数据
                "colspan"=>array(
                    array(
                        "name"=>Yii::t("ka","Visiting stage"),//拜访阶段
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity")),//数量
                            array("name"=>Yii::t("ka","Contract amt")),//合同金额
                        )
                    ),
                    array(
                        "name"=>Yii::t("ka","Quotation stage"),//报价阶段
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity")),//数量
                            array("name"=>Yii::t("ka","Contract amt")),//合同金额
                        )
                    ),
                    array(
                        "name"=>Yii::t("ka","Conversion rate"),//转化率
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","Quotation/Visit")),//报价/拜访
                        )
                    ),
                )
            ),//全部数据
            array("name"=>Yii::t("ka","amount for next 90 days"),"background"=>"#2A6BA4","color"=>"#ffffff",//未来90天加权报价金额
                "colspan"=>array(
                    array(
                        "name"=>$this->search_month.Yii::t("ka"," month"),//月份
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity")),//数量
                            array("name"=>Yii::t("ka","Contract amt")),//合同金额
                        )
                    )
                )
            ),//未来90天加权报价金额
            array("name"=>Yii::t("ka","amount for this month"),"background"=>"#2A6BA4","color"=>"#ffffff",//本月可实现销售金额
                "colspan"=>array(
                    array(
                        "name"=>$this->search_month.Yii::t("ka"," month"),//月份
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity")),//数量
                            array("name"=>Yii::t("ka","Contract amt")),//合同金额
                        )
                    )
                )
            ),//本月可实现销售金额
            array("name"=>Yii::t("ka","90 Day rate"),"background"=>"#2A6BA4","color"=>"#ffffff",//90天转化率
                "colspan"=>array(
                    array(
                        "name"=>$this->search_month.Yii::t("ka"," month"),//月份
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","(Actual transactions this month/90 day weighted)")),//（本月实际成交/90天加权）
                        )
                    ),
                    /*
                    array(
                        "name"=>Yii::t("ka","(Actual transactions this month/90 day weighted)"),//（本月实际成交/90天加权）
                        "rowspan"=>2
                    )
                    */
                )
            ),//90天转化率
            array("name"=>Yii::t("ka","Sales performance"),"background"=>"#4472C4","color"=>"#ffffff",//每月KA销售业绩
                "colspan"=>array(
                    array(
                        "name"=>Yii::t("ka","YTD"),//YTD
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity")),//数量
                            array("name"=>Yii::t("ka","Contract amt")),//合同金额
                        )
                    ),
                    array(
                        "name"=>Yii::t("ka","YTD rate"),//YTD转化率
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","YTD amt rate")),//金额转化（成交金额/拜访阶段金额）
                            array("name"=>Yii::t("ka","YTD num rate")),//数量转化（成交数量/拜访数量）
                        )
                    ),
                    array(
                        "name"=>$this->ka_month.Yii::t("ka","Month MTD"),//5月MTD
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity")),//数量
                            array("name"=>Yii::t("ka","Contract amt")),//合同金额
                        )
                    ),
                )
            ),//每月KA销售业绩
        );
        return $topList;
    }

    //顯示提成表的表格內容（表頭）
    private function tableTopHtml(){
        $topList = self::getTopArr();
        $trOne="";
        $trTwo="";
        $trThree="";
        $html="<thead>";
        foreach ($topList as $list){
            $clickName=$list["name"];
            $colList=key_exists("colspan",$list)?$list['colspan']:array();
            $style = "";
            $colNum=0;
            if(key_exists("background",$list)){
                $style.="background:{$list["background"]};";
            }
            if(key_exists("color",$list)){
                $style.="color:{$list["color"]};";
            }
            if(!empty($colList)){
                foreach ($colList as $col){
                    $threeCol=key_exists("colspan",$col)?$col['colspan']:array();
                    if(!empty($threeCol)){
                        foreach ($threeCol as $three){
                            $this->th_sum++;
                            $trThree.="<th style='{$style}'><span>".$three["name"]."</span></th>";

                        }
                    }else{
                        $this->th_sum++;
                    }
                    $threeColNum=count($threeCol);
                    $colNum+=$threeColNum;
                    $threeColNum = empty($threeColNum)?1:$threeColNum;
                    //$this->th_sum++;

                    if(key_exists("rowspan",$col)){
                        $trTwo.="<th colspan='{$threeColNum}' rowspan='{$col["rowspan"]}' style='{$style}'><span>".$col["name"]."</span></th>";
                    }else{
                        $trTwo.="<th colspan='{$threeColNum}' style='{$style}'><span>".$col["name"]."</span></th>";
                    }
                }
            }
            $colNum = empty($colNum)?1:$colNum;
            $trOne.="<th style='{$style}' colspan='{$colNum}'";
            if(key_exists("rowspan",$list)){
                $trOne.=" rowspan='{$list["rowspan"]}'";
            }
            if(key_exists("startKey",$list)){
                $trOne.=" data-key='{$list['startKey']}'";
            }
            $trOne.=" ><span>".$clickName."</span></th>";
        }
        $html.=$this->tableHeaderWidth();//設置表格的單元格寬度
        $this->th_sum++;
        $html.="<tr>{$trOne}</tr><tr>{$trTwo}</tr><tr>{$trThree}</tr>";
        $html.="</thead>";
        return $html;
    }

    //設置表格的單元格寬度
    private function tableHeaderWidth(){
        $html="<tr>";
        for($i=0;$i<=$this->th_sum;$i++){
            if($i==0){
                $width = 120;
            }else{
                $width=90;
            }
            $html.="<th class='header-width' data-width='{$width}' width='{$width}px'>{$i}</th>";
        }
        return $html."</tr>";
    }

    public function tableBodyHtml(){
        $html="";
        if(!empty($this->data)){
            $this->downJsonText=array();
            $html.="<tbody>";
            $html.=$this->showServiceHtml($this->data);
            $html.="</tbody>";
            $this->downJsonText=json_encode($this->downJsonText);
        }
        return $html;
    }
    //获取td对应的键名
    private function getDataAllKeyStr(){
        $bodyKey = array(
            "kam_name","visit_num","visit_amt","quota_num","quota_amt","quota_rate",
            "sign_90_num","sign_90_amt","sign_this_num","sign_this_amt","this_rate",
            "ytd_num","ytd_amt","ytd_amt_rate","ytd_num_rate","mtd_num","mtd_amt"
        );
        return $bodyKey;
    }

    public static function showNum($num){
        $pre="";
        if (strpos($num," +")!==false){
            $pre=" +";
            $num = end(explode(" +",$num));
        }
        if (is_numeric($num)){
            $number = floatval($num);
            //$number=sprintf("%.2f",$number);
        }else{
            $number = $num;
        }
        return $pre.$number;
    }

    //將城市数据寫入表格
    private function showServiceHtml($data){
        $bodyKey = $this->getDataAllKeyStr();
        $clickTdList = $this->getClickTdList();
        $html="";
        if(!empty($data)){
            $allRow = [];//总计(所有地区)
            foreach ($data as $city=>$row){
                $regionRow = [];//地区汇总
                foreach ($row as $list){
                    $id = $list["employee_id"];
                    $this->resetTdRow($list);
                    $html.="<tr>";
                    foreach ($bodyKey as $keyStr){
                        if(!key_exists($keyStr,$regionRow)){
                            $regionRow[$keyStr]=0;
                        }
                        if(!key_exists($keyStr,$allRow)){
                            $allRow[$keyStr]=0;
                        }
                        $text = key_exists($keyStr,$list)?$list[$keyStr]:"0";
                        $regionRow[$keyStr]+=is_numeric($text)?floatval($text):0;
                        $allRow[$keyStr]+=is_numeric($text)?floatval($text):0;
                        $text = self::showNum($text);
                        $this->downJsonText["excel"][$city][$id][]=$text;
                        $class="";
                        $title="";
                        if(key_exists($keyStr,$clickTdList)){
                            $class.=" td_detail";
                            $title=$clickTdList[$keyStr];
                        }
                        $html.="<td class='{$class}' data-title='{$title}' data-type='{$keyStr}' data-employee_id='{$list['employee_id']}'>";
                        $html.="<span>{$text}</span></td>";
                        $html.="</td>";
                    }
                    $html.="</tr>";
                }
                //地区汇总
                //$html.=$this->printTableTr($regionRow,$bodyKey);
                //$html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
            }
            //所有汇总
            $allRow["city"]="_ALL";
            $allRow["kam_name"]=Yii::t("ka","all total");
            $html.=$this->printTableTr($allRow,$bodyKey);
            $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
            $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
        }
        return $html;
    }

    protected function printTableTr($data,$bodyKey){
        $this->resetTdRow($data,true);
        $html="<tr class='tr-end click-tr'>";
        foreach ($bodyKey as $keyStr){
            $text = key_exists($keyStr,$data)?$data[$keyStr]:"0";
            $tdClass = ComparisonForm::getTextColorForKeyStr($text,$keyStr);
            $text = self::showNum($text);
            $this->downJsonText["excel"][$data['city']]["count"][]=$text;
            $html.="<td class='{$tdClass}' style='font-weight: bold'><span>{$text}</span></td>";
        }
        $html.="</tr>";
        return $html;
    }

    public function tableFooterHtml(){
        $html="<tfoot>";
        $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
        $html.="</tfoot>";
        return $html;
    }

    //下載
    public function downExcel($excelData){
        if(!is_array($excelData)){
            $excelData = json_decode($excelData,true);
            $excelData = empty($excelData)?array():$excelData;
            $excelData = key_exists("excel",$excelData)?$excelData["excel"]:array();
        }
        $this->validateDate("","");
        $headList = $this->getTopArr();
        $excel = new DownKAExcel();
        $excel->colTwo=1;
        $excel->SetHeaderTitle(Yii::t("app","KA Statistic"));
        $excel->SetHeaderString($this->start_date." ~ ".$this->end_date);
        $excel->init();
        $excel->setKAHeader($headList);
        $excel->setKAData($excelData);
        $excel->outExcel(Yii::t("app","KA Statistic"));
    }

    //获取年份
    public static function getYearList(){
        $year = date("Y");
        $list = array();
        for ($i=$year-4;$i<=$year+1;$i++){
            if($i>2022){
                $list[$i] = $i.Yii::t("ka"," year");
            }
        }
        return $list;
    }
    //获取月份
    public static function getMonthList(){
        $list = array();
        for ($i=1;$i<=12;$i++){
            $list[$i] = $i.Yii::t("ka"," month");
        }
        return $list;
    }

    //顯示表格內的數據來源
    public function ajaxDetailForHtml(){
        $suffix = Yii::app()->params['envSuffix'];
        $employee_id = key_exists("employee_id",$_GET)?$_GET["employee_id"]:0;
        $this->search_year = key_exists("year",$_GET)?$_GET["year"]:0;
        $this->search_month = key_exists("month",$_GET)?$_GET["month"]:0;
        $this->ka_year = $this->search_year;
        $this->ka_month = $this->search_month;
        $type = key_exists("type",$_GET)?$_GET["type"]:0;
        $this->validateDate("","");

        $row = Yii::app()->db->createCommand()->select("id,name,code")->from("hr$suffix.hr_employee")
            ->where("id=:id",array(":id"=>$employee_id))->queryRow();
        $this->employee_id = $row["id"];
        $this->employee_code = $row["code"];
        $this->employee_name = $row["name"];

        if(!$row||!key_exists($type,$this->getClickTdList())){
            return "<p>数据异常，请刷新重试</p>";
        }
        $value = $type."_table";
        $html = "<table class='table table-bordered table-striped table-hover'>";
        $html.=$this->$value();
        $html.="</table>";
        return $html;
    }

    //需要顯示表格詳情的欄位
    private function getClickTdList(){
        return array(
            "visit_num"=>Yii::t("ka","Visiting stage"),//拜访阶段
            "quota_num"=>Yii::t("ka","Quotation stage"),//报价阶段
            "sign_90_num"=>Yii::t("ka","amount for next 90 days"),//未来90天加权报价金额
            "sign_this_num"=>Yii::t("ka","amount for this month"),//本月可实现销售金额
            "ytd_num"=>Yii::t("ka","YTD"),//YTD
            "mtd_num"=>$this->search_month.Yii::t("ka","Month MTD"),//月MTD
        );
    }

    //未来90天加权报价金额(签约概率>=51)
    private function sign_90_num_table(){
        $startDate = date("Y-m-d",strtotime($this->start_date));
        $endDate = date("Y-m-d",strtotime($this->start_date." + 3 months - 1 days"));
        $whereSql = "a.available_date BETWEEN '{$startDate}' and '{$endDate}' ";
        $whereSql.= " and a.kam_id='{$this->employee_id}' and g.rate_num<100 and a.sign_odds>50 and a.sign_odds<100 ";

        $amtSql = "IFNULL(a.available_amt,0)";
        //$dateIFSql = "a.available_date<='{$this->end_date}' and IFNULL(a.available_date,a.apply_date)>='{$this->start_date}'";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,a.available_amt,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,g.rate_num
                ")->from("sal_ka_bot a")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->order("if(g.rate_num>0,a.available_date,-1) desc,a.available_date desc")
            ->queryAll();
        return $this->staticTableBodyTwo($rows);
    }

    //本月可实现销售金额(签约概率>=81)
    private function sign_this_num_table(){
        $searchDate = date("Y/m",strtotime($this->start_date));
        $whereSql = "DATE_FORMAT(a.available_date,'%Y/%m')='{$searchDate}' and g.rate_num<100 and a.sign_odds>80 and a.sign_odds<100 ";
        $whereSql.= " and a.kam_id='{$this->employee_id}'";

        $amtSql = "IFNULL(a.available_amt,0)";
        //$dateIFSql = "a.available_date<='{$this->end_date}' and IFNULL(a.available_date,a.apply_date)>='{$this->start_date}'";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,a.available_amt,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,g.rate_num
                ")->from("sal_ka_bot a")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->order("if(g.rate_num>0,a.available_date,-1) desc,a.available_date desc")
            ->queryAll();
        return $this->staticTableBody($rows);
    }

    //拜访阶段詳情
    private function visit_num_table(){
        $whereSql = "DATE_FORMAT(a.available_date,'%Y')='{$this->ka_year}'";
        $whereSql.= " and a.kam_id='{$this->employee_id}'";
        $searchDate = $this->start_date;

        $amtSql = "IFNULL(a.available_amt,0)";
        //$dateIFSql = "a.available_date<='{$this->end_date}' and IFNULL(a.available_date,a.apply_date)>='{$this->start_date}'";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,a.available_amt,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,g.rate_num
                ")->from("sal_ka_bot a")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->order("if(g.rate_num>0,a.available_date,-1) desc,a.available_date desc")
            ->queryAll();
        return $this->staticTableBody($rows);
    }

    //报价阶段詳情
    private function quota_num_table(){
        $whereSql = "DATE_FORMAT(a.available_date,'%Y')='{$this->ka_year}'";
        $whereSql.= " and a.kam_id='{$this->employee_id}' and g.rate_num>=30";

        $amtSql = "IFNULL(a.available_amt,0)";
        //$dateIFSql = "a.available_date<='{$this->end_date}' and IFNULL(a.available_date,a.apply_date)>='{$this->start_date}'";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,a.available_amt,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,g.rate_num
                ")->from("sal_ka_bot a")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->order("if(g.rate_num>0,a.available_date,-1) desc,a.available_date desc")
            ->queryAll();
        return $this->staticTableBody($rows);
    }

    //YTD
    private function ytd_num_table(){
        $whereSql = "DATE_FORMAT(f.ava_date,'%Y')='{$this->ka_year}'";
        $whereSql.= " and a.kam_id='{$this->employee_id}' and g.rate_num=100";

        $amtSql = "IFNULL(f.ava_fact_amt,0)";
        $selectText="a.id,a.kam_id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,
        a.customer_name,a.contact_user,g.pro_name,g.rate_num";
        $rows = Yii::app()->db->createCommand()
            ->select("{$selectText},
            CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,
            sum({$amtSql}) as available_amt")
            ->from("sal_ka_bot_ava f")
            ->leftJoin("sal_ka_bot a","f.bot_id=a.id")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->group($selectText)
            ->queryAll();
        return $this->staticTableBodyThree($rows);
    }

    private function getBotAvaAmt($row,$type="year"){
        $amt = 0;
        if (isset($row["id"])){
            $whereSql = "a.bot_id='{$row['id']}'";
            if($type=="month"){
                $searchDate = date("Y/m",strtotime($this->start_date));
                $whereSql.=" and DATE_FORMAT(a.ava_date,'%Y/%m')='{$searchDate}'";
            }else{
                $whereSql.=" and DATE_FORMAT(a.ava_date,'%Y')='{$this->ka_year}'";
            }
            $amt = Yii::app()->db->createCommand()
                ->select("sum(IFNULL(a.ava_fact_amt,0)) as amt_money")
                ->from("sal_ka_bot_ava a")
                ->where($whereSql)
                ->queryScalar();
        }
        return $amt;
    }

    //mtd_num
    private function mtd_num_table(){
        $searchDate = date("Y/m",strtotime($this->start_date));
        $whereSql = "DATE_FORMAT(f.ava_date,'%Y/%m')='$searchDate'";
        $whereSql.= " and a.kam_id='{$this->employee_id}' and g.rate_num=100";

        $amtSql = "IFNULL(f.ava_fact_amt,0)";
        $selectText="a.id,a.kam_id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,
        a.customer_name,a.contact_user,g.pro_name,g.rate_num";
        $rows = Yii::app()->db->createCommand()
            ->select("{$selectText},
            CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,
            sum({$amtSql}) as available_amt")
            ->from("sal_ka_bot_ava f")
            ->leftJoin("sal_ka_bot a","f.bot_id=a.id")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->group($selectText)
            ->queryAll();
        return $this->staticTableBodyThree($rows);
    }

    private function staticTableBody($rows){
        $html = "<thead>";
        $html.="<tr>";
        $html.="<th width='120px'>预估可成交日期</th>";
        $html.="<th width='95px'>录入日期</th>";
        $html.="<th width='110px'>客户编号</th>";
        $html.="<th>客户公司</th>";
        $html.="<th width='80px'>签约概率</th>";
        $html.="<th>沟通阶段</th>";
        $html.="<th width='120px'>预估可成交金额</th>";
        $html.="<th width='1px'></th>";
        $html.="</tr>";
        $html.= "</thead><tbody>";
        if($rows){
            $sumAmt = 0;
            $sumNum = 0;
            foreach ($rows as $row){
                $row['available_amt'] = empty($row['available_amt'])?0:floatval($row['available_amt']);
                $row['rate_num'] = floatval($row['rate_num']);
                $sumAmt+=$row['available_amt'];
                $sumNum++;
                $link = self::drawEditButton('KA01', 'kABot/edit', 'kABot/view', array('index'=>$row['id']));
                $html.="<tr data-id='{$row["id"]}'>";
                $html.="<td>".General::toDate($row['available_date'])."</td>";
                $html.="<td>".General::toDate($row['apply_date'])."</td>";
                $html.="<td>".$row['customer_no']."</td>";
                $html.="<td>".$row['customer_name']."</td>";
                $html.="<td>".KABotForm::getSignOddsListForId($row['sign_odds'],true)."</td>";
                $html.="<td>".$row['link_name']."</td>";
                $html.="<td class='text-right'>".$row['available_amt']."</td>";
                $html.="<td>".$link."</td>";
                $html.="</tr>";
            }
            $html.="<tr>";
            $html.="<td colspan='2' class='text-right'>总数量:</td><td colspan='2'>{$sumNum}</td>";
            $html.="<td colspan='2' class='text-right'>总金额:</td><td colspan='2'>{$sumAmt}</td>";
            $html.="</tr>";
            $html.="<tr><td colspan='8' class='text-right text-danger'>&nbsp;</td></tr>";
        }else{
            $html.="<tr><td colspan='8'>无</td></tr>";
        }
        $html.= "</tbody>";
        return $html;
    }

    //未来90天加权报价金额
    private function staticTableBodyTwo($rows){
        $html = "<thead>";
        $html.="<tr>";
        $html.="<th width='120px'>预估可成交日期</th>";
        $html.="<th width='95px'>录入日期</th>";
        $html.="<th width='110px'>客户编号</th>";
        $html.="<th>客户公司</th>";
        $html.="<th width='80px'>签约概率</th>";
        $html.="<th>沟通阶段</th>";
        $html.="<th width='120px'>预估可成交金额</th>";
        $html.="<th width='95px'>统计金额</th>";
        $html.="<th width='1px'></th>";
        $html.="</tr>";
        $html.= "</thead><tbody>";
        if($rows){
            $sumAmt = 0;
            $sumNum = 0;
            foreach ($rows as $row){
                $row['sign_odds'] = empty($row['sign_odds'])?0:floatval($row['sign_odds']);
                $row['available_amt'] = empty($row['available_amt'])?0:floatval($row['available_amt']);
                $row['rate_num'] = floatval($row['rate_num']);
                $amt_me = $row['available_amt'];
                $amt_me*= $row['sign_odds']>80?1:0.5;
                $sumAmt+=$amt_me;
                $sumNum++;
                $link = self::drawEditButton('KA01', 'kABot/edit', 'kABot/view', array('index'=>$row['id']));
                $html.="<tr data-id='{$row["id"]}'>";
                $html.="<td>".General::toDate($row['available_date'])."</td>";
                $html.="<td>".General::toDate($row['apply_date'])."</td>";
                $html.="<td>".$row['customer_no']."</td>";
                $html.="<td>".$row['customer_name']."</td>";
                $html.="<td>".KABotForm::getSignOddsListForId($row['sign_odds'],true)."</td>";
                $html.="<td>".$row['link_name']."</td>";
                $html.="<td class='text-right'>".$row['available_amt']."</td>";
                $html.="<td class='text-right'>".$amt_me."</td>";
                $html.="<td>".$link."</td>";
                $html.="</tr>";
            }
            $html.="<tr>";
            $html.="<td colspan='2' class='text-right'>总数量:</td><td colspan='2'>{$sumNum}</td>";
            $html.="<td colspan='3' class='text-right'>总金额:</td><td colspan='2'>{$sumAmt}</td>";
            $html.="</tr>";
            $html.="<tr><td colspan='9' class='text-right text-danger'>&nbsp;</td></tr>";
        }else{
            $html.="<tr><td colspan='9'>无</td></tr>";
        }
        $html.= "</tbody>";
        return $html;
    }

    //YTD、MTD
    private function staticTableBodyThree($rows){
        $html = "<thead>";
        $html.="<tr>";
        $html.="<th width='120px'>预估可成交日期</th>";
        $html.="<th width='95px'>录入日期</th>";
        $html.="<th width='110px'>客户编号</th>";
        $html.="<th>客户公司</th>";
        $html.="<th width='80px'>签约概率</th>";
        $html.="<th>沟通阶段</th>";
        $html.="<th width='120px'>计算金额</th>";
        $html.="<th width='1px'></th>";
        $html.="</tr>";
        $html.= "</thead><tbody>";
        if($rows){
            $sumAmt = 0;
            $sumNum = 0;
            foreach ($rows as $row){
                $row['available_amt'] = empty($row['available_amt'])?0:floatval($row['available_amt']);
                $row['rate_num'] = floatval($row['rate_num']);
                $sumAmt+=$row['available_amt'];
                $sumNum++;
                $link = self::drawEditButton('KA01', 'kABot/edit', 'kABot/view', array('index'=>$row['id']));
                $html.="<tr data-id='{$row["id"]}'>";
                $html.="<td>".General::toDate($row['available_date'])."</td>";
                $html.="<td>".General::toDate($row['apply_date'])."</td>";
                $html.="<td>".$row['customer_no']."</td>";
                $html.="<td>".$row['customer_name']."</td>";
                $html.="<td>".KABotForm::getSignOddsListForId($row['sign_odds'],true)."</td>";
                $html.="<td>".$row['link_name']."</td>";
                $html.="<td class='text-right'>".$row['available_amt']."</td>";
                $html.="<td>".$link."</td>";
                $html.="</tr>";
            }
            $html.="<tr>";
            $html.="<td colspan='2' class='text-right'>总数量:</td><td colspan='2'>{$sumNum}</td>";
            $html.="<td colspan='2' class='text-right'>总金额:</td><td colspan='2'>{$sumAmt}</td>";
            $html.="</tr>";
            $html.="<tr><td colspan='8' class='text-right text-danger'>&nbsp;</td></tr>";
        }else{
            $html.="<tr><td colspan='8'>无</td></tr>";
        }
        $html.= "</tbody>";
        return $html;
    }


    public static function drawEditButton($access, $writeurl, $readurl, $param) {
        $rw = Yii::app()->user->validRWFunction($access);
        $url = $rw ? $writeurl : $readurl;
        $icon = $rw ? "glyphicon glyphicon-pencil" : "glyphicon glyphicon-eye-open";
        $lnk=Yii::app()->createUrl($url,$param);

        return "<a href=\"$lnk\" target='_blank'><span class=\"$icon\"></span></a>";
    }
}