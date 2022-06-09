<?php $rows = StopAgainForm::getAgainList($stop_id); ?>

<?php if (!empty($rows)): ?>
    <legend><?php echo Yii::t("customer","visit to record");?></legend>

    <div class="form-group">
        <div class="col-lg-10 col-lg-offset-1">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    <th><?php echo Yii::t("customer","shift date")?></th>
                    <th><?php echo Yii::t("customer","shift type")?></th>
                    <th><?php echo Yii::t("customer","again end date")?></th>
                    <th><?php echo Yii::t("customer","customer name")?></th>
                    <th><?php echo Yii::t("customer","shift remark")?></th>
                    <th><?php echo Yii::t("customer","shift staff")?></th>
                    <th class="hide"><?php echo Yii::t("customer","Record Date")?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($rows as $row){
                    $row["again_end_date"] =empty($row["end_bool"])?$row["again_end_date"]:"";
                    echo "<tr>";
                    echo "<td>".$row["back_date"]."</td>";
                    echo "<td>".$row["type_name"]."</td>";
                    echo "<td>".$row["again_end_date"]."</td>";
                    echo "<td>".$row["customer_name"]."</td>";
                    echo "<td>".$row["back_remark"]."</td>";
                    echo "<td>".$row["disp_name"]."</td>";
                    echo "<td class='hide'>".$row["lcd"]."</td>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif ?>
