
<?php
$storeNum = CGetName::getClueStoreSumByServiceID($model->clue_service_id);
$addActionUrl=Yii::app()->createUrl('clueFlow/addClueFlow');
$updateActionUrl=Yii::app()->createUrl('clueFlow/updateClueFlow');
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'clueFlowDialog',
    'header'=>Yii::t('clue','add clue flow'),
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','OK'), array('data-dismiss'=>'modal',
            'id'=>'dialogAddClueFlowBtn',
            'data-url_type'=>1,//1:新增 2：修改
            'data-add_url'=>$addActionUrl,
            'data-update_url'=>$updateActionUrl,
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY
        )),
    ),
    'show'=>false,
    'size'=>" modal-lg",
));
?>
<div class="form-horizontal">
    <?php echo TbHtml::hiddenField("ClueFlowForm[id]",0,array('id'=>"win_clue_flow_id"));?>
    <?php echo TbHtml::hiddenField("ClueFlowForm[clue_service_id]",$model->clue_service_id);?>
    <div class="form-group">
        <?php
        echo Tbhtml::label(Yii::t('clue','store num'),'',array('class'=>"col-lg-2 control-label"));
        ?>
        <div class="col-lg-3">
            <?php
            echo Tbhtml::textField("ClueFlowForm[store_num]",$storeNum,
                array('id'=>"win_store_num",'readonly'=>true,'data-val'=>$storeNum)
            );
            ?>
        </div>
        <div class="col-lg-7">
            <div class="btn-group <?php echo empty($storeNum)?"":"hide"?>">
                <?php echo TbHtml::button(Yii::t("clue","add clue store"),array(
                    'data-toggle'=>'modal','data-target'=>'#clueStoreFormDialog','id'=>'#openAddClueStore','color'=>TbHtml::BUTTON_COLOR_PRIMARY
                ));?>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?php
        echo Tbhtml::label(Yii::t('clue','flow date'),'',array('class'=>"col-lg-2 control-label",'required'=>true));
        ?>
        <div class="col-lg-3">
            <?php
            echo Tbhtml::textField("ClueFlowForm[visit_date]",'',
                array('id'=>"win_visit_date",'autocomplete'=>'off','prepend'=>'<span class="fa fa-calendar"></span>')
            );
            ?>
        </div>
        <?php
        echo Tbhtml::label(Yii::t('clue','last flow date'),'',array('class'=>"col-lg-2 control-label"));
        ?>
        <div class="col-lg-3">
            <?php
            echo Tbhtml::textField("ClueFlowForm[last_visit_date]",'',
                array('id'=>"win_last_visit_date",'autocomplete'=>'off','prepend'=>'<span class="fa fa-calendar"></span>')
            );
            ?>
        </div>
    </div>
    <div class="form-group">
        <?php
        echo Tbhtml::label(Yii::t('clue','visit obj'),'',array('class'=>"col-lg-2 control-label",'required'=>true));
        ?>
        <div class="col-lg-8">
            <?php
            echo Tbhtml::dropDownList("ClueFlowForm[visit_obj][]",'',CGetName::getVisitObjList(),
                array('id'=>"win_visit_obj",'multiple'=>'multiple')
            );
            ?>
        </div>
    </div>
    <div class="form-group">
        <?php
        echo Tbhtml::label(Yii::t('clue','sign odds'),'',array('class'=>"col-lg-2 control-label",'required'=>true));
        ?>
        <div class="col-lg-3">
            <?php
            echo Tbhtml::dropDownList("ClueFlowForm[sign_odds]",'',CGetName::getSignOddsList(),
                array('id'=>"win_sign_odds")
            );
            ?>
        </div>
        <?php
        echo Tbhtml::label(Yii::t('clue','rpt bool'),'',array('class'=>"col-lg-2 control-label",'required'=>true));
        ?>
        <div class="col-lg-3">
            <?php
            echo Tbhtml::inlineRadioButtonList("ClueFlowForm[rpt_bool]",0,CGetName::getRptBoolList(),
                array('id'=>"win_rpt_bool")
            );
            ?>
        </div>
    </div>
    <div class="form-group">
        <?php
        echo Tbhtml::label(Yii::t('clue','flow text'),'',array('class'=>"col-lg-2 control-label",'required'=>true));
        ?>
        <div class="col-lg-6">
            <?php
            echo Tbhtml::textArea("ClueFlowForm[visit_text]",'',
                array('id'=>"win_visit_text",'rows'=>4)
            );
            ?>
        </div>
    </div>
    <div class="form-group">
        <?php
        echo Tbhtml::label(Yii::t('clue','lbs main'),'',array('class'=>"col-lg-2 control-label"));
        ?>
        <div class="col-lg-8">
            <?php
            echo Tbhtml::dropDownList("ClueFlowForm[lbs_main]",'',CGetName::getLbsMainList($model->city),
                array('id'=>"win_lbs_main",'empty'=>'')
            );
            ?>
        </div>
    </div>
    <div class="form-group">
        <?php
        echo Tbhtml::label(Yii::t('clue','predict date'),'',array('class'=>"col-lg-2 control-label"));
        ?>
        <div class="col-lg-3">
            <?php
            echo Tbhtml::textField("ClueFlowForm[predict_date]",'',
                array('id'=>"win_predict_date",'autocomplete'=>'off','prepend'=>'<span class="fa fa-calendar"></span>')
            );
            ?>
        </div>
        <?php
        echo Tbhtml::label(Yii::t('clue','predict amt'),'',array('class'=>"col-lg-2 control-label"));
        ?>
        <div class="col-lg-3">
            <?php
            echo Tbhtml::numberField("ClueFlowForm[predict_amt]",'',
                array('id'=>"win_predict_amt",'min'=>0)
            );
            ?>
        </div>
    </div>
</div>
<?php
$thisDate = date("Y/m/d");
$js="
    $('#dialogAddClueFlowBtn').on('click',function(){
        var url = '';
        if($(this).data('url_type')==1){
            url=$(this).data('add_url');
        }else{
            url=$(this).data('update_url');
        }
        jQuery.yii.submitForm(this,url,{});
        return false;
    });
    
$('#win_visit_obj').select2({
	tags: false,
	multiple: true,
	maximumInputLength: 0,
	maximumSelectionLength: 10,
	allowClear: true,
	language: 'zh-CN',
	disabled: false,
	templateSelection: formatState
});

$('#openAddClueFlow').on('click',function(){
    $('#clueFlowDialog .modal-title').text('增加跟进记录');
    var trObj = $('.clue_flow_update:last');
    $('#dialogAddClueFlowBtn').data('url_type',1);
    if(trObj.length==1){
        trObj = trObj.parents('tr').eq(0);
        changeWinInputByTrObj(trObj);
    }
    $('#win_visit_date').val('{$thisDate}').removeClass('readonly').removeAttr('readonly').trigger('change');
    $('#win_visit_text').val('');
    $('#win_store_num').val($('#win_store_num').data('val'));
});

$('.clue_flow_update').on('click',function(){
    var trObj = $(this).parents('tr').eq(0);
    $('#dialogAddClueFlowBtn').data('url_type',2);
    $('#clueFlowDialog .modal-title').text('修改跟进记录');
    $('#clueFlowDialog').modal('show');
    changeWinInputByTrObj(trObj);
});

function changeWinInputByTrObj(trObj){
    var updateBtn = trObj.find('.clue_flow_update').eq(0);
    var visit = ''+updateBtn.data('visit');
    visit = visit.split(',');
    $('#win_visit_date').val(trObj.find('.flow_visit_date').data('value')).trigger('change').addClass('readonly').attr('readonly','readonly');
    $('#win_sign_odds').val(trObj.find('.flow_sign_odds').data('value')).trigger('change');
    $('#win_visit_text').val(trObj.find('.flow_visit_text').data('value')).trigger('change');
    $('#win_rpt_bool').val(trObj.find('.flow_rpt_bool').data('value')).trigger('change');
    $('#win_last_visit_date').val(updateBtn.data('last')).trigger('change');
    $('#win_visit_obj').val(visit).trigger('change');
    $('#win_clue_flow_id').val(updateBtn.data('id')).trigger('change');
    $('#win_predict_date').val(trObj.find('.flow_predict_date').data('value')).trigger('change');
    $('#win_predict_amt').val(trObj.find('.flow_predict_amt').data('value')).trigger('change');
    $('#win_store_num').val(trObj.find('.flow_store_num').data('value')).trigger('change');
    $('#win_lbs_main').val(updateBtn.data('lbs_main')).trigger('change');
}

$('.clue_flow_delete').on('click',function(){
    $('#win_clue_flow_id').val($(this).data('id')).trigger('change');
    $('#confirmDialog1').modal('show');
});
    
$('#win_visit_date,#win_last_visit_date,#win_predict_date').datepicker({autoclose: true,language: 'zh_cn', format: 'yyyy/mm/dd'});
";
Yii::app()->clientScript->registerScript('addClueFlowBtn',$js,CClientScript::POS_READY);
?>

<?php
$this->endWidget();
?>
