<?php
$modelClass = get_class($model);//vir_status
$type = $model->isReadonly()||$model->pro_type!='A'?"view":"edit";
$virModel = new VirtualProForm($type);
$bool = $virModel->retrieveDataByBatchIDAndVirID($model->id,$model->vir_id);
if(!$bool){
    Dialog::message(Yii::t('dialog','Validation Message'), "数据异常，请刷新重试");
    $this->redirect(Yii::app()->createUrl('virtualHead/index'));
}
$virModel->setContHeadRow();
?>

<div class="box box-info">
    <div class="box-body">
        <?php if ($model->pro_type=="A"): ?>
            <!--虚拟合约信息-->
            <?php $this->renderPartial('//virtualBatch/vir_form_A',array("model"=>$model,"virModel"=>$virModel,"form"=>$form)); ?>
            <!--销售信息-->
            <?php $this->renderPartial('//virtualBatch/vir_form_G',array("model"=>$model,"virModel"=>$virModel,"form"=>$form)); ?>
            <!--结算信息-->
            <?php $this->renderPartial('//virtualBatch/vir_form_B',array("model"=>$model,"virModel"=>$virModel,"form"=>$form)); ?>
            <!--服务项目-->
            <?php $this->renderPartial('//virtualBatch/vir_form_C',array("model"=>$model,"virModel"=>$virModel,"form"=>$form)); ?>
        <?php endif ?>
        <!--涉及门店-->
        <?php $this->renderPartial('//virtualBatch/vir_form_D',array("model"=>$model,"form"=>$form)); ?>

        <!--附件-->
        <?php $this->renderPartial('//virtualBatch/vir_form_F',array("model"=>$model,"form"=>$form)); ?>
    </div>
</div>

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
EOF;
Yii::app()->clientScript->registerScript('select2_1',$js,CClientScript::POS_READY);
$ClueSSEFormStr ="VirtualHeadForm";
if($model->isReadonly()===false){
    $ajaxYewudalei = Yii::app()->createAbsoluteUrl("clueHead/ajaxYewudalei");
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
        var store_id = ""+$(this).data('id');
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
        obj[store_id]={};
        obj[store_id]['s_id']=store_id;
        obj[store_id]['detail']=sseObj;
    });
    var jsonText = JSON.stringify(obj);
    $('#serviceJson').val(jsonText);
});
EOF;
    $dateList=array(
        'pro_date',
    );
    if($model->pro_type=="A"){
        $dateList[]='sign_date';
        $dateList[]='cont_start_dt';
        $dateList[]='cont_end_dt';
    }
    $js.= Script::genDatePicker($dateList);
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
	}
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
    var fileType = "jpg|jpeg|png|xlsx|pdf|docx|txt|doc|wps";
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
<?php
if ($model->pro_type=="A"&&$model->isReadonly()===false){
    $this->renderPartial("//cont/settingFreeJS");
}
?>
