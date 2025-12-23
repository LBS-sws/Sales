<?php
$content = '<div class="form-horizontal">';
$content.= '<div class="form-group">';
$content.= Tbhtml::label(Yii::t('clue','assign type'),'',array('class'=>"col-lg-4 control-label"));
$content.= '<div class="col-lg-4">';
$content.= Tbhtml::dropDownList("assign_type", '1', CGetName::getAssignTypeList(),
    array('id'=>"assign_type")
);
$content.= '</div></div>';
$content.= '<div class="form-group">';
$content.= Tbhtml::label(Yii::t('clue','rec employee'),'',array('class'=>"col-lg-4 control-label"));
$content.= '<div class="col-lg-6">';
$content.= Tbhtml::dropDownList("assign_employee",'', array(),
    array('id'=>"assign_employee",)
);

$content.= '</div></div>';
$content.= '<div class="form-group" style="display: none;">';
$content.= Tbhtml::label(Yii::t('clue','rec city'),'',array('class'=>"col-lg-4 control-label"));
$content.= '<div class="col-lg-6">';
$content.= Tbhtml::dropDownList("assign_city",$assignCity, CGetName::getAssignCityList(),
    array('id'=>"assign_city")
);
$content.= '</div></div>';
$content.= '</div>';
$this->widget('bootstrap.widgets.TbModal', array(
    'id'=>'clueAssignDialog',
    'header'=>Yii::t('clue','clue assign'),
    'content'=>$content,
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','OK'), array('data-dismiss'=>'modal','id'=>'dialogAssignBtnOk','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
    ),
    'show'=>false,
));

$ajaxEmployee = Yii::app()->createUrl('clueStore/searchAssignEmployee');
$js="
$('#assign_type').change(function(){
    var assign_type=$(this).val();
    assign_type = parseInt(assign_type,10);
    $('#assign_employee').parents('.form-group').eq(0).hide();
    $('#assign_city').parents('.form-group').eq(0).hide();
    switch(assign_type){
        case 1://员工
            $('#assign_employee').parents('.form-group').eq(0).show();
            break;
        case 2://城市
            $('#assign_city').parents('.form-group').eq(0).show();
            break;
        case 3://抢单
            $('#assign_city').parents('.form-group').eq(0).show();
            break;
    }
});

    $('#dialogAssignBtnOk').on('click',function(){
        var url = '{$actionUrl}';
        var assign_id = '';
        var elm=$('#dialogAssignBtnOk');
        $('.select_check').each(function(){
            assign_id+=assign_id==''?'':',';
            assign_id+=$(this).val();
        });
        url+='?assign_type='+$('#assign_type').val()+'&assign_employee='+$('#assign_employee').val();
        url+='&assign_city='+$('#assign_city').val();
        url+='&assign_id='+assign_id;
        window.location.href=url;
    });
    
$('#assign_employee').select2({
    dropdownParent: $('#clueAssignDialog'),
    multiple: false,
    maximumInputLength: 10,
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

$('#assign_city').select2({
    dropdownParent: $('#clueAssignDialog'),
    multiple: false,
    maximumInputLength: 10,
    language: 'zh-CN'
});
";
Yii::app()->clientScript->registerScript('clueAssignBtn',$js,CClientScript::POS_READY);
?>

