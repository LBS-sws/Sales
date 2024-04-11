<?php
$this->pageTitle=Yii::app()->name . ' - RA Statistic Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'CAStatistic-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','RA Statistic'); ?></strong>
	</h1>
<!--
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Layout</a></li>
		<li class="active">Top Navigation</li>
	</ol>
-->
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
        <?php echo TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('ka','Enquiry'), array(
            'submit'=>Yii::app()->createUrl('cAStatistic/view')));
        ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>

            <div class="form-group">
                <?php echo $form->labelEx($model,'search_year',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->dropDownList($model, 'search_year',CAStatisticForm::getYearList(),
                        array('readonly'=>false)
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'search_month',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->dropDownList($model, 'search_month',CAStatisticForm::getMonthList(),
                        array('readonly'=>false)
                    ); ?>
                </div>
            </div>
		</div>
	</div>
</section>


<?php
$js="
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
$js = Script::genDatePicker(array(
    'CAStatisticForm_start_date',
    'CAStatisticForm_end_date'
));
Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


