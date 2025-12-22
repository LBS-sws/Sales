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
<?php if (!empty($model->showStore)): ?>
                    <span>(</span>
                    <span><?php echo Yii::t("clue","total amt");?>:</span>
                    <span id="totalAmt"><?php echo $model->total_amt;?></span>
                    <span>)</span>
<?php endif ?>
                </strong>
            </h4>
        </div>

        <?php if ($model->isReadonly()===false): ?>
            <div class="form-group">
                <div class="col-lg-12">
                    <div class="btn-group">
                        <?php echo TbHtml::button(Yii::t("clue","clue service store"),array(
                            'name'=>'sseStore','data-toggle'=>'modal','data-target'=>'#sseStoreDialog','color'=>TbHtml::BUTTON_COLOR_PRIMARY
                        ));?>
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
                            $html.='<tr class="hide"><td colspan="9"></td></tr>';
                            foreach ($rows as $row){
                                $classHide = in_array($row["clue_store_id"],$model->showStore)?"":" hide";
                                $storeList=CGetName::getClueStoreRowByStoreID($row["clue_store_id"]);
                                $areaText = empty($storeList['area'])?0:$storeList['area'];
                                $busine_id_text = implode("、",$row["busine_id_text"]);
                                $busine_id_text = CGetName::getBusineStrByText($busine_id_text);
                                $html.="<tr data-id='{$row['clue_store_id']}' data-area='{$areaText}' class='win_sse_store {$classHide}'>";
                                $html.="<td>".$storeList["store_name"]."</td>";
                                $html.="<td>".CGetName::getDistrictStrByKey($storeList["district"])."</td>";
                                $html.="<td>".$storeList["address"]."</td>";
                                $html.="<td>".$storeList["cust_person"]."</td>";
                                $html.="<td>".$storeList["cust_tel"]."</td>";
                                $html.="<td>".$storeList["invoice_header"]."</td>";
                                $html.="<td>".$storeList["tax_id"]."</td>";
                                $html.="<td>".$storeList["invoice_address"]."</td>";
                                $html.="<td>".$busine_id_text."</td>";
                                $html.="<td>".CGetName::getEmployeeNameByKey($row["sales_id"])."</td>";
                                $html.="<td class='area'>".CGetName::getAreaStrByArea($storeList["area"])."</td>";
                                $html.="</tr>";
                                $html.="<tr class='win_sse_form {$classHide} active' data-id='{$row['clue_store_id']}'>";
                                $html.="<td colspan='11'>";
                                $sec_type = $model->isReadonly()===true?'view':"edit";
                                $clueSSEModel = new ContProSSEForm($sec_type);
                                $clueSSEModel->busine_id = $row["busine_id"];
                                $clueSSEModel->service = array();
                                foreach ($row["detail_json"] as $detailJson){
                                    $clueSSEModel->service=array_merge($clueSSEModel->service,$detailJson);
                                }
                                $html.=$this->renderPartial("//contPro/sseForm",array('clueSSEModel'=>$clueSSEModel,'form'=>$form),true);
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
if($model->isReadonly()===false){
    $this->renderPartial("//contPro/sseStoreDialog",array('model'=>$model));
    $this->renderPartial("//cont/settingFreeJS");
}
?>