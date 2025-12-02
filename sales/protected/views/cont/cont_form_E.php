<?php
//结算信息
$modelClass = get_class($model);
?>
<div class="box box-info">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong><?php echo Yii::t("clue","Cost Information");?></strong>
                <span class="text-info fa fa-angle-left"></span>
            </h4>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('predict_amt'),"predict_amt",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->textField($model,"predict_amt",array(
                    'readonly'=>true,'id'=>'predict_amt'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('pay_week'),"pay_week",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                if($model->isReadonly()){
                    echo $form->hiddenField($model,"pay_week",array('id'=>'pay_week'));
                    echo TbHtml::textField("pay_week",CGetName::getPayWeekStrByKey($model->pay_week),array(
                        'readonly'=>true
                    ));
                }else{
                    echo $form->dropDownList($model,"pay_week",CGetName::getPayWeekList(),array(
                        'readonly'=>$model->isReadonly(),'id'=>'pay_week'
                    ));
                }
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('pay_type'),"pay_type",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->dropDownList($model,"pay_type",CGetName::getPayTypeList(),array(
                    'readonly'=>$model->isReadonly(),'id'=>'pay_type','empty'=>''
                ));
                ?>
            </div>
        </div>

        <div class="information-hide" style="display: block">
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel('deposit_need'),"deposit_need",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->numberField($model,"deposit_need",array(
                        'readonly'=>$model->isReadonly(),'id'=>'deposit_need'
                    ));
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('deposit_amt'),"deposit_amt",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->numberField($model,"deposit_amt",array(
                        'readonly'=>$model->isReadonly(),'id'=>'deposit_amt'
                    ));
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('deposit_rmk'),"deposit_rmk",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->textArea($model,"deposit_rmk",array(
                        'readonly'=>$model->isReadonly(),'id'=>'deposit_rmk','rows'=>2
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel('fee_type'),"fee_type",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    if($model->isReadonly()){
                        echo $form->hiddenField($model,"fee_type",array('id'=>'fee_type'));
                        echo TbHtml::textField("fee_type",CGetName::getFeeTypeStrByKey($model->fee_type),array(
                            'readonly'=>true
                        ));
                    }else{
                        echo $form->dropDownList($model,"fee_type",CGetName::getFeeTypeList(),array(
                            'readonly'=>$model->isReadonly(),'id'=>'fee_type'
                        ));
                    }
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('pay_month'),"pay_month",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->numberField($model,"pay_month",array(
                        'readonly'=>$model->isReadonly()||$model->fee_type!=1,'id'=>'pay_month','append'=>'月'
                    ));
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('pay_start'),"pay_start",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->numberField($model,"pay_start",array(
                        'readonly'=>$model->isReadonly()||$model->fee_type!=1,'id'=>'pay_start','append'=>'月'
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel('settle_type'),"settle_type",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    if($model->isReadonly()){
                        echo $form->hiddenField($model,"settle_type",array('id'=>'settle_type'));
                        echo TbHtml::textField("settle_type",CGetName::getSettleTypeStrByKey($model->settle_type),array(
                            'readonly'=>true
                        ));
                    }else{
                        echo $form->dropDownList($model,"settle_type",CGetName::getSettleTypeList(),array(
                            'readonly'=>$model->isReadonly(),'id'=>'settle_type'
                        ));
                    }
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('bill_day'),"bill_day",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    if($model->isReadonly()){
                        echo $form->hiddenField($model,"bill_day",array('id'=>'bill_day'));
                        echo TbHtml::textField("bill_day",CGetName::getBillDayStrByKey($model->bill_day),array(
                            'readonly'=>true
                        ));
                    }else{
                        echo $form->dropDownList($model,"bill_day",CGetName::getBillDayList(),array(
                            'readonly'=>$model->isReadonly(),'id'=>'bill_day'
                        ));
                    }
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('receivable_day'),"receivable_day",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    if($model->isReadonly()){
                        echo $form->hiddenField($model,"receivable_day",array('id'=>'receivable_day'));
                        echo TbHtml::textField("receivable_day",CGetName::getReceivableDayStrByKey($model->receivable_day),array(
                            'readonly'=>true
                        ));
                    }else{
                        echo $form->dropDownList($model,"receivable_day",CGetName::getReceivableDayList(),array(
                            'readonly'=>$model->isReadonly(),'id'=>'receivable_day'
                        ));
                    }
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php if ($model->clue_type==2): ?>
                    <?php echo TbHtml::label($model->getAttributeLabel('profit_int'),"profit_int",array('class'=>"col-lg-1 control-label")); ?>

                    <div class="col-lg-3">
                        <?php
                        echo $form->dropDownList($model,"profit_int",CGetName::getProfitList(),array(
                            'disabled'=>$model->isReadonly(),'id'=>'profit_int','empty'=>''
                        ));
                        ?>
                    </div>
                <?php endif ?>
                <?php echo TbHtml::label($model->getAttributeLabel('bill_bool'),"bill_bool",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-2">
                    <?php
                    echo $form->inlineRadioButtonList($model,"bill_bool",CGetName::getCustVipList(),array(
                        'disabled'=>$model->isReadonly(),'id'=>'bill_bool'
                    ));
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('area_bool'),"area_bool",array('class'=>"col-lg-2 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->inlineRadioButtonList($model,"area_bool",CGetName::getCustVipList(),array(
                        'disabled'=>$model->isReadonly(),'id'=>'area_bool'
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group <?php if($model->area_bool!='Y'){ echo "hide";}?>" id="areaJsonDiv" >
                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" style="min-width: 1000px;">
                            <?php
                            $html="<thead><tr>";
                            $html.="<th width='15%'>".Yii::t("clue","服务内容")."</th>";
                            $html.="<th width='20%'>".Yii::t("clue","面积/平方米")."</th>";
                            $html.="<th>".Yii::t("clue","服务频次")."</th>";
                            $html.="<th width='15%'>".Yii::t("clue","服务次数（年总次数）")."</th>";
                            $html.="<th width='15%'>".Yii::t("clue","月金额/元（含税）")."</th>";
                            if($model->isReadonly()===false){
                                $num =count($model->areaJson);
                                $html.="<th width='1%'>";
                                $html.=TbHtml::button("+",array(
                                    "class"=>"table_add",
                                    "data-temp"=>"temp1",
                                    "data-num"=>$num,
                                ));
                                $tempHtml=$this->renderPartial('//cont/table_temp1',array("model"=>$model,"form"=>$form,"num"=>0),true);
                                $html.=TbHtml::hiddenField("temp1",$tempHtml);
                                $html.="</th>";
                            }
                            $html.="</tr></thead><tbody>";
                            if(!empty($model->areaJson)){
                                foreach ($model->areaJson as $key=>$row){
                                    $html.=$this->renderPartial('//cont/table_temp1',array("model"=>$model,"form"=>$form,"row"=>$row,"num"=>$key),true);
                                }
                            }
                            $html.="</tbody>";
                            echo $html;
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
