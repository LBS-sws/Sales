<?php
$this->pageTitle=Yii::app()->name . ' - Integral Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'Integral-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('code','Integral Form'); ?></strong>
	</h1>
</section>

<section class="content">

	<div class="box">
        <div class="box-body">
            <div class="btn-group" role="group">
                <?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                        'submit'=>Yii::app()->createUrl('integral/index')));
                ?>
            </div>
            <div class="btn-group pull-right" role="group">
                <?php echo TbHtml::button('<span class="fa fa-download"></span> '.Yii::t('misc','Xiazai'), array(
                    'submit'=>Yii::app()->createUrl('integral/downs',array('index'=>$model->id))));
                ?>
            </div>
	</div>
    </div>
    <div class="box">
        <div class="box-body">
            <div class="btn-group text-info" role="group">
                <p><b><?php echo Yii::t('dialog','Zhu'); ?></b></p>
                <p style="text-indent: 15px;"><?php echo Yii::t('dialog','Zhu_Integral'); ?></p>

            </div>
        </div>
    </div>
	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
            <style type="text/css">
                .tftable {font-size:12px;color:#fbfbfb;width:100%;border-width: 1px;border-color: #686767;border-collapse: collapse;}
                .tftable th {font-size:12px;background-color:#171515;border-width: 1px;padding: 8px;border-style: solid;border-color: #686767;text-align:left;}
                .tftable tr {background-color:white;}
                .tftable td {font-size:12px;color:#171515;border-width: 1px;padding: 8px;border-style: solid;border-color: #686767;}
            </style>
            <p><b><?php echo Yii::t('dialog','Date'); echo $model['year']."/".$model['month'];?></b>  <b> &nbsp; &nbsp; &nbsp;拜访总数量</b><b><?php echo $model['sum'];?><b/></p><p><?php echo $model['name'];?></p>

            <table class="tftable" border="1">
                <tr>
                    <th>产品</th>
                    <th>类别</th>
                    <th>单位</th>
                    <th>条件</th>
                    <th>分数</th>
                    <th style="width: 70px;">当月数量</th>
                    <th>当月分数</th>
                    <th>备注</th>
                </tr>
                <?php foreach ($model['cust_type_name']['canpin'] as $arr) {?>
                <tr>
                    <td><?php echo $arr['cust_type_name'];?></td>
                    <td> <?php echo IntegralForm::getCustTypeName($arr['cust_type_id']); ?></td>
                    <td>  </td>
                    <td> <?php echo IntegralForm::getConditionsName($arr['conditions']);?></td>
                    <td> <?php echo $arr['fraction'];?></td><td> <?php echo $arr['number'];?></td>
                    <td> <?php echo $arr['sum'];?></td>
                    <td> <?php if($arr['toplimit']!=0){echo "上限为".$arr['toplimit'] ;}?></td>
                </tr>
                <?php }?>
                <tr>
                    <td> </td><td> </td><td> </td><td> </td><td> </td>
                    <td style="background-color: #9acfea"> 小计</td>
                    <td style="background-color: #9acfea"> <?php echo $model['cust_type_name']['canpin_sum'];?></td>
                    <td> </td>
                </tr>
                <tr>
                    <th>服务</th>
                    <th>类别</th>
                    <th>单位</th>
                    <th>条件</th>
                    <th>分数</th>
                    <th>当月数量</th>
                    <th>当月分数</th>
                    <th>备注</th>
                </tr>
                <?php foreach ($model['cust_type_name']['fuwu'] as $arr) {?>
                    <tr>
                        <td><?php echo $arr['cust_type_name'];?></td>
                        <td> <?php echo IntegralForm::getCustTypeName($arr['cust_type_id']); ?></td>
                        <td>  </td>
                        <td> <?php echo IntegralForm::getConditionsName($arr['conditions']);?></td>
                        <td> <?php echo $arr['fraction'];?></td><td> <?php echo $arr['number'];?></td>
                        <td> <?php echo $arr['sum'];?></td>
                        <td> <?php if($arr['toplimit']!=0){echo "上限为".$arr['toplimit'] ;}?></td>
                    </tr>
                <?php }?>
                <tr>
                    <td> </td><td> </td><td> </td><td> </td><td> </td>
                    <td style="background-color: #9acfea"> 小计</td>
                    <td style="background-color: #9acfea"> <?php echo $model['cust_type_name']['fuwu_sum'];?></td>
                    <td> </td>
                </tr>
                <tr>
                    <th>其他</th>
                    <th>类别</th>
                    <th>单位</th>
                    <th>条件</th>
                    <th>分数</th>
                    <th>当月数量</th>
                    <th>当月分数</th>
                    <th>备注</th>
                </tr>
                <tr>
                    <td> 安装维护费</td>
                    <td> 其他</td>
                    <td> </td>
                    <td> 每个新客户</td>
                    <td> <?php echo $model['cust_type_name']['zhuangji']['fraction'];?></td>
                    <td> <?php echo $model['cust_type_name']['zhuangji']['number'];?></td>
                    <td> <?php echo $model['cust_type_name']['zhuangji']['sum'];?></td>
                    <td> </td>
                </tr>
                <?php foreach ($model['cust_type_name']['yushou'] as $arr) {?>
                    <tr>
                        <td><?php echo $arr['cust_type_name'];?></td>
                        <td> 其他</td>
                        <td>  </td>
                        <td> <?php echo IntegralForm::getConditionsName($arr['conditions']);?></td>
                        <td> <?php echo $arr['fraction'];?></td>
                        <td> <?php echo $arr['number'];?></td>
                        <td> <?php echo $arr['sum'];?></td>
                        <td> <?php if($arr['toplimit']!=0){echo $arr['toplimit']."个月" ;}?></td>
                    </tr>
                <?php }?>


                <?php foreach ($model['cust_type_name']['baifang'] as $arr) {?>
                    <tr>
                        <td><?php echo $arr['cust_type_name'];?></td>
                        <td> 其他</td>
                        <td>  </td>
                        <td> <?php echo IntegralForm::getConditionsName($arr['conditions']);?></td>
                        <td> <?php echo $arr['fraction'];?></td><td> <?php echo $arr['number'];?></td>
                        <td> <?php echo $arr['sum'];?></td>
                        <td> </td>
                    </tr>
                <?php }?>
                <tr>
                    <td> </td><td> </td><td> </td><td> </td><td> </td>
                    <td style="background-color: #9acfea"> 小计</td><td style="background-color: #9acfea"> <?php echo $model['cust_type_name']['qita_sum'];?></td>
                    <td> </td>
                </tr>
                <tr>
                    <td> </td><td> </td><td> </td><td> </td><td> </td>
                    <td style="background-color: <?php if($model['cust_type_name']['sale_day']==1){echo '#ff2222';}else{echo '#154561';}?>"> 总计</td>
                    <td style="background-color:<?php if($model['cust_type_name']['sale_day']==1){echo '#ff2222';}else{echo '#154561';}?> "> <?php echo $model['cust_type_name']['all_sum'];?></td>
                    <td> </td>
                </tr>
                <tr>
                    <td> </td><td> </td><td> </td><td> </td><td> </td>
                    <td style="background-color: #ff2222"> 最终点数</td>
                    <td style="background-color: #ff2222"> <?php echo $model['cust_type_name']['point']*100;echo "%";?></td>
                    <td> </td>
                </tr>
            </table>
        </div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = Script::genDeleteData(Yii::app()->createUrl('custtype/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


