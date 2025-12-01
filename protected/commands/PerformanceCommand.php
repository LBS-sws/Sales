<?php
class PerformanceCommand extends CConsoleCommand
{
    public function run($args)
    {
	$date = empty($args) ? date('Y-m-d') : $args[0];
        $month=date('n', strtotime($date));
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
                    $row = Yii::app()->db->createCommand()->select("id")->from("sales$suffix.sal_performance")
                        ->where("city=:city and year=:year and month=:month",array(
                            ":city"=>$city,
                            ":year"=>$year,
                            ":month"=>$month,
                        ))->queryRow();
                    if($row){
                        //已存在，不需要增加
                    }else{
                        //不存在，需要增加
                        $saveList=array(
                            "city"=>$city,
                            "year"=>$year,
                            "month"=>$month,
                            "sum"=>0,
                            "sums"=>0,
                            "spanning"=>0,
                            "otherspanning"=>0,
                        );
                        $lastRow = Yii::app()->db->createCommand()->select("*")->from("sales$suffix.sal_performance")
                            ->where("city=:city and year=:year and month=:month",array(
                                ":city"=>$city,
                                ":year"=>$lastyear,
                                ":month"=>$lastmonth,
                            ))->queryRow();
                        if($lastRow){
                            $saveList = $lastRow;
                            $saveList["year"]=$year;
                            $saveList["month"]=$month;
                            unset($saveList["id"]);
                        }
                        $saveList["lcu"]=$uid;
                        $saveList["luu"]=$uid;
                        Yii::app()->db->createCommand()->insert("sales$suffix.sal_performance",$saveList);
                    }
                }
            }
//        }

    }
}
