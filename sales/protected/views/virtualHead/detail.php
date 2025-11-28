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
        $backUrl = Yii::app()->createUrl('virtualHead/index');
        echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>$backUrl));
		?>
	</div>
            <div class="btn-group pull-right" role="group">
                <?php if (!empty($model->id)): ?>
                    <?php
                    if(in_array($model->vir_status,array(10,30))){
                        echo TbHtml::button(Yii::t("clue","Cont Amend"), array(
                            "color"=>TbHtml::BUTTON_COLOR_PRIMARY,
                            "class"=>"btn-cont",
                            "data-url"=>Yii::app()->createUrl('virtualBatch/new',array("type"=>"A")),
                        ));
                        echo TbHtml::button(Yii::t("clue","Cont Suspend"), array(
                            "color"=>TbHtml::BUTTON_COLOR_PRIMARY,
                            "class"=>"btn-cont",
                            "data-url"=>Yii::app()->createUrl('virtualBatch/new',array("type"=>"S")),
                        ));
                    }
                    if(in_array($model->vir_status,array(10,30,40))){
                        echo TbHtml::button(Yii::t("clue","Cont Terminate"), array(
                            "color"=>TbHtml::BUTTON_COLOR_PRIMARY,
                            "class"=>"btn-cont",
                            "data-url"=>Yii::app()->createUrl('virtualBatch/new',array("type"=>"T")),
                        ));
                    }
                    ?>
                    <?php
                    if(in_array($model->vir_status,array(40,50))){
                        echo TbHtml::button(Yii::t("clue","Cont Resume"), array(
                            "color"=>TbHtml::BUTTON_COLOR_PRIMARY,
                            "class"=>"btn-cont",
                            "data-url"=>Yii::app()->createUrl('virtualBatch/new',array("type"=>"R")),
                        ));
                    }
                    echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('clue','Contract traceability'), array(
                            'data-toggle'=>'modal','data-target'=>'#virTraceDialog',)
                    );
                    ?>
                <?php endif ?>
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

            <?php $this->renderPartial('//virtual/vir_form_A',array("model"=>$model,"form"=>$form)); ?>
            <?php $this->renderPartial('//virtual/vir_form_B',array("model"=>$model,"form"=>$form)); ?>
            <?php $this->renderPartial('//virtual/vir_form_C',array("model"=>$model,"form"=>$form)); ?>
            <?php $this->renderPartial('//virtual/vir_form_D',array("model"=>$model,"form"=>$form)); ?>
            <?php $this->renderPartial('//virtual/vir_form_E',array("model"=>$model,"form"=>$form)); ?>


        </div>
    </div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//virtualHead/virTrace',array("model"=>$model)); ?>
<?php
$js = Script::genDeleteData(Yii::app()->createUrl('virtualHead/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = <<<EOF
function formatState(state) {
	var rtn = $('<span style="color:black">'+state.text+'</span>');
	return rtn;
}

    $('.btn-cont').on('click',function(){
        var url = $(this).data('url');
        var check_id = '{$model->id}';
        if(check_id==''){
            showFormErrorHtml('请选择虚拟合约');
        }else{
            url+='&check_id='+check_id;
            window.location.href=url;
        }
    });
EOF;
Yii::app()->clientScript->registerScript('formatState',$js,CClientScript::POS_READY);
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

<?php $this->renderPartial("//clue/errorDialog");?>
