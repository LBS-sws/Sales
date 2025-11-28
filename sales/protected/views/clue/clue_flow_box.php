<p>&nbsp;</p>
<legend><?php echo Yii::t("clue","clue service flow");?></legend>
<div class="form-group">
    <div class="col-lg-12">
        <?php if (Yii::app()->user->validRWFunction('CM02')||Yii::app()->user->validRWFunction('CM10')): ?>
            <div class="btn-group">
                <?php
                echo TbHtml::button(Yii::t("clue","add clue flow"),array(
                    'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                    'data-load'=>Yii::app()->createUrl('clueFlow/ajaxShow'),
                    'data-submit'=>Yii::app()->createUrl('clueFlow/ajaxSave'),
                    'data-serialize'=>"ClueFlowForm[scenario]=new&ClueFlowForm[clue_service_id]=".$model->clue_service_id,
                    'data-obj'=>"#clue_service_flow",
                    'data-fun'=>"changeServiceRow",
                    'class'=>'openDialogForm',
                ));
                ?>
            </div>
        <?php endif ?>
    </div>
</div>
<div class="form-group">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    <th><?php echo Yii::t("clue","flow date")?></th><!--跟进时间-->
                    <th><?php echo Yii::t("clue","sign odds")?></th><!--签单概率-->
                    <th><?php echo Yii::t("clue","store num")?></th><!--预计成交金额-->
                    <th><?php echo Yii::t("clue","predict amt(year)")?></th><!--预计成交金额-->
                    <th><?php echo Yii::t("clue","predict date")?></th><!--预计成交日期-->
                    <th><?php echo Yii::t("clue","visit obj")?></th><!--拜访目的-->
                    <th><?php echo Yii::t("clue","flow text")?></th><!--跟进内容-->
                    <th><?php echo Yii::t("clue","rpt bool")?></th><!--是否报价-->
                    <th><?php echo Yii::t("clue","create staff")?></th><!--创建人-->
                    <th><?php echo Yii::t("clue","create date")?></th><!--创建时间-->
                    <th width="130px"><?php echo Yii::t("clue","operate")?></th><!--操作-->
                </tr>
                </thead>
                <tbody>
                <?php
                $html = "";
                if(!empty($rows)){
                    $uid = Yii::app()->user->id;
                    foreach ($rows as $row){
                        $row['visit_date'] = empty($row['visit_date'])?null:General::toDate($row['visit_date']);
                        $row['last_visit_date'] = empty($row['last_visit_date'])?null:General::toDate($row['last_visit_date']);
                        $html.="<tr data-id='{$row['id']}'>";
                        $html.="<td class='flow_visit_date' data-value='{$row['visit_date']}'>".$row["visit_date"]."</td>";
                        $html.="<td class='flow_sign_odds' data-value='{$row['sign_odds']}'>".CGetName::getSignOddsStrByKey($row["sign_odds"])."</td>";
                        $html.="<td class='flow_store_num' data-value='{$row['store_num']}'>".$row["store_num"]."</td>";
                        $html.="<td class='flow_predict_amt' data-value='{$row['predict_amt']}'>".$row["predict_amt"]."</td>";
                        $html.="<td class='flow_predict_date' data-value='{$row['predict_date']}'>".$row["predict_date"]."</td>";
                        $html.="<td class='flow_visit_obj_text' data-value='{$row['visit_obj_text']}'>".$row["visit_obj_text"]."</td>";
                        $html.="<td class='flow_visit_text' data-value='{$row['visit_text']}'>".$row["visit_text"]."</td>";
                        $html.="<td class='flow_rpt_bool' data-value='{$row['rpt_bool']}'>".CGetName::getRptBoolStrByKey($row["rpt_bool"])."</td>";
                        $html.="<td data-value='{$row['create_staff']}'>".CGetName::getEmployeeNameByKey($row["create_staff"])."</td>";
                        $html.="<td data-value='{$row['lcd']}'>".$row["lcd"]."</td>";
                        $html.="<td>";
                        if($row["update_bool"]==1&&$row["lcu"]==$uid){
                            $html.=TbHtml::button("修改",array(
                                'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                                'data-load'=>Yii::app()->createUrl('clueFlow/ajaxShow'),
                                'data-submit'=>Yii::app()->createUrl('clueFlow/ajaxSave'),
                                'data-serialize'=>"ClueFlowForm[scenario]=edit&ClueFlowForm[id]=".$row["id"],
                                'data-obj'=>"#clue_service_flow",
                                'data-fun'=>"changeServiceRow",
                                'class'=>'openDialogForm ',
                            ));
                            $html.=TbHtml::button("删除",array(
                                'data-load'=>Yii::app()->createUrl('clueFlow/ajaxDelete'),
                                'data-submit'=>Yii::app()->createUrl('clueFlow/ajaxSave'),
                                'data-serialize'=>"ClueFlowForm[scenario]=delete&ClueFlowForm[clue_service_id]={$row['clue_service_id']}&ClueFlowForm[id]=".$row["id"],
                                'data-obj'=>"#clue_service_flow",
                                'data-fun'=>"changeServiceRow",
                                'class'=>'openDialogForm ',
                            ));
                        }
                        $html.="</td>";
                        $html.="</tr>";
                    }
                    $html.="<tr class='hide' id='flowNoneTr'><td colspan='7'>没有跟进记录</td>";
                }else{
                    $html.="<tr id='flowNoneTr'><td colspan='7'>没有跟进记录</td>";
                }
                echo $html;
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>