<?php
$this->pageTitle=Yii::app()->name . ' - ClubSetting';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'clubSetting-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Club setting'); ?></strong>
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
		<?php 
			if (Yii::app()->user->validRWFunction('HC10'))
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add'), array(
					'submit'=>Yii::app()->createUrl('clubSetting/new'),
				)); 
		?>
	</div>
            <div class="pull-right">
                <p style="margin: 7px 0px;">本页面将影响<a href="<?php echo Yii::app()->createUrl('servicePlan/index');?>">“服务计划”</a>的选项。</p>
            </div>
	</div></div>
	<?php $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('club','club setting list'),
			'model'=>$model,
				'viewhdr'=>'//clubSetting/_listhdr',
				'viewdtl'=>'//clubSetting/_listdtl',
				'gridsize'=>'24',
				'height'=>'600',
				'search'=>array(
							'explain_text'
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
