<?php
$this->breadcrumbs=array(
	'System Setting'=>array('url'=>'javascript:void(0);'),
	'Clue Level'=>array('url'=>'clueLevel/index'),
	$model->id?'Edit':'New',
);
?>
<section class="content-header">
	<h1>
		<strong><?php echo $model->id?'编辑':'新增'; ?>客户等级</strong>
	</h1>
</section>

<section class="content">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'clue-level-form',
		'action'=>Yii::app()->createUrl('clueLevel/save'),
		'method'=>'post',
		'enableAjaxValidation'=>false,
	)); ?>

	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> 新增另一记录', array(
					'submit'=>Yii::app()->createUrl('clueLevel/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> 返回', array(
				'submit'=>Yii::app()->createUrl('clueLevel/index'))); 
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::submitButton('<span class="fa fa-upload"></span> 保存', array(
				'color'=>TbHtml::BUTTON_COLOR_PRIMARY)); 
			?>
<?php endif ?>
<?php if ($model->scenario=='edit'): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> 删除', array(
			'name'=>'btnDelete2','id'=>'btnDelete2','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model,'scenario'); ?>
			<?php echo $form->hiddenField($model,'id'); ?>
			<?php echo $form->errorSummary($model); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'level_code',array('class'=>"control-label")); ?>
				<div>
					<?php echo $form->textField($model,'level_code',array('class'=>'form-control','style'=>'max-width:500px')); ?>
					<?php echo $form->error($model,'level_code'); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'level_name',array('class'=>"control-label")); ?>
				<div>
					<?php echo $form->textField($model,'level_name',array('class'=>'form-control','style'=>'max-width:500px')); ?>
					<?php echo $form->error($model,'level_name'); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'sort',array('class'=>"control-label")); ?>
				<div>
					<?php echo $form->numberField($model,'sort',array('class'=>'form-control','style'=>'max-width:500px')); ?>
					<?php echo $form->error($model,'sort'); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'status',array('class'=>"control-label")); ?>
				<div>
					<?php echo $form->dropDownList($model,'status',array(1=>'启用',0=>'禁用'),array('class'=>'form-control','style'=>'max-width:500px')); ?>
					<?php echo $form->error($model,'status'); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'level_desc',array('class'=>"control-label")); ?>
				<div>
					<?php echo $form->textArea($model,'level_desc',array('class'=>'form-control','rows'=>3,'style'=>'max-width:600px')); ?>
					<?php echo $form->error($model,'level_desc'); ?>
				</div>
			</div>

			<?php if ($model->scenario!='view'): ?>
				<div class="form-group" style="display:none;">
					<?php echo TbHtml::button('<span class="fa fa-remove"></span> 删除', array(
							'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
						);
					?>
				</div>
			<?php endif ?>

			<?php $this->endWidget(); ?>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = Script::genDeleteData(Yii::app()->createUrl('clueLevel/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>
