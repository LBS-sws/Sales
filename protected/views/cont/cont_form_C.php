<?php
//销售信息
$modelClass = get_class($model);
?>
<div class="box box-info">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong><?php echo Yii::t("clue","Sales Information");?></strong>
            </h4>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('sales_id'),"sales_id",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                if(!$model->isReadonly()&&$model->clueHeadRow["clue_type"]==1){
                    echo $form->dropDownList($model, 'sales_id',CGetName::getAssignEmployeeCityList($model->clueHeadRow["city"],$model->sales_id),
                        array('readonly'=>$model->isReadonly(),'id'=>'sales_id','empty'=>'')
                    );
                }else{
                    echo $form->hiddenField($model, 'sales_id');
                    $salesName = CGetName::getEmployeeNameByKey($model->sales_id);
                    echo TbHtml::textField("sales_id",$salesName,array(
                        'readonly'=>true,'id'=>'sales_id'
                    ));
                }
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('yewudalei'),"yewudalei",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

            <div class="col-lg-3">
                <?php
                if($model->isReadonly()){
                    echo $form->hiddenField($model,'yewudalei',array('id'=>'yewudalei'));
                    echo TbHtml::textField('yewudalei',CGetName::getYewudaleiStrByKey($model->yewudalei),
                        array('readonly'=>true,'id'=>'yewudalei_name')
                    );
                }else{
                    echo $form->dropDownList($model, 'yewudalei',CGetName::getYewudaleiListByEmployee($model->sales_id),
                        array('readonly'=>$model->isReadonly(),'id'=>'yewudalei','empty'=>'')
                    );
                }
                ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label($model->getAttributeLabel('other_sales_id'),"other_sales_id",array('class'=>"col-lg-1 control-label")); ?>

            <div class="col-lg-3">
                <?php echo $form->hiddenField($model, 'other_sales_id'); ?>
                <?php
                if($model->isReadonly()){
                    echo $form->hiddenField($model,'other_sales_id');
                    echo TbHtml::textField("other_sales_id",CGetName::getEmployeeNameByKey($model->other_sales_id),array(
                        'readonly'=>true,'id'=>'other_sales_id_text'
                    ));
                }else{
                    $saleslist = CGetName::getVEmployeeListByCity($model->city,$model->other_sales_id);
                    echo $form->dropDownList($model, 'other_sales_id',$saleslist,
                        array('readonly'=>$model->isReadonly(),'id'=>'other_sales_id','empty'=>'')
                    );
                }
                ?>
            </div>
            <?php echo TbHtml::label($model->getAttributeLabel('other_yewudalei'),"yewudalei",array('class'=>"col-lg-1 control-label")); ?>

            <div class="col-lg-3">
                <?php
                if($model->isReadonly()){
                    echo $form->hiddenField($model,'other_yewudalei',array('id'=>'other_yewudalei'));
                    echo TbHtml::textField('other_yewudalei',CGetName::getYewudaleiStrByKey($model->other_yewudalei),
                        array('readonly'=>true,'id'=>'other_yewudalei_name')
                    );
                }else{
                    echo $form->dropDownList($model, 'other_yewudalei',CGetName::getYewudaleiListByEmployee($model->other_sales_id),
                        array('readonly'=>$model->isReadonly(),'id'=>'other_yewudalei','empty'=>'')
                    );
                }
                ?>
            </div>
        </div>
    </div>
</div>
