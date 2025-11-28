<?php
$this->pageTitle=Yii::app()->name . ' - Clue Invoice Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    .select2-container.select2-container-disabled .select2-choice {
        background-color: #ddd;
        border-color: #a8a8a8;
    }
    select.readonly{ pointer-events: none;}
    select[readonly]{pointer-events: none;}
    .select2.select2-container{ width: 100%!important;}
    .select2-container--default .select2-selection--multiple .select2-selection__choice{ padding: 0px 7px;}
    .select2-container .select2-selection--single{ height: 34px;}

</style>
<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('clue','invoice form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php
        switch ($type){
            case 1:
                echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('clueHead/view',array("index"=>$model->clue_id))));
                break;
            case 2:
                echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('clueInvoice/index')));
                break;
            default:
                echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('clueInvoice/invoiceList',array("clue_id"=>$model->clue_id))));
        }
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('clueInvoice/save',array("type"=>$type))));
			?>
<?php endif ?>
<?php if ($model->scenario=='edit'): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
                <div class="btn-group pull-right" role="group">
                    <?php if ($model->scenario!='new'): ?>
                        <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('clue','Clue Invoice History'), array(
                                'data-toggle'=>'modal','data-target'=>'#clueHistoryDialog',)
                        );
                        ?>
                    <?php endif ?>
                </div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
            <?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'city'); ?>
            <?php echo $form->hiddenField($model, 'clue_id'); ?>
            <div class="form-group">
                <?php
                echo TbHtml::label(Yii::t("clue","clue code"),'',array('class'=>"col-lg-2 control-label"));
                ?>
                <div class="col-lg-2">
                    <?php
                    echo TbHtml::textField("clue_code",$model->clueHeadRow["clue_code"],array(
                            "class"=>'form-control','readonly'=>true
                    ));
                    ?>
                </div>
                <?php
                echo TbHtml::label(Yii::t("clue","clue type"),'',array('class'=>"col-lg-1 control-label"));
                ?>
                <div class="col-lg-2">
                    <?php
                    echo TbHtml::textField("clue_code",CGetName::getClueTypeStr($model->clueHeadRow["clue_type"]),array(
                            "class"=>'form-control','readonly'=>true
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php
                echo TbHtml::label(Yii::t("clue","clue name"),'',array('class'=>"col-lg-2 control-label"));
                ?>
                <div class="col-lg-8">
                    <?php
                    echo TbHtml::textField("cust_name",$model->clueHeadRow["cust_name"],array(
                            "class"=>'form-control','readonly'=>true
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'invoice_name',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-8">
                    <?php
                    echo $form->textField($model,'invoice_name',array(
                            "class"=>'form-control','readonly'=>$model->isReadOnly()
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'invoice_type',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php
                    echo $form->inlineRadioButtonList($model,'invoice_type',CGetName::getInvoiceTypeList(),array(
                        'disabled'=>$model->isReadOnly(),'baseID'=>'invoice_type'
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'invoice_header',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php
                    echo $form->textField($model,'invoice_header',array(
                        "class"=>'form-control','readonly'=>$model->isReadOnly()
                    ));
                    ?>
                </div>
                <?php echo Tbhtml::label($model->getAttributeLabel('tax_id'),false,array('class'=>"col-lg-1 control-label","required"=>($model->invoice_type==2||$model->invoice_type==1))); ?>
                <div class="col-lg-2">
                    <?php
                    echo $form->textField($model,'tax_id',array(
                        "class"=>'form-control','readonly'=>$model->isReadOnly(),'id'=>'tax_id'
                    ));
                    ?>
                </div>
                <?php echo Tbhtml::label($model->getAttributeLabel('invoice_address'),false,array('class'=>"col-lg-1 control-label","required"=>$model->invoice_type==2)); ?>
                <div class="col-lg-2">
                    <?php
                    echo $form->textField($model,'invoice_address',array(
                        "class"=>'form-control','readonly'=>$model->isReadOnly(),'id'=>'invoice_address'
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo Tbhtml::label($model->getAttributeLabel('invoice_number'),false,array('class'=>"col-lg-2 control-label","required"=>$model->invoice_type==2)); ?>
                <div class="col-lg-2">
                    <?php
                    echo $form->textField($model,'invoice_number',array(
                        "class"=>'form-control','readonly'=>$model->isReadOnly(),'id'=>'invoice_number'
                    ));
                    ?>
                </div>
                <?php echo Tbhtml::label($model->getAttributeLabel('invoice_user'),false,array('class'=>"col-lg-1 control-label","required"=>$model->invoice_type==2)); ?>
                <div class="col-lg-2">
                    <?php
                    echo $form->textField($model,'invoice_user',array(
                        "class"=>'form-control','readonly'=>$model->isReadOnly(),'id'=>'invoice_user'
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'invoice_phone',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php
                    echo $form->textField($model,'invoice_phone',array(
                        "class"=>'form-control','readonly'=>$model->isReadOnly()
                    ));
                    ?>
                </div>
                <?php echo $form->labelEx($model,'show_pay',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php
                    echo $form->inlineRadioButtonList($model,'show_pay',CGetName::getCustVipList(),array(
                        'disabled'=>$model->isReadOnly()
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'show_cpy',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php
                    echo $form->inlineRadioButtonList($model,'show_cpy',CGetName::getCustVipList(),array(
                        'disabled'=>$model->isReadOnly()
                    ));
                    ?>
                </div>
                <?php echo $form->labelEx($model,'show_opy',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php
                    echo $form->inlineRadioButtonList($model,'show_opy',CGetName::getCustVipList(),array(
                        'disabled'=>$model->isReadOnly()
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'invoice_rmk',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-4">
                    <?php
                    echo $form->textArea($model,'invoice_rmk',array(
                        "class"=>'form-control','readonly'=>$model->isReadOnly(),'rows'=>4
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'z_display',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-8">
                    <?php
                    echo $form->inlineRadioButtonList($model,'z_display',CGetName::getDisplayList(),array(
                        'readonly'=>$model->isReadOnly()
                    ));
                    ?>
                </div>
            </div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//clue/historylist',array("model"=>$model,"type"=>4)); ?>
<?php
$js = Script::genDeleteData(Yii::app()->createUrl('clueInvoice/delete',array("type"=>$type)));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = <<<EOF
    $('#invoice_type input').click(function(){
        $('#tax_id').parent('div').prev('label').children('span').remove();
        $('#invoice_address').parent('div').prev('label').children('span').remove();
        $('#invoice_number').parent('div').prev('label').children('span').remove();
        $('#invoice_user').parent('div').prev('label').children('span').remove();
        var invoice_type = $(this).val();
        switch(invoice_type){
            case "1":
                $('#tax_id').parent('div').prev('label').append('<span class="required">*</span>');
                break;
            case "2":
                $('#tax_id').parent('div').prev('label').append('<span class="required">*</span>');
                $('#invoice_address').parent('div').prev('label').append('<span class="required">*</span>');
                $('#invoice_number').parent('div').prev('label').append('<span class="required">*</span>');
                $('#invoice_user').parent('div').prev('label').append('<span class="required">*</span>');
                break;
            case "3":
                break;
        }
    });
EOF;
Yii::app()->clientScript->registerScript('select2_1',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


