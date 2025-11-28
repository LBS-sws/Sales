<?php
    $contIDRows = CGetName::getContractSelectByNoStore($model->clue_id,$model->id);
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal'));
	if(!empty($contIDRows)){
        $ftrbtn[] = TbHtml::link(Yii::t('dialog','OK'),'javascript:void(0);', array(
            'class'=>"btn btn-primary",
            'id'=>"bindingContOk",
            'data-url'=>Yii::app()->createUrl('contPro/new',array("store_id"=>$model->id,"type"=>"NA"))
        ));
    }
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'bindingContDialog',
					'header'=>Yii::t('clue','binding contract'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>
<div class="form-group">
    <?php echo TbHtml::label(Yii::t("clue","contract"),'contract',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
    <div class="col-lg-9">
        <?php
        if(!empty($contIDRows)){
            echo TbHtml::dropDownList("contract","",$contIDRows,array(
                "class"=>'form-control','id'=>'contract'
            ));
        }else{
            echo "<p class='form-control-static'>没有可关联的合约</p>";
        }
        ?>
    </div>
</div>

<?php
	$this->endWidget();
?>
<?php

$js = <<<EOF
$('#bindingContOk').on('click',function() {
    var url = $(this).data('url');
    var contract = $('#contract').val();
    window.location.href=url+"&cont_id="+contract;
});	
EOF;
Yii::app()->clientScript->registerScript('bindingContOk',$js,CClientScript::POS_READY);
?>