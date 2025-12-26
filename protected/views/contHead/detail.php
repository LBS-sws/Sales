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
		<strong><?php echo Yii::t('clue','Contract Form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php
        $session = Yii::app()->session;
        $backUrl = Yii::app()->createUrl('contHead/index');
        echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>$backUrl));
		?>

        <?php
        if($model->cont_status==19){
            echo TbHtml::button("上传印章文件",array('data-toggle'=>'modal','data-target'=>'#open-seal-Dialog','color'=>TbHtml::BUTTON_COLOR_PRIMARY));
        }
        ?>
	</div>
            <div class="btn-group pull-right" role="group">
                <?php if (in_array($model->cont_status,array(10,30))): ?>
                    <?php
                    echo TbHtml::button(Yii::t("clue","Cont Renew"), array(
                        'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                        'submit'=>Yii::app()->createUrl('contPro/new',array("cont_id"=>$model->id))
                    ));
                    echo TbHtml::button(Yii::t("clue","Cont Amend"), array(
                        'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                        'submit'=>Yii::app()->createUrl('contPro/new',array("cont_id"=>$model->id,"type"=>"A"))
                    ));
                    ?>
                <?php endif ?>
                <?php
                // 合并删除按钮（仅草稿状态可见）
                if(in_array($model->cont_status,array(0,9))){
                    echo TbHtml::button('<span class="fa fa-trash"></span> 合并删除', array(
                        'color'=>TbHtml::BUTTON_COLOR_DANGER,
                        'submit'=>Yii::app()->createUrl('contHead/merge',array("clue_id"=>$model->clue_id))
                    ));
                }
                ?>
                <?php
                if(in_array($model->cont_status,array(0,9))){
                    echo TbHtml::button('<span class="fa fa-edit"></span> '.Yii::t('clue','update'), array(
                            'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                        'submit'=>Yii::app()->createUrl('contHead/edit',array("index"=>$model->id))));
                }else{
                    echo TbHtml::link(Yii::t("clue","link mh"),CGetName::getMHUrlByClueRptMHID($model->mh_id),array(
                        "class"=>"btn btn-default",
                        "target"=>"_blank",
                    ));
                }
                ?>
            </div>
	</div></div>

    <div class="row">
        <div class="col-lg-12 cont_detail">
            <?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo $form->hiddenField($model, 'clue_id'); ?>
            <?php echo $form->hiddenField($model, 'clue_type'); ?>
            <?php echo $form->hiddenField($model, 'clue_service_id'); ?>
            <?php echo $form->hiddenField($model, 'city'); ?>
            <?php echo $form->hiddenField($model, 'mh_id'); ?>
            <?php echo $form->hiddenField($model, 'serviceJson',array("id"=>"serviceJson")); ?>

            <div class="box box-info">
                <div class="box-body">
                    <?php
                    $tabs=array();
                    //基本资料
                    $tabs[] = array(
                        'label'=>Yii::t('clue',"Basic Information"),
                        'content'=>$this->renderPartial('//contHead/dv_detail',array("model"=>$model,"form"=>$form),true),
                        'active'=>true,
                        "id"=>"cont_dv_basic"
                    );
                    //关联门店
                    $tabs[] = array(
                        'label'=>Yii::t('clue',"clue service store"),
                        'content'=>$this->renderPartial('//contHead/dv_store',array("model"=>$model,"form"=>$form),true),
                        'active'=>false,
                        "id"=>"cont_dv_store"
                    );
                    //操作记录
                    $tabs[] = array(
                        'label'=>Yii::t('clue',"client operation"),
                        'content'=>$this->renderPartial('//contHead/dv_operation',array("model"=>$model,"form"=>$form),true),
                        'active'=>false,
                        "id"=>"cont_dv_operation"
                    );
                    //合同追溯
                    $tabs[] = array(
                        'label'=>Yii::t('clue',"Contract traceability"),
                        'content'=>$this->renderPartial('//contHead/dv_trace',array("model"=>$model,"form"=>$form),true),
                        'active'=>false,
                        "id"=>"cont_dv_trace"
                    );
                    echo TbHtml::tabbableTabs($tabs);
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php
$js = Script::genDeleteData(Yii::app()->createUrl('contHead/delete'));
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
if($model->cont_status==19){
    $this->renderPartial("//cont/sealDialog",array("model"=>$model));
}
?>
<?php
$this->renderPartial("//lookFile/lookFileDialog");
?>
<?php $this->renderPartial("//clue/errorDialog");?>
