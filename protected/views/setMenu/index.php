<?php
$this->pageTitle=Yii::app()->name . ' - Visit Type';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','menu setting'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if (Yii::app()->user->validRWFunction('HC21'))
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
					'submit'=>Yii::app()->createUrl('setMenu/new'),
				)); 
		?>
	</div>
	</div></div>
	<?php
    $modelName = get_class($model);
    $addHtml = TbHtml::dropDownList("{$modelName}[set_type]",$model->set_type,CGetName::getSetMenuList(),array("class"=>"btn_submit","empty"=>"-- 全部 --"));
    $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('app','menu setting'),
			'model'=>$model,
				'viewhdr'=>'//setMenu/_listhdr',
				'viewdtl'=>'//setMenu/_listdtl',
				'search_add_html'=>$addHtml,
				'search'=>array(
							'name',
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
$url = Yii::app()->createUrl('setMenu/index',array("pageNum"=>1));
$js = "
    $('.btn_submit').on('change',function(){
        jQuery.yii.submitForm(this,'{$url}',{});
    });
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
