
<?php
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'sseStoreDialog',
    'header'=>Yii::t("clue","clue service store"),
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal')),
        TbHtml::button(Yii::t('dialog','OK'), array('id'=>'okBtnSseStore','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
    ),
    'show'=>false,
));
?>
<div class="form-group">
    <div class="col-lg-12">
        <div class="table-responsive" style="width: 100%;">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th>
                        <?php
                        echo TbHtml::checkBox("checkAll",false,array("class"=>"win_check_all"));
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
                $html =TbHtml::hiddenField("checkStore",implode(",",$model->showStore),array("id"=>"checkStore"));
                if(isset($storeRows)){
                    foreach ($storeRows as $row){
                        $checkBool = in_array($row["id"],$model->showStore)?true:false;
                        $html.="<tr>";
                        $html.="<td>";
                        $html.=TbHtml::checkBox("winClueSSE[check][]",$checkBool,array("class"=>"win_check_one","value"=>$row["id"]));
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
                    $html.="没有可关联门店";
                    $html.="</td></tr>";
                }
                echo $html;
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->endWidget(); ?>


<?php
$modelClass = get_class($model);
$js = <<<EOF
$('.win_check_all').on('click',function(){
	var val = $(this).prop('checked');
	$('.win_check_one').prop('checked',val);
});

$('#okBtnSseStore').on('click',function(){
    var checkStore=[];
    $('.win_check_one').each(function(){
        var store_id = $(this).val();
        if($(this).is(':checked')){
            checkStore.push(store_id);
            $('.win_sse_store[data-id="'+store_id+'"]').removeClass('hide');
            $('.win_sse_form[data-id="'+store_id+'"]').removeClass('hide');
        }else{
            $('.win_sse_store[data-id="'+store_id+'"]').addClass('hide');
            $('.win_sse_form[data-id="'+store_id+'"]').addClass('hide');
        }
    });
    checkStore = checkStore.join(',');
    $('#checkStore').val(checkStore);
});
   
EOF;
Yii::app()->clientScript->registerScript('changeSseStore',$js,CClientScript::POS_READY);
?>
