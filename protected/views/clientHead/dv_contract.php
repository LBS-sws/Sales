<?php
/**
 * 合约
 */
?>
<div  style="padding-top: 15px;">
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue',"virtual code"); ?></th><!--虚拟合约编号-->
                <th><?php echo Yii::t('clue',"store name"); ?></th><!--门店名称-->
                <th><?php echo Yii::t('clue',"city"); ?></th><!--城市-->
                <th><?php echo Yii::t('clue',"service obj"); ?></th><!--服务项目-->
                <th><?php echo Yii::t('clue',"status"); ?></th><!--状态-->
                <th><?php echo Yii::t('clue',"sign type"); ?></th><!--签约类型-->
                <th><?php echo Yii::t('clue',"sales"); ?></th><!--销售-->
                <th><?php echo Yii::t('clue',"total amt"); ?></th><!--总金额-->
                <th><?php echo Yii::t('clue',"sign date"); ?></th><!--签约时间-->
                <th><?php echo Yii::t('clue',"contract start date"); ?></th><!--合约开始时间-->
                <th><?php echo Yii::t('clue',"contract end date"); ?></th><!--合约结束时间-->
                <th><?php echo Yii::t('clue',"first date"); ?></th><!--首次日期-->
            </tr>
            </thead>
            <tbody id="dv_person_body">
            <?php
            $list = CGetName::getContractVirRowsByClueID($model->id);
            if($list){
                $html ="";
                foreach ($list as $row){
                    $storeList = CGetName::getClueStoreRowByStoreID($row["clue_store_id"]);
                    $url=Yii::app()->createUrl('virtualHead/detail',array("index"=>$row['id']));
                    $html.="<tr>";
                    $html.="<td>";
                    $html.=TbHtml::link($row["vir_code"],$url);
                    $html.="</td>";
                    $html.="<td>".$storeList["store_name"]."</td>";
                    $html.="<td>".General::getCityName($storeList["city"])."</td>";
                    $html.="<td>".CGetName::getBusineStrByText($row["busine_id_text"])."</td>";
                    $html.="<td>".CGetName::getContVirStatusStrByKey($row["vir_status"])."</td>";
                    $html.="<td>".CGetName::getSignTypeStrByKey($row["sign_type"])."</td>";
                    $html.="<td>".CGetName::getEmployeeNameByKey($row["sales_id"])."</td>";
                    $html.="<td>".$row["year_amt"]."</td>";
                    $html.="<td>".$row["sign_date"]."</td>";
                    $html.="<td>".$row["cont_start_dt"]."</td>";
                    $html.="<td>".$row["cont_end_dt"]."</td>";
                    $html.="<td>".$row["first_date"]."</td>";
                    $html.="</tr>";
                }
                echo $html;
            }
            ?>
            </tbody>
        </table>
    </div>
</div>