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
    .cont_detail .form-group{ margin-bottom: 5px;}

    .information-header>h4{ padding-top: 0px;margin-top: 0px;}
    .information-hide{ display: none;}
    .win_sse_form{ position: relative;}
    .win_sse_form>td:before{ content: "...";position: absolute;left: 0px;top: 0px;height: 15px;line-height: 15px;}
    .win_sse_form.active>td:before{ content: "";height: 0px;}
    .win_sse_form>td>.col-lg-12{ float:left;width:100%;height: 2px;overflow: hidden;}
    .win_sse_form.active>td>.col-lg-12{ height: auto;overflow: visible;}
    .compare-bottom-div{ position: fixed;bottom: 10px;right: 10px;width: 420px;max-height: 400px;box-shadow: 0 1px 1px rgba(0,0,0,0.1);background: #fff;overflow-y: scroll;z-index: 2}

    @media (min-width: 1200px){
        .col-lg-right{ padding-right: 0px;}
        .col-lg-left{ padding-left: 0px;}
    }
</style>
<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('clue','Virtual Form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php
        $session = Yii::app()->session;
        $backUrl = Yii::app()->createUrl('virtualHead/detail',array("index"=>$model->vir_id));
        echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>$backUrl));
		?>
	</div>
	</div></div>

    <div class="row">
        <div class="col-lg-12 cont_detail">
            <?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo $form->hiddenField($model, 'clue_id'); ?>
            <?php echo $form->hiddenField($model, 'clue_type'); ?>
            <?php echo $form->hiddenField($model, 'clue_store_id'); ?>
            <?php echo $form->hiddenField($model, 'clue_service_id'); ?>
            <?php echo $form->hiddenField($model, 'city'); ?>
            <?php echo $form->hiddenField($model, 'mh_id'); ?>
            <?php echo $form->hiddenField($model, 'vir_status'); ?>
            <?php echo $form->hiddenField($model, 'busine_id_text'); ?>

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
                        <?php echo TbHtml::label($model->getAttributeLabel('pro_code'),"pro_code",array('class'=>"col-lg-1 control-label")); ?>
                        <div class="col-lg-3">
                            <?php
                            echo $form->textField($model,"pro_code",array(
                                'readonly'=>true,'id'=>'pro_code'
                            ));
                            ?>
                        </div>
                    </div>
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

            <?php $this->renderPartial('//virtual/vir_form_A',array("model"=>$model,"form"=>$form)); ?>
            <?php $this->renderPartial('//virtual/vir_form_B',array("model"=>$model,"form"=>$form)); ?>
            <?php $this->renderPartial('//virtual/vir_form_C',array("model"=>$model,"form"=>$form)); ?>
            <?php $this->renderPartial('//virtual/vir_form_D',array("model"=>$model,"form"=>$form)); ?>


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
?>

<?php $this->endWidget(); ?>

<?php echo $model->printCompareHtml();?>
<?php $this->renderPartial("//clue/errorDialog");?>
