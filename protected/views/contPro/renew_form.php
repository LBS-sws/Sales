<?php
$modelClass = get_class($model);
?>

<div class="box box-info">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong><?php echo Yii::t("clue","Pro Info");?></strong>
            </h4>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('pro_date'),"pro_date",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->textField($model,"pro_date",array(
                    'readonly'=>$model->isReadonly(),'id'=>'pro_date','autocomplete'=>'off','prepend'=>'<span class="fa fa-calendar"></span>'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('pro_type'),"pro_type",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
            <div class="col-lg-3">
                <?php
                echo $form->dropDownList($model,"pro_type",CGetName::getProTypeList(),array(
                    'readonly'=>true,'id'=>'pro_type'
                ));
                ?>
            </div>
            <?php if ($model->scenario!='new'): ?>
                <?php echo TbHtml::label($model->getAttributeLabel('pro_code'),"pro_code",array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    echo $form->textField($model,"pro_code",array(
                        'readonly'=>true,'id'=>'pro_code'
                    ));
                    ?>
                </div>
            <?php endif ?>
            <?php if ($model->pro_type=='A'): ?>
                <div class="col-lg-11 col-lg-offset-1">
                    <p class="form-control-static text-danger">
                        <?php
                        echo Yii::t("clue","pro_hint_text");
                        ?>
                    </p>
                </div>
            <?php endif ?>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('pro_remark'),"pro_remark",array('class'=>"col-lg-1 control-label")); ?>

            <div class="col-lg-7">
                <?php
                echo $form->textArea($model,"pro_remark",array(
                    'readonly'=>$model->isReadonly(),'id'=>'pro_remark','rows'=>3
                ));
                ?>
            </div>
        </div>
    </div>
</div>
