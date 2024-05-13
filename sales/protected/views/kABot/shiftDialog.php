<?php
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_DEFAULT));
	$ftrbtn[] = TbHtml::button(Yii::t('ka','Shift'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY,'submit'=>$submit));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'shiftDialog',
					'header'=>Yii::t('ka','Shift'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>

<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("customer_no"),'',array('class'=>"col-sm-4 control-label")); ?>
    <div class="col-sm-6">
        <?php
        echo TbHtml::textField("customer_name",$model->customer_no,array("readonly"=>true));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label($model->getAttributeLabel("kam_id"),'',array('class'=>"col-sm-4 control-label")); ?>
    <div class="col-sm-6">
        <?php
        echo TbHtml::textField("kam_name",$model->kam_name,array("readonly"=>true));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label("转移后类型",'',array('class'=>"col-sm-4 control-label","required"=>true)); ?>
    <div class="col-sm-6">
        <?php
        echo TbHtml::dropDownList("shift_to_tab",$model->getThisTablePre(),KABotForm::getTablePreList(),array("readonly"=>false,"empty"=>""));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label("转移后销售",'',array('class'=>"col-sm-4 control-label","required"=>true)); ?>
    <div class="col-sm-6">
        <?php
        echo TbHtml::dropDownList("shift_to_staff",'',KABotForm::getKABotStaffForAccess(),array("readonly"=>false,"empty"=>""));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label("转移备注",'',array('class'=>"col-sm-4 control-label","required"=>true)); ?>
    <div class="col-sm-6">
        <?php
        echo TbHtml::textArea("shift_remark",'',array("readonly"=>false,"rows"=>4));
        ?>
    </div>
</div>

<?php
	$this->endWidget();
?>
