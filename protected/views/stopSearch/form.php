<?php
$this->pageTitle=Yii::app()->name . ' - StopSearch Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'StopSearch-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('customer','Stop Customer Search Form'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('stopSearch/index')));
		?>
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
            <?php if (Yii::app()->user->validFunction('CN13')): ?>
            <div class="form-group">
                <?php echo $form->labelEx($model,'lcu',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->textField($model, 'luu',
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'lcd',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->textField($model, 'lcd',
                        array('readonly'=>($model->scenario=='view'),
                            'prepend'=>'<span class="fa fa-calendar"></span>',
                        )
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'luu',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->textField($model, 'luu',
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'lud',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->textField($model, 'lud',
                        array('readonly'=>($model->scenario=='view'),
                            'prepend'=>'<span class="fa fa-calendar"></span>',
                        )
                    ); ?>
                </div>
            </div>
            <?php endif ?>

            <?php $this->renderPartial('//site/stopAgain',array("stop_id"=>$model->id)); ?>
            <legend><?php echo Yii::t("customer","service detail");?></legend>
            <?php $this->renderPartial('//site/serviceForm',array("model"=>$model,"form"=>$form)); ?>
        </div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = Script::genDeleteData(Yii::app()->createUrl('stopSearch/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);


$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


