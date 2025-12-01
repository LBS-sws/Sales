<?php
$this->pageTitle=Yii::app()->name . ' - Sales Visit Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'visit-form',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('sales','Sales Fenxi Form'); ?></strong>
    </h1>
</section>

<section class="content">
    <div class="box"><div class="box-body">
            <div class="btn-group" role="group">
                <?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('report/visit')));
                ?>
            </div>

            <div class="btn-group pull-right" role="group">
                <?php echo TbHtml::button('<span class="fa fa-download"></span> '.Yii::t('misc','Xiazai'), array(
                    'submit'=>Yii::app()->createUrl('report/xiazai')));
                ?>
            </div>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-body" style=" overflow-x:auto; overflow-y:auto;">
            <?php
            echo $form->hiddenField($model, 'scenario');
            echo $form->hiddenField($model, 'id');
            echo $form->hiddenField($model, 'city');
            ?>
            <input type="text" name="RptFive[city]" value="<?php echo $fenxi['city']?>" style="display:none"/>
            <input type="text" name="RptFive[start_dt]" value="<?php echo $fenxi['start_dt']?>" style="display:none"/>
            <input type="text" name="RptFive[end_dt]" value="<?php echo $fenxi['end_dt']?>" style="display:none"/>
            <input type="text" name="RptFive[bumen]" value="<?php echo $fenxi['bumen']?>" style="display:none"/>

            <?php if(!empty($fenxi['sale'])){foreach ($fenxi['sale'] as $v) {?>
                <input name="RptFive[sale][]" type="checkbox" value="<?php echo $v?>" style="display:none" checked />
            <?php } }?>



            <style type="text/css">
                .tftable {font-size:12px;color:#333333;border-width: 1px;border-color: #729ea5;border-collapse: collapse;}
                .tftable th {font-size:12px;background-color:#acc8cc;border-width: 1px;padding: 8px;border-style: solid;border-color: #729ea5;text-align: center;width: 50px;}
                .tftable tr {background-color:#d4e3e5;}
                .tftable td {font-size:12px;border-width: 1px;padding: 8px;border-style: solid;border-color: #729ea5;width: 75px;}
                .tftable tr:hover {background-color:#ffffff;}
            </style>
            <style type="text/css">
                .tftable1 {font-size:12px;color:#333333;border-width: 1px;border-color: #9dcc7a;border-collapse: collapse;}
                .tftable1 th {font-size:12px;background-color:#abd28e;border-width: 1px;padding: 8px;border-style: solid;border-color: #9dcc7a;text-align:center;width: 50px;}
                .tftable1 tr {background-color:#bedda7;}
                .tftable1 td {font-size:12px;border-width: 1px;padding: 8px;border-style: solid;border-color: #9dcc7a;width: 75px;}
                .tftable1 tr:hover {background-color:#ffffff;}
            </style>
            <style type="text/css">
                .tftable2 {font-size:12px;color:#333333;border-width: 1px;border-color: #a9a9a9;border-collapse: collapse;}
                .tftable2 th {font-size:12px;background-color:#b8b8b8;border-width: 1px;padding: 8px;border-style: solid;border-color: #a9a9a9;text-align:center;width: 50px;}
                .tftable2 tr {background-color:#cdcdcd;}
                .tftable2 td {font-size:12px;border-width: 1px;padding: 8px;border-style: solid;border-color: #a9a9a9;width: 75px;}
                .tftable2 tr:hover {background-color:#ffffff;}
            </style>
            <style type="text/css">
                .tftable3 {font-size:12px;color:#333333;border-width: 1px;border-color: #ebab3a;border-collapse: collapse;}
                .tftable3 th {font-size:12px;background-color:#e6983b;border-width: 1px;padding: 8px;border-style: solid;border-color: #ebab3a;text-align:center;width: 50px;}
                .tftable3 tr {background-color:#f0c169;}
                .tftable3 td {font-size:12px;border-width: 1px;padding: 8px;border-style: solid;border-color: #ebab3a;width: 75px;}
                .tftable3 tr:hover {background-color:#ffffff;}
            </style>
            <?php if(!empty($model['all'])){?>
            <div>   <h4><?php echo Yii::t('report','zhu');?></h4>
                <h3><?php echo Yii::t('report','Department total data');?></h3>

                <h4><b><?php echo Yii::t('report','Total visits');?>:<?php echo $model['all']['money']['all'];?> <?php echo Yii::t('report','Signing quantity');?>：<?php echo $model['all']['money']['sum'];?>  <?php echo Yii::t('report','Signing amount');?>:<?php echo $model['all']['money']['money'];?> </b></h4>

                <table class="tftable" border="1">
                    <tr><th rowspan="5" width="100"><?php echo Yii::t('report','visit');?></th><?php for($i=0;$i<count($model['all']['visit']);$i++){?><th ><?php echo $model['all']['visit'][$i]['name'];?></th><td ><?php echo $model['all']['visit'][$i]['0'];?></td><?php if($i==6||$i==13||$i==20||$i==27||$i==34||$i==41){ echo "</tr>";}?><?php }?>
                    <tr></tr>
                </table>

                <table class="tftable1" border="1">
                    <tr><th rowspan="2" width="100"><?php echo Yii::t('report','obj');?></th><?php for($i=0;$i<count($model['all']['obj']);$i++){?><th ><?php echo $model['all']['obj'][$i]['name'];?></th><td ><?php echo $model['all']['obj'][$i]['0'];?></td><?php if($i==6||$i==13||$i==20||$i==27||$i==34||$i==41){ echo "</tr>";}?><?php }?>
                       <tr></tr>
                </table>

                <table class="tftable2" border="1">
                    <tr><th rowspan="<?php echo ceil(count($model['all']['address'])/7);?>" width="100"><?php echo Yii::t('report','quyu');?></th><?php for($i=0;$i<count($model['all']['address']);$i++){?><th ><?php echo $model['all']['address'][$i]['name'];?></th><td ><?php echo $model['all']['address'][$i]['0'];?></td><?php if($i==6||$i==13||$i==20||$i==27||$i==34||$i==41){ echo "</tr>";}?><?php }?>
                    <tr></tr>
                </table>

                <?php if (!empty($model['all'])): ?>
                <table class="tftable3" border="1">
                    <tr><th rowspan="<?php echo ceil(count($model['all']['food'])/7);?>" width="100"><?php echo Yii::t('report','food');?></th><?php for($i=0;$i<count($model['all']['food']);$i++){?><th ><?php echo $model['all']['food'][$i]['name'];?></th><td ><?php echo $model['all']['food'][$i]['0'];?></td><?php if($i==6||$i==13||$i==20||$i==27||$i==34||$i==41){ echo "</tr>";}?><?php }?>
                    <tr></tr>
                    <tr><th rowspan="<?php echo ceil(count($model['all']['nofood'])/7);?>" width="100"><?php echo Yii::t('report','nofood');?></th><?php for($i=0;$i<count($model['all']['nofood']);$i++){?><th ><?php echo $model['all']['nofood'][$i]['name'];?></th><td ><?php echo $model['all']['nofood'][$i]['0'];?></td><?php if($i==6||$i==13||$i==20||$i==27||$i==34||$i==41){ echo "</tr>";}?><?php }?>
                    <tr></tr>
                </table>
                <?php endif ?>
            </div>
            <?php }?>
            <?php if(!empty($model['one'])){ foreach ($model['one'] as $arr){?>
            <div>
                <h3><?php echo $arr['name'];?></h3>
                <h4><b><?php echo Yii::t('report','Total visits');?>:<?php echo $arr['money']['all'];?> <?php echo Yii::t('report','Signing quantity');?>：<?php echo $arr['money']['sum'];?>  <?php echo Yii::t('report','Signing amount');?>:<?php echo $arr['money']['money'];?> </b></h4>

                <table class="tftable" border="1">
                    <tr><th rowspan="5" width="100"><?php echo Yii::t('report','visit');?></th><?php for($i=0;$i<count($arr['visit']);$i++){?><th ><?php echo $arr['visit'][$i]['name'];?></th><td ><?php echo $arr['visit'][$i]['0'];?></td><?php if($i==6||$i==13||$i==20||$i==27||$i==34||$i==41){ echo "</tr>";}?><?php }?>
                    <tr></tr>
                </table>

                <table class="tftable1" border="1">
                    <tr><th rowspan="2" width="100"><?php echo Yii::t('report','obj');?></th><?php for($i=0;$i<count($arr['obj']);$i++){?><th ><?php echo $arr['obj'][$i]['name'];?></th><td ><?php echo $arr['obj'][$i]['0'];?></td><?php if($i==6||$i==13||$i==20||$i==27||$i==34||$i==41){ echo "</tr>";}?><?php }?>
                    <tr></tr>
                </table>

                <table class="tftable2" border="1">
                    <tr><th rowspan="<?php echo ceil(count($arr['address'])/7);?>" width="100"><?php echo Yii::t('report','quyu');?></th><?php for($i=0;$i<count($arr['address']);$i++){?><th ><?php echo $arr['address'][$i]['name'];?></th><td ><?php echo $arr['address'][$i]['0'];?></td><?php if($i==6||$i==13||$i==20||$i==27||$i==34||$i==41){ echo "</tr>";}?><?php }?>
                    <tr></tr>
                </table>

                <?php if (!empty($model['all'])): ?>
                <table class="tftable3" border="1">
                    <tr><th rowspan="<?php echo ceil(count($arr['food'])/7);?>" width="100"><?php echo Yii::t('report','food');?></th><?php for($i=0;$i<count($arr['food']);$i++){?><th ><?php echo $arr['food'][$i]['name'];?></th><td ><?php echo $arr['food'][$i]['0'];?></td><?php if($i==6||$i==13||$i==20||$i==27||$i==34||$i==41){ echo "</tr>";}?><?php }?>
                    <tr></tr>
                    <tr><th rowspan="<?php echo ceil(count($arr['nofood'])/7);?>" width="100"><?php echo Yii::t('report','nofood');?></th><?php for($i=0;$i<count($arr['nofood']);$i++){?><th ><?php echo $arr['nofood'][$i]['name'];?></th><td ><?php echo $arr['nofood'][$i]['0'];?></td><?php if($i==6||$i==13||$i==20||$i==27||$i==34||$i==41){ echo "</tr>";}?><?php }?>
                    <tr></tr>
                </table>
                <?php endif ?>
            </div>
            <?php } } ?>
        </div>
    </div>



</section>



<?php
$js = Script::genDeleteData(Yii::app()->createUrl('visit/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


