<?php
class SaleCommand extends CConsoleCommand
{
    public function run()
    {
        //转移之后的员工如果离职，需要还原
        $suffix = Yii::app()->params['envSuffix'];
        $sql="UPDATE sales$suffix.sal_visit a
              LEFT JOIN hr$suffix.hr_binding c on a.shift_user = c.user_id 
              LEFT JOIN hr$suffix.hr_employee f on c.employee_id = f.id
              set a.shift='Y',a.shift_user=NULL       
              WHERE a.shift_user IS NOT NULL and a.shift='Z' and f.staff_status=-1";
        $command=Yii::app()->db->createCommand($sql)->execute();
    }
}