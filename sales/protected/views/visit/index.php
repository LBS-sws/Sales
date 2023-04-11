<?php
$this->pageTitle=Yii::app()->name . ' - Sales Visit';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'visit-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('sales','Sales Visit'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if (Yii::app()->user->validRWFunction('HK01'))
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
					'submit'=>Yii::app()->createUrl('visit/new'), 
				)); 
		?>
		<?php echo TbHtml::button('<span class="fa fa-file-text-o"></span> '.Yii::t('sales','Report'), array(
				'submit'=>Yii::app()->createUrl('visit/report')));
		?>
	</div>
	<div class="btn-group" role="group">
		<span class="text-red">
			<?php 
				echo Yii::t('sales','Note: Please set searching criteria before pressing Report button, otherwise all data will be generated in the report.'); 
				echo CHtml::link(' ('.Yii::t('sales','Example').')','#',
					array('onclick'=>'showHelp();return false;'));
			?>
		</span>
	</div>
	</div></div>
	<?php
    $search_add_html="";
    $modelName = get_class($model);
    $search_add_html .= TbHtml::dropDownList($modelName.'[shift]',$model->shift,VisitList::getShiftList(),
        array("class"=>"form-control submit_shift"));

    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('sales','Visit List'),
        'model'=>$model,
        'viewhdr'=>'//visit/_listhdr',
        'viewdtl'=>'//visit/_listdtl',
        'advancedSearch'=>true,
        'hasDateButton'=>true,
        'hasSearchBar'=>true,
        'search_add_html'=>$search_add_html,
    ));
	?>
</section>

<?php $this->renderPartial('//visit/help'); ?>

<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
	echo $form->hiddenField($model,'filter');
?>
<?php $this->endWidget(); ?>
<?php $this->renderPartial('//site/fileviewx',array('model'=>$model,
    'form'=>$form,
    'doctype'=>'VISIT',
    'header'=>Yii::t('dialog','File Attachment'),

));
?>
<?php
Script::genFileDownload($model,$form->id,'VISIT');

$js = Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);

$link = Yii::app()->createAbsoluteUrl("visit");
$js = <<<EOF
function showattm(docid) {
	var data = "docId="+docid;
	var link = "$link"+"/listfile";
	$.ajax({
		type: 'GET',
		url: link,
		data: data,
		success: function(data) {
	
			$("#fileviewvisit").html(data);
			$('#fileuploadvisit').modal('show');
		},
		error: function(data) { // if error occured
			alert("Error occured.please try again");
		},
		dataType:'html'
	});
}

EOF;
Yii::app()->clientScript->registerScript('fileview',$js,CClientScript::POS_HEAD);

$js = "
$('body').on('click','#chkboxAll',function() {
	var val = $(this).prop('checked');
	$('input[type=checkbox][name*=\"select\"]').prop('checked',val);
});
";
Yii::app()->clientScript->registerScript('selectAll',$js,CClientScript::POS_READY);
?>



<?php
$link = Yii::app()->createAbsoluteUrl("visit/updatevip");
$js = <<<EOF
function star(id) {
	var sts = $('#vip_'+id).val();
	var flag = (sts=='N' ? 'Y' : 'N');
	var data = "id="+id+"&sts="+flag;
	$.ajax({
		type: 'GET',
		url: '$link',
		data: data,
		success: function(data) {
			if (data!='NIL') {
				$("[id^='name_']").each(function(){
					var name = $(this).html();
					if (name==data) {
						var tag_o = $(this).attr('id');
						var tag_n1 = tag_o.replace('name_','star_');
						var tag_n2 = tag_o.replace('name_','vip_');
						var cn = (flag=='Y') ? '<span class="fa fa-star"></span>' : '<span class="fa fa-star-o"></span>';
						$('#'+tag_n1).html(cn);
						$('#'+tag_n2).val(flag);
					}
				});
			}
		},
		error: function(data) { // if error occured
			var x = 1;
		},
		dataType:'html'
	});
}
EOF;
Yii::app()->clientScript->registerScript('starClick',$js,CClientScript::POS_HEAD);

$js = <<<EOF
function showHelp() {
	$('#helpdialog').modal('show');
}
EOF;
Yii::app()->clientScript->registerScript('helpClick',$js,CClientScript::POS_HEAD);

$js = "
    $('.submit_shift').on('change',function(){
        $('form:first').submit();
    });
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
$js = Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>


