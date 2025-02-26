<?php
class RanklSaveCommand extends CConsoleCommand
{
    //销售段位分数保存
    public function run($args)
    {
        $date = empty($args) ? date("Y-m-d") : $args[0];
        $suffix = Yii::app()->params['envSuffix'];
        $start = date("Y-m-01", strtotime($date));
        $end = date("Y-m-31",strtotime($date));
        $sql="select a.code
            from security$suffix.sec_city a left outer join security$suffix.sec_city b on a.code=b.region 
            where b.code is null 
            order by a.code";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $city = $row['code'];
                $model = new RankForm('view');
                $sql="select * from sal_rank  where month>='$start' and month<='$end' and city='$city'";
                $row = Yii::app()->db->createCommand($sql)->queryAll();
                foreach ($row as $id){
                    $model->retrieveData($id['id']);
                }
            }
        }
    }
}