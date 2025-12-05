<?php
$className = get_class($model);
?>
<?php echo TbHtml::hiddenField("{$className}[scenario]", $model->scenario); ?>
<?php echo TbHtml::hiddenField("{$className}[id]", $model->id); ?>
<?php echo TbHtml::hiddenField("{$className}[clue_id]", $model->clue_id); ?>

<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("invoice_name"),'',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
    <div class="col-lg-6">
        <?php
        echo TbHtml::textField("{$className}[invoice_name]",$model->invoice_name,array(
            "class"=>'form-control','readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("invoice_type"),'',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::inlineRadioButtonList("{$className}[invoice_type]",$model->invoice_type,CGetName::getInvoiceTypeList(),array(
            'disabled'=>$model->isReadonly(),'baseID'=>'win_invoice_type'
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("invoice_header"),'',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[invoice_header]",$model->invoice_header,array(
            "class"=>'form-control','readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("tax_id"),'',array('class'=>"col-lg-2 control-label","required"=>($model->invoice_type==2||$model->invoice_type==1))); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[tax_id]",$model->tax_id,array(
            "class"=>'form-control','readonly'=>$model->isReadonly(),'id'=>'win_tax_id'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($model->getAttributeLabel("invoice_address"),'',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[invoice_address]",$model->invoice_address,array(
            "class"=>'form-control','readonly'=>$model->isReadonly(),'id'=>'win_invoice_address'
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("invoice_number"),'',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[invoice_number]",$model->invoice_number,array(
            "class"=>'form-control','readonly'=>$model->isReadonly(),'id'=>'win_invoice_number'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($model->getAttributeLabel("invoice_user"),'',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[invoice_user]",$model->invoice_user,array(
            "class"=>'form-control','readonly'=>$model->isReadonly(),'id'=>'win_invoice_user'
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("invoice_phone"),'',array('class'=>"col-lg-2 control-label","required"=>$model->invoice_type==2)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[invoice_phone]",$model->invoice_phone,array(
            "class"=>'form-control','readonly'=>$model->isReadonly(),'id'=>'win_invoice_phone'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($model->getAttributeLabel("show_pay"),'',array('class'=>"col-lg-2 control-label","required"=>$model->invoice_type==2)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::inlineRadioButtonList("{$className}[show_pay]",$model->show_pay,CGetName::getCustVipList(),array(
            'readonly'=>$model->isReadonly(),'id'=>'win_show_pay'
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("show_cpy"),'',array('class'=>"col-lg-2 control-label","required"=>$model->invoice_type==2)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::inlineRadioButtonList("{$className}[show_cpy]",$model->show_cpy,CGetName::getCustVipList(),array(
            'readonly'=>$model->isReadonly(),'id'=>'win_show_cpy'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($model->getAttributeLabel("show_opy"),'',array('class'=>"col-lg-2 control-label","required"=>$model->invoice_type==2)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::inlineRadioButtonList("{$className}[show_opy]",$model->show_opy,CGetName::getCustVipList(),array(
            'readonly'=>$model->isReadonly(),'id'=>'win_show_opy'
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("invoice_rmk"),'',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-6">
        <?php
        echo TbHtml::textArea("{$className}[invoice_rmk]",$model->invoice_rmk,array(
            'readonly'=>$model->isReadonly(),'rows'=>3
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("z_display"),'',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::inlineRadioButtonList("{$className}[z_display]",$model->z_display,CGetName::getDisplayList(),array(
            'readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
</div>

<script type="text/javascript">
<?php
$js = <<<EOF
    $('#win_invoice_type input').click(function(){
        $('#win_tax_id').parent('div').prev('label').children('span').remove();
        //$('#win_invoice_address').parent('div').prev('label').children('span').remove();
        //$('#win_invoice_number').parent('div').prev('label').children('span').remove();
        //$('#win_invoice_user').parent('div').prev('label').children('span').remove();
        var invoice_type = $(this).val();
        switch(invoice_type){
            case "1":
                $('#win_tax_id').parent('div').prev('label').append('<span class="required">*</span>');
                break;
            case "2":
                $('#win_tax_id').parent('div').prev('label').append('<span class="required">*</span>');
                //$('#win_invoice_address').parent('div').prev('label').append('<span class="required">*</span>');
                //$('#win_invoice_number').parent('div').prev('label').append('<span class="required">*</span>');
                //$('#win_invoice_user').parent('div').prev('label').append('<span class="required">*</span>');
                break;
            case "3":
                break;
        }
    });
EOF;
echo $js;
?>
</script>
