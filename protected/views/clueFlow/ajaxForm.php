
<?php
$storeNum = $model->store_num;
$showStoreBool = isset($showStoreBool)?$showStoreBool:true;
?>
<?php echo TbHtml::hiddenField("ClueFlowForm[id]",$model->id,array('id'=>"win_clue_flow_id"));?>
<?php echo TbHtml::hiddenField("ClueFlowForm[scenario]",$model->scenario);?>
<?php echo TbHtml::hiddenField("ClueFlowForm[clue_service_id]",$model->clue_service_id);?>

<?php if ($showStoreBool): ?>
    <div class="form-group">
        <?php
        echo Tbhtml::label(Yii::t('clue','store num'),'win_store_num',array('class'=>"col-lg-2 control-label"));
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
                    'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                    'data-load'=>Yii::app()->createUrl('clueSSE/ajaxShow'),
                    'data-submit'=>Yii::app()->createUrl('clueSSE/ajaxSave'),
                    'data-serialize'=>"ClueSSEForm[scenario]=new&ClueSSEForm[clue_service_id]=".$model->clue_service_id,
                    'data-obj'=>"#clue_service_store",
                    'class'=>'openDialogForm',
                ));?>
            </div>
        </div>
    </div>
<?php endif ?>
<div class="form-group">
    <?php
    echo Tbhtml::label(Yii::t('clue','flow date'),'win_visit_date',array('class'=>"col-lg-2 control-label",'required'=>true));
    ?>
    <div class="col-lg-3">
        <?php
        echo Tbhtml::textField("ClueFlowForm[visit_date]",$model->visit_date,
            array('id'=>"win_visit_date",'autocomplete'=>'off','prepend'=>'<span class="fa fa-calendar"></span>')
        );
        ?>
    </div>
    <?php
    echo Tbhtml::label(Yii::t('clue','last flow date'),'win_last_visit_date',array('class'=>"col-lg-2 control-label"));
    ?>
    <div class="col-lg-3">
        <?php
        echo Tbhtml::textField("ClueFlowForm[last_visit_date]",$model->last_visit_date,
            array('id'=>"win_last_visit_date",'autocomplete'=>'off','prepend'=>'<span class="fa fa-calendar"></span>')
        );
        ?>
    </div>
</div>
<div class="form-group">
    <?php
    echo Tbhtml::label(Yii::t('clue','visit obj'),'win_visit_obj',array('class'=>"col-lg-2 control-label",'required'=>true));
    ?>
    <div class="col-lg-8">
        <?php
        $visitObjList = isset($model->clueServiceRow["busine_id"])&&$model->clueServiceRow["busine_id"]==array("G")?CGetName::getVisitObjList():CGetName::getVisitObjListNotDEAL();
        echo Tbhtml::dropDownList("ClueFlowForm[visit_obj][]",$model->visit_obj,$visitObjList,
            array('id'=>"win_visit_obj",'multiple'=>'multiple')
        );
        ?>
    </div>
</div>
<div class="form-group">
    <?php
    echo Tbhtml::label(Yii::t('clue','sign odds'),'win_sign_odds',array('class'=>"col-lg-2 control-label",'required'=>true));
    ?>
    <div class="col-lg-3">
        <?php
        $signOddsList = CGetName::getSignOddsList();
        if($model->clueHeadRow["clue_type"]==2){
            if(empty($model->clueServiceRow)||$model->clueServiceRow["service_status"]<5){
                unset($signOddsList[100]);
            }
        }
        echo Tbhtml::dropDownList("ClueFlowForm[sign_odds]",$model->sign_odds,$signOddsList,
            array('id'=>"win_sign_odds",'data-box_type'=>$model->clueHeadRow["box_type"])
        );
        ?>
    </div>
    <div class="col-lg-7">
        <div class="row">
            <div class="col-lg-11">
                <div class="row">
                    <?php if ($model->clueHeadRow["box_type"]==1): ?>
                        <div class="col-lg-6">
                            <div class="row">
                                <?php
                                echo Tbhtml::label(Yii::t('clue','survey bool'),'win_survey_bool',array('class'=>"col-lg-5 col-lg-left col-lg-right control-label",'required'=>true));
                                ?>
                                <div class="col-lg-7">
                                    <?php
                                    echo Tbhtml::inlineRadioButtonList("ClueFlowForm[survey_bool]",$model->survey_bool,CGetName::getRptBoolList(),
                                        array('id'=>"win_survey_bool")
                                    );
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>
                    <div class="col-lg-6">
                        <div class="row">
                            <?php
                            echo Tbhtml::label(Yii::t('clue','rpt bool'),'win_rpt_bool',array('class'=>"col-lg-5 col-lg-left col-lg-right control-label",'required'=>true));
                            ?>
                            <div class="col-lg-7">
                                <?php
                                echo Tbhtml::inlineRadioButtonList("ClueFlowForm[rpt_bool]",$model->rpt_bool,CGetName::getRptBoolList(),
                                    array('id'=>"win_rpt_bool")
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-group">
    <?php
    echo Tbhtml::label(Yii::t('clue','flow text'),'win_visit_text',array('class'=>"col-lg-2 control-label",'required'=>true));
    ?>
    <div class="col-lg-6">
        <?php
        echo Tbhtml::textArea("ClueFlowForm[visit_text]",$model->visit_text,
            array('id'=>"win_visit_text",'rows'=>4,'placeholder'=>$model->clueHeadRow["box_type"]==1&&empty($model->sign_odds)?"请详细描述无意向情况":"")
        );
        ?>
    </div>
</div>
<?php if ($model->clueHeadRow["box_type"]==1): ?>
    <div class="form-group <?php echo empty($model->sign_odds)?'':'hide';?>" id="win_box_intention">
        <?php
        echo Tbhtml::label(Yii::t('clue','no intention'),'no_intention_id',array('class'=>"col-lg-2 control-label",'required'=>true));
        ?>
        <div class="col-lg-8">
            <?php
            echo Tbhtml::dropDownList("ClueFlowForm[no_intention_id]",$model->no_intention_id,CGetName::getStopSetIDListByType(3),
                array('id'=>"win_no_intention_id",'empty'=>Yii::t("clue","none intention"))
            );
            ?>
        </div>
    </div>
<?php endif ?>
<div id="win_box_lbs_predict" class="<?php echo $model->clueHeadRow["box_type"]==1&&empty($model->sign_odds)?'hide':'';?>">
    <div class="form-group">
        <?php
        echo Tbhtml::label(Yii::t('clue','lbs main'),'win_lbs_main',array('class'=>"col-lg-2 control-label"));
        ?>
        <div class="col-lg-8">
            <?php
            echo Tbhtml::dropDownList("ClueFlowForm[lbs_main]",$model->lbs_main,CGetName::getLbsMainList($model->city),
                array('id'=>"win_lbs_main",'empty'=>'')
            );
            ?>
        </div>
    </div>
    <div class="form-group">
        <?php
        echo Tbhtml::label(Yii::t('clue','predict date'),'win_predict_date',array('class'=>"col-lg-2 control-label"));
        ?>
        <div class="col-lg-3">
            <?php
            echo Tbhtml::textField("ClueFlowForm[predict_date]",$model->predict_date,
                array('id'=>"win_predict_date",'autocomplete'=>'off','prepend'=>'<span class="fa fa-calendar"></span>')
            );
            ?>
        </div>
        <?php
        echo Tbhtml::label(Yii::t('clue','predict amt(year)'),'win_predict_amt',array('class'=>"col-lg-3 control-label",'required'=>isset($model->clueServiceRow["busine_id"])&&$model->clueServiceRow["busine_id"]==array("G")));
        ?>
        <div class="col-lg-3">
            <?php
            echo Tbhtml::numberField("ClueFlowForm[predict_amt]",$model->predict_amt,
                array('id'=>"win_predict_amt",'min'=>0)
            );
            ?>
        </div>
    </div>
</div>

<script>
<?php
$minDate = date("Y/m/d",strtotime("-1 day"));
$thisDate = date("Y/m/d");
$js="
$('#win_visit_obj').select2({
	tags: false,
	multiple: true,
	maximumInputLength: 0,
	maximumSelectionLength: 10,
	allowClear: true,
	language: 'zh-CN',
	disabled: false,
	templateSelection: function(state) {
        var rtn = $('<span style=\"color:black\">'+state.text+'</span>');
        return rtn;
    } 
});

$('#win_sign_odds').change(function(){
    if($(this).data('box_type')==1){
        if($(this).val()==0){
            $('#win_box_intention').removeClass('hide');
            $('#win_box_lbs_predict').addClass('hide');
            $('#win_visit_text').attr('placeholder','请详细描述无意向情况');
        }else{
            $('#win_box_lbs_predict').removeClass('hide');
            $('#win_box_intention').addClass('hide');
            $('#win_visit_text').attr('placeholder','');
        }
    }
});
$('#win_visit_obj').on('change',function(){
    if(JSON.stringify($(this).val())=='[\"10\"]'){
        $('#win_sign_odds').val(100);
        $('label[for=\"win_predict_amt\"]').append('<span class=\"required\">*</span>');
    }else{
        $('label[for=\"win_predict_amt\"]').children('span').remove();
    }
});

$('#win_visit_date,#win_last_visit_date,#win_predict_date').datepicker({startDate: '{$minDate}',autoclose: true,language: 'zh_cn', format: 'yyyy/mm/dd'});
";
echo $js;
?>
</script>
