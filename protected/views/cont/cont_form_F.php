<?php
//门店及服务项目
$modelClass = get_class($model);
?>
<div class="box box-info">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong>
                    <span><?php echo Yii::t("clue","Store And Service Information");?></span>
                    <span>(</span>
                    <span><?php echo Yii::t("clue","total amt");?>:</span>
                    <span id="totalAmt"><?php echo $model->total_amt;?></span>
                    <span>)</span>
                </strong>
            </h4>
        </div>

        <?php if ($model->isReadonly()===false): ?>
            <div class="form-group">
                <div class="col-lg-12">
                    <div class="btn-group">
                        <?php echo TbHtml::button(Yii::t("clue","batch service fre"),array(
                            "class"=>'batch_fre serviceFreText serviceFreBatch','data-fun'=>'settingBatchFre','color'=>TbHtml::BUTTON_COLOR_PRIMARY
                        ));?>
                    </div>
                </div>
            </div>
        <?php endif ?>
        <div class="form-group">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th><?php echo Yii::t("clue","store name")?></th><!--门店名称-->
                            <th><?php echo Yii::t("clue","district")?></th><!--区域-->
                            <th><?php echo Yii::t("clue","address")?></th><!--详细地址-->
                            <th><?php echo Yii::t("clue","customer person")?></th><!--联络人-->
                            <th><?php echo Yii::t("clue","person tel")?></th><!--联系人电话-->
                            <th><?php echo Yii::t("clue","invoice header")?></th><!--开票抬头-->
                            <th><?php echo Yii::t("clue","tax id")?></th><!--税号-->
                            <th><?php echo Yii::t("clue","invoice address")?></th><!--开票地址-->
                            <th><?php echo Yii::t("clue","service obj")?></th><!--服务项目-->
                            <th><?php echo Yii::t("clue","charge sales")?></th><!--负责销售-->
                            <th><?php echo Yii::t("clue","clue area")?></th><!--门店面积-->
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $html = "";
                        $rows = $model->clueSSERow;
                        if(!empty($rows)){
                            $busine_id_text = CGetName::getBusineStrByText($model->busine_id_text);
                            $html.='<tr class="hide"><td colspan="9"></td></tr>';
                            foreach ($rows as $row){
                                $areaText = empty($row['area'])?0:$row['area'];
                                $html.="<tr data-id='{$row['clue_store_id']}' data-area='{$areaText}' class='win_sse_store'>";
                                $html.="<td>".$row["store_name"]."</td>";
                                $html.="<td>".CGetName::getDistrictStrByKey($row["district"])."</td>";
                                $html.="<td>".$row["address"]."</td>";
                                $html.="<td>".$row["cust_person"]."</td>";
                                $html.="<td>".$row["cust_tel"]."</td>";
                                $html.="<td>".$row["invoice_header"]."</td>";
                                $html.="<td>".$row["tax_id"]."</td>";
                                $html.="<td>".$row["invoice_address"]."</td>";
                                $html.="<td>".$busine_id_text."</td>";
                                $html.="<td>".CGetName::getEmployeeNameByKey($row["create_staff"])."</td>";
                                $html.="<td class='area'>".CGetName::getAreaStrByArea($row["area"])."</td>";
                                $html.="</tr>";
                                $html.="<tr class='win_sse_form active' data-id='{$row['clue_store_id']}'>";
                                $html.="<td colspan='11'>";
                                $html.=$this->renderPartial("//cont/sseForm",array('row'=>$row,'model'=>$model,'form'=>$form),true);
                                $html.="</td>";
                                $html.="</tr>";
                            }
                        }
                        echo $html;
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if ($model->isReadonly()===false){
    $this->renderPartial("//cont/settingFreeJS");
}
?>