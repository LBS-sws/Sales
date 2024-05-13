<?php
$shiftRow = KABotForm::getShiftRowForID($model->getThisTablePre(),$model->id);
?>

<?php if (!empty($shiftRow)&&$model->getScenario()=="view"): ?>
<legend>转移资料</legend>
<div class="form-group">
    <?php echo TbHtml::label("项目类型（转移前）",'',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php
        echo TbHtml::textField("show_tab",KABotForm::getTableStrForPre($shiftRow["shift_from_tab"]),array("readonly"=>true));
        ?>
    </div>
    <?php echo TbHtml::label("KA销售（转移前）",'',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php
        echo TbHtml::hiddenField("shift_staff",$shiftRow["shift_from_id"]);
        echo TbHtml::hiddenField("shift_staff",$shiftRow["shift_from_staff"]);
        echo TbHtml::textField("show_staff",KABotForm::getEmployeeNameForId($shiftRow["shift_from_staff"]),array("readonly"=>true));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label("项目类型（转移后）",'',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php
        echo TbHtml::textField("show_tab",KABotForm::getTableStrForPre($shiftRow["shift_to_tab"]),array("readonly"=>true));
        ?>
    </div>
    <?php echo TbHtml::label("KA销售（转移后）",'',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php
        echo TbHtml::hiddenField("shift_staff",$shiftRow["shift_to_id"]);
        echo TbHtml::hiddenField("shift_staff",$shiftRow["shift_to_staff"]);
        echo TbHtml::textField("show_staff",KABotForm::getEmployeeNameForId($shiftRow["shift_to_staff"]),array("readonly"=>true));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label("转移备注",'',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-6">
        <?php
        echo TbHtml::textArea("show_remark",$shiftRow["shift_remark"],array("readonly"=>true,"rows"=>4));
        ?>
    </div>
</div>
    <legend>&nbsp;</legend>
<?php endif ?>
