<div class="information-header">
    <h4>
        <strong>涉及门店</strong>
    </h4>
</div>
<div class="form-group">
    <div class="col-lg-12">
        <div class="input-group" style="max-width: 480px;">
            <?php echo TbHtml::textField("virtualBatchStoreSearch","",array(
                "class"=>"form-control",
                "placeholder"=>"搜索门店名称/编号/虚拟合约号",
                "id"=>"virtualBatchStoreSearchInput"
            )); ?>
            <span class="input-group-btn">
                <?php echo TbHtml::button('<i class="fa fa-search"></i> 搜索', array(
                    'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                    'id'=>'btnVirtualBatchStoreSearch',
                    'type'=>'button'
                )); ?>
                <?php echo TbHtml::button('<i class="fa fa-refresh"></i> 重置', array(
                    'color'=>TbHtml::BUTTON_COLOR_DEFAULT,
                    'id'=>'btnVirtualBatchStoreReset',
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
                    <th><?php echo Yii::t("clue","store code")?></th><!--门店编号-->
                    <th><?php echo Yii::t("clue","store name")?></th><!--门店名称-->
                    <th><?php echo Yii::t("clue","sales")?></th><!--门店编号-->
                    <th><?php echo Yii::t("clue","virtual code")?></th><!--虚拟合约编号-->
                    <th><?php echo Yii::t("clue","service obj")?></th><!--服务项目-->
                    <th><?php echo Yii::t("clue","yewudalei")?></th><!--业务大类-->
                    <th><?php echo Yii::t("clue","sign type")?></th><!--签约类型-->
                    <th><?php echo Yii::t("clue","invoice amt")?></th><!--发票金额-->
                    <th><?php echo Yii::t("clue","status")?></th><!--状态-->
                    <?php if ($model->pro_type=="T"): ?>
                        <th><?php echo Yii::t("clue","surplus num")?></th><!--剩余次数 -->
                        <th><?php echo Yii::t("clue","surplus amt")?></th><!--剩余金额-->
                    <?php endif ?>
                </tr>
                </thead>
                <tbody id="virtualBatchStoreTbody">
                <?php
                $html = "";
                $rows = $model->virHeadRows;
                if(!empty($rows)){
                    foreach ($rows as $row){
                        $storeList=CGetName::getClueStoreRowByStoreID($row["clue_store_id"]);
                        $busine_id_text = CGetName::getBusineStrByText($row["busine_id_text"]);
                        $html.="<tr>";
                        $html.="<td>".$storeList["store_code"]."</td>";
                        $html.="<td>".$storeList["store_name"]."</td>";
                        $html.="<td>".CGetName::getEmployeeNameByKey($row["sales_id"])."</td>";
                        $html.="<td>";
                        $url=Yii::app()->createUrl('virtualHead/detail',array("index"=>$row['id']));
                        $html.=TbHtml::link($row["vir_code"],$url,array(
                            "target"=>"_blank"
                        ));
                        $html.="</td>";
                        $html.="<td>".$busine_id_text."</td>";
                        $html.="<td>".CGetName::getYewudaleiStrByKey($row["yewudalei"])."</td>";
                        $html.="<td>".CGetName::getSignTypeStrByKey($row["sign_type"])."</td>";
                        $html.="<td>".$row["year_amt"]."</td>";
                        $html.="<td>".CGetName::getContVirStatusStrByKey($row["vir_status"])."</td>";
                        if($model->pro_type=="T"){
                            $surplus_number = isset($model->surplus_json[$row["id"]]["surplus_number"])?$model->surplus_json[$row["id"]]["surplus_number"]:0;
                            $surplus_money = isset($model->surplus_json[$row["id"]]["surplus_money"])?$model->surplus_json[$row["id"]]["surplus_money"]:0;
                            $html.="<td>".$surplus_number."</td>";
                            $html.="<td>".$surplus_money."</td>";
                        }
                        $html.="</tr>";
                    }
                }
                echo $html;
                ?>
                </tbody>
            </table>
        </div>
        <div id="virtualBatchStorePagination" style="text-align:center;margin-top:10px;"></div>
    </div>
</div>

<style>
    .virbatch-page-hide { display: none !important; }
</style>
<?php
$js = <<<EOF
var virtualBatchStorePageSize = 10;
var virtualBatchStoreCurrentPage = 1;
var virtualBatchStoreCurrentSearch = '';

function virtualBatchNormalizeText(text) {
    return $.trim((text || '') + '').toLowerCase();
}

function virtualBatchRenderPagination(totalRow, pageNum, noOfPages) {
    totalRow = parseInt(totalRow || 0);
    pageNum = parseInt(pageNum || 1);
    noOfPages = parseInt(noOfPages || 1);
    virtualBatchStoreCurrentPage = pageNum;

    var html = '';
    if (noOfPages > 1) {
        html += '<div style="display:inline-block;">';
        if (pageNum > 1) {
            html += '<a href="javascript:void(0);" class="virbatch-store-page-link" data-page="' + (pageNum - 1) + '">上一页</a> ';
        }
        var startPage = Math.max(1, pageNum - 2);
        var endPage = Math.min(noOfPages, pageNum + 2);
        for (var i = startPage; i <= endPage; i++) {
            if (i === pageNum) {
                html += '<span style="margin:0 5px;font-weight:bold;">' + i + '</span>';
            } else {
                html += '<a href="javascript:void(0);" class="virbatch-store-page-link" data-page="' + i + '" style="margin:0 5px;">' + i + '</a>';
            }
        }
        if (pageNum < noOfPages) {
            html += ' <a href="javascript:void(0);" class="virbatch-store-page-link" data-page="' + (pageNum + 1) + '">下一页</a>';
        }
        html += '</div>';
        html += ' <span style="margin-left:15px;">共 ' + totalRow + ' 条记录，' + noOfPages + ' 页</span>';
    } else if (totalRow > 0) {
        html = '<span>共 ' + totalRow + ' 条记录</span>';
    }
    $('#virtualBatchStorePagination').html(html);
}

function virtualBatchApplyFilterAndPage(page) {
    page = parseInt(page || 1);
    if (page <= 0) {
        page = 1;
    }

    var keyword = virtualBatchNormalizeText(virtualBatchStoreCurrentSearch);
    var rows = $('#virtualBatchStoreTbody').find('tr');
    var matched = [];

    rows.each(function(){
        var tr = $(this);
        tr.removeClass('virbatch-page-hide');
        if (!keyword) {
            matched.push(tr);
            return;
        }
        var text = virtualBatchNormalizeText(tr.text());
        if (text.indexOf(keyword) >= 0) {
            matched.push(tr);
        } else {
            tr.addClass('virbatch-page-hide');
        }
    });

    var totalRow = matched.length;
    var noOfPages = totalRow > 0 ? Math.max(1, Math.ceil(totalRow / virtualBatchStorePageSize)) : 1;
    if (page > noOfPages) {
        page = noOfPages;
    }
    virtualBatchStoreCurrentPage = page;

    var start = (page - 1) * virtualBatchStorePageSize;
    var end = start + virtualBatchStorePageSize;

    for (var i = 0; i < matched.length; i++) {
        if (i < start || i >= end) {
            $(matched[i]).addClass('virbatch-page-hide');
        }
    }

    virtualBatchRenderPagination(totalRow, page, noOfPages);
}

$('#btnVirtualBatchStoreSearch').on('click', function(){
    virtualBatchStoreCurrentSearch = $('#virtualBatchStoreSearchInput').val() || '';
    virtualBatchApplyFilterAndPage(1);
});

$('#virtualBatchStoreSearchInput').on('keypress', function(e){
    if (e.which == 13) {
        virtualBatchStoreCurrentSearch = $(this).val() || '';
        virtualBatchApplyFilterAndPage(1);
        return false;
    }
});

$('#btnVirtualBatchStoreReset').on('click', function(){
    $('#virtualBatchStoreSearchInput').val('');
    virtualBatchStoreCurrentSearch = '';
    virtualBatchApplyFilterAndPage(1);
});

$(document).on('click', '.virbatch-store-page-link', function(){
    var page = parseInt($(this).data('page') || 1);
    virtualBatchApplyFilterAndPage(page);
});

$(document).ready(function(){
    virtualBatchApplyFilterAndPage(1);
});
EOF;
Yii::app()->clientScript->registerScript('virtualBatchStorePager',$js,CClientScript::POS_READY);
?>
