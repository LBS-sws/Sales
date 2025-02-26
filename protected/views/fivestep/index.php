<?php
$this->pageTitle=Yii::app()->name . ' - Five Steps';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'fivestep-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('sales','Five Steps'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if (Yii::app()->user->validRWFunction('HK03'))
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
					'submit'=>Yii::app()->createUrl('fivestep/new'), 
				)); 
		?>
	</div>
	</div></div>
	<?php 
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('sales','Five Steps List'),
			'model'=>$model,
				'viewhdr'=>'//fivestep/_listhdr',
				'viewdtl'=>'//fivestep/_listdtl',
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
<?php $this->renderPartial('//fivestep/dtlview',array('model'=>$model)); ?>
<?php $this->endWidget(); ?>

<?php
$link = Yii::app()->createAbsoluteUrl("fivestep");
$msg = Yii::t('sales','Loading...');
$link2 = TbHtml::link(Yii::t('sales','Download Media'), Yii::app()->createUrl('fivestep/downloadmedia',array('index'=>'idx')));
$js = <<<EOF
function showmedia(id) {
	var data = "index="+id;
	var link = "$link"+"/showmedia";
	$.ajax({
		type: 'GET',
		url: link,
		data: data,
		beforeSend: function() {
			$("#video_here").empty();
			$("#video_here").load();
			$("#mediafile").html('$msg');
			$('#dtlviewdialog').modal('show');
		},
		success: function(data) {
			$("#video_here").html(data);
			$("#video_here").load();
			var str = '$link2';
			var link2 = str.replace(/idx/g,id);
			$("#mediafile").html(link2);
		},
		error: function(data) { // if error occured
			alert("Error occured.please try again");
		},
		dataType:'html'
	});
}
EOF;
Yii::app()->clientScript->registerScript('mediaview',$js,CClientScript::POS_HEAD);

$js = Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>


