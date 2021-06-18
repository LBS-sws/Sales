<style>
    .select2-container.select2-container-disabled .select2-choice {
        background-color: #ddd;
        border-color: #a8a8a8;
    }</style>
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
		<strong><?php echo Yii::t('sales','Sales Visit Form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('visit/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('visit/index'))); 
		?>
<?php if (!$model->isReadOnly()): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('visit/save'))); 
			?>
<?php endif ?>
<?php if ($model->scenario=='edit' && !$model->isReadOnly()): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
<?php //if ($model->scenario=='edit' && !$model->isReadOnly() && $model->status=='N'): ?>
	<?php 
//		echo TbHtml::button('<span class="fa fa-map-marker"></span> '.Yii::t('sales','Visited'), array(
//			'name'=>'btnVisit','id'=>'btnVisit',)
//		);
	?>
<?php //endif ?>
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
						<?php echo $form->textField($model, 'visit_dt', array('class'=>'form-control pull-right','readonly'=>($model->scenario!='new'))); ?>
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
				<div class="col-sm-3" <?php if($model->isReadOnly() || $model->status=='Y'){echo "style='pointer-events:none;'";}?> >
					<?php
						$typelist = $model->getVisitTypeList();
						if ($model->isReadOnly()) {
							echo $form->hiddenField($model, 'visit_type');
							echo TbHtml::textField('visit_type_name', $typelist[$model->visit_type], array('readonly'=>($model->isReadOnly()||$model->status!='N')));
						} else {
							echo $form->dropDownList($model, 'visit_type', $typelist, array('readonly'=>($model->isReadOnly()||$model->status!='N')));
						}
					?>
				</div>
			</div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'quotation',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3" >
                    <?php
                    $quotation = array(
                            '否'=>Yii::t('sales','No Quotation'),
                            '是'=>Yii::t('sales','Quotations'),
                    );
                        echo $form->dropDownList($model, 'quotation', $quotation, array('readonly'=>''));
                    ?>
                </div>
            </div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'visit_obj',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-8" <?php if($model->isReadOnly() || $model->status=='Y'){echo "style='pointer-events:none;'";}?>>
					<?php
						$typelist = $model->getVisitObjList();
//						if ($model->isReadOnly() || $model->status=='Y') {
//							echo $form->hiddenField($model, 'visit_obj');
//							echo TbHtml::textField('visit_obj_name', $typelist[$model->visit_obj], array('readonly'=>true));
//						} else {
							echo $form->dropDownList($model, 'visit_obj', $typelist,
								array('class'=>'select2','multiple'=>'multiple','disabled'=>'disabled')
							);
//						}
					?>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,Yii::t('sales','客户名称（包括分店名）'),array('class'=>"col-sm-2 control-label")); ?>
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
                <?php echo $form->labelEx($model,'service_type',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-8" <?php if($model->isReadOnly() || $model->status=='Y'){echo "style='pointer-events:none;'";}?>>
                    <?php
                    $typelist = $model->getServiceTypeList();
                    //						if ($model->isReadOnly() || $model->status=='Y') {
                    //							echo $form->hiddenField($model, 'visit_obj');
                    //							echo TbHtml::textField('visit_obj_name', $typelist[$model->visit_obj], array('readonly'=>true));
                    //						} else {
                    echo $form->dropDownList($model, 'service_type', $typelist,
                        array('class'=>'select2','multiple'=>'multiple','disabled'=>'disabled')
                    );
                    //						}
                    ?>
                </div>
            </div>

<!--			<div class="form-group">-->
<!--				--><?php //echo $form->labelEx($model,'cust_alt_name',array('class'=>"col-sm-2 control-label")); ?>
<!--				<div class="col-sm-5">-->
<!--					--><?php //echo $form->textField($model, 'cust_alt_name', array('readonly'=>$model->isReadOnly())); ?>
<!--				</div>-->
<!--			</div>-->

			<div class="form-group">
				<?php echo $form->labelEx($model,'cust_type',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php
						$typegrouplist = array(1=>Yii::t('sales','Catering'),2=>Yii::t('sales','Non-catering'));
						if ($model->isReadOnly()) {
							echo $form->hiddenField($model, 'cust_type_group');
							echo TbHtml::textField('cust_type_group_name', $typegrouplist[$model->cust_type_group], array('readonly'=>true));
						} else {
							echo $form->dropDownList($model, 'cust_type_group', $typegrouplist,array('class'=>'de_class','de_type'=>'val'));
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
							echo $form->dropDownList($model, 'cust_type', $typelist,array('class'=>'de_class','de_type'=>'val'));
						}
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'cust_person',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'cust_person', array('readonly'=>$model->isReadOnly(),'class'=>'de_class','de_type'=>'val')); ?>
				</div>
				<?php echo $form->labelEx($model,'cust_tel',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'cust_tel', array('readonly'=>$model->isReadOnly(),'class'=>'de_class','de_type'=>'val')); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'cust_person_role',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'cust_person_role', array('readonly'=>$model->isReadOnly(),'class'=>'de_class','de_type'=>'val')); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'district',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php 
						$districtlist = $model->getDistrictList();
						if ($model->isReadOnly()) {
							echo $form->hiddenField($model, 'district');
							echo TbHtml::textField('district_name', $districtlist[$model->district], array('readonly'=>true));
						} else {
							echo $form->dropDownList($model, 'district', $districtlist,array('class'=>'de_class','de_type'=>'val'));
						}
					?>
				</div>
				<?php echo $form->labelEx($model,'street',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'street', array('readonly'=>($model->isReadOnly()),'class'=>'de_class','de_type'=>'val')); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'remarks',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'remarks', 
						array('rows'=>3,'cols'=>60,'maxlength'=>5000,'readonly'=>($model->isReadOnly()),'class'=>'de_class','de_type'=>'val')
					); ?>
				</div>
			</div>

<?php

	foreach($model->serviceDefinition() as $gid=>$items) {
		$fieldid = get_class($model).'_service_svc_'.$gid;
		$fieldname = get_class($model).'[service][svc_'.$gid.']';
		$fieldvalue = isset($model->service['svc_'.$gid]) ? $model->service['svc_'.$gid] : '';
		
		$content = "<legend>".$items['name']."</legend>";
		$content .= "<div class='form-group' data-num='11111'>";
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
		$de_bool=2;//默認值專用 1：默認 2：空白
		foreach ($items['items'] as $fid=>$fv) {
            $de_bool = $items['type']=="none"||in_array($fid,array('A7','B6','C7','D6','E7'))?2:1;
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
								array('size'=>5,'min'=>0,'max'=>100,'id'=>$fieldid,'readonly'=>($model->isReadOnly()),'class'=>'de_class','de_type'=>'val','de_bool'=>$de_bool,
									'placeholder'=>Yii::t('sales','Percentage'),
								)
							); 
					break;
				case 'qty':
					$out .= '<div class="col-sm-2">';
					$out .= TbHtml::numberField($fieldname, $fieldvalue, 
								array('size'=>5,'min'=>0,'id'=>$fieldid,'readonly'=>($model->isReadOnly()),'class'=>'de_class','de_type'=>'val','de_bool'=>$de_bool,
									'placeholder'=>Yii::t('sales','Qty'),
								)
							); 
					break;
				case 'annual':
				case 'amount':
					$out .= '<div class="col-sm-2">';
					$out .= TbHtml::numberField($fieldname, $fieldvalue, 
								array('size'=>8,'min'=>0,'id'=>$fieldid,'readonly'=>($model->isReadOnly()),'class'=>'de_class','de_type'=>'val','de_bool'=>$de_bool,
									'placeholder'=>Yii::t('sales','Amount'),
								)
							); 
					break;
				case 'text':
					$out .= '<div class="col-sm-2">';
					$out .= TbHtml::textField($fieldname, $fieldvalue, 
								array('id'=>$fieldid,'readonly'=>($model->isReadOnly()),'class'=>'de_class','de_type'=>'val','de_bool'=>$de_bool,
									'placeholder'=>Yii::t('sales','Text'),
								)
							); 
					break;
				case 'rmk':
					$out .= '<div class="col-sm-7">';
					$out .= TbHtml::textArea($fieldname, $fieldvalue, 
								array('id'=>$fieldid,'rows'=>3,'cols'=>60,'maxlength'=>5000,'class'=>'de_class','de_type'=>'val','de_bool'=>$de_bool,
									'placeholder'=>Yii::t('sales','Remarks'),
									'readonly'=>($model->isReadOnly())
								)
							);
					break;
				case 'checkbox':
					$out .= '<div class="col-sm-2">';
					$out .= TbHtml::checkBox($fieldname, ($fieldvalue=='Y'), 
								array('id'=>$fieldid,'disabled'=>($model->isReadOnly()),'class'=>'de_class','de_type'=>'checked','de_bool'=>$de_bool,
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
	disabled: $disabled,
	templateSelection: formatState
});
$('#VisitForm_service_type').select2({
	tags: false,
	multiple: true,
	maximumInputLength: 0,
	maximumSelectionLength: 10,
	allowClear: true,
	language: '$lang',
	disabled: $disabled,
	templateSelection: formatState
});

function formatState(state) {
	var rtn = $('<span style="color:black">'+state.text+'</span>');
	return rtn;
}


$('#VisitForm_service_type').on('select2:opening select2:closing', function( event ) {
    var searchfield = $(this).parent().find('.select2-search__field');
    searchfield.prop('disabled', true);
});
$('#VisitForm_service_type').on('select2:opening select2:closing', function( event ) {
    var searchfield = $(this).parent().find('.select2-search__field');
    searchfield.prop('disabled', true);
});

$('#VisitForm_cust_type_group').on('change',function() {
	var group = $(this).val();
	var cust_type = $(this).data('cust_type');
	var data = "group="+group;
	$.ajax({
		type: 'GET',
		url: '$link3',
		data: data,
		success: function(data) {
			$('#VisitForm_cust_type').html(data);
			if(cust_type != undefined){
			    $('#VisitForm_cust_type').val(cust_type);
			    $('#VisitForm_cust_type_group').removeData('cust_type');
			}
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
		//达大厦的
		    $('.de_class').each(function(){
		        var de_type = $(this).attr('de_type');
		        var de_bool = $(this).attr('de_bool');
		        var str = $(this).attr('name');
		        str = str.split("[").pop();
		        str = str.split("]")[0];
		        var dataValue = data.hasOwnProperty(str)?data[str]:false;
		        if(de_bool != 2&&dataValue!==false&&dataValue!==false){
		            switch(de_type){
		                case 'checked':
                            $(this).prop('checked', dataValue=='Y');
		                    break;
                        default:
                            $(this).val(dataValue);
		            }
		            if(str == 'cust_type_group'){
		                $(this).data('cust_type',data['cust_type']);
		                $(this).trigger('change');
		            }
		        }
		    });
		  
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

if ($model->scenario=='edit' && !$model->isReadOnly() && $model->status=='N') {
	$link3 = Yii::app()->createAbsoluteUrl("visit/visited");
	$js = <<<EOF
$('#btnVisit').on('click', function() {
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function(position) {
			var lat = position.coords.latitude;
			var long = position.coords.longitude;
			$('#VisitForm_latitude').val(lat);
			$('#VisitForm_latitude').val(long);
		});
	}
	jQuery.yii.submitForm(this,'$link3',{});
});
EOF;
	Yii::app()->clientScript->registerScript('visited',$js,CClientScript::POS_READY);
}
//  $('#VisitForm_service_svc_A').val(data.svc_A);
//		    $('#VisitForm_service_svc_A1').val(data.svc_A1);
//		    $('#VisitForm_service_svc_A2').val(data.svc_A2);
//		    $('#VisitForm_service_svc_A3').val(data.svc_A3);
//		    $('#VisitForm_service_svc_A4').val(data.svc_A4);
//		    $('#VisitForm_service_svc_A5').val(data.svc_A5);
//		    $('#VisitForm_service_svc_A9').val(data.svc_A9);
//		    $('#VisitForm_service_svc_A6').val(data.svc_A6);
//		    $('#VisitForm_service_svc_A7').val(data.svc_A7);
//		    $('#VisitForm_service_svc_A8').val(data.svc_A8);
//		    $('#VisitForm_service_svc_B').val(data.svc_B);
//		    $('#VisitForm_service_svc_B1').val(data.svc_B1);
//		    $('#VisitForm_service_svc_B2').val(data.svc_B2);
//		    $('#VisitForm_service_svc_B3').val(data.svc_B3);
//		    $('#VisitForm_service_svc_B4').val(data.svc_B4);
//		    $('#VisitForm_service_svc_B5').val(data.svc_B5);
//		    $('#VisitForm_service_svc_B6').val(data.svc_B6);
//		    $('#VisitForm_service_svc_B7').val(data.svc_B7);
//		    $('#VisitForm_service_svc_C').val(data.svc_C);
//		    $('#VisitForm_service_svc_C1').val(data.svc_C1);
//		    $('#VisitForm_service_svc_C2').prop('checked', data.svc_C2=='Y');
//		    $('#VisitForm_service_svc_C3').prop('checked', data.svc_C3=='Y');
//		    $('#VisitForm_service_svc_C4').prop('checked', data.svc_C4=='Y');
//		    $('#VisitForm_service_svc_C5').prop('checked', data.svc_C5=='Y');
//		    $('#VisitForm_service_svc_C9').prop('checked', data.svc_C9=='Y');
//		    $('#VisitForm_service_svc_C6').val(data.svc_C6);
//		    $('#VisitForm_service_svc_C7').val(data.svc_C7);
//		    $('#VisitForm_service_svc_C8').val(data.svc_C8);
//		    $('#VisitForm_service_svc_D').val(data.svc_D);
//		    $('#VisitForm_service_svc_D1').val(data.svc_D1);
//		    $('#VisitForm_service_svc_D2').val(data.svc_D2);
//		    $('#VisitForm_service_svc_D3').val(data.svc_D3);
//		    $('#VisitForm_service_svc_D4').val(data.svc_D4);
//		    $('#VisitForm_service_svc_D5').val(data.svc_D5);
//		    $('#VisitForm_service_svc_D6').val(data.svc_D6);
//		    $('#VisitForm_service_svc_D7').val(data.svc_D7);
//		    $('#VisitForm_service_svc_E').val(data.svc_E);
//		    $('#VisitForm_service_svc_E1').val(data.svc_E1);
//		    $('#VisitForm_service_svc_E2').val(data.svc_E2);
//		    $('#VisitForm_service_svc_E3').val(data.svc_E3);
//		    $('#VisitForm_service_svc_E4').val(data.svc_E4);
//		    $('#VisitForm_service_svc_E5').val(data.svc_E5);
//		    $('#VisitForm_service_svc_E6').val(data.svc_E6);
//		    $('#VisitForm_service_svc_E7').val(data.svc_E7);
//		    $('#VisitForm_service_svc_E8').val(data.svc_E8);
//		    $('#VisitForm_service_svc_F1').val(data.svc_F1);
//		    $('#VisitForm_service_svc_F2').val(data.svc_F2);
//		    $('#VisitForm_service_svc_F3').val(data.svc_F3);
//		    $('#VisitForm_service_svc_F4').val(data.svc_F4);
//		    $('#VisitForm_service_svc_G1').val(data.svc_G1);
//		    $('#VisitForm_service_svc_G2').val(data.svc_G2);
//		    $('#VisitForm_service_svc_G3').val(data.svc_G3);

if ($model->scenario=='new') {
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


