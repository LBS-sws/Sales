<?php
/* @var $this VirtualContractCheckController */
/* @var $data array */
/* @var $currentPage int */
/* @var $totalRecords int */
/* @var $pageSize int */
/* @var $totalPages int */

$this->pageTitle = '虚拟合约频次金额检查（仅显示异常）';
?>

<div class="container-fluid">
    <h2>虚拟合约频次金额检查</h2>

    <div class="alert alert-info">
        <strong>⭐ 只显示异常数据：</strong>此页面仅显示服务频次金额与月金额(month_amt)不一致的虚拟合约。<br>
        <strong>检查逻辑：</strong>参考 DataMigrationVirtualContractProcessor::processServiceFrequency 方法。<br>
    </div>



    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p>
                                <strong style="color: red;">异常数据总数：</strong><span style="color: red; font-weight: bold;"><?php echo $totalRecords; ?></span> 条<br>
                                <strong>当前页显示：</strong><?php echo count($data); ?> 条<br>
                                <strong>当前页码：</strong>第 <?php echo $currentPage; ?> 页 / 共 <?php echo $totalPages; ?> 页<br>
                                <strong>每页显示：</strong><?php echo $pageSize; ?> 条
                            </p>
                        </div>
                        <div class="col-md-6 text-right">
                            <button class="btn btn-primary" onclick="syncBatch()" style="margin-top: 10px; margin-right: 10px;">
                                <i class="glyphicon glyphicon-refresh"></i> 批量同步选中
                            </button>
                            <button class="btn btn-warning" onclick="selectAll()" style="margin-top: 10px;">
                                <i class="glyphicon glyphicon-check"></i> 全选/取消
                            </button>
                        </div>
                    </div>

                    <!-- 分页控件 -->
                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-12">
                            <form method="get" class="form-inline">
                                <div class="form-group">
                                    <label>每页显示：</label>
                                    <select name="pageSize" class="form-control" onchange="this.form.submit()">
                                        <option value="20" <?php echo $pageSize == 20 ? 'selected' : ''; ?>>20条</option>
                                        <option value="50" <?php echo $pageSize == 50 ? 'selected' : ''; ?>>50条</option>
                                        <option value="100" <?php echo $pageSize == 100 ? 'selected' : ''; ?>>100条</option>
                                        <option value="200" <?php echo $pageSize == 200 ? 'selected' : ''; ?>>200条</option>
                                    </select>
                                </div>

                                <div class="form-group" style="margin-left: 20px;">
                                    <label>跳转到：</label>
                                    <input type="number" name="page" class="form-control" style="width: 80px;"
                                           value="<?php echo $currentPage; ?>" min="1" max="<?php echo $totalPages; ?>">
                                    <button type="submit" class="btn btn-primary">GO</button>
                                </div>
                            </form>

                            <div style="margin-top: 10px;">
                                <ul class="pagination" style="margin: 0;">
                                    <!-- 首页 -->
                                    <li class="<?php echo $currentPage == 1 ? 'disabled' : ''; ?>">
                                        <a href="<?php echo $this->createUrl('index', array('page' => 1, 'pageSize' => $pageSize)); ?>">首页</a>
                                    </li>

                                    <!-- 上一页 -->
                                    <li class="<?php echo $currentPage == 1 ? 'disabled' : ''; ?>">
                                        <a href="<?php echo $this->createUrl('index', array('page' => max(1, $currentPage - 1), 'pageSize' => $pageSize)); ?>">上一页</a>
                                    </li>

                                    <!-- 页码 -->
                                    <?php
                                    $startPage = max(1, $currentPage - 2);
                                    $endPage = min($totalPages, $currentPage + 2);
                                    for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <li class="<?php echo $i == $currentPage ? 'active' : ''; ?>">
                                            <a href="<?php echo $this->createUrl('index', array('page' => $i, 'pageSize' => $pageSize)); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- 下一页 -->
                                    <li class="<?php echo $currentPage == $totalPages ? 'disabled' : ''; ?>">
                                        <a href="<?php echo $this->createUrl('index', array('page' => min($totalPages, $currentPage + 1), 'pageSize' => $pageSize)); ?>">下一页</a>
                                    </li>

                                    <!-- 末页 -->
                                    <li class="<?php echo $currentPage == $totalPages ? 'disabled' : ''; ?>">
                                        <a href="<?php echo $this->createUrl('index', array('page' => $totalPages, 'pageSize' => $pageSize)); ?>">末页</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($data)): ?>
        <div class="alert alert-success">
            <strong>太棒了！</strong>当前页没有异常数据。
            <?php if ($totalRecords == 0): ?>
                <br><strong>系统中所有虚拟合约的频次金额都是正常的！</strong>
            <?php else: ?>
                <br>可以翻到其他页查看异常数据。
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr style="background-color: #f5f5f5;">
                        <th style="width: 50px;"><input type="checkbox" id="checkAll" onclick="toggleAll(this)"></th>
                        <th style="width: 60px;">序号</th>
                        <th style="width: 80px;">虚拟合约ID</th>
                        <th style="width: 150px;">虚拟合约编号</th>
                        <th style="width: 120px;">客户编号</th>
                        <th style="width: 200px;">客户名称</th>
                        <th style="width: 120px;">门店编号</th>
                        <th style="width: 200px;">门店名称</th>
                        <th style="width: 150px;">服务项目</th>
                        <th style="width: 100px;">频次类型</th>
                        <th style="width: 80px;">服务总次数</th>
                        <th style="width: 100px;">数据库月金额</th>
                        <th style="width: 100px;">计算月金额</th>
                        <th style="width: 80px;">差异金额</th>
                        <th style="width: 100px;">年金额</th>
                        <th style="min-width: 300px;">频次详情(周表)</th>
                        <th style="min-width: 250px;">service_fre_text</th>
                        <th style="min-width: 300px;">service_fre_json</th>
                        <th style="width: 100px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $index => $row): ?>
                        <tr>
                            <td><input type="checkbox" class="vir-check" value="<?php echo $row['id']; ?>"></td>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <a href="<?php echo $this->createUrl('/contractVirtual/view', array('id' => $row['id'])); ?>" target="_blank">
                                    <?php echo isset($row['vir_code']) ? CHtml::encode($row['vir_code']) : 'VIR-' . $row['id']; ?>
                                </a>
                            </td>
                            <td><?php echo isset($row['clue_code']) ? CHtml::encode($row['clue_code']) : '-'; ?></td>
                            <td><?php echo isset($row['clue_name']) ? CHtml::encode($row['clue_name']) : '-'; ?></td>
                            <td><?php echo isset($row['store_code']) ? CHtml::encode($row['store_code']) : '-'; ?></td>
                            <td><?php echo isset($row['store_name']) ? CHtml::encode($row['store_name']) : '-'; ?></td>
                            <td><?php echo isset($row['business_name']) ? CHtml::encode($row['business_name']) : '-'; ?></td>
                            <td><?php echo isset($row['service_fre_type']) ? CHtml::encode($row['service_fre_type']) : '-'; ?></td>
                            <td><?php echo isset($row['service_sum']) ? $row['service_sum'] : 0; ?></td>
                            <td style="text-align: right; font-weight: bold;">
                                <?php echo number_format(isset($row['month_amt']) ? $row['month_amt'] : 0, 2); ?>
                            </td>
                            <td style="text-align: right; font-weight: bold; color: blue;">
                                <?php echo number_format(isset($row['calculated_month_amt']) ? $row['calculated_month_amt'] : 0, 2); ?>
                            </td>
                            <td style="text-align: right; font-weight: bold; color: red;">
                                <?php echo number_format(isset($row['diff']) ? $row['diff'] : 0, 2); ?>
                            </td>
                            <td style="text-align: right;">
                                <?php echo number_format(isset($row['year_amt']) ? $row['year_amt'] : 0, 2); ?>
                            </td>
                            <td style="white-space: pre-wrap; font-size: 12px;">
                                <?php echo isset($row['week_detail']) ? CHtml::encode($row['week_detail']) : '-'; ?>
                            </td>
                            <td style="white-space: pre-wrap; font-size: 12px; background-color: #f9f9f9;">
                                <?php echo isset($row['service_fre_text']) ? CHtml::encode($row['service_fre_text']) : '-'; ?>
                            </td>
                            <td style="white-space: pre-wrap; font-size: 11px; background-color: #fffef0;">
                                <?php echo isset($row['service_fre_json_formatted']) ? CHtml::encode($row['service_fre_json_formatted']) : '-'; ?>
                            </td>
                            <td style="text-align: center;">
                                <button class="btn btn-sm btn-primary" onclick="syncOne(<?php echo $row['id']; ?>)">
                                    同步
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 底部分页 -->
        <div style="margin-top: 20px; text-align: center;">
            <ul class="pagination">
                <li class="<?php echo $currentPage == 1 ? 'disabled' : ''; ?>">
                    <a href="<?php echo $this->createUrl('index', array('page' => 1, 'pageSize' => $pageSize)); ?>">首页</a>
                </li>
                <li class="<?php echo $currentPage == 1 ? 'disabled' : ''; ?>">
                    <a href="<?php echo $this->createUrl('index', array('page' => max(1, $currentPage - 1), 'pageSize' => $pageSize)); ?>">上一页</a>
                </li>

                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                    <li class="<?php echo $i == $currentPage ? 'active' : ''; ?>">
                        <a href="<?php echo $this->createUrl('index', array('page' => $i, 'pageSize' => $pageSize)); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <li class="<?php echo $currentPage == $totalPages ? 'disabled' : ''; ?>">
                    <a href="<?php echo $this->createUrl('index', array('page' => min($totalPages, $currentPage + 1), 'pageSize' => $pageSize)); ?>">下一页</a>
                </li>
                <li class="<?php echo $currentPage == $totalPages ? 'disabled' : ''; ?>">
                    <a href="<?php echo $this->createUrl('index', array('page' => $totalPages, 'pageSize' => $pageSize)); ?>">末页</a>
                </li>
            </ul>
        </div>

        <div class="alert alert-warning" style="margin-top: 20px;">
            <strong>⭐ 重要说明：</strong>本页面只显示异常数据！<br><br>
            <strong>字段说明：</strong><br>
            <ul>
                <li><strong>数据库月金额：</strong>sal_contract_virtual 表中的 month_amt 字段</li>
                <li><strong>计算月金额：</strong>根据 sal_contract_vir_week 表中的频次数据实时计算（次数 × 单价的平均值）</li>
                <li><strong>差异金额：</strong>两者的差值（<span style="color: red; font-weight: bold;">只显示差异 > 0.01元 的记录</span>）</li>
                <li><strong>频次详情(周表)：</strong>从 sal_contract_vir_week 表计算得出，显示每个月份的服务次数、单价和月金额</li>
                <li><strong>service_fre_text：</strong>虚拟合约的频次描述文字（存储在主表）</li>
                <li><strong>service_fre_json：</strong>虚拟合约的频次详细配置（JSON格式，存储在主表），包含总金额、总次数、频次列表等信息</li>
            </ul>
            <p style="margin-top: 10px; color: #856404;">
                <strong>对比说明：</strong>频次详情(周表) 是从 sal_contract_vir_week 表实时计算的；service_fre_json 是存储在主表的冗余字段。两者应该保持一致。
            </p>
            <p style="margin-top: 10px; color: #0066cc; font-weight: bold;">
                💡 如果当前页没有数据，说明这一范围的虚拟合约都是正常的！
            </p>
        </div>
    <?php endif; ?>
</div>

<style>
    .table-responsive {
        overflow-x: auto;
    }

    .table th {
        white-space: nowrap;
        font-weight: bold;
        text-align: center;
    }

    .table td {
        vertical-align: middle;
    }

    .alert {
        margin-bottom: 15px;
    }

    .btn-sm {
        padding: 3px 8px;
        font-size: 12px;
    }
</style>

<script>
// 全选/取消全选
function toggleAll(checkbox) {
    var checks = document.getElementsByClassName('vir-check');
    for (var i = 0; i < checks.length; i++) {
        checks[i].checked = checkbox.checked;
    }
}

// 全选按钮（顶部）
function selectAll() {
    var checkAll = document.getElementById('checkAll');
    checkAll.checked = !checkAll.checked;
    toggleAll(checkAll);
}

// 单个同步
function syncOne(virId) {
    if (!confirm('确定要从派单同步这条数据吗？')) {
        return;
    }

    $.ajax({
        url: '<?php echo $this->createUrl("syncOne"); ?>',
        type: 'POST',
        data: {vir_id: virId},
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                alert('同步成功！页面即将刷新...');
                // 强制刷新，避免缓存
                location.href = location.href + (location.href.indexOf('?') > -1 ? '&' : '?') + '_t=' + new Date().getTime();
            } else {
                alert('同步失败：' + res.msg);
            }
        },
        error: function() {
            alert('请求失败，请检查网络');
        }
    });
}

// 批量同步
function syncBatch() {
    var checks = document.getElementsByClassName('vir-check');
    var ids = [];

    for (var i = 0; i < checks.length; i++) {
        if (checks[i].checked) {
            ids.push(checks[i].value);
        }
    }

    if (ids.length == 0) {
        alert('请先选择要同步的数据');
        return;
    }

    if (!confirm('确定要同步选中的 ' + ids.length + ' 条数据吗？')) {
        return;
    }

    $.ajax({
        url: '<?php echo $this->createUrl("syncBatch"); ?>',
        type: 'POST',
        data: {vir_ids: ids},
        dataType: 'json',
        success: function(res) {
            alert(res.msg + (res.success ? '\n页面即将刷新...' : ''));
            if (res.success) {
                // 强制刷新，避免缓存
                location.href = location.href + (location.href.indexOf('?') > -1 ? '&' : '?') + '_t=' + new Date().getTime();
            }
        },
        error: function() {
            alert('请求失败，请检查网络');
        }
    });
}
</script>
