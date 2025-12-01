<?php
/**
 * 操作
 */
?>
<div  style="padding-top: 15px;">
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue',"Operator User"); ?></th>
                <th><?php echo Yii::t('clue',"Operator Time"); ?></th>
                <th><?php echo Yii::t('clue',"Operator Text"); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $list = CGetName::getClueHistoryRows($model->id,1);
            if($list){
                $html ="";
                $updateBool = Yii::app()->user->validRWFunction('CM10');
                foreach ($list as $row){
                    $username = empty($row["disp_name"])?$row["lcu"]:$row["disp_name"];
                    $html.="<tr>";
                    $html.="<td>".$username."</td>";
                    $html.="<td>".$row["lcd"]."</td>";
                    $html.="<td>".$row["history_html"]."</td>";
                    $html.="</tr>";
                }
                echo $html;
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

