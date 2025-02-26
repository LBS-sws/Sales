<?php
class IntegralCommand extends CConsoleCommand
{
    public function run($args)
    {
        $date = empty($args) ? date("Y-m-d") : $args[0];
        $month = date("m", strtotime($date));
        $year = date("Y", strtotime($date));
        $suffix = Yii::app()->params['envSuffix'];
        $sql="select a.code
				from security$suffix.sec_city a left outer join security$suffix.sec_city b on a.code=b.region 
				where b.code is null 
				order by a.code";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $city = $row['code'];
                $sql1="select b.user_id from hr$suffix.hr_employee a
                      inner join hr$suffix.hr_binding b on a.id=b.employee_id 
                      inner join hr$suffix.hr_dept c on a.position=c.id 
                      where  a.city='$city'  and (c.manager_type ='1' or c.manager_type ='2') and a.staff_status='0'
";
                $row = Yii::app()->db->createCommand($sql1)->queryAll();
                $sql2="select username from sales$suffix.sal_integral where year='$year' and month='$month' and  city='$city'";
                $arr = Yii::app()->db->createCommand($sql2)->queryAll();
                $color = array_udiff($row,$arr,create_function(
                        '$a,$b','return strcmp(implode("",$a),implode("",$b));')
                );
                if(!empty($color)){
                    foreach ($color as $sale){
                        $a=$this->getEmployee($sale['user_id'],$year,$month);
                        if($a==1){
                            $sql = "insert into sales$suffix.sal_integral(city, year, month, username) 
				values('$city', '$year', '$month', '".$sale['user_id']."')
			";
                            $command=Yii::app()->db->createCommand($sql)->execute();
                        }
                    }
                }

            }
        }
    }

    public  function getEmployee($user_id,$year,$month){
        $suffix = Yii::app()->params['envSuffix'];
        $sql="select d.code from hr$suffix.hr_employee d                     
              left outer join hr$suffix.hr_binding e on  d.id=e.employee_id
              where e.user_id='$user_id'
";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
            $sql1="select visit_dt from sales$suffix.sal_visit   where username='$user_id' order by visit_dt
";
            $record = Yii::app()->db->createCommand($sql1)->queryRow();
            $timestrap=strtotime($record['visit_dt']);
            $years=date('Y',$timestrap);
            $months=date('m',$timestrap);
//        print_r($record);exit();
            if($years==$year&&$months==$month){
                if(date('d',$timestrap)=='01'){
                $a=1;//加入积分
                 }else{
                $a=2;
                 }
              }else{
                $a=1;//加入积分
            }

//        }else{
//            $next=$months+1;
//            if($next==13){
//                $next=1;
//                $years=$years+1;
//            }
//            if(($years==$year&&$months==$month)||($years==$year&&$next==$month)){
//                $a=1;//不加入东成西就
//            }else{
//                $a=2;
//            }
//        }
        return $a;
    }
}