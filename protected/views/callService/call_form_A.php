<?php
$modelClass = get_class($model);
?>
<div class="box box-info">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong>
                    <?php
                    echo Yii::t("clue","service info");
                    ?>
                </strong>
            </h4>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label(Yii::t("clue","contract top code"),false,array('class'=>"col-lg-1 control-label")); ?>
            <div class="col-lg-3">
                <p class="form-control-static">
                    <?php
                    echo TbHtml::link($model->contHeadRow["cont_code"],Yii::app()->createUrl('contHead/detail',array("index"=>$model->cont_id)),array(
                        "target"=>"_blank"
                    ));
                    ?>
                </p>
            </div>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label(Yii::t("clue","service obj"),"busine_id",array('class'=>"col-lg-1 control-label","required"=>true)); ?>
            <div class="col-lg-3">
                <?php echo $form->dropDownList($model, 'busine_id',CGetName::getServiceDefListByIDList($model->contHeadRow["busine_id"]),
                    array('readonly'=>$model->isReadonly(),'id'=>'busine_id','empty'=>'')
                ); ?>
            </div>
            <div class="col-lg-12">
                <div class="call-alert"><span class="fa fa-exclamation-circle"></span>已选中<span id="storeSelect">0</span>个门店，请在下方选择<span class="text-danger">每个门店每月服务次数</span>，确认后会同步LBS日报月份新签记录</div>
            </div>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label(Yii::t("clue","free month"),false,array('class'=>"col-lg-1 control-label","required"=>true)); ?>
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="free-year">
                            <?php
                            $year = date("Y",strtotime($model->apply_date));
                            echo $year;
                            ?>
                        </div>
                        <div class="free-month">
                            <?php
                            $html = "";
                            for($i=1;$i<=12;$i++){
                                $html.=$model->getMonthTempHtml($year,$i);
                            }
                            echo $html;
                            ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="free-year">
                            <?php
                            $year++;
                            echo $year;
                            ?>
                        </div>
                        <div class="free-month">
                            <?php
                            $html = "";
                            for($i=1;$i<=12;$i++){
                                $html.=$model->getMonthTempHtml($year,$i);
                            }
                            echo $html;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <?php echo TbHtml::label(Yii::t("clue","service total amt"),false,array('class'=>"col-lg-1 control-label","required"=>true)); ?>
            <div class="col-lg-3">
                <?php echo $form->numberField($model, 'call_amt',
                    array('readonly'=>true,'id'=>'call_amt')
                ); ?>
            </div>
            <div class="col-lg-8">
                <p class="form-control-static">
                    <small class="text-muted">公式：所有门店总价的汇总</small>
                </p>
            </div>
            <div class="col-lg-12">
                <div class="call-alert"><span class="fa fa-exclamation-circle"></span>注意：门店<span id="oldStoreCode"></span>已呼叫过，继续呼叫会在原有基础上增加次数</div>
            </div>
        </div>
    </div>
</div>
