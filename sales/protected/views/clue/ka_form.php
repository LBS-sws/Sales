<?php echo $form->hiddenField($model, 'id'); ?>
<?php echo $form->hiddenField($model, 'scenario'); ?>
<?php echo $form->hiddenField($model, 'clue_type'); ?>
<?php echo $form->hiddenField($model, 'clue_status'); ?>
<?php echo $form->hiddenField($model, 'city'); ?>
<div class="form-group">
    <?php echo $form->labelEx($model,'entry_date',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->textField($model, 'entry_date',
            array('readonly'=>$model->isReadonly(),'id'=>'entry_date',
                'prepend'=>'<span class="fa fa-calendar"></span>')
        ); ?>
    </div>
    <?php if ($model->scenario!='new'): ?>
        <div class="form-group">
            <?php echo $form->labelEx($model,'clue_code',array('class'=>"col-lg-2 control-label")); ?>
            <div class="col-lg-3">
                <?php echo $form->textField($model, 'clue_code',
                    array('readonly'=>true)
                ); ?>
            </div>
        </div>
    <?php endif ?>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'cust_name',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-8">
        <?php echo $form->textField($model, 'cust_name',
            array('readonly'=>$model->isReadonly())
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'cust_type_group',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->textField($model, 'cust_type_group',
            array('readonly'=>$model->isReadonly())
        ); ?>
    </div>
    <?php echo $form->labelEx($model,'cust_type',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->textField($model, 'cust_type',
            array('readonly'=>$model->isReadonly())
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'city',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php
        echo TbHtml::textField("city_name",General::getCityName($model->city),
            array('readonly'=>true)
            );
        ?>
    </div>
    <?php echo $form->labelEx($model,'district',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->textField($model, 'district',
            array('readonly'=>$model->isReadonly())
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'street',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-8">
        <?php echo $form->textField($model, 'street',
            array('readonly'=>$model->isReadonly())
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'clue_source',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->textField($model, 'clue_source',
            array('readonly'=>$model->isReadonly())
        ); ?>
    </div>
    <?php echo $form->labelEx($model,'district',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->textField($model, 'district',
            array('readonly'=>$model->isReadonly())
        ); ?>
    </div>
</div>