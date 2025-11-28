<?php
/* Reimbursement Form */

class ReportVisitForm extends CReportForm
{
    public $staffs;
    public $staffs_desc;

    protected $allQ;//所有年金额（不包含维诺）
    protected $minQ;//所有年金额（不包含维诺、纸品、一次性售卖）
    protected $classBySvcList=array();

    protected function labelsEx() {
        return array(
            'staffs'=>Yii::t('report','Staffs'),
            'start date'=>Yii::t('report','Start Date'),
            'end date'=>Yii::t('report','End Date'),
            'city'=>Yii::t('report','City'),
            'sort'=>Yii::t('report','Sort'),
            'sale'=>Yii::t('report','Sale'),
            'bumen'=>Yii::t('report','Bumen'),
        );
    }

    protected function rulesEx() {
        return array(
            array('staffs, staffs_desc','safe'),
        );
    }

    protected function queueItemEx() {
        return array(
            'STAFFS'=>$this->staffs,
            'STAFFSDESC'=>$this->staffs_desc,
        );
    }

    public function init() {
        $this->id = 'RptFive';
        $this->name = Yii::t('app','Five Steps');
        $this->format = 'EXCEL';
        $this->city = "";
        $this->cityname ="";
        $this->fields = 'start_dt,end_dt,staffs,staffs_desc';
        $this->start_dt = date('Y/m/01', strtotime(date("Y/m/d")));
        $this->end_dt = date("Y/m/d");
        $this->staffs = '';
        $this->bumen = '';
        $this->sort = "";
        $this->sale = '';
        $this->all = '';
        $this->one = '';
        $this->staffs_desc = Yii::t('misc','All');

        $infoRows = Yii::app()->db->createCommand()->select("a.id_char,b.class_id")
            ->from("sal_service_type_info a")
            ->leftJoin("sal_service_type b","a.type_id=b.id")
            ->where("a.input_type='yearAmount' and b.class_id is not null")->queryAll();//不需要蔚诺空气业务
        $minQ=array();
        $allQ=array();
        $classBySvcList=array();
        if($infoRows){
            foreach ($infoRows as $infoRow){
                $keyStr="".$infoRow["class_id"];
                if(!key_exists($keyStr,$classBySvcList)){
                    $classBySvcList[$keyStr]=array();
                }
                $classBySvcList[$keyStr][]="svc_".$infoRow["id_char"];
                $allQ[]="svc_".$infoRow["id_char"];
                if(!in_array($keyStr,array(6,7))){//纸品（6），一次性售卖（7）
                    $minQ[]="svc_".$infoRow["id_char"];
                }
            }
        }
        $this->allQ = "'".implode("','",$allQ)."'";
        $this->minQ = "'".implode("','",$minQ)."'";
        $this->classBySvcList = $classBySvcList;
    }

    public function city(){
        $suffix = Yii::app()->params['envSuffix'];
        $model = new City();
        $city_allow=Yii::app()->user->city_allow();
        $city=Yii::app()->user->city();
        $records=$model->getDescendant($city);
        array_unshift($records,$city);
        $cityList=array();
        foreach ($records as $k) {
            $sql = "select name from security$suffix.sec_city where code='" . $k . "'";
            $name = Yii::app()->db->createCommand($sql)->queryRow();
            if($name){
                $cityList[$k] = $name['name'];
            }
        }

        $sql = "select code,name from security$suffix.sec_city where code in ({$city_allow})";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        foreach ($records as $record) {
            if(!key_exists($record["code"],$cityList)){
                $cityList[$record["code"]]=$record['name'];
            }
        }
        return $cityList;
    }

    public function saleman(){
        $suffix = Yii::app()->params['envSuffix'];
        $city=Yii::app()->user->city();
//        $sql="select code,name from hr$suffix.hr_employee WHERE  position in (SELECT id FROM hr$suffix.hr_dept where dept_class='sales') AND staff_status = 0 AND city='".$city."'";
//        $records = Yii::app()->db->createCommand($sql)->queryAll();
        $sql1="select a.name from hr$suffix.hr_employee a, hr$suffix.hr_binding b, security$suffix.sec_user_access c,security$suffix.sec_user d 
        where a.id=b.employee_id and b.user_id=c.username and c.system_id='sal' and c.a_read_write like '%HK01%' and c.username=d.username and d.status='A' and a.city='".$city."'";
        $records = Yii::app()->db->createCommand($sql1)->queryAll();
//        $records=array_merge($records,$name);
        //print_r($name);
        return $records;
    }

    public static function salemanForHr($city,$startDate="",$endDate=""){
        $suffix = Yii::app()->params['envSuffix'];
        $city=empty($city)?Yii::app()->user->city():$city;
        $startDate = empty($startDate)?date("Y/m/01"):date("Y/m/d",strtotime($startDate));
        $endDate = empty($endDate)?date("Y/m/d"):date("Y/m/d",strtotime($endDate));
        $city_allow = City::model()->getDescendantList($city);
        $city_allow .= (empty($city_allow)) ? "'$city'" : ",'$city'";
        $list=array();
        $rows = Yii::app()->db->createCommand()->select("a.name,d.user_id,a.staff_status")
            ->from("security{$suffix}.sec_user_access f")
            ->leftJoin("hr{$suffix}.hr_binding d","d.user_id=f.username")
            ->leftJoin("hr{$suffix}.hr_employee a","d.employee_id=a.id")
            ->where("f.system_id='sal' and f.a_read_write like '%HK01%' and (
                (a.staff_status = 0 and date_format(a.entry_time,'%Y/%m/%d')<='{$endDate}')
                or
                (a.staff_status=-1 and date_format(a.leave_time,'%Y/%m/%d')>='{$startDate}' and date_format(a.entry_time,'%Y/%m/%d')<='{$endDate}')
             ) AND a.city in ({$city_allow})"
            )->order("a.id desc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $name_label = $row["name"];
                $name_label.= empty($row["staff_status"])?"":"（已离职）";
                $list[] = array("name"=>$row["name"],"user_id"=>$row["user_id"],"name_label"=>$name_label);
            }
        }
        return $list;
    }

    public static function getAllSales($city,$startDate,$endDate){
        $suffix = Yii::app()->params['envSuffix'];
        $city = empty($city)?Yii::app()->user->city():$city;
        $startDate = empty($startDate)?date("Y-m-01"):$startDate;
        $endDate = empty($endDate)?date("Y-m-31"):$endDate;
        $city_allow = City::model()->getDescendantList($city);
        $city_allow .= (empty($city_allow)) ? "'$city'" : ",'$city'";
        $rows = Yii::app()->db->createCommand()->select("a.username,f.name")
            ->from("sal_visit a")
            ->leftJoin("hr$suffix.hr_binding b","a.username = b.user_id")
            ->leftJoin("hr$suffix.hr_employee f","b.employee_id = f.id")
            ->where("a.visit_dt between '{$startDate}' and '{$endDate}' and a.city in ($city_allow)")
            ->group("a.username,f.name")
            ->queryAll();
        return $rows;
    }

    public function salepeople($endDate=""){
        $suffix = Yii::app()->params['envSuffix'];
        $city=Yii::app()->user->city();
        $endDate = empty($endDate)?date("Y-m-d"):date("Y-m-d",strtotime($endDate));
        $dateSql = " and (a.staff_status=0 or (a.staff_status=-1 and replace(leave_time,'/', '-')>='{$endDate}'))";
        $city_allow = City::model()->getDescendantList($city);
        $city_allow .= (empty($city_allow)) ? "'$city'" : ",'$city'";
//        $sql="select a.name,b.user_id from hr$suffix.hr_employee a ,hr$suffix.hr_binding b
//            WHERE  position in (SELECT id FROM hr$suffix.hr_dept where dept_class='sales') AND a.staff_status = 0 AND a.city in ($city_allow) AND a.id=b.employee_id";
        $sql = "select a.name,d.username from hr$suffix.hr_employee a, hr$suffix.hr_binding b, security$suffix.sec_user_access c,security$suffix.sec_user d 
        where a.id=b.employee_id and b.user_id=c.username and c.system_id='sal' and c.a_read_write like '%HK01%' and c.username=d.username {$dateSql} and d.city in ($city_allow)";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        return $records;
    }
    public function salepeoples($city,$endDate=""){
        $endDate = empty($endDate)?date("Y-m-d"):date("Y-m-d",strtotime($endDate));
        $dateSql = " and (a.staff_status=0 or (a.staff_status=-1 and replace(leave_time,'/', '-')>='{$endDate}'))";
        $suffix = Yii::app()->params['envSuffix'];
        $city_allow = City::model()->getDescendantList($city);
        $city_allow .= (empty($city_allow)) ? "'$city'" : ",'$city'";
//        $sql="select a.name,b.user_id from hr$suffix.hr_employee a ,hr$suffix.hr_binding b
//            WHERE  position in (SELECT id FROM hr$suffix.hr_dept where dept_class='sales') AND a.staff_status = 0 AND a.city in ($city_allow) AND a.id=b.employee_id";
        $sql = "select a.name,d.username from hr$suffix.hr_employee a, hr$suffix.hr_binding b, security$suffix.sec_user_access c,security$suffix.sec_user d 
        where a.id=b.employee_id and b.user_id=c.username and c.system_id='sal' and c.a_read_write like '%HK01%' and c.username=d.username {$dateSql} and d.city in ($city_allow)";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        // print_r('<pre>');
        //print_r($records);
        return $records;
    }

    public function fenxi($model){
        $start_dt=str_replace("/","-",$model['start_dt']);
        $end_dt=str_replace("/","-",$model['end_dt']);
        $suffix = Yii::app()->params['envSuffix'];
        $city=$model['city'];
        $city_allow = City::model()->getDescendantList($city);
        $city_allow .= (empty($city_allow)) ? "'$city'" : ",'$city'";
        $sql = "select a.*, b.name as city_name, concat(f.code,' - ',f.name) as staff,  
				d.name as visit_type_name, g.name as cust_type_name,
				h.name as district_name, VisitObjDesc(a.visit_obj) as visit_obj_name, i.cust_vip
				from sal_visit a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_visit_type d on a.visit_type = d.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.city in ($city_allow) and visit_dt<='".$end_dt."' and visit_dt>='".$start_dt."'  
			";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
//                print_r('<pre/>');
//        print_r($records);
        $jiudian=array();
        $sql2 = "select name
				from sal_cust_type
				where city='".$model['city']."' or city='99999' and type_group='1'";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shul($jiudian,$records,'cust_type_name',$record[$i]['name']));

        }
        $arr['food']=$record;
        $sql2 = "select name
				from sal_cust_type
				where city='".$model['city']."' or city='99999' and type_group='2'";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shul($jiudian,$records,'cust_type_name',$record[$i]['name']));

        }
        $arr['nofood']=$record;
        $sql2 = "select name
				from sal_visit_type
			";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shul($jiudian,$records,'visit_type_name',$record[$i]['name']));

        }
        $arr['visit']=$record;
        $sql2 = "select name
				from sal_visit_obj
			";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shul($jiudian,$records,'visit_obj_name',$record[$i]['name']));

        }
        $arr['obj']=$record;
        $sql1 = "select name
				from sal_cust_district
				where city='".$model['city']."' ";
        $record = Yii::app()->db->createCommand($sql1)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shul($jiudian,$records,'district_name',$record[$i]['name']));

        }
        $meney=$this->moneys($records);
        $arr['address']=$record;
        $arr['money']=$meney;

        return $arr;

    }


    public function fenxione($model){
        $start_dt=str_replace("/","-",$model['start_dt']);
        $end_dt=str_replace("/","-",$model['end_dt']);
        $suffix = Yii::app()->params['envSuffix'];
        $a=0;
        $sqls="select name,code from security$suffix.sec_city where region='".$model['city']."'";
        $recity = Yii::app()->db->createCommand($sqls)->queryAll();
        if(empty($recity)){
            foreach ($model['sale'] as $v) {
                $sql = "select a.*, b.name as city_name, concat(f.code,' - ',f.name) as staff,  
				d.name as visit_type_name, g.name as cust_type_name,
				h.name as district_name, VisitObjDesc(a.visit_obj) as visit_obj_name, i.cust_vip
				from sal_visit a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_visit_type d on a.visit_type = d.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.city='" . $model['city'] . "' and visit_dt<='" . $end_dt . "' and visit_dt>='" . $start_dt . "' and  f.name='".$v."'";
                $records = Yii::app()->db->createCommand($sql)->queryAll();
                $jiudian=array();
                $sql2 = "select name
				from sal_cust_type
				where city='".$model['city']."' or city='99999' and type_group='1'";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shul($jiudian,$records,'cust_type_name',$record[$i]['name']));

                }
                $arr['food']=$record;
                $sql2 = "select name
				from sal_cust_type
				where city='".$model['city']."' or city='99999' and type_group='2'";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shul($jiudian,$records,'cust_type_name',$record[$i]['name']));

                }
                $arr['nofood']=$record;
                $sql2 = "select name
				from sal_visit_type
			";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shul($jiudian,$records,'visit_type_name',$record[$i]['name']));

                }
                $arr['visit']=$record;
                $sql2 = "select name
				from sal_visit_obj
			";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shul($jiudian,$records,'visit_obj_name',$record[$i]['name']));

                }
                $arr['obj']=$record;

                $sql1 = "select name
				from sal_cust_district
				where city='".$model['city']."' ";
                $record = Yii::app()->db->createCommand($sql1)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shul($a,$records,'district_name',$record[$i]['name']));
                }
                $meney=$this->moneys($records);
                $arr['address']=$record;
                $arr['money']=$meney;
                $arr['name']=$v;
                $arr['nameType']="sale";
                $att[]=$arr;

            }
        }else{
            foreach ($recity as $v) {
                $city=$v['code'];
                $city_allow = City::model()->getDescendantList($city);
                $city_allow .= (empty($city_allow)) ? "'$city'" : ",'$city'";
                $sql = "select a.*, b.name as city_name, concat(f.code,' - ',f.name) as staff,  
				d.name as visit_type_name, g.name as cust_type_name,
				h.name as district_name, VisitObjDesc(a.visit_obj) as visit_obj_name, i.cust_vip
				from sal_visit a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_visit_type d on a.visit_type = d.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.city in ($city_allow) and visit_dt<='" . $end_dt . "' and visit_dt>='" . $start_dt . "'";
                $records = Yii::app()->db->createCommand($sql)->queryAll();

                $jiudian=array();
                $sql2 = "select name
				from sal_cust_type
				where city='".$model['city']."' or city='99999' and type_group='1'";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shul($jiudian,$records,'cust_type_name',$record[$i]['name']));

                }
                $arr['food']=$record;
                $sql2 = "select name
				from sal_cust_type
				where city='".$model['city']."' or city='99999' and type_group='2'";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shul($jiudian,$records,'cust_type_name',$record[$i]['name']));

                }
                $arr['nofood']=$record;
                $sql2 = "select name
				from sal_visit_type
			";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shul($jiudian,$records,'visit_type_name',$record[$i]['name']));

                }
                $arr['visit']=$record;
                $sql2 = "select name
				from sal_visit_obj
			";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shul($jiudian,$records,'visit_obj_name',$record[$i]['name']));

                }
                $arr['obj']=$record;
                $sql1 = "select name
				from sal_cust_district
				where city='".$city."' ";
                $record = Yii::app()->db->createCommand($sql1)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shul($a,$records,'district_name',$record[$i]['name']));
                }
                $meney=$this->moneys($records);
                $arr['address']=$record;
                $arr['money']=$meney;
                $arr['name']=$v['name'];
                $arr['nameType']="city";
                $att[]=$arr;

            }
        }


        //foreach ()
//        print_r('<pre/>');
        //  print_r($records);
        return $att;
    }

    public function fenxis($model){
        $start_dt=str_replace("/","-",$model['start_dt']);
        $end_dt=str_replace("/","-",$model['end_dt']);
        $suffix = Yii::app()->params['envSuffix'];
        $city=$model['city'];
        $city_allow = City::model()->getDescendantList($city);
        $city_allow .= (empty($city_allow)) ? "'$city'" : ",'$city'";
        $sql = "select a.*, b.name as city_name, concat(f.code,' - ',f.name) as staff,  
				d.name as visit_type_name, g.name as cust_type_name,
				h.name as district_name, VisitObjDesc(a.visit_obj) as visit_obj_name, i.cust_vip
				from sal_visit a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_visit_type d on a.visit_type = d.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.city in ($city_allow) and visit_dt<='".$end_dt."' and visit_dt>='".$start_dt."'  
			";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        $sql1 = "select name
				from sal_cust_district
				where city='".$model['city']."' ";
        $record = Yii::app()->db->createCommand($sql1)->queryAll();
        $arr=array();
        $jiudian=0;
        $sql2 = "select name
				from sal_cust_type
				where city='".$model['city']."' or city='99999' and type_group='1'";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shuls($jiudian,$records,'cust_type_name',$record[$i]['name']));

        }
        $arr['food']=$record;
        $sql2 = "select name
				from sal_cust_type
				where city='".$model['city']."' or city='99999' and type_group='2'";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shuls($jiudian,$records,'cust_type_name',$record[$i]['name']));

        }
        $arr['nofood']=$record;
        $sql2 = "select name
				from sal_visit_type
			";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shuls($jiudian,$records,'visit_type_name',$record[$i]['name']));

        }
        $arr['visit']=$record;
        $sql2 = "select name
				from sal_visit_obj
			";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shuls($jiudian,$records,'visit_obj_name',$record[$i]['name']));

        }
        $arr['obj']=$record;
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shuls($jiudian,$records,'district_name',$record[$i]['name']));

        }
        $meney=$this->moneys($records);
        $arr['address']=$record;
        $arr['money']=$meney;
//        print_r('<pre/>');
//        print_r($arr);
        return $arr;

    }


    public function fenxiones($model){
        $start_dt=str_replace("/","-",$model['start_dt']);
        $end_dt=str_replace("/","-",$model['end_dt']);
        $suffix = Yii::app()->params['envSuffix'];
        $arr=array();
        $sqls="select name,code from security$suffix.sec_city where region='".$model['city']."'";
        $recity = Yii::app()->db->createCommand($sqls)->queryAll();
        if(empty($recity)){
            foreach ($model['sale'] as $v) {
                $sql = "select a.*, b.name as city_name, concat(f.code,' - ',f.name) as staff,  
				d.name as visit_type_name, g.name as cust_type_name,
				h.name as district_name, VisitObjDesc(a.visit_obj) as visit_obj_name, i.cust_vip
				from sal_visit a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_visit_type d on a.visit_type = d.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.city='" . $model['city'] . "' and visit_dt<='" . $end_dt . "' and visit_dt>='" . $start_dt . "' and  f.name='".$v."'";
                $records = Yii::app()->db->createCommand($sql)->queryAll();
                $jiudian=0;
                $sql2 = "select name
				from sal_cust_type
				where city='".$model['city']."' or city='99999' and type_group='1'";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shuls($jiudian,$records,'cust_type_name',$record[$i]['name']));

                }
                $arr['food']=$record;
                $sql2 = "select name
				from sal_cust_type
				where city='".$model['city']."' or city='99999' and type_group='2'";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shuls($jiudian,$records,'cust_type_name',$record[$i]['name']));

                }
                $arr['nofood']=$record;
                $sql2 = "select name
				from sal_visit_type
			";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shuls($jiudian,$records,'visit_type_name',$record[$i]['name']));

                }
                $arr['visit']=$record;
                $sql2 = "select name
				from sal_visit_obj
			";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shuls($jiudian,$records,'visit_obj_name',$record[$i]['name']));

                }
                $arr['obj']=$record;
                $sql1 = "select name
				from sal_cust_district
				where city='".$model['city']."' ";
                $record = Yii::app()->db->createCommand($sql1)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shuls($jiudian,$records,'district_name',$record[$i]['name']));
                }
                $meney=$this->moneys($records);
                $arr['address']=$record;
                $arr['money']=$meney;
                $arr['name']=$v;
                $att[]=$arr;

            }
        }else{
            foreach ($recity as $v) {
                $city=$v['code'];
                $city_allow = City::model()->getDescendantList($city);
                $city_allow .= (empty($city_allow)) ? "'$city'" : ",'$city'";
                $sql = "select a.*, b.name as city_name, concat(f.code,' - ',f.name) as staff,  
				d.name as visit_type_name, g.name as cust_type_name,
				h.name as district_name, VisitObjDesc(a.visit_obj) as visit_obj_name, i.cust_vip
				from sal_visit a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_visit_type d on a.visit_type = d.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.city in ($city_allow) and visit_dt<='" . $end_dt . "' and visit_dt>='" . $start_dt . "'";
                $records = Yii::app()->db->createCommand($sql)->queryAll();
                $jiudian=0;
                $sql2 = "select name
				from sal_cust_type
				where city='".$model['city']."' or city='99999' and type_group='1'";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shuls($jiudian,$records,'cust_type_name',$record[$i]['name']));

                }
                $arr['food']=$record;
                $sql2 = "select name
				from sal_cust_type
				where city='".$model['city']."' or city='99999' and type_group='2'";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shuls($jiudian,$records,'cust_type_name',$record[$i]['name']));

                }
                $arr['nofood']=$record;
                $sql2 = "select name
				from sal_visit_type
			";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shuls($jiudian,$records,'visit_type_name',$record[$i]['name']));

                }
                $arr['visit']=$record;
                $sql2 = "select name
				from sal_visit_obj
			";
                $record = Yii::app()->db->createCommand($sql2)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shuls($jiudian,$records,'visit_obj_name',$record[$i]['name']));

                }
                $arr['obj']=$record;
                $sql1 = "select name
				from sal_cust_district
				where city='".$city."' ";
                $record = Yii::app()->db->createCommand($sql1)->queryAll();
                for($i=0;$i<count($record);$i++){
                    array_push($record[$i],$this->shuls($jiudian,$records,'district_name',$record[$i]['name']));
                }
                $meney=$this->moneys($records);
                $arr['address']=$record;
                $arr['money']=$meney;
                $arr['name']=$v['name'];
                $att[]=$arr;

            }
        }

        //foreach ()
//        print_r('<pre/>');
        //  print_r($records);
        return $att;
    }


    public function sale($model){
        $start_dt=str_replace("/","-",$model['start_dt']);
        $end_dt=str_replace("/","-",$model['end_dt']);
        $suffix = Yii::app()->params['envSuffix'];
        $city=Yii::app()->user->city;
        $user = Yii::app()->user->id;
        $sql = "select a.*, b.name as city_name, concat(f.code,' - ',f.name) as staff,  
				d.name as visit_type_name, g.name as cust_type_name,
				h.name as district_name, VisitObjDesc(a.visit_obj) as visit_obj_name, i.cust_vip
				from sal_visit a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_visit_type d on a.visit_type = d.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.city ='".$city."' and visit_dt<='".$end_dt."' and visit_dt>='".$start_dt."'  and  a.username='".$user."'
			";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
//                print_r('<pre/>');
//       print_r($records);
        $sql1 = "select name
				from sal_cust_district
				where city='".$city."' ";
        $record = Yii::app()->db->createCommand($sql1)->queryAll();
        $arr=array();
        $jiudian=0;
        $sql2 = "select name
				from sal_cust_type
				where city='".$city."' or city='99999' and type_group='1'";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shul($jiudian,$records,'cust_type_name',$record[$i]['name']));

        }
        $arr['food']=$record;
        $sql2 = "select name
				from sal_cust_type
				where city='".$city."' or city='99999' and type_group='2'";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shul($jiudian,$records,'cust_type_name',$record[$i]['name']));

        }
        $arr['nofood']=$record;
        $sql2 = "select name
				from sal_visit_type
			";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shul($jiudian,$records,'visit_type_name',$record[$i]['name']));

        }
        $arr['visit']=$record;
        $sql2 = "select name
				from sal_visit_obj
			";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shul($jiudian,$records,'visit_obj_name',$record[$i]['name']));

        }
        $arr['obj']=$record;
        $sql1 = "select name
				from sal_cust_district
				where city='".$city."' ";
        $record = Yii::app()->db->createCommand($sql1)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shul($jiudian,$records,'district_name',$record[$i]['name']));
        }
        $meney=$this->moneys($records);
        $arr['address']=$record;
        $arr['money']=$meney;

        return $arr;

    }

    public function sales($model){
        $start_dt=str_replace("/","-",$model['start_dt']);
        $end_dt=str_replace("/","-",$model['end_dt']);
        $suffix = Yii::app()->params['envSuffix'];
        $city=Yii::app()->user->city;
        $user = Yii::app()->user->id;
        $sql = "select a.*, b.name as city_name, concat(f.code,' - ',f.name) as staff,  
				d.name as visit_type_name, g.name as cust_type_name,
				h.name as district_name, VisitObjDesc(a.visit_obj) as visit_obj_name, i.cust_vip
				from sal_visit a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id 
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_visit_type d on a.visit_type = d.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.city ='".$city."' and visit_dt<='".$end_dt."' and visit_dt>='".$start_dt."'  and  a.username='".$user."'
			";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        $sql1 = "select name
				from sal_cust_district
				where city='".$city."' ";
        $record = Yii::app()->db->createCommand($sql1)->queryAll();
        $arr=array();
        $jiudian=0;
        $sql2 = "select name
				from sal_cust_type
				where city='".$city."' or city='99999' and type_group='1'";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shuls($jiudian,$records,'cust_type_name',$record[$i]['name']));

        }
        $arr['food']=$record;
        $sql2 = "select name
				from sal_cust_type
				where city='".$city."' or city='99999' and type_group='2'";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shuls($jiudian,$records,'cust_type_name',$record[$i]['name']));

        }
        $arr['nofood']=$record;
        $sql2 = "select name
				from sal_visit_type
			";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shuls($jiudian,$records,'visit_type_name',$record[$i]['name']));

        }
        $arr['visit']=$record;
        $sql2 = "select name
				from sal_visit_obj
			";
        $record = Yii::app()->db->createCommand($sql2)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shuls($jiudian,$records,'visit_obj_name',$record[$i]['name']));

        }
        $arr['obj']=$record;
        $sql1 = "select name
				from sal_cust_district
				where city='".$city."' ";
        $record = Yii::app()->db->createCommand($sql1)->queryAll();
        for($i=0;$i<count($record);$i++){
            array_push($record[$i],$this->shuls($jiudian,$records,'district_name',$record[$i]['name']));

        }
        $meney=$this->moneys($records);
        $arr['address']=$record;
        $arr['money']=$meney;

        return $arr;

    }

    public function shul($sum,$records,$name,$names){
        $all=0;
        $sum_arr=array();
        $sum=array();
        for($i=0;$i<count($records);$i++){
            if(strpos($records[$i][$name],$names)!==false&&(strpos($records[$i]['visit_obj_name'],'签单')!==false)){
                $sqlid="select count(visit_id) as sum from  sal_visit_info where field_id in ({$this->allQ}) and field_value>'0' and visit_id='".$records[$i]['id']."'";
                $model = Yii::app()->db->createCommand($sqlid)->queryRow();
                if($model){
                    $sum_arr[]=$model['sum'];
                }
                $sql="select sum(field_value) as money_amt from sal_visit_info where field_id in ({$this->minQ}) and field_value>'0' and visit_id = '".$records[$i]['id']."'";
                $row = Yii::app()->db->createCommand($sql)->queryRow();
                if($row){
                    $sum[]=$row["money_amt"];
                }
            }
            if(strpos($records[$i][$name],$names)!==false){
                $all=$all+1;
            }
        }
        if(!empty($sum)){
            $money=array_sum($sum);
        }else{
            $money=0;
        }
        if(!empty($sum_arr)){
            $sums=array_sum($sum_arr);
        }else{
            $sums=0;
        }
        $messz=$all."/".$sums."/".$money;
        return $messz;
    }

    public function shuls($sum,$records,$name,$names){
        $all=0;
        $sum_arr=array();
        $sum=array();
        for($i=0;$i<count($records);$i++){
            if(strpos($records[$i][$name],$names)!==false&&(strpos($records[$i]['visit_obj_name'],'签单')!==false)){
                $sqlid="select count(visit_id) as sum from  sal_visit_info where field_id in ({$this->allQ}) and field_value>'0' and visit_id='".$records[$i]['id']."'";
                $model = Yii::app()->db->createCommand($sqlid)->queryRow();
                if($model){
                    $sum_arr[]=$model['sum'];
                }
                $sql="select sum(field_value) as money_amt from sal_visit_info where field_id in ({$this->minQ}) and field_value>'0' and visit_id = '".$records[$i]['id']."'";
                $row = Yii::app()->db->createCommand($sql)->queryRow();
                if($row){
                    $sum[]=$row["money_amt"];
                }
            }
            if(strpos($records[$i][$name],$names)!==false){
                $all=$all+1;
            }
        }
        if(!empty($sum)){
            $money=array_sum($sum);
        }else{
            $money=0;
        }
        if(!empty($sum_arr)){
            $sums=array_sum($sum_arr);
        }else{
            $sums=0;
        }
        $messz['sum']=$sums;
        $messz['money']=$money;
        $messz['all']=$all;
        return $messz;
    }

    public function moneys($records){
        $suffix = Yii::app()->params['envSuffix'];
        $a=0;
        $sum_arr=array();
        for($i=0;$i<count($records);$i++){
            if(strpos($records[$i]['visit_obj_name'],'签单')!==false){
                $sqlid="select count(visit_id) as sum from  sal_visit_info where field_id in ({$this->allQ}) and field_value>'0' and visit_id='".$records[$i]['id']."'";
                $model = Yii::app()->db->createCommand($sqlid)->queryRow();
                if($model){
                    $sum_arr[]=$model['sum'];
                }
                $sql="select sum(field_value) as money_amt from sal_visit_info where field_id in ({$this->minQ}) and field_value>'0' and visit_id = '".$records[$i]['id']."'";
                $row = Yii::app()->db->createCommand($sql)->queryRow();
                if($row){
                    $sum[]=$row["money_amt"];
                }
            }
        }
        if(!empty($sum)){
            $sums=array_sum($sum_arr);
            $money['money']=array_sum($sum);
            $money['sum']=$sums;
            $money['all']=count($records);
        }else{
            $money['money']=0;
            $money['sum']=0;
            $money['all']=count($records);
        }
        return $money;
    }

//    public function moneyone($records){
//        $suffix = Yii::app()->params['envSuffix'];
//        for($i=0;$i<count($records);$i++){
//            if(strpos($records[$i]['visit_obj_name'],'签单')!==false){
//                $sql="select * from sal_visit_info where visit_id = '".$records[$i]['id']."'";
//                $rows = Yii::app()->db->createCommand($sql)->queryAll();
//                foreach ($rows as $v){
//                    $arr[$v['field_id']]=$v['field_value'];
//                }
//                if(empty($arr['svc_A7'])){
//                    $arr['svc_A7']=0;
//                }
//                if(empty($arr['svc_B6'])){
//                    $arr['svc_B6']=0;
//                }
//                if(empty($arr['svc_C7'])){
//                    $arr['svc_C7']=0;
//                }
//                if(empty($arr['svc_D6'])){
//                    $arr['svc_A7']=0;
//                }
//                if(empty($arr['svc_E7'])){
//                    $arr['svc_E7']=0;
//                }
//                if(empty($arr['svc_F4'])){
//                    $arr['svc_F4']=0;
//                }
//                if(empty($arr['svc_G3'])){
//                    $arr['svc_G3']=0;
//                }
//                $sum[]=$arr['svc_A7']+$arr['svc_B6']+$arr['svc_C7']+$arr['svc_D6']+$arr['svc_E7']+$arr['svc_F4']+$arr['svc_G3'];
//            }
//        }
//        if(!empty($sum)){
//            $money=array_sum($sum);
//        }else{
//            $money=0;
//        }
//        return $money;
//    }

    public function retrieveDatas($model){
        Yii::$enableIncludePath = false;
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        //spl_autoload_unregister(array('YiiBase','autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel;
        $objReader  = PHPExcel_IOFactory::createReader('Excel2007');
        $path = Yii::app()->basePath.'/commands/template/sale.xlsx';
        $objPHPExcel = $objReader->load($path);
//        print_r("<pre>");
//        print_r($model);
        $countExcelList=array();
        $optionList = array(
            array("name"=>"拜访类型",'value'=>"visit"),
            array("name"=>"拜访目的",'value'=>"obj"),
            array("name"=>"区域",'value'=>"address"),
            array("name"=>"客服类别（餐饮）",'value'=>"food"),
            array("name"=>"客服类别（非餐饮）",'value'=>"nofood"),
        );
        $i=3;
        $ex=$i;
        $i1=$i+1;
        $i13=$i+2;
        if(!empty($model['all'])){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'部门总数据') ;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i1,'总拜访量'.$model['all']['money']['all'].'签单量：'.$model['all']['money']['sum'].'签单金额'.$model['all']['money']['money']) ;
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$i.':AC'.$i);
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$i1.':AC'.$i1);
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(25);
            $objPHPExcel->getActiveSheet()->getRowDimension($i1)->setRowHeight(25);
            foreach ($optionList as $item){
                $a=$i13;
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,$item["name"]) ;
                $itemName = $item["value"];
                if(count($model['all'][$itemName])>0){
                    $for_i=0;
                    foreach ($model['all'][$itemName] as $numKey=>$row){
                        $for_i++;
                        if($for_i!==1&&$for_i%7==1){//换行
                            $for_i = 1;
                            $i13++;
                        }
                        $num = ($for_i-1)*4;
                        $dataList = explode("/",$model['all'][$itemName][$numKey][0]);
                        $dataList = count($dataList)==3?$dataList:array(0,0,0);
                        $objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($num+1, $i13, $model['all'][$itemName][$numKey]['name']);
                        $objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($num+2, $i13, $dataList[0]);
                        $objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($num+3, $i13, $dataList[1]);
                        $objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($num+4, $i13, $dataList[2]);
                    }
                }
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
                $i13++;
            }
            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        //'style' => PHPExcel_Style_Border::BORDER_THICK,//边框是粗的
                        'style' => PHPExcel_Style_Border::BORDER_THIN,//细边框
                        'color' => array('argb' => '0xCC000000'),
                    ),
                ),
            );
            $objPHPExcel->getActiveSheet()->getStyle('A'.$ex.':AC'.($i13-1))->applyFromArray($styleArray);
        }
        if(!empty($model['one'])){
            $i=$i13+3;

            foreach ($model['one'] as $arr){
                $ex=$i;
                $i1=$i+1;
                $i13=$i+2;
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$arr['name']) ;
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i1,'总拜访量'.$arr['money']['all'].'签单量：'.$arr['money']['sum'].'签单金额'.$arr['money']['money']) ;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$i.':AC'.$i);
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$i1.':AC'.$i1);
                $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(25);
                $objPHPExcel->getActiveSheet()->getRowDimension($i1)->setRowHeight(25);
                foreach ($optionList as $item){
                    $a=$i13;
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,$item["name"]) ;
                    $itemName = $item["value"];
                    if(count($arr[$itemName])>0){
                        $for_i=0;
                        foreach ($arr[$itemName] as $numKey=>$row){
                            $for_i++;
                            if($for_i!==1&&$for_i%7==1){//换行
                                $for_i = 1;
                                $i13++;
                            }
                            $num = ($for_i-1)*4;
                            $dataList = explode("/",$arr[$itemName][$numKey][0]);
                            $dataList = count($dataList)==3?$dataList:array(0,0,0);
                            $objPHPExcel->getActiveSheet()
                                ->setCellValueByColumnAndRow($num+1, $i13, $arr[$itemName][$numKey]['name']);
                            $objPHPExcel->getActiveSheet()
                                ->setCellValueByColumnAndRow($num+2, $i13, $dataList[0]);
                            $objPHPExcel->getActiveSheet()
                                ->setCellValueByColumnAndRow($num+3, $i13, $dataList[1]);
                            $objPHPExcel->getActiveSheet()
                                ->setCellValueByColumnAndRow($num+4, $i13, $dataList[2]);

                            if($arr["nameType"]=='sale'){
                                if(!key_exists($arr['name'],$countExcelList)){
                                    $staffList = self::getEmployeeListForName($arr['name']);
                                    $staffList = $staffList?$staffList:array("city_name"=>'',"entry_time"=>'');
                                    $countExcelList[$arr['name']]=array(
                                        "name"=>$arr['name'],//销售姓名
                                        "city"=>$staffList['city_name'],//所属地区
                                        "entryTime"=>$staffList['entry_time'],//销售入职时间
                                        "moSum"=>0,//陌拜数量
                                        "moNum"=>0,//陌拜签单量
                                        "moAmt"=>0,//陌拜金额
                                        "keSum"=>0,//转介绍数量
                                        "keNum"=>0,//转介绍签单量
                                        "keAmt"=>0,//转介绍金额
                                    );
                                }
                                if($arr[$itemName][$numKey]['name']=="陌拜"){
                                    $countExcelList[$arr['name']]["moSum"]+=empty($dataList[0])||!is_numeric($dataList[0])?0:$dataList[0];
                                    $countExcelList[$arr['name']]["moNum"]+=empty($dataList[1])||!is_numeric($dataList[1])?0:$dataList[1];
                                    $countExcelList[$arr['name']]["moAmt"]+=empty($dataList[2])||!is_numeric($dataList[2])?0:$dataList[2];
                                }
                                if($arr[$itemName][$numKey]['name']=="客户资源"){
                                    $countExcelList[$arr['name']]["keSum"]+=empty($dataList[0])||!is_numeric($dataList[0])?0:$dataList[0];
                                    $countExcelList[$arr['name']]["keNum"]+=empty($dataList[1])||!is_numeric($dataList[1])?0:$dataList[1];
                                    $countExcelList[$arr['name']]["keAmt"]+=empty($dataList[2])||!is_numeric($dataList[2])?0:$dataList[2];
                                }
                            }
                        }
                    }
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
                    $i13++;
                }
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            //'style' => PHPExcel_Style_Border::BORDER_THICK,//边框是粗的
                            'style' => PHPExcel_Style_Border::BORDER_THIN,//细边框
                            'color' => array('argb' => '0xCC000000'),
                        ),
                    ),
                );
                $objPHPExcel->getActiveSheet()->getStyle('A'.$ex.':AC'.($i13-1))->applyFromArray($styleArray);
                $i=$i13+2;
            }
        }

        //增加汇总页
        if(!empty($countExcelList)){
            $sheetIndex = $objPHPExcel->getActiveSheetIndex();
            $sheetIndex++;
            $objPHPExcel->createSheet();
            $objPHPExcel->setActiveSheetIndex($sheetIndex);
            $objPHPExcel->getActiveSheet()->setTitle("汇总");
            $countRow=1;
            $titleList = array("销售姓名","所属地区","销售入职时间","陌拜数量","陌拜签单量","陌拜金额","转介绍数量","转介绍签单量","转介绍金额");
            $bodyList = array("name","city","entryTime","moSum","moNum","moAmt","keSum","keNum","keAmt");
            foreach ($titleList as $key=>$name){
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($key, $countRow, $name);
            }
            foreach ($countExcelList as $row){
                $countRow++;
                foreach ($bodyList as $countCol=>$keyStr){
                    $name = key_exists($keyStr,$row)?$row[$keyStr]:"";
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($countCol, $countRow, $name);
                }
            }
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        ob_start();
        $objWriter->save('php://output');
        $output = ob_get_clean();
        spl_autoload_register(array('YiiBase','autoload'));
        $time=time();
        $str="templates/sale_".$time.".xlsx";
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="'.$str.'"');
        header("Content-Transfer-Encoding:binary");
        echo $output;
    }

    public static function getEmployeeListForName($name){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("a.*,b.name as city_name")
            ->from("hr{$suffix}.hr_employee a")
            ->leftJoin("security{$suffix}.sec_city b","a.city=b.code")
            ->where("a.name=:name",array(":name"=>$name))
            ->order("staff_status desc,id desc")
            ->queryRow();
        return $row;
    }

    public function retrieveData($model){
        Yii::$enableIncludePath = false;
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        //spl_autoload_unregister(array('YiiBase','autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel;
        $objReader  = PHPExcel_IOFactory::createReader('Excel2007');
        $path = Yii::app()->basePath.'/commands/template/sale.xlsx';
        $objPHPExcel = $objReader->load($path);

        $optionList = array(
            array("name"=>"拜访类型",'value'=>"visit"),
            array("name"=>"拜访目的",'value'=>"obj"),
            array("name"=>"区域",'value'=>"address"),
            array("name"=>"客服类别（餐饮）",'value'=>"food"),
            array("name"=>"客服类别（非餐饮）",'value'=>"nofood"),
        );
        if(!empty($model['all'])){
            $i=3;
            $ex=$i;
            $i1=$i+1;
            $i2=$i+2;
            $i13=$i+2;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'个人总数据') ;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i1,'总拜访量'.$model['all']['money']['all'].'签单量：'.$model['all']['money']['sum'].'签单金额'.$model['all']['money']['money']) ;
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$i.':AC'.$i);
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$i1.':AC'.$i1);
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(25);
            $objPHPExcel->getActiveSheet()->getRowDimension($i1)->setRowHeight(25);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i2,'拜访类型') ;
            foreach ($optionList as $item){
                $a=$i13;
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,$item["name"]) ;
                $itemName = $item["value"];
                if(count($model['all'][$itemName])>0){
                    $for_i=0;
                    foreach ($model['all'][$itemName] as $numKey=>$row){
                        $for_i++;
                        if($for_i!==1&&$for_i%7==1){//换行
                            $for_i = 1;
                            $i13++;
                        }
                        $num = ($for_i-1)*4;
                        $dataList = explode("/",$model['all'][$itemName][$numKey][0]);
                        $dataList = count($dataList)==3?$dataList:array(0,0,0);
                        $objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($num+1, $i13, $model['all'][$itemName][$numKey]['name']);
                        $objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($num+2, $i13, $dataList[0]);
                        $objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($num+3, $i13, $dataList[1]);
                        $objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($num+4, $i13, $dataList[2]);
                    }
                }
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
                $i13++;
            }
            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        //'style' => PHPExcel_Style_Border::BORDER_THICK,//边框是粗的
                        'style' => PHPExcel_Style_Border::BORDER_THIN,//细边框
                        'color' => array('argb' => '0xCC000000'),
                    ),
                ),
            );
            $objPHPExcel->getActiveSheet()->getStyle('A'.$ex.':AC'.($i13-1))->applyFromArray($styleArray);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        ob_start();
        $objWriter->save('php://output');
        $output = ob_get_clean();
        spl_autoload_register(array('YiiBase','autoload'));
        $time=time();
        $str="templates/sale_".$time.".xlsx";
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="'.$str.'"');
        header("Content-Transfer-Encoding:binary");
        echo $output;
    }

    public function Summary($model){
        $start_dt=str_replace("/","-",$model['start_dt']);
        $end_dt=str_replace("/","-",$model['end_dt']);
        $suffix = Yii::app()->params['envSuffix'];
        $models=array();
        $classList = CGetName::getSetMenuTypeList("serviceTypeClass");
        $classBySvcList = $this->classBySvcList;
        foreach ($model['sale'] as $code=>$peoples){
            $sum_arr=array();
            $people=array();
            $sql = "select a.city, a.username, sum(convert(b.field_value, decimal(12,2))) as money 
				from sal_visit a force index (idx_visit_02), sal_visit_info b   
				where a.id=b.visit_id and b.field_id in ({$this->minQ}) 
				and a.visit_dt >= '$start_dt'and a.visit_dt <= '$end_dt' and  a.visit_obj like '%10%' and a.username ='$peoples' 
				group by a.city, a.username 
			";
            $records = Yii::app()->db->createCommand($sql)->queryAll();
            if(empty($records[0]['money'])){
                $people['money']=0;
            }else{
                $people['money']=floatval($records[0]['money']);
            }
//            print_r('<pre/>');
//            print_r($records);
            $localOffice = Yii::t("report","local office");
            $sqls="select a.name as cityname,d.name as names,d.staff_status,d.entry_time,
                dept.name as dept_name,if(d.office_id=0,'{$localOffice}',office.name) as office_name
                from hr$suffix.hr_binding b 
                LEFT JOIN hr$suffix.hr_employee d on d.id=b.employee_id
                LEFT JOIN hr$suffix.hr_dept dept on dept.id=d.position
                LEFT JOIN hr$suffix.hr_office office on office.id=d.office_id
                LEFT JOIN security$suffix.sec_user c on c.username=b.user_id
                LEFT JOIN security$suffix.sec_city a on d.city=a.code
                where b.user_id='".$peoples."'";
            $cname = Yii::app()->db->createCommand($sqls)->queryRow();
            $sql1="select id,visit_dt  from sal_visit where username='".$peoples."'  and  visit_dt >= '$start_dt'and visit_dt <= '$end_dt' and visit_obj like '%10%'";
            $arr = Yii::app()->db->createCommand($sql1)->queryAll();
            $start_dt1= date("Y-m-01", strtotime($start_dt));
            $end_dt1=date("Y-m-31", strtotime($end_dt));
            $sql_rank="select now_score  from sal_rank where username='".$peoples."'  and  month >= '$start_dt1' and month <= '$end_dt1' order by month desc";//add order by desc
            $rank = Yii::app()->db->createCommand($sql_rank)->queryRow();
            foreach ($arr as $id){//svc_H6(蔚諾服務的金額)
                $sqlid="select count(visit_id) as sum from  sal_visit_info where field_id in ({$this->allQ}) and field_value>'0' and visit_id='".$id['id']."'";
                $sum = Yii::app()->db->createCommand($sqlid)->queryRow();
                $sum_arr[]=$sum['sum'];
            }
            if(!empty($sum_arr)){
                $sums=array_sum($sum_arr);
            }else{
                $sums=0;
            }
            $sqlbf="select count(id) from sal_visit where username='".$peoples."'  and  visit_dt >= '$start_dt'and visit_dt <= '$end_dt' ";
            $baifang = Yii::app()->db->createCommand($sqlbf)->queryScalar();
            $people['visit']=$baifang;
            $people['singular']=$sums;
            $people['dept_name']=$cname['dept_name'];
            $people['office_name']=$cname['office_name'];
            $people['entry_time']=$cname['entry_time'];
            $people['cityname']=$cname['cityname'];
            $people['names']=$cname['names'].(intval($cname['staff_status'])=="-1"?"（离职）":"");//员工名字
            $people['username']=$peoples;//账号名字
            //其他金额
            foreach ($classList as $set_id=>$set_name){
                $people["amt_".$set_id]=0;//按金额
                $people["sum_".$set_id]=0;//按单数
            }
            foreach ($arr as $arrs){
                foreach ($classBySvcList as $set_id=>$charArr){
                    if(!empty($charArr)){
                        $charSql = implode("','",$charArr);
                        $sql2="select field_value from sal_visit_info where visit_id='".$arrs['id']."' and field_id in ('{$charSql}') ";
                        $money2 = Yii::app()->db->createCommand($sql2)->queryRow();
                        if($money2&&!empty($money2['field_value'])){
                            $people["amt_".$set_id]+=floatval($money2['field_value']);//按金额
                            $people["sum_".$set_id]++;//按单数
                        }
                    }
                }
            }
            $sql_rank_name="select * from sal_level where start_fraction <='".$rank['now_score']."' and end_fraction >='".$rank['now_score']."'";
            $rank_name= Yii::app()->db->createCommand($sql_rank_name)->queryRow();
            $people['rank']=$rank_name['level'];
            $models[$code]=$people;

        }
        $arraycol = array_column($models,$model['sort']);
        array_multisort($arraycol,SORT_DESC,$models);
        return $models;
    }

    public function performanceDatas($model){
        $classList = CGetName::getSetMenuTypeList("serviceTypeClass");
        Yii::$enableIncludePath = false;
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        spl_autoload_unregister(array('YiiBase','autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel;
        $objReader  = PHPExcel_IOFactory::createReader('Excel2007');
        $path = Yii::app()->basePath.'/commands/template/performance.xlsx';
        $objPHPExcel = $objReader->load($path);
        for($i=0;$i<count($model['all']);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+3), $model['all'][$i]['names']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+3), $model['all'][$i]['cityname']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+3), $model['all'][$i]['singular']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+3), $model['all'][$i]['money']) ;
            $keyCum = 3;
            foreach ($classList as $set_id=>$set_name){
                $keyCum++;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($keyCum,$i+3,$model['all'][$i]['amt_'.$set_id]);
                $keyCum++;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($keyCum,$i+3,$model['all'][$i]['sum_'.$set_id]);

            }
            /*
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+3), $model['all'][$i]['svc_A7']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+3), $model['all'][$i]['svc_A7s']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+3), $model['all'][$i]['svc_B6']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+3), $model['all'][$i]['svc_B6s']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($i+3), $model['all'][$i]['svc_C7']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+3), $model['all'][$i]['svc_C7s']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('K'.($i+3), $model['all'][$i]['svc_D6']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('L'.($i+3), $model['all'][$i]['svc_D6s']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('M'.($i+3), $model['all'][$i]['svc_E7']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('N'.($i+3), $model['all'][$i]['svc_E7s']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('O'.($i+3), $model['all'][$i]['svc_F4']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('P'.($i+3), $model['all'][$i]['svc_F4s']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('Q'.($i+3), $model['all'][$i]['svc_G3']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('R'.($i+3), $model['all'][$i]['svc_G3s']) ;
            */
        }
//        print_r('<pre/>');
//        print_r($model['all']);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        ob_start();
        $objWriter->save('php://output');
        $output = ob_get_clean();
        spl_autoload_register(array('YiiBase','autoload'));
        $time=time();
        $str="templates/performance_".$time.".xlsx";
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="'.$str.'"');
        header("Content-Transfer-Encoding:binary");
        echo $output;
    }

    public function TurnoverExcel($arr){
        $start_dt=str_replace("/","-",$arr['start_dt']);
        $end_dt=str_replace("/","-",$arr['end_dt']);
        $suffix = Yii::app()->params['envSuffix'];
        $city=General::getCityName($arr['city']);
        Yii::$enableIncludePath = false;
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        spl_autoload_unregister(array('YiiBase','autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel;
        $objReader  = PHPExcel_IOFactory::createReader('Excel2007');
        $path = Yii::app()->basePath.'/commands/template/TurnoverExcel.xlsx';
        $objPHPExcel = $objReader->load($path);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:C1');
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '销售成交率报表 -'.$city) ;
        $i=3;
//        $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(25);
        foreach ($arr['sale'] as $man){
            $sql1="select employee_name  from hr$suffix.hr_binding  where user_id='$man' ";
            $name = Yii::app()->db->createCommand($sql1)->queryScalar();
            $ia=$this->TurnoverDate($man,1,$start_dt,$end_dt);
            $ib=$this->TurnoverDate($man,2,$start_dt,$end_dt);
            $inv=$this->TurnoverDate($man,4,$start_dt,$end_dt);
            $poapyinx=$this->TurnoverDate($man,5,$start_dt,$end_dt);
            $kongqi=$this->TurnoverDate($man,7,$start_dt,$end_dt);
            //数据
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$i.':C'.$i);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, '拜访日期'.$arr['start_dt'].'-'.$arr['end_dt']) ;
            $objPHPExcel->getActiveSheet()->mergeCells('D'.$i.':F'.$i);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i, '员工 ：'.$name) ;
            $i=$i+1;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'服务类型');
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,'IA');
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,'IB');
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,'INV');
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,'飘盈香');
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,'空气净化机');
            $i=$i+1;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'拜访数量');
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$ia['visit']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$ib['visit']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$inv['visit']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$poapyinx['visit']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$kongqi['visit']);
            $i=$i+1;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'签单数量');
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$ia['sign']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$ib['sign']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$inv['sign']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$poapyinx['sign']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$kongqi['sign']);
            $i=$i+1;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'签单成交率');
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$ia['turnover']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$ib['turnover']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$inv['turnover']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$poapyinx['turnover']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$kongqi['turnover']);
            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        //'style' => PHPExcel_Style_Border::BORDER_THICK,//边框是粗的
                        'style' => PHPExcel_Style_Border::BORDER_THIN,//细边框
                        'color' => array('argb' => '999999'),
                    ),
                ),
            );
            $a=$i-4;
            $objPHPExcel->getActiveSheet()->getStyle('A'.$a.':F'.$i)->applyFromArray($styleArray);
            $i=$i+2;
        }
//        print_r('<pre/>');
//        print_r($model['all']);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        ob_start();
        $objWriter->save('php://output');
        $output = ob_get_clean();
        spl_autoload_register(array('YiiBase','autoload'));
        $time=time();
        $str="templates/TurnoverExcel_".$time.".xlsx";
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="'.$str.'"');
        header("Content-Transfer-Encoding:binary");
        echo $output;
    }

    public function TurnoverDate($man,$a,$start_dt,$end_dt){
        $att=array();
        $sql1="select cust_name from sal_visit  where username='$man' and service_type like '%$a%'  and visit_dt >= '$start_dt'and visit_dt <= '$end_dt' group by cust_name";
        $ai = Yii::app()->db->createCommand($sql1)->queryAll();
        if(empty($ai)){
            $ai=0;
        }else{
            $ai=count($ai);
        }

        $sql2="select cust_name  from sal_visit  where username='$man' and service_type like '%$a%' and visit_obj like '%10%'  and visit_dt >= '$start_dt'and visit_dt <= '$end_dt' group by cust_name";
        $b = Yii::app()->db->createCommand($sql2)->queryAll();
        if(empty($b)){
            $b=0;
        }else{
            $b=count($b);
        }
        if($ai==0){
            $c=0;
        }else{
            $c=$b/$ai;
        }
        $att['visit']=$ai;
        $att['sign']=$b;
        $att['turnover']=(round($c,2)*100)."%";
        return $att;
    }
}
