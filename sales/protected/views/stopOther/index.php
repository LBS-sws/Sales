<?php
$this->pageTitle=Yii::app()->name . ' - StopOther';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'stopOther-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Stop Customer Other'); ?></strong>
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
    <?php if (Yii::app()->user->validRWFunction('SC02')): ?>
    <div class="box"><div class="box-body">
            <div class="btn-group">
                <div class="btn-group">
                    <?php
                    echo TbHtml::button(Yii::t('misc','Batch allocation'), array(
                        'submit'=>Yii::app()->createUrl('stopOther/shiftAll')));
                    ?>
                </div>
                <div class="btn-group">
                    <select class="form-control" name="StopOtherForm[shiftStaff]">
                        <option value=""><?php echo Yii::t('report','Please select the assigned person');?></option>
                        <?php foreach ($saleman as $v) {?>
                            <option value="<?php echo $v['id'];?>"><?php echo $v['name'];?> </option>
                        <?php }?>
                    </select>
                </div>
            </div>
        </div></div>
    <?php endif ?>
    <?php
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('app','Stop Customer Other').Yii::t('customer',' List'),
        'model'=>$model,
        'viewhdr'=>'//stopOther/_listhdr',
        'viewdtl'=>'//stopOther/_listdtl',
        'gridsize'=>'24',
        'height'=>'600',
        'search'=>array(
            'company_name',
            'cont_info',
            'service',
            'salesman',
        ),
    ));
    ?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
?>
<?php $this->endWidget(); ?>

<?php
$js ="
$('.checkOne').on('click',function(e){
    e.stopPropagation();
});
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
