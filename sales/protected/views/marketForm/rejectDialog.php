<?php
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','class'=>'pull-left','color'=>TbHtml::BUTTON_COLOR_DEFAULT));
	$ftrbtn[] = TbHtml::button(Yii::t('market','OK'), array('color'=>TbHtml::BUTTON_COLOR_PRIMARY,'id'=>"btnRejectMarket"));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'rejectDialog',
					'header'=>Yii::t('market','Reject'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>

<div class="form-horizontal">
    <div class="form-group">
        <div class="col-lg-10 col-lg-offset-1">
            <p class="form-control-static text-danger">确定后，该资料会转成无意向，且无法修改及跟进。</p>
        </div>
    </div>
    <div class="form-group">
        <?php echo TbHtml::label(Yii::t("market","reject note"),'',array('class'=>"col-lg-3 control-label")); ?>
        <div class="col-lg-7">
            <?php echo TbHtml::textArea('reject_note','',
                array('readonly'=>(false),'rows'=>4)
            ); ?>
        </div>
    </div>
</div>

<?php
	$this->endWidget();
?>
<?php
$assignUrl = Yii::app()->createUrl('marketCompany/reject');
$submit = empty($submit)?$assignUrl:$submit;
$jsHtml=isset($jsHtml)?$jsHtml:"";
$type_num=isset($type_num)?$type_num:0;
$js = <<<EOF

$('#btnRejectMarket').on('click',function(){
    var data = {};
    {$jsHtml}
    data['type_num']='{$type_num}';
    data['assign_id']=$('#assign_id').val();
    data['reject_note']=$('#reject_note').val();
    jQuery.yii.submitForm(this,'{$submit}',data);
});
EOF;
Yii::app()->clientScript->registerScript('rejectMarket',$js,CClientScript::POS_READY);