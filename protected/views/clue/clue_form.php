<?php echo $form->hiddenField($model, 'id',array("class"=>'select_check')); ?>
<?php echo $form->hiddenField($model, 'scenario'); ?>
<?php echo $form->hiddenField($model, 'clue_type'); ?>
<?php echo $form->hiddenField($model, 'clue_status'); ?>
<?php echo $form->hiddenField($model, 'table_type'); ?>
<?php echo $form->hiddenField($model, 'city',array("id"=>"clue_city")); ?>
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
    <div class="col-lg-2">
        <?php echo $form->textField($model, 'entry_date',
            array('readonly'=>$model->isReadonly(),'id'=>'entry_date',
                'prepend'=>'<span class="fa fa-calendar"></span>')
        ); ?>
    </div>
    <?php if ($model->rec_type==1||$modelClass=="ClueHeadForm"): ?>
        <?php echo $form->labelEx($model,'rec_employee_id',array('class'=>"col-lg-1 control-label")); ?>
        <div class="col-lg-2">
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
            array('readonly'=>$model->isReadonly(),'id'=>'cust_name','autocomplete'=>'off')
        ); ?>
    </div>
</div>
<?php if ($model->isReadonly()): ?>
    <div class="bat_phone_div_click text-center open"><span>收起</span></div>
<?php endif ?>
<div class="bat_phone_div">
    <div class="form-group">
        <?php echo $form->labelEx($model,'full_name',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-5">
            <?php echo $form->textField($model, 'full_name',
                array('readonly'=>$model->isReadonly(),'id'=>'full_name')
            ); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->labelEx($model,'clue_type',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-2">
            <?php
            echo TbHtml::textField("clue_type",CGetName::getClueTypeStr($model->clue_type),
                array('readonly'=>true)
            );
            ?>
        </div>
        <?php echo $form->labelEx($model,'service_type',array('class'=>"col-lg-1 control-label")); ?>
        <div class="col-lg-2">
            <?php echo $form->dropDownList($model, 'service_type',VisitForm::getServiceTypeList(),
                array('readonly'=>$model->isReadonly(),'id'=>'service_type','class'=>'select2','multiple'=>'multiple')
            ); ?>
        </div>
        <?php if (!$model->isReadonly()): ?>
            <a tabindex="0" role="button" id="popover-a" type="button" data-toggle="popover" data-trigger="focus" data-placement="bottom"><span class="glyphicon glyphicon-info-sign"></span></a>
        <?php endif ?>
        <?php echo $form->labelEx($model,'yewudalei',array('class'=>"col-lg-1 control-label")); ?>
        <div class="col-lg-2">
            <?php
            if($model->isReadonly()){
                echo $form->hiddenField($model,'yewudalei');
                echo TbHtml::textField('yewudalei',CGetName::getYewudaleiStrByKey($model->yewudalei),
                    array('readonly'=>true)
                );
            }else{
                echo $form->dropDownList($model, 'yewudalei',CGetName::getYewudaleiListByEmployee($model->rec_employee_id),
                    array('readonly'=>$model->isReadonly(),'empty'=>'')
                );
            }
            ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->labelEx($model,'group_bool',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-2">
            <?php
            if($model->clue_type==2){
                $model->group_bool='Y';
                echo $form->hiddenField($model, 'group_bool');
                echo TbHtml::inlineRadioButtonList('group_bool',"Y",CGetName::getCustVipList(),
                    array('disabled'=>true)
                );
            }else{
                echo $form->inlineRadioButtonList($model, 'group_bool',CGetName::getCustVipList(),
                    array('disabled'=>$model->isReadonly()||$model->clue_type==2)
                );
            }
            ?>
        </div>
        <?php echo $form->labelEx($model,'cust_vip',array('class'=>"col-lg-1 control-label")); ?>
        <div class="col-lg-2">
            <?php echo $form->inlineRadioButtonList($model, 'cust_vip',CGetName::getCustVipList(),
                array('disabled'=>$model->isReadonly())
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
            if($model->isReadonly()){
                echo $form->hiddenField($model,"cust_class",array("id"=>"cust_class"));
                echo TbHtml::textField("cust_class",CGetName::getCustClassStrByKey($model->cust_class),array('readonly'=>true));
            }else{
                $typelist = CGetName::getCustClassList((empty($model->cust_class_group) ? "0" : $model->cust_class_group));
                echo $form->dropDownList($model, 'cust_class',$typelist,
                    array('readonly'=>$model->isReadonly(),"id"=>"cust_class","empty"=>'')
                );
            }
            ?>
        </div>
        <?php echo $form->labelEx($model,'clue_source',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-3">
            <?php echo $form->dropDownList($model, 'clue_source',KASraForm::getSourceListForId($model->clue_source),
                array('readonly'=>$model->isReadonly(),'id'=>'clue_source')
            ); ?>
        </div>
    </div>
    <!-- ========================================== -->
    <!-- 新增: 客户等级下拉 客户标签 -->
    <!-- ========================================== -->
    <div class="form-group">
        <!-- 客户等级: 单选，需要从 sal_clue_level 表查询 -->
        <?php echo $form->labelEx($model,'clue_level_id',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-3">
            <?php
            // 调用后端方法获取等级列表
            echo $form->dropDownList($model, 'clue_level_id', ClueForm::getClueLevelList(),
                array('readonly'=>$model->isReadonly(),'id'=>'clue_level_id')
            ); ?>
        </div>
        <!-- 新增: 客户标签多选下拉框 -->
        <?php echo $form->labelEx($model,'clue_tag_ids',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-3">
            <?php
            // 调用后端方法获取所有可用的标签并显示为多选下拉框
            echo $form->dropDownList($model, 'clue_tag_ids', ClueForm::getClueTagList(),
                array('readonly'=>$model->isReadonly(),'id'=>'clue_tag_ids','class'=>'select2','multiple'=>'multiple')
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
                echo $form->dropDownList($model, 'cust_type',KASourceForm::getSourceListForId($model->cust_type),
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
        <div class="form-group">
            <?php echo $form->labelEx($model,'cust_ka_class',array('class'=>"col-lg-2 control-label")); ?>
            <div class="col-lg-2">
                <?php
                echo $form->dropDownList($model, 'cust_ka_class',KAClassForm::getClassListForId($model->cust_ka_class),
                    array('readonly'=>$model->isReadonly(),"id"=>"cust_ka_class")
                ); ?>
            </div>
            <?php echo $form->labelEx($model,'cont_person',array('class'=>"col-lg-1 control-label")); ?>
            <div class="col-lg-2">
                <?php
                echo $form->textField($model, "cont_person",
                    array('readonly'=>$model->isReadonly(),"id"=>"cont_person")
                );
                ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model,'cont_tel',array('class'=>"col-lg-2 control-label")); ?>
            <div class="col-lg-2">
                <?php
                echo $form->textField($model, 'cont_tel',
                    array('readonly'=>$model->isReadonly(),"id"=>"cont_tel")
                ); ?>
            </div>
            <?php echo $form->labelEx($model,'cont_email',array('class'=>"col-lg-1 control-label col-lg-left")); ?>
            <div class="col-lg-2">
                <?php
                echo $form->textField($model, "cont_email",
                    array('readonly'=>$model->isReadonly(),"id"=>"cont_email")
                );
                ?>
            </div>
            <?php echo $form->labelEx($model,'cont_person_role',array('class'=>"col-lg-1 control-label col-lg-left")); ?>
            <div class="col-lg-2">
                <?php
                echo $form->textField($model, "cont_person_role",
                    array('readonly'=>$model->isReadonly(),"id"=>"cont_person_role")
                );
                ?>
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
            <?php
            $districtRow = CGetName::getDistrictStrByKey($model->district,"*");
            $cityName=General::getCityName($model->city);
            if(!is_array($districtRow)){
                $nationalList = CGetName::getNationalAreaRowByCityName($cityName);
                $districtRow = array("tree_names"=>"","parent_ids"=>$nationalList?$nationalList["parent_ids"]:"");
            }
            echo $form->hiddenField($model,'district');
            echo TbHtml::textField("district",$districtRow['tree_names'],
                array('readonly'=>$model->isReadOnly(),'autocomplete'=>'off','data-clue'=>$model->clue_type,'class'=>'nationalClick','id'=>'district','data-city'=>$model->city,'data-city_name'=>$cityName,'data-name'=>$districtRow['tree_names'],'data-ids'=>$districtRow['parent_ids'])
            );
            ?>
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
            <?php echo $form->hiddenField($model, 'latitude',array("id"=>"latitude","class"=>'map_lat')); ?>
            <?php echo $form->hiddenField($model, 'longitude',array("id"=>"longitude","class"=>'map_lng')); ?>
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
                    'class'=>'openMapBaiDu',
                    "data-lat"=>$model->latitude,
                    "data-lng"=>$model->longitude,
                    "data-search"=>$model->isReadonly()?0:1,
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
        <?php echo TbHtml::label(Yii::t("clue","Contact Information"),'cust_tel',array('class'=>"col-lg-1 control-label",'required'=>$model->isAttributeRequired('cust_tel'))); ?>
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
    <div class="form-group">
        <?php echo $form->labelEx($model,'clue_remark',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-5">
            <?php
            echo $form->textArea($model, 'clue_remark',
                array('readonly'=>$model->isReadonly(),'rows'=>3)
            ); ?>
        </div>
        <?php if ($model->scenario!='new'): ?>
            <?php echo $form->labelEx($model,'u_id',array('class'=>"col-lg-1 control-label")); ?>
            <div class="col-lg-2">
                <?php
                echo $form->textField($model, 'u_id',
                    array('readonly'=>true,'rows'=>3)
                ); ?>
            </div>
        <?php endif ?>
    </div>

    <?php $this->renderPartial('//clue/file_form',array("model"=>$model,"form"=>$form)); ?>
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
<div class="hide" id="poppver-hint-con">
    <ul class="list-group poppver-hint-con">
        <li class="list-group-item">1.虫害管理：定制虫害管理方案，产出服务报告；<span>IB</span></li>
        <li class="list-group-item">2.卫生间深度清洁；<span>IA</span></li>
        <li class="list-group-item">3.香薰服务系统；<span>飘盈香客户</span></li>
        <li class="list-group-item">4.空气清新机、空间消毒；<span>IC</span></li>
        <li class="list-group-item">5.隔油池/箱、抽油烟机深度清洁<span>IA</span></li>
    </ul>
</div>
<?php

$link3 = Yii::app()->createAbsoluteUrl("clueHead/getcusttypelist");
$ajaxYewudalei = Yii::app()->createAbsoluteUrl("clueHead/ajaxYewudalei");
switch(Yii::app()->language) {
    case 'zh_cn': $lang = 'zh-CN'; break;
    case 'zh_tw': $lang = 'zh-TW'; break;
    default: $lang = Yii::app()->language;
}
$disabled = $model->isReadonly()? 'true':'false';
$js = <<<EOF

$('#popover-a').popover({
    html: true,
    content: $('#poppver-hint-con').html()
});
			$('.bat_phone_div_click').click(function(){
				if($(this).hasClass('open')){
					$(this).removeClass('open');
					$(this).find('span').eq(0).text('展开');
					$(this).next('.bat_phone_div').slideUp(100);
				}else{
					$(this).addClass('open');
					$(this).find('span').eq(0).text('收起');
					$(this).next('.bat_phone_div').slideDown(100);
				}
			});
$('#cust_class_group').on('change',function() {
	var group = $(this).val();
	var data = "group="+group;
	$.ajax({
		type: 'GET',
		url: '$link3',
		data: data,
		success: function(data) {
			$('#cust_class').html(data);
		},
		error: function(data) { // if error occured
			var x = 1;
		},
		dataType:'html'
	});
});	
    $('#district').change(function(){
        $('#address').val($(this).val());
    });
$('#service_type').select2({
	tags: false,
	multiple: true,
	maximumInputLength: 0,
	maximumSelectionLength: 10,
	allowClear: true,
	language: '$lang',
	disabled: $disabled,
	templateSelection: formatState
});
	
// 初始化客户标签多选
$('#clue_tag_ids').select2({
	tags: false,
	multiple: true,
	maximumInputLength: 0,
	allowClear: true,
	language: '$lang',
	disabled: $disabled
});
	
$('#clue_level_id').select2({
	multiple: false,
	maximumInputLength: 0,
	language: '$lang',
	disabled: $disabled
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
<style>
    #popover-a{display: block;padding: 7px 15px;text-align: center;}
    @media (min-width: 1200px){
        #popover-a{ float: left;padding: 7px;margin: 0px -17px;}
    }
    .poppver-hint-con{ margin: -10px -15px;font-size: 12px;width: 400px;}
    .poppver-hint-con span{ float: right;width: 70px;font-weight: bold}
    .popover{ max-width: 400px !important;}
    /* 修复 select2 标签文字颜色 */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        color: #333;
        background-color: #f5f5f5;
    }
</style>