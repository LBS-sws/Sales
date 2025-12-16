<?php
$this->breadcrumbs=array(
	'System Setting'=>array('url'=>'javascript:void(0);'),
	'Clue Tag'=>array('url'=>'clueTag/index'),
	'View',
);
?>
<div class="container-fluid">
	<div class="page-header">
		<h1>
			查看客户标签
		</h1>
	</div>

	<?php $this->widget('zii.widgets.CDetailView', array(
		'data'=>$model,
		'attributes'=>array(
			'id',
			'tag_code',
			'tag_name',
			'tag_desc',
			array(
				'name'=>'tag_color',
				'value'=>'<span style="background-color:'.$model->tag_color.';color:white;padding:5px 10px;border-radius:3px;">'.$model->tag_color.'</span>',
				'type'=>'html',
			),
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
			'url'=>array('clueTag/edit','index'=>$model->id),
		)); ?>
		<?php echo TbHtml::linkButton('返回', array(
			'color'=>TbHtml::BUTTON_COLOR_DEFAULT,
			'url'=>array('clueTag/index'),
		)); ?>
	</div>
</div>
