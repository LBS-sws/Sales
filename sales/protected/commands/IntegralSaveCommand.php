<?php
class IntegralSaveCommand extends CConsoleCommand
{
    public function run($args)
    {
        $date = empty($args) ? date("Y-m-d") : $args[0];
        $month = date("m", strtotime($date));
        $year = date("Y",strtotime($date));
        $model = new IntegralForm('view');
        $sql="select * from sal_integral where year<='$year' and month>='$month'";
        $row = Yii::app()->db->createCommand($sql)->queryAll();
        foreach ($row as $id){
            $model->retrieveDataNew($id['id']);
            $sql1="update sal_integral set point='".$model['cust_type_name']['point']."',all_sum='".$model['cust_type_name']['all_sum']."' where id='".$id['id']."'";
            $command=Yii::app()->db->createCommand($sql1)->execute();
        }
        $month1 = date("m", strtotime("$date -1 month"));
        $year1 = date("Y",strtotime(" $date -1 month"));
        $sql1="select * from sal_integral where year<='$year1' and month>='$month1'";
        $row1 = Yii::app()->db->createCommand($sql1)->queryAll();
        foreach ($row1 as $id){
            $model->retrieveDataNew($id['id']);
            $sql1="update sal_integral set point='".$model['cust_type_name']['point']."',all_sum='".$model['cust_type_name']['all_sum']."' where id='".$id['id']."'";
            $command=Yii::app()->db->createCommand($sql1)->execute();
        }
        $month2 = date("m", strtotime("$date -2 month"));
        $year2 = date("Y",strtotime(" $date -2 month"));
        $sql2="select * from sal_integral where year<='$year2' and month>='$month2'";
        $row2 = Yii::app()->db->createCommand($sql2)->queryAll();
        foreach ($row2 as $id){
            $model->retrieveDataNew($id['id']);
            $sql1="update sal_integral set point='".$model['cust_type_name']['point']."',all_sum='".$model['cust_type_name']['all_sum']."' where id='".$id['id']."'";
            $command=Yii::app()->db->createCommand($sql1)->execute();
        }
    }
}