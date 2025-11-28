<?php
$this->pageTitle=Yii::app()->name . ' - Import';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'import-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
'htmlOptions'=>array('enctype'=>'multipart/form-data'),
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','U Import'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			echo TbHtml::button(Yii::t('misc','Submit'), array('submit'=>Yii::app()->createUrl('import/submit'))); 
		//	echo TbHtml::button(Yii::t('misc','Submit'), array('submit'=>Yii::app()->createUrl('import/activate'))); 
		?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'id'); ?>

		<div class="form-group">
			<?php echo $form->labelEx($model,'import_type',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-3">
				<?php echo $form->dropDownList($model, 'import_type', $model->getImportTypeList(),array("id"=>"import_type")); ?>
			</div>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <?php
                    echo TbHtml::link("下载导入模板","#",array("id"=>"downExcelLink"));
                    ?>
                </p>
            </div>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'import_file',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-3">
				<?php echo $form->fileField($model, 'import_file',array("class"=>"form-control")); ?>
			</div>
            <div class="col-sm-7">
                <p class="form-control-static text-danger">* 必须是xlsx格式的文件，且文件大小不能大于5M</p>
			</div>
		</div>
	</div>
</section>

<?php

$link = Yii::app()->createUrl('import/downExcel');
$js = <<<EOF
$('#downExcelLink').on('click',function(){
    var url = '{$link}';
    url+='?type='+$('#import_type').val();
    window.open(url);
});
EOF;
Yii::app()->clientScript->registerScript('downExcel',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

</div><!-- form -->

