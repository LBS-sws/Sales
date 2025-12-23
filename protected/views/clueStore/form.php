<?php
$this->pageTitle=Yii::app()->name . ' - Clue Store Form';
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

    @media (min-width: 1200px){
        .col-lg-right{ padding-right: 0px;}
        .col-lg-left{ padding-left: 0px;}
    }
</style>
<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('clue','store form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php
        $type = CGetName::getSessionByStore();
        switch ($type){
            case 1:
                echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('clueStore/storeList',array("clue_id"=>$model->clue_id))));
                break;
            case 2:
                if(empty($model->id)){
                    echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                        'submit'=>Yii::app()->createUrl('clueStore/index')));
                }else{
                    echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                        'submit'=>Yii::app()->createUrl('clueStore/detail',array("index"=>$model->id))));
                }
                break;
            case 4://客户列表
                echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('clueStore/storeList',array("clue_id"=>$model->clue_id))));
                break;
            case 5://客户详情
                echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('clientHead/view',array("index"=>$model->clue_id))));
                break;
            default:
                echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('clueHead/view',array("index"=>$model->clue_id))));
        }
		?>
<?php if ($model->scenario!='view'): ?>
            <?php
            if ($model->scenario!='new') {
                echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
                    'name'=>'btnAdd','id'=>'btnAdd','submit'=>Yii::app()->createUrl('clueStore/new',array("clue_id"=>$model->clue_id,"type"=>$type))));
            }
            ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('clueStore/save',array("type"=>$type))));
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
                    <?php if ($model->scenario=='new'): ?>
                        <?php
                        echo TbHtml::button('快捷操作-填入线索信息', array('color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                            'submit'=>Yii::app()->createUrl('clueStore/new',array("clue_id"=>$model->clue_id,"type"=>$type,'fast'=>1))));
                        ?>
                    <?php endif ?>
                    <?php if ($model->scenario!='new'): ?>
                        <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('clue','Clue Store History'), array(
                                'data-toggle'=>'modal','data-target'=>'#clueHistoryDialog',)
                        );
                        ?>
                    <?php endif ?>
                </div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
            <?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'clue_id'); ?>

            <?php $this->renderPartial('//clueStore/storeForm',array("model"=>$model,"form"=>$form)); ?>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//clue/historylist',array("model"=>$model,"type"=>2)); ?>
<?php
$js = Script::genDeleteData(Yii::app()->createUrl('clueStore/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$link3 = Yii::app()->createAbsoluteUrl("clueHead/getcusttypelist");
$ajaxArea = Yii::app()->createAbsoluteUrl("clueHead/ajaxArea");
$ajaxYewudalei = Yii::app()->createAbsoluteUrl("clueHead/ajaxYewudalei");
$disable = $model->isReadOnly()?"true":"false";
$ajaxEmployee = Yii::app()->createUrl('clueStore/searchAssignEmployee');
$js = <<<EOF
$('#cust_class_group').on('change',function() {
	var group = $(this).val();
	var data = "group="+group;
	$.ajax({
		type: 'GET',
		url: '$link3',
		data: data,
		success: function(data) {
			$('#cust_class').html(data);
		},
		error: function(data) { // if error occured
			var x = 1;
		},
		dataType:'html'
	});
});	
    $('#invoice_id').on('change',function(){
        if($(this).val()==''){
            //$('#invoice_header').parent('div').prev('label').children('span').remove();
            $('#invoice_header').val('').prop('readonly',false);
            $('#tax_id').val('').prop('readonly',false);
            $('#invoice_address').val('').prop('readonly',false);
            $('#invoice_number').val('').prop('readonly',false);
            $('#invoice_user').val('').prop('readonly',false);
            $('#invoice_type input').prop('checked',false).prop('disabled',false);
            $('#invoice_type input[value=""]').trigger('click');
        }else{
            var optionObj = $(this).find('option:selected');
            var invoice_type = optionObj.data('invoice_type');
            $('#invoice_header').val(optionObj.data('invoice_header')).prop('readonly',true);
            $('#tax_id').val(optionObj.data('tax_id')).prop('readonly',true);
            $('#invoice_address').val(optionObj.data('invoice_address')).prop('readonly',true);
            $('#invoice_number').val(optionObj.data('invoice_number')).prop('readonly',true);
            $('#invoice_user').val(optionObj.data('invoice_user')).prop('readonly',true);
            $('#invoice_type input').prop('checked',false).prop('disabled',true);
            $('#invoice_type input[value="'+invoice_type+'"]').prop('checked',true).trigger('click');
        }
    });
    $('#citySelect').select2({
        multiple: false,
        maximumInputLength: 10,
        disabled: {$disable}
    });
    $('#create_staff').select2({
        multiple: false,
        maximumInputLength: 10,
        disabled: {$disable},
        language: 'zh-CN',
        ajax: {
            url: '{$ajaxEmployee}',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    keyword: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            },
            cache: true
        },
        placeholder: '请输入员工姓名或编号搜索'
    });
    $('#district').change(function(){
        $('#address').val($(this).val());
    });
    $('#citySelect').change(function(){
        $.ajax({
            type: 'GET',
            url: '{$ajaxArea}',
            data: "city="+$('#citySelect').val(),
            success: function(data) {
                var officeObj = $(data['officeObj']);
                $('#cityOffice').html(officeObj.html());
            },
            error: function(data) { // if error occured
                var x = 1;
            },
            dataType:'json'
        });
    });

$('#create_staff').change(function(){
	if($('#yewudalei').prop('tagName')=='SELECT'){
        var url = '{$ajaxYewudalei}?employee_id='+$(this).val();
        $.ajax({
            type: 'GET',
            url: url,
            success: function(data) {
                    $('#yewudalei').html(data);
            },
            error: function(data) { // if error occured
                var x = 1;
            },
            dataType:'html'
        });
	}
});

    $('#invoice_type input').click(function(){
        $('#invoice_header').parent('div').prev('label').children('span').remove();
        $('#tax_id').parent('div').prev('label').children('span').remove();
        //$('#invoice_address').parent('div').prev('label').children('span').remove();
        //$('#invoice_number').parent('div').prev('label').children('span').remove();
        //$('#invoice_user').parent('div').prev('label').children('span').remove();
        var invoice_type = $(this).val();
        switch(invoice_type){
            case '':
                $('#invoice_header').prop('readonly',true);
                $('#tax_id').prop('readonly',true);
                $('#invoice_address').prop('readonly',true);
                $('#invoice_number').prop('readonly',true);
                $('#invoice_user').prop('readonly',true);
                break;
            case '1':
                $('#invoice_header').parent('div').prev('label').append('<span class="required">*</span>');
                $('#tax_id').parent('div').prev('label').append('<span class="required">*</span>');
                break;
            case '2':
                $('#invoice_header').parent('div').prev('label').append('<span class="required">*</span>');
                $('#tax_id').parent('div').prev('label').append('<span class="required">*</span>');
                //$('#invoice_address').parent('div').prev('label').append('<span class="required">*</span>');
                //$('#invoice_number').parent('div').prev('label').append('<span class="required">*</span>');
                //$('#invoice_user').parent('div').prev('label').append('<span class="required">*</span>');
                break;
            case '3':
                $('#invoice_header').parent('div').prev('label').append('<span class="required">*</span>');
        }
        if(!$(this).is(':disabled')&&(invoice_type==1||invoice_type==2||invoice_type==3)){
            $('#invoice_header').prop('readonly',false);
            $('#tax_id').prop('readonly',false);
            $('#invoice_address').prop('readonly',false);
            $('#invoice_number').prop('readonly',false);
            $('#invoice_user').prop('readonly',false);
        }
    });
EOF;
Yii::app()->clientScript->registerScript('select2_1',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

<?php
$this->renderPartial('//clue/map_baidu',array(
    "model"=>$model,
));
?>
<?php
$this->renderPartial('//clue/nationalArea');
?>
