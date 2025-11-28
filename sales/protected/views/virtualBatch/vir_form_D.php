<div class="information-header">
    <h4>
        <strong>涉及门店</strong>
    </h4>
</div>
<div class="form-group">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th><?php echo Yii::t("clue","store code")?></th><!--门店编号-->
                    <th><?php echo Yii::t("clue","store name")?></th><!--门店名称-->
                    <th><?php echo Yii::t("clue","sales")?></th><!--门店编号-->
                    <th><?php echo Yii::t("clue","virtual code")?></th><!--虚拟合约编号-->
                    <th><?php echo Yii::t("clue","service obj")?></th><!--服务项目-->
                    <th><?php echo Yii::t("clue","yewudalei")?></th><!--业务大类-->
                    <th><?php echo Yii::t("clue","sign type")?></th><!--签约类型-->
                    <th><?php echo Yii::t("clue","invoice amt")?></th><!--发票金额-->
                    <th><?php echo Yii::t("clue","status")?></th><!--状态-->
                    <?php if ($model->pro_type=="T"): ?>
                        <th><?php echo Yii::t("clue","surplus num")?></th><!--剩余次数 -->
                        <th><?php echo Yii::t("clue","surplus amt")?></th><!--剩余金额-->
                    <?php endif ?>
                </tr>
                </thead>
                <tbody>
                <?php
                $html = "";
                $rows = $model->virHeadRows;
                if(!empty($rows)){
                    foreach ($rows as $row){
                        $storeList=CGetName::getClueStoreRowByStoreID($row["clue_store_id"]);
                        $busine_id_text = CGetName::getBusineStrByText($row["busine_id_text"]);
                        $html.="<tr>";
                        $html.="<td>".$storeList["store_code"]."</td>";
                        $html.="<td>".$storeList["store_name"]."</td>";
                        $html.="<td>".CGetName::getEmployeeNameByKey($row["sales_id"])."</td>";
                        $html.="<td>";
                        $url=Yii::app()->createUrl('virtualHead/detail',array("index"=>$row['id']));
                        $html.=TbHtml::link($row["vir_code"],$url,array(
                            "target"=>"_blank"
                        ));
                        $html.="</td>";
                        $html.="<td>".$busine_id_text."</td>";
                        $html.="<td>".CGetName::getYewudaleiStrByKey($row["yewudalei"])."</td>";
                        $html.="<td>".CGetName::getSignTypeStrByKey($row["sign_type"])."</td>";
                        $html.="<td>".$row["year_amt"]."</td>";
                        $html.="<td>".CGetName::getContVirStatusStrByKey($row["vir_status"])."</td>";
                        if($model->pro_type=="T"){
                            $surplus_number = isset($model->surplus_json[$row["id"]]["surplus_number"])?$model->surplus_json[$row["id"]]["surplus_number"]:0;
                            $surplus_money = isset($model->surplus_json[$row["id"]]["surplus_money"])?$model->surplus_json[$row["id"]]["surplus_money"]:0;
                            $html.="<td>".$surplus_number."</td>";
                            $html.="<td>".$surplus_money."</td>";
                        }
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