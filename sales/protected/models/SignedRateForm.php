<?php

class SignedRateForm extends CFormModel
{
	/* User Fields */
    public $search_start_date;//查詢開始日期
    public $search_end_date;//查詢結束日期
    public $search_type=3;//查詢類型 1：季度 2：月份 3：天
    public $search_year;//查詢年份
    public $search_month;//查詢月份
    public $search_city;//查詢城市
	public $start_date;
	public $end_date;
	public $day_num;

    public $data=array();
    public $visit_type_list=array();//销售拜访类别
    public $signSql="";

	public $th_sum=0;//所有th的个数

    public $downJsonText='';
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
            'search_city'=>Yii::t('summary','search city'),
		);
	}

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('search_city,search_type,search_start_date,search_end_date,search_year,search_month','safe'),
            array('search_type','required'),
            array('search_type','validateDate'),
        );
    }

    public function validateDate($attribute, $params) {
        switch ($this->search_type){
            case 1://1：季度
                if(empty($this->search_year)){
                    $this->addError($attribute, "查询年份不能为空");
                }else{
                    $this->start_date = $this->search_year."/01/01";
                    $this->end_date = $this->search_year."/12/31";
                }
                break;
            case 2://2：月份
                if(empty($this->search_year)||empty($this->search_month)){
                    $this->addError($attribute, "查询月份不能为空");
                }else{
                    $dateTimer = strtotime($this->search_year."/".$this->search_month."/01");
                    $this->start_date = date("Y/m/01",$dateTimer);
                    $this->end_date = date("Y/m/t",$dateTimer);
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
                        $this->search_year = $startYear;
                        $this->start_date = date("Y/m/d",strtotime($this->search_start_date));
                        $this->end_date = date("Y/m/d",strtotime($this->search_end_date));
                    }
                }
                break;
        }

        $visit_type_list =array();
        $rows = Yii::app()->db->createCommand()->select("id,name")
            ->from("sal_visit_type")->order("id asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $visit_type_list[$row["id"]]=$row;
            }
        }
        $this->visit_type_list = $visit_type_list;
        $this->signSql = CountSearch::getDealString("a.visit_obj");
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
            'search_city'=>$this->search_city,
            'search_year'=>$this->search_year,
            'search_month'=>$this->search_month,
            'search_type'=>$this->search_type,
            'search_start_date'=>$this->search_start_date,
            'search_end_date'=>$this->search_end_date
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

    public function retrieveData() {
        $data = array();
        $city = $this->search_city;
        $city_allow = City::model()->getDescendantList($city);
        $cstr = $city;
        $city_allow .= (empty($city_allow)) ? "'$cstr'" : ",'$cstr'";
        //$city_allow = Yii::app()->user->city_allow();
        SignedRateForm::setDayNum($this->start_date,$this->end_date,$this->day_num);
        $startDate = $this->start_date;
        $endDate = $this->end_date;

        $salesList = CountSearch::getSalesForRW($city_allow,$startDate,$endDate);

        $deMoreList = $this->defMoreList();
        $city_name = "";
        $city = "";
        foreach ($salesList as $staffRow){
            if($staffRow["city"]!==$city){
                $city = $staffRow["city"];
                $city_name = General::getCityName($city);
            }
            $user_id = $staffRow["user_id"];
            $temp = $deMoreList;
            $temp["user_id"]=$user_id;
            $temp["city"]=$staffRow["city"];
            $temp["city_name"]=$city_name;
            $temp["employee_name"]=$staffRow["name_label"];
            $this->setTempForEmployee($temp,$staffRow);
            $this->setTempForVisit($temp,$staffRow);

            $data[$user_id] = $temp;
        }

        $this->data = $data;
        $session = Yii::app()->session;
        $session['signedRate_c01'] = $this->getCriteria();
        return true;
    }

    private function setTempForEmployee(&$temp,$staffRow){
        $suffix = Yii::app()->params['envSuffix'];
        $localOffice = Yii::t("summary","local office");
        $groupList = array(
            0=>Yii::t("summary","none"),//無
            1=>Yii::t("summary","group business"),//商業組
            2=>Yii::t("summary","group repast"),//餐飲組
        );
        $row = Yii::app()->db->createCommand()
            ->select("a.name,b.name as position_name,a.group_type,entry_time,
            if(a.office_id=0,'{$localOffice}',f.name) as office_name")
            ->from("hr{$suffix}.hr_employee a")
            ->leftJoin("hr{$suffix}.hr_dept b","b.id=a.position")
            ->leftJoin("hr{$suffix}.hr_office f","f.id=a.office_id")
            ->where("a.id=:id",array(":id"=>$staffRow["id"]))->queryRow();
        if($row){
            $year_long = strtotime($this->end_date)-strtotime($row["entry_time"]);
            $year_long = $year_long/(60*60*24*30);
            $year_long = round($year_long);
            $row["group_type"] = key_exists($row["group_type"],$groupList)?$row["group_type"]:0;
            $temp["position_name"]=$row["position_name"];
            $temp["office_name"]=$row["office_name"];
            $temp["entry_time"]=$row["entry_time"];
            $temp["group_name"]=$groupList[$row["group_type"]];
            $temp["year_long"]=$year_long.Yii::t("summary"," months");
        }
    }

    private function setTempForVisit(&$temp,$staffRow){
        $idCharSql = "SELECT GROUP_CONCAT(CONCAT(\"'svc_\",a.id_char,\"'\")) as idChars FROM sal_service_type_info a LEFT JOIN sal_service_type b ON a.type_id=b.id 
WHERE b.class_id is NOT null AND a.input_type='yearAmount' AND  b.class_id not in (6,7)";
        $idChar = Yii::app()->db->createCommand($idCharSql)->queryRow();
        $idChar =$idChar?$idChar["idChars"]:"''";
        $selectAmtSQL = "";//签单金额查询
        $selectSignSQL = "";//签单数量查询
        foreach ($this->visit_type_list as $row){
            $selectAmtSQL.= ",sum(if(a.visit_type={$row['id']},b.field_value,0)) as money_{$row['id']}";
            $selectSignSQL.= ",sum(if(a.visit_type={$row['id']},1,0)) as success_{$row['id']}";
        }
        $dateSql = " and a.visit_dt BETWEEN '{$this->start_date}' and '{$this->end_date}'";
        //签单金额不包含纸品
        $amtSql = " and b.field_id in ({$idChar})";
        //总拜访量
        $temp["visit_sum"] = Yii::app()->db->createCommand()->select("count(a.id)")->from("sal_visit a")
            ->where("a.username=:id {$dateSql}",array(":id"=>$staffRow["user_id"]))->queryScalar();
        //签单金额查询
        $amtRow = Yii::app()->db->createCommand()->select("sum(b.field_value) as money_sum {$selectAmtSQL}")
            ->from("sal_visit_info b")
            ->leftJoin("sal_visit a","b.visit_id=a.id")
            ->where("a.username=:id and ({$this->signSql}) {$dateSql} {$amtSql}",array(":id"=>$staffRow["user_id"]))->queryRow();
        //总签单量查询
        $signRow = Yii::app()->db->createCommand()->select("count(a.id) as sign_sum {$selectSignSQL}")->from("sal_visit a")
            ->where("a.username=:id and ({$this->signSql}) {$dateSql}",array(":id"=>$staffRow["user_id"]))->queryRow();

        $amtRow = $amtRow?$amtRow:array();
        $signRow = $signRow?$signRow:array();
        $amtRow = array_merge($amtRow,$signRow);
        if($amtRow){
            foreach ($amtRow as $key=>$value){
                if(key_exists($key,$temp)){
                    $temp[$key] = empty($value)?0:$value;
                }
            }
        }

    }

    //設置該城市的默認值
    private function defMoreList(){
        $arr=array(
            "user_id"=>0,
            "city"=>"",
            "city_name"=>"",
            "employee_name"=>"",//员工名称
            "office_name"=>"",//办事处office_id
            "group_name"=>"",//组别group_type
            "position_name"=>"",//职位position
            "entry_time"=>"",//入职日期
            "year_long"=>0,//年资
        );
        foreach ($this->visit_type_list as $row){
            $arr["money_".$row["id"]]=0;//签单金额
            $arr["success_".$row["id"]]=0;//成交量
            $arr["success_rate".$row["id"]]=0;//成交占比
            $arr["money_rate".$row["id"]]=0;//签单占比
        }
        $arr["money_sum"]=0;//签单总金额
        $arr["visit_sum"]=0;//总拜访量
        $arr["sign_sum"]=0;//总签单量
        $arr["success_rate_sum"]=0;//总成交率
        return $arr;
    }

    protected function resetTdRow(&$list,$bool=false){
        foreach ($this->visit_type_list as $row){
            $list["success_rate".$row["id"]]=self::numAndSumRate($list["success_".$row["id"]],$list["visit_sum"]);;//成交占比
            $list["money_rate".$row["id"]]=self::numAndSumRate($list["money_".$row["id"]],$list["money_sum"]);;//签单占比
        }
        //总成交率
        $list["success_rate_sum"] = self::numAndSumRate($list["sign_sum"],$list["visit_sum"]);
    }

    public static function numAndSumRate($num,$sum){
        if(empty($sum)){
            return 0;
        }else{
            $rate = $num/$sum;
            $rate = round($rate,3)*100;
            return empty($rate)?0:($rate."%");
        }
    }

    public static function showNum($num){
        return $num;
    }

    //顯示提成表的表格內容
    public function signedRateHtml(){
        $html= '<table id="signedRate" class="table table-fixed table-condensed table-bordered table-hover">';
        $html.=$this->tableTopHtml();
        $html.=$this->tableBodyHtml();
        $html.=$this->tableFooterHtml();
        $html.="</table>";
        return $html;
    }

    private function getTopArr(){
        $colorList = array("#f7fd9d","#fcd5b4","#C5D9F1","#D9D9D9","#EBF1DE","#f2dcdb");
        $topList=array(
            array("name"=>Yii::t("summary","City"),"rowspan"=>2),//城市
            array("name"=>Yii::t("summary","employee name"),"rowspan"=>2),//员工名称
            array("name"=>Yii::t("summary","office name"),"rowspan"=>2),//办事处
            array("name"=>Yii::t("summary","group name"),"rowspan"=>2),//组别
            array("name"=>Yii::t("summary","position name"),"rowspan"=>2),//职位
            array("name"=>Yii::t("summary","entry date"),"rowspan"=>2),//入职日期
            array("name"=>Yii::t("summary","year long"),"rowspan"=>2),//年资
        );
        $colspan=array(
            array("name"=>Yii::t("summary","sign money")),//签单金额
            array("name"=>Yii::t("summary","sign count")),//成交量
            array("name"=>Yii::t("summary","sign count rate")),//成交占比
            array("name"=>Yii::t("summary","sign money rate")),//签单占比
        );
        $i=0;
        foreach ($this->visit_type_list as $row){
            $i = key_exists($i,$colorList)?$i:0;
            $color = $colorList[$i];
            $topList[] = array("name" =>$row["name"], "background" =>$color,
                "colspan" => $colspan
            );
            $i++;
        }
        $topList[] = array("name" =>Yii::t("summary","total"), "background" =>"#FDE9D9",
            "colspan" => array(
                array("name"=>Yii::t("summary","sign money sum")),//总金额
                array("name"=>Yii::t("summary","visit sum")),//总拜访量
                array("name"=>Yii::t("summary","sign sum")),//签单量
                array("name"=>Yii::t("summary","sign sum rate")),//成交率
            )
        );//合计

        return $topList;
    }

    //顯示提成表的表格內容（表頭）
    protected function tableTopHtml(){
        $this->th_sum = 0;
        $topList = self::getTopArr();
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
            if(in_array($i,array(1,2))){
                $width=90;
            }else{
                $width=75;
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
            $html.=TbHtml::hiddenField("excel",$this->downJsonText);
        }
        return $html;
    }

    //获取td对应的键名
    private function getDataAllKeyStr(){
        $bodyKey = array(
            "city_name","employee_name","office_name","group_name","position_name",
            "entry_time","year_long"
        );
        foreach ($this->visit_type_list as $row){
            $bodyKey[]="money_".$row["id"];//签单金额
            $bodyKey[]="success_".$row["id"];//成交量
            $bodyKey[]="success_rate".$row["id"];//成交占比
            $bodyKey[]="money_rate".$row["id"];//签单占比
        }
        $bodyKey[]="money_sum";
        $bodyKey[]="visit_sum";
        $bodyKey[]="sign_sum";
        $bodyKey[]="success_rate_sum";
        return $bodyKey;
    }

    //設置百分比顏色
    public static function getTextColorForKeyStr($text,$keyStr){
        $tdClass = "";
        return $tdClass;
    }

    //將城市数据寫入表格
    private function showServiceHtml($data){
        $bodyKey = $this->getDataAllKeyStr();
        $html="";
        if(!empty($data)){
            foreach ($data as $user_id=>$row){
                $this->resetTdRow($row);
                $html.="<tr>";
                foreach ($bodyKey as $keyStr){
                    $text = key_exists($keyStr,$row)?$row[$keyStr]:"0";
                    $tdClass = "";
                    $exprData = self::tdClick($tdClass,$keyStr,$user_id);//点击后弹窗详细内容
                    $this->downJsonText["excel"][$user_id][$keyStr]=$text;

                    $html.="<td class='{$tdClass}' {$exprData}><span>{$text}</span></td>";
                }
                $html.="</tr>";
            }
            $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
            $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
        }
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
            $excelData = key_exists("excel",$excelData)?$excelData["excel"]:array();
        }
        $this->validateDate("","");
        $headList = $this->getTopArr();
        $titleTwo = $this->start_date." ~ ".$this->end_date."\r\n";
        $titleTwo.= Yii::t("summary","search city")."：".General::getCityName($this->search_city);
        $excel = new DownSummary();
        $excel->colTwo = 7;
        $excel->SetHeaderTitle(Yii::t("app","Signed conversion rate"));
        $excel->SetHeaderString($titleTwo);
        $excel->init();
        $excel->setSummaryHeader($headList);
        $excel->setUServiceData($excelData);
        $excel->outExcel(Yii::t("app","Signed conversion rate"));
    }

    protected function clickList(){
        return array(
            "new_month_n_last"=>array("title"=>Yii::t("summary","Last Month Single + New(INV)").Yii::t("summary"," (last year)"),"type"=>"ServiceINVMonthNewLast"),
            "new_month_n"=>array("title"=>Yii::t("summary","Last Month Single + New(INV)"),"type"=>"ServiceINVMonthNew"),
            "new_sum_n_last"=>array("title"=>Yii::t("summary","New(single) + New(INV)").Yii::t("summary"," (last year)"),"type"=>"ServiceINVNewLast"),
            "new_sum_n"=>array("title"=>Yii::t("summary","New(single) + New(INV)"),"type"=>"ServiceINVNew"),
            "new_sum_last"=>array("title"=>Yii::t("summary","New(not single)").Yii::t("summary"," (last year)"),"type"=>"ServiceNewLast"),
            "new_sum"=>array("title"=>Yii::t("summary","New(not single)"),"type"=>"ServiceNew"),
            "stop_sum_last"=>array("title"=>Yii::t("summary","YTD Stop").Yii::t("summary"," (last year)"),"type"=>"ServiceStopLast"),
            "stop_sum"=>array("title"=>Yii::t("summary","YTD Stop"),"type"=>"ServiceStop"),
            "resume_sum_last"=>array("title"=>Yii::t("summary","YTD Resume").Yii::t("summary"," (last year)"),"type"=>"ServiceResumeLast"),
            "resume_sum"=>array("title"=>Yii::t("summary","YTD Resume"),"type"=>"ServiceResume"),
            "pause_sum_last"=>array("title"=>Yii::t("summary","YTD Pause").Yii::t("summary"," (last year)"),"type"=>"ServicePauseLast"),
            "pause_sum"=>array("title"=>Yii::t("summary","YTD Pause"),"type"=>"ServicePause"),
            "amend_sum_last"=>array("title"=>Yii::t("summary","YTD Amend").Yii::t("summary"," (last year)"),"type"=>"ServiceAmendLast"),
            "amend_sum"=>array("title"=>Yii::t("summary","YTD Amend"),"type"=>"ServiceAmend"),
        );
    }

    private function tdClick(&$tdClass,$keyStr,$username){
        $expr = " data-username='{$username}'";
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
        $arr[1]=Yii::t("summary","search year");//季度
        $arr[2]=Yii::t("summary","search month");//月度
        $arr[3]=Yii::t("summary","search day");//日期
        return $arr;
    }

    public static function getSelectYear(){
        $arr = array();
        $year = date("Y");
        for($i=$year-3;$i<$year+3;$i++){
            if($i>=2023){
                $arr[$i] = $i.Yii::t("report","Year");
            }
        }
        return $arr;
    }

    public static function getSelectMonth(){
        $arr = array();
        for($i=1;$i<=12;$i++){
            $arr[$i] = $i.Yii::t("report","Month");
        }
        return $arr;
    }
}