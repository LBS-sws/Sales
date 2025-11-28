
<?php
$modelClass="CallServiceForm";
?>
<div class="table-responsive" style="width: 100%;">
    <?php
    echo TbHtml::hiddenField("CallServiceForm[scenario]",$model->scenario);
    echo TbHtml::hiddenField("CallServiceForm[cont_id]",$model->cont_id);
    echo TbHtml::hiddenField("CallServiceForm[store_ids]",$model->store_ids);
    echo TbHtml::hiddenField("CallServiceForm[busine_id]",$model->busine_id);
    ?>
    <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
            <th>
                <?php
                echo TbHtml::checkBox("checkAll",false,array("class"=>"win_check_all"));
                ?>
            </th>
            <th><?php echo Yii::t("clue","store code"); ?></th>
            <th><?php echo Yii::t("clue","store name"); ?></th>
            <th><?php echo Yii::t("clue","store address"); ?></th>
            <th><?php echo Yii::t("clue","customer person"); ?></th>
            <th><?php echo Yii::t("clue","person tel"); ?></th>
            <th><?php echo Yii::t("clue","invoice header"); ?></th>
            <th><?php echo Yii::t("clue","tax id"); ?></th>
            <th><?php echo Yii::t("clue","invoice address"); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $html ="";
        if($list){
            foreach ($list as $row){
                $html.="<tr>";
                $html.="<td>";
                $html.=TbHtml::checkBox("check[]",false,array("class"=>"win_check_one","value"=>$row["id"]));
                $html.="</td>";
                $html.="<td>".$row["store_code"]."</td>";
                $html.="<td>".$row["store_name"]."</td>";
                $html.="<td>".$row["address"]."</td>";
                $html.="<td>".$row["cust_person"]."</td>";
                $html.="<td>".$row["cust_tel"]."</td>";
                $html.="<td>".$row["invoice_header"]."</td>";
                $html.="<td>".$row["tax_id"]."</td>";
                $html.="<td>".$row["invoice_address"]."</td>";
                $html.="</tr>";
            }
        }else{
            $html.="<tr><td colspan='9'>没有需要呼叫的门店</td></tr>";
        }
        echo $html;
        ?>
        </tbody>
    </table>
</div>
<script>
    <?php

    $js = <<<EOF
$('.win_check_all').on('click',function(){
	var val = $(this).prop('checked');
	$('.win_check_one').prop('checked',val);
});
EOF;
    echo $js;
    ?>
</script>