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
    .media_table,.media_remark,.media_text {
        display: block;
        width: 100%;
    }
    .media_remark,.media_text{ padding: 0px 15px;}
    .media_text>p{ margin: 0px;}
    @media (min-width: 768px){
        .col-sm-1.control-label {
            padding-left: 0px;
            padding-right: 0px;
            white-space: nowrap;
        }
        .media_table{ display: table;width: 100%;}
        .media_remark,.media_text{ display: table-cell;vertical-align: bottom;}
        .media_remark{ width: 50%;}
        .media_text{ padding: 0px;}
    }
    input[readonly]{pointer-events: none;}
    select[readonly]{pointer-events: none;}
    .select2-container .select2-selection--single{ height: 34px;}
	
	
			.bat_phone_div_click.open{ }
			.bat_phone_div_click{ border-top:1px solid #d2d6de;padding-bottom:4px;}
			.bat_phone_div_click span{ display:inline-block;border:1px solid #d2d6de; padding:7px 12px;}
</style>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','CA Bot'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('cABot/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('cABot/index')));
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('cABot/save')));
			?>
<?php endif ?>
<?php if ($model->scenario=='edit'): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>

            <div class="btn-group pull-right" role="group">
                <?php if ($model->scenario!='new'): ?>
                    <?php
                    if (Yii::app()->user->validFunction('CN18')){
                        echo TbHtml::button('<span class="fa fa-refresh"></span> '.Yii::t('ka','Shift'), array(
                                'data-toggle'=>'modal','data-target'=>'#shiftDialog',)
                        );
                    }
                    ?>

                    <?php

                    if($model->employee_id==$model->kam_id){
                        echo TbHtml::button('<span class="glyphicon glyphicon-pencil"></span> '."编辑", array(
                            'submit'=>Yii::app()->createUrl('cABot/edit',array("index"=>$model->id))));
                    }
                    ?>
                    <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('ka','Flow Info'), array(
                            'data-toggle'=>'modal','data-target'=>'#flowinfodialog',)
                    );
                    ?>
                <?php endif ?>
                <?php
                $counter = ($model->no_of_attm[$model->file_key] > 0) ? ' <span id="doc'.$model->file_key.'" class="label label-info">'.$model->no_of_attm[$model->file_key].'</span>' : ' <span id="doc'.$model->file_key.'"></span>';
                echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('misc','Attachment').$counter, array(
                        'name'=>'btnFile','id'=>'btnFile','data-toggle'=>'modal','data-target'=>'#fileupload'.$model->file_key,)
                );
                ?>
            </div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'city'); ?>
			<?php echo $form->hiddenField($model, 'kam_id'); ?>
            <?php echo CHtml::hiddenField('dtltemplate_info'); ?>
            <?php echo CHtml::hiddenField('dtltemplate_ava'); ?>

            <?php $this->renderPartial('//kABot/shiftForm',array("model"=>$model)); ?>

            <div class="form-group">
                <?php echo $form->labelEx($model,'apply_date',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'apply_date',
                        array('readonly'=>($model->scenario!='new'),'autocomplete'=>'off','prepend'=>'<span class="fa fa-calendar"></span>','id'=>'apply_date')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'kam_id',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'kam_name',
                        array('readonly'=>(true))
                    ); ?>
                </div>
                <?php if ($model->scenario!='new'): ?>
                <?php echo $form->labelEx($model,'customer_no',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'customer_no',
                        array('readonly'=>(true))
                    ); ?>
                </div>
                <?php endif ?>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'customer_name',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-7">
                    <div class="btn-group" style="width: 100%">
                        <?php echo $form->textField($model, 'customer_name', array('maxlength'=>250,'id'=>'customer_name','autocomplete'=>'off','readonly'=>($model->scenario=='view'))); ?>
                        <ul class="dropdown-menu" id="customer_name_menu" style="width: 100%">
                        </ul>
                    </div>
                </div>

            </div>
			<div class="bat_phone_div_click text-center"><span>展开</span></div>
			<div class="bat_phone_div" style="display:none;">
				<div class="form-group">
					<?php echo $form->labelEx($model,'head_city_id',array('class'=>"col-sm-2 control-label")); ?>
					<div class="col-sm-2">
						<?php echo $form->dropDownList($model, 'head_city_id',KAAreaForm::getCityListForId($model->head_city_id),
							array('readonly'=>($model->scenario=='view'),'class'=>'changeCity')
						); ?>
					</div>
					<?php echo $form->labelEx($model,'talk_city_id',array('class'=>"col-sm-1 control-label")); ?>
					<div class="col-sm-2">
						<?php echo $form->dropDownList($model, 'talk_city_id',KAAreaForm::getCityListForId($model->talk_city_id),
							array('readonly'=>($model->scenario=='view'),'class'=>'changeCity','id'=>'talk_city_id')
						); ?>
					</div>
					<?php echo $form->labelEx($model,'area_id',array('class'=>"col-sm-1 control-label")); ?>
					<div class="col-sm-2">
						<?php echo $form->dropDownList($model, 'area_id',KAAreaForm::getCityListForId($model->area_id),
							array('readonly'=>($model->scenario=='view'),'class'=>'changeCity')
						); ?>
					</div>
				</div>
				<div class="form-group">
					<?php echo $form->labelEx($model,'work_user',array('class'=>"col-sm-2 control-label")); ?>
					<div class="col-sm-2">
						<?php echo $form->textField($model, 'work_user',
							array('readonly'=>($model->scenario=='view'))
						); ?>
					</div>
					<?php echo $form->labelEx($model,'work_phone',array('class'=>"col-sm-1 control-label")); ?>
					<div class="col-sm-2">
						<?php echo $form->textField($model, 'work_phone',
							array('readonly'=>($model->scenario=='view'))
						); ?>
					</div>
					<?php echo $form->labelEx($model,'work_email',array('class'=>"col-sm-1 control-label")); ?>
					<div class="col-sm-2">
						<?php echo $form->textField($model, 'work_email',
							array('readonly'=>($model->scenario=='view'))
						); ?>
					</div>
				</div>
				<div class="form-group">
					<?php echo $form->labelEx($model,'contact_user',array('class'=>"col-sm-2 control-label")); ?>
					<div class="col-sm-2">
						<?php echo $form->textField($model, 'contact_user',
							array('readonly'=>($model->scenario=='view'))
						); ?>
					</div>
					<?php echo $form->labelEx($model,'contact_phone',array('class'=>"col-sm-1 control-label")); ?>
					<div class="col-sm-2">
						<?php echo $form->textField($model, 'contact_phone',
							array('readonly'=>($model->scenario=='view'))
						); ?>
					</div>
					<?php echo $form->labelEx($model,'contact_email',array('class'=>"col-sm-1 control-label")); ?>
					<div class="col-sm-2">
						<?php echo $form->textField($model, 'contact_email',
							array('readonly'=>($model->scenario=='view'))
						); ?>
					</div>
				</div>
				<div class="form-group">
					<?php echo $form->labelEx($model,'contact_adr',array('class'=>"col-sm-2 control-label")); ?>
					<div class="col-sm-7">
						<?php echo $form->textField($model, 'contact_adr',
							array('readonly'=>($model->scenario=='view'))
						); ?>
					</div>
				</div>
			</div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'source_id',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->dropDownList($model, 'source_id',KASourceForm::getSourceListForId($model->source_id),
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'source_text',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->dropDownList($model, 'source_text',KASraForm::getSourceListForId($model->source_text),
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'class_id',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->dropDownList($model, 'class_id',KAClassForm::getClassListForId($model->class_id),
                        array('readonly'=>($model->scenario=='view'),"id"=>"class_id")
                    ); ?>
                    <?php
                    $classStyle = array('readonly'=>($model->scenario=='view'),"id"=>"class_other");
                    if(KAClassForm::getClassNameForId($model->class_id)!=="其它"){
                        $classStyle["class"]="hide";
                    }
                    echo $form->textField($model, 'class_other',$classStyle);
                    ?>
                </div>
                <?php echo $form->labelEx($model,'level_id',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->dropDownList($model, 'level_id',KALevelForm::getLevelListForId($model->level_id,"CKA"),
                        array('readonly'=>($model->scenario=='view'),"id"=>"level_id")
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'busine_id',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-7">
                    <?php echo $form->dropDownList($model, 'busine_id',KABusineForm::getBusineListForArr($model->busine_id),
                        array('readonly'=>($model->scenario=='view'),'id'=>'busine_id','class'=>'select2','multiple'=>'multiple')
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'link_id',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-4">
                    <?php echo $form->dropDownList($model, 'link_id',KALinkForm::getLinkListForId($model->link_id),
                        array('readonly'=>($model->scenario=='view'),'id'=>'link_id')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'support_user',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->dropDownList($model, 'support_user',CABotForm::getSupportUserList($model->talk_city_id,$model->support_user),
                        array('readonly'=>($model->scenario=='view'),'id'=>'support_user')
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'available_date',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'available_date',
                        array('readonly'=>($model->scenario=='view'),'autocomplete'=>'off','id'=>'available_date','prepend'=>'<span class="fa fa-calendar"></span>')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'available_amt',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'available_amt',
                        array('readonly'=>($model->scenario=='view'),'id'=>'available_amt','prepend'=>'<span class="fa fa-money"></span>')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'sign_odds',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->dropDownList($model, 'sign_odds',CABotForm::getSignOddsListForId(),
                        array('readonly'=>($model->scenario=='view'),'id'=>'sign_odds')
                    ); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'sign_date',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'sign_date',
                        array('readonly'=>($model->scenario=='view'),'autocomplete'=>'off','id'=>'sign_date','prepend'=>'<span class="fa fa-calendar"></span>')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'sign_month',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->dropDownList($model, 'sign_month',CABotForm::getSignMonthListForId(),
                        array('readonly'=>($model->scenario=='view'),'id'=>'sign_month')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'sum_amt',array('class'=>"col-sm-1 control-label text-red")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'sum_amt',
                        array('readonly'=>(true),'id'=>'sum_amt','prepend'=>'<span class="fa fa-money"></span>')
                    ); ?>
                </div>
            </div>

            <div id="ava_box" class="box changeTable <?php echo KALinkForm::getLinkRateNumForId($model->link_id)!==100?"hide":"";?>">
                <div class="box-body table-responsive">
                    <div class="col-lg-12">
                        <div class="row">
                            <?php
                            $this->widget('ext.layout.TableView2Widget', array(
                                'model'=>$model,
                                'attribute'=>'avaInfo',
                                'expr_id'=>'ava',
                                'viewhdr'=>'//cABot/_ava_hdr',
                                'viewdtl'=>'//cABot/_ava_dtl',
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'remark',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-8">
                    <?php echo $form->textArea($model, 'remark',
                        array('readonly'=>($model->scenario=='view'),'rows'=>4)
                    ); ?>
                </div>
            </div>


            <div class="box changeTable">
                <div class="box-body table-responsive">
                    <div class="col-lg-8 col-lg-offset-2">
                        <div class="row">
                            <?php
                            $this->widget('ext.layout.TableView2Widget', array(
                                'model'=>$model,
                                'attribute'=>'detail',
                                'expr_id'=>'info',
                                'viewhdr'=>'//cABot/_formhdr',
                                'viewdtl'=>'//cABot/_formdtl',
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
    'form'=>$form,
    'doctype'=>strtoupper($model->file_key),
    'header'=>Yii::t('dialog','File Attachment'),
    'ronly'=>($model->scenario=='view'),
));
?>
<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//cABot/historylist',array("model"=>$model)); ?>
<?php $this->renderPartial('//kABot/shiftDialog',array("model"=>$model,"submit"=>Yii::app()->createUrl('cABot/shift'))); ?>
<?php
Script::genFileUpload($model,$form->id,strtoupper($model->file_key));
$js = "
$('table').on('change','[id^=\"CABotForm\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#CABotForm_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});


			$('.bat_phone_div_click').click(function(){
				if($(this).hasClass('open')){
					$(this).removeClass('open');
					$(this).find('span').eq(0).text('展开');
					$(this).next('.bat_phone_div').slideUp(100);
				}else{
					$(this).addClass('open');
					$(this).find('span').eq(0).text('收起');
					$(this).next('.bat_phone_div').slideDown(100);
				}
			});
";

if ($model->scenario=='new') {
	$js.="$('.bat_phone_div_click').trigger('click');";
}
Yii::app()->clientScript->registerScript('setFlag',$js,CClientScript::POS_READY);
switch(Yii::app()->language) {
    case 'zh_cn': $lang = 'zh-CN'; break;
    case 'zh_tw': $lang = 'zh-TW'; break;
    default: $lang = Yii::app()->language;
}
$disabled = !($model->scenario=='view') ? 'false' : 'true';

$ajaxUrl=Yii::app()->createUrl('cABot/ajaxSupportUser');
$link3=Yii::app()->createUrl('cABot/ajaxCustomerName');
$js ="
function formatState(state) {
	var rtn = $('<span style=\"color:black\">'+state.text+'</span>');
	return rtn;
}
$('#support_user').select2({
    multiple: false,
    maximumInputLength: 10,
    language: '$lang',
    disabled: $disabled
});

$('#busine_id').select2({
	tags: false,
	multiple: true,
	maximumInputLength: 0,
	maximumSelectionLength: 10,
	allowClear: true,
	language: '$lang',
	disabled: $disabled,
	templateSelection: formatState
});

$('#class_id').change(function(){
    if($(this).children('option:selected').text()=='其它'){
        $('#class_other').removeClass('hide');
    }else{
        $('#class_other').addClass('hide');
    }
});

$('#link_id').change(function(){
    if($(this).children('option:selected').text().indexOf('100%')>-1){
        $('#ava_box').removeClass('hide');
        $('#sign_date').prop('readonly','');
        $('#sign_month').prop('disabled','');
        $('#sign_odds').val(100);
    }else{
        $('#ava_box').addClass('hide');
        $('#sign_date').val('').prop('readonly','readonly');
        $('#sign_month').val('').prop('disabled','disabled');
        if($('#sign_odds').val()==100){
            $('#sign_odds').val('');
        }
    }
});

function changeCustomerName(){
    var that = $(this);
    $(this).parent('div').addClass('open');
    $(this).next('.dropdown-menu').html('<li><a>查询中...</span></li>');
	var data = \"group=\"+$(this).val()+\"&id=\"+$('#CABotForm_id').val();
	$.ajax({
		type: 'GET',
		url: '$link3',
		data: data,
		success: function(data) {
			that.next('.dropdown-menu').html(data);
		},
		error: function(data) { // if error occured
			var x = 1;
		},
		dataType:'html'
	});
}
$('#customer_name').on('click',function(e){
    e.stopPropagation();
});
$('#customer_name').on('focus',changeCustomerName);
$('#customer_name').on('keyup',changeCustomerName);
$('body').on('click',function(){
    $('#customer_name').parent('div').removeClass('open');
});

    $('body').delegate('.changeSumAmt','change keyup',function(){
        var sum_amt=0;
        $('.changeSumAmt').each(function(){
            var amt=$(this).val();
            amt = amt==''?0:parseFloat(amt);
            sum_amt+=amt;
        });
        $('#sum_amt').val(sum_amt);
    });

    $('.changeCity').change(function(){
        var city = $(this).val();
        $('.changeCity').each(function(){
            if($(this).val()==''){
                $(this).val(city);
                if($(this).attr('id')=='talk_city_id'){
                    $(this).trigger('change');
                }
            }
        });
    });
    
    
    $('#talk_city_id').on('change',function(){
        $.ajax({
            type: 'POST',
            url: '{$ajaxUrl}',
            data: {
                'city':$('#talk_city_id').val()
            },
            dataType: 'json',
            success: function(data) {
                $('#support_user').html('');
                $.each(data['list'],function(value,name){
                    $('#support_user').prepend('<option value=\"'+value+'\">'+name+'</option>');
                });
                $('#support_user').val('');
            },
            error: function(data) { // if error occured
                alert('Error occured.please try again');
            }
        });
    });
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
$js = Script::genDeleteData(Yii::app()->createUrl('cABot/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

if ($model->scenario!='view') {
    $language = Yii::app()->language;
    $js = <<<EOF
$('table').on('click','.btnDelRow', function() {
	$(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
	$(this).closest('tr').hide();
});
EOF;
    Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);

    $js = <<<EOF
$(document).ready(function(){
    $('.changeTable').each(function(){
        if($(this).find('table.table').length==1){
            var expr_id = $(this).find('table.table').data('expr');
            var ct = $('#tblDetail'+expr_id+' tr').eq(1).html();
            $('#dtltemplate'+expr_id).attr('value',ct);
        }
    });
});

$('.btnAddRow').on('click',function() {
    var expr_id = $(this).parents('table.table').eq(0).data('expr');
	var r = $('#tblDetail'+expr_id+' tr').length;
	if (r>0) {
		var nid = '';
		var ct = $('#dtltemplate'+expr_id).val();
		$('#tblDetail'+expr_id+' tbody:last').prepend('<tr>'+ct+'</tr>');
		$('#tblDetail'+expr_id+' tbody>tr').eq(0).find('[id*=\"CABotForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);

			if (id.indexOf('_info_date') != -1){
			    $(this).attr('value','');
			    $(this).datepicker({autoclose: true,language: '$language', format: 'yyyy/mm/dd'});
			}
			if (id.indexOf('_ava_date') != -1){
			    $(this).attr('value','');
			    $(this).datepicker({autoclose: true,language: '$language', format: 'yyyy/mm',maxViewMode:2,minViewMode:1});
			}
			if (id.indexOf('_info_text') != -1) $(this).val('');
			if (id.indexOf('_ava_amt') != -1) $(this).val('');
			if (id.indexOf('_ava_rate') != -1) $(this).val('');
			if (id.indexOf('_ava_fact_amt') != -1) $(this).val('');
			if (id.indexOf('_ava_note') != -1) $(this).val('');
			if (id.indexOf('_ava_city') != -1) $(this).val('');
			if (id.indexOf('_ava_num') != -1) $(this).val('');
			if (id.indexOf('_id') != -1) $(this).attr('value',0);
		});
		if (nid != '') {
			var topos = $('#'+nid).position().top;
			$('#tbl_detail').scrollTop(topos);
		}
	}
});
EOF;
    Yii::app()->clientScript->registerScript('addRow',$js,CClientScript::POS_READY);

    $dateList = array(
        'sign_date,.info_date,#available_date',
    );
    if($model->scenario=='new'){
        $dateList[]="apply_date";
    }
    $js = Script::genDatePicker($dateList);
    $js.="
		$('.ava_date').datepicker({autoclose: true,language: '$language', format: 'yyyy/mm',maxViewMode:2,minViewMode:1});
	";
    Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


