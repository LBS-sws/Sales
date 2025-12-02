<?php
$className = get_class($model);
?>
<?php echo TbHtml::hiddenField("{$className}[scenario]", $model->scenario); ?>
<?php echo TbHtml::hiddenField("{$className}[id]", $model->id); ?>
<?php echo TbHtml::hiddenField("{$className}[clue_id]", $model->clue_id); ?>
<?php echo TbHtml::hiddenField("{$className}[clue_store_id]", $model->clue_store_id); ?>

<?php if ($model->scenario!='new'): ?>
    <div class="form-group">
        <?php echo TbHtml::label($model->getAttributeLabel("person_code"),'',array('class'=>"col-lg-4 control-label")); ?>
        <div class="col-lg-4">
            <?php
            echo TbHtml::textField("{$className}[person_code]",$model->person_code,array(
                "class"=>'form-control','readonly'=>true
            ));
            ?>
        </div>
    </div>
<?php endif ?>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("cust_person"),'',array('class'=>"col-lg-4 control-label",'required'=>true)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[cust_person]",$model->cust_person,array(
            "class"=>'form-control','readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("sex"),'',array('class'=>"col-lg-4 control-label")); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::inlineRadioButtonList("{$className}[sex]",$model->sex,CGetName::getPersonSexList(),array(
            'readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("cust_person_role"),'',array('class'=>"col-lg-4 control-label",'required'=>true)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[cust_person_role]",$model->cust_person_role,array(
            "class"=>'form-control','readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("cust_tel"),'',array('class'=>"col-lg-4 control-label",'required'=>true)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[cust_tel]",$model->cust_tel,array(
            "class"=>'form-control','readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("cust_email"),'',array('class'=>"col-lg-4 control-label")); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[cust_email]",$model->cust_email,array(
            "class"=>'form-control','readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
</div>