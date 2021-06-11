
<?php
	$ftrbtn = array();
$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_DEFAULT,"class"=>"pull-left"));
$ftrbtn[] = TbHtml::button(Yii::t('redeem','apply'), array('color'=>TbHtml::BUTTON_COLOR_PRIMARY,'submit'=>$submit));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'integralApply',
					'header'=>Yii::t('redeem','apply cut'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>

    <div class="form-group">
        <?php echo TbHtml::label(Yii::t('redeem','Cut Name'),'',array('class'=>"col-sm-2 control-label")); ?>
        <div class="col-sm-4">
            <?php echo TbHtml::hiddenField('GiftApply[id]','',
                array('id'=>'id')
            ); ?>
            <?php echo TbHtml::textField('GiftApply[gift_name]','',
                array('readonly'=>(true),'id'=>'gift_name')
            ); ?>
        </div>
        <?php echo TbHtml::label(Yii::t('redeem','Cut Integral'),'',array('class'=>"col-sm-2 control-label")); ?>
        <div class="col-sm-4">
            <?php echo TbHtml::numberField('GiftApply[bonus_point]','',
                array('readonly'=>(true),'id'=>'bonus_point')
            ); ?>
        </div>
    </div>

    <div class="form-group">
        <?php echo TbHtml::label(Yii::t('redeem','Number of applications'),'',array('class'=>"col-sm-2 control-label")); ?>
        <div class="col-sm-4">
            <?php echo TbHtml::numberField('GiftApply[apply_num]','1',
                array('readonly'=>(false))
            ); ?>
        </div>
    </div>

    <div class="form-group">
        <?php echo TbHtml::label(Yii::t('redeem','Remark'),'',array('class'=>"col-sm-2 control-label")); ?>
        <div class="col-sm-8">
            <?php echo TbHtml::textArea('GiftApply[remark]','',
                array('rows'=>4,'cols'=>50,'maxlength'=>1000,'readonly'=>(false))
            ); ?>
        </div>
    </div>
<?php
	$this->endWidget();
?>