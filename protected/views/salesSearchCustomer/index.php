<?php
$this->pageTitle=Yii::app()->name . ' - Customer Enquiry';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'salesSearchCustomer',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
//'layout'=>TbHtml::FORM_LAYOUT_INLINE,
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('sales','LBS Customer Enquiry'); ?></strong>
	</h1>
</section>


<section class="content">
	<div class="box">
        <div class="box-body">
		<div class="form-group">
            <?php echo $form->labelEx($model,'company_name',array('class'=>"col-sm-2 control-label")); ?>
            <div class="col-sm-5">
                <div class="input-group">
                    <div class="btn-group" style="width: 100%">
                        <?php echo $form->textField($model, 'company_name', array('maxlength'=>250,'id'=>'company_name','autocomplete'=>'off')); ?>
                        <ul class="dropdown-menu" id="company_name_menu" style="width: 100%">
                        </ul>
                    </div>
                    <span class="input-group-btn">
                        <?php
                        echo TbHtml::button('dummyButton', array('style'=>'display:none','disabled'=>true,'submit'=>'#',));
                        echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Search'), array(
                            'id'=>'btnSubmit',
                        ));
                        ?>
                    </span>
                </div><!-- /input-group -->
            </div>
		</div>
		<div class="btn-group" role="group">
		</div>
	</div></div>

	<?php 
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('customer','Customer List'),
			'model'=>$model,
				'viewhdr'=>'//salesSearchCustomer/_listhdr',
				'viewdtl'=>'//salesSearchCustomer/_listdtl',
				'hasSearchBar'=>false,
		));
	?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
?>
<?php $this->endWidget(); ?>

<?php
switch(Yii::app()->language) {
	case 'zh_cn': $lang = 'zh-CN'; break;
	case 'zh_tw': $lang = 'zh-TW'; break;
	default: $lang = Yii::app()->language;
}
//$disabled = (!$model->isReadOnly()) ? 'false' : 'true';
$link3 = Yii::app()->createAbsoluteUrl("salesSearchCustomer/ajaxCompanyName");
	$js = <<<EOF
$('#SalesSearchCustomerList_city_list').select2({
	tags: false,
	multiple: true,
	maximumInputLength: 0,
	maximumSelectionLength: 200,
	allowClear: true,
	language: '$lang',
	disabled: false
});
function changeCompanyName(){
    var that = $(this);
    $(this).parent('.btn-group').addClass('open');
    $(this).next('.dropdown-menu').html('<li><a>查询中...</span></li>');
	var data = "group="+$(this).val();
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
$('#company_name').on('click',function(e){
    e.stopPropagation();
});
$('#company_name').on('focus',changeCompanyName);
$('#company_name').on('keyup',changeCompanyName);
$('body').on('click',function(){
    $('#company_name').parent('.btn-group').removeClass('open');
});
$('#company_name_menu').delegate('.clickThis','click',function(){
    $('#company_name').val($(this).text());
    $('form:first').submit();
});

$('#SalesSearchCustomerList_city_list').on('select2:opening select2:closing', function( event ) {
    var searchfield = $(this).parent().find('.select2-search__field');
    searchfield.prop('disabled', true);
});
EOF;
Yii::app()->clientScript->registerScript('select2',$js,CClientScript::POS_READY);

$js = <<<EOF
function showdetail(id) {
	var icon = $('#btn_'+id).attr('class');
	if (icon.indexOf('plus') >= 0) {
		$('.detail_'+id).show();
		$('#btn_'+id).attr('class', 'fa fa-minus-square');
	} else {
		$('.detail_'+id).hide();
		$('#btn_'+id).attr('class', 'fa fa-plus-square');
	}
}
EOF;
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_HEAD);

$url = Yii::app()->createUrl('salesSearchCustomer/index', array('pageNum'=>1));
$js = <<<EOF
$('#btnSubmit').on('click', function() {
	Loading.show();
	jQuery.yii.submitForm(this,'$url',{});
});
EOF;
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);

//$js = Script::genTableRowClick();
//Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>

