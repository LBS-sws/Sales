<p>&nbsp;</p>
<legend><?php echo Yii::t("clue","clue service store");?><small>（总金额：<?php echo isset($model->clueServiceRow["total_amt"])?$model->clueServiceRow["total_amt"]:"";?>）</small></legend>

<?php
$updateBool = $model->clueServiceRow['service_status']<7&&(Yii::app()->user->validRWFunction('CM02')||Yii::app()->user->validRWFunction('CM10'));
?>
<?php if ($updateBool): ?>
    <div class="form-group">
        <div class="col-lg-12">
            <div class="btn-group">
                <?php echo TbHtml::button(Yii::t("clue","add clue store"),array(
                    'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                    'data-load'=>Yii::app()->createUrl('clueSSE/ajaxShow'),
                    'data-submit'=>Yii::app()->createUrl('clueSSE/ajaxSave'),
                    'data-serialize'=>"ClueSSEForm[scenario]=new&ClueSSEForm[clue_service_id]=".$model->clue_service_id,
                    'data-obj'=>"#clue_service_store",
                    'class'=>'openDialogForm',
                    'data-fun'=>'select2SSE',
                ));?>
            </div>
            <?php if ($updateBool&&!empty($rows)): ?>
                <div class="btn-group pull-right">
                    <?php
                    echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('clue','save'), array(
                        'data-submit'=>Yii::app()->createUrl('clueSSE/ajaxAllSave'),
                        'data-obj'=>"#clue_service_store",
                        'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                        'class'=>'sse-all-form-save',
                    ));
                    ?>
                </div>
            <?php endif ?>
        </div>
    </div>
<?php endif ?>
<div class="form-group">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th width="1%">&nbsp;</th><!--操作-->
                    <th><?php echo Yii::t("clue","store name")?></th><!--门店名称-->
                    <th><?php echo Yii::t("clue","district")?></th><!--区域-->
                    <th><?php echo Yii::t("clue","address")?></th><!--详细地址-->
                    <th><?php echo Yii::t("clue","customer person")?></th><!--联络人-->
                    <th><?php echo Yii::t("clue","person tel")?></th><!--联系人电话-->
                    <th><?php echo Yii::t("clue","invoice header")?></th><!--开票抬头-->
                    <th><?php echo Yii::t("clue","tax id")?></th><!--税号-->
                    <th><?php echo Yii::t("clue","invoice address")?></th><!--开票地址-->
                    <th><?php echo Yii::t("clue","clue area")?></th><!--门店面积-->
                </tr>
                </thead>
                <tbody>
                <?php
                $html = "";
                if(!empty($rows)){
                    $html.='<tr class="hide"><td colspan="10"></td></tr>';
                    foreach ($rows as $row){
                        //$row["update_bool"] = $row["update_bool"]==1&&$row["rec_bool"]==1?1:0;
                        $row["update_bool"] = $updateBool?1:0;
                        $html.="<tr data-id='{$row['a_id']}' class='win_sse_store'>";
                        if($row["update_bool"]==1){
                            $html.="<td>";
                            $html.=TbHtml::button("<span class='fa fa-remove'></span>",array(
                                'data-load'=>Yii::app()->createUrl('clueSSE/ajaxDelete'),
                                'data-submit'=>Yii::app()->createUrl('clueSSE/ajaxSave'),
                                'data-serialize'=>"ClueSSEForm[scenario]=delete&ClueSSEForm[id]={$row['a_id']}&ClueSSEForm[clue_service_id]=".$model->clue_service_id,
                                'data-obj'=>"#clue_service_store",
                                'class'=>'openDialogForm',
                                'data-fun'=>"select2SSE",
                            ));
                            $html.="</td>";
                        }else{
                            $html.="<td>&nbsp;</td>";
                        }
                        $html.="<td>".$row["store_name"]."</td>";
                        $html.="<td>".CGetName::getDistrictStrByKey($row["district"])."</td>";
                        $html.="<td>".$row["address"]."</td>";
                        $html.="<td>".$row["cust_person"]."</td>";
                        $html.="<td>".$row["cust_tel"]."</td>";
                        $html.="<td>".$row["invoice_header"]."</td>";
                        $html.="<td>".$row["tax_id"]."</td>";
                        $html.="<td>".$row["invoice_address"]."</td>";
                        $html.="<td>".CGetName::getAreaStrByArea($row["area"])."</td>";
                        $html.="</tr>";
                        $html.="<tr class='win_sse_form active'>";
                        $html.="<td colspan='10'>";
                        $html.=$this->renderPartial("//clue/sseForm",array('row'=>$row),true);
                        $html.="</td>";
                        $html.="</tr>";
                    }
                }else{
                    $html.="<tr id='storeNoneTr'><td colspan='10'>没有绑定门店</td>";
                }
                echo $html;
                ?>
                </tbody>
                <?php if ($updateBool&&!empty($rows)): ?>
                    <tfoot>
                    <tr>
                        <th colspan="10" class="text-center">
                            <?php
                            echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('clue','save'), array(
                                'data-submit'=>Yii::app()->createUrl('clueSSE/ajaxAllSave'),
                                'data-obj'=>"#clue_service_store",
                                'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                                'class'=>'sse-all-form-save',
                            ));
                            ?>
                        </th>
                    </tr>
                    </tfoot>
                <?php endif ?>
            </table>
        </div>
    </div>
</div>

<script>
    <?php
    $js = <<<EOF
$('.win_sse_store').click(function(e){
    if(!$(e.target).hasClass('fa-remove')&&!$(e.target).hasClass('openDialogForm')){
        if($(this).next('tr.win_sse_form').hasClass('active')){
            $(this).next('tr.win_sse_form').removeClass('active');
        }else{
            $(this).next('tr.win_sse_form').addClass('active');
        }
    }
});

function select2SSE(response){
    $('.changePestMethod,.changeDevice,.changeWare').select2({
	    tags: false,
        multiple: true,
        allowClear: true,
        closeOnSelect: false,
        disabled: false,
        templateSelection: function(state) {
            var rtn = $('<span style="color:black">'+state.text+'</span>');
            return rtn;
        }
    });
}
EOF;
    echo $js;
    ?>
</script>