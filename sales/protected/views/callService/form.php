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
		<strong><?php echo Yii::t("clue","call service"); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php
        $session = Yii::app()->session;
        if(empty($model->id)){
            $backUrl = Yii::app()->createUrl('contHead/detail',array("index"=>$model->cont_id));
        }else{
            $backUrl = Yii::app()->createUrl('callService/index');
        }
        echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'name'=>'btnBack','submit'=>$backUrl));
		?>
        <?php if ($model->scenario!='view'&&in_array($model->call_status,array(0,9))): ?>
            <?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('clue','draft'), array(
                'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                'submit'=>Yii::app()->createUrl('callService/save',array('type'=>'draft'))));
            ?>
            <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('clue','need audit'), array(
                'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                'submit'=>Yii::app()->createUrl('callService/save',array('type'=>'audit'))));
            ?>
            <?php if ($model->scenario!='new'): ?>
                <?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
                    'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                    'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog'));
                ?>
            <?php endif ?>
        <?php endif ?>
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
?>

<?php $this->endWidget(); ?>
<?php $this->renderPartial("//clue/openForm");?>
<?php $this->renderPartial("//clue/errorDialog");?>
