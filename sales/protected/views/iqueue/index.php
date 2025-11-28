<?php
$this->pageTitle=Yii::app()->name . ' - Import Manager';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'queue-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('import','Import Manager'); ?></strong> 

	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
				echo TbHtml::button('<span class="fa fa-refresh"></span> '.Yii::t('misc','Refresh'), array(
					'submit'=>Yii::app()->createUrl('iqueue/index'), 
				)); 
		?>
	</div>
            <div class="pull-right">
                <p style="margin: 7px 0px;">未进行：P，完成：C，错误：E。</p>
            </div>
	</div></div>
	<?php $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('queue','Queue List'),
			'model'=>$model,
				'viewhdr'=>'//iqueue/_listhdr',
				'viewdtl'=>'//iqueue/_listdtl',
				'search'=>array(
							'import_type',
							'import_name',
							'status',
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

<?php
	$link = Yii::app()->createUrl('iqueue/downExcel');
	$js = <<<EOF
function downExcel(id,type) {
    var url = '{$link}';
    url+='?index='+id+'&type='+type;
    window.location.href=url;
}
EOF;
	Yii::app()->clientScript->registerScript('downExcel',$js,CClientScript::POS_HEAD);
?>

<?php $this->endWidget(); ?>

