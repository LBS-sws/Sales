<?php
//甲方信息
$modelClass = get_class($model);
?>

<div class="box box-info">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong>主合同:
                    <?php
                    if($modelClass=="VirtualHeadForm"){
                        $goUrl = Yii::app()->createUrl('contHead/detail',array('index'=>$model->contHeadRow['id']));
                        echo TbHtml::link($model->contHeadRow['cont_code'],$goUrl,array(
                            "target"=>'_blank'
                        ));
                    }else{
                        echo $model->contHeadRow['cont_code'];
                    }
                    ?>
                </strong>
            </h4>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('vir_code'),"vir_code",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->textField($model,"vir_code",array(
                    'readonly'=>true,'id'=>'vir_code'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('lbs_main'),"lbs_main",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->dropDownList($model,"lbs_main",CGetName::getLbsMainList($model->city),array(
                    'readonly'=>$model->isReadonly(),'id'=>'lbs_main'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('busine_id_text'),"busine_id_text",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo TbHtml::textField("busine_id_text",$model->busine_id_text,array(
                    'readonly'=>true,'id'=>'busine_id_text'
                ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('sign_date'),"sign_date",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->textField($model,"sign_date",array(
                    'readonly'=>$model->isReadonly(),'id'=>'sign_date','prepend'=>'<span class="fa fa-calendar"></span>'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('cont_start_dt'),"cont_start_dt",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->textField($model,"cont_start_dt",array(
                    'readonly'=>$model->isReadonly(),'id'=>'cont_start_dt','prepend'=>'<span class="fa fa-calendar"></span>'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('cont_end_dt'),"cont_end_dt",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->textField($model,"cont_end_dt",array(
                    'readonly'=>$model->isReadonly(),'id'=>'cont_end_dt','prepend'=>'<span class="fa fa-calendar"></span>'
                ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('sign_type'),"sign_type",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo $form->dropDownList($model,"sign_type",CGetName::getSignTypeList(),array(
                    'readonly'=>$model->isReadonly(),'id'=>'sign_type'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('con_v_type'),"con_v_type",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
            <div class="col-lg-3">
                <?php
                echo $form->dropDownList($model,"con_v_type",CGetName::getContTypeList(),array(
                    'readonly'=>$model->isReadonly(),'id'=>'con_v_type'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('yewudalei'),"yewudalei",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
            <div class="col-lg-3">
                <?php
                echo $form->hiddenField($model,'yewudalei');
                echo TbHtml::textField('yewudalei',CGetName::getYewudaleiStrByKey($model->yewudalei),
                    array('readonly'=>true)
                );
                ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('is_seal'),"is_seal",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>
            <div class="col-lg-3">
                <?php
                echo $form->inlineRadioButtonList($model,"is_seal",CGetName::getCustVipList(),array(
                    'disabled'=>$model->isReadonly(),'baseID'=>'is_seal'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('seal_type_id'),"seal_type_id",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>
            <div class="col-lg-3">
                <?php
                echo $form->dropDownList($model,"seal_type_id",CGetName::getSealTypeList(),array(
                    'readonly'=>$model->isReadonly()||$model->is_seal=="N",'id'=>'seal_type_id','empty'=>''
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('vir_status'),"vir_status",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                echo TbHtml::textField("vir_status",CGetName::getContVirStatusStrByKey($model->vir_status),array(
                    'readonly'=>true,'id'=>'vir_status'
                ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('service_timer'),"service_timer",array('class'=>"col-lg-1 control-label col-lg-left")); ?>
            <div class="col-lg-2">
                <?php
                echo $form->textField($model,"service_timer",array(
                    'readonly'=>$model->isReadonly(),'id'=>'service_timer','append'=>'分钟'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('prioritize_seal'),"prioritize_seal",array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
            <div class="col-lg-2">
                <?php
                echo $form->inlineRadioButtonList($model,"prioritize_seal",CGetName::getCustVipList(),array(
                    'disabled'=>$model->isReadonly(),'baseID'=>'prioritize_seal'
                ));
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('prioritize_service'),"prioritize_service",array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
            <div class="col-lg-3">
                <?php
                echo $form->inlineRadioButtonList($model,"prioritize_service",CGetName::getCustVipList(),array(
                    'disabled'=>$model->isReadonly(),'baseID'=>'prioritize_service'
                ));
                ?>
            </div>
        </div>
        <?php if ($model->clue_type==1): ?>
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel('is_renewal'),"prioritize_service",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
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