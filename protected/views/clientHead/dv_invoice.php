<?php
/**
 * 开票信息
 */
?>

<div  style="padding-top: 15px;">
    <div>
        <?php
        echo TbHtml::button(Yii::t('clue','add invoice'), array(
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
            'data-load'=>Yii::app()->createUrl('clueInvoice/ajaxShow'),
            'data-submit'=>Yii::app()->createUrl('clueInvoice/ajaxSave'),
            'data-serialize'=>"ClueInvoiceForm[scenario]=new&ClueInvoiceForm[clue_id]=".$model->id,
            'data-obj'=>"#clue_dv_invoice_dummy",
            'data-fun'=>'refreshInvoiceData',
            'class'=>'openDialogForm',
        ));
        ?>
    </div>
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue',"invoice name"); ?></th>
                <th><?php echo Yii::t('clue',"invoice type"); ?></th>
                <th><?php echo Yii::t('clue',"invoice header"); ?></th>
                <th><?php echo Yii::t('clue',"tax id"); ?></th>
                <th><?php echo Yii::t('clue',"invoice address"); ?></th>
                <th><?php echo Yii::t('clue',"invoice number"); ?></th>
                <th><?php echo Yii::t('clue',"invoice user"); ?></th>
                <th><?php echo Yii::t('clue',"z display"); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody id="dv_invoice_body">
            <tr><td colspan="9" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-lg-12 text-center" id="clue_invoice_pagination"></div>
    </div>
</div>

<?php
$ajaxUrl = Yii::app()->createUrl('clientHead/ajaxLoadInvoice');
$editUrl = Yii::app()->createUrl('clueInvoice/ajaxShow');
$saveUrl = Yii::app()->createUrl('clueInvoice/ajaxSave');
$clueId = $model->id;
$js = <<<EOF
var invoiceLoaded = false;
var invoiceCurrentPage = 1;
$('a[href="#clue_dv_invoice"]').on('shown.bs.tab', function (e) {
    if(!invoiceLoaded){
        loadClientInvoice(1);
        invoiceLoaded = true;
    }
});

function loadClientInvoice(page){
    var tbody = $('#dv_invoice_body');
    var paginationDiv = $('#clue_invoice_pagination');
    if(!page || page < 1){
        page = 1;
    }
    
    $.ajax({
        url: '{$ajaxUrl}',
        type: 'GET',
        data: {
            clue_id: {$clueId},
            page: page
        },
        dataType: 'json',
        success: function(response){
            if(response.status === 1){
                var html = '';
                invoiceCurrentPage = response.pageNum || page;
                if(response.data && response.data.length > 0){
                    $.each(response.data, function(i, row){
                        html += '<tr>';
                        html += '<td>' + (row.invoice_name || '') + '</td>';
                        html += '<td>' + (row.invoice_type || '') + '</td>';
                        html += '<td>' + (row.invoice_header || '') + '</td>';
                        html += '<td>' + (row.tax_id || '') + '</td>';
                        html += '<td>' + (row.invoice_address || '') + '</td>';
                        html += '<td>' + (row.invoice_number || '') + '</td>';
                        html += '<td>' + (row.invoice_user || '') + '</td>';
                        html += '<td>' + (row.z_display || '') + '</td>';
                        html += '<td>';
                        if(row.can_edit){
                            html += '<a href="javascript:void(0);" class="openDialogForm" data-load="{$editUrl}" data-submit="{$saveUrl}" data-serialize="ClueInvoiceForm[scenario]=edit&ClueInvoiceForm[id]=' + row.id + '" data-obj="#clue_dv_invoice_dummy" data-fun="refreshInvoiceData"><span class="glyphicon glyphicon-pencil"></span></a>';
                        }
                        html += '</td>';
                        html += '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="9" style="text-align:center;">暂无数据</td></tr>';
                }
                tbody.html(html);
                renderInvoicePagination(response.pageNum, response.noOfPages, response.totalRow, response.pageSize);
            } else {
                tbody.html('<tr><td colspan="9" style="text-align:center; color: red;">加载失败: ' + (response.error || '未知错误') + '</td></tr>');
                paginationDiv.html('');
            }
        },
        error: function(xhr, status, error){
            var errorMsg = '加载失败，请刷新页面重试';
            if(xhr.responseJSON && xhr.responseJSON.error){
                errorMsg = '加载失败: ' + xhr.responseJSON.error;
            } else if(error){
                errorMsg = '加载失败: ' + error;
            }
            tbody.html('<tr><td colspan="9" style="text-align:center; color: red;">' + errorMsg + '</td></tr>');
            paginationDiv.html('');
        }
    });
}

function renderInvoicePagination(currentPage, totalPages, totalRows, pageSize){
    var paginationDiv = $('#clue_invoice_pagination');
    var html = '';
    currentPage = currentPage ? parseInt(currentPage, 10) : 1;
    totalPages = totalPages ? parseInt(totalPages, 10) : 1;
    totalRows = totalRows ? parseInt(totalRows, 10) : 0;
    pageSize = pageSize ? parseInt(pageSize, 10) : 10;

    if(totalPages > 1){
        html += '<ul class="pagination">';
        html += '<li ' + (currentPage === 1 ? 'class="disabled"' : '') + '><a href="javascript:void(0);" onclick="loadClientInvoice(' + (currentPage - 1) + ')">上一页</a></li>';

        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, currentPage + 2);

        if(startPage > 1){
            html += '<li><a href="javascript:void(0);" onclick="loadClientInvoice(1)">1</a></li>';
            if(startPage > 2){
                html += '<li class="disabled"><a href="javascript:void(0);">...</a></li>';
            }
        }

        for(var i = startPage; i <= endPage; i++){
            html += '<li ' + (currentPage === i ? 'class="active"' : '') + '><a href="javascript:void(0);" onclick="loadClientInvoice(' + i + ')">' + i + '</a></li>';
        }

        if(endPage < totalPages){
            if(endPage < totalPages - 1){
                html += '<li class="disabled"><a href="javascript:void(0);">...</a></li>';
            }
            html += '<li><a href="javascript:void(0);" onclick="loadClientInvoice(' + totalPages + ')">' + totalPages + '</a></li>';
        }

        html += '<li ' + (currentPage === totalPages ? 'class="disabled"' : '') + '><a href="javascript:void(0);" onclick="loadClientInvoice(' + (currentPage + 1) + ')">下一页</a></li>';
        html += '</ul>';
    }
    html += '<p class="text-center">共 ' + totalRows + ' 条记录，每页 ' + pageSize + ' 条，共 ' + totalPages + ' 页</p>';
    paginationDiv.html(html);
}

function refreshInvoiceData(response){
    // 保存成功后重新加载数据
    loadClientInvoice(invoiceCurrentPage);
}
EOF;
Yii::app()->clientScript->registerScript('loadClientInvoice',$js,CClientScript::POS_READY);
?>
