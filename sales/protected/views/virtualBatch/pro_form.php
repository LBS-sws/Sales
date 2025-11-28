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
            <?php
            if($model->pro_type=="S"){
                $labelStr = Yii::t("clue","Suspend Date");
            }elseif($model->pro_type=="T"){
                $labelStr = Yii::t("clue","Terminate Date");
            }else{
                $labelStr = $model->getAttributeLabel('pro_date');
            }
            echo TbHtml::label($labelStr,"pro_date",array('class'=>"col-lg-1 control-label",'required'=>true));
            ?>

            <div class="col-lg-3">
                <?php
                echo $form->textField($model,"pro_date",array(
                    'readonly'=>$model->isReadonly(),'id'=>'pro_date','prepend'=>'<span class="fa fa-calendar"></span>'
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
        <?php if (in_array($model->pro_type,array("S","T"))): ?>
            <div class="form-group">
                <?php
                echo TbHtml::label($model->getAttributeLabel('stop_set_id'),"stop_set_id",array('class'=>"col-lg-1 control-label",'required'=>true));
                ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model,"stop_set_id",CGetName::getStopSetIDListByType($model->pro_type,$model->stop_set_id),array(
                        'readonly'=>$model->isReadonly(),'id'=>'stop_set_id'
                    ));
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('stop_month_amt'),"stop_month_amt",array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    echo $form->textField($model,"stop_month_amt",array(
                        'readonly'=>true,'id'=>'stop_month_amt'
                    ));
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('stop_year_amt'),"stop_year_amt",array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    echo $form->textField($model,"stop_year_amt",array(
                        'readonly'=>true,'id'=>'stop_year_amt'
                    ));
                    ?>
                </div>
            </div>
            <?php if (in_array($model->pro_type,array("T"))): ?>
                <div class="form-group">
                    <?php echo TbHtml::label($model->getAttributeLabel('need_back'),"need_back",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
                    <div class="col-lg-3">
                        <?php
                        echo $form->inlineRadioButtonList($model,"need_back",CGetName::getCustVipList(),array(
                            'readonly'=>$model->isReadonly(),'id'=>'need_back'
                        ));
                        ?>
                    </div>
                    <?php echo TbHtml::label($model->getAttributeLabel('surplus_num'),"surplus_num",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
                    <div class="col-lg-3">
                        <?php
                        echo $form->numberField($model,"surplus_num",array(
                            'readonly'=>true,'id'=>'surplus_num'
                        ));
                        ?>
                    </div>
                    <?php echo TbHtml::label($model->getAttributeLabel('surplus_amt'),"surplus_amt",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
                    <div class="col-lg-3">
                        <?php
                        echo $form->numberField($model,"surplus_amt",array(
                            'readonly'=>true,'id'=>'surplus_amt'
                        ));
                        ?>
                    </div>
                </div>
            <?php endif ?>
        <?php endif ?>
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
