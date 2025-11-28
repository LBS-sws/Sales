<?php
$this->pageTitle=Yii::app()->name . ' - Visit Type Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    .select2-container.select2-container-disabled .select2-choice {
        background-color: #ddd;
        border-color: #a8a8a8;
    }
    select.readonly{ pointer-events: none;}
    select[readonly]{pointer-events: none;}
    .select2.select2-container{ width: 100%!important;}
    .select2-container--default .select2-selection--multiple .select2-selection__choice{ padding: 0px 7px;}
    .select2-container .select2-selection--single{ height: 34px;}

</style>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','menu setting'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('setMenu/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('setMenu/index')));
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('setMenu/save')));
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
			<?php echo $form->hiddenField($model, 'set_id'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'set_id',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->numberField($model, 'set_id',
						array('readonly'=>($model->scenario!='new'))
					); ?>
				</div>
                <div class="col-lg-8">
                    <p class="form-control-static text-danger">不知道如何设置可以空着</p>
                </div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'set_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->textField($model, 'set_name',
						array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
					); ?>
				</div>
                <div class="col-sm-8">
                    <p class="form-control-static text-danger" id="setTypeHint"></p>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'set_type',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
                    <?php
                    echo $form->dropDownList($model, 'set_type',CGetName::getSetMenuList(),
                        array('readonly'=>($model->scenario!='new'),'options'=>CGetName::getSetMenuHintList(),'empty'=>'','id'=>'set_type')
                    ); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'u_code',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
                    <?php
                    echo $form->textField($model, 'u_code',
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'mh_code',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
                    <?php
                    echo $form->textField($model, 'mh_code',
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'z_display',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->inlineRadioButtonList($model, 'z_display',CGetName::getDisplayList(),
						array('readonly'=>($model->scenario=='view'))
					); ?>
				</div>
			</div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'z_index',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php
                    echo $form->numberField($model, 'z_index',
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
                <div class="col-lg-8">
                    <p class="form-control-static"><?php echo Yii::t("customer","z_index_title"); ?></p>
                </div>
            </div>
			
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
switch(Yii::app()->language) {
    case 'zh_cn': $lang = 'zh-CN'; break;
    case 'zh_tw': $lang = 'zh-TW'; break;
    default: $lang = Yii::app()->language;
}
$disabled = $model->scenario=='view'? 'true':'false';
$js="
$('#set_type').change(function(){
    var hintText = $(this).find('option:selected').data('hint');
    $('#setTypeHint').text(hintText);
});
$('#set_type').trigger('change');
";
Yii::app()->clientScript->registerScript('selectBoxFunction',$js,CClientScript::POS_READY);
$js = <<<EOF
function formatState(state) {
	var rtn = $('<span style="color:black">'+state.text+'</span>');
	return rtn;
}
EOF;
Yii::app()->clientScript->registerScript('formatState',$js,CClientScript::POS_READY);
$js = Script::genDeleteData(Yii::app()->createUrl('setMenu/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


