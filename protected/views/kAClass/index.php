<?php
$this->pageTitle=Yii::app()->name . ' - Visit Type';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','KA Class'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if (Yii::app()->user->validRWFunction('KA10'))
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
					'submit'=>Yii::app()->createUrl('kAClass/new'), 
				)); 
		?>
	</div>
	</div></div>
	<?php $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('ka','Setting Type List'),
			'model'=>$model,
				'viewhdr'=>'//kAClass/_listhdr',
				'viewdtl'=>'//kAClass/_listdtl',
				'search'=>array(
							'pro_name',
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
