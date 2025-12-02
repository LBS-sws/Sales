<div class="information-header">
    <h4>
        <strong>结算信息</strong>
    </h4>
</div>

<div class="form-group">
    <?php echo TbHtml::label($virModel->getAttributeLabel('predict_amt'),"predict_amt",array('class'=>"col-lg-1 control-label col-lg-left")); ?>

    <div class="col-lg-3">
        <?php
        echo TbHtml::textField("predict_amt",'',array(
            'readonly'=>true,'id'=>'predict_amt'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('pay_week'),"pay_week",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>

    <div class="col-lg-3">
        <?php
        echo $form->dropDownList($virModel,"pay_week",CGetName::getPayWeekList(),array(
            'readonly'=>$virModel->isReadonly(),'id'=>'pay_week','empty'=>''
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('pay_type'),"pay_type",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>

    <div class="col-lg-3">
        <?php
        echo $form->dropDownList($virModel,"pay_type",CGetName::getPayTypeList(),array(
            'readonly'=>$virModel->isReadonly(),'id'=>'pay_type','empty'=>''
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($virModel->getAttributeLabel('deposit_need'),"deposit_need",array('class'=>"col-lg-1 control-label")); ?>

    <div class="col-lg-3">
        <?php
        echo $form->textField($virModel,"deposit_need",array(
            'readonly'=>$virModel->isReadonly(),'id'=>'deposit_need'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('deposit_amt'),"deposit_amt",array('class'=>"col-lg-1 control-label col-lg-left")); ?>

    <div class="col-lg-3">
        <?php
        echo $form->textField($virModel,"deposit_amt",array(
            'readonly'=>$virModel->isReadonly(),'id'=>'deposit_amt'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('deposit_rmk'),"deposit_rmk",array('class'=>"col-lg-1 control-label col-lg-left")); ?>

    <div class="col-lg-3">
        <?php
        echo $form->textArea($virModel,"deposit_rmk",array(
            'readonly'=>$virModel->isReadonly(),'id'=>'deposit_rmk'
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($virModel->getAttributeLabel('fee_type'),"fee_type",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

    <div class="col-lg-3">
        <?php
        echo $form->dropDownList($virModel,"fee_type",CGetName::getFeeTypeList(),array(
            'readonly'=>$virModel->isReadonly(),'id'=>'fee_type','empty'=>''
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('pay_month'),"pay_month",array('class'=>"col-lg-1 control-label col-lg-left")); ?>

    <div class="col-lg-3">
        <?php
        echo $form->textField($virModel,"pay_month",array(
            'readonly'=>$virModel->isReadonly()||$virModel->fee_type!=1,'id'=>'pay_month','append'=>'月'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('pay_start'),"pay_start",array('class'=>"col-lg-1 control-label col-lg-left")); ?>

    <div class="col-lg-3">
        <?php
        echo $form->textField($virModel,"pay_start",array(
            'readonly'=>$virModel->isReadonly()||$virModel->fee_type!=1,'id'=>'pay_start','append'=>'月'
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($virModel->getAttributeLabel('settle_type'),"settle_type",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

    <div class="col-lg-3">
        <?php
        echo $form->dropDownList($virModel,"settle_type",CGetName::getSettleTypeList(),array(
            'readonly'=>$virModel->isReadonly(),'id'=>'settle_type','empty'=>''
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('bill_day'),"bill_day",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

    <div class="col-lg-3">
        <?php
        echo $form->dropDownList($virModel,"bill_day",CGetName::getBillDayList(),array(
            'readonly'=>$virModel->isReadonly(),'id'=>'bill_day','empty'=>''
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('receivable_day'),"receivable_day",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

    <div class="col-lg-3">
        <?php
        echo $form->dropDownList($virModel,"receivable_day",CGetName::getReceivableDayList(),array(
            'readonly'=>$virModel->isReadonly(),'id'=>'receivable_day','empty'=>''
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php if ($virModel->clue_type==2): ?>
        <?php echo TbHtml::label($virModel->getAttributeLabel('profit_int'),"profit_int",array('class'=>"col-lg-1 control-label")); ?>

        <div class="col-lg-3">
            <?php
            echo $form->dropDownList($virModel,"profit_int",CGetName::getProfitList(),array(
                'readonly'=>$virModel->isReadonly(),'id'=>'profit_int','empty'=>''
            ));
            ?>
        </div>
    <?php endif ?>
    <?php echo TbHtml::label($virModel->getAttributeLabel('bill_bool'),"bill_bool",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

    <div class="col-lg-3">
        <?php
        echo $form->inlineRadioButtonList($virModel,"bill_bool",CGetName::getCustVipList(),array(
            'readonly'=>$virModel->isReadonly(),'disabled'=>$virModel->isReadonly(),'id'=>'bill_bool'
        ));
        ?>
    </div>
</div>