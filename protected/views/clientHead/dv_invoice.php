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
</div>

<?php
$ajaxUrl = Yii::app()->createUrl('clientHead/ajaxLoadInvoice');
$editUrl = Yii::app()->createUrl('clueInvoice/ajaxShow');
$saveUrl = Yii::app()->createUrl('clueInvoice/ajaxSave');
$clueId = $model->id;
$js = <<<EOF
var invoiceLoaded = false;
$('a[href="#clue_dv_invoice"]').on('shown.bs.tab', function (e) {
    if(!invoiceLoaded){
        loadClientInvoice();
        invoiceLoaded = true;
    }
});

function loadClientInvoice(){
    var tbody = $('#dv_invoice_body');
    
    $.ajax({
        url: '{$ajaxUrl}',
        type: 'GET',
        data: {
            clue_id: {$clueId}
        },
        dataType: 'json',
        success: function(response){
            if(response.status === 1){
                var html = '';
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
            } else {
                tbody.html('<tr><td colspan="9" style="text-align:center; color: red;">加载失败: ' + (response.error || '未知错误') + '</td></tr>');
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
        }
    });
}

function refreshInvoiceData(response){
    // 保存成功后重新加载数据
    loadClientInvoice();
}
EOF;
Yii::app()->clientScript->registerScript('loadClientInvoice',$js,CClientScript::POS_READY);
?>
