<?php
$this->pageTitle=Yii::app()->name . ' - Performance Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'Performance-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('sales','Rank sales history Form'); ?></strong>
	</h1>
</section>

<section class="content">

	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
<!--		--><?php //
//			if ($model->scenario!='new' && $model->scenario!='view') {
//				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
//					'submit'=>Yii::app()->createUrl('custtype/new')));
//			}
//		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('historyrank/index_s')));
		?>

	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
            <div> <h1>销售历程</h1>
                <h5 ><?php echo $model['lic'];?></h5>
            </div>

			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
            <?php  foreach ($model['rank'] as $a){?>
            <div style="width: 230px;text-align: center;float: left;">
                <img src="../images/<?php echo $a['rank'].'.png'?>">
                    <h2 style="margin-top: 0px;">第<?php echo $a['season'];?>赛季</h2>
                <span><?php $b=$a['month'];echo $start=date('Y-m', strtotime("$b -1 month")).'至'.date('Y-m', strtotime("$b"));?></span>
            </div>
            <?php }?>

		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = Script::genDeleteData(Yii::app()->createUrl('target/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


