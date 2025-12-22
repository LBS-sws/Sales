<?php

class OutBusinessForm extends CFormModel
{
	/* User Fields */
    public $search_start_date;//查詢開始日期
    public $search_end_date;//查詢結束日期
    public $search_type=2;//查詢類型 1：季度 2：月份 3：天
    public $search_year;//查詢年份
    public $search_month;//查詢月份
    public $search_month_end;//查詢月份(结束)
    public $search_quarter;//查詢季度
	public $start_date;
	public $end_date;
    public $month_type;
	public $day_num=0;
	public $outBusiness_year;
    public $month_start_date;
    public $month_end_date;
    public $last_month_start_date;
    public $last_month_end_date;

    public $data=array();
    public $dataTwo=array();
    public $outCity=array();//

	public $th_sum=2;//所有th的个数

    public $downJsonText='';

    protected $class_type="NONE";//类型 NONE:普通  KA:KA
    public $u_load_data=array();//查询时长数组
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
            'search_type'=>Yii::t('summary','search type'),
            'search_start_date'=>Yii::t('summary','start date'),
            'search_end_date'=>Yii::t('summary','end date'),
            'search_year'=>Yii::t('summary','search year'),
            'search_quarter'=>Yii::t('summary','search quarter'),
            'search_month'=>Yii::t('summary','search month'),
		);
	}

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('search_type,search_start_date,search_end_date,search_year,search_quarter,search_month,search_month_end','safe'),
            array('search_type','required'),
            array('search_type','validateDate'),
        );
    }

    public function validateDate($attribute, $params) {
        switch ($this->search_type){
            case 1://1：季度
                if(empty($this->search_year)||empty($this->search_quarter)){
                    $this->addError($attribute, "查询季度不能为空");
                }else{
                    $dateStr = $this->search_year."/".$this->search_quarter."/01";
                    $this->start_date = date("Y/m/01",strtotime($dateStr));
                    $this->end_date = date("Y/m/t",strtotime($dateStr." + 2 month"));
                    $this->month_type = $this->search_quarter;
                }
                break;
            case 2://2：月份
                if(empty($this->search_year)||empty($this->search_month)){
                    $this->addError($attribute, "查询月份不能为空");
                }else{
                    $dateTimer = strtotime($this->search_year."/".$this->search_month."/01");
                    $this->start_date = date("Y/m/01",$dateTimer);
                    $this->end_date = date("Y/m/t",$dateTimer);
                    $i = ceil($this->search_month/3);//向上取整
                    $this->month_type = 3*$i-2;
                }
                break;
            case 3://3：天
                if(empty($this->search_start_date)||empty($this->search_start_date)){
                    $this->addError($attribute, "查询日期不能为空");
                }else{
                    $startYear = date("Y",strtotime($this->search_start_date));
                    $endYear = date("Y",strtotime($this->search_end_date));
                    if($startYear!=$endYear){
                        $this->addError($attribute, "请把开始年份跟结束年份保持一致");
                    }else{
                        $this->search_month = date("n",strtotime($this->search_start_date));
                        $i = ceil($this->search_month/3);//向上取整
                        $this->month_type = 3*$i-2;
                        $this->search_year = $startYear;
                        $this->start_date = $this->search_start_date;
                        $this->end_date = $this->search_end_date;
                    }
                }
                break;
        }
        if($this->end_date<$this->start_date){
            $this->addError($attribute, "查询时间异常");
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
            'search_month'=>$this->search_month,
            'search_month_end'=>$this->search_month_end,
            'search_type'=>$this->search_type,
            'search_quarter'=>$this->search_quarter,
            'search_start_date'=>$this->search_start_date,
            'search_end_date'=>$this->search_end_date
        );
    }

    protected function computeDate(){
        $this->start_date = empty($this->start_date)?date("Y/01/01"):$this->start_date;
        $this->end_date = empty($this->end_date)?date("Y/m/t"):$this->end_date;
        $this->outBusiness_year = date("Y",strtotime($this->start_date));
        $this->month_start_date = date("m/d",strtotime($this->start_date));
        $this->month_end_date = date("m/d",strtotime($this->end_date));

        $this->last_month_start_date = CountSearch::computeLastMonth($this->start_date);
        $this->last_month_end_date = CountSearch::computeLastMonth($this->end_date);
    }

    protected function getOnlyCitySetList($city){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("a.code as city_code,a.name as city_name,b.region_code,f.name as region_name")
            ->from("swo_city_set b")
            ->leftJoin("security$suffix.sec_city a","a.code=b.code")
            ->leftJoin("security$suffix.sec_city f","b.region_code=f.code")
            ->where("b.show_type=1 and b.code=:code",array(":code"=>$city))
            ->queryRow();
        if($row){
            return $row;
        }else{
            $cityName = self::getCityName($city);
            return array("city_code"=>$city,"city_name"=>$cityName,"region_code"=>"none","region_name"=>"none");
        }
    }

    public static function getCityName($code) {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("name")->from("security$suffix.sec_city")
            ->where("code=:code",array(":code"=>$code))->queryRow();
        if($row){
            return $row["name"];
        }else{
            return $code;
        }
    }

    protected function getOnlyStaffList($staffCode){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("code,name,city,table_type")
            ->from("hr{$suffix}.hr_employee")
            ->where("code=:code",array(":code"=>$staffCode))
            ->order("if(staff_status=0,99,staff_status) desc,table_type asc")
            ->queryRow();
        return $row?$row:array("code"=>$staffCode,"name"=>$staffCode,"city"=>"none","table_type"=>1);
    }

    public function retrieveData() {
        $this->u_load_data['load_start'] = time();
        $this->data = array();
        $this->dataTwo = array();
        $this->outCity = array();
        $startDate = $this->start_date;
        $endDate = $this->end_date;

        $this->u_load_data['u_load_start'] = time();
        //获取外包员工的服务金额(详情金额)
        $outStaffMoney = CountSearch::getOutBusinessServiceMoney($startDate,$endDate);
        $this->u_load_data['u_load_end'] = time();

        $allCityList = array();//城市最终归属的区域

        $city_allow = Yii::app()->user->city_allow();
        $city_allow.= ",'none'";//不存在的员工也需要显示出来
        if(!empty($outStaffMoney)){
            foreach ($outStaffMoney as $staffCode=>$money){
                $staffList = self::getOnlyStaffList($staffCode);
                if (strpos($city_allow,"'{$staffList["city"]}'")===false){//如果没有该城市的权限则不显示
                    continue;
                }
                if(!key_exists($staffList["city"],$allCityList)){
                    $allCityList[$staffList["city"]]=self::getOnlyCitySetList($staffList["city"]);
                }
                $cityList = $allCityList[$staffList["city"]];
                $this->addDataOne($cityList,$staffList,$money);
                if($staffList["table_type"]==4){//业务承揽
                    $this->addDataTwo($cityList,$staffList,$money);
                }
                $this->addOutCity($cityList,$staffList,$money);
            }
        }

        $session = Yii::app()->session;
        $session['outBusiness_c01'] = $this->getCriteria();
        $this->u_load_data['load_end'] = time();
        return true;
    }

    protected function addOutCity($cityList,$staffList,$money){
        $tempTwo=array(
            "region_code"=>$cityList["region_code"],//
            "region_name"=>$cityList["region_name"],//
            "city"=>$cityList["city_code"],
            "city_name"=>$cityList["city_name"],
            "table_type"=>$staffList["table_type"],
            "staff_code"=>$staffList["code"],
            "staff_name"=>$staffList["name"],
            "service_money"=>$money,//服务总金额
        );
        $cityCode = $staffList["city"];
        if(!key_exists($cityCode,$this->outCity)){
            $this->outCity[$cityCode]=array();
        }
        $this->outCity[$cityCode][]=$tempTwo;
    }

    protected function addDataOne($cityList,$staffList,$money){
        $regionCode = $cityList["region_code"];
        $cityCode = $cityList["city_code"];
        $temp=array(
            "city"=>$cityList["city_code"],
            "city_name"=>$cityList["city_name"],
            "u_actual_money"=>0,//实际服务金额(不包含产品金额)
            "outBusiness_money"=>0,//外包服务总金额
            "outBusiness_rate"=>0,//外包比例
            "outBusiness_cost"=>0,//外包成本
            "outBusiness_cost_rate"=>0,//外包成本/外包服务总金额%
        );
        if(!key_exists($regionCode,$this->data)){
            $this->data[$regionCode]=array(
                "region"=>$cityList["region_code"],
                "region_name"=>$cityList["region_name"],
                "list"=>array()
            );
        }
        if(!key_exists($cityCode,$this->data[$regionCode]["list"])){
            $this->data[$regionCode]["list"][$cityCode]=$temp;
        }
        $this->data[$regionCode]["list"][$cityCode]["u_actual_money"]+=$money;
        if($staffList["table_type"]==4){//业务承揽
            $this->data[$regionCode]["list"][$cityCode]["outBusiness_money"]+=$money;
        }
    }

    protected function addDataTwo($cityList,$staffList,$money){
        $regionCode = $cityList["region_code"];
        $cityCode = $cityList["city_code"];
        $tempTwo=array(
            "region_code"=>$cityList["region_code"],//
            "region_name"=>$cityList["region_name"],//
            "city"=>$cityList["city_code"],
            "city_name"=>$cityList["city_name"],
            "staff_code"=>$staffList["code"],
            "staff_name"=>$staffList["name"],
            "service_money"=>$money,//服务总金额
        );
        if(!key_exists($regionCode,$this->dataTwo)){
            $this->dataTwo[$regionCode]=array(
                "region"=>$cityList["region_code"],
                "region_name"=>$cityList["region_name"],
                "list"=>array()
            );
        }
        if(!key_exists($cityCode,$this->dataTwo[$regionCode]["list"])){
            $this->dataTwo[$regionCode]["list"][$cityCode]=array();
        }
        $this->dataTwo[$regionCode]["list"][$cityCode][]=$tempTwo;
    }

    protected function resetTdRow(&$list,$bool=false,$type=1){
        //"city_name","u_actual_money","outBusiness_money","outBusiness_rate","outBusiness_cost","outBusiness_cost_rate"
        if($type==1){
            $list["outBusiness_rate"] = self::comparisonRate($list["outBusiness_money"],$list["u_actual_money"]);
            $list["outBusiness_cost"] = "";
            $list["outBusiness_cost_rate"] = "";
        }else{
            if($bool){
                $list["region_name"] = "汇总：".$list["city_name"];
                $list["city_name"] = "";
                $list["position_name"] = "";
                $list["staff_name"] = "";
                $list["average"] = empty($list["countNum"])?0:$list["service_money"]/$list["countNum"];
                $list["average"] = round($list["average"],2);
            }
        }
    }

    //顯示提成表的表格內容
    public function outBusinessHtml($type=1){
        $html= "<table id=\"outBusiness_{$type}\" class=\"table table-fixed table-condensed table-bordered table-hover\">";
        $html.=$this->tableTopHtml($type);
        $html.=$this->tableBodyHtml($type);
        $html.=$this->tableFooterHtml();
        $html.="</table>";
        return $html;
    }

    private function getTopArr(){
        $topList=array(
            array("name"=>Yii::t("summary","City"),"rowspan"=>2),//城市
            array("name"=>Yii::t("summary","service all amount"),"rowspan"=>2),//实际服务金额
            array("name"=>Yii::t("summary","OutBusiness amount"),"rowspan"=>2),//外包服务总金额
            array("name"=>Yii::t("summary","OutBusiness rate"),"rowspan"=>2),//外包比例
            array("name"=>Yii::t("summary","OutBusiness cost amount"),"rowspan"=>2),//外包成本
            array("name"=>Yii::t("summary","OutBusiness cost rate"),"rowspan"=>2),//外包成本/外包服务总金额%
        );

        return $topList;
    }

    private function getTopArrTwo(){
        $topList=array(
            array("name"=>Yii::t("summary","Area"),"rowspan"=>2),//区域
            array("name"=>Yii::t("summary","City"),"rowspan"=>2),//城市
            array("name"=>Yii::t("summary","Employ Code"),"rowspan"=>2),//员工
            array("name"=>Yii::t("summary","Employ Name"),"rowspan"=>2),//职位
            array("name"=>Yii::t("summary","Paid Amt"),"rowspan"=>2),//服务金额
        );

        return $topList;
    }

    //顯示提成表的表格內容（表頭）
    protected function tableTopHtml($type=1){
        $this->th_sum = 0;
        if($type==1){
            $topList = self::getTopArr();
        }else{
            $topList = self::getTopArrTwo();
        }
        $trOne="";
        $trTwo="";
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
                    $colNum++;
                    $trTwo.="<th style='{$style}'><span>".$col["name"]."</span></th>";
                    $this->th_sum++;
                }
            }else{
                $this->th_sum++;
            }
            $colNum = empty($colNum)?1:$colNum;
            $trOne.="<th style='{$style}' colspan='{$colNum}'";
            if($colNum>1){
                $trOne.=" class='click-th'";
            }
            if(key_exists("rowspan",$list)){
                $trOne.=" rowspan='{$list["rowspan"]}'";
            }
            if(key_exists("startKey",$list)){
                $trOne.=" data-key='{$list['startKey']}'";
            }
            $trOne.=" ><span>".$clickName."</span></th>";
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
            if($i==6){
                $width=110;
            }else{
                $width=90;
            }
            $html.="<th class='header-width' data-width='{$width}' width='{$width}px'>{$i}</th>";
        }
        return $html."</tr>";
    }

    public function tableBodyHtml($type=1){
        $html="";
        if(!empty($this->data)){
            $this->downJsonText=array();
            $html.="<tbody>";
            if($type==1){
                $keyStr = "oneData";
                $html.=$this->showServiceHtml($this->data,$type);
            }else{
                $keyStr = "twoData";
                $html.=$this->showServiceHtmlTwo($this->dataTwo,$type);
            }
            $html.="</tbody>";
            $this->downJsonText=json_encode($this->downJsonText);
            $html.=TbHtml::hiddenField("excel[{$keyStr}]",$this->downJsonText);
        }
        return $html;
    }
    //获取td对应的键名
    private function getDataAllKeyStr($type){
        if($type==1){
            $bodyKey = array(
                "city_name","u_actual_money","outBusiness_money","outBusiness_rate","outBusiness_cost","outBusiness_cost_rate"
            );
        }else{
            $bodyKey = array(
                "region_name","city_name","staff_code","staff_name","service_money"
            );
        }
        return $bodyKey;
    }

    public static function comparisonRate($num,$numLast){
        $num = is_numeric($num)?floatval($num):0;
        $numLast = is_numeric($numLast)?floatval($numLast):0;
        if(empty($numLast)){
            return "";
        }else{
            $rate = ($num/$numLast);
            $rate = round($rate,4)*100;
            return $rate."%";
        }
    }
    //設置百分比顏色
    public static function showNum($keyStr,$num){
        if(in_array($keyStr,array("staff_code","staff_name"))){
            return "".$num;
        }
        if (strpos($num,'%')!==false){
            $number = floatval($num);
            $number=sprintf("%.2f",$number)."%";
        }elseif (is_numeric($num)){
            $number = floatval($num);
            $number=sprintf("%.2f",$number);
        }else{
            $number = $num;
        }
        return $number;
    }

    //設置百分比顏色
    public static function getTextColorForKeyStr($text,$keyStr){
        $tdClass = "";

        return $tdClass;
    }

    //將城市数据寫入表格
    private function showServiceHtml($data,$type){
        $bodyKey = $this->getDataAllKeyStr($type);
        $html="";
        if(!empty($data)){
            $allRow = array("countNum"=>0);//总计(所有地区)
            foreach ($data as $regionList){
                if(!empty($regionList["list"])) {
                    $regionRow = array("countNum"=>0);//地区汇总
                    foreach ($regionList["list"] as $tdStr=>$cityList) {
                        $allRow["countNum"]++;
                        $regionRow["countNum"]++;
                        $this->resetTdRow($cityList,false,$type);
                        $html.="<tr>";
                        foreach ($bodyKey as $keyStr){
                            if(!key_exists($keyStr,$regionRow)){
                                $regionRow[$keyStr]=0;
                            }
                            if(!key_exists($keyStr,$allRow)){
                                $allRow[$keyStr]=0;
                            }
                            $text = key_exists($keyStr,$cityList)?$cityList[$keyStr]:"0";
                            $regionRow[$keyStr]+=is_numeric($text)?floatval($text):0;
                            $allRow[$keyStr]+=is_numeric($text)?floatval($text):0;
                            $tdClass = OutBusinessForm::getTextColorForKeyStr($text,$keyStr);
                            $exprData = self::tdClick($tdClass,$keyStr,$cityList["city"]);//点击后弹窗详细内容
                            $text = OutBusinessForm::showNum($keyStr,$text);
                            //$inputHide = TbHtml::hiddenField("excel[{$regionList['region']}][list][{$cityList['city']}][{$keyStr}]",$text);
                            $this->downJsonText["excel"][$regionList['region']]['list'][$tdStr][$keyStr]="{$text}";

                            $html.="<td class='{$tdClass}' {$exprData}><span>{$text}</span></td>";
                        }
                        $html.="</tr>";
                    }
                    //地区汇总
                    $regionRow["region"]=$regionList["region"];
                    $regionRow["city_name"]=$regionList["region_name"];
                    $regionRow["staff_code"]='';
                    $html.=$this->printTableTr($regionRow,$bodyKey,$type);
                    $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
                }
            }
            //地区汇总
            $allRow["region"]="allRow";
            $allRow["city_name"]=Yii::t("summary","all total");
            $allRow["staff_code"]='';
            $html.=$this->printTableTr($allRow,$bodyKey,$type);
            $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
            $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
        }
        return $html;
    }

    //將城市数据寫入表格
    private function showServiceHtmlTwo($data,$type){
        $bodyKey = $this->getDataAllKeyStr($type);
        $html="";
        if(!empty($data)){
            $allRow = array("countNum"=>0);//总计(所有地区)
            foreach ($data as $regionList){
                if(!empty($regionList["list"])) {
                    $regionRow = array("countNum"=>0);//地区汇总
                    foreach ($regionList["list"] as $cityTwoStr=>$citysList) {
                        foreach ($citysList as $cityKey=>$cityList){//由于需要把员工城市放一起所以多循环一次
                            $tdStr = $cityTwoStr."_".$cityKey;
                            $allRow["countNum"]++;
                            $regionRow["countNum"]++;
                            $this->resetTdRow($cityList,false,$type);
                            $html.="<tr>";
                            foreach ($bodyKey as $keyStr){
                                if(!key_exists($keyStr,$regionRow)){
                                    $regionRow[$keyStr]=0;
                                }
                                if(!key_exists($keyStr,$allRow)){
                                    $allRow[$keyStr]=0;
                                }
                                $text = key_exists($keyStr,$cityList)?$cityList[$keyStr]:"0";
                                $regionRow[$keyStr]+=is_numeric($text)?floatval($text):0;
                                $allRow[$keyStr]+=is_numeric($text)?floatval($text):0;
                                $tdClass = OutBusinessForm::getTextColorForKeyStr($text,$keyStr);
                                $exprData = self::tdClick($tdClass,$keyStr,$cityList["city"]);//点击后弹窗详细内容
                                $text = OutBusinessForm::showNum($keyStr,$text);
                                //$inputHide = TbHtml::hiddenField("excel[{$regionList['region']}][list][{$cityList['city']}][{$keyStr}]",$text);
                                $this->downJsonText["excel"][$regionList['region']]['list'][$tdStr][$keyStr]="{$text}";

                                $html.="<td class='{$tdClass}' {$exprData}><span>{$text}</span></td>";
                            }
                            $html.="</tr>";
                        }
                    }
                    //地区汇总
                    $regionRow["region"]=$regionList["region"];
                    $regionRow["city_name"]=$regionList["region_name"];
                    $regionRow["staff_code"]='';
                    $html.=$this->printTableTr($regionRow,$bodyKey,$type);
                    $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
                }
            }
            //地区汇总
            $allRow["region"]="allRow";
            $allRow["city_name"]=Yii::t("summary","all total");
            $allRow["staff_code"]='';
            $html.=$this->printTableTr($allRow,$bodyKey,$type);
            $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
            $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
        }
        return $html;
    }

    protected function printTableTr($data,$bodyKey,$type){
        $this->resetTdRow($data,true,$type);
        $html="<tr class='tr-end click-tr'>";
        foreach ($bodyKey as $keyStr){
            $text = key_exists($keyStr,$data)?$data[$keyStr]:"0";
            $tdClass = OutBusinessForm::getTextColorForKeyStr($text,$keyStr);
            $text = OutBusinessForm::showNum($keyStr,$text);
            //$inputHide = TbHtml::hiddenField("excel[{$data['region']}][count][{$keyStr}]",$text);
            $this->downJsonText["excel"][$data['region']]['count'][$keyStr]="{$text}";
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
        $oneData = key_exists("oneData",$excelData)?$excelData["oneData"]:array();
        $twoData = key_exists("twoData",$excelData)?$excelData["twoData"]:array();
        if(!is_array($oneData)){
            $oneData = json_decode($oneData,true);
            $oneData = key_exists("excel",$oneData)?$oneData["excel"]:array();
        }
        if(!is_array($twoData)){
            $twoData = json_decode($twoData,true);
            $twoData = key_exists("excel",$twoData)?$twoData["excel"]:array();
        }
        $this->validateDate("","");
        $this->outBusiness_year = date("Y",strtotime($this->start_date));
        $this->month_start_date = date("m/d",strtotime($this->start_date));
        $this->month_end_date = date("m/d",strtotime($this->end_date));
        $headList = $this->getTopArr();
        $headListTwo = $this->getTopArrTwo();
        $excel = new DownSummary();
        $titleName = Yii::t("app",'Out Business');
        $excel->SetHeaderTitle($titleName);
        $excel->SetHeaderString($this->start_date." ~ ".$this->end_date);
        $excel->init();
        $excel->colTwo=6;
        $excel->setSummaryHeader($headList);
        $excel->setOutBusinessData($oneData);
        $excel->setSheetName($titleName);
        $titleName = Yii::t("summary","OutBusiness productivity");
        $excel->addSheet($titleName);
        $excel->SetHeaderTitle($titleName);
        $excel->SetHeaderString($this->start_date." ~ ".$this->end_date);
        $excel->outHeader(1);
        $excel->setSummaryHeader($headListTwo);
        $excel->setOutBusinessData($twoData);
        $excel->outExcel($titleName);
    }

    protected function clickList(){
        return array(
            "u_actual_money"=>array("type"=>"all","title"=>"实际服务金额"),
            "outBusiness_money"=>array("type"=>"business","title"=>"业务承揽总金额"),
        );
    }

    private function tdClick(&$tdClass,$keyStr,$city){
        $expr = " data-city='{$city}' $keyStr ";
        $list = $this->clickList();
        if(key_exists($keyStr,$list)){
            $tdClass.=" td_detail";
            $expr.= " data-type='{$list[$keyStr]['type']}'";
            $expr.= " data-title='{$list[$keyStr]['title']}'";
        }

        return $expr;
    }

    public static function getSelectType(){
        $arr = array();
        //$arr[1]=Yii::t("summary","search quarter");//季度
        $arr[2]=Yii::t("summary","search month");//月度
        //$arr[3]=Yii::t("summary","search day");//日期
        return $arr;
    }
}