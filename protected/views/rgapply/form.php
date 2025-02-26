<?php
if (empty($model->id)&&$model->scenario == "edit"){
    $this->redirect(Yii::app()->createUrl('rgapply/index'));
}
$this->pageTitle=Yii::app()->name . ' - rgapply Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'rgapply-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
    'htmlOptions'=>array('enctype' => 'multipart/form-data')
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('redeem','Apply title'); ?></strong>
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
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('redeem','Back'), array(
				'submit'=>Yii::app()->createUrl('rgapply/index')));
		?>

<!--        --><?php //if ($model->scenario!='view'): ?>
            <?php if ($model->status == 0): ?>
                <?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('redeem','examine'), array(
                    'submit'=>Yii::app()->createUrl('rgapply/audit/1')));
                ?>

                <?php echo TbHtml::button('<span class="fa fa-mail-reply-all"></span> '.Yii::t('redeem','reject'),  array(
                    'submit'=>Yii::app()->createUrl('rgapply/audit/2')));
                ?>
            <?php endif ?>

<!--        --><?php //endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'status'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'employee_id'); ?>
<!--            --><?php //if ($model->status == 0): ?>
<!--                <div class="form-group has-error">-->
<!--                    --><?php //echo $form->labelEx($model,'id',array('class'=>"col-sm-2 control-label")); ?>
<!--                    <div class="col-sm-5">-->
<!--                        --><?php //echo $form->textArea($model, 'id',
//                            array('readonly'=>true)
//                        ); ?>
<!--                    </div>-->
<!--                </div>-->
<!--                <legend></legend>-->
<!--            --><?php //endif; ?>

            <?php
            $this->renderPartial('//site/rgapply',array(
                'form'=>$form,
                'model'=>$model,
                'readonly'=>($model->scenario=='view'||$model->status == 1||$model->status == 2),
            ));
            ?>


		</div>
	</div>
</section>

<?php
$this->renderPartial('//site/removedialog');
?>
<?php

$js = Script::genDeleteData(Yii::app()->createUrl('rgapply/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

</div><!-- form -->

