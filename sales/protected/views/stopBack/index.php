<?php
$this->pageTitle=Yii::app()->name . ' - StopBack';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'stopBack-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Stop Customer Back'); ?></strong>
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
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('app','Stop Customer Back').Yii::t('customer',' List'),
        'model'=>$model,
        'viewhdr'=>'//stopBack/_listhdr',
        'viewdtl'=>'//stopBack/_listdtl',
        'gridsize'=>'24',
        'height'=>'600',
        'search'=>array(
            'company_name',
            'status_dt',
            'back_date',
            'back_name',
            'description',
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
$updateAjaxUrl = Yii::app()->createUrl('stopBack/updateAjaxVip');
$js ="
$('.updateVip').on('click',function(e){
    e.stopPropagation();
    var id = $(this).data('id');
    var that = this;
    $.post('{$updateAjaxUrl}', { service_id: id, time: Date.now() },function(data){
        console.log(data);
        if(data.status==1){
            $(that).children('span:first').attr('class',data.message);
        }else{
            alert(data.message);
        }
    },'json');
});
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
