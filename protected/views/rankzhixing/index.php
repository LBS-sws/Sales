<?php
$this->pageTitle=Yii::app()->name . ' - rank';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'rank-form',
    'action'=>Yii::app()->createUrl('rank/index_s'),
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('sales','Sales Rank history'); ?></strong>
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
                <?php echo TbHtml::button(Yii::t('misc','新增赛季'), array(
                    'submit'=>Yii::app()->createUrl('rankzhixing/newsaiji')));
                ?>
            </div>
            <div class="btn-group" role="group">
                <?php echo TbHtml::button(Yii::t('misc','新增赛季当前人员'), array(
                    'submit'=>Yii::app()->createUrl('rankzhixing/newpeople')));
                ?>
            </div>
            <div class="btn-group" role="group">
                <?php echo TbHtml::button(Yii::t('misc','保存数据'), array(
                    'submit'=>Yii::app()->createUrl('rankzhixing/save')));
                ?>
            </div>
            <?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','清空所有数据'), array(
                    'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
            );
            ?>
        </div></div>
    <div class="box box-info">
        <div class="box-body">
            <!--			--><?php //echo $form->hiddenField($model, 'id'); ?>
            <!--			--><?php //echo $form->hiddenField($model, 'name'); ?>
            <!--			--><?php //echo $form->hiddenField($model, 'fields'); ?>
            <!--			--><?php //echo $form->hiddenField($model, 'form'); ?>

            <div class="form-group">
                <div class="col-sm-3">
                    <div class="input-group date">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <?php echo $form->textField($model, 'zhi_dt', array('class'=>'form-control pull-right','readonly'=>(''))); ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<?php $this->renderPartial('//site/removedialog'); ?>
<?php
$url=Yii::app()->createUrl('report/city');
$js = <<<EOF

EOF;
?>
<?php
Yii::app()->clientScript->registerScript('changestyle',$js,CClientScript::POS_READY);
$js = Script::genLookupSearchEx();
Yii::app()->clientScript->registerScript('lookupSearch',$js,CClientScript::POS_READY);
$js = Script::genDeleteData(Yii::app()->createUrl('rankzhixing/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);
$js = Script::genLookupButtonEx('btnStaff', 'staff', 'staffs', 'staffs_desc',
    array(),
    true
);
Yii::app()->clientScript->registerScript('lookupStaffs',$js,CClientScript::POS_READY);

$js = Script::genLookupSelect();
Yii::app()->clientScript->registerScript('lookupSelect',$js,CClientScript::POS_READY);

$js = Script::genDatePicker(array(
    'ReportXS01Form_start_dt',
    'ReportXS01Form_end_dt',
));
Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

</div><!-- form -->

