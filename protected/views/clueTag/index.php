<?php
$this->pageTitle=Yii::app()->name . ' - Clue Tag';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'clue-tag-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong>客户标签</strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if (Yii::app()->user->validRWFunction('HC23'))
				echo TbHtml::button('<span class="fa fa-file-o"></span> 新增', array(
					'submit'=>Yii::app()->createUrl('clueTag/new'), 
				)); 
		?>
	</div>
	</div></div>
	<?php $this->widget('ext.layout.ListPageWidget', array(
			'title'=>'客户标签列表',
			'model'=>$model,
			'viewhdr'=>'//clueTag/_listhdr',
			'viewdtl'=>'//clueTag/_listdtl',
			'search'=>array(
				'tag_name',
			),
	));
	?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
?>
<?php $this->endWidget(); ?>

<?php
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
