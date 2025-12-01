
<?php
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'clueStoreFormDialog',
    'header'=>Yii::t('clue','add clue store'),
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','OK'), array('data-dismiss'=>'modal',
            'submit'=>Yii::app()->createUrl('clueSSE/add'),
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY
        )),
    ),
    'show'=>false,
    'size'=>" modal-lg",
));
?>

<div class="table-responsive" style="width: 100%;">
    <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
            <th>
                <?php
                echo TbHtml::checkBox("checkAll",false,array("class"=>"win_check_all"));
                echo TbHtml::hiddenField("ClueSSEForm[id]",0,array("id"=>"win_clue_sse_id"));
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
        $list = CGetName::getClueStoreNotSSERows($model->id,$model->clue_service_id);
        $html ="";
        if($list){
            foreach ($list as $row){
                $html.="<tr>";
                $html.="<td>";
                $html.=TbHtml::hiddenField("winClueSSE[clue_service_id]",$model->clue_service_id);
                $html.=TbHtml::checkBox("winClueSSE[check][]",false,array("class"=>"win_check_one","value"=>$row["id"]));
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
            $html.=TbHtml::button(Yii::t('clue','add store'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                'submit'=>Yii::app()->createUrl('clueStore/new',array("clue_id"=>$model->id,"type"=>1))
            ));
            $html.="</td></tr>";
        }
        echo $html;
        ?>
        </tbody>
    </table>
</div>
<?php

$js="
$('.win_check_all').on('click',function(){
	var val = $(this).prop('checked');
	$('.win_check_one').prop('checked',val);
});
$('.clue_store_delete').on('click',function(){
    $('#win_clue_sse_id').val($(this).data('id')).trigger('change');
    $('#confirmDialog2').modal('show');
});
    
";
Yii::app()->clientScript->registerScript('addClueStoreFormBtn',$js,CClientScript::POS_READY);

?>
<?php
$this->endWidget();
?>

