<?php
    $suffix = Yii::app()->params['envSuffix'];
      //城市
    $sql = "select code,name from security$suffix.sec_city ";
    $rows = Yii::app()->db->createCommand($sql)->queryAll();
    $arr=array();
    foreach ($rows as $a){
         //人数
        $sql1="select distinct  username FROM sal_visit  WHERE city='".$a['code']."'";
        $people = Yii::app()->db->createCommand($sql1)->queryAll();
        $peoples=count($people);
        //单数
        $sql2="select id from sal_visit where city='".$a['code']."' and  visit_obj like '%10%'";
        $sum = Yii::app()->db->createCommand($sql2)->queryAll();
        $sums=count($sum);
        //分数
        $sale=$sums/($peoples==0?1:$peoples);
        $sale=round($sale,2)*100;
        $sale=$sale."%";
        $model['city']=$a['name'];
        $model['renjun']=$sale;
        $arr[]=$model;
    }
$last_names = array_column($arr,'renjun');
array_multisort($last_names,SORT_DESC,$arr);


?>
<div class="box">

    <div  >
        <table border="1" style="width: 49%;text-align: center;">
            <tr><td><?php echo Yii::t('report','ranking');?></td><td><?php echo Yii::t('report','city');?></td><td><?php echo Yii::t('report','renjun');?></td>
            <?php for($i=0;$i<count($arr);$i++) {?>
                <tr> <?php if($i<(count($arr)/2)){?>  <td><?php echo $i+1;?></td><td><?php echo $arr[$i]['city'];?></td><td><?php echo $arr[$i]['renjun'];}?></td>

               </tr>
            <?php }?>
        </table>
        <table  border="1" style="width: 49%;float: right;margin-top:-335px ;text-align: center;" >
            <tr><td><?php echo Yii::t('report','ranking');?></td><td><?php echo Yii::t('report','city');?></td><td><?php echo Yii::t('report','renjun');?></td>
                <?php for($i=0;$i<count($arr);$i++) {?>
            <tr>
                <?php if($i>(count($arr)/2-1)){?>  <td><?php echo $i+1;?></td><td><?php echo $arr[$i]['city'];?></td><td><?php echo $arr[$i]['renjun'];}?></td>
            </tr>
            <?php }?>
        </table>
    </div>
    <div>

    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
</div>