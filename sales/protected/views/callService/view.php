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

    .information-header>h4{ padding-top: 0px;margin-top: 0px;}
    .information-hide{ display: none;}
    .win_sse_form{ position: relative;}
    .win_sse_form>td:before{ content: "...";position: absolute;left: 0px;top: 0px;height: 15px;line-height: 15px;}
    .win_sse_form.active>td:before{ content: "";height: 0px;}
    .win_sse_form>td>.col-lg-12{ float:left;width:100%;height: 2px;overflow: hidden;}
    .win_sse_form.active>td>.col-lg-12{ height: auto;overflow: visible;}
    .call-alert{ border-radius: 3px;margin-top: 5px;padding:5px 10px;border: 1px solid rgba(245, 154, 35, 1);background: rgba(245, 154, 35, 0.19607843137254902);}
    .call-alert>.fa{ color:rgba(245, 154, 35, 1);padding-right: 4px;}
    .free-year{ display: inline-block;padding: 0px 15px;font-weight: bold;margin-top: 10px;}
    .free-month-text{ text-align: center;padding: 5px 0px;}
    .free-month-input>input{ border: none;text-align: center;}
    .free-month{display: inline-block;}
    .free-month-text,.free-month-input{border: 1px solid #ccc;}
    .disabled .free-month-text{background: #eee;}
    .active .free-month-text{background: #337ab7;color:#fff;border-color: #337ab7;}
    .active .free-month-input{border-color: #337ab7;}
    .active .form-control[readonly]{background: #fff;}
    @media (min-width: 1200px){
        .col-lg-right{ padding-right: 0px;}
        .col-lg-left{ padding-left: 0px;}
    }
</style>
<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('clue','call service'); ?></strong>
        <?php
        $url=Yii::app()->createUrl('callService/edit',array("index"=>$model->id,"token"=>isset($token)?$token:""));
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
            <?php echo $form->hiddenField($model, 'clue_id'); ?>
            <?php echo $form->hiddenField($model, 'clue_type'); ?>
            <?php echo $form->hiddenField($model, 'cont_id',array("id"=>"cont_id")); ?>
            <?php echo $form->hiddenField($model, 'store_ids',array("id"=>"store_ids",'data-old'=>$model->store_ids)); ?>
            <?php echo $form->hiddenField($model, 'apply_date',array("id"=>"apply_date")); ?>

            <?php $this->renderPartial('//callService/call_form',array("model"=>$model,"form"=>$form)); ?>

        </div>
    </div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php
$js = Script::genDeleteData(Yii::app()->createUrl('callService/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

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
<?php $this->renderPartial("//clue/openForm");?>
<?php $this->renderPartial("//clue/errorDialog");?>
