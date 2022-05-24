<?php
$this->pageTitle=Yii::app()->name . ' - StopSearch';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'stopSearch-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Stop Customer Search'); ?></strong>
	</h1>
<!--
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Layout</a></li>
		<li class="active">Top Navigation</li>
	</ol>
-->
</section>
<?php
    echo TbHtml::button("test",array('class'=>'hide','submit'=>'#'));
?>

<section class="content">
    <?php
    $search=array(
        'company_name',
        'status_dt',
        'back_date',
        'back_name',
        'description',
        'salesman',
    );
    if(!Yii::app()->user->isSingleCity()){
        $search[]="city";
    }
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('customer','Stop Customer Search List'),
        'model'=>$model,
        'viewhdr'=>'//stopSearch/_listhdr',
        'viewdtl'=>'//stopSearch/_listdtl',
        'gridsize'=>'24',
        'height'=>'600',
        'search'=>$search,
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
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
