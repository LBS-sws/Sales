<?php
$className = get_class($model);
?>
<?php echo TbHtml::hiddenField("{$className}[scenario]", $model->scenario); ?>
<?php echo TbHtml::hiddenField("{$className}[id]", $model->id); ?>
<?php echo TbHtml::hiddenField("{$className}[clue_id]", $model->clue_id); ?>

<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("city_code"),'',array('class'=>"col-lg-4 control-label",'required'=>true)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::dropDownList("{$className}[city_code]",$model->city_code,CGetName::getStoreCityList(),array(
            "class"=>'form-control','id'=>'u_city_code','readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("city_type"),'',array('class'=>"col-lg-4 control-label",'required'=>true)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::dropDownList("{$className}[city_type]",$model->city_type,array(Yii::t("clue","other u area"),Yii::t("clue","local u area")),array(
            "class"=>'form-control','readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
</div>

<script>
    $('#u_city_code').select2({
        dropdownParent: $('#open-form-Dialog'),
        multiple: false,
        maximumInputLength: 10,
        disabled: false
    });
</script>