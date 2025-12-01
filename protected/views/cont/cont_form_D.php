<?php
//合同信息
$modelClass = get_class($model);
$renewBool = $modelClass=="ContProForm"&&$model->pro_type=="C"?true:false;
?>
<div class="box box-info">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong><?php echo Yii::t("clue","Contract Information");?></strong>
                <span class="text-info fa fa-angle-left"></span>
            </h4>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('id'),"id",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->textField($model,"id",array(
                    'readonly'=>true,'id'=>'id'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('busine_id'),"busine_id",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->dropDownList($model,"busine_id",CGetName::getServiceDefList(),array(
                    'readonly'=>true,'id'=>'busine_id','multiple'=>'multiple'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('sign_type'),"sign_type",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->dropDownList($model,"sign_type",CGetName::getSignTypeList(),array(
                    'readonly'=>true,'id'=>'sign_type','empty'=>''
                ));
                ?>
            </div>
        </div>

        <div class="information-hide" style="display: block">
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel('cont_type'),"cont_type",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model,"cont_type",CGetName::getContactTypeList(),array(
                        'readonly'=>$model->isReadonly(),'id'=>'cont_type','empty'=>''
                    ));
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('con_v_type'),"con_v_type",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model,"con_v_type",CGetName::getContTypeList(),array(
                        'readonly'=>$model->isReadonly(),'id'=>'con_v_type','empty'=>''
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel('sign_date'),"sign_date",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->textField($model,"sign_date",array(
                            'autocomplete'=>'off',
                        'readonly'=>$model->isReadonly(),'id'=>'sign_date','prepend'=>'<span class="fa fa-calendar"></span>'
                    ));
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('cont_start_dt'),"cont_start_dt",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->textField($model,"cont_start_dt",array(
                        'autocomplete'=>'off',
                        'readonly'=>$renewBool||$model->isReadonly(),'id'=>'cont_start_dt','prepend'=>'<span class="fa fa-calendar"></span>'
                    ));
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('cont_end_dt'),"cont_end_dt",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->textField($model,"cont_end_dt",array(
                        'autocomplete'=>'off',
                        'readonly'=>$model->isReadonly(),'id'=>'cont_end_dt','prepend'=>'<span class="fa fa-calendar"></span>'
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel('is_seal'),"seal_type_id",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->inlineRadioButtonList($model,"is_seal",CGetName::getCustVipList(),array(
                        'disabled'=>$model->isReadonly(),'baseID'=>'is_seal'
                    ));
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('seal_type_id'),"seal_type_id",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model,"seal_type_id",CGetName::getSealTypeList(),array(
                        'readonly'=>$model->isReadonly()||$model->is_seal=="N",'id'=>'seal_type_id','empty'=>''
                    ));
                    ?>
                </div>
                <?php if ($model->clue_type==2): ?>
                    <?php echo $form->hiddenField($model, 'group_bool');?>
                <?php else:?>
                    <?php echo TbHtml::label($model->getAttributeLabel('group_bool'),"group_bool",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>

                    <div class="col-lg-3">
                        <?php
                        echo $form->inlineRadioButtonList($model,"group_bool",CGetName::getCustVipList(),array(
                            'disabled'=>$model->isReadonly(),'id'=>'group_bool'
                        ));
                        ?>
                    </div>
                <?php endif ?>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel('service_timer'),"service_timer",array('class'=>"col-lg-1 control-label")); ?>

                <div class="col-lg-2">
                    <?php
                    echo $form->numberField($model,"service_timer",array(
                        'readonly'=>$model->isReadonly(),'id'=>'service_timer','append'=>"分钟"
                    ));
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('prioritize_seal'),"prioritize_seal",array('class'=>"col-lg-2 control-label",'required'=>true)); ?>

                <div class="col-lg-2">
                    <?php
                    echo $form->inlineRadioButtonList($model,"prioritize_seal",CGetName::getCustVipList(),array(
                        'disabled'=>$model->isReadonly(),'id'=>'prioritize_seal'
                    ));
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel('prioritize_service'),"prioritize_service",array('class'=>"col-lg-2 control-label",'required'=>true)); ?>

                <div class="col-lg-2">
                    <?php
                    echo $form->inlineRadioButtonList($model,"prioritize_service",CGetName::getCustVipList(),array(
                        'disabled'=>$model->isReadonly(),'id'=>'prioritize_service'
                    ));
                    ?>
                </div>
            </div>
            <?php if ($model->clue_type==1): ?>
                <div class="form-group">
                    <?php echo TbHtml::label($model->getAttributeLabel('is_renewal'),"con_v_type",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
                    <div class="col-lg-3">
                        <?php
                        echo $form->inlineRadioButtonList($model,"is_renewal",CGetName::getCustVipList(),array(
                            'disabled'=>$model->isReadonly(),'baseID'=>'is_renewal'
                        ));
                        ?>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>
