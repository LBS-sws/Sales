<?php
$this->pageTitle=Yii::app()->name . ' - Customer Type Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('code','Customer Type Form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('custtype/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('custtype/index'))); 
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('custtype/save'))); 
			?>
<?php endif ?>
<?php if ($model->scenario=='edit'): ?>
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

			<div class="form-group">
				<?php echo $form->labelEx($model,'city',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php 
						$list = $model->getCityList();
						echo $form->dropDownList($model, 'city', $list, array('readonly'=>($model->scenario=='view'))); 
					?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'type_group',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php 
						echo $form->dropDownList($model, 'type_group', 
							array(1=>Yii::t('sales','Catering'), 2=>Yii::t('sales','Non-catering')), 
							array('readonly'=>($model->scenario=='view'))); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textField($model, 'name', 
						array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'rpt_type',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->textField($model, 'rpt_type', 
						array('size'=>10,'maxlength'=>10,'readonly'=>($model->scenario=='view'))
					); ?>
				</div>
			</div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'z_display',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-5">
                    <?php
                    $list = array(Yii::t("customer","none"),Yii::t("customer","show"));
                    echo $form->inlineRadioButtonList($model, 'z_display',$list,
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
            </div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = Script::genDeleteData(Yii::app()->createUrl('custtype/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


