<?php
class FivestepCommand extends CConsoleCommand
{
    public function run()
    {
        $suffix = Yii::app()->params['envSuffix'];
        $sql="select a.id ,a.filename from sales$suffix.sal_fivestep a 
	          left outer join hr$suffix.hr_binding b on a.username=b.user_id
	          left outer join hr$suffix.hr_employee c on b.employee_id=c.id
	          where c.staff_status=-1 AND a.filename!='已删除'";//删除离职员工的销售五部曲附件
        $arr=Yii::app()->db->createCommand($sql)->queryAll();
        if(!empty($arr)){
            foreach ($arr as $a){
                if(file_exists($a['filename'])){
                    unlink($a['filename']);
                }
                Yii::app()->db->createCommand()->update("sales$suffix.sal_fivestep",array(
                    "filename"=>"已删除"
                ),"id={$a["id"]}");
                /* 由於銷售五部曲在人事系統有關聯，所以不能刪除（2023-04-06）
                $sql1="delete from sales$suffix.sal_fivestep where id = '".$a['id']."'";
                $command=Yii::app()->db->createCommand($sql1)->execute();
                $sql2="delete from sales$suffix.sal_fivestep_info where five_id = '".$a['id']."'";
                $commands=Yii::app()->db->createCommand($sql2)->execute();
                */
            }
        }
    }
}