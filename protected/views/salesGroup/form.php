<?php
$this->pageTitle=Yii::app()->name . ' - SalesGroup Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'SalesGroup-form',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    .fa{ cursor: pointer;}
    .select2.select2-container{ width: 100%!important;}
    .select2-container--default .select2-selection--multiple .select2-selection__choice{ padding: 0px 7px;}
    .select2-container .select2-selection--single{ height: 34px;}

    .sales-group .fa-caret-right{ padding: 1px 7px;width: 30px;text-align: center;}
    .sales-group .media-left{ border-left: 1px dashed #CCCCCC;}
    .sales-group .media{ position: relative;}
    .media.active>.media-body>.media{ display: block;}
    .media.active>.media-left>.fa-caret-right:before{ content: "\f0d7";}
    .media>.media-body>.media{ display: none;}
    .media,.media-left,.media-body{overflow: visible;}
    .media-left-dashed:before{ content:"";position: absolute;left: -39px;top:9px;width: 39px;height: 0px;border-bottom: 1px dashed #CCCCCC;}
    .media-body>.media:last-child:after{ content:"";position: absolute;left: -41px;top:10px;width: 41px;height: 100%;background: #fff;}

    .del_media{margin-left: 10px;}
</style>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('app','sales group setting'); ?></strong>
    </h1>
    <!--
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="#">Layout</a></li>
            <li class="active">Top Navigation</li>
        </ol>
    -->
</section>

<section class="content">
    <div class="box"><div class="box-body">
            <div class="btn-group" role="group">
                <?php if ($model->scenario!='view'): ?>
                    <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
                        'submit'=>Yii::app()->createUrl('salesGroup/save')));
                    ?>
                <?php endif ?>
            </div>
        </div></div>

    <div class="box box-info">
        <div class="box-body">
            <?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'dataStr',array("id"=>"dataStr")); ?>
            <?php echo TbHtml::hiddenField('temp',$model->getMediaHtml(),array("id"=>"temp")); ?>

            <div class="form-group">
                <div class="col-lg-12">
                    <div class="btn-group" role="group">
                        <?php echo TbHtml::button('全部展开', array(
                            'id'=>"btnDown"));
                        ?>
                        <?php echo TbHtml::button('全部收起', array(
                            'id'=>"btnUp"));
                        ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-12 sales-group">
                    <!--fa-caret-down-->
                    <div class="media active">
                        <div class="media-left"><span class="fa fa-caret-right"></span></div>
                        <div class="media-body" data-id="" data-name="1" data-staff="" data-type="N">
                            <b class="media-heading">
                                <span>人员组织架构</span>
                                <span>&nbsp;</span>
                                <span class="fa fa-plus add_media"></span>
                            </b>
                            <?php
                            echo $model->parentDataJsonHtml();
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<?php

$js = "
$('#btnDown').on('click',function(){
    $('.sales-group').find('.media').addClass('active');
});
$('#btnUp').on('click',function(){
    $('.sales-group').find('.media').removeClass('active');
});

$('#staffDialogOK').on('click', function (e) {
    var temp = $('#temp').val();
    var staff=$('#employee_id').val();
    var staff_text=$('#employee_id option:selected').text();
    if(staff==''){
        return false;
    }
    if($('#staffDialog').data('type')==1){//增加
        var addObj = $('.add_media.add_click').eq(0);
        addObj.parents('.media').eq(0).addClass('active');
        addObj.parents('.media-body').eq(0).append(temp);
        var mediaObj = addObj.parents('.media-body').eq(0).children('.media').eq(-1);
        mediaObj.find('.click-name').eq(0).html(staff_text);
        //mediaObj.attr('data-name',staff_text);
        //mediaObj.attr('data-type','N');
        //mediaObj.attr('data-staff',staff);
        //mediaObj.attr('data-id',0);
        mediaObj.data({'name':staff_text,'type':'N','staff':staff,'id':0});
    }else{
        var updateObj = $('.click-name.update_click').eq(0);
        var bodyObj = updateObj.parents('.media').eq(0);
        updateObj.text(staff_text);
        //bodyObj.attr('data-name',staff_text);
        //bodyObj.attr('data-staff',staff);
        bodyObj.data({'name':staff_text,'staff':staff});
    }
});
$('.sales-group').on('click','.click-name',function(){
    $('#staffDialog .modal-title').text('修改');
    var staff=$(this).parents('.media').eq(0).data('staff');
    var staffText=$(this).text();
    if($('#employee_id').find('option[value=\"'+staff+'\"]').length===0){
        var newOption = new Option(staffText, staff, true, true);
        $('#employee_id').append(newOption);
    }
    $('#employee_id').val(staff).trigger('change');
    $('#staffDialog').data('type',2).modal('show');
    $('.click-name').removeClass('update_click');
    $(this).addClass('update_click');
});
$('.sales-group').on('click','.add_media',function(){
    $('#staffDialog .modal-title').text('增加');
    $('#employee_id').val('').trigger('change');
    $('#staffDialog').data('type',1).modal('show');
    $('.add_media').removeClass('add_click');
    $(this).addClass('add_click');
});
$('.sales-group').on('click','.fa-caret-right',function(){
    if($(this).parents('.media').eq(0).hasClass('active')){
        $(this).parents('.media').eq(0).removeClass('active');
    }else{
        $(this).parents('.media').eq(0).addClass('active');
    }
});
$('.sales-group').on('click','.del_media',function(){
    var name=$(this).parents('.media').eq(0).data('name');
    $('.del_media').removeClass('del_click');
    $(this).addClass('del_click');
    $('#confirmDialog .modal-body>p').text('您确定要删除 '+name+' 这一行及下属员工吗？');
    $('#confirmDialog').modal('show');
});
$('#delBtnOK').on('click',function(){
    $('.del_media.del_click').parents('.media').eq(0).data('type','D');
    $('.del_media.del_click').parents('.media').eq(0).hide();
});


$('#SalesGroup-form').submit(function(){
    var dataArr=[];
    $('.sales-group>.media>.media-body>.media').each(function(){
        dataArr = addForeachByObj(dataArr,this);
    });
    dataArr = JSON.stringify(dataArr);
    $('#dataStr').val(dataArr);
});

function addForeachByObj(dataArr,obj){
    var thisTemp={};
    thisTemp['id']=$(obj).data('id');
    thisTemp['name']=$(obj).data('name');
    thisTemp['staff']=$(obj).data('staff');
    thisTemp['type']=$(obj).data('type');
    thisTemp['list']=[];
    if($(obj).children('.media-body').children('.media').length>0){
        $(obj).children('.media-body').children('.media').each(function(){
            thisTemp['list'] = addForeachByObj(thisTemp['list'],this);
        });
    }
    dataArr.push(thisTemp);
    return dataArr;
}
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>
<?php $this->endWidget(); ?>


<?php
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'staffDialog',
    'header'=>"增加",
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','OK'), array('data-dismiss'=>'modal',
            'id'=>'staffDialogOK',
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY
        )),
    ),
    'show'=>false,
));
?>
<div class="form-horizontal">
    <div class="form-group">
        <?php
        echo Tbhtml::label("选择员工",'',array('class'=>"col-lg-3 control-label"));
        ?>
        <div class="col-lg-5">
            <?php
            echo Tbhtml::dropDownList("employee_id",'',array(),
                array('id'=>"employee_id",'empty'=>'')
            );
            ?>
        </div>
    </div>
</div>
<?php
switch(Yii::app()->language) {
    case 'zh_cn': $lang = 'zh-CN'; break;
    case 'zh_tw': $lang = 'zh-TW'; break;
    default: $lang = Yii::app()->language;
}
$js="
$('#employee_id').select2({
    dropdownParent: $('#staffDialog'),
    multiple: false,
    maximumInputLength: 10,
    language: '$lang',
    disabled: false,
    ajax: {
        url: '".Yii::app()->createUrl('salesGroup/searchEmployee')."',
        type: 'POST',
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return {
                keyword: params.term
            };
        },
        processResults: function (data) {
            return {
                results: data.results
            };
        },
        cache: true
    },
    placeholder: '请输入员工姓名或编号搜索'
});
";
Yii::app()->clientScript->registerScript('addStaffBtn',$js,CClientScript::POS_READY);
?>

<?php
$this->endWidget();
?><?php
$this->widget('bootstrap.widgets.TbModal', array(
    'id'=>'confirmDialog',
    'header'=>"删除",
    'content'=>"<p>".Yii::t('dialog','Are you sure to back?')."</p>",
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','OK'), array('id'=>"delBtnOK",'data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
        TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
    ),
    'show'=>false,
));
?>
