<?php echo $form->hiddenField($model, 'id',array("class"=>'select_check')); ?>
<?php echo $form->hiddenField($model, 'scenario'); ?>
<?php echo $form->hiddenField($model, 'clue_type'); ?>
<?php echo $form->hiddenField($model, 'clue_status'); ?>
<?php echo $form->hiddenField($model, 'city'); ?>
<?php echo $form->hiddenField($model, 'rec_type'); ?>
<?php echo $form->hiddenField($model, 'rec_employee_id'); ?>
<?php
$modelClass = get_class($model);
?>
<?php if ($model->scenario!='new'): ?>
    <div class="form-group">
        <?php echo $form->labelEx($model,'clue_code',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-3">
            <?php echo $form->textField($model, 'clue_code',
                array('readonly'=>true)
            ); ?>
        </div>
        <?php echo $form->labelEx($model,'clue_status',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-3">
            <?php
            echo TbHtml::textField("clue_status",CGetName::getClueStatusStrByKey($model->clue_status),
                array('readonly'=>true)
            );
            ?>
        </div>
    </div>
<?php endif ?>
<div class="form-group">
    <?php echo $form->labelEx($model,'entry_date',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->textField($model, 'entry_date',
            array('readonly'=>$model->isReadonly(),'id'=>'entry_date',
                'prepend'=>'<span class="fa fa-calendar"></span>')
        ); ?>
    </div>
    <?php if ($model->rec_type==1): ?>
        <?php echo $form->labelEx($model,'rec_employee_id',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-3">
            <?php
            echo TbHtml::textField("rec_employee_id",CGetName::getEmployeeNameByKey($model->rec_employee_id),
                array('readonly'=>true)
            );
            ?>
        </div>
    <?php endif ?>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'cust_name',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-8">
        <?php echo $form->textField($model, 'cust_name',
            array('readonly'=>$model->scenario!='new','id'=>'cust_name')
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'clue_type',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php
        echo TbHtml::textField("clue_type",CGetName::getClueTypeStr($model->clue_type),
            array('readonly'=>true)
        );
        ?>
    </div>
    <?php echo $form->labelEx($model,'service_type',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->dropDownList($model, 'service_type',VisitForm::getServiceTypeList(),
            array('readonly'=>$model->scenario!='new')
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'cust_class_group',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-1 col-lg-right">
        <?php
        echo $form->dropDownList($model, 'cust_class_group',CGetName::getCustTypeGroupList(),
            array('readonly'=>$model->isReadonly(),"id"=>"cust_class_group","empty"=>"","data-cust_class"=>$model->cust_class)
        ); ?>
    </div>
    <div class="col-lg-2 col-lg-left">
        <?php
        $typelist = CGetName::getCustClassList((empty($model->cust_class_group) ? "0" : $model->cust_class_group));
        echo $form->dropDownList($model, 'cust_class',$typelist,
            array('readonly'=>$model->isReadonly(),"id"=>"cust_class")
        ); ?>
    </div>
    <?php echo $form->labelEx($model,'clue_source',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->dropDownList($model, 'clue_source',KASraForm::getSourceListForId($model->clue_source),
            array('readonly'=>$model->isReadonly(),'id'=>'clue_source')
        ); ?>
    </div>
</div>
<?php if ($model->clue_type==2): ?>
    <div class="form-group">
        <?php echo $form->labelEx($model,'cust_level',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-2">
            <?php
            echo $form->dropDownList($model, 'cust_level',KALevelForm::getLevelListForId($model->cust_level),
                array('readonly'=>$model->isReadonly(),"id"=>"cust_level")
            ); ?>
        </div>
        <?php echo $form->labelEx($model,'cust_type',array('class'=>"col-lg-1 control-label")); ?>
        <div class="col-lg-2">
            <?php
            echo $form->dropDownList($model, 'cust_type',KAClassForm::getClassListForId($model->cust_type),
                array('readonly'=>$model->isReadonly(),"id"=>"cust_type")
            ); ?>
        </div>
        <?php echo $form->labelEx($model,'support_user',array('class'=>"col-lg-1 control-label")); ?>
        <div class="col-lg-2">
            <?php echo $form->dropDownList($model, 'support_user',CGetName::getSupportUserList($model->city,$model->support_user),
                array('readonly'=>($model->scenario=='view'),'id'=>'support_user')
            ); ?>
        </div>
    </div>
<?php endif ?>
<div class="form-group">
    <?php echo $form->labelEx($model,'city',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-2">
        <?php
        echo TbHtml::textField("city_name",General::getCityName($model->city),
            array('readonly'=>true)
        );
        ?>
    </div>
    <?php echo $form->labelEx($model,'district',array('class'=>"col-lg-1 control-label")); ?>
    <div class="col-lg-2">
        <?php echo $form->dropDownList($model, 'district',CGetName::getDistrictList($model->city),
            array('readonly'=>$model->isReadonly(),'empty'=>'')
        ); ?>
    </div>
    <?php echo $form->labelEx($model,'street',array('class'=>"col-lg-1 control-label")); ?>
    <div class="col-lg-2">
        <?php echo $form->textField($model, 'street',
            array('readonly'=>$model->isReadonly())
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'address',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-5">
        <?php echo $form->textField($model, 'address',
            array('readonly'=>$model->isReadonly(),'id'=>'address')
        ); ?>
    </div>
    <?php echo $form->labelEx($model,'latitude',array('class'=>"col-lg-1 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->hiddenField($model, 'latitude',array("id"=>"latitude")); ?>
        <?php echo $form->hiddenField($model, 'longitude',array("id"=>"longitude")); ?>
        <p class="form-control-static">
            <span id="mapSpan">
                <?php
                $mapStr = "";
                $mapStr.= empty($model->longitude)?"":($model->longitude);
                $mapStr.= empty($model->latitude)?"":("<br/>".$model->latitude);
                echo empty($mapStr)?"未设置":$mapStr;
                ?>
            </span>
            <?php
            echo TbHtml::link(Yii::t("clue","map pun"),"javascript:void(0);",array(
                'data-toggle'=>'modal','data-target'=>'#map_baidu'
            ));
            ?>
        </p>
    </div>
</div>
<?php if ($model->clue_type==2): ?>
    <div class="form-group">
        <?php echo $form->labelEx($model,'busine_id',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-5">
            <?php echo $form->dropDownList($model, 'busine_id',KABusineForm::getBusineListForArr($model->busine_id),
                array('readonly'=>$model->isReadonly(),'id'=>'busine_id','class'=>'select2','multiple'=>'multiple')
            ); ?>
        </div>
        <?php echo $form->labelEx($model,'talk_city_id',array('class'=>"col-lg-1 control-label")); ?>
        <div class="col-lg-2">
            <?php echo $form->dropDownList($model, 'talk_city_id',KAAreaForm::getCityListForArr($model->talk_city_id),
                array('readonly'=>$model->isReadonly(),'class'=>'changeCity select2','id'=>'talk_city_id','multiple'=>'multiple')
            ); ?>
        </div>
    </div>
<?php endif ?>
<div class="form-group">
    <?php echo $form->labelEx($model,'cust_person',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-2">
        <?php echo $form->textField($model, 'cust_person',
            array('readonly'=>$model->isReadonly())
        ); ?>
    </div>
    <?php echo TbHtml::label(Yii::t("clue","Contact Information"),'cust_tel',array('class'=>"col-lg-1 control-label")); ?>
    <div class="col-lg-3">
        <div class="row">
            <div class="col-lg-6 col-lg-right">
                <?php echo $form->textField($model, 'cust_tel',
                    array('readonly'=>$model->isReadonly(),'placeholder'=>'请输入电话','id'=>'cust_tel')
                ); ?>
            </div>
            <div class="col-lg-6 col-lg-left">
                <?php echo $form->textField($model, 'cust_email',
                    array('readonly'=>$model->isReadonly(),'placeholder'=>'请输入邮箱')
                ); ?>
            </div>
        </div>
    </div>
    <?php echo $form->labelEx($model,'cust_person_role',array('class'=>"col-lg-1 control-label")); ?>
    <div class="col-lg-1 col-lg-left">
        <?php echo $form->textField($model, 'cust_person_role',
            array('readonly'=>$model->isReadonly())
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'cust_address',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-5">
        <?php echo $form->textField($model, 'cust_address',
            array('readonly'=>$model->isReadonly())
        ); ?>
    </div>
    <?php echo $form->labelEx($model,'area',array('class'=>"col-lg-1 control-label")); ?>
    <div class="col-lg-2">
        <?php echo $form->numberField($model, 'area',
            array('readonly'=>$model->isReadonly(),'min'=>0,'append'=>Yii::t("clue","m2"))
        ); ?>
    </div>
</div>

<?php if (!empty($model->clue_status)): ?>
    <div class="form-group">
        <?php echo $form->labelEx($model,'end_date',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-3">
            <?php echo $form->textField($model, 'end_date',
                array('readonly'=>true,
                    'prepend'=>'<span class="fa fa-calendar"></span>')
            ); ?>
        </div>
        <?php echo $form->labelEx($model,'last_date',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-3">
            <?php echo $form->textField($model, 'last_date',
                array('readonly'=>true,
                    'prepend'=>'<span class="fa fa-calendar"></span>')
            ); ?>
        </div>
    </div>
<?php endif ?>

<?php

$link3 = Yii::app()->createAbsoluteUrl("visit/getcusttypelist");
switch(Yii::app()->language) {
    case 'zh_cn': $lang = 'zh-CN'; break;
    case 'zh_tw': $lang = 'zh-TW'; break;
    default: $lang = Yii::app()->language;
}
$disabled = $model->isReadonly()? 'true':'false';
$js = <<<EOF
$('#cust_class_group').on('change',function() {
	var group = $(this).val();
	var cust_class = $(this).data('cust_class');
	var data = "group="+group;
	$.ajax({
		type: 'GET',
		url: '$link3',
		data: data,
		success: function(data) {
			$('#cust_class').html(data);
			if(cust_class != undefined){
			    $('#cust_class').val(cust_class);
			    $('#cust_class_group').removeData('cust_class');
			}
		},
		error: function(data) { // if error occured
			var x = 1;
		},
		dataType:'html'
	});
});	
EOF;
if($model->clue_type==2){
    $js.="
$('#support_user').select2({
    multiple: false,
    maximumInputLength: 10,
    language: '$lang',
    disabled: $disabled
});
$('#busine_id,#talk_city_id').select2({
	tags: false,
	multiple: true,
	maximumInputLength: 0,
	maximumSelectionLength: 10,
	allowClear: true,
	language: '$lang',
	disabled: $disabled,
	templateSelection: formatState
});
    ";
}
Yii::app()->clientScript->registerScript('select2_1',$js,CClientScript::POS_READY);
?>
