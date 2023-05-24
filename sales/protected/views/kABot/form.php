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
		<strong><?php echo Yii::t('app','KA Bot'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('kABot/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('kABot/index'))); 
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('kABot/save'))); 
			?>
<?php endif ?>
<?php if ($model->scenario=='edit'): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
            <?php if ($model->scenario!='new'): ?>
                <div class="btn-group pull-right" role="group">
                    <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('ka','Flow Info'), array(
                            'data-toggle'=>'modal','data-target'=>'#flowinfodialog',)
                    );
                    ?>
                </div>
            <?php endif ?>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'city'); ?>
            <?php echo CHtml::hiddenField('dtltemplate'); ?>

            <div class="form-group">
                <?php echo $form->labelEx($model,'apply_date',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'apply_date',
                        array('readonly'=>($model->scenario!='new'),'prepend'=>'<span class="fa fa-calendar"></span>','id'=>'apply_date')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'customer_no',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'customer_no',
                        array('readonly'=>($model->scenario!='new'))
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'kam_id',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'kam_id',
                        array('readonly'=>(true))
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'customer_name',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-7">
                    <?php echo $form->textField($model, 'customer_name',
                        array('readonly'=>($model->scenario!='new'))
                    ); ?>
                </div>
            </div>
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
                <?php echo $form->labelEx($model,'contact_dept',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'contact_dept',
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
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
                    <?php echo $form->textField($model, 'source_text',
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'busine_id',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->dropDownList($model, 'busine_id',KABusineForm::getBusineListForId($model->busine_id),
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'level_id',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->dropDownList($model, 'level_id',KALevelForm::getLevelListForId($model->level_id),
                        array('readonly'=>($model->scenario=='view'),"id"=>"level_id")
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'class_id',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php
                    $KAClassList = KALevelForm::getClassListForId($model->class_id);
                    echo $form->dropDownList($model, 'class_id',$KAClassList["list"],
                        array('readonly'=>($model->scenario=='view'),"options"=>$KAClassList["options"],"id"=>"class_id")
                    );
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'link_id',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-4">
                    <?php echo $form->dropDownList($model, 'link_id',KALinkForm::getLinkListForId($model->link_id),
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'month_amt',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'month_amt',
                        array('readonly'=>($model->scenario=='view'),'prepend'=>'<span class="fa fa-money"></span>')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'quarter_amt',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'quarter_amt',
                        array('readonly'=>($model->scenario=='view'),'prepend'=>'<span class="fa fa-money"></span>')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'year_amt',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'year_amt',
                        array('readonly'=>($model->scenario=='view'),'prepend'=>'<span class="fa fa-money"></span>')
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'sign_date',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'sign_date',
                        array('readonly'=>($model->scenario=='view'),'id'=>'sign_date','prepend'=>'<span class="fa fa-calendar"></span>')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'sign_month',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'sign_month',
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'sign_amt',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'sign_amt',
                        array('readonly'=>($model->scenario=='view'),'prepend'=>'<span class="fa fa-money"></span>')
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'support_user',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->dropDownList($model, 'support_user',KABotForm::getSupportUserList($model->talk_city_id,$model->support_user),
                        array('readonly'=>($model->scenario=='view'),'id'=>'support_user')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'sign_odds',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->dropDownList($model, 'sign_odds',KABotForm::getSignOddsListForId(),
                        array('readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'remark',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-5">
                    <?php echo $form->textArea($model, 'remark',
                        array('readonly'=>($model->scenario=='view'),'rows'=>4)
                    ); ?>
                </div>
            </div>


            <div class="box">
                <div class="box-body table-responsive">
                    <div class="col-lg-8 col-lg-offset-2">
                        <div class="row">
                            <?php
                            $this->widget('ext.layout.TableView2Widget', array(
                                'model'=>$model,
                                'attribute'=>'detail',
                                'viewhdr'=>'//kABot/_formhdr',
                                'viewdtl'=>'//kABot/_formdtl',
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//kABot/historyList',array("model"=>$model)); ?>

<?php
$js = "
$('table').on('change','[id^=\"KABotForm\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#KABotForm_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});
";
Yii::app()->clientScript->registerScript('setFlag',$js,CClientScript::POS_READY);
$ajaxUrl=Yii::app()->createUrl('kABot/ajaxSupportUser');
$js ="
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
    
    $('#level_id').change(function(){
        var level = $(this).val();
        if(level!=''){
            $('#class_id>option').hide();
            $('#class_id>option').eq(0).show();
            $('#class_id>option[data-level='+level+']').show();
            $('#class_id').val('');
        }else{
            $('#class_id>option').show();
        }
    });
    
    $('#class_id').change(function(){
        var level = $('#class_id>option:selected').data('level');
        if($('#level_id').val()==''&&level!=undefined){
            $('#level_id').val(level);
            $('#class_id>option[data-level!='+level+']').hide();
            $('#class_id>option').eq(0).show();
        }
    });
    $('#talk_city_id').on('change',function(){
        console.log('ajax');
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
$js = Script::genDeleteData(Yii::app()->createUrl('kABot/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

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
$(document).ready(function(){
	var ct = $('#tblDetail tr').eq(1).html();
	$('#dtltemplate').attr('value',ct);
});

$('#btnAddRow').on('click',function() {
	var r = $('#tblDetail tr').length;
	if (r>0) {
		var nid = '';
		var ct = $('#dtltemplate').val();
		$('#tblDetail tbody:last').append('<tr>'+ct+'</tr>');
		$('#tblDetail tr').eq(-1).find('[id*=\"KABotForm_\"]').each(function(index) {
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
			if (id.indexOf('_info_text') != -1) $(this).val('');
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
        'sign_date,.info_date',
    );
    if($model->scenario=='new'){
        $dateList[]="apply_date";
    }
    $js = Script::genDatePicker($dateList);
    Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


