<?php
$this->pageTitle=Yii::app()->name . ' - Clue Box Form';
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
    @media (min-width: 1200px){
        .col-lg-right{ padding-right: 0px;}
        .col-lg-left{ padding-left: 0px;}
    }
    .bat_phone_div_click.open{ }
    .bat_phone_div_click{ border-top:1px solid #d2d6de;padding-bottom:4px;}
    .bat_phone_div_click span{ display:inline-block;border:1px solid #d2d6de; padding:7px 12px;}
</style>
<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('clue','Clue Form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
                    'name'=>'btnAdd','id'=>'btnAdd','data-toggle'=>'modal','data-target'=>'#clueDialog'));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('clueBox/index'))); 
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('clueBox/save')));
			?>
<?php endif ?>
<?php if ($model->scenario=='edit'): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
            <?php if ($model->scenario!='new'): ?>
                <div class="btn-group pull-right" role="group">
                    <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('clue','Clue History'), array(
                            'data-toggle'=>'modal','data-target'=>'#clueHistoryDialog',)
                    );
                    ?>
                    <?php echo TbHtml::button('<span class="fa fa-level-down"></span> '.Yii::t('clue','assign'), array(
                            'data-toggle'=>'modal','data-target'=>'#clueAssignDialog',)
                    );
                    ?>
                </div>
            <?php endif ?>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
            <?php
            $this->renderPartial("//clue/clue_form",array("form"=>$form,"model"=>$model));
            ?>

			
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//clue/historylist',array("model"=>$model)); ?>
<?php
$js = Script::genDeleteData(Yii::app()->createUrl('clueBox/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);


$js = <<<EOF
function formatState(state) {
	var rtn = $('<span style="color:black">'+state.text+'</span>');
	return rtn;
}
EOF;
$js.= Script::genDatePicker(array(
    'entry_date',
));
Yii::app()->clientScript->registerScript('formatState',$js,CClientScript::POS_READY);
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>
<?php
echo TbHtml::button("",array("submit"=>"","class"=>"hide"));
$this->renderPartial('//clue/select_clue',array("actionUrl"=>Yii::app()->createUrl('clueBox/new')));
$this->renderPartial('//clue/clue_assign',array("actionUrl"=>Yii::app()->createUrl('clueBox/assign'),"assignCity"=>$model->city));
?>
<?php
$this->renderPartial('//clue/map_baidu',array(
    "model"=>$model,
));
?>
<?php
$this->renderPartial('//clue/nationalArea');
?>


