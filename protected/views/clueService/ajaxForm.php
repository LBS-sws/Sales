
<?php echo TbHtml::hiddenField("ClueServiceForm[id]",$model->id,array('id'=>"win_clue_service_id"));?>
<?php echo TbHtml::hiddenField("ClueServiceForm[scenario]",$model->scenario);?>
<?php echo TbHtml::hiddenField("ClueServiceForm[clue_id]",$model->clue_id);?>
<div class="form-group">
    <?php
    echo Tbhtml::label(Yii::t('clue','visit type'),'win_visit_type',array('class'=>"col-lg-2 control-label",'required'=>true));
    ?>
    <div class="col-lg-4">
        <?php
        echo Tbhtml::dropDownList("ClueServiceForm[visit_type]",'',CGetName::getVisitTypeList(),
            array('id'=>"win_visit_type")
        );
        ?>
    </div>
</div>
<div class="form-group">
    <?php
    echo Tbhtml::label(Yii::t('clue','service obj'),'busine_id_service',array('class'=>"col-lg-2 control-label",'required'=>true));
    ?>
    <div class="col-lg-4">
        <?php
        echo Tbhtml::dropDownList("ClueServiceForm[busine_id][]",'', CGetName::getServiceDefList(),
            array('id'=>"busine_id_service",'multiple'=>'multiple')
        );
        ?>
    </div>
</div>

<script>
<?php
$js="
$('#busine_id_service').select2({
	tags: false,
	multiple: true,
	maximumInputLength: 0,
	maximumSelectionLength: 10,
	allowClear: true,
	language: 'zh-CN',
	disabled: false,
	templateSelection: function(state) {
        var rtn = $('<span style=\"color:black\">'+state.text+'</span>');
        return rtn;
    } 
});

$('#busine_id_service').on('change',function(){
    if($(this).val()=='G'){
        $('#win_visit_obj').append('<option value=\"10\" >签单</option>');
    }else{
        $('#win_visit_obj').children('option[value=\"10\"]').remove();
    }
});
";
echo $js;
?>
</script>

<?php
// 按照旧逻辑：直接检查 clue_type，如果为 1 则显示跟进表单
if($model->clue_type==1){
    $flowModel = new ClueFlowForm("new");
    $flowModel->clue_id=$model->clue_id;
    $flowModel->clue_type=1;
    $flowModel->visit_date=date("Y/m/d");
    $flowModel->clueHeadRow=$model->clueHeadRow;
    $this->renderPartial('//clueFlow/ajaxForm',array("model"=>$flowModel,"showStoreBool"=>false));
}
?>
