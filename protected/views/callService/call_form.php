<?php
$modelClass = get_class($model);
?>

<?php $this->renderPartial('//callService/call_form_A',array("model"=>$model,"form"=>$form)); ?>
<?php $this->renderPartial('//callService/call_form_B',array("model"=>$model,"form"=>$form)); ?>

<?php
switch(Yii::app()->language) {
    case 'zh_cn': $lang = 'zh-CN'; break;
    case 'zh_tw': $lang = 'zh-TW'; break;
    default: $lang = Yii::app()->language;
}
$disabled = $model->isReadonly()? 'true':'false';
$submitUrl = Yii::app()->createUrl('callService/ajaxAddStoreShow');
$js = <<<EOF
$('#busine_id').change(function(){
    var serialize="";
    var store_ids=$("#store_ids").val();
    store_ids = store_ids==""?$("#store_ids").data('old'):store_ids;
    serialize+="CallServiceForm[cont_id]="+$("#cont_id").val();
    serialize+="&CallServiceForm[store_ids]="+store_ids;
    serialize+="&CallServiceForm[busine_id]="+$("#busine_id").val();
    $.ajax({
        type: "POST", // 请求类型
        url: "{$submitUrl}", // 服务器端点URL
        data: serialize, // 发送到服务器的数据
        dataType: "JSON", // 
        success: function(response) {
            // 请求成功时的回调函数
            if(response.status==1){
                $('#store-div').html(response.html);
                resetAllCall(response);
            }else{
                showFormErrorHtml(response.error);
            }
        },
        error: function(xhr, status, error) {
            // 请求失败时的回调函数
            showFormErrorHtml(error);
        }
    });
});
$('.change_free_num').on('change keyup',function(){
    var num = $(this).val();
    if(num==''){
        $(this).parents('.row:first').removeClass('active');
    }else{
        $(this).parents('.row:first').addClass('active');
    }
});
$('#store-div').on('click','.removeTr',function(){
    $(this).parents('tr:first').remove();
    resetAllCall('aa');
});
$('.change_free_num').on('change keyup',resetAllCall);

function resetAllCall(req){
    var freSum=0;//总次数
    var freAmt=0;//总金额
    var store_ids=[];//被选中的门店
    $('.change_free_num').each(function(){
        var num = $(this).val();
        num=num==""?0:parseInt(num,10);
        freSum+=num;
    });
    $('.changeVir').each(function(){
        store_ids.push($(this).data('store'));
        var unitPrice = $(this).children(".unitPrice:first").text();
        unitPrice=unitPrice==""?0:parseFloat(unitPrice);
        unitPrice*=freSum;
        freAmt+=unitPrice;
        $(this).children(".totalPrice:first").text(unitPrice);
    });
    store_ids = store_ids.join(',');
    $('#store_ids').val(store_ids);
    var oldCallText = "";
    $('.change_free_num').each(function(){
        if($(this).val()!=''){
            var codeList=[];
            var freeMonthStr=$(this).data('month');
            $('.callText[data-code!=0]').each(function(){
                var store_code = $(this).data('code');
                var month_str = $(this).data('month');
                if (month_str.includes(freeMonthStr)){
                    codeList.push(store_code);
                }
            });
            if(codeList.length!=0){
                codeList = codeList.join('、');
                freeMonthStr = freeMonthStr.split('/');
                oldCallText+=oldCallText==""?"":";";
                oldCallText+=freeMonthStr[0]+"年"+freeMonthStr[1]+"月:"+codeList;
            }
        }
    });
    if(oldCallText!=""){
        $('#oldStoreCode').text(oldCallText);    
        $('#oldStoreCode').parent('div').show();  
    }else{
        $('#oldStoreCode').parent('div').hide();  
    }
    $('#storeSelect').text($('.changeVir').length);
    $("#call_amt").val(freAmt);
}

    resetAllCall('aa');
EOF;
Yii::app()->clientScript->registerScript('select2_1',$js,CClientScript::POS_READY);
if($model->isReadonly()===false){
    $js = <<<EOF
	
EOF;
    Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);
    $js = <<<EOF
$('.addStore').click(function(e){
    var serialize="";
    serialize+="CallServiceForm[cont_id]="+$("#cont_id").val();
    serialize+="&CallServiceForm[store_ids]="+$("#store_ids").val();
    serialize+="&CallServiceForm[busine_id]="+$("#busine_id").val();
    $(this).data("serialize",serialize);
});
EOF;
    Yii::app()->clientScript->registerScript('addRow',$js,CClientScript::POS_READY);
}
?>
