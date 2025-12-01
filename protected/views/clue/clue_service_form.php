<?php
$content = '<div class="form-horizontal">';
$content.= TbHtml::hiddenField("ClueServiceForm[clue_id]",$model->id);
$content.= '<div class="form-group">';
$content.= Tbhtml::label(Yii::t('clue','visit type'),'',array('class'=>"col-lg-4 control-label",'required'=>true));
$content.= '<div class="col-lg-6">';
$content.= Tbhtml::dropDownList("ClueServiceForm[visit_type]",'',CGetName::getVisitTypeList(),
    array('id'=>"visit_type")
);
$content.= '</div></div>';
$content.= '<div class="form-group">';
$content.= Tbhtml::label(Yii::t('clue','service obj'),'',array('class'=>"col-lg-4 control-label",'required'=>true));
$content.= '<div class="col-lg-6">';
$content.= Tbhtml::dropDownList("ClueServiceForm[busine_id][]",'', CGetName::getServiceDefList(),
    array('id'=>"busine_id_service",'multiple'=>'multiple')
);
$content.= '</div></div>';
$content.= '</div>';
$this->widget('bootstrap.widgets.TbModal', array(
    'id'=>'addClueServiceDialog',
    'header'=>Yii::t('clue','add clue service'),
    'content'=>$content,
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','OK'), array('data-dismiss'=>'modal','id'=>'dialogAddClueServiceBtn','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
    ),
    'show'=>false,
));

$js="
    $('#dialogAddClueServiceBtn').on('click',function(){
        jQuery.yii.submitForm(this,'{$actionUrl}',{});
        return false;
    });
    
$('#busine_id_service').select2({
	tags: false,
	multiple: true,
	maximumInputLength: 0,
	maximumSelectionLength: 10,
	allowClear: true,
	language: 'zh-CN',
	disabled: false,
	templateSelection: formatState
});
";
Yii::app()->clientScript->registerScript('addClueServiceBtn',$js,CClientScript::POS_READY);
?>

