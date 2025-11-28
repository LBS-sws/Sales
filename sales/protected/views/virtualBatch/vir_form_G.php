<div class="information-header">
    <h4>
        <strong>销售信息</strong>
    </h4>
</div>
<div class="form-group">
    <?php echo TbHtml::label($virModel->getAttributeLabel('sales_id'),"sales_id",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
    <div class="col-lg-3">
        <?php
        echo TbHtml::textField("sales_id",CGetName::getEmployeeNameByKey($virModel->sales_id),array(
            'readonly'=>true,'id'=>'sales_id'
        ));
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('yewudalei'),"yewudalei",array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
    <div class="col-lg-3">
        <?php
        if($model->isReadonly()){
            echo $form->hiddenField($virModel,'yewudalei',array('id'=>'yewudalei'));
            echo TbHtml::textField('yewudalei',CGetName::getYewudaleiStrByKey($virModel->yewudalei),
                array('readonly'=>true,'id'=>'yewudalei_name')
            );
        }else{
            echo $form->dropDownList($virModel, 'yewudalei',CGetName::getYewudaleiListByEmployee($virModel->sales_id),
                array('readonly'=>$model->isReadonly(),'id'=>'yewudalei','empty'=>'')
            );
        }
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($virModel->getAttributeLabel('other_sales_id'),"other_sales_id",array('class'=>"col-lg-1 control-label")); ?>
    <div class="col-lg-3">
        <?php
        if($model->isReadonly()){
            echo $form->hiddenField($virModel,'other_sales_id');
            echo TbHtml::textField("other_sales_id",CGetName::getEmployeeNameByKey($virModel->other_sales_id),array(
                'readonly'=>true,'id'=>'other_sales_id'
            ));
        }else{
            $saleslist = CGetName::getVEmployeeListByCity($virModel->city);
            echo $form->dropDownList($virModel, 'other_sales_id',$saleslist,
                array('readonly'=>$model->isReadonly(),'id'=>'other_sales_id','empty'=>'')
            );
        }
        ?>
    </div>
    <?php echo TbHtml::label($virModel->getAttributeLabel('other_yewudalei'),"other_yewudalei",array('class'=>"col-lg-1 control-label")); ?>
    <div class="col-lg-3">
        <?php
        if($model->isReadonly()){
            echo $form->hiddenField($virModel,'other_yewudalei',array('id'=>'other_yewudalei'));
            echo TbHtml::textField('other_yewudalei',CGetName::getYewudaleiStrByKey($virModel->other_yewudalei),
                array('readonly'=>true,'id'=>'other_yewudalei_name')
            );
        }else{
            echo $form->dropDownList($virModel, 'other_yewudalei',CGetName::getYewudaleiListByEmployee($virModel->other_sales_id),
                array('readonly'=>$model->isReadonly(),'id'=>'other_yewudalei','empty'=>'')
            );
        }
        ?>
    </div>
</div>