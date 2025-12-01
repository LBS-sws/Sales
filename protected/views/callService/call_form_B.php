<?php
$modelClass = get_class($model);
?>
<div class="box box-info">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong>
                    <?php
                    echo Yii::t("clue","and store");
                    ?>
                </strong>
            </h4>
        </div>
        <?php if (!$model->isReadonly()): ?>
            <div class="form-group">
                <div class="col-lg-12">
                    <?php
                    echo TbHtml::button(Yii::t("clue","clue service store"),array(
                        'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                        'data-load'=>Yii::app()->createUrl('callService/ajaxStoreShow'),
                        'data-submit'=>Yii::app()->createUrl('callService/ajaxAddStoreShow'),
                        'data-serialize'=>"",
                        'data-obj'=>"#store-div",
                        'class'=>'openDialogForm addStore',
                        'data-fun'=>"resetAllCall",
                    ));
                    ?>
                </div>
            </div>
        <?php endif ?>
        <div class="form-group">
            <div class="col-lg-12" id="store-div">
                <?php $this->renderPartial('//callService/call_form_store',array("model"=>$model)); ?>
            </div>
        </div>
    </div>
</div>
