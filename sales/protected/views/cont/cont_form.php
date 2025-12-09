<?php
$modelClass = get_class($model);
?>

<?php $this->renderPartial('//cont/cont_form_A',array("model"=>$model,"form"=>$form)); ?>
<?php $this->renderPartial('//cont/cont_form_B',array("model"=>$model,"form"=>$form)); ?>
<?php $this->renderPartial('//cont/cont_form_C',array("model"=>$model,"form"=>$form)); ?>
<?php $this->renderPartial('//cont/cont_form_D',array("model"=>$model,"form"=>$form)); ?>
<?php $this->renderPartial('//cont/cont_form_E',array("model"=>$model,"form"=>$form)); ?>
<?php $this->renderPartial('//cont/cont_form_F',array("model"=>$model,"form"=>$form)); ?>
<?php $this->renderPartial('//cont/cont_form_G',array("model"=>$model,"form"=>$form)); ?>

<?php
$this->renderPartial("//lookFile/lookFileDialog");
?>
<?php
switch(Yii::app()->language) {
    case 'zh_cn': $lang = 'zh-CN'; break;
    case 'zh_tw': $lang = 'zh-TW'; break;
    default: $lang = Yii::app()->language;
}
$disabled = $model->isReadonly()? 'true':'false';
$js = <<<EOF
$('.lookFile').on('click',function(){
    var id = $(this).data('id');
});
$('.information-header').on('click',function(){
    var spanObj = $(this).find('span.text-info');
    if(spanObj.length>=1){
        if(spanObj.hasClass('fa-angle-right')){
            spanObj.removeClass('fa-angle-right').addClass('fa-angle-left');
            $(this).parent('.box-body').children('.information-hide').stop().slideDown("fast");
        }else{
            spanObj.removeClass('fa-angle-left').addClass('fa-angle-right');
            $(this).parent('.box-body').children('.information-hide').stop().slideUp("fast");
        }
    }
});
$('#other_sales_id').select2({
    multiple: false,
    maximumInputLength: 10,
	language: '$lang',
	disabled: {$disabled}
});
$('#busine_id').select2({
	tags: false,
	multiple: true,
	maximumInputLength: 0,
	maximumSelectionLength: 10,
	allowClear: true,
	language: '$lang',
	disabled: true,
	templateSelection: formatState
});
$('#service_type_select').select2({
	tags: false,
	multiple: true,
	maximumInputLength: 0,
	maximumSelectionLength: 10,
	allowClear: true,
	language: '$lang',
	disabled: true,
	templateSelection: formatState
});
EOF;
Yii::app()->clientScript->registerScript('select2_1',$js,CClientScript::POS_READY);
$ClueSSEFormStr = empty($model->id)?"ClueSSEForm":"ContSSEForm";
if($model->isReadonly()===false){
    $ajaxYewudalei = Yii::app()->createAbsoluteUrl("clueHead/ajaxYewudalei");
    $ajaxAddDate = Yii::app()->createAbsoluteUrl("clueHead/ajaxAddDate");
    $js = <<<EOF
	
$('table').on('change','[id^="{$modelClass}"]',function() {
	var n=$(this).attr('id').split('_');
	$('#{$modelClass}_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});
$('#{$modelClass}_area_bool').on('change','input',function() {
    if($(this).val()=='Y'){
        $('#areaJsonDiv').removeClass('hide');
    }else{
        $('#areaJsonDiv').addClass('hide');
    }
});
$('.win_sse_store').click(function(){
    if($(this).next('tr.win_sse_form').hasClass('active')){
        $(this).next('tr.win_sse_form').removeClass('active');
    }else{
        $(this).next('tr.win_sse_form').addClass('active');
    }
});
$('form:first').submit(function(){
    var obj = {};
    $(".win_sse_form").each(function(){
        var clue_store_id = $(this).data('id');
        //var sse_id = $(this).data('sse_id');
        var sseObj = {};
        $(this).find('*[name*="{$ClueSSEFormStr}[service]"]').each(function(){
            var keyStr = $(this).attr('name');
            var keyVal = $(this).val();
            keyStr = keyStr.replace('{$ClueSSEFormStr}[service][', '');
            keyStr = keyStr.replace(']', '');
            if($(this).attr('type')=='checkbox'){
                if(!$(this).is(':checked')){
                    keyVal='N';
                }
            }
            sseObj[keyStr]=keyVal;
        });
        obj[clue_store_id]={};
        //obj[clue_store_id]['s_id']=sse_id;
        obj[clue_store_id]['detail']=sseObj;
    });
    var jsonText = JSON.stringify(obj);
    $('#serviceJson').val(jsonText);
});
EOF;
    $js.= Script::genDatePicker(array(
        'sign_date',
        'cont_start_dt',
        'cont_end_dt',
    ));
    Yii::app()->clientScript->registerScript('changeFunction',$js,CClientScript::POS_READY);
    $js = <<<EOF
$('table').on('click','.table_del', function() {
	$(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
	$(this).closest('tr').hide();
});
$('#is_seal input').change(function(){
    if($(this).val()=="Y"){
        $('#seal_type_id').removeClass('readonly').removeAttr('readonly');
    }else{
        $('#seal_type_id').val('').addClass('readonly').attr('readonly','readonly');
    }
});
$('#fee_type').change(function(){
    if($(this).val()==1){
        $('#pay_month').removeClass('readonly').removeAttr('readonly');
        $('#pay_start').removeClass('readonly').removeAttr('readonly');
    }else{
        $('#pay_month').val('').addClass('readonly').attr('readonly','readonly');
        $('#pay_start').val('').addClass('readonly').attr('readonly','readonly');
    }
});

$('#other_sales_id').change(function(){
	if($('#other_yewudalei').prop('tagName')=='SELECT'){
        var url = '{$ajaxYewudalei}?employee_id='+$(this).val();
        $.ajax({
            type: 'GET',
            url: url,
            success: function(data) {
                    $('#other_yewudalei').html(data);
            },
            error: function(data) { // if error occured
                var x = 1;
            },
            dataType:'html'
        });
	}else{
	    $('#other_yewudalei').val($('#yewudalei').val());
	    if($('#other_sales_id').val()==''){
	        $('#other_yewudalei_name').val('');
	    }else{
	        $('#other_yewudalei_name').val($('#yewudalei_name').val());
	    }
	}
});

$('#sign_date').change(function(){
    $('#cont_start_dt').datepicker("setDate",$(this).val()).trigger('change');
});

$('#cont_start_dt').change(function(){
    $.ajax({
        type: 'post',
        url: '{$ajaxAddDate}',
        data: {'date':$(this).val()},
        success: function(data) {
            if(data.state==1){
                $('#cont_end_dt').datepicker("setDate",data.endDate).trigger('change');
            }
        },
        error: function(data) { // if error occured
            var x = 1;
        },
        dataType:'json'
    });
});
EOF;
    Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);
    $js = <<<EOF
$('table').on('change','.fileVal',function() {
    var fileInput = $(this);
    var filename = fileInput.val();
    var pos = filename.lastIndexOf("\\\\")+1;
    filename = filename.substring(pos, filename.length);
    //验证文件
    if(this.files[0].size>{$model->docMaxSize}){
        showFormErrorHtml("文件大小不能超过10M");
        $(this).val('');
        return false;
    }
    
    var pos = filename.lastIndexOf(".");
    var str = filename.substring(pos, filename.length);
    var str1 = str.toLowerCase();
    var fileType = "jpg|jpeg|png|xlsx|pdf|docx|txt";
    var re = new RegExp("\.(" + fileType + ")$");
    if (!re.test(str1)) {
        showFormErrorHtml("文件格式不正确，只能上传格式为：" + fileType + "的文件。");
        $(this).val('');
        return false;
    }else{
        $(this).parents('tr:first').find('.fileName').val(filename);
    }
});
$('table').on('click','.table_add',function() {
	var r = $(this).data('num');
	if (r>=0) {
	    r++;
	    $(this).data('num',r);
		var nid = '';
		var ct = $(this).next('input').val();
		$(this).parents('thead').eq(0).next('tbody').append(ct);
		$(this).parents('table').eq(0).find('tbody>tr').eq(-1).find('[id*=\"{$modelClass}_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);
		});
	}
});
EOF;
    Yii::app()->clientScript->registerScript('addRow',$js,CClientScript::POS_READY);
}
?>
