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
    <div class="form-group">
        <?php echo TbHtml::label(Yii::t("market","allot type"),'',array('class'=>"col-lg-4 control-label")); ?>
        <div class="col-lg-4">
            <?php
            echo TbHtml::dropDownList("allot_type",2,MarketFun::getAllowTypeList(),array("readonly"=>false));
            ?>
        </div>
    </div>
    <div class="form-group" id="allot_city_div">
        <?php echo TbHtml::label(Yii::t("market","allot city"),'',array('class'=>"col-lg-4 control-label")); ?>
        <div class="col-lg-4">
            <?php
            echo TbHtml::dropDownList("allot_city",'',MarketFun::getAllCityList(),array("empty"=>""));
            ?>
        </div>
    </div>
    <div class="form-group" id="allot_employee_div" style="display: none;">
        <?php echo TbHtml::label(Yii::t("market","allot employee"),'',array('class'=>"col-lg-4 control-label")); ?>
        <div class="col-lg-4">
            <?php
            echo TbHtml::dropDownList("allot_employee",'',MarketFun::getKASalesList(),array("readonly"=>false,"empty"=>""));
            ?>
        </div>
    </div>
</div>

<?php
	$this->endWidget();
?>
<?php
$assignUrl = Yii::app()->createUrl('marketCompany/assign');
$assignUrl = empty($submit)?$assignUrl:$submit;
$jsHtml=isset($jsHtml)?$jsHtml:"";
$js = <<<EOF
$('#allot_type').on('change',function(){
    if($(this).val()==1){
        $('#allot_city_div').hide();
        $('#allot_employee_div').show();
    }else{
        $('#allot_employee_div').hide();
        $('#allot_city_div').show();
    }
});

$('#btnAssignMarket').on('click',function(){
    var data = {};
    {$jsHtml}
    data['assign_type']='{$assignType}';
    data['assign_id']=$('#assign_id').val();
    data['allot_type']=$('#allot_type').val();
    data['allot_city']=$('#allot_city').val();
    data['allot_employee']=$('#allot_employee').val();
    jQuery.yii.submitForm(this,'{$assignUrl}',data);
});
EOF;
Yii::app()->clientScript->registerScript('assignMarket',$js,CClientScript::POS_READY);

?>