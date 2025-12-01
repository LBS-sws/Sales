
<div class="table-responsive" style="width: 100%;">
    <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
            <th>
                <?php
                echo TbHtml::checkBox("checkAll",false,array("class"=>"win_check_all"));
                echo TbHtml::hiddenField("ClueSSEForm[scenario]",$model->scenario);
                echo TbHtml::hiddenField("ClueSSEForm[clue_service_id]",$model->clue_service_id);
                ?>
            </th>
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
        $list = CGetName::getClueStoreNotSSERows($model->clue_id,$model->clue_service_id);
        $html ="";
        if($list){
            foreach ($list as $row){
                $html.="<tr>";
                $html.="<td>";
                $html.=TbHtml::checkBox("ClueSSEForm[check][]",false,array("class"=>"win_check_one","value"=>$row["id"]));
                $html.="</td>";
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
            $html.="<tr><td colspan='8'>";
            $html.=TbHtml::button(Yii::t('clue','add store'), array(
                'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                'data-load'=>Yii::app()->createUrl('clueStore/ajaxShow'),
                'data-submit'=>Yii::app()->createUrl('clueStore/ajaxSave'),
                'data-serialize'=>"ClueStoreForm[scenario]=new&ClueStoreForm[city]={$model->city}&ClueStoreForm[clue_id]=".$model->clue_id,
                'data-obj'=>"#clue_dv_store",
                'class'=>'openDialogForm',
            ));
            $html.="</td></tr>";
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
$('.clue_store_delete').on('click',function(){
    $('#win_clue_sse_id').val($(this).data('id')).trigger('change');
    $('#confirmDialog2').modal('show');
});
EOF;
    echo $js;
    ?>
</script>