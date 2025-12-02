<?php
class IntegralSaveCommand extends CConsoleCommand
{
    public function run($args)
    {
        echo "IntegralSave Start:\n";
        $date = empty($args) ? date("Y-m-d") : $args[0];
        $month = date("m", strtotime($date));
        $year = date("Y",strtotime($date));
        echo "year({$year}),month({$month})\n";
        $sql="select id from sal_integral where year='$year' and month='$month'";
        $row = Yii::app()->db->createCommand($sql)->queryAll();
        foreach ($row as $id){
            $model = new IntegralForm('view');
            $model->retrieveDataNew($id['id']);
            unset($model);
        }
		/*
        $month1 = date("m", strtotime("$date -1 month"));
        $year1 = date("Y",strtotime(" $date -1 month"));
        echo "year({$year1}),month({$month1})\n";
        $sql1="select id from sal_integral where year='$year1' and month='$month1'";
        $row1 = Yii::app()->db->createCommand($sql1)->queryAll();
        foreach ($row1 as $id){
            $model = new IntegralForm('view');
            $model->retrieveDataNew($id['id']);
            unset($model);
        }
        $month2 = date("m", strtotime("$date -2 month"));
        $year2 = date("Y",strtotime(" $date -2 month"));
        echo "year({$year2}),month({$month2})\n";
        $sql2="select id from sal_integral where year='$year2' and month='$month2'";
        $row2 = Yii::app()->db->createCommand($sql2)->queryAll();
        foreach ($row2 as $id){
            $model = new IntegralForm('view');
            $model->retrieveDataNew($id['id']);
            unset($model);
        }
		*/
        echo "IntegralSave End:\n";
    }
}