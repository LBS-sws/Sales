<?php
class PerformanceCommand extends CConsoleCommand
{
    public function run($args)
    {
	$date = empty($args) ? date('Y-m-d') : $args[0];
        $month=date('m', strtotime($date));
        $year=date('Y', strtotime($date));
        $day=date('d', strtotime($date));
//        if($day=='01'){
            $suffix = Yii::app()->params['envSuffix'];
            $sql="select a.code
				from security$suffix.sec_city a left outer join security$suffix.sec_city b on a.code=b.region 
				where b.code is null 
				order by a.code";
            $rows = Yii::app()->db->createCommand($sql)->queryAll();
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    $city = $row['code'];
                    $uid = 'admin';
                    $lastmonth=$month-1;
                    $lastyear=$year;
                    if($lastmonth==0){
                        $lastmonth=12;
                        $lastyear=$year-1;
                    }
                    $sqls="select * from sales$suffix.sal_performance where city='$city' and year='$lastyear' and month='$lastmonth' ";
                    $row = Yii::app()->db->createCommand($sqls)->queryRow();
                    if(empty($row)){
                        $sql = "insert into sales$suffix.sal_performance(city, year, month, sum,sums,spanning,otherspanning,lcu, luu) 
				values('$city', '$year', '$month', '0', '0','0','0','$uid', '$uid')
			";
                    }else{
                        $sql = "insert into sales$suffix.sal_performance(city, year, month, sum,sums,spanning,otherspanning,lcu, luu) 
				values('$city', '$year', '$month', '".$row['sum']."', '".$row['sums']."','".$row['spanning']."','".$row['otherspanning']."','$uid', '$uid')
			";
                    }
                    $command=Yii::app()->db->createCommand($sql)->execute();
                }
            }
//        }

    }
}
