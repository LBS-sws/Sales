<?php
$this->pageTitle=Yii::app()->name . ' - Sales Visit Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'visit-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('sales','Sale List'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">

		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('shift/index')));
		?>


<?php //if ($model->scenario=='edit' && !$model->isReadOnly() && $model->status=='N'): ?>
	<?php 
//		echo TbHtml::button('<span class="fa fa-map-marker"></span> '.Yii::t('sales','Visited'), array(
//			'name'=>'btnVisit','id'=>'btnVisit',)
//		);
	?>
        <?php  if (Yii::app()->user->validRWFunction('HA03')){?>
        <div class="btn-group">
            <select class="form-control" name="ShiftForm[visit_shift]" id="ShiftForm_visit_type">
                <option value=""><?php echo Yii::t('report','Please select the assigned person');?></option>
                <?php foreach ($saleman as $v) {?>
                    <option value="<?php echo $v['id'];?>"><?php echo $v['name'];?> </option>
                <?php }?>
            </select>
            </div>
        <?php }?>
<?php //endif ?>
        <?php
        if (Yii::app()->user->validRWFunction('HA03'))
            echo TbHtml::button(Yii::t('misc','Distribution'), array(
                'submit'=>Yii::app()->createUrl('shift/zhuanone')));
        ?>
	</div>
	<div class="btn-group pull-right" role="group">
	<?php 
		$counter = ($model->no_of_attm['visit'] > 0) ? ' <span id="docvisit" class="label label-info">'.$model->no_of_attm['visit'].'</span>' : ' <span id="docvisit"></span>';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('misc','Attachment').$counter, array(
			'name'=>'btnFile','id'=>'btnFile','data-toggle'=>'modal','data-target'=>'#fileuploadvisit',)
		);
	?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php 
				echo $form->hiddenField($model, 'scenario'); 
				echo $form->hiddenField($model, 'username');
				echo $form->hiddenField($model, 'id');
				echo $form->hiddenField($model, 'city');
				echo $form->hiddenField($model, 'status');
				echo $form->hiddenField($model, 'status_dt');
				echo $form->hiddenField($model, 'latitude');
				echo $form->hiddenField($model, 'longitude');
				echo $form->hiddenField($model, 'deal');
			?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'visit_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php echo $form->textField($model, 'visit_dt', array('class'=>'form-control pull-right','readonly'=>($model->isReadOnly()||$model->status!='N'))); ?>
					</div>
				</div>
<!--
<?php //if ($model->status=='Y'): ?>
				<?php //echo $form->labelEx($model,'status_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php //echo $form->textField($model, 'status_dt', array('class'=>'form-control pull-right','readonly'=>true)); ?>
					</div>
				</div>
<?php //endif ?>
-->
			</div>
			
<?php if ($model->isReadAll()): ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'staff',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textField($model, 'staff', array('readonly'=>true)); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'post_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-4">
					<?php echo $form->textField($model, 'post_name', array('readonly'=>true)); ?>
				</div>

				<?php echo $form->labelEx($model,'dept_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-4">
					<?php echo $form->textField($model, 'dept_name', array('readonly'=>true)); ?>
				</div>
			</div>
<?php endif ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'visit_type',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php
						$typelist = $model->getVisitTypeList();
						if ($model->isReadOnly()) {
							echo $form->hiddenField($model, 'visit_type');
							echo TbHtml::textField('visit_type_name', $typelist[$model->visit_type], array('readonly'=>true));
						} else {
							echo $form->dropDownList($model, 'visit_type', $typelist); 
						}
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'visit_obj',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-8">
					<?php
						$typelist = $model->getVisitObjList();
//						if ($model->isReadOnly() || $model->status=='Y') {
//							echo $form->hiddenField($model, 'visit_obj');
//							echo TbHtml::textField('visit_obj_name', $typelist[$model->visit_obj], array('readonly'=>true));
//						} else {
							echo $form->dropDownList($model, 'visit_obj', $typelist,
								array('class'=>'select2','multiple'=>'multiple')
							);
//						}
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'cust_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-5">
					<?php 
						if ($model->isReadOnly() || $model->status=='Y') {
							echo $form->textField($model, 'cust_name', array('readonly'=>true)); 
						} else {
							$list = empty($model->cust_name) ? array() : array($model->cust_name=>$model->cust_name);
							echo $form->dropDownList($model, 'cust_name', $list, 
								array('class'=>'select2')
							);
						}
					?>
				</div>
				
				<?php  ?>
				<div class="col-sm-3">
					<?php 
						echo $form->checkBox($model, 'cust_vip', 
								array('disabled'=>($model->isReadOnly()),
									'uncheckValue'=>'N', 'value'=>'Y',
								)
							); 
						echo $form->labelEx($model,'cust_vip',array('class'=>"control-label"));
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'cust_alt_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-5">
					<?php echo $form->textField($model, 'cust_alt_name', array('readonly'=>$model->isReadOnly())); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'cust_type',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php
						$typegrouplist = array(1=>Yii::t('sales','Catering'),2=>Yii::t('sales','Non-catering'));
						if ($model->isReadOnly()) {
							echo $form->hiddenField($model, 'cust_type_group');
							echo TbHtml::textField('cust_type_group_name', $typegrouplist[$model->cust_type_group], array('readonly'=>true));
						} else {
							echo $form->dropDownList($model, 'cust_type_group', $typegrouplist); 
						}
					?>
				</div>
				<div class="col-sm-3">
					<?php
						$typelist = $model->getCustTypeList((empty($model->cust_type_group) ? 1 : $model->cust_type_group));
						if ($model->isReadOnly()) {
							echo $form->hiddenField($model, 'cust_type');
							echo TbHtml::textField('cust_type_name', $typelist[$model->cust_type], array('readonly'=>true));
						} else {
							echo $form->dropDownList($model, 'cust_type', $typelist); 
						}
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'cust_person',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'cust_person', array('readonly'=>$model->isReadOnly())); ?>
				</div>
				<?php echo $form->labelEx($model,'cust_tel',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'cust_tel', array('readonly'=>$model->isReadOnly())); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'cust_person_role',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'cust_person_role', array('readonly'=>$model->isReadOnly())); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'district',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php 
						$districtlist = $model->getDistrictList();
						if ($model->isReadOnly()) {
							echo $form->hiddenField($model, 'district');
						//	echo TbHtml::textField('district_name', $districtlist[$model->district], array('readonly'=>true));
						} else {
							echo $form->dropDownList($model, 'district', $districtlist);
						}
					?>
				</div>
				<?php echo $form->labelEx($model,'street',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'street', array('readonly'=>($model->isReadOnly()))); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'remarks',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'remarks', 
						array('rows'=>3,'cols'=>60,'maxlength'=>5000,'readonly'=>($model->isReadOnly()))
					); ?>
				</div>
			</div>

<?php
	foreach($model->serviceDefinition() as $gid=>$items) {
		$fieldid = get_class($model).'_service_svc_'.$gid;
		$fieldname = get_class($model).'[service][svc_'.$gid.']';
		$fieldvalue = isset($model->service['svc_'.$gid]) ? $model->service['svc_'.$gid] : '';
		
		$content = "<legend>".$items['name']."</legend>";
		$content .= "<div class='form-group'>";
		switch ($items['type']) {
			case 'qty':
				$content .= TbHtml::label(Yii::t('sales','Qty'),$fieldid, array('class'=>"col-sm-2 control-label"));
				$content .= "<div class='col-sm-2'>"
							.TbHtml::numberField($fieldname, $fieldvalue, 
								array('size'=>5,'min'=>0,'id'=>$fieldid,'readonly'=>($model->isReadOnly()),
									'placeholder'=>Yii::t('sales','Qty'),
								)
							)
							."</div>"; 
				break;
			case 'annual':
				$content .= TbHtml::label(Yii::t('sales','Monthly Amount'),$fieldid, array('class'=>"col-sm-2 control-label"));
				$content .= "<div class='col-sm-2'>"
							.TbHtml::numberField($fieldname, $fieldvalue, 
								array('size'=>8,'min'=>0,'id'=>$fieldid,'readonly'=>($model->isReadOnly()),	
									'placeholder'=>Yii::t('sales','Amount'),
								)
							)
							."</div>"; 
				break;
			case 'amount':
				$content .= TbHtml::label(Yii::t('sales','Amount'),$fieldid, array('class'=>"col-sm-2 control-label"));
				$content .= "<div class='col-sm-2'>"
							.TbHtml::numberField($fieldname, $fieldvalue, 
								array('size'=>8,'min'=>0,'id'=>$fieldid,'readonly'=>($model->isReadOnly()),	
									'placeholder'=>Yii::t('sales','Amount'),
								)
							)
							."</div>"; 
				break;
		}
		$content .= "</div>";
		
		$cnt = 0;
		$out = '';
		foreach ($items['items'] as $fid=>$fv) {
			$fieldid = get_class($model).'_service_svc_'.$fid;
			$fieldname = get_class($model).'[service][svc_'.$fid.']';
			$fieldvalue = $model->service['svc_'.$fid];
			
			if ($cnt==0) $out .= '<div class="form-group">';

//			$out .= '<div class="col-sm-2">';
			$out .= TbHtml::label($fv['name'], $fieldid, array('class'=>"col-sm-2 control-label"));
//			$out .= '</div>';
			switch ($fv['type']) {
				case 'pct':
					$out .= '<div class="col-sm-2">';
					$out .= TbHtml::numberField($fieldname, $fieldvalue, 
								array('size'=>5,'min'=>0,'max'=>100,'id'=>$fieldid,'readonly'=>($model->isReadOnly()),
									'placeholder'=>Yii::t('sales','Percentage'),
								)
							); 
					break;
				case 'qty':
					$out .= '<div class="col-sm-2">';
					$out .= TbHtml::numberField($fieldname, $fieldvalue, 
								array('size'=>5,'min'=>0,'id'=>$fieldid,'readonly'=>($model->isReadOnly()),
									'placeholder'=>Yii::t('sales','Qty'),
								)
							); 
					break;
				case 'annual':
				case 'amount':
					$out .= '<div class="col-sm-2">';
					$out .= TbHtml::numberField($fieldname, $fieldvalue, 
								array('size'=>8,'min'=>0,'id'=>$fieldid,'readonly'=>($model->isReadOnly()),	
									'placeholder'=>Yii::t('sales','Amount'),
								)
							); 
					break;
				case 'text':
					$out .= '<div class="col-sm-2">';
					$out .= TbHtml::textField($fieldname, $fieldvalue, 
								array('id'=>$fieldid,'readonly'=>($model->isReadOnly()),	
									'placeholder'=>Yii::t('sales','Text'),
								)
							); 
					break;
				case 'rmk':
					$out .= '<div class="col-sm-7">';
					$out .= TbHtml::textArea($fieldname, $fieldvalue, 
								array('id'=>$fieldid,'rows'=>3,'cols'=>60,'maxlength'=>5000,
									'placeholder'=>Yii::t('sales','Remarks'),
									'readonly'=>($model->isReadOnly())
								)
							);
					break;
				case 'checkbox':
					$out .= '<div class="col-sm-2">';
					$out .= TbHtml::checkBox($fieldname, ($fieldvalue=='Y'), 
								array('id'=>$fieldid,'disabled'=>($model->isReadOnly()),
									'uncheckValue'=>'N', 'value'=>'Y',
								)
							); 
					break;
			}
			$out .= '</div>';
			$cnt++;
			
			$eol = (isset($fv['eol']) && $fv['eol']);
			
			if ($cnt==3 || $eol) {
				$out .= '</div>';
				$content .= $out;
				$cnt = 0;
				$out = '';
			}
		}
		if (!empty($out)) {
			if ($cnt!=0) $out .= '</div>';
			$content .= $out;
			$cnt = 0;
		}
		echo $content;
	}	
?>
			
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'VISIT',
													'header'=>Yii::t('dialog','File Attachment'),
													'ronly'=>($model->scenario=='view' || $model->isReadOnly()),
													)); 
?>

<?php
Script::genFileUpload($model,$form->id,'VISIT');

$link1 = Yii::app()->createAbsoluteUrl("visit/searchcust");
$link2 = Yii::app()->createAbsoluteUrl("visit/readcust");
$link3 = Yii::app()->createAbsoluteUrl("visit/getcusttypelist");
switch(Yii::app()->language) {
	case 'zh_cn': $lang = 'zh-CN'; break;
	case 'zh_tw': $lang = 'zh-TW'; break;
	default: $lang = Yii::app()->language;
}
$disabled = (!$model->isReadOnly()) ? 'false' : 'true';
	$js = <<<EOF
$('#VisitForm_visit_obj').select2({
	tags: false,
	multiple: true,
	maximumInputLength: 0,
	maximumSelectionLength: 10,
	allowClear: true,
	language: '$lang',
	disabled: $disabled
});

$('#VisitForm_visit_obj').on('select2:opening select2:closing', function( event ) {
    var searchfield = $(this).parent().find('.select2-search__field');
    searchfield.prop('disabled', true);
});

$('#VisitForm_cust_type_group').on('change',function() {
	var group = $(this).val();
	var data = "group="+group;
	$.ajax({
		type: 'GET',
		url: '$link3',
		data: data,
		success: function(data) {
			$('#VisitForm_cust_type').html(data);
		},
		error: function(data) { // if error occured
			var x = 1;
		},
		dataType:'html'
	});
});	
EOF;
Yii::app()->clientScript->registerScript('select2_1',$js,CClientScript::POS_READY);

if (!$model->isReadOnly() && $model->status=='N') {
	$js = <<<EOF
$('#VisitForm_cust_name').select2({
	tags: true,
	ajax: {
		delay: 250,
		url: '$link1',
		dataType: 'json'
	},
	minimumInputLength: 1,
	language: '$lang'
});

$('#VisitForm_cust_name').on('change', function(){
	var name = $(this).val();
	var data = "name="+name;
	$.ajax({
		type: 'GET',
		url: '$link2',
		data: data,
		success: function(data) {
			$('#VisitForm_cust_person').val(data.cust_person);
			$('#VisitForm_cust_person_role').val(data.cust_person_role);
			$('#VisitForm_cust_tel').val(data.cust_tel);
			$('#VisitForm_cust_type').val(data.cust_type);
			$('#VisitForm_district').val(data.district);
			$('#VisitForm_street').val(data.street);
			$('#VisitForm_cust_alt_name').val(data.cust_alt_name);
			$('#VisitForm_cust_vip').prop('checked', data.cust_vip=='Y');
		},
		error: function(data) { // if error occured
			var x = 1;
		},
		dataType:'json'
	});
});
EOF;
	Yii::app()->clientScript->registerScript('select2',$js,CClientScript::POS_READY);
}

//if ($model->scenario=='edit' && !$model->isReadOnly() && $model->status=='N') {
//	$link3 = Yii::app()->createAbsoluteUrl("visit/visited");
//	$js = <<<EOF
//$('#btnVisit').on('click', function() {
//	if (navigator.geolocation) {
//		navigator.geolocation.getCurrentPosition(function(position) {
//			var lat = position.coords.latitude;
//			var long = position.coords.longitude;
//			$('#VisitForm_latitude').val(lat);
//			$('#VisitForm_latitude').val(long);
//		});
//	}
//	jQuery.yii.submitForm(this,'$link3',{});
//});
//EOF;
//	Yii::app()->clientScript->registerScript('visited',$js,CClientScript::POS_READY);
//}

if (!$model->isReadOnly() && $model->status=='N') {
	$js = Script::genDatePicker(array(
			'VisitForm_visit_dt',
		));
	Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}

$js = Script::genDeleteData(Yii::app()->createUrl('visit/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


