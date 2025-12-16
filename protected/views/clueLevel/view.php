<?php
$this->breadcrumbs=array(
	'System Setting'=>array('url'=>'javascript:void(0);'),
	'Clue Level'=>array('url'=>'clueLevel/index'),
	'View',
);
?>
<div class="container-fluid">
	<div class="page-header">
		<h1>
			查看客户等级
		</h1>
	</div>

	<?php $this->widget('zii.widgets.CDetailView', array(
		'data'=>$model,
		'attributes'=>array(
			'id',
			'level_code',
			'level_name',
			'level_desc',
			'sort',
			array(
				'name'=>'status',
				'value'=>$model->status==1?'启用':'禁用',
			),
		),
	)); ?>

	<div style="margin-top:20px;">
		<?php echo TbHtml::linkButton('编辑', array(
			'color'=>TbHtml::BUTTON_COLOR_WARNING,
			'url'=>array('clueLevel/edit','index'=>$model->id),
		)); ?>
		<?php echo TbHtml::linkButton('返回', array(
			'color'=>TbHtml::BUTTON_COLOR_DEFAULT,
			'url'=>array('clueLevel/index'),
		)); ?>
	</div>
</div>
