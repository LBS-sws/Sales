<?php
/**
 * 虚拟合约数据同步页面
 */
$this->pageTitle = '虚拟合约数据同步 - ' . Yii::app()->name;
?>

<style>
.sync-container {
    padding: 20px;
}

.sync-header {
    background: #f5f5f5;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.sync-form {
    background: white;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: inline-block;
    width: 120px;
    font-weight: bold;
}

.form-group input[type="text"],
.form-group textarea {
    width: 400px;
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.form-group textarea {
    height: 100px;
    resize: vertical;
}

.btn-group {
    margin-top: 20px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 10px;
}

.btn-primary {
    background: #337ab7;
    color: white;
}

.btn-primary:hover {
    background: #286090;
}

.btn-success {
    background: #5cb85c;
    color: white;
}

.btn-success:hover {
    background: #449d44;
}

.btn-warning {
    background: #f0ad4e;
    color: white;
}

.btn-warning:hover {
    background: #ec971f;
}

.result-container {
    margin-top: 20px;
}

.result-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.result-table th,
.result-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

.result-table th {
    background: #f5f5f5;
    font-weight: bold;
}

.result-table tr:hover {
    background: #f9f9f9;
}

.diff-row {
    background: #fff3cd !important;
}

.diff-cell {
    background: #ffe6e6;
}

.same-cell {
    background: #e6ffe6;
}

.checkbox-group {
    margin: 10px 0;
}

.checkbox-group label {
    display: inline-block;
    margin-right: 20px;
    font-weight: normal;
}

.loading {
    display: none;
    color: #337ab7;
    margin-left: 10px;
}

.error-message {
    color: #d9534f;
    padding: 10px;
    background: #f2dede;
    border: 1px solid #ebccd1;
    border-radius: 4px;
    margin: 10px 0;
}

.success-message {
    color: #3c763d;
    padding: 10px;
    background: #dff0d8;
    border: 1px solid #d6e9c6;
    border-radius: 4px;
    margin: 10px 0;
}

.field-select-all {
    margin-bottom: 10px;
}
</style>

<div class="sync-container">
    <div class="sync-header">
        <h2>虚拟合约数据同步</h2>
        <p>从派单系统获取最新数据，对比CRM数据差异，支持选择性更新指定字段</p>
    </div>

    <div class="sync-form">
        <h3>1. 查询虚拟合约</h3>

        <div class="form-group">
            <label>派单系统ID:</label>
            <input type="text" id="u_id" placeholder="输入单个派单ID">
        </div>

        <div class="form-group">
            <label>虚拟合同编号:</label>
            <input type="text" id="vir_code" placeholder="输入虚拟合同编号">
        </div>

        <div class="form-group">
            <label>批量派单ID:</label>
            <textarea id="batch_u_ids" placeholder="输入多个派单ID，用逗号分隔&#10;例如: 12345,12346,12347"></textarea>
        </div>

        <div class="btn-group">
            <button class="btn btn-primary" onclick="compareData()">
                对比数据
                <span class="loading" id="loading-compare">正在加载...</span>
            </button>
            <button class="btn btn-warning" onclick="clearResults()">清空结果</button>
        </div>
    </div>

    <div class="sync-form" id="update-section" style="display: none;">
        <h3>2. 选择要更新的字段</h3>

        <div class="field-select-all">
            <label>
                <input type="checkbox" id="select-all-fields" onclick="toggleAllFields(this)">
                全选/取消全选
            </label>
        </div>

        <div class="checkbox-group">
            <label><input type="checkbox" class="update-field" value="month_amt"> 合约月金额</label>
            <label><input type="checkbox" class="update-field" value="year_amt"> 合约年金额</label>
            <label><input type="checkbox" class="update-field" value="service_sum"> 服务总次数</label>
            <label><input type="checkbox" class="update-field" value="service_fre_type"> 服务频次类型</label>
            <label><input type="checkbox" class="update-field" value="service_fre_text"> 服务频次描述</label>
            <label><input type="checkbox" class="update-field" value="service_fre_json"> 服务频次JSON</label>
        </div>

        <div class="btn-group">
            <button class="btn btn-success" onclick="updateSelected()">
                更新选中的合约
                <span class="loading" id="loading-update">正在更新...</span>
            </button>
        </div>
    </div>

    <div class="result-container" id="result-container" style="display: none;">
        <h3>对比结果</h3>
        <div id="message-area"></div>
        <div id="result-area"></div>
    </div>
</div>

<script>
var compareResults = [];

/**
 * 对比数据
 */
function compareData() {
    var uId = document.getElementById('u_id').value.trim();
    var virCode = document.getElementById('vir_code').value.trim();
    var batchUIds = document.getElementById('batch_u_ids').value.trim();

    if (!uId && !virCode && !batchUIds) {
        alert('请至少输入一个查询条件');
        return;
    }

    var params = {};
    if (batchUIds) {
        params.batch = batchUIds;
    } else if (uId) {
        params.u_id = uId;
    } else if (virCode) {
        params.vir_code = virCode;
    }

    showLoading('compare', true);

    $.ajax({
        url: '<?php echo Yii::app()->createUrl('virtualContractSync/compare'); ?>',
        type: 'GET',
        data: params,
        dataType: 'json',
        success: function(response) {
            showLoading('compare', false);

            if (response.success) {
                compareResults = response.data;
                displayResults(response.data);
                document.getElementById('update-section').style.display = 'block';
            } else {
                showError(response.message);
            }
        },
        error: function() {
            showLoading('compare', false);
            showError('请求失败，请检查网络连接');
        }
    });
}

/**
 * 显示对比结果
 */
function displayResults(data) {
    if (data.length === 0) {
        showError('未找到数据');
        return;
    }

    var html = '<table class="result-table">';
    html += '<thead><tr>';
    html += '<th><input type="checkbox" id="select-all" onclick="toggleAll(this)"></th>';
    html += '<th>虚拟合约ID</th>';
    html += '<th>派单ID</th>';
    html += '<th>虚拟合同编号</th>';
    html += '<th>字段</th>';
    html += '<th>CRM值</th>';
    html += '<th>派单值</th>';
    html += '<th>差异</th>';
    html += '</tr></thead><tbody>';

    data.forEach(function(item) {
        if (item.error) {
            html += '<tr class="diff-row">';
            html += '<td></td>';
            html += '<td>' + (item.vir_id || '-') + '</td>';
            html += '<td>' + (item.u_id || '-') + '</td>';
            html += '<td>' + (item.vir_code || '-') + '</td>';
            html += '<td colspan="4" style="color: red;">' + item.error + '</td>';
            html += '</tr>';
        } else {
            var diffCount = Object.keys(item.differences).length;
            var rowClass = diffCount > 0 ? 'diff-row' : '';

            if (diffCount === 0) {
                html += '<tr class="' + rowClass + '">';
                html += '<td><input type="checkbox" class="vir-checkbox" value="' + item.vir_id + '" data-has-diff="false"></td>';
                html += '<td>' + item.vir_id + '</td>';
                html += '<td>' + item.u_id + '</td>';
                html += '<td>' + item.vir_code + '</td>';
                html += '<td colspan="4" class="same-cell">数据一致，无需更新</td>';
                html += '</tr>';
            } else {
                var firstRow = true;
                for (var field in item.differences) {
                    var diff = item.differences[field];
                    html += '<tr class="' + rowClass + '">';

                    if (firstRow) {
                        html += '<td rowspan="' + diffCount + '"><input type="checkbox" class="vir-checkbox" value="' + item.vir_id + '" data-has-diff="true"></td>';
                        html += '<td rowspan="' + diffCount + '">' + item.vir_id + '</td>';
                        html += '<td rowspan="' + diffCount + '">' + item.u_id + '</td>';
                        html += '<td rowspan="' + diffCount + '">' + item.vir_code + '</td>';
                        firstRow = false;
                    }

                    html += '<td>' + diff.field_name + '</td>';
                    html += '<td class="diff-cell">' + formatValue(diff.crm_value) + '</td>';
                    html += '<td class="same-cell">' + formatValue(diff.paidan_value) + '</td>';
                    html += '<td>' + (diff.diff === true ? '不同' : diff.diff) + '</td>';
                    html += '</tr>';
                }
            }
        }
    });

    html += '</tbody></table>';

    document.getElementById('result-area').innerHTML = html;
    document.getElementById('result-container').style.display = 'block';

    var diffCount = data.filter(function(item) {
        return item.has_diff && !item.error;
    }).length;

    if (diffCount > 0) {
        showMessage('找到 ' + data.length + ' 条记录，其中 ' + diffCount + ' 条有差异', 'success');
    } else {
        showMessage('找到 ' + data.length + ' 条记录，数据全部一致', 'success');
    }
}

/**
 * 格式化值
 */
function formatValue(value) {
    if (value === null || value === undefined) {
        return '-';
    }
    if (typeof value === 'object') {
        return JSON.stringify(value);
    }
    return value;
}

/**
 * 全选/取消全选
 */
function toggleAll(checkbox) {
    var checkboxes = document.querySelectorAll('.vir-checkbox');
    checkboxes.forEach(function(cb) {
        // 只选择有差异的
        if (cb.getAttribute('data-has-diff') === 'true') {
            cb.checked = checkbox.checked;
        }
    });
}

/**
 * 全选/取消全选字段
 */
function toggleAllFields(checkbox) {
    var fieldCheckboxes = document.querySelectorAll('.update-field');
    fieldCheckboxes.forEach(function(cb) {
        cb.checked = checkbox.checked;
    });
}

/**
 * 更新选中的合约
 */
function updateSelected() {
    var selectedVirIds = [];
    document.querySelectorAll('.vir-checkbox:checked').forEach(function(cb) {
        selectedVirIds.push(cb.value);
    });

    if (selectedVirIds.length === 0) {
        alert('请至少选择一个虚拟合约');
        return;
    }

    var selectedFields = [];
    document.querySelectorAll('.update-field:checked').forEach(function(cb) {
        selectedFields.push(cb.value);
    });

    if (selectedFields.length === 0) {
        alert('请至少选择一个要更新的字段');
        return;
    }

    if (!confirm('确定要更新选中的 ' + selectedVirIds.length + ' 个虚拟合约的 ' + selectedFields.length + ' 个字段吗？')) {
        return;
    }

    showLoading('update', true);

    var url = selectedVirIds.length === 1
        ? '<?php echo Yii::app()->createUrl('virtualContractSync/updateSingle'); ?>'
        : '<?php echo Yii::app()->createUrl('virtualContractSync/updateBatch'); ?>';

    var data = selectedVirIds.length === 1
        ? { vir_id: selectedVirIds[0], fields: selectedFields }
        : { vir_ids: selectedVirIds, fields: selectedFields };

    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            showLoading('update', false);

            if (response.success) {
                showMessage(response.message, 'success');

                // 显示详细结果
                if (response.details) {
                    var detailHtml = '<h4>更新详情：</h4><ul>';
                    response.details.forEach(function(detail) {
                        detailHtml += '<li>' + detail.vir_code + ': ';
                        if (detail.success) {
                            detailHtml += '<span style="color: green;">成功</span> - 更新了 ' + detail.updated_fields.join(', ');
                        } else {
                            detailHtml += '<span style="color: red;">失败</span> - ' + detail.message;
                        }
                        detailHtml += '</li>';
                    });
                    detailHtml += '</ul>';
                    document.getElementById('message-area').innerHTML += detailHtml;
                }

                // 刷新数据
                setTimeout(function() {
                    compareData();
                }, 2000);
            } else {
                showError(response.message);
            }
        },
        error: function() {
            showLoading('update', false);
            showError('更新失败，请检查网络连接');
        }
    });
}

/**
 * 清空结果
 */
function clearResults() {
    document.getElementById('u_id').value = '';
    document.getElementById('vir_code').value = '';
    document.getElementById('batch_u_ids').value = '';
    document.getElementById('result-area').innerHTML = '';
    document.getElementById('message-area').innerHTML = '';
    document.getElementById('result-container').style.display = 'none';
    document.getElementById('update-section').style.display = 'none';
    compareResults = [];
}

/**
 * 显示加载状态
 */
function showLoading(type, show) {
    var loadingEl = document.getElementById('loading-' + type);
    if (loadingEl) {
        loadingEl.style.display = show ? 'inline' : 'none';
    }
}

/**
 * 显示消息
 */
function showMessage(message, type) {
    var className = type === 'success' ? 'success-message' : 'error-message';
    document.getElementById('message-area').innerHTML =
        '<div class="' + className + '">' + message + '</div>';
}

/**
 * 显示错误
 */
function showError(message) {
    showMessage(message, 'error');
}
</script>

