<?php
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','class'=>'pull-left','color'=>TbHtml::BUTTON_COLOR_DEFAULT));
	$ftrbtn[] = TbHtml::button(Yii::t('market','Back'), array('color'=>TbHtml::BUTTON_COLOR_PRIMARY,'id'=>"btnBackMarket"));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'backDialog',
					'header'=>Yii::t('market','Back'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>

<div class="form-horizontal">
    <div class="form-group">
        <?php echo TbHtml::label(Yii::t("market","back note"),'',array('class'=>"col-lg-3 control-label")); ?>
        <div class="col-lg-7">
            <?php echo TbHtml::textArea( 'back_note','',
                array('readonly'=>(false),'rows'=>4)
            ); ?>
        </div>
    </div>
</div>

<?php
	$this->endWidget();
?>
<?php
$assignUrl = Yii::app()->createUrl('marketCompany/back');
$submit = empty($submit)?$assignUrl:$submit;
$jsHtml=isset($jsHtml)?$jsHtml:"";
$type_num=isset($type_num)?$type_num:0;
$js = <<<EOF

$('#btnBackMarket').on('click',function(){
    var data = {};
    {$jsHtml}
    data['type_num']='{$type_num}';
    data['assign_id']=$('#assign_id').val();
    data['back_note']=$('#back_note').val();
    jQuery.yii.submitForm(this,'{$submit}',data);
});
EOF;
Yii::app()->clientScript->registerScript('backMarket',$js,CClientScript::POS_READY);