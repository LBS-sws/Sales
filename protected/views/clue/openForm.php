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

<div style="display:none">
    <div id="clue_dv_store_dummy"></div>
    <div id="clue_dv_person_dummy"></div>
    <div id="clue_dv_invoice_dummy"></div>
    <div id="clue_dv_u_staff_dummy"></div>
    <div id="clue_dv_u_area_dummy"></div>
</div>

<style>
/* 移动端优化：确保可点击元素在触摸设备上正常工作 */
.openDialogForm {
    cursor: pointer;
    -webkit-tap-highlight-color: rgba(0,0,0,0.1);
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    touch-action: manipulation; /* 优化触摸响应 */
    position: relative; /* 确保可以正常定位 */
}
/* 确保按钮在移动端有足够的点击区域（仅对非按钮元素） */
.openDialogForm:not(button):not(.btn) {
    min-height: 44px; /* iOS推荐的最小触摸目标 */
    display: inline-block; /* 确保min-height生效 */
}
/* 确保链接在移动端有足够的点击区域 */
.openDialogForm a {
    min-height: 44px;
    display: inline-block;
    padding: 8px 12px; /* 增加内边距以提高点击区域 */
}
</style>

<?php
//$('body').on('click','#yt1',function(){
//$(this).css('pointer-events','none');
//jQuery.yii.submitForm(this,'/sales/clientHead/save',{});return false;
//});
$js = <<<'EOF'
$('#open-form-Dialog').on('shown.bs.modal',function(){
    $('.modal.fade.in').not('#open-form-Dialog').css('display','none');
});
$('#open-form-Dialog').on('hidden.bs.modal',function(){
    $('.modal.fade.in').css('display','block');
});
// 处理弹框打开的统一函数
function handleOpenDialogForm(elem, e) {
    if(!elem) {
        console.error('handleOpenDialogForm: elem is undefined');
        return false;
    }
    var $elem = $(elem);
    if($elem.length === 0) {
        console.error('handleOpenDialogForm: elem not found');
        return false;
    }
    if(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    var loadUrl = $elem.data('load');
    var submitUrl = $elem.data('submit');
    var funExpr = $elem.data('fun');
    var formData = $elem.data('serialize');
    if(formData===undefined){
        formData = $elem.attr('data-serialize');
    }
    var obj = $elem.data('obj');
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
            if(response && response.title && response.html){
                $('#open-form-Dialog .modal-title').html(response.title);
                $('#open-form-div').html(response.html);
                $('#open-form-btn-ok').data('submit',submitUrl);
                $('#open-form-btn-ok').data('obj',obj);
                if(funExpr!=''&&funExpr!=undefined){
                    $('#open-form-btn-ok').data('funExpr',funExpr);
                }
                $('#open-form-Dialog').data('ajax',0).modal('show');
            }else{
                var errorMsg = response.error || response.message || '加载表单失败';
                showFormErrorHtml(errorMsg);
                $('#open-form-Dialog').data('ajax',0);
            }
        },
        error: function(xhr, status, error) {
            // 请求失败时的回调函数
            console.error('Ajax错误:', xhr, status, error);
            console.error('Response:', xhr.responseText);
            showFormErrorHtml('加载表单失败，请重试');
            $('#open-form-Dialog').data('ajax',0);
        }
    });
    return false;
}

// 支持移动端的点击事件处理
$('body').on('touchstart','.openDialogForm',function(e){
    var $elem = $(this);
    if($elem.length === 0) {
        return false;
    }
    
    // 检查是否有触摸事件
    if(!e.originalEvent || !e.originalEvent.touches || e.originalEvent.touches.length === 0) {
        return false;
    }
    
    var touchStartTime = Date.now();
    var touchStartX = e.originalEvent.touches[0].clientX;
    var touchStartY = e.originalEvent.touches[0].clientY;
    
    // 标记这个元素已经处理了touchstart
    $elem.data('touchHandled', false);
    
    var touchendHandler = function(e2){
        $elem.off('touchend', touchendHandler);
        
        // 检查是否有触摸结束事件
        if(!e2.originalEvent || !e2.originalEvent.changedTouches || e2.originalEvent.changedTouches.length === 0) {
            return;
        }
        
        var touchEndTime = Date.now();
        var touchEndX = e2.originalEvent.changedTouches[0].clientX;
        var touchEndY = e2.originalEvent.changedTouches[0].clientY;
        var timeDiff = touchEndTime - touchStartTime;
        var xDiff = Math.abs(touchEndX - touchStartX);
        var yDiff = Math.abs(touchEndY - touchStartY);
        
        // 判断是否为点击（不是滑动），时间小于300ms，移动距离小于10px
        if(timeDiff < 300 && xDiff < 10 && yDiff < 10) {
            e2.preventDefault();
            e2.stopPropagation();
            $elem.data('touchHandled', true);
            var domElem = $elem[0];
            if(domElem) {
                handleOpenDialogForm(domElem, e2);
            }
        }
    };
    
    $elem.on('touchend', touchendHandler);
});

$('body').on('click','.openDialogForm',function(e){
    var $elem = $(this);
    // 如果touchstart已经处理了，忽略click事件（避免重复触发）
    if($elem.data('touchHandled')) {
        $elem.removeData('touchHandled');
        e.preventDefault();
        e.stopPropagation();
        return false;
    }
    return handleOpenDialogForm(this, e);
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
                // 兼容HTML返回格式（旧版）
                if(response.html && $(obj).length>0){
                    $(obj).html(response.html);
                }
                // 显示成功提示
                if(response.dialog!==false){
                    var successMsg = response.message || '保存成功!';
                    showFormErrorHtml(successMsg);
                }
                // 执行回调函数（新版优先使用）
                // 注意：这里移到了显示成功提示之后，确保即使HTML替换失败也能执行回调
                if(funExpr!=''&&funExpr!=undefined){
                    try {
                        eval(funExpr + '(response)');
                    } catch (e) {
                        console.error('回调函数执行错误:', e);
                    }
                }
                $('#open-form-Dialog').modal('hide');
            }else{
                showFormErrorHtml(response.error);
            }
        },
        error: function(xhr, status, error) {
            // 请求失败时的回调函数
            console.error('Ajax错误:', xhr, status, error);
            console.error('Response:', xhr.responseText);
            showFormErrorHtml('保存失败，请重试');
            $('#open-form-Dialog').data('ajax',0);
        }
    });
});
EOF;
Yii::app()->clientScript->registerScript('open_form',$js,CClientScript::POS_READY);
?>
