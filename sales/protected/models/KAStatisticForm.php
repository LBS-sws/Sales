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

    private function getSignList(){
	    $list = array();
	    $sign_90_Rows = $this->sign_90_num_list();
	    $sign_this_Rows = $this->sign_this_num_list();
	    $conList = array(
            "sign_90_num"=>0,//未来90天数量
            "sign_90_amt"=>0,//未来90天金额
            "sign_this_num"=>0,//本月数量
            "sign_this_amt"=>0,//本月金额
        );

	    foreach ($sign_90_Rows as $row){
            $employee_id = $row["kam_id"];
            if(!key_exists($employee_id,$list)){
                $list[$employee_id]=$conList;
            }
            if($row["old_sign_odds"]>=81){
                $list[$employee_id]["sign_90_num"]++;
                $list[$employee_id]["sign_90_amt"]+=$row["old_sum_amt"]*1;//2023-07-10由0.8改成1
            }elseif ($row["old_sign_odds"]>=51){
                $list[$employee_id]["sign_90_num"]++;
                $list[$employee_id]["sign_90_amt"]+=$row["old_sum_amt"]*0.5;
            }
        }
	    foreach ($sign_this_Rows as $row){
            $employee_id = $row["kam_id"];
            if(!key_exists($employee_id,$list)){
                $list[$employee_id]=$conList;
            }
            if($row["old_sign_odds"]>=81){
                $list[$employee_id]["sign_this_num"]++;
                $list[$employee_id]["sign_this_amt"]+=$row["old_sum_amt"];
            }
        }
        return $list;
    }

    public function retrieveData() {
        $this->data=array();
        $suffix = Yii::app()->params['envSuffix'];
        $city_allow = Yii::app()->user->city_allow();
        $whereSql = "DATE_FORMAT(a.apply_date,'%Y')='{$this->ka_year}'";
        if(Yii::app()->user->validFunction('CN15')){
            //$whereSql.= " and (a.kam_id='{$this->employee_id}' or h.city in ({$city_allow}))";
            $whereSql = "";//2023/06/16 改為可以看的所有記錄
        }else{
            $whereSql.= " and a.kam_id='{$this->employee_id}'";
        }
        $rows = Yii::app()->db->createCommand()
            ->select("DATE_FORMAT(a.apply_date,'%Y') as apply_year,h.id,h.code,h.name,h.city,
                sum(if(DATE_FORMAT(a.apply_date,'%Y/%m/%d')<='{$this->end_date}',1,0)) as visit_num,sum(if(DATE_FORMAT(a.apply_date,'%Y/%m/%d')<='{$this->end_date}' and b.rate_num>0,a.sum_amt,0)) as visit_amt,
                sum(if(b.rate_num>=30,1,0)) as quota_num,sum(if(b.rate_num>=30,a.sum_amt,0)) as quota_amt,
                sum(if(b.rate_num>=100,1,0)) as ytd_num,sum(if(b.rate_num>=100,a.sum_amt,0)) as ytd_amt
            ")->from("sal_ka_bot a")
            ->leftJoin("sal_ka_link b","a.link_id=b.id")
            ->leftJoin("hr{$suffix}.hr_employee h","a.kam_id=h.id")
            ->where($whereSql)
            ->group("apply_year,h.id,h.code,h.name,h.city")
            ->queryAll();
        $signAllList = $this->getSignList();
        if($rows){
            foreach ($rows as $row){
                if(key_exists($row["id"],$signAllList)){
                    $signList = $signAllList[$row["id"]];
                }else{
                    $signList = array(
                        "sign_90_num"=>0,//未来90天数量
                        "sign_90_amt"=>0,//未来90天金额
                        "sign_this_num"=>0,//本月数量
                        "sign_this_amt"=>0,//本月金额
                    );
                }
                $mtdRow = $this->getMtdRow($row);
                $list = array(
                    "employee_id"=>$row["id"],
                    "kam_name"=>$row["name"]." ({$row["code"]})",
                    "visit_num"=>$row["visit_num"],
                    "visit_amt"=>$row["visit_amt"],
                    "quota_num"=>$row["quota_num"],
                    "quota_amt"=>$row["quota_amt"],
                    "sign_90_num"=>$signList["sign_90_num"],//未来90天数量
                    "sign_90_amt"=>$signList["sign_90_amt"],//未来90天金额
                    "sign_this_num"=>$signList["sign_this_num"],//本月数量
                    "sign_this_amt"=>$signList["sign_this_amt"],//本月金额
                    "ytd_num"=>$row["ytd_num"],
                    "ytd_amt"=>$row["ytd_amt"],
                    "mtd_num"=>empty($mtdRow["mtd_num"])?0:$mtdRow["mtd_num"],
                    "mtd_amt"=>empty($mtdRow["mtd_amt"])?0:$mtdRow["mtd_amt"],
                );
                $this->data[$row["city"]][$row["id"]] = $list;
            }
        }

        $session = Yii::app()->session;
        $session['kAStatistic_c01'] = $this->getCriteria();
        return true;
    }

    private function getMtdRow($row){
        $row = Yii::app()->db->createCommand()
            ->select("count(a.id) as mtd_num,sum(a.sum_amt) as mtd_amt")
            ->from("sal_ka_bot a")
            ->leftJoin("sal_ka_link b","a.link_id=b.id")
            ->where("a.kam_id=:id and b.rate_num>=100 and a.sign_date between '{$this->start_date}' and '{$this->end_date}'",
                array(":id"=>$row["id"])
            )->queryRow();
        if($row){
            return $row;
        }else{
            return array("mtd_num"=>0,"mtd_amt"=>0);
        }
    }

    protected function resetTdRow(&$list,$bool=false){
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
                    foreach ($threeCol as $three){
                        $this->th_sum++;
                        $trThree.="<th style='{$style}'><span>".$three["name"]."</span></th>";

                    }
                    $threeColNum=count($threeCol);
                    $colNum+=$threeColNum;
                    $threeColNum = empty($threeColNum)?1:$threeColNum;
                    //$this->th_sum++;
                    $trTwo.="<th colspan='{$threeColNum}' style='{$style}'><span>".$col["name"]."</span></th>";
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
            "kam_name","visit_num","visit_amt","quota_num","quota_amt",
            "sign_90_num","sign_90_amt","sign_this_num","sign_this_amt",
            "ytd_num","ytd_amt","mtd_num","mtd_amt"
        );
        return $bodyKey;
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
    private function sign_90_num_list($employee_id=""){
        $startDate = date("Y/m/01",strtotime($this->search_year."/{$this->search_month}/01"));
        $endDate = date("Y/m/d",strtotime("{$startDate} + 1 months - 1 day"));
        $whereSql = "and a.lcd BETWEEN '{$startDate}' and '{$endDate}'";
        if(!empty($employee_id)){
            $whereSql.= " and b.kam_id='{$employee_id}'";
        }
        $historySql = Yii::app()->db->createCommand()
            ->select("a.bot_id,b.kam_id,max(a.lcd) as lcd")
            ->from("sal_ka_bot_history a")
            ->leftJoin("sal_ka_bot b","a.bot_id=b.id")
            ->where("a.espe_type=1 {$whereSql}")
            ->group("a.bot_id,b.kam_id")
            ->getText();
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.kam_id,a.sign_odds,a.follow_date,a.apply_date,a.customer_no,a.customer_name,a.sum_amt,
                g.sign_odds as old_sign_odds,g.sum_amt as old_sum_amt
                ")
            ->from("($historySql) f")
            ->leftJoin("sal_ka_bot a","f.bot_id=a.id")
            ->leftJoin("sal_ka_bot_history g","g.bot_id=f.bot_id and g.lcd=f.lcd")
            ->queryAll();
        $list = array();
        if($rows){
            foreach ($rows as $row){
                if($row["old_sign_odds"]>=51){
                    $list[]=$row;
                }
            }
        }
        return $list;
    }

    //未来90天加权报价金额(签约概率>=51)
    private function sign_90_num_table(){
        $list = $this->sign_90_num_list($this->employee_id);
        return $this->staticTableBodyTwo($list);
    }

    //本月可实现销售金额(签约概率>=81)
    private function sign_this_num_list($employee_id=""){
        $startDate = date("Y/m/01",strtotime($this->search_year."/{$this->search_month}/01"));
        $endDate = date("Y/m/d",strtotime("{$startDate} + 1 months - 1 day"));
        $whereSql = "and a.lcd BETWEEN '{$startDate}' and '{$endDate}'";
        if(!empty($employee_id)){
            $whereSql.= " and b.kam_id='{$employee_id}'";
        }
        $historySql = Yii::app()->db->createCommand()
            ->select("a.bot_id,b.kam_id,max(a.lcd) as lcd")
            ->from("sal_ka_bot_history a")
            ->leftJoin("sal_ka_bot b","a.bot_id=b.id")
            ->where("a.espe_type=1 {$whereSql}")
            ->group("a.bot_id,b.kam_id")
            ->getText();
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.kam_id,a.sign_odds,a.follow_date,a.apply_date,a.customer_no,a.customer_name,a.sum_amt,
                g.sign_odds as old_sign_odds,g.sum_amt as old_sum_amt
                ")
            ->from("($historySql) f")
            ->leftJoin("sal_ka_bot a","f.bot_id=a.id")
            ->leftJoin("sal_ka_bot_history g","g.bot_id=f.bot_id and g.lcd=f.lcd")
            ->queryAll();
        $list = array();
        if($rows){
            foreach ($rows as $row){
                if($row["old_sign_odds"]>=81){
                    $list[]=$row;
                }
            }
        }
        return $list;
    }

    //本月可实现销售金额(签约概率>=81)
    private function sign_this_num_table(){
        $list = $this->sign_this_num_list($this->employee_id);
        return $this->staticTableBodyThree($list);
    }

    //拜访阶段詳情
    private function visit_num_table(){
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = "DATE_FORMAT(a.apply_date,'%Y/%m/%d')<='{$this->end_date}'";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.sign_odds,a.follow_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,a.sum_amt,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,g.rate_num
                ")->from("sal_ka_bot a")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where("{$whereSql} and a.kam_id='{$this->employee_id}'")
            ->order("if(g.rate_num>0,a.follow_date,-1) desc,a.follow_date desc")
            ->queryAll();
        return $this->staticTableBody($rows);
    }

    //报价阶段詳情
    private function quota_num_table(){
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = "DATE_FORMAT(a.apply_date,'%Y')='{$this->ka_year}'";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.sign_odds,a.follow_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,a.sum_amt,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,g.rate_num
                ")->from("sal_ka_bot a")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where("{$whereSql} and a.kam_id='{$this->employee_id}' and g.rate_num>=30")
            ->order("if(g.rate_num>0,a.follow_date,-1) desc,a.follow_date desc")
            ->queryAll();
        return $this->staticTableBody($rows);
    }

    //YTD
    private function ytd_num_table(){
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = "DATE_FORMAT(a.apply_date,'%Y')='{$this->ka_year}'";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.sign_odds,a.follow_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,a.sum_amt,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,g.rate_num
                ")->from("sal_ka_bot a")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where("{$whereSql} and a.kam_id='{$this->employee_id}' and g.rate_num>=100")
            ->order("if(g.rate_num>0,a.follow_date,-1) desc,a.follow_date desc")
            ->queryAll();
        return $this->staticTableBody($rows);
    }

    //mtd_num
    private function mtd_num_table(){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.sign_odds,a.follow_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,a.sum_amt,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,g.rate_num
                ")->from("sal_ka_bot a")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where("a.kam_id='{$this->employee_id}' and g.rate_num>=100 and a.sign_date between '{$this->start_date}' and '{$this->end_date}'")
            ->order("if(g.rate_num>0,a.follow_date,-1) desc,a.follow_date desc")
            ->queryAll();
        return $this->staticTableBody($rows);
    }

    private function staticTableBody($rows){
        $html = "<thead>";
        $html.="<tr>";
        $html.="<th width='95px'>跟进时间</th>";
        $html.="<th width='95px'>录入日期</th>";
        $html.="<th width='110px'>客户编号</th>";
        $html.="<th>客户公司</th>";
        $html.="<th width='80px'>签约概率</th>";
        $html.="<th>沟通阶段</th>";
        $html.="<th width='95px'>总销售机会</th>";
        $html.="<th width='1px'></th>";
        $html.="</tr>";
        $html.= "</thead><tbody>";
        if($rows){
            $sumAmt = 0;
            $sumNum = 0;
            foreach ($rows as $row){
                $row['sum_amt'] = floatval($row['sum_amt']);
                $row['rate_num'] = floatval($row['rate_num']);
                $sumAmt+= $row['rate_num']>0?$row['sum_amt']:0;
                $sumNum++;
                $link = self::drawEditButton('KA01', 'kABot/edit', 'kABot/view', array('index'=>$row['id']));
                $html.="<tr data-id='{$row["id"]}'>";
                $html.="<td>".General::toDate($row['follow_date'])."</td>";
                $html.="<td>".General::toDate($row['apply_date'])."</td>";
                $html.="<td>".$row['customer_no']."</td>";
                $html.="<td>".$row['customer_name']."</td>";
                $html.="<td>".KABotForm::getSignOddsListForId($row['sign_odds'],true)."</td>";
                $html.="<td>".$row['link_name']."</td>";
                $html.="<td class='text-right'>".$row['sum_amt']."</td>";
                $html.="<td>".$link."</td>";
                $html.="</tr>";
            }
            $html.="<tr>";
            $html.="<td colspan='2' class='text-right'>总数量:</td><td colspan='2'>{$sumNum}</td>";
            $html.="<td colspan='2' class='text-right'>总金额:</td><td colspan='2'>{$sumAmt}</td>";
            $html.="</tr>";
            $html.="<tr><td colspan='8' class='text-right text-danger'>数量统计所有录入的记录，总金额不统计沟通阶段为0%的客户</td></tr>";
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
        $html.="<th rowspan='2' width='95px'>跟进时间</th>";
        $html.="<th rowspan='2' width='95px'>录入日期</th>";
        $html.="<th rowspan='2' width='110px'>客户编号</th>";
        $html.="<th rowspan='2'>客户公司</th>";
        $html.="<th colspan='2' style=\"background:#4472C4;color:#ffffff;\">现在数据</th>";
        $html.="<th colspan='3' style=\"background:#2A6BA4;color:#ffffff;\">历史数据</th>";
        $html.="<th rowspan='2' width='1px'>&nbsp;</th>";
        $html.="</tr>";
        $html.="<tr>";
        $html.="<th width='80px' style=\"background:#4472C4;color:#ffffff;\">签约概率</th>";
        $html.="<th width='95px' style=\"background:#4472C4;color:#ffffff;\">总销售机会</th>";
        $html.="<th width='80px' style=\"background:#2A6BA4;color:#ffffff;\">签约概率</th>";
        $html.="<th width='95px' style=\"background:#2A6BA4;color:#ffffff;\">总销售机会</th>";
        $html.="<th width='80px' style=\"background:#2A6BA4;color:#ffffff;\">计算金额</th>";
        $html.="</tr>";
        $html.= "</thead><tbody>";
        if($rows){
            $sumAmt = 0;
            $sumNum = 0;
            foreach ($rows as $row){
                $row['old_sum_amt'] = floatval($row['old_sum_amt']);
                $com_amt = $row['sign_odds']>=81?$row['old_sum_amt']*1:$row['old_sum_amt']*0.5;
                $sumAmt+= $com_amt;
                $sumNum++;
                $link = self::drawEditButton('KA01', 'kABot/edit', 'kABot/view', array('index'=>$row['id']));
                $html.="<tr data-id='{$row["id"]}'>";
                $html.="<td>".General::toDate($row['follow_date'])."</td>";
                $html.="<td>".General::toDate($row['apply_date'])."</td>";
                $html.="<td>".$row['customer_no']."</td>";
                $html.="<td>".$row['customer_name']."</td>";
                $html.="<td>".KABotForm::getSignOddsListForId($row['sign_odds'],true)."</td>";
                $html.="<td class='text-right'>".floatval($row['sum_amt'])."</td>";
                $html.="<td>".KABotForm::getSignOddsListForId($row['old_sign_odds'],true)."</td>";
                $html.="<td class='text-right'>".$row['old_sum_amt']."</td>";
                $html.="<td class='text-right'>".$com_amt."</td>";
                $html.="<td>".$link."</td>";
                $html.="</tr>";
            }
            $html.="<tr>";
            $html.="<td colspan='2' class='text-right'>总数量:</td><td colspan='4'>{$sumNum}</td>";
            $html.="<td colspan='2' class='text-right'>总金额:</td><td colspan='2'>{$sumAmt}</td>";
            $html.="</tr>";
        }else{
            $html.="<tr><td colspan='10'>无</td></tr>";
        }
        $html.= "</tbody>";
        return $html;
    }

    //本月可实现销售金额
    private function staticTableBodyThree($rows){
        $html = "<thead>";
        $html.="<tr>";
        $html.="<th rowspan='2' width='95px'>跟进时间</th>";
        $html.="<th rowspan='2' width='95px'>录入日期</th>";
        $html.="<th rowspan='2' width='110px'>客户编号</th>";
        $html.="<th rowspan='2'>客户公司</th>";
        $html.="<th colspan='2' style=\"background:#4472C4;color:#ffffff;\">现在数据</th>";
        $html.="<th colspan='2' style=\"background:#2A6BA4;color:#ffffff;\">历史数据</th>";
        $html.="<th rowspan='2' width='1px'>&nbsp;</th>";
        $html.="</tr>";
        $html.="<tr>";
        $html.="<th width='80px' style=\"background:#4472C4;color:#ffffff;\">签约概率</th>";
        $html.="<th width='95px' style=\"background:#4472C4;color:#ffffff;\">总销售机会</th>";
        $html.="<th width='80px' style=\"background:#2A6BA4;color:#ffffff;\">签约概率</th>";
        $html.="<th width='95px' style=\"background:#2A6BA4;color:#ffffff;\">总销售机会</th>";
        $html.="</tr>";
        $html.= "</thead><tbody>";
        if($rows){
            $sumAmt = 0;
            $sumNum = 0;
            foreach ($rows as $row){
                $row['old_sum_amt'] = floatval($row['old_sum_amt']);
                $sumAmt+= $row['old_sum_amt'];
                $sumNum++;
                $link = self::drawEditButton('KA01', 'kABot/edit', 'kABot/view', array('index'=>$row['id']));
                $html.="<tr data-id='{$row["id"]}'>";
                $html.="<td>".General::toDate($row['follow_date'])."</td>";
                $html.="<td>".General::toDate($row['apply_date'])."</td>";
                $html.="<td>".$row['customer_no']."</td>";
                $html.="<td>".$row['customer_name']."</td>";
                $html.="<td>".KABotForm::getSignOddsListForId($row['sign_odds'],true)."</td>";
                $html.="<td class='text-right'>".floatval($row['sum_amt'])."</td>";
                $html.="<td>".KABotForm::getSignOddsListForId($row['old_sign_odds'],true)."</td>";
                $html.="<td class='text-right'>".$row['old_sum_amt']."</td>";
                $html.="<td>".$link."</td>";
                $html.="</tr>";
            }
            $html.="<tr>";
            $html.="<td colspan='2' class='text-right'>总数量:</td><td colspan='3'>{$sumNum}</td>";
            $html.="<td colspan='2' class='text-right'>总金额:</td><td colspan='2'>{$sumAmt}</td>";
            $html.="</tr>";
        }else{
            $html.="<tr><td colspan='9'>无</td></tr>";
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