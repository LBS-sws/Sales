<?php
$modelClass = get_class($model);
?>
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <?php
        $html="<thead><tr>";
        if($model->isReadonly()===false){
            $html.="<th width='1%'>&nbsp;</th>";
        }
        $html.="<th width='120px'>".Yii::t("clue","store code")."</th>";
        $html.="<th>".Yii::t("clue","store name")."</th>";
        $html.="<th width='100px'>".Yii::t("clue","district")."</th>";
        $html.="<th width='220px'>".Yii::t("clue","address")."</th>";
        $html.="<th width='100px'>".Yii::t("clue","status")."</th>";
        $html.="<th width='100px'>".Yii::t("clue","unit price")."</th>";
        $html.="<th width='100px'>".Yii::t("clue","total price")."</th>";
        $html.="<th width='200px'>".Yii::t("clue","virtual code")."</th>";
        $html.="<th width='200px'>".Yii::t("clue","call service kill")."</th>";
        if($model->isReadonly()===false){
            /*
            $num =count($model->fileJson);
            $html.="<th width='1%'>";
            $html.=TbHtml::button("+",array(
                "class"=>"table_add",
                "data-temp"=>"temp2",
                "data-num"=>$num,
                'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
            ));
            $tempHtml=$this->renderPartial('//cont/table_temp2',array("model"=>$model,"form"=>$form,"num"=>0),true);
            $html.=TbHtml::hiddenField("temp2",$tempHtml);
            $html.="</th>";
            */
        }
        $html.="</tr></thead><tbody>";
        if(!empty($model->vir_json)){
            foreach ($model->vir_json as $virRow){
                $storeRow = CGetName::getClueStoreRowByStoreID($virRow["clue_store_id"]);
                $callList = CGetName::getOldCallTextByVirID($virRow["id"],$model->id);
                $html.="<tr class='changeVir' data-store='{$virRow["clue_store_id"]}'>";
                if($model->isReadonly()===false){
                    $html.="<td>";
                    $html.=TbHtml::button("<span class='fa fa-remove'></span>",array(
                        'class'=>'removeTr',
                        'data-id'=>$virRow["clue_store_id"],
                    ));
                    $html.="</td>";
                }
                $html.="<td>".$storeRow["store_code"]."</td>";
                $html.="<td>".$storeRow["store_name"]."</td>";
                $html.="<td>".CGetName::getDistrictStrByKey($storeRow["district"])."</td>";
                $html.="<td>".$storeRow["address"]."</td>";
                $html.="<td>".CGetName::getContVirStatusStrByKey($virRow["vir_status"])."</td>";
                $html.="<td class='unitPrice'>".$virRow["call_fre_amt"]."</td>";
                $html.="<td class='totalPrice'>".$virRow["call_fre_amt"]*$model->call_sum."</td>";
                $html.="<td>";
                $html.=TbHtml::link($virRow["vir_code"],Yii::app()->createUrl('virtualHead/detail',array("index"=>$virRow["id"])),array(
                    "target"=>"_blank"
                ));
                $html.="</td>";
                $html.="<td class='callText' data-month='{$callList["monthText"]}' data-code='".(empty($callList["callText"])?0:$storeRow["store_code"])."'>".$callList["callText"]."</td>";
                $html.="</tr>";
            }
        }
        $html.="</tbody>";
        echo $html;
        ?>
    </table>
</div>