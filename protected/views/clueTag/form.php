<?php
$this->breadcrumbs=array(
	'System Setting'=>array('url'=>'javascript:void(0);'),
	'Clue Tag'=>array('url'=>'clueTag/index'),
	$model->id?'Edit':'New',
);
?>
<section class="content-header">
	<h1>
		<strong><?php echo $model->id?'编辑':'新增'; ?>客户标签</strong>
	</h1>
</section>

<section class="content">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'clue-tag-form',
		'action'=>Yii::app()->createUrl('clueTag/save'),
		'method'=>'post',
		'enableAjaxValidation'=>false,
	)); ?>

	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> 新增另一记录', array(
					'submit'=>Yii::app()->createUrl('clueTag/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> 返回', array(
				'submit'=>Yii::app()->createUrl('clueTag/index'))); 
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
				<?php echo $form->labelEx($model,'tag_code',array('class'=>"control-label")); ?>
				<div>
					<?php echo $form->textField($model,'tag_code',array('class'=>'form-control','style'=>'max-width:500px')); ?>
					<?php echo $form->error($model,'tag_code'); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'tag_name',array('class'=>"control-label")); ?>
				<div>
					<?php echo $form->textField($model,'tag_name',array('class'=>'form-control','style'=>'max-width:500px')); ?>
					<?php echo $form->error($model,'tag_name'); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'tag_color',array('class'=>"control-label")); ?>
				<div>
					<div style="display:flex; gap:10px; align-items:center;">
						<input type="color" id="tag_color_picker" value="<?php echo $model->tag_color?$model->tag_color:'#999999'; ?>" style="width:60px; height:40px; border:none; border-radius:4px; cursor:pointer;">
						<?php echo $form->textField($model,'tag_color',array('class'=>'form-control','id'=>'tag_color_input','style'=>'max-width:150px','readonly'=>true)); ?>
					</div>
					<?php echo $form->error($model,'tag_color'); ?>
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
				<?php echo $form->labelEx($model,'tag_desc',array('class'=>"control-label")); ?>
				<div>
					<?php echo $form->textArea($model,'tag_desc',array('class'=>'form-control','rows'=>3,'style'=>'max-width:600px')); ?>
					<?php echo $form->error($model,'tag_desc'); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'status',array('class'=>"control-label")); ?>
				<div>
					<?php echo $form->dropDownList($model,'status',array(1=>'启用',0=>'禁用'),array('class'=>'form-control','style'=>'max-width:500px')); ?>
					<?php echo $form->error($model,'status'); ?>
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
$js = Script::genDeleteData(Yii::app()->createUrl('clueTag/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<script>
$(function(){
	// 颜色拾取器与输入框同步
	$('#tag_color_picker').on('change input', function(){
		var color = $(this).val();
		$('#tag_color_input').val(color);
	});
	
	// 输入框修改时同步拾取器
	$('#tag_color_input').on('change', function(){
		var color = $(this).val() || '#999999';
		$('#tag_color_picker').val(color);
	});
});
</script>
