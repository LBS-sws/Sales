<div class="information-header">
    <h4>
        <strong>虚拟合约信息</strong>
    </h4>
</div>
<div class="form-group">
    <?php echo TbHtml::label($virModel->getAttributeLabel('lbs_main'),"lbs_main",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

    <div class="col-lg-3">
        <?php
        echo $form->dropDownList($virModel,"lbs_main",CGetName::getLbsMainList($virModel->city),array(
            'readonly'=>$virModel->isReadonly(),'id'=>'lbs_main','empty'=>''
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('busine_id_text'),"busine_id_text",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

    <div class="col-lg-3">
        <?php
        echo TbHtml::textField("busine_id_text",$virModel->busine_id_text,array(
            'readonly'=>true,'id'=>'busine_id_text'
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($virModel->getAttributeLabel('sign_date'),"sign_date",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

    <div class="col-lg-3">
        <?php
        echo $form->textField($virModel,"sign_date",array(
            'readonly'=>$virModel->isReadonly(),'id'=>'sign_date','prepend'=>'<span class="fa fa-calendar"></span>'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('cont_start_dt'),"cont_start_dt",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>

    <div class="col-lg-3">
        <?php
        echo $form->textField($virModel,"cont_start_dt",array(
            'readonly'=>$virModel->isReadonly(),'id'=>'cont_start_dt','prepend'=>'<span class="fa fa-calendar"></span>'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('cont_end_dt'),"cont_end_dt",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>

    <div class="col-lg-3">
        <?php
        echo $form->textField($virModel,"cont_end_dt",array(
            'readonly'=>$virModel->isReadonly(),'id'=>'cont_end_dt','prepend'=>'<span class="fa fa-calendar"></span>'
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($virModel->getAttributeLabel('is_seal'),"is_seal",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
    <div class="col-lg-3">
        <?php
        echo $form->inlineRadioButtonList($virModel,"is_seal",CGetName::getCustVipList(),array(
            'disabled'=>$virModel->isReadonly(),'baseID'=>'is_seal'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('seal_type_id'),"seal_type_id",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>
    <div class="col-lg-3">
        <?php
        echo $form->dropDownList($virModel,"seal_type_id",CGetName::getSealTypeList(),array(
            'readonly'=>$virModel->isReadonly()||$virModel->is_seal=="N",'id'=>'seal_type_id','empty'=>''
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('con_v_type'),"con_v_type",array('class'=>"col-lg-1 control-label col-lg-left",'required'=>true)); ?>
    <div class="col-lg-3">
        <?php
        echo $form->dropDownList($virModel,"con_v_type",CGetName::getContTypeList(),array(
            'readonly'=>$virModel->isReadonly(),'id'=>'con_v_type','empty'=>''
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($virModel->getAttributeLabel('service_timer'),"service_timer",array('class'=>"col-lg-1 control-label col-lg-left")); ?>

    <div class="col-lg-2">
        <?php
        echo $form->textField($virModel,"service_timer",array(
            'readonly'=>$virModel->isReadonly(),'id'=>'service_timer','append'=>'分钟'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('prioritize_seal'),"prioritize_service",array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
    <div class="col-lg-2">
        <?php
        echo $form->inlineRadioButtonList($virModel,"prioritize_seal",CGetName::getCustVipList(),array(
            'disabled'=>$virModel->isReadonly(),'baseID'=>'prioritize_seal'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('prioritize_service'),"prioritize_service",array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
    <div class="col-lg-2">
        <?php
        echo $form->inlineRadioButtonList($virModel,"prioritize_service",CGetName::getCustVipList(),array(
            'disabled'=>$virModel->isReadonly(),'baseID'=>'prioritize_service'
        ));
        ?>
    </div>
</div>

<?php if ($virModel->clue_type==1): ?>
    <div class="form-group">
        <?php echo TbHtml::label($virModel->getAttributeLabel('is_renewal'),"is_renewal",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
        <div class="col-lg-2">
            <?php
            echo $form->inlineRadioButtonList($virModel,"is_renewal",CGetName::getCustVipList(),array(
                'disabled'=>$virModel->isReadonly(),'baseID'=>'is_renewal'
            ));
            ?>
        </div>
    </div>
<?php endif ?>