<div class="mpr-0 col-lg-4">
    <div class="box box-clue-service <?php echo $row["box_class"];?>">
        <div class="box-body clickBoxService" data-clue_id="<?php echo $row["clue_id"];?>" data-id="<?php echo $row["id"];?>">
            <div class="row">
                <div class="col-sm-12" style="height: 35px;">
                    <?php if ($row["rpt_bool"]): ?>
                        <?php
                        $rpt_url=Yii::app()->createUrl('clueRpt/new',array("clue_service_id"=>$row["id"]));
                        echo TbHtml::link(Yii::t('clue','send clue report'),$rpt_url,array(
                            "class"=>"pull-right btn btn-primary"
                        ));
                        ?>
                    <?php endif ?>
                    <?php if ($row["busine_id"]!="G"&&$row["contract_bool"]): ?>
                        <?php
                        $con_url=Yii::app()->createUrl('contHead/new',array("clue_service_id"=>$row["id"]));
                        echo TbHtml::link("发起合同审批",$con_url,array(
                            "class"=>"pull-right btn btn-primary"
                        ));
                        ?>
                    <?php endif ?>
                    <?php if ($row["busine_id"]!="G"&&!empty($row["status_text"])): ?>
                    <div class="pull-right">
                        <p class="">
                            <?php echo $row["status_text"];?>
                        </p>
                    </div>
                    <?php endif ?>
                    <div>
                        <?php echo CGetName::getBusineStrByText($row["busine_id_text"]);?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 clue-service-visit">
                    <p><?php echo Yii::t("clue","visit type")."：".CGetName::getVisitTypeStrByKey($row["visit_type"]);?></p>
                    <p><?php echo Yii::t("clue","visit obj")."：".$row["visit_obj_text"];?></p>
                    <p><?php echo Yii::t("clue","sign odds")."：".CGetName::getSignOddsStrByKey($row["sign_odds"]);?></p>
                    <p><?php echo Yii::t("clue","predict date")."：".$row["predict_date"];?></p>
                    <p class="box_lbs_main"><?php echo Yii::t("clue","lbs main")."：".CGetName::getLbsMainNameByKey($row["lbs_main"]);?></p>
                </div>
                <div class="col-sm-6 clue-service-total">
                    <p><?php echo Yii::t("clue","predict total amount");?></p>
                    <p class="box_predict_amt"><b>&nbsp;<?php echo $row["predict_amt"];?></b></p>
                    <p><?php echo Yii::t("clue","report total amount");?></p>
                    <p><b>&nbsp;<?php echo $row["rpt_amt"];?></b></p>
                    <p><?php echo Yii::t("clue","expect total amount");?></p>
                    <p><b>&nbsp;<?php echo $row["service_status"]>=10?$row["total_amt"]:"";?></b></p>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 clue-service-footer">
                    <div class="pull-left">
                        <span><?php echo Yii::t("clue","rec staff")."：".CGetName::getEmployeeNameByKey($row["create_staff"]);?></span>
                    </div>
                    <div class="pull-right">
                        <span><?php echo $row["lcd"];?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>