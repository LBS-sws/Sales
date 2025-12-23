<div class="form-group">
    <?php
    echo TbHtml::label(Yii::t("clue","clue code"),'',array('class'=>"col-lg-2 control-label"));
    ?>
    <div class="col-lg-2">
        <?php
        echo TbHtml::textField("clue_code",$model->clueHeadRow["clue_code"],array(
            "class"=>'form-control','readonly'=>true
        ));
        ?>
    </div>
    <?php
    echo TbHtml::label(Yii::t("clue","clue type"),'',array('class'=>"col-lg-1 control-label"));
    ?>
    <div class="col-lg-2">
        <?php
        echo TbHtml::textField("clue_code",CGetName::getClueTypeStr($model->clueHeadRow["clue_type"]),array(
            "class"=>'form-control','readonly'=>true
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php
    echo TbHtml::label(Yii::t("clue","clue name"),'',array('class'=>"col-lg-2 control-label"));
    ?>
    <div class="col-lg-5">
        <?php
        echo TbHtml::textField("cust_name",$model->clueHeadRow["cust_name"],array(
            "class"=>'form-control','readonly'=>true
        ));
        ?>
    </div>
    <div class="col-lg-5">
        <p class="form-control-static">
            <?php
            if($model->clueHeadRow["table_type"]==1){
                $goUrl = Yii::app()->createUrl('clueHead/view',array('index'=>$model->clue_id));
            }else{
                $goUrl = Yii::app()->createUrl('clientHead/view',array('index'=>$model->clue_id));
            }
            echo TbHtml::link("查看",$goUrl,array(
                "target"=>'_blank'
            ));
            ?>
        </p>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'store_name',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-5">
        <?php
        echo $form->textField($model,'store_name',array(
            "class"=>'form-control','readonly'=>$model->isReadOnly()
        ));
        ?>
    </div>
    <?php if ($model->scenario!='new'): ?>
        <?php echo $form->labelEx($model,'store_code',array('class'=>"col-lg-1 control-label")); ?>
        <div class="col-lg-2">
            <?php
            echo $form->textField($model,'store_code',array(
                "class"=>'form-control','readonly'=>true
            ));
            ?>
        </div>
    <?php endif ?>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'store_full_name',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-2">
        <?php
        echo $form->textField($model,'store_full_name',array(
            "class"=>'form-control','readonly'=>$model->isReadOnly()
        ));
        ?>
    </div>
    <?php
    echo TbHtml::label(Yii::t("clue","city manger"),'',array('class'=>"col-lg-1 control-label","required"=>true));
    ?>
    <div class="col-lg-2">
        <?php
        if($model->clueHeadRow["clue_type"]==1||$model->scenario!='new'){
            echo $form->hiddenField($model, 'city',array("id"=>"city"));
            echo TbHtml::textField("city",General::getCityName($model->city),array(
                "class"=>'form-control','readonly'=>true
            ));
        }else{
            echo $form->dropDownList($model,"city",CGetName::getStoreCityList(),array(
                "class"=>'form-control','readonly'=>$model->isReadonly(),"id"=>"citySelect"
            ));
        }
        ?>
    </div>
    <?php
    echo TbHtml::label(Yii::t("clue","office id"),'',array('class'=>"col-lg-1 control-label","required"=>true));
    ?>
    <div class="col-lg-2">
        <?php
        if($model->clueHeadRow["table_type"]==1&&!$model->isReadonly()){
            echo $form->dropDownList($model,"office_id",CGetName::getOfficeList($model->city),array(
                "class"=>'form-control','readonly'=>$model->isReadonly(),"id"=>"cityOffice"
            ));
        }else{
            echo $form->hiddenField($model, 'office_id',array("id"=>"office_id"));
            echo TbHtml::textField("office_id",CGetName::getOfficeStrByKey($model->office_id,"name"),array(
                "class"=>'form-control','readonly'=>true
            ));
        }
        ?>
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
</div>
<!-- 新增: 门店客户等级和客户标签 -->
<div class="form-group">
    <?php echo $form->labelEx($model,'store_level_id',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php
        echo $form->dropDownList($model, 'store_level_id', ClueStoreForm::getClueLevelList(),
            array('readonly'=>$model->isReadonly(),'id'=>'store_level_id','class'=>'form-control')
        ); ?>
    </div>
    <?php echo $form->labelEx($model,'clue_tag_ids',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php
        echo $form->dropDownList($model, 'clue_tag_ids', ClueStoreForm::getClueTagList(),
            array('readonly'=>$model->isReadonly(),'id'=>'clue_tag_ids','class'=>'form-control select2','multiple'=>'multiple')
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'create_staff',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-2">
        <?php
        if($model->isReadOnly()){
            echo $form->hiddenField($model, 'create_staff');
            echo TbHtml::textField("create_staff",CGetName::getEmployeeNameByKey($model->create_staff),array(
                "class"=>'form-control','readonly'=>true
            ));
        }else{
            $staffList = array();
            if (!empty($model->create_staff)) {
                $staffList[$model->create_staff] = CGetName::getEmployeeNameByKey($model->create_staff);
            }
            echo $form->dropDownList($model,'create_staff',$staffList,array(
                "class"=>'form-control','readonly'=>$model->isReadOnly(),'id'=>'create_staff','empty'=>''
            ));
        }
        ?>
    </div>
    <?php echo $form->labelEx($model,'yewudalei',array('class'=>"col-lg-1 control-label")); ?>
    <div class="col-lg-2">
        <?php
        if($model->isReadOnly()){
            echo $form->hiddenField($model, 'yewudalei');
            echo TbHtml::textField("yewudalei",CGetName::getYewudaleiStrByKey($model->yewudalei),array(
                "class"=>'form-control','readonly'=>true
            ));
        }else{
            echo $form->dropDownList($model,'yewudalei',CGetName::getYewudaleiListByEmployee($model->create_staff),array(
                "class"=>'form-control','readonly'=>$model->isReadOnly(),'id'=>'yewudalei','empty'=>''
            ));
        }
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'district',array('class'=>"col-lg-2 control-label")); ?>
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
            array('readonly'=>$model->isReadOnly(),'autocomplete'=>'off','data-clue'=>$model->clueHeadRow["clue_type"],'class'=>'nationalClick','id'=>'district','data-city'=>$model->city,'data-city_name'=>$cityName,'data-name'=>$districtRow['tree_names'],'data-ids'=>$districtRow['parent_ids'])
        );
        ?>
    </div>
    <?php echo $form->labelEx($model,'area',array('class'=>"col-lg-1 control-label")); ?>
    <div class="col-lg-2">
        <?php
        echo $form->textField($model,'area',array(
            "class"=>'form-control','readonly'=>$model->isReadOnly(),'append'=>'平方米'
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'address',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-5">
        <?php
        echo $form->textField($model,'address',array(
            "class"=>'form-control','readonly'=>$model->isReadOnly(),"id"=>"address"
        ));
        ?>
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
    <?php echo $form->labelEx($model,'invoice_id',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-4">
        <?php
        echo $form->dropDownList($model,'invoice_id',CGetName::getInvoiceList($model->clue_id,$model->invoice_id),array(
            "options"=>CGetName::getInvoiceOptionsList($model->clue_id,$model->invoice_id),
            "class"=>'form-control','readonly'=>$model->isReadOnly(),'empty'=>'自定义',"id"=>"invoice_id"
        ));
        ?>
    </div>
    <div class="col-lg-6">
        <p class="form-control-static text-danger"><?php echo Yii::t("clue","invoice hint");?></p>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'invoice_type',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-2">
        <?php
        echo $form->inlineRadioButtonList($model,'invoice_type',CGetName::getInvoiceTypeList(),array(
            'disabled'=>($model->isReadOnly()||!empty($model->invoice_id)),'baseID'=>'invoice_type','empty'=>'无'
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo Tbhtml::label($model->getAttributeLabel('invoice_header'),false,array('class'=>"col-lg-2 control-label","required"=>($model->invoice_type==1||$model->invoice_type==2||$model->invoice_type==3))); ?>
    <div class="col-lg-2">
        <?php
        echo $form->textField($model,'invoice_header',array(
            "class"=>'form-control','readonly'=>(!empty($model->invoice_id)||empty($model->invoice_type)),"id"=>"invoice_header"
        ));
        ?>
    </div>
    <?php echo Tbhtml::label($model->getAttributeLabel('tax_id'),false,array('class'=>"col-lg-1 control-label","required"=>($model->invoice_type==1||$model->invoice_type==2))); ?>
    <div class="col-lg-2">
        <?php
        echo $form->textField($model,'tax_id',array(
            "class"=>'form-control','readonly'=>(!empty($model->invoice_id)||empty($model->invoice_type)),"id"=>"tax_id"
        ));
        ?>
    </div>
    <?php echo Tbhtml::label($model->getAttributeLabel('invoice_address'),false,array('class'=>"col-lg-1 control-label")); ?>
    <div class="col-lg-2">
        <?php
        echo $form->textField($model,'invoice_address',array(
            "class"=>'form-control','readonly'=>(!empty($model->invoice_id)||empty($model->invoice_type)),"id"=>"invoice_address"
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo Tbhtml::label($model->getAttributeLabel('invoice_number'),false,array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-2">
        <?php
        echo $form->textField($model,'invoice_number',array(
            "class"=>'form-control','readonly'=>(!empty($model->invoice_id)||empty($model->invoice_type)),'id'=>'invoice_number'
        ));
        ?>
    </div>
    <?php echo Tbhtml::label($model->getAttributeLabel('invoice_user'),false,array('class'=>"col-lg-1 control-label")); ?>
    <div class="col-lg-2">
        <?php
        echo $form->textField($model,'invoice_user',array(
            "class"=>'form-control','readonly'=>(!empty($model->invoice_id)||empty($model->invoice_type)),'id'=>'invoice_user'
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'store_remark',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-4">
        <?php
        echo $form->textArea($model,'store_remark',array(
            "class"=>'form-control','readonly'=>$model->isReadOnly(),'rows'=>4
        ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'z_display',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-8">
        <?php
        echo $form->inlineRadioButtonList($model,'z_display',CGetName::getDisplayList(),array(
            'readonly'=>$model->isReadOnly()
        ));
        ?>
    </div>
</div>
<?php
$js = <<<'JS'
$(function() {
    // 初始化客户标签多选下拉框
    $('#clue_tag_ids').select2({
        tags: false,
        multiple: true,
        maximumInputLength: 0,
        allowClear: true,
        disabled: false
    });
});
JS;
Yii::app()->clientScript->registerScript('storeFormTagsInit',$js,CClientScript::POS_END);
?>
<style>
    /* 修复 select2 标签文字颜色 */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        color: #333;
        background-color: #f5f5f5;
    }
</style>
