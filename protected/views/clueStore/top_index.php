<?php
$this->pageTitle=Yii::app()->name . ' - Clue Store';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>
<style>
    .select2.select2-container{ width: 100%!important;}
    .select2-container--default .select2-selection--multiple .select2-selection__choice{ padding: 0px 7px;}
    .select2-container .select2-selection--single{ height: 34px;}
</style>

<section class="content-header">
	<h1>
		<strong>
            <?php
            echo Yii::t('app','Clue Store');
            ?>
        </strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
        <?php
        if (Yii::app()->user->validRWFunction('CM02')){
            echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('clue','add store'), array(
                    'data-toggle'=>'modal','data-target'=>'#selectClueFormDialog')
            );
        }
        ?>
	</div>
            <?php if (Yii::app()->user->validRWFunction('CM02')): ?>
                <div class="btn-group pull-right" role="group">
                    <?php
                    echo TbHtml::button(Yii::t('clue','import clue store'), array(
                            'data-toggle'=>'modal','data-target'=>'#importClueDialog','data-type'=>'clueStore')
                    );
                    ?>
                </div>
            <?php endif ?>
	</div></div>
	<?php
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('clue','store list'),
        'model'=>$model,
        'viewhdr'=>'//clueStore/top_listhdr',
        'viewdtl'=>'//clueStore/top_listdtl',
        'advancedSearch'=>true,
    ));
	?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
	echo $form->hiddenField($model,'filter');
?>
<?php $this->endWidget(); ?>

<?php
$js = "
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>

<?php
$this->renderPartial('//clueStore/select_clue',array(
    "model"=>$model,
));

$this->renderPartial('//clue/importClueDialog',array("importType"=>"clueStore"));
?>