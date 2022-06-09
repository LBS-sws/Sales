<?php
$this->pageTitle=Yii::app()->name . ' - StopBack Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'StopBack-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('app','Stop Customer Back').Yii::t('customer',' Form'); ?></strong>
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
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('stopBack/index')));
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('stopBack/save')));
			?>
<?php endif ?>
<?php if ($model->scenario!='new' && $model->scenario!='view' && !empty($model->id)): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
        <div class="box-body">
            <?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'service_id'); ?>

            <legend><?php echo Yii::t("customer","shift detail");?></legend>
            <div class="form-group">
                <?php echo $form->labelEx($model,'back_date',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->textField($model, 'back_date',
                        array('readonly'=>($model->scenario=='view'),'id'=>'back_date','autocomplete'=>'off',
                            'prepend'=>'<span class="fa fa-calendar"></span>',
                        )
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'back_type',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->dropDownList($model, 'back_type',StopTypeForm::getStopTypeList($model->back_type),
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'customer_name',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->textField($model, 'customer_name',
                        array('readonly'=>($model->scenario=='view'),'autocomplete'=>'off')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'bold_service',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3" style="padding-top: 6px;">
                    <?php echo $form->checkBox($model, 'bold_service',
                        array('readonly'=>($model->scenario=='view'),'autocomplete'=>'off')
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'back_remark',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-7">
                    <?php echo $form->textArea($model, 'back_remark',
                        array('readonly'=>($model->scenario=='view'),'autocomplete'=>'off','rows'=>3)
                    ); ?>
                </div>
            </div>

            <?php $this->renderPartial('//site/stopAgain',array("stop_id"=>$model->id)); ?>
            <legend><?php echo Yii::t("customer","service detail");?></legend>
            <?php $this->renderPartial('//site/serviceForm',array("model"=>$model,"form"=>$form)); ?>
        </div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = Script::genDeleteData(Yii::app()->createUrl('stopBack/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

if ($model->scenario!='view') {
    $js = Script::genDatePicker(array(
        'back_date',
    ));
    Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


