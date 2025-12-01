<?php
//甲方信息
$modelClass = get_class($model);
?>

<div class="box box-info">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong>服务安排</strong>
            </h4>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('store_name'),"store_name",array('class'=>"col-lg-1 control-label")); ?>

            <div class="col-lg-3">
                <?php
                echo TbHtml::textField("store_name",$model->storeHeadRow['store_name'],array(
                    'readonly'=>true,'id'=>'store_name'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('store_code'),"store_code",array('class'=>"col-lg-1 control-label")); ?>
            <div class="col-lg-3">
                <?php
                echo TbHtml::textField("store_code",$model->storeHeadRow['store_code'],array(
                    'readonly'=>true,'id'=>'store_code'
                ));
                ?>
            </div>
            <div class="col-lg-4">
                <p class="form-control-static">
                    <?php
                    $goUrl = Yii::app()->createUrl('clueStore/detail',array('index'=>$model->storeHeadRow['id']));
                    echo TbHtml::link("查看门店",$goUrl,array(
                        "target"=>'_blank'
                    ));
                    ?>
                </p>
            </div>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('service_main'),"service_main",array('class'=>"col-lg-1 control-label")); ?>

            <div class="col-lg-3">
                <?php
                echo TbHtml::textField("service_main",CGetName::getLbsMainNameByKey($model->service_main),array(
                    'readonly'=>true,'id'=>'service_main'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('first_date'),"first_date",array('class'=>"col-lg-1 control-label")); ?>

            <div class="col-lg-3">
                <?php
                echo TbHtml::textField("first_date",$model->first_date,array(
                    'readonly'=>true,'id'=>'first_date'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('fast_date'),"fast_date",array('class'=>"col-lg-1 control-label")); ?>

            <div class="col-lg-3">
                <?php
                echo TbHtml::textField("fast_date",$model->first_date,array(
                    'readonly'=>true,'id'=>'fast_date'
                ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('u_service_json'),"u_service_json",array('class'=>"col-lg-1 control-label")); ?>

            <div class="col-lg-8">
                <?php
                $text = isset($model->u_service_json["title"])?$model->u_service_json["title"]:"";
                echo TbHtml::textArea("u_service_json",$text,array(
                    'readonly'=>true,'rows'=>3
                ));
                ?>
            </div>
        </div>
    </div>
</div>