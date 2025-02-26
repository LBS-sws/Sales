<?php
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','class'=>'pull-left','color'=>TbHtml::BUTTON_COLOR_DEFAULT));
	$ftrbtn[] = TbHtml::button(Yii::t('market','Success'), array('color'=>TbHtml::BUTTON_COLOR_PRIMARY,'id'=>"btnSuccessMarket"));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'successDialog',
					'header'=>Yii::t('market','Success'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>

<div class="form-horizontal">
    <div class="form-group">
        <div class="col-lg-10 col-lg-offset-2">
            <p class="form-control-static">完成后，该资料会转成已完成，且无法修改及跟进。</p>
        </div>
        <div class="col-lg-10 col-lg-offset-2">
            <p class="form-control-static text-danger">您确定要完成该资料吗？</p>
        </div>
    </div>
</div>

<?php
	$this->endWidget();
?>
<?php
$assignUrl = Yii::app()->createUrl('marketCompany/success');
$submit = empty($submit)?$assignUrl:$submit;
$jsHtml=isset($jsHtml)?$jsHtml:"";
$type_num=isset($type_num)?$type_num:0;
$js = <<<EOF

$('#btnSuccessMarket').on('click',function(){
    var data = {};
    {$jsHtml}
    data['type_num']='{$type_num}';
    data['assign_id']=$('#assign_id').val();
    jQuery.yii.submitForm(this,'{$submit}',data);
});
EOF;
Yii::app()->clientScript->registerScript('successMarket',$js,CClientScript::POS_READY);