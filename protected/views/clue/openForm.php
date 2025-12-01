<?php
$form=$this->beginWidget('TbActiveForm', array(
    'id'=>'open-form',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
));
?>
<?php
// data-bs-backdrop="static" data-bs-keyboard="false"
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'open-form-Dialog',
    'header'=>"",
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal'
        )),
        TbHtml::button(Yii::t('dialog','OK'), array(
            'id'=>"open-form-btn-ok",
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY
        )),
    ),
    'keyboard'=>false,
    'backdrop'=>'static',
    'show'=>false,
    'size'=>" modal-lg",
));
?>
<div id="open-form-div">

</div>
<?php $this->endWidget(); ?>

<?php
$this->renderPartial("//clue/errorDialog");
?>
<?php $this->endWidget(); ?>

<?php
//$('body').on('click','#yt1',function(){
//$(this).css('pointer-events','none');
//jQuery.yii.submitForm(this,'/sales/clientHead/save',{});return false;
//});
$js = <<<EOF
$('#open-form-Dialog').on('shown.bs.modal',function(){
    $('.modal.fade.in').not('#open-form-Dialog').css('display','none');
});
$('#open-form-Dialog').on('hidden.bs.modal',function(){
    $('.modal.fade.in').css('display','block');
});
$('body').on('click','.openDialogForm',function(e){
    var loadUrl = $(this).data('load');
    var submitUrl = $(this).data('submit');
    var funExpr = $(this).data('fun');
    var formData = $(this).data('serialize'); 
    var obj = $(this).data('obj');
    var ajaxBool = $('#open-form-Dialog').data('ajax');
    if(ajaxBool==1){
        return false;//已经在加载了
    }else{
        $('#open-form-Dialog').data('ajax',1);
    }
    $.ajax({
        type: "POST", // 请求类型
        url: loadUrl, // 服务器端点URL
        data: formData, // 发送到服务器的数据
        dataType: "JSON", // 
        success: function(response) {
            // 请求成功时的回调函数
            $('#open-form-Dialog .modal-title').html(response.title);
            $('#open-form-div').html(response.html);
            $('#open-form-btn-ok').data('submit',submitUrl);
            $('#open-form-btn-ok').data('obj',obj);
            if(funExpr!=''&&funExpr!=undefined){
                $('#open-form-btn-ok').data('funExpr',funExpr);
            }
            $('#open-form-Dialog').data('ajax',0).modal('show');
        },
        error: function(xhr, status, error) {
            // 请求失败时的回调函数
            showFormErrorHtml(error);
            $('#open-form-Dialog').data('ajax',0);
        }
    });
});
$('#open-form-btn-ok').click(function(){
    var submitUrl = $(this).data('submit');
    var obj = $(this).data('obj');
    var funExpr = $(this).data('funExpr');
    var ajaxBool = $('#open-form-Dialog').data('ajax');
    var formData = $('#open-form').serialize(); // 序列化表单数据
    if(ajaxBool==1){
        return false;//已经在加载了
    }else{
        $('#open-form-Dialog').data('ajax',1);
    }
    $.ajax({
        type: "POST", // 请求类型
        url: submitUrl, // 服务器端点URL
        data: formData, // 发送到服务器的数据
        dataType: "JSON", // 
        success: function(response) {
            $('#open-form-Dialog').data('ajax',0);
            // 请求成功时的回调函数
            if(response.status==1){
                if($(obj).length>0){
                    $(obj).html(response.html);
                }
                if(funExpr!=''&&funExpr!=undefined){
                    eval(funExpr + '(response)');
                }
                if(response.dialog!==false){
                    showFormErrorHtml('保存成功!');
                }
                $('#open-form-Dialog').modal('hide');
            }else{
                showFormErrorHtml(response.error);
            }
        },
        error: function(xhr, status, error) {
            // 请求失败时的回调函数
            showFormErrorHtml(error);
            $('#open-form-Dialog').data('ajax',0);
        }
    });
});
EOF;
Yii::app()->clientScript->registerScript('open_form',$js,CClientScript::POS_READY);
?>
