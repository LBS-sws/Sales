<?php
//甲方信息
$modelClass = get_class($model);
?>
<div class="box box-info">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong><?php echo Yii::t("clue","Party A Information");?></strong>
                <span class="text-info fa fa-angle-right"></span>
            </h4>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label(Yii::t("clue","customer name"),'cust_name',array('class'=>"col-lg-1 control-label")); ?>
            <div class="col-lg-3">
                <?php echo $form->textField($model, 'clueHeadRow[cust_name]',
                    array('readonly'=>true,'id'=>'cust_name')
                ); ?>
            </div>
            <?php echo TbHtml::label(Yii::t("clue","customer class"),'cust_name',array('class'=>"col-lg-1 control-label")); ?>
            <div class="col-lg-3">
                <div class="row">
                    <div class="col-lg-6 col-lg-right">
                        <?php
                        echo $form->dropDownList($model, 'clueHeadRow[cust_class_group]',CGetName::getCustTypeGroupList(),
                            array('readonly'=>true,"id"=>"cust_class_group","empty"=>"")
                        ); ?>
                    </div>
                    <div class="col-lg-6 col-lg-left">
                        <?php
                        $typelist = CGetName::getCustClassList((empty($model->clueHeadRow["cust_class_group"]) ? "0" : $model->clueHeadRow["cust_class_group"]));
                        echo $form->dropDownList($model, 'clueHeadRow[cust_class]',$typelist,
                            array('readonly'=>true,"id"=>"cust_class","empty"=>"")
                        ); ?>
                    </div>
                </div>
            </div>
            <?php echo TbHtml::label(Yii::t("clue","service type"),'cust_name',array('class'=>"col-lg-1 control-label")); ?>
            <div class="col-lg-3">
                <?php echo $form->dropDownList($model, 'clueHeadRow[service_type]',VisitForm::getServiceTypeList(),
                    array('readonly'=>true,"empty"=>"")
                ); ?>
            </div>
        </div>
        <div class="information-hide">
            <?php if ($model->clue_type==2): ?>
                <div class="form-group">
                    <?php echo TbHtml::label(Yii::t("clue","customer type"),'cust_name',array('class'=>"col-lg-1 control-label")); ?>
                    <div class="col-lg-3">
                        <?php
                        echo $form->dropDownList($model, 'clueHeadRow[cust_type]',KAClassForm::getClassListForId($model->clueHeadRow["cust_type"]),
                            array('readonly'=>true,"empty"=>"")
                        ); ?>
                    </div>
                    <?php echo TbHtml::label(Yii::t("clue","clue source"),'cust_name',array('class'=>"col-lg-1 control-label")); ?>
                    <div class="col-lg-2">
                        <?php
                        echo $form->dropDownList($model, 'clueHeadRow[clue_source]',KASraForm::getSourceListForId($model->clueHeadRow["clue_source"]),
                            array('readonly'=>true,"empty"=>"")
                        ); ?>
                    </div>
                    <?php echo TbHtml::label(Yii::t("clue","customer type"),'cust_name',array('class'=>"col-lg-1 control-label")); ?>
                    <div class="col-lg-2">
                        <?php
                        echo $form->dropDownList($model, 'clueHeadRow[cust_level]',KALevelForm::getLevelListForId($model->clueHeadRow["cust_level"]),
                            array('readonly'=>true,"empty"=>"")
                        ); ?>
                    </div>
                </div>
            <?php endif ?>
            <div class="form-group">
                <?php echo TbHtml::label(Yii::t("clue","district"),'cust_name',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    $district = General::getCityName($model->city);
                    $district.= "-".CGetName::getDistrictStrByKey($model->clueHeadRow["district"]);
                    echo TbHtml::textField('clueHeadRow[cust_name]',$district,
                        array('readonly'=>true)
                    ); ?>
                </div>
                <?php echo TbHtml::label(Yii::t("clue","address"),'cust_name',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->textField($model, 'clueHeadRow[address]',
                        array('readonly'=>true)
                    ); ?>
                </div>
                <?php echo TbHtml::label(Yii::t("clue","customer vip"),'cust_name',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->inlineRadioButtonList($model, 'clueHeadRow[cust_vip]',CGetName::getCustVipList(),
                        array('disabled'=>true)
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label(Yii::t("clue","client person"),'cust_name',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->textField($model, 'clueHeadRow[cust_person]',
                        array('readonly'=>true)
                    ); ?>
                </div>
                <?php echo TbHtml::label(Yii::t("clue","Contact Information"),'cust_name',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <div class="row">
                        <div class="col-lg-6 col-lg-right">
                            <?php echo $form->textField($model, 'clueHeadRow[cust_tel]',
                                array('readonly'=>true,'placeholder'=>'请输入电话','id'=>'cust_tel')
                            ); ?>
                        </div>
                        <div class="col-lg-6 col-lg-left">
                            <?php echo $form->textField($model, 'clueHeadRow[cust_email]',
                                array('readonly'=>true,'placeholder'=>'请输入邮箱')
                            ); ?>
                        </div>
                    </div>
                </div>
                <?php echo TbHtml::label(Yii::t("clue","person role"),'cust_name',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3 col-lg-left">
                    <?php echo $form->textField($model, 'clueHeadRow[cust_person_role]',
                        array('readonly'=>true)
                    ); ?>
                </div>
            </div>
        </div>
    </div>
</div>
