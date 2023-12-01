<?php
$this->pageTitle=Yii::app()->name . ' - Market Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    .select2>.selection>.select2-selection{ height: 33px;}
    @media (min-width: 768px){
        .col-sm-1.control-label {
            padding-left: 0px;
            padding-right: 0px;
            white-space: nowrap;
        }
    }
</style>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Market Company'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('marketCompany/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('marketCompany/index'))); 
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('marketCompany/save'))); 
			?>
<?php endif ?>
<?php if ($model->scenario=='edit'&&MarketFun::isAssign($model)): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
                <div class="btn-group pull-right" role="group">
                    <?php if ($model->scenario!='new'): ?>
                        <?php if (MarketFun::isAssign($model)): ?>
                            <?php echo TbHtml::button('<span class="fa fa-envelope"></span> '.Yii::t('market','Assign'), array(
                                    'data-toggle'=>'modal','data-target'=>'#assigndialog',)
                            );
                            ?>
                        <?php endif ?>
                        <?php if (!in_array($model->status_type,array(8,10))): ?>
                            <?php echo TbHtml::button('<span class="fa fa-trash"></span> '.Yii::t('market','Reject'), array(
                                    'data-toggle'=>'modal','data-target'=>'#rejectDialog',)
                            );
                            ?>
                            <?php echo TbHtml::button('<span class="fa fa-glass"></span> '.Yii::t('market','Success'), array(
                                    'data-toggle'=>'modal','data-target'=>'#successDialog',)
                            );
                            ?>
                        <?php endif ?>
                        <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('market','Flow Info'), array(
                                'data-toggle'=>'modal','data-target'=>'#flowinfodialog',)
                        );
                        ?>
                    <?php endif ?>
                    <?php
                    $counter = ($model->no_of_attm['market'] > 0) ? ' <span id="docmarket" class="label label-info">'.$model->no_of_attm['market'].'</span>' : ' <span id="docmarket"></span>';
                    echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('misc','Attachment').$counter, array(
                            'name'=>'btnFile','id'=>'btnFile','data-toggle'=>'modal','data-target'=>'#fileuploadmarket',)
                    );
                    ?>
                </div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'status_type'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'city',array("id"=>"city")); ?>
            <?php echo CHtml::hiddenField('dtltemplate_user'); ?>
            <?php echo CHtml::hiddenField('dtltemplate_info'); ?>
            <?php echo CHtml::hiddenField('assign_id',$model->id); ?>

            <?php if (in_array($model->status_type,array(1,2))): ?>
                <div class="form-group has-error">
                    <?php echo $form->labelEx($model,'back_note',array('class'=>"col-sm-2 control-label")); ?>
                    <div class="col-sm-7">
                        <?php echo $form->textArea($model, 'back_note',
                            array('readonly'=>(true),'rows'=>4)
                        ); ?>
                    </div>
                </div>
            <?php endif ?>

            <?php if ($model->status_type==8): ?>
                <div class="form-group has-error">
                    <?php echo $form->labelEx($model,'reject_note',array('class'=>"col-sm-2 control-label")); ?>
                    <div class="col-sm-7">
                        <?php echo $form->textArea($model, 'reject_note',
                            array('readonly'=>(true),'rows'=>4)
                        ); ?>
                    </div>
                </div>
            <?php endif ?>

            <?php if ($model->scenario!='new'): ?>
                <div class="form-group">
                    <?php echo $form->labelEx($model,'number_no',array('class'=>"col-sm-2 control-label")); ?>
                    <div class="col-sm-2">
                        <?php echo $form->textField($model, 'number_no',
                            array('readonly'=>(true))
                        ); ?>
                    </div>
                    <?php echo $form->labelEx($model,'status_type',array('class'=>"col-sm-2 control-label")); ?>
                    <div class="col-sm-2">
                        <?php
                        echo TbHtml::textField("status_type",MarketFun::getStatusStr($model),array("readonly"=>true));
                         ?>
                    </div>
                </div>
            <?php endif ?>

            <?php
            $this->renderPartial('//marketForm/marketForm',array(
                'model'=>$model,
                'form'=>$form
            ));
            ?>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
    'form'=>$form,
    'doctype'=>'MARKET',
    'header'=>Yii::t('dialog','File Attachment'),
    'ronly'=>($model->scenario=='view' || $model->isReadOnly()),
));
?>
<?php $this->renderPartial('//marketForm/historylist',array("model"=>$model)); ?>
<?php
Script::genFileUpload($model,$form->id,'MARKET');
$js = "
$('table').on('change','[id^=\"MarketCompanyForm\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#MarketCompanyForm_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});
";
Yii::app()->clientScript->registerScript('setFlag',$js,CClientScript::POS_READY);

$disabled = (!$model->isReadOnly()) ? 'false' : 'true';
$js ="
$('#city_name').select2({
	tags: true,
	multiple: false,
	maximumInputLength: 0,
	maximumSelectionLength: 10,
	disabled: $disabled,
	templateSelection: formatState
});
function formatState(state) {
   $('#city').val(state.id);
	var rtn = $('<span>'+state.text+'</span>');
	return rtn;
}
$('#city_name').change(function(){
    var area = $('#city_name>option:selected').data('area');
	if($('#area').val()==''&&$('#area>option[value=\"'+area+'\"]').length==1){
	    $('#area').val(area);
	}
});
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
$js = Script::genDeleteData(Yii::app()->createUrl('marketCompany/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);
$uid = Yii::app()->user->id;
if ($model->scenario!='view') {
    $language = Yii::app()->language;
    $js = <<<EOF
$('table').on('click','#btnDelRow', function() {
	$(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
	$(this).closest('tr').hide();
});
EOF;
    Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);

    $js = <<<EOF
    $('table').on('change','.user_phone',function(){
        $(this).parents('tr:first').find('.user_wechat').val($(this).val());
    });
    
$(document).ready(function(){
	var user = $('#tblDetail_user tr').eq(1).html();
	var info = $('#tblDetail_info tr').eq(1).html();
	$('#dtltemplate_user').attr('value',user);
	$('#dtltemplate_info').attr('value',info);
});

$('.btnAddRow').on('click',function() {
	var table = $(this).parents('table:first');
	var r = table.find('tr').length;
	if (r>0) {
		var nid = '';
		var expr = table.data('expr');
		var ct = $('#dtltemplate'+expr).val();
		table.find('tbody:last').append('<tr>'+ct+'</tr>');
		table.find('tr').eq(-1).find('[id*=\"MarketCompanyForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);

			if (id.indexOf('_id') != -1) $(this).attr('value',0);
			if (id.indexOf('_info_date') != -1){
			    $(this).removeClass('readonly').removeAttr('readonly');
			    $(this).attr('value','');
			    $(this).datepicker({autoclose: true,language: '$language', format: 'yyyy/mm/dd'});
			}
			if (id.indexOf('_info_text') != -1){
			    $(this).removeClass('readonly').removeAttr('readonly');
			    $(this).val('');
			}
			if (id.indexOf('_state_id') != -1){
			    $(this).removeClass('readonly').removeAttr('readonly');
			    $(this).val('');
			}
			if (id.indexOf('_user_name') != -1) $(this).val('');
			if (id.indexOf('_user_dept') != -1) $(this).val('');
			if (id.indexOf('_user_phone') != -1) $(this).val('');
			if (id.indexOf('_user_email') != -1) $(this).val('');
			if (id.indexOf('_user_wechat') != -1) $(this).val('');
			if (id.indexOf('_user_text') != -1) $(this).val('');
			if (id.indexOf('_lcu') != -1) $(this).attr('value','{$uid}');
		});
	}
});
EOF;
    Yii::app()->clientScript->registerScript('addRow',$js,CClientScript::POS_READY);

    $dateList = array(
        'company_date,.info_date',
    );
    $js = Script::genDatePicker($dateList);
    Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

<?php
$this->renderPartial('//marketForm/assigndialog',array(
    'assignType'=>'form',
    'submit'=>Yii::app()->createUrl('marketCompany/assign'),
    'jsHtml'=>"",
));
?>

<?php
$this->renderPartial('//marketForm/rejectDialog',array(
    'submit'=>Yii::app()->createUrl('marketCompany/reject'),
    'type_num'=>1,
    'jsHtml'=>""
));
?>

<?php
$this->renderPartial('//marketForm/successDialog',array(
    'submit'=>Yii::app()->createUrl('marketCompany/success'),
    'type_num'=>1,
    'jsHtml'=>""
));
?>
