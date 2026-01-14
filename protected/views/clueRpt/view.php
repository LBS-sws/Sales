<?php
$this->pageTitle=Yii::app()->name . ' - Clue Head Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
    'htmlOptions'=>array('enctype'=>'multipart/form-data'),
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
		<strong><?php echo Yii::t('clue','Clue Rpt Form'); ?></strong>
        <?php
        $url=Yii::app()->createUrl('clueRpt/edit',array("index"=>$model->id,"token"=>isset($token)?$token:""));
        echo TbHtml::link("返回CRM系统",$url,array(
            "target"=>"_blank",
            "class"=>"btn btn-primary"
        ));
        ?>
	</h1>
</section>

<section class="content">
	<div class="box box-info">
		<div class="box-body">
            <?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo $form->hiddenField($model, 'clue_type'); ?>
            <?php echo $form->hiddenField($model, 'city'); ?>
            <?php echo $form->hiddenField($model, 'mh_id'); ?>
            <?php echo $form->hiddenField($model, 'rpt_status'); ?>
            <div class="form-group">
                <?php echo $form->labelEx($model,'sales_id',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->hiddenField($model, 'sales_id'); ?>
                    <?php
                    echo TbHtml::textField("sales_id",CGetName::getEmployeeNameByKey($model->sales_id),array(
                            'readonly'=>true
                    ));
                    ?>
                </div>
                <?php echo $form->labelEx($model,'rpt_status',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->hiddenField($model, 'rpt_status'); ?>
                    <?php
                    echo TbHtml::textField("rpt_status",CGetName::getRptStatusStrByKey($model->rpt_status),array(
                        'readonly'=>true
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'clue_id',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->textField($model, 'clue_id',
                        array('readonly'=>true)
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'clue_service_id',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'clue_service_id',
                        array('readonly'=>true)
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'cust_name',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-6">
                    <?php echo $form->textField($model, 'cust_name',
                        array('readonly'=>true)
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'cust_class',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->hiddenField($model, 'cust_class'); ?>
                    <?php

                    echo TbHtml::textField("cust_class",CGetName::getCustClassStrByKey($model->cust_class),array(
                        'readonly'=>true
                    ));
                    ?>
                </div>
                <?php echo $form->labelEx($model,'cust_level',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->hiddenField($model, 'cust_level'); ?>
                    <?php
                    echo TbHtml::textField("cust_level",CGetName::getCustLevelStrByKey($model->cust_level),array(
                        'readonly'=>true
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'yewudalei',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    echo TbHtml::textField("yewudalei",CGetName::getYewudaleiStrByKey($model->clueHeadRow["yewudalei"]),array(
                        'readonly'=>true
                    ));
                    ?>
                </div>
                <?php echo $form->labelEx($model,'lbs_main',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model, 'lbs_main',CGetName::getLbsMainList($model->city),array(
                        'readonly'=>$model->isReadonly(),'empty'=>''
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'total_amt',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->numberField($model, 'total_amt',
                        array('readonly'=>$model->isReadonly())
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'file_type',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model, 'file_type',CGetName::getFileTypeList(),array(
                        'readonly'=>$model->isReadonly(),'empty'=>''
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'cont_type_id',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->dropDownList($model, 'cont_type_id',CGetName::getContTypeList(),
                        array('readonly'=>$model->isReadonly(),'empty'=>'')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'fee_add',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model, 'fee_add',CGetName::getHasAndNotList(),array(
                        'readonly'=>$model->isReadonly(),'empty'=>''
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'service_type_id',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->dropDownList($model, 'service_type_id',CGetName::getServiceFreeList(),
                        array('readonly'=>$model->isReadonly(),'empty'=>'')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'bill_week',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model, 'bill_week',CGetName::getBillWeekList(),array(
                        'readonly'=>$model->isReadonly(),'empty'=>''
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'audit_type',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->dropDownList($model, 'audit_type',CGetName::getAuditTypeList(),
                        array('readonly'=>$model->isReadonly(),'empty'=>'')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'cut_type',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model, 'cut_type',CGetName::getHasAndNotList(),array(
                        'readonly'=>$model->isReadonly(),'empty'=>''
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel("is_seal"),'is_seal',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->inlineRadioButtonList($model, 'is_seal',CGetName::getCustVipList(),array(
                        'disabled'=>$model->isReadonly()
                    ));
                    ?>
                </div>
                <div id="seal_type_div" class="<?php echo $model->is_seal=="N"?"hide":"";?>">
                    <?php echo TbHtml::label($model->getAttributeLabel("seal_type_id"),'seal_type_id',array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                    <div class="col-lg-6">
                        <?php
                        echo TbHtml::textField("seal_type_id",CGetName::getSealTypeStrByIDs($model->seal_type_id),array(
                            'readonly'=>true
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'rptFileJson',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-8">
                    <?php
                    $this->renderPartial('//clueRpt/from_table',array(
                        "model"=>$model,
                        "form"=>$form,
                        "fileJson"=>$model->rptFileJson,
                        "valueStr"=>"rptFileJson"
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'contFileJson',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-8">
                    <?php
                    $this->renderPartial('//clueRpt/from_table',array(
                        "model"=>$model,
                        "form"=>$form,
                        "fileJson"=>$model->contFileJson,
                        "valueStr"=>"contFileJson"
                    ));
                    ?>
                </div>
            </div>
		</div>
	</div>
</section>

<?php

$js = <<<EOF
function formatState(state) {
	var rtn = $('<span style="color:black">'+state.text+'</span>');
	return rtn;
}
EOF;
Yii::app()->clientScript->registerScript('formatState',$js,CClientScript::POS_READY);
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
$mHModel = new CMHCurlModel();
$js = "
    window.saveData=function(e){
        //console.log(e);
        var messageObj = {
            type: 'saveData',
            state: true,
            message: '验证消息',
            sysCode:'{$mHModel->sysCode}',
            businessKey:'{$model->id}'
        };
        hotent.sendMessage(messageObj);
    }
    ";
Yii::app()->clientScript->registerScript('mhCheckFunction',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

<?php
$this->renderPartial("//lookFile/lookFileDialog",array("lookUrl"=>Yii::app()->createUrl('lookFile/rpt')));
?>
