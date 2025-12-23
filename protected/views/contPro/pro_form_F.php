<?php
//门店及服务项目
$modelClass = get_class($model);
?>
<div class="box box-info">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong>
                    <span><?php echo Yii::t("clue","Store And Service Information");?></span>
<?php if (!empty($model->showStore)): ?>
                    <span>(</span>
                    <span><?php echo Yii::t("clue","total amt");?>:</span>
                    <span id="totalAmt"><?php echo $model->total_amt;?></span>
                    <span>)</span>
<?php endif ?>
                </strong>
            </h4>
        </div>

        <?php if ($model->isReadonly()===false): ?>
            <div class="form-group">
                <div class="col-lg-12">
                    <div class="btn-group">
                        <?php echo TbHtml::button(Yii::t("clue","clue service store"),array(
                            'name'=>'sseStore','data-toggle'=>'modal','data-target'=>'#sseStoreDialog','color'=>TbHtml::BUTTON_COLOR_PRIMARY,'type'=>'button'
                        ));?>
                        <?php echo TbHtml::button(Yii::t("clue","batch service fre"),array(
                            "class"=>'batch_fre serviceFreText serviceFreBatch','data-fun'=>'settingBatchFre','color'=>TbHtml::BUTTON_COLOR_PRIMARY,'type'=>'button'
                        ));?>
                    </div>
                </div>
            </div>
        <?php endif ?>
        <div class="form-group">
            <div class="col-lg-12">
                <div class="input-group" style="max-width: 480px;">
                    <?php echo TbHtml::textField("contProStoreSearch","",array(
                        "class"=>"form-control",
                        "placeholder"=>"搜索门店名称/编号",
                        "id"=>"contProStoreSearchInput"
                    )); ?>
                    <span class="input-group-btn">
                        <?php echo TbHtml::button('<i class="fa fa-search"></i> 搜索', array(
                            'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                            'id'=>'btnContProStoreSearch',
                            'type'=>'button'
                        )); ?>
                        <?php echo TbHtml::button('<i class="fa fa-refresh"></i> 重置', array(
                            'color'=>TbHtml::BUTTON_COLOR_DEFAULT,
                            'id'=>'btnContProStoreReset',
                            'type'=>'button'
                        )); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th><?php echo Yii::t("clue","store name")?></th><!--门店名称-->
                            <th><?php echo Yii::t("clue","district")?></th><!--区域-->
                            <th><?php echo Yii::t("clue","address")?></th><!--详细地址-->
                            <th><?php echo Yii::t("clue","customer person")?></th><!--联络人-->
                            <th><?php echo Yii::t("clue","person tel")?></th><!--联系人电话-->
                            <th><?php echo Yii::t("clue","invoice header")?></th><!--开票抬头-->
                            <th><?php echo Yii::t("clue","tax id")?></th><!--税号-->
                            <th><?php echo Yii::t("clue","invoice address")?></th><!--开票地址-->
                            <th><?php echo Yii::t("clue","service obj")?></th><!--服务项目-->
                            <th><?php echo Yii::t("clue","charge sales")?></th><!--负责销售-->
                            <th><?php echo Yii::t("clue","clue area")?></th><!--门店面积-->
                        </tr>
                        </thead>
                        <tbody id="contProStoreTableBody">
                        <?php
                        $html = "";
                        $rows = $model->clueSSERow;
                        $showStore = $model->showStore;
                        if(!empty($showStore)){
                            $html.='<tr class="hide"><td colspan="11"></td></tr>';
                            $sec_type = $model->isReadonly()===true?'view':"edit";
                            foreach ($showStore as $storeId){
                                $storeId = "".$storeId;
                                if(!isset($rows[$storeId])){
                                    continue;
                                }
                                $row = $rows[$storeId];
                                $storeList=CGetName::getClueStoreRowByStoreID($row["clue_store_id"]);
                                $areaText = empty($storeList['area'])?0:$storeList['area'];
                                $busine_id_text = implode("、",$row["busine_id_text"]);
                                $busine_id_text = CGetName::getBusineStrByText($busine_id_text);
                                $html.="<tr data-id='{$row['clue_store_id']}' data-area='{$areaText}' class='win_sse_store'>";
                                $html.="<td>".$storeList["store_name"]."</td>";
                                $html.="<td>".CGetName::getDistrictStrByKey($storeList["district"])."</td>";
                                $html.="<td>".$storeList["address"]."</td>";
                                $html.="<td>".$storeList["cust_person"]."</td>";
                                $html.="<td>".$storeList["cust_tel"]."</td>";
                                $html.="<td>".$storeList["invoice_header"]."</td>";
                                $html.="<td>".$storeList["tax_id"]."</td>";
                                $html.="<td>".$storeList["invoice_address"]."</td>";
                                $html.="<td>".$busine_id_text."</td>";
                                $html.="<td>".CGetName::getEmployeeNameByKey($row["sales_id"])."</td>";
                                $html.="<td class='area'>".CGetName::getAreaStrByArea($storeList["area"])."</td>";
                                $html.="</tr>";
                                $html.="<tr class='win_sse_form active' data-id='{$row['clue_store_id']}'>";
                                $html.="<td colspan='11'>";
                                $clueSSEModel = new ContProSSEForm($sec_type);
                                $clueSSEModel->busine_id = $row["busine_id"];
                                $clueSSEModel->service = array();
                                foreach ($row["detail_json"] as $detailJson){
                                    $clueSSEModel->service=array_merge($clueSSEModel->service,$detailJson);
                                }
                                $html.=$this->renderPartial("//contPro/sseForm",array('clueSSEModel'=>$clueSSEModel,'form'=>$form),true);
                                $html.="</td>";
                                $html.="</tr>";
                            }
                        }
                        echo $html;
                        ?>
                        </tbody>
                    </table>
                </div>
                <div id="contProStorePagination" style="text-align:center;margin-top:10px;"></div>
            </div>
        </div>
    </div>
</div>

<?php
if($model->isReadonly()===false){
    $this->renderPartial("//contPro/sseStoreDialog",array('model'=>$model));
    $this->renderPartial("//cont/settingFreeJS");
}
?>

<?php
$sec_type = $model->isReadonly()===true ? 'view' : 'edit';
$tplModel = new ContProSSEForm($sec_type);
$tplModel->city = $model->city;
$tplModel->busine_id = $model->busine_id;
$tplModel->service = array();
$tplHtml = $this->renderPartial("//contPro/sseForm", array('clueSSEModel' => $tplModel, 'form' => $form), true);
?>
<div id="contProSseFormTpl" class="hide"><?php echo $tplHtml; ?></div>

<style>
    .contpro-page-hide { display: none !important; }
</style>
<?php
$js = <<<EOF
var contProStorePageSize = 10;
var contProStoreCurrentPage = 1;
var contProStoreCurrentSearch = '';

function contProStoreNormalizeText(text) {
    return $.trim((text || '') + '').toLowerCase();
}

function contProStoreGetSelectedRows() {
    return $('.win_sse_store').filter(function(){
        return !$(this).hasClass('hide');
    });
}

function contProStoreSetRowPageHide(storeId, hide) {
    var selector = '[data-id="' + storeId + '"]';
    var storeRow = $('.win_sse_store' + selector);
    var formRow = $('.win_sse_form' + selector);
    if (hide) {
        storeRow.addClass('contpro-page-hide');
        formRow.addClass('contpro-page-hide');
    } else {
        storeRow.removeClass('contpro-page-hide');
        formRow.removeClass('contpro-page-hide');
    }
}

function contProStoreRenderPagination(totalRow, pageNum, noOfPages) {
    totalRow = parseInt(totalRow || 0);
    pageNum = parseInt(pageNum || 1);
    noOfPages = parseInt(noOfPages || 1);
    contProStoreCurrentPage = pageNum;

    var html = '';
    if (noOfPages > 1) {
        html += '<div style="display:inline-block;">';
        if (pageNum > 1) {
            html += '<a href="javascript:void(0);" class="contpro-store-page-link" data-page="' + (pageNum - 1) + '">上一页</a> ';
        }
        var startPage = Math.max(1, pageNum - 2);
        var endPage = Math.min(noOfPages, pageNum + 2);
        for (var i = startPage; i <= endPage; i++) {
            if (i === pageNum) {
                html += '<span style="margin:0 5px;font-weight:bold;">' + i + '</span>';
            } else {
                html += '<a href="javascript:void(0);" class="contpro-store-page-link" data-page="' + i + '" style="margin:0 5px;">' + i + '</a>';
            }
        }
        if (pageNum < noOfPages) {
            html += ' <a href="javascript:void(0);" class="contpro-store-page-link" data-page="' + (pageNum + 1) + '">下一页</a>';
        }
        html += '</div>';
        html += ' <span style="margin-left:15px;">共 ' + totalRow + ' 条记录，' + noOfPages + ' 页</span>';
    } else if (totalRow > 0) {
        html = '<span>共 ' + totalRow + ' 条记录</span>';
    }
    $('#contProStorePagination').html(html);
}

function contProStoreApplyFilterAndPage(page) {
    page = parseInt(page || 1);
    if (page <= 0) {
        page = 1;
    }

    var selectedRows = contProStoreGetSelectedRows();
    var matchedIds = [];
    var keyword = contProStoreNormalizeText(contProStoreCurrentSearch);

    selectedRows.each(function(){
        var row = $(this);
        var storeId = '' + row.data('id');
        var text = contProStoreNormalizeText(row.text());
        if (!keyword || text.indexOf(keyword) >= 0) {
            matchedIds.push(storeId);
        } else {
            contProStoreSetRowPageHide(storeId, true);
        }
    });

    var totalRow = matchedIds.length;
    var noOfPages = totalRow > 0 ? Math.max(1, Math.ceil(totalRow / contProStorePageSize)) : 1;
    if (page > noOfPages) {
        page = noOfPages;
    }
    contProStoreCurrentPage = page;

    for (var i = 0; i < matchedIds.length; i++) {
        contProStoreSetRowPageHide(matchedIds[i], false);
    }

    var start = (page - 1) * contProStorePageSize;
    var end = start + contProStorePageSize;
    for (var j = 0; j < matchedIds.length; j++) {
        var storeId2 = matchedIds[j];
        var inPage = (j >= start && j < end);
        contProStoreSetRowPageHide(storeId2, !inPage);
    }

    contProStoreRenderPagination(totalRow, page, noOfPages);
}

window.refreshContProStorePager = function(){
    contProStoreApplyFilterAndPage(contProStoreCurrentPage);
};

$('#btnContProStoreSearch').on('click', function(){
    contProStoreCurrentSearch = $('#contProStoreSearchInput').val() || '';
    contProStoreApplyFilterAndPage(1);
});

$('#contProStoreSearchInput').on('keypress', function(e){
    if (e.which == 13) {
        contProStoreCurrentSearch = $(this).val() || '';
        contProStoreApplyFilterAndPage(1);
        return false;
    }
});

$('#btnContProStoreReset').on('click', function(){
    $('#contProStoreSearchInput').val('');
    contProStoreCurrentSearch = '';
    contProStoreApplyFilterAndPage(1);
});

$(document).on('click', '.contpro-store-page-link', function(){
    var page = parseInt($(this).data('page') || 1);
    contProStoreApplyFilterAndPage(page);
});

$(document).ready(function(){
    contProStoreApplyFilterAndPage(1);
});
EOF;
Yii::app()->clientScript->registerScript('contProStorePager',$js,CClientScript::POS_READY);
?>
