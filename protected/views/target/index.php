<?php
$this->pageTitle=Yii::app()->name . ' - Target';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'Target-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('code','Target'); ?></strong>
	</h1>
</section>

<section class="content">
<!--	<div class="box"><div class="box-body">-->
<!--	<div class="btn-group" role="group">-->
<!--		--><?php
//				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
//					'submit'=>Yii::app()->createUrl('target/new'),
//				));
//		?>
<!--	</div>-->
<!--	</div></div>-->
	<?php $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('code','Target List'),
			'model'=>$model,
				'viewhdr'=>'//target/_listhdr',
				'viewdtl'=>'//target/_listdtl',
				'search'=>array(
							'year',
							'month',
							'city',
                            'employee_name',
						),
                'hasDateButton'=>true,
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
echo TBhtml::button('dummyButtin',array('style'=>'display:none','disabled'=>true,'submit'=>'#',));
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
