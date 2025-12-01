<?php
    $suffix = Yii::app()->params['envSuffix'];
    $models=array();
    $sql="select distinct  a.username ,c.employee_name ,b.name  FROM sal_visit a 
          inner join hr$suffix.hr_binding c on a.username = c.user_id 
          inner join security$suffix.sec_city b on a.city = b.code 
          ";
    //人名
    $people = Yii::app()->db->createCommand($sql)->queryAll();

    foreach ($people as $a){
        $sql1="select id from sal_visit where username='".$a['username']."' and  visit_obj like '%10%'";
        $id = Yii::app()->db->createCommand($sql1)->queryAll();
        //区域
        $sql="select name from security$suffix.sec_city where code=(select region from security$suffix.sec_city where name='".$a['name']."')";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        //单数
        //$sums=count($id);
        $money=0;
        $moneys=0;
        foreach ($id as $b){
            $sql2="select field_id, field_value from sal_visit_info where field_id in ('svc_A7','svc_B6','svc_C7','svc_D6','svc_E7','svc_F4','svc_G3') and visit_id = '".$b['id']."'";
            $array = Yii::app()->db->createCommand($sql2)->queryAll();
            $summoney = 0;
            foreach($array as $item){
                $summoney += $item['field_value'];
            }
            //总金额
            $money+=$summoney;
        }
        $moneys+=$money;
        if(!empty($rows)){
            $model['quyu']=$rows[0]['name'];
        }else{
            $model['quyu']='空';
        }
        $model['name']=$a['employee_name'];
        $model['user']=$a['username'];
        $model['city']=$a['name'];;
        $model['money']=$moneys;
        $models[]=$model;
        $last_names = array_column($models,'money');
        array_multisort($last_names,SORT_DESC,$models);
        $models = array_slice($models, 0, 20);
    }

//print_r('<pre/>');
//print_r($models);

?>


<div class="box box-primary" >
    <div class="box-header with-border">
        <h3 class="box-title">销售个人签单总金额排行榜</h3>


        <!--            <div class="box-tools pull-right">-->
        <!--                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>-->
        <!--            </div>-->
    </div>
    <!-- /.box-header -->

    <div class="box-body">
        <div id='notify' class="direct-chat-messages" style="height: 250px;">
            <table class="table table-bordered small">
                <tr><td><b>排名</b></td><td><b>城市</b></td><td><b>区域</b></td><td><b>姓名</b></td><td><b>签单总金额</b></td>
                    <?php for($i=0;$i<count($models);$i++) {?>
                <tr>
                   <td style="color:<?php if($i==0){echo '#FF0000';}if($i==1){echo '#871F78';}if($i==2){echo '#0000FF';}?>"  ><?php echo $i+1;?></td><td style="color:<?php if($i==0){echo '#FF0000';}if($i==1){echo '#871F78';}if($i==2){echo '#0000FF';}?>"><?php echo $models[$i]['city'];?></td><td style="color:<?php if($i==0){echo '#FF0000';}if($i==1){echo '#871F78';}if($i==2){echo '#0000FF';}?>"><?php echo $models[$i]['quyu'];?></td><td style="color:<?php if($i==0){echo '#FF0000';}if($i==1){echo '#871F78';}if($i==2){echo '#0000FF';}?>"><?php echo $models[$i]['name'];?></td><td  style="color:<?php if($i==0){echo '#FF0000';}if($i==1){echo '#871F78';}if($i==2){echo '#0000FF';}?>"><?php echo $models[$i]['money'];?></td>
                </tr>
            <?php }?>
            </table>
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <small>每出现签单时即时刷新数据</small>
    </div>
    <!-- /.box-footer -->
</div>
<!-- /.box -->

