<?php
$this->pageTitle=Yii::app()->name . ' - 数据迁移任务列表';
?>

<style>
.task-list-container {
    padding: 15px;
}
.task-item {
    padding: 15px;
    margin-bottom: 15px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
}
.task-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.task-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.task-stats {
    display: flex;
    gap: 20px;
    margin-top: 10px;
}
.task-stat-item {
    font-size: 14px;
}
.task-stat-item strong {
    font-size: 18px;
    margin-left: 5px;
}
.filter-panel {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}
</style>

<section class="content-header">
    <h1>
        <strong>数据迁移任务列表</strong>
        <button type="button" id="btn-clear-cache-top" class="btn btn-danger btn-sm" style="margin-left: 15px;" title="清除所有缓存（PHP OPcache + 数据缓存）">
            <i class="fa fa-trash"></i> 清除缓存
        </button>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo Yii::app()->createUrl('site/index'); ?>"><i class="fa fa-dashboard"></i> 首页</a></li>
        <li><a href="<?php echo Yii::app()->createUrl('dataMigration/index'); ?>">数据迁移</a></li>
        <li class="active">任务列表</li>
    </ol>
</section>

<section class="content">
    <div class="box box-info">
        <div class="box-body task-list-container">
            <!-- 筛选面板 -->
            <div class="filter-panel">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>任务类型：</label>
                            <select id="filter-type" class="form-control">
                                <option value="">全部</option>
                                <option value="client">客户</option>
                                <option value="clientStore">门店</option>
                                <option value="cont">主合约</option>
                                <option value="vir">虚拟合约</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>任务状态：</label>
                            <select id="filter-status" class="form-control">
                                <option value="">全部</option>
                                <option value="0">待处理</option>
                                <option value="1">处理中</option>
                                <option value="2">已完成</option>
                                <option value="3">失败</option>
                                <option value="4">已取消</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>搜索关键词：</label>
                            <input type="text" id="filter-keyword" class="form-control" placeholder="任务编号、备注...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" id="btn-search" class="btn btn-primary btn-block">
                                    <i class="fa fa-search"></i> 搜索
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" id="btn-refresh" class="btn btn-default">
                            <i class="fa fa-refresh"></i> 刷新
                        </button>
                        <button type="button" id="btn-clear-filter" class="btn btn-default">
                            <i class="fa fa-remove"></i> 清除筛选
                        </button>
                        <button type="button" id="btn-clear-cache" class="btn btn-danger">
                            <i class="fa fa-trash"></i> 清除PHP缓存
                        </button>
                        <?php if (Yii::app()->params['envSuffix'] === 'uat'): ?>
                        <button type="button" id="btn-super-remove" class="btn btn-danger" style="background-color: #8b0000; border-color: #700000;" title="仅UAT环境可用">
                            <i class="fa fa-exclamation-triangle"></i> 超级删除（全部导入数据）[UAT专用]
                        </button>
                        <?php endif; ?>
                        <a href="<?php echo Yii::app()->createUrl('dataMigration/debug'); ?>" class="btn btn-warning">
                            <i class="fa fa-bug"></i> 调试工具
                        </a>
                        <a href="<?php echo Yii::app()->createUrl('dataMigration/index'); ?>" class="btn btn-success pull-right">
                            <i class="fa fa-plus"></i> 创建新任务
                        </a>
                    </div>
                </div>
            </div>

            <!-- 自动刷新指示器 -->
            <div id="auto-refresh-indicator" class="alert alert-info" style="display: none; margin-bottom: 15px; padding: 10px;">
                <i class="fa fa-refresh fa-spin"></i>
                <strong>自动刷新已启用</strong> - 检测到有任务正在处理中，页面将每 5 秒自动更新一次进度
                <button type="button" class="btn btn-xs btn-default pull-right" onclick="stopAutoRefresh(); $('#auto-refresh-indicator').hide();">
                    <i class="fa fa-stop"></i> 停止自动刷新
                </button>
            </div>

            <!-- 统计面板 -->
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-2 col-sm-4 col-xs-6">
                    <div class="small-box bg-aqua">
                        <div class="inner">
                            <h3 id="stat-total">0</h3>
                            <p>总任务数</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-tasks"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-xs-6">
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h3 id="stat-processing">0</h3>
                            <p>处理中</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-spinner"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-xs-6">
                    <div class="small-box bg-green">
                        <div class="inner">
                            <h3 id="stat-completed">0</h3>
                            <p>已完成</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-check"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-xs-6">
                    <div class="small-box bg-red">
                        <div class="inner">
                            <h3 id="stat-failed">0</h3>
                            <p>失败</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-times"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-xs-6">
                    <div class="small-box bg-gray">
                        <div class="inner">
                            <h3 id="stat-pending">0</h3>
                            <p>待处理</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-clock-o"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-xs-6">
                    <div class="small-box bg-purple">
                        <div class="inner">
                            <h3 id="stat-cancelled">0</h3>
                            <p>已取消</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-ban"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 任务列表 -->
            <div id="task-list-container">
                <div class="text-center" style="padding: 50px;">
                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                    <p style="margin-top: 15px;">加载中...</p>
                </div>
            </div>

            <!-- 分页 -->
            <div id="pagination-container" style="text-align: center; margin-top: 15px;"></div>
        </div>
    </div>
</section>

<!-- 任务详情模态框 -->
<div class="modal fade" id="task-detail-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-info-circle"></i> 任务详情
                </h4>
            </div>
            <div class="modal-body" id="task-detail-content">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin"></i> 加载中...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<!-- 失败记录模态框 -->
<div class="modal fade" id="failed-records-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-exclamation-triangle"></i> 失败记录详情
                </h4>
            </div>
            <div class="modal-body">
                <div id="failed-records-content">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin"></i> 加载中...
                    </div>
                </div>
                <div class="text-right" style="margin-top: 15px;">
                    <button type="button" class="btn btn-warning" id="btn-retry-failed-in-modal">
                        <i class="fa fa-refresh"></i> 重新处理失败记录
                    </button>
                    <button type="button" class="btn btn-info" id="btn-reset-failed-in-modal">
                        <i class="fa fa-repeat"></i> 重置失败状态
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<!-- 通用消息模态框 -->
<div class="modal fade" id="message-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" id="message-modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i id="message-modal-icon" class="fa fa-info-circle"></i>
                    <span id="message-modal-title-text">提示</span>
                </h4>
            </div>
            <div class="modal-body" id="message-modal-body">
                <!-- 消息内容 -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="message-modal-btn-cancel" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="message-modal-btn-ok" data-dismiss="modal">确定</button>
            </div>
        </div>
    </div>
</div>

<?php
$getTaskListUrl = Yii::app()->createUrl('dataMigration/getTaskList');
$getTaskDetailsUrl = Yii::app()->createUrl('dataMigration/getTaskDetails');
$retryFailedUrl = Yii::app()->createUrl('dataMigration/retryFailed');
$resetFailedUrl = Yii::app()->createUrl('dataMigration/resetFailed');
$previewUrl = Yii::app()->createUrl('dataMigration/previewData');
$clearCacheUrl = Yii::app()->createUrl('dataMigration/clearCache');
$deleteDataUrl = Yii::app()->createUrl('iqueueNew/remove');
$superRemoveUrl = Yii::app()->createUrl('iqueueNew/superRemove');

$js = <<<JS
// ========== 全局消息提示函数 ==========
function showMessage(message, type, title) {
    type = type || 'info';
    var iconClass = {
        'success': 'fa-check-circle',
        'error': 'fa-times-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    }[type] || 'fa-info-circle';
    
    var headerClass = {
        'success': 'bg-success',
        'error': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info'
    }[type] || 'bg-info';
    
    title = title || {
        'success': '成功',
        'error': '错误',
        'warning': '警告',
        'info': '提示'
    }[type];
    
    $('#message-modal-icon').attr('class', 'fa ' + iconClass);
    $('#message-modal-title-text').text(title);
    $('#message-modal-header').attr('class', 'modal-header ' + headerClass);
    $('#message-modal-body').html(message.split('\\n').join('<br>'));
    $('#message-modal-btn-cancel').hide();
    $('#message-modal-btn-ok').off('click').one('click', function() {
        $('#message-modal').modal('hide');
    });
    $('#message-modal').modal('show');
}

var currentPage = 1;
var pageSize = 10;
var totalPages = 1;
var currentLogId = null; // 用于失败记录模态框


// 加载任务列表
window.loadTaskList = function(page) {
    page = page || 1;
    currentPage = page;
    
    var filter = {
        type: $('#filter-type').val(),
        status: $('#filter-status').val(),
        keyword: $('#filter-keyword').val(),
        page: page,
        page_size: pageSize
    };
    
    $.ajax({
        url: '$getTaskListUrl',
        type: 'GET',
        data: filter,
        dataType: 'json',
        success: function(response) {
            if (response.status == 1) {
                renderTaskList(response.data.list);
                renderPagination(response.data.pagination);
                updateStats(response.data.stats);
            } else {
                $('#task-list-container').html('<div class="alert alert-danger">加载失败：' + (response.message || '未知错误') + '</div>');
            }
        },
        error: function(xhr, status, error) {
            $('#task-list-container').html('<div class="alert alert-danger">请求失败：' + error + '</div>');
        }
    });
}

// 渲染任务列表
function renderTaskList(tasks) {
    var container = $('#task-list-container');
    container.empty();
    
    if (!tasks || tasks.length === 0) {
        container.html('<div class="alert alert-info text-center">暂无任务记录</div>');
        return;
    }
    
    var typeMap = {
        'client': '客户',
        'clientStore': '门店',
        'cont': '主合约',
        'vir': '虚拟合约'
    };
    
    var statusMap = {
        0: { text: '待处理', class: 'default' },
        1: { text: '处理中', class: 'warning' },
        2: { text: '已完成', class: 'success' },
        3: { text: '失败', class: 'danger' },
        4: { text: '已取消', class: 'default' }
    };
    
    tasks.forEach(function(task) {
        var status = statusMap[task.task_status] || { text: '未知', class: 'default' };
        var progressPercent = task.progress || 0;
        
        var html = '<div class="task-item">';
        html += '<div class="task-header">';
        html += '<div>';
        html += '<h4 style="margin: 0;">';
        html += '<span class="label label-primary">' + (typeMap[task.migration_type] || task.migration_type) + '</span> ';
        html += '<strong>' + (task.task_code || '未知') + '</strong>';
        html += '</h4>';
        html += '<small style="color: #999;">创建时间：' + (task.created_at || '-') + '</small>';
        html += '</div>';
        html += '<div>';
        html += '<span class="label label-' + status.class + '" style="font-size: 14px; padding: 6px 12px;">' + status.text + '</span>';
        html += '</div>';
        html += '</div>';
        
        // 进度条
        if (task.task_status == 1 || task.task_status == 2) {
            html += '<div class="progress" style="margin-top: 10px; margin-bottom: 10px;">';
            html += '<div class="progress-bar progress-bar-' + (task.task_status == 2 ? 'success' : 'info') + '" style="width: ' + progressPercent + '%;">';
            html += progressPercent + '%';
            html += '</div>';
            html += '</div>';
        }
        
        // 统计信息
        html += '<div class="task-stats">';
        html += '<div class="task-stat-item"><i class="fa fa-map-marker"></i> 城市数：<strong>' + (task.total_cities || 0) + '</strong></div>';
        html += '<div class="task-stat-item"><i class="fa fa-check-circle" style="color: #00a65a;"></i> 成功：<strong style="color: #00a65a;">' + (task.success_count || 0) + '</strong></div>';
        html += '<div class="task-stat-item"><i class="fa fa-times-circle" style="color: #dd4b39;"></i> 失败：<strong style="color: #dd4b39;">' + (task.error_count || 0) + '</strong></div>';
        if (task.task_status == 2 && task.end_time) {
            var startTime = new Date(task.start_time);
            var endTime = new Date(task.end_time);
            var duration = Math.round((endTime - startTime) / 1000);
            html += '<div class="task-stat-item"><i class="fa fa-clock-o"></i> 耗时：<strong>' + formatDuration(duration) + '</strong></div>';
        }
        html += '</div>';
        
        // 城市阶段进度（直接在列表中展示）
        if (task.city_details && task.city_details.length > 0) {
            html += '<div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">';
            html += '<h5 style="margin-bottom: 10px; color: #666;"><i class="fa fa-list"></i> 各城市处理进度</h5>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-condensed table-bordered" style="margin-bottom: 0; font-size: 12px;">';
            html += '<thead style="background-color: #f9f9f9;">';
            html += '<tr>';
            html += '<th style="width: 150px;">城市</th>';
            html += '<th style="width: 80px; text-align: center;">状态</th>';
            html += '<th style="width: 80px; text-align: center;">成功</th>';
            html += '<th style="width: 80px; text-align: center;">失败</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';
            
            var cityStatusMap = {
                0: { text: '待处理', class: 'label-default' },
                1: { text: '处理中', class: 'label-warning' },
                2: { text: '完成', class: 'label-success' },
                3: { text: '失败', class: 'label-danger' }
            };
            
            task.city_details.forEach(function(city) {
                var cityStatus = cityStatusMap[city.status] || { text: '未知', class: 'label-default' };
                html += '<tr>';
                html += '<td><strong>' + city.city_name + '</strong>';
                
                // 如果正在处理中，显示实时进度
                if (city.status == 1 && city.total_records > 0) {
                    var processedRecords = city.processed_records || 0;
                    var totalRecords = city.total_records || 1;
                    var cityProgress = Math.round((processedRecords / totalRecords) * 100);
                    html += '<div class="progress" style="height: 5px; margin: 5px 0 0 0;">';
                    html += '<div class="progress-bar progress-bar-info progress-bar-striped active" style="width: ' + cityProgress + '%"></div>';
                    html += '</div>';
                    html += '<small style="color: #999;">' + processedRecords + '/' + totalRecords + ' (' + cityProgress + '%)</small>';
                }
                
                html += '</td>';
                html += '<td style="text-align: center;"><span class="label ' + cityStatus.class + '">' + cityStatus.text + '</span></td>';
                html += '<td style="text-align: center;"><span class="text-success"><strong>' + (city.success_count || 0) + '</strong></span></td>';
                html += '<td style="text-align: center;"><span class="text-danger"><strong>' + (city.error_count || 0) + '</strong></span></td>';
                html += '</tr>';
            });
            
            html += '</tbody>';
            html += '</table>';
            html += '</div>';
            
            if (task.total_cities > task.city_details.length) {
                html += '<div class="text-center" style="margin-top: 5px; color: #999; font-size: 11px;">';
                html += '（仅显示前 ' + task.city_details.length + ' 个城市，共 ' + task.total_cities + ' 个城市，点击"查看详情"查看全部）';
                html += '</div>';
            }
            
            html += '</div>';
        }
        
        // 操作按钮
        html += '<div style="margin-top: 15px;">';
        var taskId = task.id || task.task_id; // 兼容写法
        html += '<button class="btn btn-sm btn-info" onclick="viewTaskDetail(' + taskId + ')"><i class="fa fa-info-circle"></i> 查看详情</button> ';
        
        // 如果有失败记录，显示"查看失败记录"和"重新处理失败记录"按钮
        if (task.error_count > 0 && task.last_log_id) {
            html += '<button class="btn btn-sm btn-warning" onclick="viewFailedRecords(' + task.last_log_id + ')"><i class="fa fa-exclamation-triangle"></i> 查看失败记录</button> ';
            html += '<button class="btn btn-sm btn-primary" onclick="retryFailedRecords(' + task.last_log_id + ')" style="background-color: #f39c12; border-color: #e08e0b;"><i class="fa fa-refresh"></i> 重新处理失败记录</button> ';
        }
        
        // 如果任务失败或已完成，显示"重新拉取数据并导入"按钮
        if (task.task_status == 2 || task.task_status == 3) {
            html += '<button class="btn btn-sm btn-success" onclick="retryTask(' + taskId + ')" title="重新从派单系统拉取最新数据并导入"><i class="fa fa-repeat"></i> 重新拉取并导入</button> ';
        }
        
        // 删除按钮（仅当有last_log_id时显示）
        if (task.last_log_id) {
            html += '<button class="btn btn-sm btn-danger" onclick="deleteTaskData(' + task.last_log_id + ', \'' + task.task_code + '\', \'' + (typeMap[task.migration_type] || task.migration_type) + '\')" title="删除该任务导入的所有数据"><i class="fa fa-trash"></i> 删除导入数据</button>';
        }
        html += '</div>';
        
        html += '</div>';
        container.append(html);
    });
}

// 格式化时长
function formatDuration(seconds) {
    if (seconds < 60) return seconds + 's';
    if (seconds < 3600) return Math.floor(seconds / 60) + 'm' + (seconds % 60) + 's';
    return Math.floor(seconds / 3600) + 'h' + Math.floor((seconds % 3600) / 60) + 'm';
}

// 渲染分页
function renderPagination(pagination) {
    if (!pagination) return;
    
    totalPages = pagination.total_pages || 1;
    currentPage = pagination.current_page || 1;
    
    var html = '';
    if (totalPages > 1) {
        html += '<ul class="pagination">';
        
        if (currentPage > 1) {
            html += '<li><a href="javascript:void(0);" onclick="loadTaskList(' + (currentPage - 1) + ')">上一页</a></li>';
        }
        
        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, currentPage + 2);
        
        if (startPage > 1) {
            html += '<li><a href="javascript:void(0);" onclick="loadTaskList(1)">1</a></li>';
            if (startPage > 2) html += '<li class="disabled"><span>...</span></li>';
        }
        
        for (var i = startPage; i <= endPage; i++) {
            if (i == currentPage) {
                html += '<li class="active"><span>' + i + '</span></li>';
            } else {
                html += '<li><a href="javascript:void(0);" onclick="loadTaskList(' + i + ')">' + i + '</a></li>';
            }
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += '<li class="disabled"><span>...</span></li>';
            html += '<li><a href="javascript:void(0);" onclick="loadTaskList(' + totalPages + ')">' + totalPages + '</a></li>';
        }
        
        if (currentPage < totalPages) {
            html += '<li><a href="javascript:void(0);" onclick="loadTaskList(' + (currentPage + 1) + ')">下一页</a></li>';
        }
        
        html += '</ul>';
    }
    
    $('#pagination-container').html(html);
}

// 更新统计
function updateStats(stats) {
    if (!stats) return;
    $('#stat-total').text(stats.total || 0);
    $('#stat-processing').text(stats.processing || 0);
    $('#stat-completed').text(stats.completed || 0);
    $('#stat-failed').text(stats.failed || 0);
    $('#stat-pending').text(stats.pending || 0);
    $('#stat-cancelled').text(stats.cancelled || 0);
}

// 全局变量：任务详情分页
var currentTaskId = null;
var currentTaskPage = 1;
var totalTaskPages = 1;

// 查看任务详情
window.viewTaskDetail = function(taskId, page) {
    page = page || 1;
    currentTaskId = taskId;
    currentTaskPage = page;
    
    $('#task-detail-modal').modal('show');
    $('#task-detail-content').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>');
    
    $.ajax({
        url: '$getTaskDetailsUrl',
        type: 'GET',
        data: { 
            task_id: taskId,
            page: page,
            page_size: 10
        },
        dataType: 'json',
        success: function(response) {
            if (response.status == 1) {
                totalTaskPages = response.total_pages || 1;
                //  修复：从 response.data.details 获取城市列表
                var details = (response.data && Array.isArray(response.data.details)) ? response.data.details : [];
                var failedRecords = response.failed_records || [];
                var totalFailed = response.total_failed || 0;
                var currentPage = response.current_page || 1;
                var totalPages = response.total_pages || 1;
                //  任务信息从 response.data 获取
                var taskInfo = response.data || null;
                
                renderTaskDetail(details, failedRecords, totalFailed, currentPage, totalPages, taskInfo);
            } else {
                $('#task-detail-content').html('<div class="alert alert-danger">加载失败：' + (response.message || '未知错误') + '</div>');
            }
        },
        error: function(xhr, status, error) {
            $('#task-detail-content').html('<div class="alert alert-danger">请求失败：' + error + '</div>');
        }
    });
}

// 渲染任务详情
function renderTaskDetail(details, failedRecords, totalFailed, currentPage, totalPages, taskInfo) {
    //  安全检查：确保 details 是数组
    if (!Array.isArray(details)) {
        console.error('renderTaskDetail: details 不是数组', details);
        details = [];
    }
    if (!Array.isArray(failedRecords)) {
        failedRecords = [];
    }
    
    var html = '';
    
    // 1. 任务总体进度汇总
    if (taskInfo) {
        html += '<div class="row" style="margin-bottom: 20px;">';
        html += '<div class="col-md-12">';
        html += '<div class="info-box bg-gray-light" style="border: 1px solid #ddd; box-shadow: none;">';
        html += '<span class="info-box-icon bg-aqua"><i class="fa fa-tasks"></i></span>';
        html += '<div class="info-box-content">';
        html += '<span class="info-box-text">任务编号: <strong>' + taskInfo.task_code + '</strong> | 总体进度</span>';
        html += '<div class="progress" style="height: 10px; margin: 5px 0;">';
        html += '<div class="progress-bar progress-bar-aqua" style="width: ' + taskInfo.progress + '%"></div>';
        html += '</div>';
        html += '<span class="progress-description">';
        html += '城市进度: <strong>' + taskInfo.completed_cities + '</strong> / ' + taskInfo.total_cities + ' (' + taskInfo.progress + '%) | ';
        html += '成功: <strong class="text-success">' + taskInfo.success_count + '</strong> | ';
        html += '失败: <strong class="text-danger">' + taskInfo.error_count + '</strong>';
        html += '</span>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
    }

    html += '<h4 style="margin-bottom: 15px;"><i class="fa fa-map-marker"></i> 各城市处理进度</h4>';
    html += '<div class="table-responsive">';
    html += '<table class="table table-bordered table-striped table-condensed">';
    html += '<thead style="background: #f4f4f4;"><tr><th>城市</th><th>状态</th><th>进度详情</th><th>开始时间</th><th>结束时间</th><th>耗时</th></tr></thead>';
    html += '<tbody>';
    
    if (details.length === 0) {
        html += '<tr><td colspan="6" class="text-center text-muted">暂无城市详情数据</td></tr>';
    } else {
        var statusMap = {
            0: { text: '待处理', class: 'label-default' },
            1: { text: '处理中', class: 'label-warning' },
            2: { text: '成功', class: 'label-success' },
            3: { text: '失败', class: 'label-danger' }
        };
        
        details.forEach(function(item) {
            var status = statusMap[item.status] || { text: '未知', class: 'label-default' };
            var elapsed = '-';
            if (item.start_time && item.end_time) {
                var start = new Date(item.start_time);
                var end = new Date(item.end_time);
                elapsed = formatDuration(Math.round((end - start) / 1000));
            }
            
            html += '<tr>';
            html += '<td style="vertical-align: middle;"><strong>' + (item.city_name || '-') + '</strong> <small class="text-muted">(' + item.city_code + ')</small></td>';
            html += '<td style="vertical-align: middle;"><span class="label ' + status.class + '">' + status.text + '</span></td>';
            html += '<td style="vertical-align: middle;">';
            
            // 显示进度信息
            if (item.status == 1 && item.total_records > 0) {
                // 处理中：显示实时进度
                var processedRecords = item.processed_records || 0;
                var totalRecords = item.total_records || 1;
                var detailProgress = Math.round((processedRecords / totalRecords) * 100);
                html += '<div style="margin-bottom: 3px;">进度: <strong>' + processedRecords + '</strong>/' + totalRecords + ' (' + detailProgress + '%)</div>';
            }
            
            html += '<span class="text-success" title="成功"><strong>' + (item.success_count || 0) + '</strong></span> / ';
            html += '<span class="text-danger" title="失败"><strong>' + (item.error_count || 0) + '</strong></span>';
            if (item.status == 3 && item.error_message) {
                html += ' <i class="fa fa-info-circle text-danger" title="' + item.error_message + '"></i>';
            }
            html += '</td>';
            html += '<td style="font-size: 11px; vertical-align: middle;">' + (item.start_time ? item.start_time.substring(11, 19) : '-') + '</td>';
            html += '<td style="font-size: 11px; vertical-align: middle;">' + (item.end_time ? item.end_time.substring(11, 19) : '-') + '</td>';
            html += '<td style="vertical-align: middle;">' + elapsed + '</td>';
            html += '</tr>';
        });
    }
    html += '</tbody></table>';
    html += '</div>';
    
    // 显示失败记录明细
    if (totalFailed > 0 && failedRecords && failedRecords.length > 0) {
        html += '<hr style="margin: 20px 0;">';
        html += '<h4 style="margin-bottom: 15px;"><i class="fa fa-exclamation-triangle text-danger"></i> 失败记录明细 <span class="badge badge-danger">' + totalFailed + '</span></h4>';
        
        html += '<table class="table table-bordered table-hover">';
        html += '<thead>';
        html += '<tr style="background-color: #f5f5f5;">';
        html += '<th style="width: 50px;">ID</th>';
        html += '<th style="width: 80px;">城市</th>';
        html += '<th style="width: 100px;">类型</th>';
        html += '<th>原始数据（部分字段）</th>';
        html += '<th style="width: 250px;">错误原因</th>';
        html += '<th style="width: 120px;">导入时间</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';
        
        var typeNames = {
            'client': '客户',
            'clientStore': '门店',
            'cont': '合约',
            'vir': '虚拟合约'
        };
        
        failedRecords.forEach(function(record) {
            html += '<tr>';
            html += '<td>' + record.id + '</td>';
            
            // 从 source_data_parsed 中提取城市名称
            var cityDisplay = record.city || '-';
            if (record.source_data_parsed && record.source_data_parsed['城市']) {
                cityDisplay = record.source_data_parsed['城市'];
            }
            html += '<td>' + cityDisplay + '</td>';
            
            html += '<td><span class="label label-info">' + (typeNames[record.migration_type] || record.migration_type) + '</span></td>';
            
            // 解析并显示关键字段
            html += '<td style="font-size: 11px;">';
            if (record.source_data_parsed) {
                var keyFields = [];
                if (record.source_data_parsed['客户名称']) keyFields.push('名称: ' + record.source_data_parsed['客户名称']);
                if (record.source_data_parsed['门店编号']) keyFields.push('编号: ' + record.source_data_parsed['门店编号']);
                if (record.source_data_parsed['合约编号']) keyFields.push('编号: ' + record.source_data_parsed['合约编号']);
                if (record.source_data_parsed['虚拟合约编号']) keyFields.push('编号: ' + record.source_data_parsed['虚拟合约编号']);
                if (record.source_data_parsed['客户编号']) keyFields.push('客户: ' + record.source_data_parsed['客户编号']);
                
                if (keyFields.length > 0) {
                    html += keyFields.join(', ');
                } else {
                    html += '<span class="text-muted">（查看完整数据）</span>';
                }
            } else {
                html += '-';
            }
            html += '</td>';
            
            // 错误信息
            html += '<td><span style="color: #dd4b39; font-size: 11px;">' + (record.error_message || '-') + '</span></td>';
            html += '<td style="font-size: 11px;">' + (record.created_at || '-') + '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        
        // 分页
        if (totalPages > 1) {
            html += '<div class="text-center" style="margin-top: 15px;">';
            html += '<ul class="pagination pagination-sm" style="margin: 0;">';
            
            // 上一页
            if (currentPage > 1) {
                html += '<li><a href="#" onclick="viewTaskDetail(' + currentTaskId + ', ' + (currentPage - 1) + '); return false;">上一页</a></li>';
            } else {
                html += '<li class="disabled"><span>上一页</span></li>';
            }
            
            // 页码
            var startPage = Math.max(1, currentPage - 2);
            var endPage = Math.min(totalPages, currentPage + 2);
            
            if (startPage > 1) {
                html += '<li><a href="#" onclick="viewTaskDetail(' + currentTaskId + ', 1); return false;">1</a></li>';
                if (startPage > 2) {
                    html += '<li class="disabled"><span>...</span></li>';
                }
            }
            
            for (var i = startPage; i <= endPage; i++) {
                if (i == currentPage) {
                    html += '<li class="active"><span>' + i + '</span></li>';
                } else {
                    html += '<li><a href="#" onclick="viewTaskDetail(' + currentTaskId + ', ' + i + '); return false;">' + i + '</a></li>';
                }
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += '<li class="disabled"><span>...</span></li>';
                }
                html += '<li><a href="#" onclick="viewTaskDetail(' + currentTaskId + ', ' + totalPages + '); return false;">' + totalPages + '</a></li>';
            }
            
            // 下一页
            if (currentPage < totalPages) {
                html += '<li><a href="#" onclick="viewTaskDetail(' + currentTaskId + ', ' + (currentPage + 1) + '); return false;">下一页</a></li>';
            } else {
                html += '<li class="disabled"><span>下一页</span></li>';
            }
            
            html += '</ul>';
            html += '<p style="margin-top: 10px; color: #666;">第 ' + currentPage + ' / ' + totalPages + ' 页，共 ' + totalFailed + ' 条失败记录</p>';
            html += '</div>';
        }
    } else if (totalFailed == 0) {
        html += '<div class="alert alert-success" style="margin-top: 15px;">';
        html += '<i class="fa fa-check-circle"></i> <strong>所有记录均导入成功！</strong>';
        html += '</div>';
    }
    
    $('#task-detail-content').html(html);
}

// 全局变量：失败记录分页
var failedRecordsPage = 1;
var failedRecordsPageSize = 10;  // 每页显示10条
var failedRecordsTotalPages = 1;
var failedRecordsTotalCount = 0;

// 查看失败记录
window.viewFailedRecords = function(logId, page) {
    page = page || 1;
    failedRecordsPage = page;
    currentLogId = logId;
    
    $('#failed-records-modal').modal('show');
    $('#failed-records-content').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>');
    
    $.ajax({
        url: '$previewUrl',
        type: 'GET',
        data: {
            log_id: logId,
            status: 'E',
            page: page,
            page_size: failedRecordsPageSize
        },
        dataType: 'json',
        success: function(response) {
            if (response.status == 1) {
                failedRecordsTotalPages = response.data.total_pages || 1;
                failedRecordsTotalCount = response.data.total_count || 0;
                renderFailedRecords(response.data);
            } else {
                $('#failed-records-content').html('<div class="alert alert-danger">加载失败：' + (response.message || '未知错误') + '</div>');
            }
        },
        error: function(xhr, status, error) {
            $('#failed-records-content').html('<div class="alert alert-danger">请求失败：' + error + '</div>');
        }
    });
}

// 渲染失败记录
function renderFailedRecords(data) {
    // 显示统计信息
    var statsHtml = '<div class="alert alert-info" style="margin-bottom: 15px;">';
    statsHtml += '<strong><i class="fa fa-exclamation-triangle"></i> 失败记录统计：</strong> ';
    statsHtml += '总计 <strong style="color: #dd4b39; font-size: 16px;">' + failedRecordsTotalCount + '</strong> 条失败记录';
    if (failedRecordsTotalPages > 1) {
        statsHtml += ' | 当前第 <strong>' + failedRecordsPage + '</strong> 页，共 <strong>' + failedRecordsTotalPages + '</strong> 页';
        statsHtml += ' | 每页显示 <strong>' + failedRecordsPageSize + '</strong> 条';
    }
    statsHtml += '</div>';
    
    var html = statsHtml;
    
    // 表格
    html += '<div class="table-responsive">';
    html += '<table class="table table-bordered table-striped table-hover">';
    html += '<thead><tr>';
    html += '<th style="width: 60px;">行号</th>';
    
    if (data.headers && data.headers.length > 0) {
        data.headers.slice(0, 4).forEach(function(header) {
            html += '<th>' + header + '</th>';
        });
    }
    
    html += '<th style="min-width: 200px;">错误信息</th>';
    html += '<th style="width: 120px;">操作</th>';
    html += '</tr></thead>';
    html += '<tbody>';
    
    if (!data.rows || data.rows.length === 0) {
        var colspan = 7 + (data.headers ? data.headers.slice(0, 4).length : 0);
        html += '<tr><td colspan="' + colspan + '" class="text-center text-muted">暂无失败记录</td></tr>';
    } else {
        data.rows.forEach(function(row) {
            html += '<tr>';
            html += '<td><strong>' + row.row_index + '</strong></td>';
            
            if (data.headers && data.headers.length > 0) {
                data.headers.slice(0, 4).forEach(function(header) {
                    var value = row.data[header] || '-';
                    if (value.length > 30) {
                        value = '<span title="' + value + '">' + value.substring(0, 30) + '...</span>';
                    }
                    html += '<td>' + value + '</td>';
                });
            }
            
            html += '<td><span style="color: #dd4b39;" title="' + (row.error_message || '') + '">' + (row.error_message || '-') + '</span></td>';
            html += '<td>';
            html += '<button class="btn btn-xs btn-default" onclick="retrySingleRecord(' + row.id + ', false)" title="使用现有数据重新处理">';
            html += '<i class="fa fa-refresh"></i> 重试';
            html += '</button> ';
            html += '<button class="btn btn-xs btn-primary" onclick="retrySingleRecord(' + row.id + ', true)" title="重新从派单系统拉取最新数据">';
            html += '<i class="fa fa-cloud-download"></i> 拉取';
            html += '</button>';
            html += '</td>';
            html += '</tr>';
        });
    }
    
    html += '</tbody></table>';
    html += '</div>';
    
    // 分页控件
    if (failedRecordsTotalPages > 1) {
        html += '<div class="text-center" style="margin-top: 20px;">';
        html += '<ul class="pagination">';
        
        // 上一页
        if (failedRecordsPage > 1) {
            html += '<li><a href="javascript:void(0);" onclick="viewFailedRecords(' + currentLogId + ', ' + (failedRecordsPage - 1) + ')">上一页</a></li>';
        } else {
            html += '<li class="disabled"><span>上一页</span></li>';
        }
        
        // 页码
        var startPage = Math.max(1, failedRecordsPage - 2);
        var endPage = Math.min(failedRecordsTotalPages, failedRecordsPage + 2);
        
        if (startPage > 1) {
            html += '<li><a href="javascript:void(0);" onclick="viewFailedRecords(' + currentLogId + ', 1)">1</a></li>';
            if (startPage > 2) {
                html += '<li class="disabled"><span>...</span></li>';
            }
        }
        
        for (var i = startPage; i <= endPage; i++) {
            if (i == failedRecordsPage) {
                html += '<li class="active"><span>' + i + '</span></li>';
            } else {
                html += '<li><a href="javascript:void(0);" onclick="viewFailedRecords(' + currentLogId + ', ' + i + ')">' + i + '</a></li>';
            }
        }
        
        if (endPage < failedRecordsTotalPages) {
            if (endPage < failedRecordsTotalPages - 1) {
                html += '<li class="disabled"><span>...</span></li>';
            }
            html += '<li><a href="javascript:void(0);" onclick="viewFailedRecords(' + currentLogId + ', ' + failedRecordsTotalPages + ')">' + failedRecordsTotalPages + '</a></li>';
        }
        
        // 下一页
        if (failedRecordsPage < failedRecordsTotalPages) {
            html += '<li><a href="javascript:void(0);" onclick="viewFailedRecords(' + currentLogId + ', ' + (failedRecordsPage + 1) + ')">下一页</a></li>';
        } else {
            html += '<li class="disabled"><span>下一页</span></li>';
        }
        
        html += '</ul>';
        html += '</div>';
    }
    
    // 操作提示
    if (data.rows && data.rows.length > 0) {
        html += '<div class="alert alert-warning" style="margin-top: 15px;">';
        html += '<i class="fa fa-lightbulb-o"></i> ';
        html += '<strong>提示：</strong>您可以单独重试每条记录，或使用下方的"重新处理失败记录"按钮批量处理所有 ' + failedRecordsTotalCount + ' 条失败记录。';
        html += '</div>';
    }
    
    $('#failed-records-content').html(html);
}

// 重试整个任务（重新拉取数据并导入）
window.retryTask = function(taskId) {
    if (!confirm('确定要重试整个任务吗？\\n\\n这将：\\n1. 重新从派单系统拉取最新数据\\n2. 重新执行所有城市的导入\\n\\n注意：所有已导入的数据将保持不变，只是会重新执行一遍流程。')) {
        return;
    }
    
    // 禁用按钮
    var btn = event.target;
    if (btn.tagName !== 'BUTTON') {
        btn = $(btn).closest('button')[0];
    }
    var originalHtml = $(btn).html();
    $(btn).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 重置中...');
    
    $.ajax({
        url: '<?php echo Yii::app()->createUrl("dataMigration/retryTask"); ?>',
        type: 'POST',
        data: { task_id: taskId },
        dataType: 'json',
        success: function(response) {
            $(btn).prop('disabled', false).html(originalHtml);
            
            if (response.status == 1) {
                showMessage('任务已重置成功！\\n\\n任务编号：' + response.task_code + '\\n\\n异步进程将自动重新执行，会重新从派单系统拉取最新数据。\\n\\n请在任务列表中查看进度。', 'success');
                // 刷新任务列表
                loadTaskList(currentPage);
            } else {
                showMessage('重试失败：' + (response.message || '未知错误'), 'error');
            }
        },
        error: function(xhr, status, error) {
            $(btn).prop('disabled', false).html(originalHtml);
            console.error('RetryTask 错误详情：', xhr);
            showMessage('请求失败：' + error + '\\n\\n详细信息请查看浏览器控制台（F12）', 'error');
        }
    });
};

// 从任务列表直接重新处理失败记录
window.retryFailedRecords = function(logId) {
    if (!confirm('确定要重新处理所有失败记录吗？\\n\\n这将重新处理该任务中所有失败的记录。')) {
        return;
    }
    
    // 禁用按钮
    var btn = event.target;
    if (btn.tagName !== 'BUTTON') {
        btn = $(btn).closest('button')[0];
    }
    var originalHtml = $(btn).html();
    $(btn).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 处理中...');
    
    $.ajax({
        url: '$retryFailedUrl',
        type: 'POST',
        data: { log_id: logId },
        dataType: 'json',
        success: function(response) {
            $(btn).prop('disabled', false).html(originalHtml);
            
            if (response.status == 1) {
                var msg = '重新处理完成！\\n\\n';
                msg += '总共处理：' + (response.failed_count || 0) + ' 条\\n';
                msg += '成功：' + (response.success_count || 0) + ' 条\\n';
                msg += '失败：' + (response.error_count || 0) + ' 条';
                showMessage(msg, 'success');
                // 刷新任务列表
                loadTaskList(currentPage);
            } else {
                var errorMsg = '处理失败：' + (response.message || '未知错误');
                if (response.error) {
                    errorMsg += '\\n\\n错误详情：' + response.error;
                }
                if (response.file && response.line) {
                    errorMsg += '\\n位置：' + response.file + ':' + response.line;
                }
                console.error('RetryFailed 错误响应：', response);
                showMessage(errorMsg, 'error');
            }
        },
        error: function(xhr, status, error) {
            $(btn).prop('disabled', false).html(originalHtml);
            console.error('RetryFailed 错误详情：', xhr);
            console.error('状态：', status);
            console.error('错误：', error);
            console.error('响应文本：', xhr.responseText);
            showMessage('请求失败：' + error + '\\n\\n详细信息请查看浏览器控制台（F12）', 'error');
        }
    });
}

// 重新处理单条失败记录
window.retrySingleRecord = function(detailId, refetch) {
    if (!currentLogId) {
        showMessage('未找到日志ID', 'warning');
        return;
    }
    
    var confirmMsg = refetch 
        ? '确定要重新拉取这条记录的最新数据吗？\\n\\n说明：由于派单API是按城市批量获取的，系统会重新拉取该记录所属城市的最新数据，从中找到匹配的记录进行更新。' 
        : '确定要使用现有数据重新处理这条记录吗？';
    
    if (!confirm(confirmMsg)) {
        return;
    }
    
    // 禁用按钮，显示加载状态
    var btn = event.target;
    if (btn.tagName !== 'BUTTON') {
        btn = $(btn).closest('button')[0];
    }
    var originalHtml = $(btn).html();
    $(btn).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 处理中...');
    
    $.ajax({
        url: '$retryFailedUrl',
        type: 'POST',
        data: { 
            log_id: currentLogId,
            detail_ids: [detailId],
            refetch: refetch ? 1 : 0
        },
        dataType: 'json',
        success: function(response) {
            $(btn).prop('disabled', false).html(originalHtml);
            
            if (response.status == 1) {
                var msg = refetch ? '重新拉取并处理成功！' : '重新处理成功！';
                msg += '\\n成功：' + (response.success_count || 0) + '条\\n失败：' + (response.error_count || 0) + '条';
                if (response.refetch_info) {
                    msg += '\\n\\n' + response.refetch_info;
                }
                showMessage(msg, 'success');
                // 刷新失败记录列表
                viewFailedRecords(currentLogId, failedRecordsPage);
                // 刷新任务列表
                loadTaskList(currentPage);
            } else {
                showMessage('处理失败：' + (response.message || '未知错误'), 'error');
            }
        },
        error: function(xhr, status, error) {
            $(btn).prop('disabled', false).html(originalHtml);
            showMessage('请求失败：' + error, 'error');
        }
    });
}

// 模态框中重新处理失败记录（批量）
$('#btn-retry-failed-in-modal').on('click', function() {
    if (!currentLogId) {
        showMessage('未找到日志ID', 'warning');
        return;
    }
    
    if (!confirm('确定要重新处理所有失败记录吗？\\n\\n这将重新处理当前日志的所有失败记录。')) {
        return;
    }
    
    $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 处理中...');
    
    $.ajax({
        url: '$retryFailedUrl',
        type: 'POST',
        data: { log_id: currentLogId },
        dataType: 'json',
        success: function(response) {
            $('#btn-retry-failed-in-modal').prop('disabled', false).html('<i class="fa fa-refresh"></i> 重新处理失败记录');
            
            if (response.status == 1) {
                showMessage('处理完成！\\n成功：' + (response.success_count || 0) + '\\n失败：' + (response.error_count || 0), 'success');
                viewFailedRecords(currentLogId); // 刷新失败记录
                loadTaskList(currentPage); // 刷新任务列表
            } else {
                showMessage('处理失败：' + (response.message || '未知错误'), 'error');
            }
        },
        error: function(xhr, status, error) {
            $('#btn-retry-failed-in-modal').prop('disabled', false).html('<i class="fa fa-refresh"></i> 重新处理失败记录');
            showMessage('请求失败：' + error, 'error');
        }
    });
});

// 模态框中重置失败状态
$('#btn-reset-failed-in-modal').on('click', function() {
    if (!currentLogId) {
        showMessage('未找到日志ID', 'warning');
        return;
    }
    
    if (!confirm('确定要重置失败状态为"待处理"吗？')) {
        return;
    }
    
    $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 重置中...');
    
    $.ajax({
        url: '$resetFailedUrl',
        type: 'POST',
        data: { log_id: currentLogId },
        dataType: 'json',
        success: function(response) {
            $('#btn-reset-failed-in-modal').prop('disabled', false).html('<i class="fa fa-repeat"></i> 重置失败状态');
            
            if (response.status == 1) {
                showMessage('已重置 ' + (response.affected_rows || 0) + ' 条记录', 'success');
                viewFailedRecords(currentLogId); // 刷新失败记录
            } else {
                showMessage('重置失败：' + (response.message || '未知错误'), 'error');
            }
        },
        error: function(xhr, status, error) {
            $('#btn-reset-failed-in-modal').prop('disabled', false).html('<i class="fa fa-repeat"></i> 重置失败状态');
            showMessage('请求失败：' + error, 'error');
        }
    });
});

// 搜索按钮
$('#btn-search').on('click', function() {
    loadTaskList(1);
});

// 刷新按钮
$('#btn-refresh').on('click', function() {
    loadTaskList(currentPage);
});

// 清除筛选按钮
$('#btn-clear-filter').on('click', function() {
    $('#filter-type').val('');
    $('#filter-status').val('');
    $('#filter-keyword').val('');
    loadTaskList(1);
});

// Enter键搜索
$('#filter-keyword').on('keypress', function(e) {
    if (e.which === 13) {
        $('#btn-search').click();
    }
});

// 删除导入数据
window.deleteTaskData = function(logId, taskCode, taskType) {
    var message = '确定要删除任务【' + taskCode + '】(' + taskType + ')导入的所有数据吗？\\n\\n';
    message += '⚠️ 警告：此操作将删除该任务导入的所有数据，包括：\\n';
    message += '• 客户、门店数据\\n';
    message += '• 主合约、虚拟合约数据\\n';
    message += '• 所有相关联的记录\\n\\n';
    message += '此操作不可恢复！请谨慎操作。';
    
    if (!confirm(message)) {
        return;
    }
    
    $.ajax({
        url: '$deleteDataUrl',
        type: 'GET',
        data: { index: logId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                showMessage('删除成功：' + response.message, 'success');
                // 刷新任务列表
                loadTaskList(currentPage);
            } else {
                showMessage('删除失败：' + (response.message || '未知错误'), 'error');
            }
        },
        error: function(xhr, status, error) {
            showMessage('删除失败，请重试。错误信息：' + error, 'error');
        }
    });
};

// 清除所有缓存（统一处理函数）
window.clearPHPCache = function(btn) {
    if (!confirm('确定要清除所有缓存吗？\\n\\n建议在代码修改或数据更新后执行。')) {
        return;
    }
    
    var originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 清除中...');
    
    $.ajax({
        url: '$clearCacheUrl',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            btn.prop('disabled', false).html(originalHtml);
            showMessage(response.message, response.status == 1 ? 'success' : 'error');
        },
        error: function(xhr, status, error) {
            btn.prop('disabled', false).html(originalHtml);
            showMessage('请求失败：' + error, 'error');
        }
    });
}

// 底部清除缓存按钮
$('#btn-clear-cache').on('click', function() {
    clearPHPCache($(this));
});

// 顶部清除缓存按钮
$('#btn-clear-cache-top').on('click', function() {
    clearPHPCache($(this));
});

// 超级删除按钮
$('#btn-super-remove').on('click', function() {
    var confirmMessage = '⚠️⚠️⚠️ 超级危险操作 ⚠️⚠️⚠️\\n\\n';
    confirmMessage += '此操作将删除所有导入数据（report_id > 5001），包括：\\n';
    confirmMessage += '• 所有导入的客户数据\\n';
    confirmMessage += '• 所有导入的门店数据\\n';
    confirmMessage += '• 所有导入的主合约数据\\n';
    confirmMessage += '• 所有导入的虚拟合约数据\\n';
    confirmMessage += '• 所有相关联的记录\\n\\n';
    confirmMessage += '此操作不可恢复！！！\\n\\n';
    confirmMessage += '请输入 "确认删除" 以继续：';
    
    var userInput = prompt(confirmMessage);
    
    if (userInput !== '确认删除') {
        showMessage('已取消超级删除操作', 'info');
        return;
    }
    
    var btn = $(this);
    var originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 正在删除...');
    
    $.ajax({
        url: '$superRemoveUrl',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            btn.prop('disabled', false).html(originalHtml);
            if (response.status === 'success') {
                showMessage(response.message, 'success');
                // 刷新任务列表
                setTimeout(function() {
                    loadTaskList(1);
                }, 2000);
            } else {
                showMessage('超级删除失败：' + (response.message || '未知错误'), 'error');
            }
        },
        error: function(xhr, status, error) {
            btn.prop('disabled', false).html(originalHtml);
            showMessage('超级删除失败，请重试。错误信息：' + error, 'error');
        }
    });
});

// 自动刷新功能（当有处理中的任务时）
var autoRefreshInterval = null;

window.startAutoRefresh = function() {
    if (autoRefreshInterval) {
        return; // 已经在运行了
    }
    
    // 每5秒自动刷新一次
    autoRefreshInterval = setInterval(function() {
        loadTaskList(currentPage);
    }, 5000);
    
    $('#auto-refresh-indicator').fadeIn();
    console.log('已启动自动刷新（每5秒）');
}

window.stopAutoRefresh = function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
        $('#auto-refresh-indicator').fadeOut();
        console.log('已停止自动刷新');
    }
}

// 在更新统计时判断是否有处理中的任务
var originalUpdateStats = updateStats;
updateStats = function(stats) {
    originalUpdateStats(stats);
    
    // 如果有处理中的任务，启动自动刷新
    if (stats.processing > 0) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
};

// 页面加载时自动加载任务列表
$(function() {
    loadTaskList(1);
});

// 页面卸载时停止自动刷新
$(window).on('beforeunload', function() {
    stopAutoRefresh();
});
JS;

Yii::app()->clientScript->registerScript('task-list', $js, CClientScript::POS_READY);
?>

