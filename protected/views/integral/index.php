<?php
$this->pageTitle=Yii::app()->name . ' - Integral';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'Integral-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('code','Integral'); ?></strong>
	</h1>
</section>

<section class="content">
<!--	<div class="box"><div class="box-body">-->
<!--	<div class="btn-group" role="group">-->
<!--		--><?php //
//			if (Yii::app()->user->validRWFunction('HC03'))
//				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
//					'submit'=>Yii::app()->createUrl('custtype/new'),
//				));
//		?>
<!--	</div>-->
<!--	</div></div>-->
	<?php $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('code','Integral List'),
			'model'=>$model,
				'viewhdr'=>'//integral/_listhdr',
				'viewdtl'=>'//integral/_listdtl',
				'search'=>array(
							'year',
							'month',
							'city',
                            'name',
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
echo TBhtml::button('dummyButtin',array('style'=>'display:none','disabled'=>true,'submit'=>'#',));
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
