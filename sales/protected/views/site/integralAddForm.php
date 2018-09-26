
<div class="form-group">
    <?php echo $form->labelEx($model,'apply_date',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
            </div>
            <?php echo $form->textField($model, 'apply_date',
                array('class'=>'form-control pull-right','readonly'=>($readonly),'id'=>"apply_date"));
            ?>
        </div>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label(Yii::t('integral','expiration date'),"",array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
            </div>
            <?php echo TbHtml::textField('exp_date','',
                array('class'=>'form-control pull-right','readonly'=>(true),'id'=>"exp_date"));
            ?>
        </div>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'integral_type',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php echo $form->dropDownList($model, 'integral_type',CreditTypeForm::getCategoryAll(),
            array('readonly'=>($readonly),'id'=>'int_type')
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'credit_type',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php echo $form->dropDownListTwo($model, 'credit_type',CreditTypeForm::getCreditTypeList(),
            array('readonly'=>($readonly),'id'=>'set_id')
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'credit_point',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php echo $form->textField($model, 'credit_point',
            array('readonly'=>(true),'id'=>'integral')
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'remark',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-5">
        <?php echo $form->textArea($model, 'remark',
            array('rows'=>4,'cols'=>50,'maxlength'=>1000,'readonly'=>($readonly))
        ); ?>
    </div>
</div>

<script>
    $(function () {
        $("#set_id").on("change",function () {
            var num = $(this).find("option:selected").attr("num");
            var gral = $(this).find("option:selected").attr("gral");
            $("#integral").val(num);
            $("#int_type").val(gral);
        })
        $("#int_type").on("change",function () {
            var gral = $(this).val();
            if(gral==""){
                $("#set_id>option").show();
            }else{
                $("#set_id>option").hide();
                $("#set_id>option[gral='"+gral+"']").show();
            }
        });
        $("#apply_date").on("change",function () {
            var value = $(this).val();
            if(value != ""){
                value = value.split("-")[0];
                value = value.split("\/")[0];
                value = parseInt(value,10)+4;
                $("#exp_date").val(value+"-12-31");
            }
        }).trigger("change");
    })
</script>