<?php
/* Reimbursement Form */

class ReportVisitForm extends CReportForm
{
	public $staffs;
	public $staffs_desc;
	
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
	}

	public function city(){
        $suffix = Yii::app()->params['envSuffix'];
        $model = new City();
        $city=Yii::app()->user->city();
        $records=$model->getDescendant($city);
        array_unshift($records,$city);
        $cityname=array();
        foreach ($records as $k) {
            $sql = "select name from security$suffix.sec_city where code='" . $k . "'";
            $name = Yii::app()->db->createCommand($sql)->queryAll();
            $cityname[]=$name[0]['name'];
        }
        $city=array_combine($records,$cityname);
        return $city;
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
				where a.city in ($city_allow) and a.shift is NULL and visit_dt<='".$end_dt."' and visit_dt>='".$start_dt."'  
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
				where a.city='" . $model['city'] . "' and a.shift is NULL and visit_dt<='" . $end_dt . "' and visit_dt>='" . $start_dt . "' and  f.name='".$v."'";
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
				where a.city in ($city_allow) and a.shift IS NULL and visit_dt<='".$end_dt."' and visit_dt>='".$start_dt."'  
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
				where a.city='" . $model['city'] . "' and a.shift is null and visit_dt<='" . $end_dt . "' and visit_dt>='" . $start_dt . "' and  f.name='".$v."'";
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
				where a.city ='".$city."' and a.shift is null and visit_dt<='".$end_dt."' and visit_dt>='".$start_dt."'  and  a.username='".$user."'
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
				where a.city ='".$city."' and a.shift is null and visit_dt<='".$end_dt."' and visit_dt>='".$start_dt."'  and  a.username='".$user."'
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
            if(strpos($records[$i][$name],$names)!==false&&(strpos($records[$i]['visit_obj_name'],'签单')!==false||strpos($records[$i]['visit_obj_name'],'续约')!==false)){
                $sqlid="select count(visit_id) as sum from  sal_visit_info where field_id in ('svc_A7','svc_B6','svc_C7','svc_D6','svc_E7') and field_value>'0' and visit_id='".$records[$i]['id']."'";
                $model = Yii::app()->db->createCommand($sqlid)->queryRow();
                $sum_arr[]=$model['sum'];
                $sql="select * from sal_visit_info where visit_id = '".$records[$i]['id']."'";
                $rows = Yii::app()->db->createCommand($sql)->queryAll();
                foreach ($rows as $v){
                    $arr[$v['field_id']]=$v['field_value'];
                }
                if(empty($arr['svc_A7'])){
                    $arr['svc_A7']=0;
                }
                if(empty($arr['svc_B6'])){
                    $arr['svc_B6']=0;
                }
                if(empty($arr['svc_C7'])){
                    $arr['svc_C7']=0;
                }
                if(empty($arr['svc_D6'])){
                    $arr['svc_D6']=0;
                }
                if(empty($arr['svc_E7'])){
                    $arr['svc_E7']=0;
                }
                if(empty($arr['svc_F4'])){
                    $arr['svc_F4']=0;
                }
                if(empty($arr['svc_G3'])){
                    $arr['svc_G3']=0;
                }
                $sum[]=$arr['svc_A7']+$arr['svc_B6']+$arr['svc_C7']+$arr['svc_D6']+$arr['svc_E7']+$arr['svc_F4']+$arr['svc_G3'];
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
            if(strpos($records[$i][$name],$names)!==false&&(strpos($records[$i]['visit_obj_name'],'签单')!==false||strpos($records[$i]['visit_obj_name'],'续约')!==false)){
                $sqlid="select count(visit_id) as sum from  sal_visit_info where field_id in ('svc_A7','svc_B6','svc_C7','svc_D6','svc_E7') and field_value>'0' and visit_id='".$records[$i]['id']."'";
                $model = Yii::app()->db->createCommand($sqlid)->queryRow();
                $sum_arr[]=$model['sum'];
                $sql="select * from sal_visit_info where visit_id = '".$records[$i]['id']."'";
                $rows = Yii::app()->db->createCommand($sql)->queryAll();
                foreach ($rows as $v){
                    $arr[$v['field_id']]=$v['field_value'];
                }
                if(empty($arr['svc_A7'])){
                    $arr['svc_A7']=0;
                }
                if(empty($arr['svc_B6'])){
                    $arr['svc_B6']=0;
                }
                if(empty($arr['svc_C7'])){
                    $arr['svc_C7']=0;
                }
                if(empty($arr['svc_D6'])){
                    $arr['svc_D6']=0;
                }
                if(empty($arr['svc_E7'])){
                    $arr['svc_E7']=0;
                }
                if(empty($arr['svc_F4'])){
                    $arr['svc_F4']=0;
                }
                if(empty($arr['svc_G3'])){
                    $arr['svc_G3']=0;
                }
                $sum[]=$arr['svc_A7']+$arr['svc_B6']+$arr['svc_C7']+$arr['svc_D6']+$arr['svc_E7']+$arr['svc_F4']+$arr['svc_G3'];
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
                // 签单数量
                $sqlid_sum="select count(visit_id) as sum from  sal_visit_info where field_id in ('svc_A7','svc_B6','svc_C7','svc_D6','svc_E7','svc_F4','svc_G3') and field_value>'0' and visit_id='".$records[$i]['id']."'";
                $moder_sum = Yii::app()->db->createCommand($sqlid_sum)->queryRow();
                //签单金额
//                $sqlid="select sum(convert(field_value, decimal(12,2))) as money  from  sal_visit_info where field_id in ('svc_A7','svc_B6','svc_C7','svc_D6','svc_E7') and field_value>'0' and visit_id='".$records[$i]['id']."'";
//                $model_money = Yii::app()->db->createCommand($sqlid)->queryRow();

                $sum_arr[]=$moder_sum['sum'];
	            $sql="select * from sal_visit_info where visit_id = '".$records[$i]['id']."'";
                $rows = Yii::app()->db->createCommand($sql)->queryAll();
               foreach ($rows as $v){
                   $arr[$v['field_id']]=$v['field_value'];
               }
                if(empty($arr['svc_A7'])){
                    $arr['svc_A7']=0;
                }
                if(empty($arr['svc_B6'])){
                    $arr['svc_B6']=0;
                }
                if(empty($arr['svc_C7'])){
                    $arr['svc_C7']=0;
                }
                if(empty($arr['svc_D6'])){
                    $arr['svc_D6']=0;
                }
                if(empty($arr['svc_E7'])){
                    $arr['svc_E7']=0;
                }
                if(empty($arr['svc_F4'])){
                    $arr['svc_F4']=0;
                }
                if(empty($arr['svc_G3'])){
                    $arr['svc_G3']=0;
                }
                $sum[]=$arr['svc_A7']+$arr['svc_B6']+$arr['svc_C7']+$arr['svc_D6']+$arr['svc_E7'];//+$arr['svc_F4']+$arr['svc_G3']

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
        if(!empty($model['all'])){
            $i=3;
            $ex=$i;
            $i1=$i+1;
            $i2=$i+2;
            $i13=$i+2;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'部门总数据') ;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i1,'总拜访量'.$model['all']['money']['all'].'签单量：'.$model['all']['money']['sum'].'签单金额'.$model['all']['money']['money']) ;
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$i.':AC'.$i);
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$i1.':AC'.$i1);
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(25);
            $objPHPExcel->getActiveSheet()->getRowDimension($i1)->setRowHeight(25);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i2,'拜访类型') ;
            $a=$i13;
            for($o=1;$o<count($model['all']['visit']);$o++){
                if($o%7==1){
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$model['all']['visit'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$model['all']['visit'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$model['all']['visit'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$model['all']['visit'][$o][0]['money']) ;
                }
                if($o%7==2){
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$model['all']['visit'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$model['all']['visit'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$model['all']['visit'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$model['all']['visit'][$o][0]['money']) ;
                }
                if($o%7==3){
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$model['all']['visit'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$model['all']['visit'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$model['all']['visit'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$model['all']['visit'][$o][0]['money']) ;
                }
                if($o%7==4){
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$model['all']['visit'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$model['all']['visit'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$model['all']['visit'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$model['all']['visit'][$o][0]['money']) ;
                }
                if($o%7==5){
                    $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$model['all']['visit'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$model['all']['visit'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$model['all']['visit'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$model['all']['visit'][$o][0]['money']) ;
                }
                if($o%7==6){
                    $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$model['all']['visit'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$model['all']['visit'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$model['all']['visit'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$model['all']['visit'][$o][0]['money']) ;
                }
                if($o%7==0){
                    $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$model['all']['visit'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$model['all']['visit'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$model['all']['visit'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$model['all']['visit'][$o][0]['money']) ;
                    $i13=$i13+1;
                }
            }
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
            $i13=$i13+1;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,'拜访目的') ;
            $a=$i13;
            for($o=1;$o<count($model['all']['obj']);$o++){
                if($o%7==1){
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$model['all']['obj'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$model['all']['obj'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$model['all']['obj'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$model['all']['obj'][$o][0]['money']) ;
                }
                if($o%7==2){
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$model['all']['obj'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$model['all']['obj'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$model['all']['obj'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$model['all']['obj'][$o][0]['money']) ;
                }
                if($o%7==3){
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$model['all']['obj'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$model['all']['obj'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$model['all']['obj'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$model['all']['obj'][$o][0]['money']) ;
                }
                if($o%7==4){
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$model['all']['obj'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$model['all']['obj'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$model['all']['obj'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$model['all']['obj'][$o][0]['money']) ;
                }
                if($o%7==5){
                    $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$model['all']['obj'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$model['all']['obj'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$model['all']['obj'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$model['all']['obj'][$o][0]['money']) ;
                }
                if($o%7==6){
                    $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$model['all']['obj'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$model['all']['obj'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$model['all']['obj'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$model['all']['obj'][$o][0]['money']) ;
                }
                if($o%7==0){
                    $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$model['all']['obj'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$model['all']['obj'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$model['all']['obj'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$model['all']['obj'][$o][0]['money']) ;
                    $i13=$i13+1;
                }
            }
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
            $i13=$i13+1;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,'客服类别（餐饮）') ;
            $a=$i13;
            for($o=1;$o<count($model['all']['food']);$o++){
                if($o%7==1){
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$model['all']['food'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$model['all']['food'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$model['all']['food'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$model['all']['food'][$o][0]['money']) ;
                }
                if($o%7==2){
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$model['all']['food'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$model['all']['food'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$model['all']['food'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$model['all']['food'][$o][0]['money']) ;
                }
                if($o%7==3){
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$model['all']['food'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$model['all']['food'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$model['all']['food'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$model['all']['food'][$o][0]['money']) ;
                }
                if($o%7==4){
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$model['all']['food'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$model['all']['food'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$model['all']['food'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$model['all']['food'][$o][0]['money']) ;
                }
                if($o%7==5){
                    $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$model['all']['food'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$model['all']['food'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$model['all']['food'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$model['all']['food'][$o][0]['money']) ;
                }
                if($o%7==6){
                    $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$model['all']['food'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$model['all']['food'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$model['all']['food'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$model['all']['food'][$o][0]['money']) ;
                }
                if($o%7==0){
                    $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$model['all']['food'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$model['all']['food'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$model['all']['food'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$model['all']['food'][$o][0]['money']) ;
                    $i13=$i13+1;
                }
            }
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
            $i13=$i13+1;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,'客服类别（非餐饮）') ;
            $a=$i13;
            for($o=1;$o<count($model['all']['nofood']);$o++){
                if($o%7==1){
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$model['all']['nofood'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$model['all']['nofood'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$model['all']['nofood'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$model['all']['nofood'][$o][0]['money']) ;
                }
                if($o%7==2){
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$model['all']['nofood'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$model['all']['nofood'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$model['all']['nofood'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$model['all']['nofood'][$o][0]['money']) ;
                }
                if($o%7==3){
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$model['all']['nofood'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$model['all']['nofood'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$model['all']['nofood'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$model['all']['nofood'][$o][0]['money']) ;
                }
                if($o%7==4){
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$model['all']['nofood'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$model['all']['nofood'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$model['all']['nofood'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$model['all']['nofood'][$o][0]['money']) ;
                }
                if($o%7==5){
                    $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$model['all']['nofood'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$model['all']['nofood'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$model['all']['nofood'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$model['all']['nofood'][$o][0]['money']) ;
                }
                if($o%7==6){
                    $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$model['all']['nofood'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$model['all']['nofood'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$model['all']['nofood'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$model['all']['nofood'][$o][0]['money']) ;
                }
                if($o%7==0){
                    $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$model['all']['nofood'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$model['all']['nofood'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$model['all']['nofood'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$model['all']['nofood'][$o][0]['money']) ;
                    $i13=$i13+1;
                }
            }
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
            $i13=$i13+1;
//            区域的
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,'区域') ;
            $a=$i13;
            for($o=1;$o<count($model['all']['address']);$o++){
                if($o%7==1){
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$model['all']['address'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$model['all']['address'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$model['all']['address'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$model['all']['address'][$o][0]['money']) ;
                }
                if($o%7==2){
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$model['all']['address'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$model['all']['address'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$model['all']['address'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$model['all']['address'][$o][0]['money']) ;
                }
                if($o%7==3){
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$model['all']['address'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$model['all']['address'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$model['all']['address'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$model['all']['address'][$o][0]['money']) ;
                }
                if($o%7==4){
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$model['all']['address'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$model['all']['address'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$model['all']['address'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$model['all']['address'][$o][0]['money']) ;
                }
                if($o%7==5){
                    $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$model['all']['address'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$model['all']['address'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$model['all']['address'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$model['all']['address'][$o][0]['money']) ;
                }
                if($o%7==6){
                    $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$model['all']['address'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$model['all']['address'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$model['all']['address'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$model['all']['address'][$o][0]['money']) ;
                }
                if($o%7==0){
                    $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$model['all']['address'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$model['all']['address'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$model['all']['address'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$model['all']['address'][$o][0]['money']) ;
                    $i13=$i13+1;
                }
            }
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        //'style' => PHPExcel_Style_Border::BORDER_THICK,//边框是粗的
                        'style' => PHPExcel_Style_Border::BORDER_THIN,//细边框
                        'color' => array('argb' => '0xCC000000'),
                    ),
                ),
            );
            $objPHPExcel->getActiveSheet()->getStyle('A'.$ex.':AC'.$i13)->applyFromArray($styleArray);
        }
        if(!empty($model['one'])){
            if(!empty($model['all'])){
                $i=21;
            }else{
                $i=3;
            }

            foreach ($model['one'] as $arr){
                $ex=$i;
                $i1=$i+1;
                $i2=$i+2;
                $i13=$i+2;
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$arr['name']) ;
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i1,'总拜访量'.$arr['money']['all'].'签单量：'.$arr['money']['sum'].'签单金额'.$arr['money']['money']) ;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$i.':AC'.$i);
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$i1.':AC'.$i1);
                $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(25);
                $objPHPExcel->getActiveSheet()->getRowDimension($i1)->setRowHeight(25);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i2,'拜访类型') ;
                $a=$i13;
                for($o=1;$o<count($arr['visit']);$o++){
                    if($o%7==1){
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$arr['visit'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$arr['visit'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$arr['visit'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$arr['visit'][$o][0]['money']) ;
                    }
                    if($o%7==2){
                        $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$arr['visit'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$arr['visit'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$arr['visit'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$arr['visit'][$o][0]['money']) ;
                    }
                    if($o%7==3){
                        $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$arr['visit'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$arr['visit'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$arr['visit'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$arr['visit'][$o][0]['money']) ;
                    }
                    if($o%7==4){
                        $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$arr['visit'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$arr['visit'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$arr['visit'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$arr['visit'][$o][0]['money']) ;
                    }
                    if($o%7==5){
                        $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$arr['visit'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$arr['visit'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$arr['visit'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$arr['visit'][$o][0]['money']) ;
                    }
                    if($o%7==6){
                        $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$arr['visit'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$arr['visit'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$arr['visit'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$arr['visit'][$o][0]['money']) ;
                    }
                    if($o%7==0){
                        $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$arr['visit'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$arr['visit'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$arr['visit'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$arr['visit'][$o][0]['money']) ;
                        $i13=$i13+1;
                    }
                }
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
                $i13=$i13+1;
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,'拜访目的') ;
                $a=$i13;
                for($o=1;$o<count($arr['obj']);$o++){
                    if($o%7==1){
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$arr['obj'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$arr['obj'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$arr['obj'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$arr['obj'][$o][0]['money']) ;
                    }
                    if($o%7==2){
                        $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$arr['obj'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$arr['obj'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$arr['obj'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$arr['obj'][$o][0]['money']) ;
                    }
                    if($o%7==3){
                        $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$arr['obj'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$arr['obj'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$arr['obj'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$arr['obj'][$o][0]['money']) ;
                    }
                    if($o%7==4){
                        $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$arr['obj'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$arr['obj'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$arr['obj'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$arr['obj'][$o][0]['money']) ;
                    }
                    if($o%7==5){
                        $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$arr['obj'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$arr['obj'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$arr['obj'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$arr['obj'][$o][0]['money']) ;
                    }
                    if($o%7==6){
                        $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$arr['obj'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$arr['obj'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$arr['obj'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$arr['obj'][$o][0]['money']) ;
                    }
                    if($o%7==0){
                        $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$arr['obj'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$arr['obj'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$arr['obj'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$arr['obj'][$o][0]['money']) ;
                        $i13=$i13+1;
                    }
                }
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
                $i13=$i13+1;
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,'客服类别（餐饮）') ;
                $a=$i13;
                for($o=1;$o<count($arr['food']);$o++){
                    if($o%7==1){
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$arr['food'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$arr['food'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$arr['food'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$arr['food'][$o][0]['money']) ;
                    }
                    if($o%7==2){
                        $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$arr['food'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$arr['food'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$arr['food'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$arr['food'][$o][0]['money']) ;
                    }
                    if($o%7==3){
                        $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$arr['food'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$arr['food'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$arr['food'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$arr['food'][$o][0]['money']) ;
                    }
                    if($o%7==4){
                        $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$arr['food'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$arr['food'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$arr['food'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$arr['food'][$o][0]['money']) ;
                    }
                    if($o%7==5){
                        $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$arr['food'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$arr['food'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$arr['food'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$arr['food'][$o][0]['money']) ;
                    }
                    if($o%7==6){
                        $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$arr['food'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$arr['food'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$arr['food'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$arr['food'][$o][0]['money']) ;
                    }
                    if($o%7==0){
                        $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$arr['food'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$arr['food'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$arr['food'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$arr['food'][$o][0]['money']) ;
                        $i13=$i13+1;
                    }
                }
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
                $i13=$i13+1;
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,'客服类别（非餐饮）') ;
                $a=$i13;
                for($o=1;$o<count($arr['nofood']);$o++){
                    if($o%7==1){
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$arr['nofood'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$arr['nofood'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$arr['nofood'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$arr['nofood'][$o][0]['money']) ;
                    }
                    if($o%7==2){
                        $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$arr['nofood'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$arr['nofood'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$arr['nofood'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$arr['nofood'][$o][0]['money']) ;
                    }
                    if($o%7==3){
                        $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$arr['nofood'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$arr['nofood'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$arr['nofood'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$arr['nofood'][$o][0]['money']) ;
                    }
                    if($o%7==4){
                        $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$arr['nofood'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$arr['nofood'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$arr['nofood'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$arr['nofood'][$o][0]['money']) ;
                    }
                    if($o%7==5){
                        $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$arr['nofood'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$arr['nofood'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$arr['nofood'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$arr['nofood'][$o][0]['money']) ;
                    }
                    if($o%7==6){
                        $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$arr['nofood'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$arr['nofood'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$arr['nofood'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$arr['nofood'][$o][0]['money']) ;
                    }
                    if($o%7==0){
                        $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$arr['nofood'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$arr['nofood'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$arr['nofood'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$arr['nofood'][$o][0]['money']) ;
                        $i13=$i13+1;
                    }
                }
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
                $i13=$i13+1;
//            区域的
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,'区域');
                $a=$i13;
                for($o=1;$o<count($arr['address']);$o++){
                    if($o%7==1){
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$arr['address'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$arr['address'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$arr['address'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$arr['address'][$o][0]['money']) ;
                    }
                    if($o%7==2){
                        $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$arr['address'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$arr['address'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$arr['address'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$arr['address'][$o][0]['money']) ;
                    }
                    if($o%7==3){
                        $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$arr['address'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$arr['address'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$arr['address'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$arr['address'][$o][0]['money']) ;
                    }
                    if($o%7==4){
                        $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$arr['address'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$arr['address'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$arr['address'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$arr['address'][$o][0]['money']) ;
                    }
                    if($o%7==5){
                        $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$arr['address'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$arr['address'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$arr['address'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$arr['address'][$o][0]['money']) ;
                    }
                    if($o%7==6){
                        $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$arr['address'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$arr['address'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$arr['address'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$arr['address'][$o][0]['money']) ;
                    }
                    if($o%7==0){
                        $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$arr['address'][$o]['name']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$arr['address'][$o][0]['all']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$arr['address'][$o][0]['sum']) ;
                        $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$arr['address'][$o][0]['money']) ;
                        $i13=$i13+1;
                    }
                }
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            //'style' => PHPExcel_Style_Border::BORDER_THICK,//边框是粗的
                            'style' => PHPExcel_Style_Border::BORDER_THIN,//细边框
                            'color' => array('argb' => '0xCC000000'),
                        ),
                    ),
                );
                $objPHPExcel->getActiveSheet()->getStyle('A'.$ex.':AC'.$i13)->applyFromArray($styleArray);
                $i=$i+18;
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

    public function retrieveData($model){
        Yii::$enableIncludePath = false;
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        //spl_autoload_unregister(array('YiiBase','autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel;
        $objReader  = PHPExcel_IOFactory::createReader('Excel2007');
        $path = Yii::app()->basePath.'/commands/template/sale.xlsx';
        $objPHPExcel = $objReader->load($path);

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
            $a=$i13;
            for($o=1;$o<count($model['all']['visit']);$o++){
                if($o%7==1){
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$model['all']['visit'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$model['all']['visit'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$model['all']['visit'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$model['all']['visit'][$o][0]['money']) ;
                }
                if($o%7==2){
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$model['all']['visit'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$model['all']['visit'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$model['all']['visit'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$model['all']['visit'][$o][0]['money']) ;
                }
                if($o%7==3){
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$model['all']['visit'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$model['all']['visit'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$model['all']['visit'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$model['all']['visit'][$o][0]['money']) ;
                }
                if($o%7==4){
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$model['all']['visit'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$model['all']['visit'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$model['all']['visit'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$model['all']['visit'][$o][0]['money']) ;
                }
                if($o%7==5){
                    $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$model['all']['visit'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$model['all']['visit'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$model['all']['visit'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$model['all']['visit'][$o][0]['money']) ;
                }
                if($o%7==6){
                    $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$model['all']['visit'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$model['all']['visit'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$model['all']['visit'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$model['all']['visit'][$o][0]['money']) ;
                }
                if($o%7==0){
                    $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$model['all']['visit'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$model['all']['visit'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$model['all']['visit'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$model['all']['visit'][$o][0]['money']) ;
                    $i13=$i13+1;
                }
            }
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
            $i13=$i13+1;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,'拜访目的') ;
            $a=$i13;
            for($o=1;$o<count($model['all']['obj']);$o++){
                if($o%7==1){
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$model['all']['obj'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$model['all']['obj'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$model['all']['obj'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$model['all']['obj'][$o][0]['money']) ;
                }
                if($o%7==2){
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$model['all']['obj'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$model['all']['obj'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$model['all']['obj'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$model['all']['obj'][$o][0]['money']) ;
                }
                if($o%7==3){
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$model['all']['obj'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$model['all']['obj'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$model['all']['obj'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$model['all']['obj'][$o][0]['money']) ;
                }
                if($o%7==4){
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$model['all']['obj'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$model['all']['obj'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$model['all']['obj'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$model['all']['obj'][$o][0]['money']) ;
                }
                if($o%7==5){
                    $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$model['all']['obj'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$model['all']['obj'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$model['all']['obj'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$model['all']['obj'][$o][0]['money']) ;
                }
                if($o%7==6){
                    $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$model['all']['obj'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$model['all']['obj'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$model['all']['obj'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$model['all']['obj'][$o][0]['money']) ;
                }
                if($o%7==0){
                    $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$model['all']['obj'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$model['all']['obj'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$model['all']['obj'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$model['all']['obj'][$o][0]['money']) ;
                    $i13=$i13+1;
                }
            }
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
            $i13=$i13+1;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,'客服类别（餐饮）') ;
            $a=$i13;
            for($o=1;$o<count($model['all']['food']);$o++){
                if($o%7==1){
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$model['all']['food'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$model['all']['food'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$model['all']['food'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$model['all']['food'][$o][0]['money']) ;
                }
                if($o%7==2){
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$model['all']['food'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$model['all']['food'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$model['all']['food'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$model['all']['food'][$o][0]['money']) ;
                }
                if($o%7==3){
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$model['all']['food'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$model['all']['food'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$model['all']['food'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$model['all']['food'][$o][0]['money']) ;
                }
                if($o%7==4){
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$model['all']['food'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$model['all']['food'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$model['all']['food'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$model['all']['food'][$o][0]['money']) ;
                }
                if($o%7==5){
                    $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$model['all']['food'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$model['all']['food'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$model['all']['food'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$model['all']['food'][$o][0]['money']) ;
                }
                if($o%7==6){
                    $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$model['all']['food'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$model['all']['food'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$model['all']['food'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$model['all']['food'][$o][0]['money']) ;
                }
                if($o%7==0){
                    $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$model['all']['food'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$model['all']['food'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$model['all']['food'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$model['all']['food'][$o][0]['money']) ;
                    $i13=$i13+1;
                }
            }
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
            $i13=$i13+1;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,'客服类别（非餐饮）') ;
            $a=$i13;
            for($o=1;$o<count($model['all']['nofood']);$o++){
                if($o%7==1){
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$model['all']['nofood'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$model['all']['nofood'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$model['all']['nofood'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$model['all']['nofood'][$o][0]['money']) ;
                }
                if($o%7==2){
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$model['all']['nofood'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$model['all']['nofood'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$model['all']['nofood'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$model['all']['nofood'][$o][0]['money']) ;
                }
                if($o%7==3){
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$model['all']['nofood'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$model['all']['nofood'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$model['all']['nofood'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$model['all']['nofood'][$o][0]['money']) ;
                }
                if($o%7==4){
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$model['all']['nofood'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$model['all']['nofood'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$model['all']['nofood'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$model['all']['nofood'][$o][0]['money']) ;
                }
                if($o%7==5){
                    $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$model['all']['nofood'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$model['all']['nofood'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$model['all']['nofood'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$model['all']['nofood'][$o][0]['money']) ;
                }
                if($o%7==6){
                    $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$model['all']['nofood'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$model['all']['nofood'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$model['all']['nofood'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$model['all']['nofood'][$o][0]['money']) ;
                }
                if($o%7==0){
                    $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$model['all']['nofood'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$model['all']['nofood'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$model['all']['nofood'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$model['all']['nofood'][$o][0]['money']) ;
                    $i13=$i13+1;
                }
            }
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
            $i13=$i13+1;
//            区域的
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i13,'区域') ;
            $a=$i13;
            for($o=1;$o<count($model['all']['address']);$o++){
                if($o%7==1){
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$i13,$model['all']['address'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$i13,$model['all']['address'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$i13,$model['all']['address'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$i13,$model['all']['address'][$o][0]['money']) ;
                }
                if($o%7==2){
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$i13,$model['all']['address'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$i13,$model['all']['address'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$i13,$model['all']['address'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$i13,$model['all']['address'][$o][0]['money']) ;
                }
                if($o%7==3){
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$i13,$model['all']['address'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$i13,$model['all']['address'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$i13,$model['all']['address'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.$i13,$model['all']['address'][$o][0]['money']) ;
                }
                if($o%7==4){
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.$i13,$model['all']['address'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('O'.$i13,$model['all']['address'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('P'.$i13,$model['all']['address'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i13,$model['all']['address'][$o][0]['money']) ;
                }
                if($o%7==5){
                    $objPHPExcel->getActiveSheet()->setCellValue('R'.$i13,$model['all']['address'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$i13,$model['all']['address'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.$i13,$model['all']['address'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.$i13,$model['all']['address'][$o][0]['money']) ;
                }
                if($o%7==6){
                    $objPHPExcel->getActiveSheet()->setCellValue('V'.$i13,$model['all']['address'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('W'.$i13,$model['all']['address'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('X'.$i13,$model['all']['address'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i13,$model['all']['address'][$o][0]['money']) ;
                }
                if($o%7==0){
                    $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i13,$model['all']['address'][$o]['name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i13,$model['all']['address'][$o][0]['all']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i13,$model['all']['address'][$o][0]['sum']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i13,$model['all']['address'][$o][0]['money']) ;
                    $i13=$i13+1;
                }
            }
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$a.':A'.$i13);
            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        //'style' => PHPExcel_Style_Border::BORDER_THICK,//边框是粗的
                        'style' => PHPExcel_Style_Border::BORDER_THIN,//细边框
                        'color' => array('argb' => '0xCC000000'),
                    ),
                ),
            );
            $objPHPExcel->getActiveSheet()->getStyle('A'.$ex.':AC'.$i13)->applyFromArray($styleArray);
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
        foreach ($model['sale'] as $code=>$peoples){
            $sum_arr=array();
            $people=array();
            $sql = "select a.city, a.username, sum(convert(b.field_value, decimal(12,2))) as money 
				from sal_visit a force index (idx_visit_02), sal_visit_info b   
				where a.id=b.visit_id and b.field_id in ('svc_A7','svc_B6','svc_C7','svc_D6','svc_E7') 
				and a.visit_dt >= '$start_dt'and a.visit_dt <= '$end_dt' and  a.visit_obj like '%10%' and a.username ='$peoples' 
				group by a.city, a.username 
			";
            $records = Yii::app()->db->createCommand($sql)->queryAll();
            if(empty($records[0]['money'])){
                $people['money']=0;
            }else{
                $people['money']=$records[0]['money'];
            }
//            print_r('<pre/>');
//            print_r($records);
            $sqls="select a.name as cityname ,d.name as names,d.staff_status from security$suffix.sec_city a	,hr$suffix.hr_binding b	 ,security$suffix.sec_user  c ,hr$suffix.hr_employee d 
                where c.username='$peoples' and b.user_id='".$peoples."' and b.employee_id=d.id and c.city=a.code";
            $cname = Yii::app()->db->createCommand($sqls)->queryRow();
            $sql1="select id,visit_dt  from sal_visit where username='".$peoples."'  and  visit_dt >= '$start_dt'and visit_dt <= '$end_dt' and visit_obj like '%10%'";
            $arr = Yii::app()->db->createCommand($sql1)->queryAll();
            $start_dt1= date("Y-m-01", strtotime($start_dt));
            $end_dt1=date("Y-m-31", strtotime($end_dt));
            $sql_rank="select now_score  from sal_rank where username='".$peoples."'  and  month >= '$start_dt1' and month <= '$end_dt1' order by month desc";//add order by desc
            $rank = Yii::app()->db->createCommand($sql_rank)->queryRow();
            foreach ($arr as $id){//svc_H6(蔚諾服務的金額)
                $sqlid="select count(visit_id) as sum from  sal_visit_info where field_id in ('svc_A7','svc_B6','svc_C7','svc_D6','svc_E7','svc_F4','svc_G3') and field_value>'0' and visit_id='".$id['id']."'";
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
            $people['cityname']=$cname['cityname'];
            $people['names']=$cname['names'].(intval($cname['staff_status'])=="-1"?"（离职）":"");//员工名字
            $people['username']=$peoples;//账号名字
            //其他金额
            $svc_A7=0;
            $svc_B6=0;
            $svc_C7=0;
            $svc_D6=0;
            $svc_E7=0;
            $svc_F4=0;
            $svc_G3=0;
            $svc_A7s=0;
            $svc_B6s=0;
            $svc_C7s=0;
            $svc_D6s=0;
            $svc_E7s=0;
            $svc_F4s=0;
            $svc_G3s=0;
            foreach ($arr as $arrs){
                $sql2="select field_value from sal_visit_info where visit_id='".$arrs['id']."' and field_id='svc_A7' ";
                $money2 = Yii::app()->db->createCommand($sql2)->queryAll();
                if(!empty($money2[0]['field_value'])){
                    $svc_A7=$svc_A7+1;
                    $svc_A7s+=$money2[0]['field_value'];
                }
                $sql3="select field_value from sal_visit_info where visit_id='".$arrs['id']."' and field_id='svc_B6' ";
                $money3 = Yii::app()->db->createCommand($sql3)->queryAll();
                if(!empty($money3[0]['field_value'])){
                    $svc_B6=$svc_B6+1;
                    $svc_B6s+=$money3[0]['field_value'];
                }
                $sql4="select field_value from sal_visit_info where visit_id='".$arrs['id']."' and field_id='svc_C7' ";
                $money4 = Yii::app()->db->createCommand($sql4)->queryAll();
                if(!empty($money4[0]['field_value'])){
                    //echo "<div class='hide' data-type='svc_c7' data-id='{$arrs['id']}' data-date='{$arrs['visit_dt']}' data-monty='{$money4[0]['field_value']}'></div>";
                    $svc_C7=$svc_C7+1;
                    $svc_C7s+=$money4[0]['field_value'];
                }
                $sql5="select field_value from sal_visit_info where visit_id='".$arrs['id']."' and field_id='svc_D6' ";
                $money5 = Yii::app()->db->createCommand($sql5)->queryAll();
                if(!empty($money5[0]['field_value'])){
                    $svc_D6=$svc_D6+1;
                    $svc_D6s+=$money5[0]['field_value'];
                }
                $sql6="select field_value from sal_visit_info where visit_id='".$arrs['id']."' and field_id='svc_E7' ";
                $money6 = Yii::app()->db->createCommand($sql6)->queryAll();
                if(!empty($money6[0]['field_value'])){
                    $svc_E7=$svc_E7+1;
                    $svc_E7s+=$money6[0]['field_value'];
                }
                $sql7="select field_value from sal_visit_info where visit_id='".$arrs['id']."' and field_id='svc_F4' ";
                $money7 = Yii::app()->db->createCommand($sql7)->queryAll();
                if(!empty($money7[0]['field_value'])){
                    $svc_F4=$svc_F4+1;
                    $svc_F4s+=$money7[0]['field_value'];
                }
                $sql8="select field_value from sal_visit_info where visit_id='".$arrs['id']."' and field_id='svc_G3' ";
                $money8 = Yii::app()->db->createCommand($sql8)->queryAll();
                if(!empty($money8[0]['field_value'])){
                    $svc_G3=$svc_G3+1;
                    $svc_G3s+=$money8[0]['field_value'];
                }
            }

            $people['svc_A7']=$svc_A7s;
            $people['svc_B6']=$svc_B6s;
            $people['svc_C7']=$svc_C7s;
            $people['svc_D6']=$svc_D6s;
            $people['svc_E7']=$svc_E7s;
            $people['svc_F4']=$svc_F4s;
            $people['svc_G3']=$svc_G3s;
            $people['svc_A7s']=$svc_A7;
            $people['svc_B6s']=$svc_B6;
            $people['svc_C7s']=$svc_C7;
            $people['svc_D6s']=$svc_D6;
            $people['svc_E7s']=$svc_E7;
            $people['svc_F4s']=$svc_F4;
            $people['svc_G3s']=$svc_G3;
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
