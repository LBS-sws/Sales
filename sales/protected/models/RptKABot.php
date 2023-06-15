<?phpclass RptKABot extends CReport {    private $city_allow;    private $year;    private $employee_id;    private $auto_all;	public function fields() {		return array(			'apply_date'=>array('label'=>Yii::t('ka','apply date'),'height'=>30,'width'=>20,'align'=>'C','fillColor'=>'D9D9D9'),			'customer_no'=>array('label'=>Yii::t('ka','customer no'),'width'=>20,'align'=>'C','fillColor'=>'D9D9D9'),            'customer_name'=>array('label'=>Yii::t('ka','customer name'),'width'=>30,'align'=>'L','fillColor'=>'D9D9D9'),			'contact_user'=>array('label'=>Yii::t('ka','contact user'),'width'=>20,'align'=>'C','fillColor'=>'D9D9D9'),            'source_id'=>array('label'=>Yii::t('ka','source name'),'width'=>18,'align'=>'C','fillColor'=>'D9D9D9'),			'class_id'=>array('label'=>Yii::t('ka','class name'),'width'=>18,'align'=>'C','fillColor'=>'D9D9D9'),			'kam_id'=>array('label'=>Yii::t('ka','KAM'),'width'=>18,'align'=>'C','fillColor'=>'D9D9D9'),			'link_id'=>array('label'=>Yii::t('ka','link name'),'width'=>30,'align'=>'C','fillColor'=>'FCE4D6'),			'head_city_id'=>array('label'=>Yii::t('ka','head city'),'width'=>18,'align'=>'C','fillColor'=>'FCE4D6'),			'talk_city_id'=>array('label'=>Yii::t('ka','talk city'),'width'=>18,'align'=>'C','fillColor'=>'FCE4D6'),			'area_id'=>array('label'=>Yii::t('ka','area city'),'width'=>18,'align'=>'C','fillColor'=>'FCE4D6'),			'contact_phone'=>array('label'=>Yii::t('ka','contact phone'),'width'=>18,'align'=>'C','fillColor'=>'FCE4D6'),			'contact_email'=>array('label'=>Yii::t('ka','contact email'),'width'=>18,'align'=>'C','fillColor'=>'FCE4D6'),			'contact_dept'=>array('label'=>Yii::t('ka','contact dept'),'width'=>18,'align'=>'C','fillColor'=>'FCE4D6'),			'source_text'=>array('label'=>Yii::t('ka','source name(A)'),'width'=>18,'align'=>'C','fillColor'=>'FCE4D6'),			'level_id'=>array('label'=>Yii::t('ka','level name'),'width'=>18,'align'=>'C','fillColor'=>'FCE4D6'),			'busine_id'=>array('label'=>Yii::t('ka','busine name'),'width'=>18,'align'=>'C','fillColor'=>'FCE4D6'),			'month_amt'=>array('label'=>Yii::t('ka','month amt'),'width'=>18,'align'=>'R','fillColor'=>'FCE4D6'),			'quarter_amt'=>array('label'=>Yii::t('ka','quarter amt'),'width'=>18,'align'=>'R','fillColor'=>'FCE4D6'),			'year_amt'=>array('label'=>Yii::t('ka','year amt'),'width'=>18,'align'=>'R','fillColor'=>'FCE4D6'),			'sign_date'=>array('label'=>Yii::t('ka','sign date'),'width'=>18,'align'=>'C','fillColor'=>'FCE4D6'),			'sign_month'=>array('label'=>Yii::t('ka','sign month'),'width'=>20,'align'=>'C','fillColor'=>'FCE4D6'),			'sign_amt'=>array('label'=>Yii::t('ka','sign amt'),'width'=>18,'align'=>'R','fillColor'=>'FFF2CC'),			'sum_amt'=>array('label'=>Yii::t('ka','sum amt'),'width'=>18,'align'=>'R','fillColor'=>'FFF2CC'),			'support_user'=>array('label'=>Yii::t('ka','support user'),'width'=>20,'align'=>'C','fillColor'=>'FFF2CC'),			'sign_odds'=>array('label'=>Yii::t('ka','sign odds'),'width'=>18,'align'=>'C','fillColor'=>'FFF2CC'),			'remark'=>array('label'=>Yii::t('ka','remark'),'width'=>30,'align'=>'C','fillColor'=>'FFF2CC'),			'info_date'=>array('label'=>Yii::t('ka','info date'),'width'=>18,'align'=>'C','fillColor'=>'FFF2CC'),			'info_text'=>array('label'=>Yii::t('ka','info text'),'width'=>30,'align'=>'C','fillColor'=>'FFF2CC'),		);	}    // Abstract: Define report detail with line structure    public function report_structure() {        return array("apply_date","customer_no","customer_name","contact_user","source_id",            "class_id","kam_id","link_id","head_city_id","talk_city_id",            "area_id","contact_phone","contact_email","contact_dept","source_text",            "level_id","busine_id","month_amt","quarter_amt","year_amt",            "sign_date","sign_month","sign_amt","sum_amt","support_user","sign_odds","remark",            array("info_date","info_text")        );    }    protected function init() {        $criteria = json_decode($this->criteria['CRITERIA'],true);        $this->city_allow = $criteria["city_allow"];        $this->year = $criteria["year"];        $this->employee_id = $criteria["employee_id"];        $this->auto_all = $criteria["auto_all"];    }	public function retrieveData() {        $suffix = Yii::app()->params['envSuffix'];        $city_allow = $this->city_allow;        if($this->auto_all){            $whereSql = " and (a.kam_id='{$this->employee_id}' or a.support_user='{$this->employee_id}' or h.city in ({$city_allow}))";        }else{            $whereSql = " and (a.kam_id='{$this->employee_id}' or a.support_user='{$this->employee_id}')";        }        $whereSql.=" and DATE_FORMAT(a.apply_date,'%Y')={$this->year} ";        $sql = "select a.*,                b.pro_name as class_name,                f.pro_name as source_name,                m.pro_name as level_name,                k.pro_name as busine_name,                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,                 CONCAT(h.name,' (',h.code,')') as kam_name 				from sal_ka_bot a				LEFT JOIN sal_ka_class b ON a.class_id=b.id				LEFT JOIN sal_ka_source f ON a.source_id=f.id				LEFT JOIN sal_ka_link g ON a.link_id=g.id				LEFT JOIN sal_ka_level m ON a.level_id=m.id				LEFT JOIN sal_ka_busine k ON a.busine_id=k.id				LEFT JOIN hr{$suffix}.hr_employee h ON a.kam_id=h.id				where a.id>0 {$whereSql} order by a.apply_date desc";        $rows = Yii::app()->db->createCommand($sql)->queryAll();        $this->data=array();        if($rows){            $cityList = KAAreaForm::getCityListForId();            foreach ($rows as $row){                $arr=array();                $arr["id"]=$row["id"];                $arr["apply_date"]=$row["apply_date"];                $arr["customer_no"]=$row["customer_no"];                $arr["customer_name"]=$row["customer_name"];                $arr["kam_id"]=$row["kam_name"];                $arr["head_city_id"]=self::getArrNameForKey($row["head_city_id"],$cityList);                $arr["talk_city_id"]=self::getArrNameForKey($row["talk_city_id"],$cityList);                $arr["area_id"]=self::getArrNameForKey($row["area_id"],$cityList);                $arr["contact_user"]=$row["contact_user"];                $arr["contact_phone"]=$row["contact_phone"];                $arr["contact_email"]=$row["contact_email"];                $arr["source_text"]=$row["source_text"];                $arr["source_id"]=$row["source_name"];                $arr["level_id"]=$row["level_name"];                $arr["class_id"]=$row["class_name"];                $arr["busine_id"]=$row["busine_name"];                $arr["contact_dept"]=$row["contact_dept"];                $arr["link_id"]=$row["link_name"];                $arr["year_amt"]=$row["year_amt"];                $arr["quarter_amt"]=$row["quarter_amt"];                $arr["month_amt"]=$row["month_amt"];                $arr["sign_date"]=$row["sign_date"];                $arr["sign_month"]=$row["sign_month"];                $arr["sign_amt"]=$row["sign_amt"];                $arr["sum_amt"]=$row["sum_amt"];                $arr["remark"]=$row["remark"];                $arr["support_user"]=KABotForm::getEmployeeNameForId($row["support_user"]);                $arr["sign_odds"]=$row["sign_odds"];                $arr["city"]=$row["city"];                $sql = "select info_date,info_text from sal_ka_bot_info where bot_id=".$row["id"]." ";                $infoRows = Yii::app()->db->createCommand($sql)->queryAll();                $arr["detail"]=$infoRows?$infoRows:array();                $this->data[]=$arr;            }        }		return true;	}	public static function getArrNameForKey($key,$arr){	    if(key_exists($key,$arr)){	        return $arr[$key];        }else{	        return $key;        }    }    public function getReportName() {        $year = isset($this->criteria) ? ' - '.$this->criteria['YEAR'] : '';        return (isset($this->criteria) ? Yii::t('report',$this->criteria['RPT_NAME']) : Yii::t('report','Nil')).$year;    }    public function getSubtitleName() {        return isset($this->criteria) ?General::getCityName($this->criteria['CITY']) : "";    }    public function genReport() {        $this->init();        $this->retrieveData();        $this->title = $this->getReportName();        $this->subtitle = $this->getSubtitleName();        $output = $this->exportExcel();        return $output;    }    public function downExcel($filename="01simple") {        $this->init();        $this->retrieveData();        $this->title = $this->getReportName();        $this->subtitle = $this->getSubtitleName();        $output = $this->exportExcel();        $filename= iconv('utf-8','gbk//ignore',$filename);        $filename.='.xlsx';        $ctype = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';        header("Content-type:".$ctype);        header('Content-Disposition: attachment; filename="'.$filename.'"');        header('Content-Length: ' . strlen($output));        echo $output;        Yii::app()->end();    }}?>