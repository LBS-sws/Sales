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
		<strong><?php echo Yii::t('app','main lbs setting'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('mainLbs/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('mainLbs/index')));
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('mainLbs/save')));
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
                    $citySelectList = CGetName::getCityListWithCityAllow(Yii::app()->user->city_allow());
                    echo $form->dropDownList($model, "city",$citySelectList,
                        array('id'=>"city")
                    ); ?>
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
				<?php echo $form->labelEx($model,'sh_code',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textField($model, 'sh_code',
						array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
					); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'mh_code',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textField($model, 'mh_code',
						array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
					); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'show_type',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->dropDownList($model, 'show_type',CGetName::getShowTypeList(),
						array('readonly'=>($model->scenario=='view'),'id'=>'show_type')
					); ?>
				</div>
			</div>
			<div class="form-group <?php if($model->show_type!=3){ echo 'hide';}?>">
				<?php echo $form->labelEx($model,'show_city',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->dropDownList($model, 'show_city',$citySelectList,
						array('readonly'=>($model->scenario=='view'),'id'=>'show_city','multiple'=>'multiple')
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
$('#show_type').change(function(){
    if($(this).val()==3){
        $('#show_city').parents('.form-group').eq(0).removeClass('hide');
    }else{
        $('#show_city').parents('.form-group').eq(0).addClass('hide');
    }
});
$('#city').select2({
    multiple: false,
    maximumInputLength: 10,
    language: '$lang',
    disabled: $disabled
});
$('#show_city').select2({
	tags: false,
	multiple: true,
	maximumInputLength: 0,
	maximumSelectionLength: 10,
	allowClear: true,
	language: '$lang',
	disabled: $disabled,
	templateSelection: formatState
});
";
Yii::app()->clientScript->registerScript('selectBoxFunction',$js,CClientScript::POS_READY);
$js = <<<EOF
function formatState(state) {
	var rtn = $('<span style="color:black">'+state.text+'</span>');
	return rtn;
}
EOF;
Yii::app()->clientScript->registerScript('formatState',$js,CClientScript::POS_READY);
$js = Script::genDeleteData(Yii::app()->createUrl('mainLbs/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


