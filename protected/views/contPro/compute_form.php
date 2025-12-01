<?php if (in_array($model->pro_type,array("A","C","NA"))): ?>
    <style>
        .free-change-alert{ border-radius: 3px;margin-top: 5px;padding:5px 10px;border: 1px solid #dc0019;background: #f7ccd3;color:#dc0019;}
        .free-change-alert>.fa{ color:#dc0019;padding-right: 4px;}
    </style>
    <div class="box box-info">
        <div class="box-body">
            <div class="information-header">
                <h4>
                    <strong><?php echo Yii::t("clue","Update Info");?></strong>
                </h4>
            </div>
            <div class="form-group hide" id="freeHintDiv">
                <div class="col-lg-8 col-lg-offset-2">
                    <div class="free-change-alert">
                        <span class="fa fa-exclamation-circle"></span>
                        <?php echo Yii::t("clue","free change hint");?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-8 col-lg-offset-2">
                    <?php
                    echo $model->printCompareHtmlByAudit();
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>
