
<?php if (!empty($model->allot_type)): ?>
    <div class="form-group">
        <?php echo $form->labelEx($model,'allot_type',array('class'=>"col-sm-2 control-label")); ?>
        <div class="col-sm-2">
            <?php
            echo TbHtml::textField("allot_type_aa",MarketFun::getAllowNameToType($model->allot_type,true),array("readonly"=>true));
            ?>
        </div>
        <?php if (in_array($model->allot_type,array(2,3))): ?>
            <?php
            $labelNum = $model->allot_type==2?2:1;
            echo $form->labelEx($model,'allot_city',array('class'=>"col-sm-{$labelNum} control-label"));
            ?>
            <div class="col-sm-2">
                <?php
                echo TbHtml::textField("allot_city_aa",General::getCityName($model->allot_city),array("readonly"=>true));
                ?>
            </div>
        <?php endif ?>
        <?php if (in_array($model->allot_type,array(1,3))): ?>
            <?php
            $labelNum = $model->allot_type==1?2:1;
            echo $form->labelEx($model,'allot_employee',array('class'=>"col-sm-{$labelNum} control-label"));
            ?>
            <div class="col-sm-2">
                <?php
                echo TbHtml::textField("allot_employee_aa",MarketFun::getEmployeeNameForId($model->allot_employee),array("readonly"=>true));
                ?>
            </div>
        <?php endif ?>
    </div>
<?php endif ?>
<div class="form-group">
    <?php echo $form->labelEx($model,'company_name',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-7">
        <?php echo $form->textField($model, 'company_name',
            array('readonly'=>($model->isReadOnly()))
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'city_name',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php
        $cityList = MarketFun::getMarketCityList($model->city_name);
        echo $form->dropDownList($model, 'city_name',$cityList["list"],
            array('readonly'=>($model->isReadOnly()),'empty'=>'','id'=>'city_name','options'=>$cityList["option"],'class'=>'changeCity')
        );
        ?>
    </div>
    <?php echo $form->labelEx($model,'area',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php echo $form->dropDownList($model, 'area',MarketFun::getAreaList(),
            array('readonly'=>($model->isReadOnly()),'id'=>'area')
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'company_date',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php echo $form->textField($model, 'company_date',
            array('readonly'=>($model->isReadOnly()),'id'=>'company_date','prepend'=>'<span class="fa fa-calendar"></span>')
        ); ?>
    </div>
    <?php echo $form->labelEx($model,'company_size',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php echo $form->dropDownList($model, 'company_size',MarketFun::getCompanySizeList(),
            array('readonly'=>($model->isReadOnly()))
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'company_type',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php echo $form->dropDownList($model, 'company_type',MarketFun::getCompanyTypeList(),
            array('readonly'=>($model->isReadOnly()))
        ); ?>
    </div>
    <?php echo $form->labelEx($model,'company_state',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php echo $form->dropDownList($model, 'company_state',MarketFun::getCompanyStateList(),
            array('readonly'=>($model->isReadOnly()))
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'legal_user',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php echo $form->textField($model, 'legal_user',
            array('readonly'=>($model->isReadOnly()))
        ); ?>
    </div>
    <?php echo $form->labelEx($model,'company_web',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php echo $form->textField($model, 'company_web',
            array('readonly'=>($model->isReadOnly()))
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'sign_address',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-7">
        <?php echo $form->textField($model, 'sign_address',
            array('readonly'=>($model->isReadOnly()))
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'run_address',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-7">
        <?php echo $form->textField($model, 'run_address',
            array('readonly'=>($model->isReadOnly()))
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'company_note',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-7">
        <?php echo $form->textArea($model, 'company_note',
            array('readonly'=>($model->isReadOnly()),'rows'=>4)
        ); ?>
    </div>
</div>

<?php if ($model->getScenario()!="new"): ?>
    <div class="form-group">
        <?php echo $form->labelEx($model,'start_date',array('class'=>"col-sm-2 control-label")); ?>
        <div class="col-sm-2">
            <?php echo $form->textField($model, 'start_date',
                array('readonly'=>(true),'prepend'=>'<span class="fa fa-calendar"></span>')
            ); ?>
        </div>
        <?php echo $form->labelEx($model,'end_date',array('class'=>"col-sm-2 control-label")); ?>
        <div class="col-sm-3">
            <?php echo $form->textField($model, 'end_date',
                array('readonly'=>(true),'prepend'=>'<span class="fa fa-calendar"></span>')
            ); ?>
        </div>
    </div>
<?php endif ?>


<div class="box">
    <div class="box-body table-responsive">
        <div class="col-lg-12">
            <div class="row">
                <?php
                $this->widget('ext.layout.TableView2Widget', array(
                    'model'=>$model,
                    'expr_id'=>'user',
                    'attribute'=>'userDetail',
                    'viewhdr'=>'//marketForm/_userhdr',
                    'viewdtl'=>'//marketForm/_userdtl',
                ));
                ?>
            </div>
        </div>
    </div>
</div>


<div class="box">
    <div class="box-body table-responsive">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="row">
                <?php
                $this->widget('ext.layout.TableView2Widget', array(
                    'model'=>$model,
                    'expr_id'=>'info',
                    'attribute'=>'detail',
                    'viewhdr'=>'//marketForm/_formhdr',
                    'viewdtl'=>'//marketForm/_formdtl',
                ));
                ?>
            </div>
        </div>
    </div>
</div>