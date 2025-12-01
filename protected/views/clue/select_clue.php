<?php
$formType=!isset($formType)?"clue":$formType;
$content = '<div class="form-horizontal">';
$content.= '<div class="form-group">';
$content.= Tbhtml::label(Yii::t('clue','city manger'),'',array('class'=>"col-lg-4 control-label"));
$content.= '<div class="col-lg-4">';
$content.= Tbhtml::dropDownList("dialog_city", Yii::app()->user->city(),CGetName::getCityListWithCityAllow(Yii::app()->user->city_allow()),
    array('id'=>"dialog_city")
);
$content.= '</div></div>';
$content.= '<div class="form-group">';
$labelName = $formType=="clue"?Yii::t('clue','clue type'):Yii::t('clue','client type');
$content.= Tbhtml::label($labelName,'',array('class'=>"col-lg-4 control-label"));
$content.= '<div class="col-lg-4">';
$clueTypeList = isset($allBool)&&$allBool?CGetName::getAllClueTypeList():CGetName::getFunClueTypeList();
$content.= Tbhtml::dropDownList("dialog_clue_type",'1', $clueTypeList,
    array('id'=>"dialog_clue_type")
);
$content.= '</div></div>';
$content.= '</div>';
$this->widget('bootstrap.widgets.TbModal', array(
    'id'=>'clueDialog',
    'header'=>$formType=="clue"?Yii::t('clue','New Clue'):Yii::t('clue','New Client'),
    'content'=>$content,
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','OK'), array('data-dismiss'=>'modal','id'=>'dialogBtnOk','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
    ),
    'show'=>false,
));

$js="
    $('#dialogBtnOk').on('click',function(){
        var url = '{$actionUrl}';
        var elm=$('#dialogBtnOk');
        url+='?city='+$('#dialog_city').val()+'&clue_type='+$('#dialog_clue_type').val();
        window.location.href=url;
    });
$('#dialog_city').select2({
    dropdownParent: $('#clueDialog'),
    multiple: false,
    maximumInputLength: 10,
    language: 'zh-CN'
});
";
Yii::app()->clientScript->registerScript('addCityRecordBtn',$js,CClientScript::POS_READY);
?>

