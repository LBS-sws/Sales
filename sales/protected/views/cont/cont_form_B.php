<?php
//乙方信息
$modelClass = get_class($model);
?>
<div class="box box-info">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong><?php echo Yii::t("clue","Party B Information");?></strong>
            </h4>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('lbs_main'),"lbs_main",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
            <div class="col-lg-3">
                <?php echo $form->dropDownList($model, 'lbs_main',CGetName::getLbsMainList($model->city),
                    array('readonly'=>$model->isReadonly(),'id'=>'lbs_main','empty'=>'')
                ); ?>
            </div>
        </div>
    </div>
</div>
