<?php
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','class'=>'pull-left','color'=>TbHtml::BUTTON_COLOR_DEFAULT));
	$ftrbtn[] = TbHtml::button(Yii::t('market','Assign'), array('color'=>TbHtml::BUTTON_COLOR_PRIMARY,'id'=>'btnAssignMarket'));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'assigndialog',
					'header'=>Yii::t('market','Assign'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>

<div class="form-horizontal">
    <div class="form-group" id="allot_employee_div">
        <?php echo TbHtml::label(Yii::t("market","allot employee"),'',array('class'=>"col-lg-4 control-label")); ?>
        <div class="col-lg-4">
            <?php
            echo TbHtml::dropDownList("allot_employee",'',MarketFun::getKASalesListForCity(Yii::app()->user->city()),array("readonly"=>false,"empty"=>""));
            ?>
        </div>
    </div>
</div>

<?php
	$this->endWidget();
?>
<?php
$assignUrl = Yii::app()->createUrl('marketArea/assign');
$jsHtml=isset($jsHtml)?$jsHtml:"";
$js = <<<EOF

$('#btnAssignMarket').on('click',function(){
    var data = {};
    {$jsHtml}
    data['assign_type']='{$assignType}';
    data['assign_id']=$('#assign_id').val();
    data['allot_employee']=$('#allot_employee').val();
    jQuery.yii.submitForm(this,'{$assignUrl}',data);
});
EOF;
Yii::app()->clientScript->registerScript('assignMarket',$js,CClientScript::POS_READY);

?>