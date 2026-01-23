
<?php
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'sseStoreDialog',
    'header'=>Yii::t("clue","clue service store"),
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','type'=>'button')),
        TbHtml::button(Yii::t('dialog','OK'), array('id'=>'okBtnSseStore','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY,'type'=>'button')),
    ),
    'show'=>false,
    'size'=>' modal-lg',
));
?>
<style>
#sseStoreDialog .modal-dialog {
    width: 90%;
    max-width: 1200px;
}
@media (max-width: 768px) {
    #sseStoreDialog .modal-dialog {
        width: 95%;
        margin: 10px auto;
    }
}
</style>
<div class="form-group">
    <div class="col-lg-12">
        <div class="form-group" style="margin-bottom: 15px;">
            <div class="col-lg-8" style="padding-left:0;">
                <div class="input-group">
                    <?php echo TbHtml::textField("sse_store_search","",array(
                        "class"=>"form-control",
                        "placeholder"=>"搜索门店名称/编号",
                        "id"=>"sseStoreSearchInput"
                    )); ?>
                    <span class="input-group-btn">
                        <?php echo TbHtml::button('<i class="fa fa-search"></i> 搜索', array(
                            'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                            'id'=>'btnSseStoreSearch',
                            'type'=>'button'
                        )); ?>
                    </span>
                </div>
            </div>
            <div class="col-lg-4" style="padding-right:0;">
                <?php echo TbHtml::button('<i class="fa fa-refresh"></i> 重置', array(
                    'color'=>TbHtml::BUTTON_COLOR_DEFAULT,
                    'id'=>'btnSseStoreReset',
                    'type'=>'button'
                )); ?>
            </div>
        </div>
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

        <div id="sseStorePagination" style="text-align:center;margin-top:10px;"></div>

        <?php echo TbHtml::hiddenField("checkStore",implode(",",$model->showStore),array("id"=>"checkStore")); ?>
    </div>
</div>
<?php $this->endWidget(); ?>


<?php
$modelClass = get_class($model);
$ajaxUrl = Yii::app()->createUrl('contPro/ajaxLoadSseStores');
$ajaxGetUrl = Yii::app()->createUrl('contPro/ajaxGetSseStoreRows');
$js = <<<EOF
var sseStoreSelectedIds = [];
var sseStoreCurrentPage = 1;
var sseStoreTotalPages = 1;
var sseStoreCurrentSearch = '';

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

function renderSseStorePagination(pageNum, noOfPages, totalRow) {
    pageNum = parseInt(pageNum || 1);
    noOfPages = parseInt(noOfPages || 1);
    totalRow = parseInt(totalRow || 0);

    sseStoreCurrentPage = pageNum;
    sseStoreTotalPages = noOfPages;

    var html = '';
    if (noOfPages > 1) {
        html += '<div style="display:inline-block;">';

        if (pageNum > 1) {
            html += '<a href="javascript:void(0);" class="sse-store-page-link" data-page="' + (pageNum - 1) + '">上一页</a> ';
        }

        var startPage = Math.max(1, pageNum - 2);
        var endPage = Math.min(noOfPages, pageNum + 2);
        for (var i = startPage; i <= endPage; i++) {
            if (i === pageNum) {
                html += '<span style="margin:0 5px;font-weight:bold;">' + i + '</span>';
            } else {
                html += '<a href="javascript:void(0);" class="sse-store-page-link" data-page="' + i + '" style="margin:0 5px;">' + i + '</a>';
            }
        }

        if (pageNum < noOfPages) {
            html += ' <a href="javascript:void(0);" class="sse-store-page-link" data-page="' + (pageNum + 1) + '">下一页</a>';
        }

        html += '</div>';
        html += ' <span style="margin-left:15px;">共 ' + totalRow + ' 条记录，' + noOfPages + ' 页</span>';
    } else if (totalRow > 0) {
        html = '<span>共 ' + totalRow + ' 条记录</span>';
    }
    $('#sseStorePagination').html(html);
}

function applySelectedStoresToMainTable(selectedIds) {
    selectedIds = selectedIds || [];
    var existing = {};
    $('.win_sse_store').each(function() {
        var storeId = '' + $(this).data('id');
        if (isStoreSelected(storeId, selectedIds)) {
            existing[storeId] = true;
        } else {
            $(this).remove();
            $('.win_sse_form[data-id="' + storeId + '"]').remove();
        }
    });
    var missing = [];
    for (var i = 0; i < selectedIds.length; i++) {
        var sid = '' + selectedIds[i];
        if (!existing[sid]) {
            missing.push(sid);
        }
    }
    return missing;
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

function appendMainStoreRows(html) {
    var tbody = $('#contProStoreTableBody');
    if (tbody.length <= 0) {
        tbody = $('.win_sse_store').closest('tbody');
    }
    if (tbody.length > 0 && html) {
        tbody.append(html);
    }
}

function escapeHtml(text) {
    return $('<div/>').text(text == null ? '' : ('' + text)).html();
}

function renderMainStoreRows(stores) {
    stores = stores || [];
    if (stores.length <= 0) {
        return '';
    }
    var tpl = $('#contProSseFormTpl').html() || '';
    var html = '';
    for (var i = 0; i < stores.length; i++) {
        var s = stores[i] || {};
        var storeId = '' + (s.id || '');
        if (!storeId) {
            continue;
        }
        html += '<tr data-id="' + escapeHtml(storeId) + '" data-area="' + escapeHtml(s.area || 0) + '" class="win_sse_store">';
        html += '<td>' + escapeHtml(s.store_name || '') + '</td>';
        html += '<td>' + escapeHtml(s.district || '') + '</td>';
        html += '<td>' + escapeHtml(s.address || '') + '</td>';
        html += '<td>' + escapeHtml(s.cust_person || '') + '</td>';
        html += '<td>' + escapeHtml(s.cust_tel || '') + '</td>';
        html += '<td>' + escapeHtml(s.invoice_header || '') + '</td>';
        html += '<td>' + escapeHtml(s.tax_id || '') + '</td>';
        html += '<td>' + escapeHtml(s.invoice_address || '') + '</td>';
        html += '<td>' + (s.busine_text || '') + '</td>';
        html += '<td>' + escapeHtml(s.sales || '') + '</td>';
        html += '<td class="area">' + escapeHtml(s.area_text || '') + '</td>';
        html += '</tr>';
        html += '<tr class="win_sse_form active" data-id="' + escapeHtml(storeId) + '"><td colspan="11">' + tpl + '</td></tr>';
    }
    return html;
}

function fillMainStoreForm(storeId, service) {
    storeId = '' + storeId;
    service = service || {};
    var formRow = $('.win_sse_form[data-id="' + storeId + '"]');
    if (formRow.length <= 0) {
        return;
    }
    for (var key in service) {
        if (!service.hasOwnProperty(key)) {
            continue;
        }
        var value = service[key];
        var name = 'ContProSSEForm[service][' + key + ']';
        var fields = formRow.find('[name="' + name + '"]');
        if (fields.length <= 0) {
            continue;
        }
        var checkbox = fields.filter(':checkbox');
        if (checkbox.length > 0) {
            checkbox.prop('checked', value === 'Y');
        } else {
            fields.val(value);
        }
    }
}

function loadMainStoreRows(storeIds, callback) {
    storeIds = storeIds || [];
    if (storeIds.length <= 0) {
        if (callback) {
            callback();
        }
        return;
    }
    window.contProStoreLoading = true;
    var proId = $('#ContProForm_id').val() || '0';
    var contId = $('#ContProForm_cont_id').val() || '0';
    var proType = $('#pro_type').val() || '';

    $.ajax({
        url: '{$ajaxGetUrl}',
        type: 'GET',
        data: {
            pro_id: proId,
            cont_id: contId,
            pro_type: proType,
            store_ids: storeIds.join(',')
        },
        dataType: 'json',
        success: function(response){
            if(response.status === 'success' && response.data){
                var html = renderMainStoreRows(response.data || []);
                appendMainStoreRows(html);
                for (var i = 0; i < response.data.length; i++) {
                    var s = response.data[i] || {};
                    if (s.id) {
                        fillMainStoreForm(s.id, s.service || {});
                    }
                }
                // 重新初始化 select2（标靶虫害、设备、洁具等）
                // 先销毁已存在的 select2 实例，避免重复初始化
                $('.changePestMethod,.changeDevice,.changeWare').each(function(){
                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                });
                setTimeout(function(){
                    $('.changePestMethod,.changeDevice,.changeWare').select2({
                        tags: false,
                        multiple: true,
                        allowClear: true,
                        closeOnSelect: false,
                        disabled: false,
                        dropdownParent: $('body'),
                        language: 'zh-CN',
                        width: '100%',
                        templateSelection: function(state) {
                            var rtn = $('<span style="color:black">'+state.text+'</span>');
                            return rtn;
                        }
                    });
                }, 100);
            }
            if (callback) {
                callback();
            }
            window.contProStoreLoading = false;
        },
        error: function(){
            if (callback) {
                callback();
            }
            window.contProStoreLoading = false;
        }
    });
}

// 加载门店数据
function loadSseStores(page, search) {
    var tbody = $('#sseStoreTableBody');
    var proId = $('#ContProForm_id').val() || '0';
    var contId = $('#ContProForm_cont_id').val() || '0';
    var proType = $('#pro_type').val() || '';
    page = page || 1;
    search = search || '';
    sseStoreCurrentSearch = search;
    
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
            pro_type: proType,
            page: page,
            search: search
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
                renderSseStorePagination(response.pageNum, response.noOfPages, response.totalRow);
            }else{
                tbody.html('<tr><td colspan="8" style="color:red;text-align:center;">加载失败：' + response.message + '</td></tr>');
                $('#sseStorePagination').html('');
            }
        },
        error: function(xhr, status, error){
            console.error('Ajax错误:', status, error);
            tbody.html('<tr><td colspan="8" style="color:red;text-align:center;">加载失败，请重试</td></tr>');
            $('#sseStorePagination').html('');
        }
    });
}

// 点击关联门店按钮时加载数据
$(document).on('click', 'button[name="sseStore"]', function(){
    resetTempSelectedIdsFromHidden();
    $('#sseStoreSearchInput').val('');
    loadSseStores(1, '');
});

$('#btnSseStoreSearch').on('click', function(){
    var search = $('#sseStoreSearchInput').val() || '';
    loadSseStores(1, search);
});

$('#sseStoreSearchInput').on('keypress', function(e){
    if(e.which == 13){
        var search = $(this).val() || '';
        loadSseStores(1, search);
        return false;
    }
});

$('#btnSseStoreReset').on('click', function(){
    $('#sseStoreSearchInput').val('');
    loadSseStores(1, '');
});

$(document).on('click', '.sse-store-page-link', function(){
    var page = parseInt($(this).data('page') || 1);
    if (page > 0) {
        loadSseStores(page, sseStoreCurrentSearch);
    }
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
    var missing = applySelectedStoresToMainTable(sseStoreSelectedIds);
    loadMainStoreRows(missing, function(){
        if (window.refreshContProStorePager) {
            window.refreshContProStorePager();
        }
    });
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
    $('#sseStorePagination').html('');
    setTimeout(cleanupSseStoreModalArtifacts, 0);
});
   
EOF;
Yii::app()->clientScript->registerScript('changeSseStore',$js,CClientScript::POS_READY);
?>
