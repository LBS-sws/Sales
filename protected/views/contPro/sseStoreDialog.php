
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
                <tbody id="sseStoreTableBody">
                </tbody>
            </table>
        </div>

        <?php echo TbHtml::hiddenField("checkStore",implode(",",$model->showStore),array("id"=>"checkStore")); ?>
    </div>
</div>
<?php $this->endWidget(); ?>


<?php
$modelClass = get_class($model);
$ajaxUrl = Yii::app()->createUrl('contPro/ajaxLoadSseStores');
$js = <<<EOF
var sseStoreSelectedIds = [];

function getHiddenCheckStoreList() {
    var checkStoreVal = $('#checkStore').val() || '';
    if (!checkStoreVal) {
        return [];
    }
    var parts = checkStoreVal.split(',');
    var ids = [];
    for (var i = 0; i < parts.length; i++) {
        var id = $.trim(parts[i]);
        if (id) {
            ids.push(id);
        }
    }
    return ids;
}

function setTempSelectedIds(ids) {
    var seen = {};
    var out = [];
    for (var i = 0; i < ids.length; i++) {
        var id = $.trim(ids[i]);
        if (id && !seen[id]) {
            seen[id] = true;
            out.push(id);
        }
    }
    sseStoreSelectedIds = out;
}

function resetTempSelectedIdsFromHidden() {
    setTempSelectedIds(getHiddenCheckStoreList());
}

function commitTempSelectedIdsToHidden() {
    $('#checkStore').val((sseStoreSelectedIds || []).join(','));
}

function isStoreSelected(storeId, selectedIds) {
    storeId = '' + storeId;
    for (var i = 0; i < selectedIds.length; i++) {
        if (selectedIds[i] === storeId) {
            return true;
        }
    }
    return false;
}

function applySelectedStoresToMainTable(selectedIds) {
    selectedIds = selectedIds || [];
    $('.win_sse_store').each(function() {
        var storeId = '' + $(this).data('id');
        if (isStoreSelected(storeId, selectedIds)) {
            $(this).removeClass('hide');
            $('.win_sse_form[data-id="' + storeId + '"]').removeClass('hide');
        } else {
            $(this).addClass('hide');
            $('.win_sse_form[data-id="' + storeId + '"]').addClass('hide');
        }
    });
}

function refreshCheckAllState() {
    var allChecked = true;
    var hasAny = false;
    $('.win_check_one').each(function() {
        hasAny = true;
        if (!$(this).is(':checked')) {
            allChecked = false;
            return false;
        }
    });
    $('.win_check_all').prop('checked', hasAny && allChecked);
}

// 加载门店数据
function loadSseStores() {
    var tbody = $('#sseStoreTableBody');
    var proId = $('#ContProForm_id').val() || '0';
    var contId = $('#ContProForm_cont_id').val() || '0';
    var proType = $('#pro_type').val() || '';
    
    if(contId == '0'){
        tbody.html('<tr><td colspan="8" style="color:red;text-align:center;">无法获取合同ID</td></tr>');
        return;
    }
    
    tbody.html('<tr><td colspan="8" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>');
    
    $.ajax({
        url: '{$ajaxUrl}',
        type: 'GET',
        data: {
            pro_id: proId,
            cont_id: contId,
            pro_type: proType
        },
        dataType: 'json',
        success: function(response){
            if(response.status === 'success'){
                var html = '';
                var selectedIds = sseStoreSelectedIds || [];
                var selectedEmpty = selectedIds.length === 0;
                var selectedAfter = selectedIds.slice(0);
                if(response.data && response.data.length > 0){
                    $.each(response.data, function(index, store){
                        var storeIdStr = '' + store.id;
                        var checked = isStoreSelected(storeIdStr, selectedIds) || (selectedEmpty && store.checked);
                        if (checked && !isStoreSelected(storeIdStr, selectedAfter)) {
                            selectedAfter.push(storeIdStr);
                        }
                        html += '<tr>';
                        html += '<td>';
                        html += '<input type="checkbox" name="winClueSSE[check][]" class="win_check_one" value="' + store.id + '"' + (checked ? ' checked' : '') + ' />';
                        html += '</td>';
                        html += '<td>' + (store.store_name || '') + '</td>';
                        html += '<td>' + (store.address || '') + '</td>';
                        html += '<td>' + (store.cust_person || '') + '</td>';
                        html += '<td>' + (store.cust_tel || '') + '</td>';
                        html += '<td>' + (store.invoice_header || '') + '</td>';
                        html += '<td>' + (store.tax_id || '') + '</td>';
                        html += '<td>' + (store.invoice_address || '') + '</td>';
                        html += '</tr>';
                    });
                }else{
                    html = '<tr><td colspan="8" style="text-align:center;">没有可关联门店</td></tr>';
                }
                tbody.html(html);
                if (selectedEmpty) {
                    setTempSelectedIds(selectedAfter);
                }
                refreshCheckAllState();
            }else{
                tbody.html('<tr><td colspan="8" style="color:red;text-align:center;">加载失败：' + response.message + '</td></tr>');
            }
        },
        error: function(xhr, status, error){
            console.error('Ajax错误:', status, error);
            tbody.html('<tr><td colspan="8" style="color:red;text-align:center;">加载失败，请重试</td></tr>');
        }
    });
}

// 点击关联门店按钮时加载数据
$(document).on('click', 'button[name="sseStore"]', function(){
    resetTempSelectedIdsFromHidden();
    loadSseStores();
});

$('.win_check_all').on('click',function(){
	var val = $(this).prop('checked');
	$('.win_check_one').prop('checked',val);
    var selectedIds = (sseStoreSelectedIds || []).slice(0);
    $('.win_check_one').each(function(){
        var storeIdStr = '' + $(this).val();
        if (val) {
            if (!isStoreSelected(storeIdStr, selectedIds)) {
                selectedIds.push(storeIdStr);
            }
        } else {
            var next = [];
            for (var i = 0; i < selectedIds.length; i++) {
                if (selectedIds[i] !== storeIdStr) {
                    next.push(selectedIds[i]);
                }
            }
            selectedIds = next;
        }
    });
    setTempSelectedIds(selectedIds);
});

$(document).on('change', '.win_check_one', function(){
    var storeIdStr = '' + $(this).val();
    var selectedIds = (sseStoreSelectedIds || []).slice(0);
    if ($(this).is(':checked')) {
        if (!isStoreSelected(storeIdStr, selectedIds)) {
            selectedIds.push(storeIdStr);
        }
    } else {
        var next = [];
        for (var i = 0; i < selectedIds.length; i++) {
            if (selectedIds[i] !== storeIdStr) {
                next.push(selectedIds[i]);
            }
        }
        selectedIds = next;
    }
    setTempSelectedIds(selectedIds);
    refreshCheckAllState();
});

$('#okBtnSseStore').on('click',function(){
    commitTempSelectedIdsToHidden();
    applySelectedStoresToMainTable(sseStoreSelectedIds);
});

function cleanupSseStoreModalArtifacts() {
    var openModals = $('.modal.in');
    if (openModals.length === 0) {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right','');
    } else if ($('.modal-backdrop').length > 1) {
        $('.modal-backdrop').not(':last').remove();
    }
}

$('#sseStoreDialog').on('hidden.bs.modal', function(){
    resetTempSelectedIdsFromHidden();
    setTimeout(cleanupSseStoreModalArtifacts, 0);
});
   
EOF;
Yii::app()->clientScript->registerScript('changeSseStore',$js,CClientScript::POS_READY);
?>
