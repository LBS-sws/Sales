<?php
$form = $this->beginWidget('TbActiveForm', array(
    'id'=>'transferStoreForm',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
    'htmlOptions'=>array('style'=>'width:100%;'),
));
?>

<div class="modal fade" id="transferStoreDialog" tabindex="-1" role="dialog" aria-labelledby="transferStoreLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transferStoreLabel">转移门店</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="transferStoreFormInner">
                    <div class="form-group">
                        <label>门店名称</label>
                        <input type="text" class="form-control" value="<?php echo CHtml::encode($model->store_name); ?>" readonly>
                        <input type="hidden" id="transfer_store_id" value="<?php echo $model->id; ?>">
                    </div>

                    <div class="form-group">
                        <label>目标客户 <span style="color:red;">*</span></label>
                        <input type="text" id="target_customer_search" class="form-control" 
                               placeholder="输入客户名称或客户编码" autocomplete="off">
                        <input type="hidden" id="target_clue_id" name="target_clue_id" value="">
                        <div id="customer_search_results" style="display:none; position:absolute; background:#fff; border:1px solid #ddd; width:100%; max-height:250px; overflow-y:auto; z-index:1000; margin-top:-1px;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>转移原因</label>
                        <textarea class="form-control" id="transfer_reason" name="transfer_reason" rows="3" placeholder="请输入转移原因（可选）"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="btnTransferStore">确认转移</button>
            </div>
        </div>
    </div>
</div>

<script>
var transferStoreGlobalTimer = null;
$(document).ready(function() {
    // 搜索客户
    $('#target_customer_search').on('input', function() {
        var keyword = $(this).val().trim();
        var storeId = $('#transfer_store_id').val();
        var results = $('#customer_search_results');

        clearTimeout(transferStoreGlobalTimer);

        if (keyword.length < 2) {
            results.hide();
            return;
        }

        transferStoreGlobalTimer = setTimeout(function() {
            $.ajax({
                url: '<?php echo Yii::app()->createUrl('clueStore/searchTargetCustomer'); ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    keyword: keyword,
                    store_id: storeId
                },
                success: function(data) {
                    if (data.status == 1 && data.results.length > 0) {
                        var html = '';
                        $.each(data.results, function(index, item) {
                            html += '<div class="customer-item" data-id="' + item.id + '" style="padding:8px; border-bottom:1px solid #eee; cursor:pointer;">' + item.name + '</div>';
                        });
                        results.html(html).show();

                        $('.customer-item').on('click', function() {
                            var customerId = $(this).data('id');
                            var customerName = $(this).text();
                            $('#target_customer_search').val(customerName);
                            $('#target_clue_id').val(customerId);
                            results.hide();
                        });
                    } else {
                        results.html('<div style="padding:8px; text-align:center;">未找到相关客户</div>').show();
                    }
                }
            });
        }, 300);
    });

    // 隐藏搜索结果
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#target_customer_search, #customer_search_results').length) {
            $('#customer_search_results').hide();
        }
    });

    // 转移门店
    $('#btnTransferStore').on('click', function() {
        var targetClueId = $('#target_clue_id').val();
        var storeId = $('#transfer_store_id').val();

        if (!targetClueId) {
            alert('请选择目标客户');
            return;
        }

        if (!confirm('确认要转移这个门店及其关联的合约吗？')) {
            return;
        }

        $.ajax({
            url: '<?php echo Yii::app()->createUrl('clueStore/transferStore'); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                store_id: storeId,
                target_clue_id: targetClueId,
                transfer_reason: $('#transfer_reason').val()
            },
            success: function(data) {
                if (data.status) {
                    alert(data.message);
                    $('#transferStoreDialog').modal('hide');
                    location.reload();
                } else {
                    alert('转移失败：' + data.message);
                }
            },
            error: function() {
                alert('请求失败，请重试');
            }
        });
    });
});
</script>

<?php $this->endWidget(); ?>
