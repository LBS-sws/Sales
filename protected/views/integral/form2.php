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
                    'submit'=>Yii::app()->createUrl('integral/downsNew',array('index'=>$model->id))));
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
            <p>
                <b><?php echo Yii::t('dialog','Date'); echo $model['year']."/".$model['month'];?></b>
                <b> &nbsp; &nbsp; &nbsp;拜访总数量</b>
                <b><?php echo $model['sum'];?><b/>
                <b> &nbsp; &nbsp; &nbsp;工作天数</b>
                <b><?php echo $model['sale_day'];?><b/>
            </p>
            <p><?php echo $model['name'];?></p>

            <table class="tftable" border="1">
                <?php
                    echo $model->getTableHtml();
                ?>
                <tr>
                    <td colspan='5'> </td>
                    <td style="background-color: <?php if($model['cust_type_name']['sale_day']==1){echo '#ff2222';}else{echo '#154561';}?>"> 总计</td>
                    <td style="background-color:<?php if($model['cust_type_name']['sale_day']==1){echo '#ff2222';}else{echo '#154561';}?> "> <?php echo $model['cust_type_name']['all_sum'];?></td>
                    <td> </td>
                </tr>
                <tr>
                    <td colspan='5'> </td>
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


