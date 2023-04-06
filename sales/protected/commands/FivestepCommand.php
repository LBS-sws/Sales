<?php
class FivestepCommand extends CConsoleCommand
{
    public function run()
    {
        $suffix = Yii::app()->params['envSuffix'];
        $sql="select a.id ,a.filename from sales$suffix.sal_fivestep a 
	          left outer join security$suffix.sec_user b on a.username=b.username
	          where b.status='I' and b.lud>='".date('Y-m-d',strtotime('-1 day'))."'";
        $arr=Yii::app()->db->createCommand($sql)->queryAll();
        if(!empty($arr)){
            foreach ($arr as $a){
                if(file_exists($a['filename'])){
                    unlink($a['filename']);
                }
/*
				$sql1="delete from sales$suffix.sal_fivestep where id = '".$a['id']."'";
                $command=Yii::app()->db->createCommand($sql1)->execute();
                $sql2="delete from sales$suffix.sal_fivestep_info where five_id = '".$a['id']."'";
                $commands=Yii::app()->db->createCommand($sql2)->execute();
*/
            }
        }
    }
}