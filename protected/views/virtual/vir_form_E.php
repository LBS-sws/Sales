<?php
//甲方信息
$modelClass = get_class($model);
?>

<div class="box box-info">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong>销售信息</strong>
            </h4>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('sales_id'),"sales_id",array('class'=>"col-lg-1 control-label")); ?>

            <div class="col-lg-3">
                <?php
                echo TbHtml::textField("sales_id",CGetName::getEmployeeNameByKey($model->sales_id),array(
                    'readonly'=>true,'id'=>'sales_id'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('yewudalei'),"yewudalei",array('class'=>"col-lg-1 control-label")); ?>
            <div class="col-lg-3">
                <?php
                echo TbHtml::textField("yewudalei",CGetName::getYewudaleiStrByKey($model->yewudalei),array(
                    'readonly'=>true,'id'=>'yewudalei'
                ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('other_sales_id'),"other_sales_id",array('class'=>"col-lg-1 control-label")); ?>

            <div class="col-lg-3">
                <?php
                echo TbHtml::textField("other_sales_id",CGetName::getEmployeeNameByKey($model->other_sales_id),array(
                    'readonly'=>true,'id'=>'other_sales_id'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('other_yewudalei'),"other_yewudalei",array('class'=>"col-lg-1 control-label")); ?>
            <div class="col-lg-3">
                <?php
                echo TbHtml::textField("other_yewudalei",CGetName::getYewudaleiStrByKey($model->other_yewudalei),array(
                    'readonly'=>true,'id'=>'other_yewudalei'
                ));
                ?>
            </div>
        </div>
    </div>
</div>