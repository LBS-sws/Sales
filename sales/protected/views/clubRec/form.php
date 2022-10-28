<?php
$this->pageTitle=Yii::app()->name . ' - ClubRec Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'ClubRec-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('club','Club recommend form'); ?></strong>
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
		<?php
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('clubRec/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('clubRec/index')));
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('clubRec/save')));
			?>
<?php endif ?>
<?php if ($model->scenario!='new' && $model->scenario!='view'): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>

			<div class="form-group">
                <?php echo $form->labelEx($model,'rec_year',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->dropDownList($model, 'rec_year',ClubSalesList::getYearList(),
                        array("id"=>"year",'readonly'=>($model->scenario=='view')));
                    ?>
                </div>
			</div>

			<div class="form-group">
                <?php echo $form->labelEx($model,'month_type',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->dropDownList($model, 'month_type',ClubSalesList::getMothTypeList(),
                        array("id"=>"month_type",'readonly'=>($model->scenario=='view')));
                    ?>
                </div>
			</div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'employee_id',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-4">
                    <?php echo $form->dropDownList($model, 'employee_id',ClubRecForm::getClubRecStaffList($model->employee_id,$model->rec_year,$model->month_type),
                        array('id'=>'employee_id','readonly'=>($model->scenario=='view')));
                    ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'rec_remark',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-5">
                    <?php echo $form->textArea($model, 'rec_remark',
                        array('readonly'=>($model->scenario=='view'),'id'=>'rec_remark','rows'=>4));
                    ?>
                </div>
            </div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<script type="text/javascript">
$(function () {
    $('#year,#month_type').change(function(){
        var year = $('#year').val();
        var month_type = $('#month_type').val();
        $.ajax({
            type: 'POST',
            url: '<?php echo Yii::app()->createAbsoluteUrl("clubRec/ajaxEmployee");?>',
            data: { year:year,month_type:month_type},
            success: function(data) {
                if (data.status==1) {
                    $('#employee_id').html(data.html);
                }
            },
            error: function(xhr, status, error) { // if error occured
                var err = eval("(" + xhr.responseText + ")");
                console.log(err.Message);
            },
            dataType:'json'
        });
    });
});
</script>
<?php
$js = Script::genDeleteData(Yii::app()->createUrl('clubRec/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


