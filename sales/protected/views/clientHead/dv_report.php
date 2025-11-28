<?php
/**
 * 报价
 */
?>

<div  style="padding-top: 15px;">
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue','clue code'); ?></th>
                <th><?php echo Yii::t('clue','clue name'); ?></th>
                <th><?php echo Yii::t('clue','clue type'); ?></th>
                <th><?php echo Yii::t('clue','city manger'); ?></th>
                <th><?php echo Yii::t('clue','trade type'); ?></th>
                <th><?php echo Yii::t('clue','level name'); ?></th>
                <th><?php echo Yii::t('clue',"clue service id"); ?></th>
                <th><?php echo Yii::t('clue',"service obj"); ?></th>
                <th><?php echo Yii::t('clue','status'); ?></th>
                <th><?php echo Yii::t('clue',"lcd"); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $list = CGetName::getClientReportHistoryRows($model->id);
            if($list){
                $html ="";
                foreach ($list as $row){
                    $html.="<tr>";
                    $html.="<td>".$row["clue_code"]."</td>";
                    $html.="<td>".$row["cust_name"]."</td>";
                    $html.="<td>".CGetName::getClueTypeStr($row["clue_type"])."</td>";
                    $html.="<td>".General::getCityName($row["city"])."</td>";
                    $html.="<td>".CGetName::getCustClassStrByKey($row["cust_class"])."</td>";
                    $html.="<td>".CGetName::getCustLevelStrByKey($row["cust_level"])."</td>";
                    $html.="<td>".$row["clue_service_id"]."</td>";
                    $html.="<td>".CGetName::getBusineStrByText($row["busine_id_text"])."</td>";
                    $html.="<td>".CGetName::getRptStatusStrByKey($row["rpt_status"])."</td>";
                    $html.="<td>".$row["lcd"]."</td>";
                    $html.="<td>";
                    $html.=TbHtml::link(Yii::t('clue',"look"),Yii::app()->createUrl('clueRpt/edit',array('index'=>$row["id"],'type'=>1)));
                    $html.="</td>";
                    $html.="</tr>";
                }
                echo $html;
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

