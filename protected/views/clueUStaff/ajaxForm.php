<?php
$className = get_class($model);
?>
<?php echo TbHtml::hiddenField("{$className}[scenario]", $model->scenario); ?>
<?php echo TbHtml::hiddenField("{$className}[id]", $model->id); ?>
<?php echo TbHtml::hiddenField("{$className}[clue_id]", $model->clue_id); ?>

<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("employee_id"),'',array('class'=>"col-lg-4 control-label",'required'=>true)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::dropDownList("{$className}[employee_id]",$model->employee_id,CGetName::getAssignEmployeeAllList(),array(
            "class"=>'form-control','id'=>'u_employee_id','readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("employee_type"),'',array('class'=>"col-lg-4 control-label",'required'=>true)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::dropDownList("{$className}[employee_type]",$model->employee_type,array(Yii::t("clue","other u staff"),Yii::t("clue","local u staff")),array(
            "class"=>'form-control','readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
</div>

<script>
    $('#u_employee_id').select2({
        dropdownParent: $('#open-form-Dialog'),
        multiple: false,
        maximumInputLength: 10,
        disabled: false
    });
</script>