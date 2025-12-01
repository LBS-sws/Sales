<?php
$this->pageTitle=Yii::app()->name . ' - Clue Head Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    .list-inline>.choice{padding-top: 4px;}
    .select2-container.select2-container-disabled .select2-choice {
        background-color: #ddd;
        border-color: #a8a8a8;
    }
    *.readonly{ pointer-events: none;}
    *[readonly]{pointer-events: none;}
    .select2.select2-container{ width: 100%!important;}
    .select2-container--default .select2-selection--multiple .select2-selection__choice{ padding: 0px 7px;}
    .select2-container .select2-selection--single{ height: 34px;}
    .clue_detail .form-group{ margin-bottom: 5px;}
    .clue_service{ margin-left:0px;margin-right: 0px;}
    .clue_service>.row{ margin-left:-15px;margin-right: 0px;}
    .clue_service .mpr-0{padding-left: 15px;padding-right: 0px;}
    .box-clue-service{ background: #f2f2f2;}
    .box-clue-service>.box-body{ min-height: 212px;cursor: pointer;}
    .box-clue-service.box-active{ background: #fff;}
    .clickBoxService{ overflow: hidden;}
    .clue-service-total{ border-left: 1px solid #dddddd;}
    .clue-service-visit>p,.clue-service-total>p{ margin-bottom: 2px;white-space: nowrap;overflow: visible;height: 20px}
    .clue-service-visit>p.box_lbs_main{ white-space: normal;height: auto}
    .clue-service-total{ }
    .clue-service-footer{ padding-top: 5px;}
    #clue-service-add{ cursor: pointer;text-align: center;line-height: 192px;}
    .win_sse_form{ position: relative;}
    .win_sse_form>td:before{ content: "...";position: absolute;left: 0px;top: 0px;height: 15px;line-height: 15px;}
    .win_sse_form.active>td:before{ content: "";height: 0px;}
    .win_sse_form form{ float:left;width:100%;height: 2px;overflow: hidden;}
    .win_sse_form.active form{ height: auto;overflow: visible;}
    @media (min-width: 1200px){
        .col-lg-right{ padding-right: 0px;}
        .col-lg-left{ padding-left: 0px;}
    }
    .bat_phone_div_click.open{ }
    .bat_phone_div_click{ border-top:1px solid #d2d6de;padding-bottom:4px;}
    .bat_service_div_click{ margin-top: -10px;padding-left: 15px;padding-right: 0px;padding-bottom: 10px;}
    .bat_service_div_click>div{ border-top:1px solid #d2d6de;background: #367fa9;border-radius: 5px;}
    .bat_phone_div_click span,.bat_service_div_click span{ display:inline-block; padding:7px 12px;background-color: #367fa9;  color: #fff;}
</style>
<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('clue','Clue Form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box">
        <div class="box-body">
            <div class="btn-group" role="group">
                <?php
                switch ($clueDetail){
                    case "service"://商机列表
                        $backSubmit = Yii::app()->createUrl('clueService/index');
                        break;
                    default:
                        $backSubmit = Yii::app()->createUrl('clueHead/index');
                }
                echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>$backSubmit));
                ?>
                <?php echo TbHtml::button('<span class="fa fa-reply-all"></span> '.Yii::t('clue','back clue box'), array(
                    'data-toggle'=>'modal','data-target'=>'#confirmDialog'));
                ?>
            </div>
            <div class="btn-group pull-right" role="group">
                <?php echo TbHtml::button('<span class="fa fa-edit"></span> '.Yii::t('clue','update'), array(
                    'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                    'submit'=>Yii::app()->createUrl('clueHead/edit',array("index"=>$model->id))));
                ?>
                <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('clue','invoice list'), array(
                    'data-toggle'=>'modal','data-target'=>'#clueInvoiceDialog'));
                ?>
                <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('clue','store list'), array(
                    'data-toggle'=>'modal','data-target'=>'#clueStoreDialog'));
                ?>
                <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('clue','Clue History'), array(
                        'data-toggle'=>'modal','data-target'=>'#clueHistoryDialog',)
                );
                ?>
            </div>
        </div>
    </div>

    <!--线索-->
	<div class="box box-info">
		<div class="box-body">
            <legend><?php echo Yii::t("clue","customer detail");?></legend>
            <div class="clue_detail">
                <?php
                $this->renderPartial("//clue/clue_form",array("form"=>$form,"model"=>$model));
                ?>
            </div>
		</div>
	</div>
    <!--商机-->
    <h3 id="serviceTitle"><strong><?php echo Yii::t("clue","clue service");?></strong></h3>
    <div class="clue_service">
        <div class="row" id="clue_service_row">
            <?php
            echo ClueServiceForm::printClueServiceBox($this,$model);
            ?>
        </div>
    </div>
</section>

<?php
$this->renderPartial('//site/confirmDialog',array(
    "header"=>Yii::t('clue','back clue box'),
    "content"=>"<p>".Yii::t('clue','back clue box body')."</p>",
    "submit"=>Yii::app()->createUrl('clueHead/backClueBox'),
));
?>
<?php
$this->renderPartial('//site/confirmDialog',array(
    "idNum"=>1,
    "header"=>Yii::t('clue','delete'),
    "content"=>"<p>".Yii::t('clue','delete clue flow body')."</p>",
    "submit"=>Yii::app()->createUrl('clueFlow/delClueFlow'),
));
?>
<?php
$this->renderPartial('//site/confirmDialog',array(
    "idNum"=>2,
    "header"=>Yii::t('clue','delete'),
    "content"=>"<p>".Yii::t('clue','delete clue sse body')."</p>",
    "submit"=>Yii::app()->createUrl('clueSSE/del'),
));
?>
<?php $this->renderPartial('//clue/invoicelist',array("model"=>$model)); ?>
<?php $this->renderPartial('//clue/storelist',array("model"=>$model)); ?>
<?php $this->renderPartial('//clue/historylist',array("model"=>$model)); ?>
<?php //$this->renderPartial('//clue/clue_service_form',array("model"=>$model,"actionUrl"=>Yii::app()->createUrl('clueService/addClueService'))); ?>
<?php
$clueServiceLink = Yii::app()->createUrl('clueHead/view');
$js = <<<EOF
$('body').on('click','.clickBoxService',function(){
    var url = '{$clueServiceLink}';
    url+='?index='+$(this).data('clue_id')+'&service_id='+$(this).data('id');
    window.location.href=url;
});
EOF;
Yii::app()->clientScript->registerScript('clickBoxService',$js,CClientScript::POS_READY);
$js = <<<EOF
function formatState(state) {
	var rtn = $('<span style="color:black">'+state.text+'</span>');
	return rtn;
}
EOF;
Yii::app()->clientScript->registerScript('formatState',$js,CClientScript::POS_READY);

$link3 = Yii::app()->createAbsoluteUrl("clueHead/getcusttypelist");
$js = <<<EOF
$('body').on('change','.win_cust_class_group',function() {
	var group = $(this).val();
	var data = "group="+group;
	$.ajax({
		type: 'GET',
		url: '$link3',
		data: data,
		success: function(data) {
			$('.win_cust_class').html(data);
		},
		error: function(data) { // if error occured
			var x = 1;
		},
		dataType:'html'
	});
});	
$('body').on('click','.sse-form-save',function(){
    var submitUrl = $(this).data('submit');
    var thisObj = $(this);
    var ajaxBool = $(this).data('ajax');
    var obj = $(this).data('obj');
    var formData = $(this).parents('form:first').serialize(); // 序列化表单数据
    if(ajaxBool==1){
        return false;//已经在加载了
    }else{
        thisObj.data('ajax',1);
    }
    $.ajax({
        type: "POST", // 请求类型
        url: submitUrl, // 服务器端点URL
        data: formData, // 发送到服务器的数据
        dataType: "JSON", // 
        success: function(response) {
            thisObj.data('ajax',0);
            // 请求成功时的回调函数
            if(response.status==1){
                $(obj).html(response.html);
                select2SSE(response);
                showFormErrorHtml('保存成功!');
            }else{
                showFormErrorHtml(response.error);
            }
        },
        error: function(xhr, status, error) {
            // 请求失败时的回调函数
            showFormErrorHtml(error);
            thisObj.data('ajax',0);
        }
    });
});
$('body').on('click','.sse-all-form-save',function(){
    var submitUrl = $(this).data('submit');
    var thisObj = $(this);
    var ajaxBool = $(this).data('ajax');
    var obj = $(this).data('obj');
    var formData = {}; // 所有表单数据
    $('#clue_service_store form').each(function(index,obj){
        formData[index] = $(this).serialize();
    });
    if(ajaxBool==1||formData.length<1){
        return false;//已经在加载了
    }else{
        thisObj.data('ajax',1);
    }
    $.ajax({
        type: "POST", // 请求类型
        url: submitUrl, // 服务器端点URL
        data: formData, // 发送到服务器的数据
        dataType: "JSON", // 
        success: function(response) {
            thisObj.data('ajax',0);
            // 请求成功时的回调函数
            if(response.status==1){
                $(obj).html(response.html);
                select2SSE(response);
                showFormErrorHtml('保存成功!');
            }else{
                showFormErrorHtml(response.error);
            }
        },
        error: function(xhr, status, error) {
            // 请求失败时的回调函数
            showFormErrorHtml(error);
            thisObj.data('ajax',0);
        }
    });
});

function changeServiceRow(response){
    $('#clue_service_row').html(response.htmlService);
}
function clickServiceRow(response){
    if(response.htmlFlow!=''){
        $('#clueFlowAndStore').html(response.htmlFlow);
        select2SSE(response);
    }
}
$('.bat_phone_div_click:first').trigger('click');
EOF;
Yii::app()->clientScript->registerScript('win_sotre_change',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

<section class="content" style="padding-top: 0px;">
<!--商机跟进记录、关联门店-->
<div id="clueFlowAndStore">
    <?php
    echo ClueFlowForm::printClueFlowAndStoreBox($this,$model);
    ?>
</div>
</section>

<?php
$this->renderPartial('//clue/openForm');
?>
<?php
$this->renderPartial('//clue/map_baidu',array(
    "model"=>$model,
));
?>
<?php
$this->renderPartial('//clue/nationalArea');
?>
<?php
$this->renderPartial('//clue/importClueDialog',array("importType"=>"clueStore","code"=>$model->clue_code));
?>