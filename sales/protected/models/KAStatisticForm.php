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
        $startDate = $this->search_year."/01/01";
        $whereSql = "and a.lcd BETWEEN '{$startDate}' and '{$this->end_date}'";
        if(Yii::app()->user->validFunction('CN15')){
            $whereSql = "";//2023/06/16 改為可以看的所有記錄
        }else{
            $whereSql.= " and b.kam_id='{$this->employee_id}'";
        }
        $rows = Yii::app()->db->createCommand()
            ->select("a.bot_id,b.kam_id,max(a.lcd) as lcd")
            ->from("sal_ka_bot_history a")
            ->leftJoin("sal_ka_bot b","a.bot_id=b.id")
            ->where("a.espe_type=1 and a.sign_odds>=51 {$whereSql}")
            ->group("a.bot_id,b.kam_id")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $employee_id = $row["kam_id"];
                if(!key_exists($employee_id,$list)){
                    $list[$employee_id]=array(
                        "sign_90_num"=>0,//未来90天数量
                        "sign_90_amt"=>0,//未来90天金额
                        "sign_this_num"=>0,//本月数量
                        "sign_this_amt"=>0,//本月金额
                    );
                }
                $historyRow = Yii::app()->db->createCommand()->select("sum_amt,sign_odds")
                    ->from("sal_ka_bot_history")
                    ->where("lcd='{$row['lcd']}' and bot_id='{$row['bot_id']}'")
                    ->queryRow();
                if($historyRow["sign_odds"]>=81){
                    $list[$employee_id]["sign_this_num"]++;
                    $list[$employee_id]["sign_this_amt"]+=$historyRow["sum_amt"];

                    $list[$employee_id]["sign_90_num"]++;
                    $list[$employee_id]["sign_90_amt"]+=$historyRow["sum_amt"]*0.8;
                }elseif ($historyRow["sign_odds"]>=51){
                    $list[$employee_id]["sign_90_num"]++;
                    $list[$employee_id]["sign_90_amt"]+=$historyRow["sum_amt"]*0.5;
                }
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
                count(a.id) as visit_num,sum(a.sum_amt) as visit_amt,
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
                    "mtd_num"=>$mtdRow["mtd_num"],
                    "mtd_amt"=>$mtdRow["mtd_amt"],
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
                        $html.="<td><span>{$text}</span></td>";
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
            $list[$i] = $i.Yii::t("ka"," year");
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
}