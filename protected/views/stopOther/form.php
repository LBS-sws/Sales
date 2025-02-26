<?php
$this->pageTitle=Yii::app()->name . ' - StopOther Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'StopOther-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Stop Customer Other').Yii::t('customer',' Form'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('stopOther/index')));
		?>
	</div>
            <?php if (Yii::app()->user->validRWFunction('SC02')): ?>
            <div class="btn-group" role="group">
                <div class="btn-group" role="group">
                    <?php
                    echo TbHtml::button(Yii::t('misc','Distribution'), array(
                        'submit'=>Yii::app()->createUrl('stopOther/shiftOne')));
                    ?>
                </div>
                <div class="btn-group">
                    <select class="form-control" name="StopOtherForm[shiftStaff]">
                        <option value=""><?php echo Yii::t('report','Please select the assigned person');?></option>
                        <?php foreach ($saleman as $v) {?>
                            <option value="<?php echo $v['id'];?>"><?php echo $v['name'];?> </option>
                        <?php }?>
                    </select>
                </div>
            </div>
            <?php endif ?>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'service_id'); ?>

            <?php $this->renderPartial('//site/serviceForm',array("model"=>$model,"form"=>$form)); ?>
		</div>
	</div>
</section>
<?php

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


