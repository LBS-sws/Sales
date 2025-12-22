<?php
$this->pageTitle=Yii::app()->name . ' - Client Head Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    .bg_clue_service{ background: #ecf0f5;padding-top: 15px;margin-left: -10px;margin-right: -10px;}
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
		<strong><?php echo Yii::t('clue','Client Form'); ?></strong>
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
                        $backSubmit = Yii::app()->createUrl('clientHead/index');
                }
                echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>$backSubmit));
                ?>
            </div>
            <div class="btn-group pull-right" role="group">
                <?php echo TbHtml::button('<span class="fa fa-edit"></span> '.Yii::t('clue','update'), array(
                    'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                        'submit'=>Yii::app()->createUrl('clientHead/edit',array("index"=>$model->id))));
                ?>
            </div>
        </div>
    </div>

    <!--客户-->
	<div class="box box-info">
		<div class="box-body">
            <legend><?php echo Yii::t('clue',"customer detail");?></legend>
            <div class="clue_detail">
                <?php
                $this->renderPartial("//clue/clue_form",array("form"=>$form,"model"=>$model));
                ?>
            </div>
		</div>
	</div>
</section>

<?php
$this->renderPartial('//site/confirmDialog',array(
    "idNum"=>1,
    "header"=>Yii::t('clue','delete'),
    "content"=>"<p>".Yii::t('clue','delete client flow body')."</p>",
    "submit"=>Yii::app()->createUrl('clueFlow/delClueFlow'),
));
?>
<?php
$this->renderPartial('//site/confirmDialog',array(
    "idNum"=>2,
    "header"=>Yii::t('clue','delete'),
    "content"=>"<p>".Yii::t('clue','delete client sse body')."</p>",
    "submit"=>Yii::app()->createUrl('clueSSE/del'),
));
?>
<?php $this->renderPartial('//clue/invoicelist',array("model"=>$model)); ?>
<?php $this->renderPartial('//clue/storelist',array("model"=>$model)); ?>
<?php $this->renderPartial('//clue/historylist',array("model"=>$model)); ?>
<?php //$this->renderPartial('//clue/clue_service_form',array("model"=>$model,"actionUrl"=>Yii::app()->createUrl('clueService/addClueService'))); ?>
<?php
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
                $(obj).find('script').each(function(){
                    if(this.text){
                        $.globalEval(this.text);
                    }else if(this.textContent){
                        $.globalEval(this.textContent);
                    }else if(this.innerHTML){
                        $.globalEval(this.innerHTML);
                    }
                });
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
                $(obj).find('script').each(function(){
                    if(this.text){
                        $.globalEval(this.text);
                    }else if(this.textContent){
                        $.globalEval(this.textContent);
                    }else if(this.innerHTML){
                        $.globalEval(this.innerHTML);
                    }
                });
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
        $('#clueFlowAndStore').find('script').each(function(){
            if(this.text){
                $.globalEval(this.text);
            }else if(this.textContent){
                $.globalEval(this.textContent);
            }else if(this.innerHTML){
                $.globalEval(this.innerHTML);
            }
        });
        select2SSE(response);
    }
}
$('.bat_phone_div_click:first').trigger('click');
EOF;
Yii::app()->clientScript->registerScript('win_sotre_change',$js,CClientScript::POS_READY);
?>
<?php
$clueServiceLink = Yii::app()->createUrl('clientHead/view');
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

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

<section class="content" style="padding-top: 0px;">
<div class="box box-info">
    <div class="box-body">
        <?php
        $tabs=array();
        //商机
        $tabs[] = array(
            'label'=>Yii::t('clue',"find client service"),
            'content'=>$this->renderPartial('//clientHead/dv_service',array("model"=>$model),true),
            'active'=>true,
            "id"=>"clue_dv_service"
        );
        //方案报价
        $tabs[] = array(
            'label'=>Yii::t('clue',"client report"),
            'content'=>$this->renderPartial('//clientHead/dv_report',array("model"=>$model),true),
            'active'=>false,
            "id"=>"clue_dv_report"
        );
        //合约信息
        $tabs[] = array(
            'label'=>Yii::t('clue',"client contract"),
            'content'=>$this->renderPartial('//clientHead/dv_contract',array("model"=>$model),true),
            'active'=>false,
            "id"=>"clue_dv_contract"
        );
        //客户门店
        $tabs[] = array(
            'label'=>Yii::t('clue',"client store"),
            'content'=>$this->renderPartial('//clientHead/dv_store',array("model"=>$model),true),
            'active'=>false,
            "id"=>"clue_dv_store"
        );
        //联系人
        $tabs[] = array(
            'label'=>Yii::t('clue',"client person"),
            'content'=>$this->renderPartial('//clientHead/dv_person',array("model"=>$model),true),
            'active'=>false,
            "id"=>"clue_dv_person"
        );
        //操作记录
        $tabs[] = array(
            'label'=>Yii::t('clue',"client operation"),
            'content'=>$this->renderPartial('//clientHead/dv_operation',array("model"=>$model),true),
            'active'=>false,
            "id"=>"clue_dv_operation"
        );
        //开票信息
        $tabs[] = array(
            'label'=>Yii::t('clue',"client invoice"),
            'content'=>$this->renderPartial('//clientHead/dv_invoice',array("model"=>$model),true),
            'active'=>false,
            "id"=>"clue_dv_invoice"
        );
        //项目负责人
        $tabs[] = array(
            'label'=>Yii::t('clue',"client u staff"),
            'content'=>$this->renderPartial('//clientHead/dv_u_staff',array("model"=>$model),true),
            'active'=>false,
            "id"=>"clue_dv_u_staff"
        );
        //项目所属区域
        $tabs[] = array(
            'label'=>Yii::t('clue',"client u area"),
            'content'=>$this->renderPartial('//clientHead/dv_u_area',array("model"=>$model),true),
            'active'=>false,
            "id"=>"clue_dv_u_area"
        );
        echo TbHtml::tabbableTabs($tabs);
        ?>
    </div>
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
