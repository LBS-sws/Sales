<?php
class SeasonCommand extends CConsoleCommand
{
    public function run()
    {
        $suffix = Yii::app()->params['envSuffix'];
        $sql_season="select season from sales$suffix.sal_season order by id desc";
        $season = Yii::app()->db->createCommand($sql_season)->queryScalar();
        if(empty($season)){
            $sql="insert into sales$suffix.sal_season (season) value ('1')                   
             ";
            $command=Yii::app()->db->createCommand($sql)->execute();
        }else{
            $season=$season+1;
            $sql="insert into sales$suffix.sal_season (season) value ('$season')       
             ";
            $command=Yii::app()->db->createCommand($sql)->execute();
        }
    }
}