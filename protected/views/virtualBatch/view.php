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
    body{ background: #ecf0f5;}
    .select2-container.select2-container-disabled .select2-choice {
        background-color: #ddd;
        border-color: #a8a8a8;
    }
    select.readonly{ pointer-events: none;}
    select[readonly]{pointer-events: none;}
    .select2.select2-container{ width: 100%!important;}
    .select2-container--default .select2-selection--multiple .select2-selection__choice{ padding: 0px 7px;}
    .select2-container .select2-selection--single{ height: 34px;}

    .information-header>h4{ padding-top: 0px;margin-top: 0px;}
    .information-hide{ display: none;}
    .win_sse_form{ position: relative;}
    .win_sse_form>td:before{ content: "...";position: absolute;left: 0px;top: 0px;height: 15px;line-height: 15px;}
    .win_sse_form.active>td:before{ content: "";height: 0px;}
    .win_sse_form>td>.col-lg-12{ float:left;width:100%;height: 2px;overflow: hidden;}
    .win_sse_form.active>td>.col-lg-12{ height: auto;overflow: visible;}
    @media (min-width: 1200px){
        .col-lg-right{ padding-right: 0px;}
        .col-lg-left{ padding-left: 0px;}
    }
</style>
<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('clue','Contract Form')." (".CGetName::getProTypeStrByKey($model->pro_type).")"; ?></strong>
        <?php
        $url=Yii::app()->createUrl('virtualBatch/edit',array("index"=>$model->id,"token"=>isset($token)?$token:""));
        echo TbHtml::link("返回CRM系统",$url,array(
            "target"=>"_blank",
            "class"=>"btn btn-primary"
        ));
        ?>
    </h1>
</section>

<section class="content">

    <div class="row">
        <div class="col-lg-12">
            <?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo $form->hiddenField($model, 'city'); ?>
            <?php echo $form->hiddenField($model, 'mh_id'); ?>
            <?php echo $form->hiddenField($model, 'vir_id'); ?>
            <?php echo $form->hiddenField($model, 'vir_id_text'); ?>
            <?php echo $form->hiddenField($model, 'busine_id_text'); ?>
            <?php echo $form->hiddenField($model, 'serviceJson',array("id"=>"serviceJson")); ?>

            <?php $this->renderPartial('//contPro/compute_form',array("model"=>$model)); ?>

            <?php $this->renderPartial('//virtualBatch/pro_form',array("model"=>$model,"form"=>$form)); ?>

            <?php if ($model->pro_type=="T"): ?>
                <?php $this->renderPartial('//virtualBatch/vir_form_back',array("model"=>$model,"form"=>$form)); ?>
            <?php endif ?>
            <?php $this->renderPartial('//virtualBatch/vir_form',array("model"=>$model,"form"=>$form)); ?>
        </div>
    </div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//cont/historylist',array("model"=>$model,"type"=>5)); ?>
<?php
$js = Script::genDeleteData(Yii::app()->createUrl('contHead/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = <<<EOF
function formatState(state) {
	var rtn = $('<span style="color:black">'+state.text+'</span>');
	return rtn;
}
if($("#compareTable").find("tr[data-key='service_fre_text']").length>0){
    $('#freeHintDiv').removeClass('hide');
}
EOF;
Yii::app()->clientScript->registerScript('formatState',$js,CClientScript::POS_READY);
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
$mHModel = new CMHCurlModel();
$state = $seal==true?"true":"false";
$message = $seal==true?"验证消息":"请前往销售系统上传盖章文件";
$js = "
    window.saveData=function(e){
        //console.log(e);
        var messageObj = {
            type: 'saveData',
            state: {$state},
            message: '{$message}',
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
$this->renderPartial("//lookFile/lookFileDialog");
?>
<?php $this->renderPartial("//clue/errorDialog");?>
