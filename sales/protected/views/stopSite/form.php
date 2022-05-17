<?php
$this->pageTitle=Yii::app()->name . ' - StopSite Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'StopSite-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Stop Customer Site').Yii::t('customer',' Form'); ?></strong>
	</h1>
<!--
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Layout</a></li>
		<li class="active">Top Navigation</li>
	</ol>
-->
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('stopSite/index')));
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('stopSite/save')));
			?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'stop_month',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-3">
				<?php echo $form->numberField($model, 'stop_month',
					array('readonly'=>($model->scenario=='view'),
                        'append'=>'<span>'.Yii::t('customer',' month').'</span>',
                        'prepend'=>'<span>'.Yii::t('customer','greater than ').'</span>',
                    )
				); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'month_money',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-3">
				<?php
                echo $form->numberField($model, 'month_money',
					array('readonly'=>($model->scenario=='view'),
                        'prepend'=>'<span>'.Yii::t('customer','greater than ').'</span>',)
				); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'year_money',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-3">
				<?php
                echo $form->numberField($model, 'year_money',
					array('readonly'=>($model->scenario=='view'),
                        'prepend'=>'<span>'.Yii::t('customer','greater than ').'</span>',)
				); ?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


