<?php
$this->pageTitle=Yii::app()->name . ' - Sales Visit';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'shift-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('sales','Sale Shift'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
        <div class="btn-group" role="group">
            <?php
            if (Yii::app()->user->validRWFunction('HA03'))
              echo TbHtml::button(Yii::t('misc','Batch allocation'), array(
                'submit'=>Yii::app()->createUrl('shift/zhuan')));
            ?>
        </div>
        <div class="btn-group">
            <?php  if (Yii::app()->user->validRWFunction('HA03')){?>
            <select class="form-control" name="ShiftList[visit_shift]" id="VisitForm_visit_type">
                <option value=""><?php echo Yii::t('report','Please select the assigned person');?></option>
                <?php foreach ($saleman as $v) {?>
                <option value="<?php echo $v['id'];?>"><?php echo $v['name'];?> </option>
                <?php }?>
            </select>
            <?php }?>
        </div>
	</div>
            <?php  if (Yii::app()->user->validRWFunction('HA03')){?>
                <div class="btn-group pull-right" role="group">
                    <span style="padding: 10px">取消后，选择的拜访数据将不在转移列表显示</span>
                    <?php
                    echo TbHtml::button(Yii::t('dialog','Cancel'), array(
                        'data-toggle'=>'modal','data-target'=>'#canceldialog','class'=>'pull-right'));
                    ?>
                </div>
            <?php }?>
	</div></div>
	<?php
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('sales','Sale List'),
			'model'=>$model,
				'viewhdr'=>'//shift/_listhdr',
				'viewdtl'=>'//shift/_listdtl',
				'advancedSearch'=>true,
		));
	?>
</section>
<?php $this->renderPartial('//site/canceldialog'); ?>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
	echo $form->hiddenField($model,'filter');
?>
<?php $this->endWidget(); ?>
<?php //$this->renderPartial('//site/removedialog'); ?>
<?php
$link = Yii::app()->createAbsoluteUrl("shift/shiftBool");
$js = "
$('#btnCancelData').on('click',function() {
	$('#canceldialog').modal('hide');
	jQuery.yii.submitForm(this,'{$link}',{});
});
";
Yii::app()->clientScript->registerScript('cancelRecord',$js,CClientScript::POS_READY);
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
$(document).ready(function(){ 
       $("#chkboxAll").on('click',function() {     
       
              $("input[name='ShiftList[id][]']").prop("checked", this.checked);  
        });          
        $("input[name='ShiftList[id][]']").on('click',function() {  
              var subs = $("input[name='ShiftList[id][]']");  
              $("#chkboxAll").prop("checked" ,subs.length == subs.filter(":checked").length ? true :false);  
        });
});
EOF;
Yii::app()->clientScript->registerScript('starClick',$js,CClientScript::POS_HEAD);

$js = Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>


