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
    @media (min-width: 1200px){
        .col-lg-right{ padding-right: 0px;}
        .col-lg-left{ padding-left: 0px;}
    }
</style>
<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('clue','Virtual Form')." (".CGetName::getProTypeStrByKey($model->pro_type).")"; ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php
        $session = Yii::app()->session;
        if(empty($model->id)){
            $backUrl = Yii::app()->createUrl('virtualHead/index');
        }else{
            $backUrl = Yii::app()->createUrl('virtualBatch/index');
        }
        echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'name'=>'btnBack','submit'=>$backUrl));
		?>
        <?php if ($model->scenario!='view'&&in_array($model->pro_status,array(0,9))): ?>
            <?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('clue','draft'), array(
                'submit'=>Yii::app()->createUrl('virtualBatch/save',array('type'=>'draft'))));
            ?>
            <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('clue','need audit'), array(
                'submit'=>Yii::app()->createUrl('virtualBatch/save',array('type'=>'audit'))));
            ?>
            <?php if ($model->scenario!='new'): ?>
                <?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
                    'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog'));
                ?>
            <?php endif ?>
        <?php endif ?>

        <?php
        if($model->pro_status==19){
            echo TbHtml::button("上传印章文件",array('data-toggle'=>'modal','data-target'=>'#open-seal-Dialog','color'=>TbHtml::BUTTON_COLOR_PRIMARY));
        }
        ?>
	</div>
            <div class="btn-group pull-right" role="group">
                <?php
                if(!empty($model->mh_id)){
                    echo TbHtml::link(Yii::t("clue","link mh"),CGetName::getMHUrlByClueRptMHID($model->mh_id),array(
                        "class"=>"btn btn-default",
                        "target"=>"_blank",
                    ));
                }
                ?>
            </div>
	</div></div>

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

            <?php $this->renderPartial('//virtualBatch/pro_form',array("model"=>$model,"form"=>$form)); ?>

            <?php if ($model->pro_type=="T"): ?>
                <?php $this->renderPartial('//virtualBatch/vir_form_back',array("model"=>$model,"form"=>$form)); ?>
            <?php endif ?>
            <?php $this->renderPartial('//virtualBatch/vir_form',array("model"=>$model,"form"=>$form)); ?>
        </div>
    </div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php if (!$model->isReadonly()&&$model->pro_type=="A"): ?>
    <?php $this->renderPartial('//cont/serviceFre'); ?>
<?php endif ?>
<?php
$js = Script::genDeleteData(Yii::app()->createUrl('virtualBatch/delete'));
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
?>

<?php $this->endWidget(); ?>

<?php
if($model->pro_status==19){
    $this->renderPartial("//cont/sealDialog",array("model"=>$model));
}
?>
<?php
$this->renderPartial("//lookFile/lookFileDialog");
?>
<?php $this->renderPartial("//clue/errorDialog");?>
