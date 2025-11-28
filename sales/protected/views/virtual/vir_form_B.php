<?php
//甲方信息
$modelClass = get_class($model);
?>

<div class="box box-info">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong>结算信息</strong>
            </h4>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('predict_amt'),"predict_amt",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo TbHtml::textField("predict_amt",$model->contHeadRow['predict_amt'],array(
                    'readonly'=>true,'id'=>'predict_amt'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('pay_week'),"pay_week",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->dropDownList($model,"pay_week",CGetName::getPayWeekList(),array(
                    'readonly'=>$model->isReadonly(),'id'=>'pay_week'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('pay_type'),"pay_type",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->dropDownList($model,"pay_type",CGetName::getPayTypeList(),array(
                    'readonly'=>$model->isReadonly(),'id'=>'pay_type'
                ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('deposit_need'),"deposit_need",array('class'=>"col-lg-1 control-label")); ?>

            <div class="col-lg-3">
                <?php
                echo $form->textField($model,"deposit_need",array(
                    'readonly'=>$model->isReadonly(),'id'=>'deposit_need'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('deposit_amt'),"deposit_amt",array('class'=>"col-lg-1 control-label col-lg-left")); ?>

            <div class="col-lg-3">
                <?php
                echo $form->textField($model,"deposit_amt",array(
                    'readonly'=>$model->isReadonly(),'id'=>'deposit_amt'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('deposit_rmk'),"deposit_rmk",array('class'=>"col-lg-1 control-label col-lg-left")); ?>

            <div class="col-lg-3">
                <?php
                echo $form->textArea($model,"deposit_rmk",array(
                    'readonly'=>$model->isReadonly(),'id'=>'deposit_rmk'
                ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('fee_type'),"fee_type",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->dropDownList($model,"fee_type",CGetName::getFeeTypeList(),array(
                    'readonly'=>$model->isReadonly(),'id'=>'fee_type'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('pay_month'),"pay_month",array('class'=>"col-lg-1 control-label col-lg-left")); ?>

            <div class="col-lg-3">
                <?php
                echo $form->textField($model,"pay_month",array(
                    'readonly'=>$model->isReadonly(),'id'=>'pay_month','append'=>'月'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('pay_start'),"pay_start",array('class'=>"col-lg-1 control-label col-lg-left")); ?>

            <div class="col-lg-3">
                <?php
                echo $form->textField($model,"pay_start",array(
                    'readonly'=>$model->isReadonly(),'id'=>'pay_start','append'=>'月'
                ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('settle_type'),"settle_type",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->dropDownList($model,"settle_type",CGetName::getSettleTypeList(),array(
                    'readonly'=>$model->isReadonly(),'id'=>'settle_type'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('bill_day'),"bill_day",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->dropDownList($model,"bill_day",CGetName::getBillDayList(),array(
                    'readonly'=>$model->isReadonly(),'id'=>'bill_day'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('receivable_day'),"receivable_day",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->dropDownList($model,"receivable_day",CGetName::getReceivableDayList(),array(
                    'readonly'=>$model->isReadonly(),'id'=>'receivable_day'
                ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <?php if ($model->clue_type==2): ?>
                <?php echo TbHtml::label($model->getAttributeLabel('profit_int'),"profit_int",array('class'=>"col-lg-1 control-label")); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model,"profit_int",CGetName::getProfitList(),array(
                        'readonly'=>$model->isReadonly(),'id'=>'profit_int','empty'=>''
                    ));
                    ?>
                </div>
            <?php endif ?>
            <?php echo TbHtml::label($model->getAttributeLabel('bill_bool'),"bill_bool",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->inlineRadioButtonList($model,"bill_bool",CGetName::getCustVipList(),array(
                    'readonly'=>$model->isReadonly(),'disabled'=>$model->isReadonly(),'id'=>'bill_bool'
                ));
                ?>
            </div>
        </div>
    </div>
</div>