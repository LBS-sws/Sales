<?php
$className = get_class($model);
?>
<?php echo TbHtml::hiddenField("{$className}[scenario]", $model->scenario); ?>
<?php echo TbHtml::hiddenField("{$className}[id]", $model->id); ?>
<?php echo TbHtml::hiddenField("{$className}[clue_id]", $model->clue_id); ?>

<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("store_name"),'',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
    <div class="col-lg-5">
        <?php
        echo TbHtml::textField("{$className}[store_name]",$model->store_name,array(
            "class"=>'form-control','readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
    <?php if ($model->scenario=='new'): ?>
        <div class="col-lg-5">
            <?php
            echo TbHtml::link("快捷操作-填入客户资料",'javascript:void(0);', array(
                'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                'data-load'=>Yii::app()->createUrl('clueStore/ajaxShow',array("fast"=>1)),
                'data-submit'=>Yii::app()->createUrl('clueStore/ajaxSave'),
                'data-serialize'=>"ClueStoreForm[scenario]=new&ClueStoreForm[city]={$model->city}&ClueStoreForm[clue_id]=".$model->clue_id,
                'data-obj'=>"#clue_dv_store",
                'class'=>'openDialogForm',
                //'submit'=>Yii::app()->createUrl('clueStore/new',array("clue_id"=>$model->id,"type"=>1))
            ));
            ?>
        </div>
    <?php else:?>
        <?php echo TbHtml::label($model->getAttributeLabel("store_code"),'',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
        <div class="col-lg-3">
            <?php
            echo TbHtml::textField("{$className}[store_code]",$model->store_code,array(
                "class"=>'form-control','readonly'=>true
            ));
            ?>
        </div>
    <?php endif ?>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("store_full_name"),'',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-5">
        <?php
        echo TbHtml::textField("{$className}[store_full_name]",$model->store_full_name,array(
            "class"=>'form-control','readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("create_staff"),'',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
    <div class="col-lg-3">
        <?php
        echo TbHtml::dropDownList("{$className}[create_staff]",$model->create_staff,CGetName::getAssignEmployeeAllList($model->create_staff),array(
            "class"=>'form-control','readonly'=>$model->isReadonly(),'id'=>'win_create_staff','empty'=>''
        ));
        ?>
    </div>
    <?php echo TbHtml::label($model->getAttributeLabel("yewudalei"),'',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
    <div class="col-lg-3">
        <?php
        if($model->isReadOnly()){
            echo TbHtml::hiddenField("{$className}[yewudalei]",$model->yewudalei,array("id"=>"win_yewudalei"));
            echo TbHtml::textField("yewudalei",CGetName::getYewudaleiStrByKey($model->yewudalei),array(
                "class"=>'form-control','readonly'=>true
            ));
        }else{
            echo TbHtml::dropDownList("{$className}[yewudalei]",$model->yewudalei,CGetName::getYewudaleiListByEmployee($model->create_staff),array(
                "class"=>'form-control','readonly'=>$model->isReadOnly(),'id'=>'win_yewudalei','empty'=>''
            ));
        }
        ?>
    </div>
</div>
<div class="form-group">
    <?php
    echo TbHtml::label(Yii::t("clue","city manger"),'',array('class'=>"col-lg-2 control-label","required"=>true));
    ?>
    <div class="col-lg-3">
        <?php
        if($model->clueHeadRow["clue_type"]==1||$model->scenario!='new'){
            echo TbHtml::hiddenField("{$className}[city]", $model->city,array("id"=>"win_hide_city"));
            echo TbHtml::textField("city",General::getCityName($model->city),array(
                "class"=>'form-control','readonly'=>true
            ));
        }else{
            echo TbHtml::dropDownList("{$className}[city]",$model->city,CGetName::getStoreCityList(),array(
                "class"=>'form-control','readonly'=>$model->isReadonly(),"id"=>"win_citySelect"
            ));
        }
        ?>
    </div>
    <?php
    echo TbHtml::label(Yii::t("clue","office id"),'',array('class'=>"col-lg-2 control-label","required"=>true));
    ?>
    <div class="col-lg-3">
        <?php
        if($model->scenario!='new'){
            echo TbHtml::hiddenField("{$className}[office_id]", $model->office_id,array("id"=>"win_hide_office_id"));
            echo TbHtml::textField("office_id",CGetName::getOfficeStrByKey($model->office_id,"name"),array(
                "class"=>'form-control','readonly'=>true
            ));
        }else{
            echo TbHtml::dropDownList("{$className}[office_id]",$model->office_id,CGetName::getOfficeList($model->city),array(
                "class"=>'form-control','readonly'=>$model->isReadonly(),"id"=>"win_cityOffice"
            ));
        }
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("cust_class_group"),'',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>

    <div class="col-lg-5">
        <div class="row">
            <div class="col-lg-6 col-lg-right">
                <?php
                echo TbHtml::dropDownList("{$className}[cust_class_group]",$model->cust_class_group,CGetName::getCustTypeGroupList(),array(
                    "class"=>'form-control win_cust_class_group','readonly'=>$model->isReadonly(),'empty'=>''
                ));
                ?>
            </div>
            <div class="col-lg-6 col-lg-left">
                <?php
                $typelist = CGetName::getCustClassList((empty($model->cust_class_group) ? "0" : $model->cust_class_group));
                echo TbHtml::dropDownList("{$className}[cust_class]",$model->cust_class,$typelist,array(
                    "class"=>'form-control win_cust_class','readonly'=>$model->isReadonly(),"empty"=>''
                ));
                ?>
            </div>
        </div>
    </div>
    <?php echo TbHtml::label($model->getAttributeLabel("district"),'',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
    <div class="col-lg-3">
        <?php
        $districtRow = CGetName::getDistrictStrByKey($model->district,"*");
        $cityName=General::getCityName($model->city);
        if(!is_array($districtRow)){
            $nationalList = CGetName::getNationalAreaRowByCityName($cityName);
            $districtRow = array("tree_names"=>"","parent_ids"=>$nationalList?$nationalList["parent_ids"]:"");
        }
        echo TbHtml::hiddenField("{$className}[district]",$model->district);
        echo TbHtml::textField("district",$districtRow['tree_names'],
            array('readonly'=>$model->isReadOnly(),'autocomplete'=>'off','data-clue'=>$model->clueHeadRow["clue_type"],'data-city'=>$model->city,'data-city_name'=>$cityName,'class'=>'nationalClick','id'=>'win_district','data-name'=>$districtRow['tree_names'],'data-ids'=>$districtRow['parent_ids'])
        );
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("address"),'',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-5">
        <?php
        echo TbHtml::textField("{$className}[address]",$model->address,array(
            "class"=>'form-control','readonly'=>$model->isReadonly(),"id"=>"win_address"
        ));
        ?>
    </div>
    <?php echo TbHtml::label($model->getAttributeLabel("area"),'',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php
        echo TbHtml::textField("{$className}[area]",$model->area,array(
            "class"=>'form-control','readonly'=>$model->isReadonly(),'append'=>'平方米'
        ));
        ?>
    </div>
</div>

<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("cust_person"),'',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php
        echo TbHtml::textField("{$className}[cust_person]",$model->cust_person,array(
            "class"=>'form-control','readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
    <?php echo TbHtml::label($model->getAttributeLabel("cust_tel"),'',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-5">
        <div class="row">
            <div class="col-lg-6 col-lg-right">
                <?php
                echo TbHtml::textField("{$className}[cust_tel]",$model->cust_tel,array(
                    "class"=>'form-control','readonly'=>$model->isReadonly(),'placeholder'=>'请输入电话'
                ));
                ?>
            </div>
            <div class="col-lg-6 col-lg-left">
                <?php
                echo TbHtml::textField("{$className}[cust_email]",$model->cust_email,array(
                    "class"=>'form-control','readonly'=>$model->isReadonly(),'placeholder'=>'请输入邮箱'
                ));
                ?>
            </div>
        </div>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("cust_person_role"),'',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php
        echo TbHtml::textField("{$className}[cust_person_role]",$model->cust_person_role,array(
            "class"=>'form-control','readonly'=>$model->isReadonly()
        ));
        ?>
    </div>
    <?php echo TbHtml::label($model->getAttributeLabel("latitude"),'',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo TbHtml::hiddenField("{$className}[latitude]", $model->latitude,array("class"=>'map_lng')); ?>
        <?php echo TbHtml::hiddenField("{$className}[longitude]", $model->longitude,array("class"=>'map_lng')); ?>
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
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("invoice_id"),'',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-5">
        <?php
        echo TbHtml::dropDownList("{$className}[invoice_id]",$model->invoice_id,CGetName::getInvoiceList($model->clue_id,$model->invoice_id),array(
            "options"=>CGetName::getInvoiceOptionsList($model->clue_id,$model->invoice_id),
            "class"=>'form-control','id'=>'win_invoice_id','readonly'=>$model->isReadonly(),'empty'=>'自定义'
        ));
        ?>
    </div>
    <div class="col-lg-5">
        <p class="form-control-static text-danger"><?php echo Yii::t("clue","invoice hint");?></p>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("invoice_type"),'',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::inlineRadioButtonList("{$className}[invoice_type]",$model->invoice_type,CGetName::getInvoiceTypeList(),array(
            'disabled'=>($model->isReadonly()||!empty($model->invoice_id)),'baseID'=>'win_invoice_type','empty'=>'无'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($model->getAttributeLabel("invoice_header"),'',array('class'=>"col-lg-2 control-label","required"=>($model->invoice_type==1||$model->invoice_type==2||$model->invoice_type==3))); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[invoice_header]",$model->invoice_header,array(
            "class"=>'form-control ','id'=>'win_invoice_header','readonly'=>(!empty($model->invoice_id)||empty($model->invoice_type))
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("tax_id"),'',array('class'=>"col-lg-2 control-label","required"=>($model->invoice_type==1||$model->invoice_type==2))); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[tax_id]",$model->tax_id,array(
            "class"=>'form-control ','id'=>'win_tax_id','readonly'=>(!empty($model->invoice_id)||empty($model->invoice_type))
        ));
        ?>
    </div>
    <?php echo TbHtml::label($model->getAttributeLabel("invoice_address"),'',array('class'=>"col-lg-2 control-label","required"=>$model->invoice_type==2)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[invoice_address]",$model->invoice_address,array(
            "class"=>'form-control ','id'=>'win_invoice_address','readonly'=>(!empty($model->invoice_id)||empty($model->invoice_type))
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("invoice_number"),'',array('class'=>"col-lg-2 control-label","required"=>$model->invoice_type==2)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[invoice_number]",$model->invoice_number,array(
            "class"=>'form-control','id'=>'win_invoice_number','readonly'=>(!empty($model->invoice_id)||empty($model->invoice_type))
        ));
        ?>
    </div>
    <?php echo TbHtml::label($model->getAttributeLabel("invoice_user"),'',array('class'=>"col-lg-2 control-label","required"=>$model->invoice_type==2)); ?>
    <div class="col-lg-4">
        <?php
        echo TbHtml::textField("{$className}[invoice_user]",$model->invoice_user,array(
            "class"=>'form-control','id'=>'win_invoice_user','readonly'=>(!empty($model->invoice_id)||empty($model->invoice_type))
        ));
        ?>
    </div>
</div>

<script>
    <?php
    $ajaxArea = Yii::app()->createAbsoluteUrl("clueHead/ajaxArea");
    $ajaxYewudalei = Yii::app()->createAbsoluteUrl("clueHead/ajaxYewudalei");
    $js = <<<EOF
    $('#win_district').change(function(){
        $('#win_address').val($(this).val());
    });
$('#win_create_staff').select2({
    dropdownParent: $('#open-form-Dialog'),
    multiple: false,
    maximumInputLength: 10,
    language: 'zh-CN'
});
    $('#win_citySelect').change(function(){
        $.ajax({
            type: 'GET',
            url: '{$ajaxArea}',
            data: "city="+$('#win_citySelect').val(),
            success: function(data) {
                var officeObj = $(data['officeObj']);
                $('#win_cityOffice').html(officeObj.html());
            },
            error: function(data) { // if error occured
                var x = 1;
            },
            dataType:'json'
        });
    });

    $('#win_invoice_id').on('change',function(){
        if($(this).val()==''){
            //$('#invoice_header').parent('div').prev('label').children('span').remove();
            $('#win_invoice_header').val('').prop('readonly',false);
            $('#win_tax_id').val('').prop('readonly',false);
            $('#win_invoice_address').val('').prop('readonly',false);
            $('#win_invoice_number').val('').prop('readonly',false);
            $('#win_invoice_user').val('').prop('readonly',false);
            $('#win_invoice_type input').prop('checked',false).prop('disabled',false);
            $('#win_invoice_type input[value=""]').trigger('click');
        }else{
            var optionObj = $(this).find('option:selected');
            var invoice_type = optionObj.data('invoice_type');
            $('#win_invoice_header').val(optionObj.data('invoice_header')).prop('readonly',true);
            $('#win_tax_id').val(optionObj.data('tax_id')).prop('readonly',true);
            $('#win_invoice_address').val(optionObj.data('invoice_address')).prop('readonly',true);
            $('#win_invoice_number').val(optionObj.data('invoice_number')).prop('readonly',true);
            $('#win_invoice_user').val(optionObj.data('invoice_user')).prop('readonly',true);
            $('#win_invoice_type input').prop('checked',false).prop('disabled',true);
            $('#win_invoice_type input[value="'+invoice_type+'"]').prop('checked',true).trigger('click');
        }
    });

    $('#win_invoice_type input').click(function(){
        $('#win_invoice_header').parent('div').prev('label').children('span').remove();
        $('#win_tax_id').parent('div').prev('label').children('span').remove();
        $('#win_invoice_address').parent('div').prev('label').children('span').remove();
        $('#win_invoice_number').parent('div').prev('label').children('span').remove();
        $('#win_invoice_user').parent('div').prev('label').children('span').remove();
        var invoice_type = $(this).val();
        switch(invoice_type){
            case '':
                $('#win_invoice_header').prop('readonly',true);
                $('#win_tax_id').prop('readonly',true);
                $('#win_invoice_address').prop('readonly',true);
                $('#win_invoice_number').prop('readonly',true);
                $('#win_invoice_user').prop('readonly',true);
                break;
            case '1':
                $('#win_invoice_header').parent('div').prev('label').append('<span class="required">*</span>');
                $('#win_tax_id').parent('div').prev('label').append('<span class="required">*</span>');
                break;
            case '2':
                $('#win_invoice_header').parent('div').prev('label').append('<span class="required">*</span>');
                $('#win_tax_id').parent('div').prev('label').append('<span class="required">*</span>');
                $('#win_invoice_address').parent('div').prev('label').append('<span class="required">*</span>');
                $('#win_invoice_number').parent('div').prev('label').append('<span class="required">*</span>');
                $('#win_invoice_user').parent('div').prev('label').append('<span class="required">*</span>');
                break;
            case '3':
                $('#win_invoice_header').parent('div').prev('label').append('<span class="required">*</span>');
                break;
        }
        if(!$(this).is(':disabled')&&(invoice_type==1||invoice_type==2||invoice_type==3)){
            $('#win_invoice_header').prop('readonly',false);
            $('#win_tax_id').prop('readonly',false);
            $('#win_invoice_address').prop('readonly',false);
            $('#win_invoice_number').prop('readonly',false);
            $('#win_invoice_user').prop('readonly',false);
        }
    });
$('#win_create_staff').change(function(){
	if($('#win_yewudalei').prop('tagName')=='SELECT'){
        var url = '{$ajaxYewudalei}?employee_id='+$(this).val();
        $.ajax({
            type: 'GET',
            url: url,
            success: function(data) {
                    $('#win_yewudalei').html(data);
            },
            error: function(data) { // if error occured
                var x = 1;
            },
            dataType:'html'
        });
	}
});
EOF;
    echo $js;
    ?>
</script>
