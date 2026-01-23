<?php
$this->pageTitle=Yii::app()->name . ' - 数据迁移';
?>

<style>
.data-migration-container {
    padding: 15px;
}
.config-panel {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}
.preview-panel {
    margin-top: 20px;
}
.stats-panel {
    background: #fff;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 15px;
}
.stats-item {
    display: inline-block;
    margin-right: 30px;
    font-size: 14px;
}
.stats-item strong {
    color: #3c8dbc;
    font-size: 18px;
}
.error-row {
    background-color: #f2dede !important;
}
.success-row {
    background-color: #dff0d8 !important;
}
.progress-container {
    margin-top: 20px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 5px;
}
.city-selector {
    position: relative;
}
.city-select-trigger.active {
    border-color: #333;
}
.city-select-dropdown.show {
    display: block;
}
.city-option {
    padding: 8px 12px;
    font-size: 14px;
    color: #333;
    cursor: pointer;
    border-bottom: 1px solid #f5f5f5;
    display: flex;
    align-items: center;
    user-select: none;
}
.city-option:hover {
    background-color: #f9f9f9;
}
.city-option input[type="checkbox"] {
    margin-right: 10px;
    width: 16px;
    height: 16px;
    cursor: pointer;
    flex-shrink: 0;
}
.city-option label {
    flex: 1;
    cursor: pointer;
    user-select: none;
}
.expand-btn.collapsed::before {
    content: '▶';
}
.expand-btn.expanded::before {
    content: '▼';
}
.city-children {
    display: none;
    background-color: #fafafa;
}
.city-children.show {
    display: block;
}
/* 自定义简单遮罩层，避免Bootstrap backdrop问题 */
.simple-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    display: none;
}
.simple-backdrop.show {
    display: block;
}
</style>

<section class="content-header">
    <h1>
        <strong>派单系统到CRM数据迁移</strong>
        <a href="<?php echo Yii::app()->createUrl('dataMigration/taskList'); ?>" class="btn btn-info btn-sm" style="margin-left: 15px;">
            <i class="fa fa-list"></i> 查看任务列表
        </a>
        <a href="<?php echo Yii::app()->createUrl('dataMigration/debug'); ?>" class="btn btn-warning btn-sm" style="margin-left: 10px;">
            <i class="fa fa-bug"></i> 调试工具
        </a>
        <button type="button" id="btn-clear-cache-top" class="btn btn-danger btn-sm" style="margin-left: 10px;" title="清除所有缓存（PHP OPcache + 数据缓存）">
            <i class="fa fa-trash"></i> 清除缓存
        </button>
        <button type="button" id="btn-fix-ui" class="btn btn-default btn-sm" style="margin-left: 10px;" title="如果按钮点不动，点这里修复">
            <i class="fa fa-wrench"></i> 修复界面
        </button>
    </h1>
</section>

<section class="content">
    <div class="box box-info">
        <div class="box-body data-migration-container">
            <!-- 数据源配置面板 -->
            <div class="config-panel">
                <h4 style="margin-top: 0;">数据源配置</h4>
                <div class="alert alert-info" style="margin-bottom: 15px;">
                    <i class="fa fa-info-circle"></i>
                    <strong>使用说明：</strong>
                    <br>1. <strong>输入基础API地址</strong>（用于获取城市和负责人列表，所有数据类型共用）。
                    <br>2. 系统会自动加载城市和负责人列表供您选择。
                    <br>3. <strong>选择数据类型</strong>（客户/门店/主合约/虚拟合约）。
                    <br>4. 如果不同数据类型使用不同的API，可在"数据API地址配置"中单独设置（可选，留空则使用基础API）。
                    <br>5. 系统将跳过数据验证环节，直接导入数据。导入过程中会自动进行容错处理。
                    <br>6. <strong>配置会自动保存</strong>到浏览器本地，下次打开页面时自动加载。
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">
                                <i class="fa fa-server"></i> 派单系统API基础地址：
                                <small class="text-muted">（用于获取城市和负责人列表）</small>
                            </label>
                            <input type="text" id="api_base_url" class="form-control" placeholder="api" value="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">认证Token（可选）：</label>
                            <input type="text" id="api_token" class="form-control" placeholder="可选，如需要请输入">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">数据类型：</label>
                            <select id="migration_type" class="form-control">
                                <option value="client">导入派单客户</option>
                                <option value="clientStore">导入派单门店</option>
                                <option value="cont">导入派单主合约</option>
                                <option value="vir">导入派单虚拟合约</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">导出模式：</label>
                            <select id="export_mode" class="form-control">
                                <option value="type">按项目类型（全量）</option>
                                <option value="city">按城市</option>
                                <option value="staff">按负责人</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group" id="project-type-group">
                            <label class="control-label">项目类型：</label>
                            <select id="project_type" class="form-control">
                                <option value="">全部</option>
                                <option value="1">KA项目</option>
                                <option value="2">地推项目</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 数据API地址配置（可选，默认使用基础API） -->
                <div class="row" style="background: #fff; padding: 15px; margin: 0 0 15px 0; border: 1px solid #e0e0e0; border-radius: 3px;">
                    <div class="col-md-12">
                        <h5 style="margin-top: 0; margin-bottom: 10px; color: #333; font-weight: 500;">
                            <i class="fa fa-link"></i> 数据API地址配置
                            <small style="font-weight: normal; color: #666;">（可选，如不填则使用基础API地址）</small>
                        </h5>
                    </div>

                    <!-- 客户API -->
                    <div class="col-md-6 api-config-item" data-type="client">
                        <div class="form-group">
                            <label class="control-label">
                                <i class="fa fa-users"></i> 客户API：
                            </label>
                            <input type="text" id="api_url_client" class="form-control api-url-input" placeholder="留空则使用基础API" value="">
                        </div>
                    </div>

                    <!-- 门店API -->
                    <div class="col-md-6 api-config-item" data-type="clientStore" style="display:none;">
                        <div class="form-group">
                            <label class="control-label">
                                <i class="fa fa-building"></i> 门店API：
                            </label>
                            <input type="text" id="api_url_clientStore" class="form-control api-url-input" placeholder="留空则使用基础API" value="">
                        </div>
                    </div>

                    <!-- 主合约API -->
                    <div class="col-md-6 api-config-item" data-type="cont" style="display:none;">
                        <div class="form-group">
                            <label class="control-label">
                                <i class="fa fa-file-text"></i> 主合约API：
                            </label>
                            <input type="text" id="api_url_cont" class="form-control api-url-input" placeholder="留空则使用基础API" value="">
                        </div>
                    </div>

                    <!-- 虚拟合约API -->
                    <div class="col-md-6 api-config-item" data-type="vir" style="display:none;">
                        <div class="form-group">
                            <label class="control-label">
                                <i class="fa fa-file-o"></i> 虚拟合约API：
                            </label>
                            <input type="text" id="api_url_vir" class="form-control api-url-input" placeholder="留空则使用基础API" value="">
                        </div>
                    </div>
                </div>
                <!-- 城市选择器 -->
                <div class="row" id="city-selector-row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label">选择城市：</label>
                            <div class="city-selector">
                                <div class="city-select-trigger" id="citySelectTrigger" style="width: 100%; padding: 10px 12px; border: 1px solid #d0d0d0; background-color: #fff; color: #333; font-size: 14px; cursor: pointer; border-radius: 2px; text-align: left; display: flex; justify-content: space-between; align-items: center;">
                                    <span id="citySelectText">请选择城市</span>
                                    <span>▼</span>
                                </div>
                                <div class="city-select-dropdown" id="citySelectDropdown" style="position: absolute; left: 0; right: 0; background: #fff; border: 1px solid #d0d0d0; border-top: none; max-height: 450px; overflow-y: auto; z-index: 1000; display: none;">
                                    <div class="city-controls" style="display: flex; gap: 8px; padding: 8px; border-bottom: 1px solid #e0e0e0; flex-wrap: wrap;">
                                        <button type="button" onclick="selectAllCities()" style="padding: 6px 10px; font-size: 12px; border: 1px solid #d0d0d0; background-color: #fff; color: #333; cursor: pointer; border-radius: 2px; flex: 1; min-width: 60px;">全选</button>
                                        <button type="button" onclick="invertCities()" style="padding: 6px 10px; font-size: 12px; border: 1px solid #d0d0d0; background-color: #fff; color: #333; cursor: pointer; border-radius: 2px; flex: 1; min-width: 60px;">反选</button>
                                        <button type="button" onclick="clearAllCities()" style="padding: 6px 10px; font-size: 12px; border: 1px solid #d0d0d0; background-color: #fff; color: #333; cursor: pointer; border-radius: 2px; flex: 1; min-width: 60px;">取消</button>
                                    </div>
                                    <div style="padding: 8px; border-bottom: 1px solid #e0e0e0;">
                                        <input type="text" id="citySearchInput" placeholder="搜索..." style="width: 100%; padding: 8px; border: 1px solid #d0d0d0; border-radius: 2px; font-size: 12px;">
                                    </div>
                                    <div id="cityListContainer"></div>
                                </div>
                            </div>
                            <div class="city-select-tags" id="citySelectTags" style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 8px;"></div>
                            <input type="hidden" id="office_code_ids" value="">
                        </div>
                    </div>
                </div>

                <!-- 负责人选择器 -->
                <div class="row" id="staff-selector-row" style="display: none;">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="control-label">选择负责人：</label>
                            <div class="form-group">
                                <input type="text" id="staffSearchInput" placeholder="搜索负责人..." style="width: 100%; padding: 10px; border: 1px solid #d0d0d0; border-radius: 2px; font-size: 14px; margin-bottom: 10px;">
                            </div>
                            <div id="staffListContainer" style="max-height: 400px; overflow-y: auto; border: 1px solid #e0e0e0; margin-bottom: 10px;"></div>
                            <div class="city-select-tags" id="staffSelectTags" style="margin-bottom: 10px;"></div>
                            <input type="hidden" id="staff_ids" value="">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">搜索关键词：</label>
                            <input type="text" id="search_keyword" class="form-control" placeholder="可选">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" id="btn-fetch-data" class="btn btn-primary">
                                    <i class="fa fa-download"></i> 获取数据
                                </button>
                                <button type="button" id="btn-batch-import-by-city" class="btn btn-success">
                                    <i class="fa fa-tasks"></i> 按城市批量导入
                                </button>
                                <button type="button" id="btn-create-async-task" class="btn btn-info" title="创建后台异步任务，无需保持页面打开">
                                    <i class="fa fa-cloud-upload"></i> 创建异步任务
                                </button>
                                <button type="button" id="btn-validate-data" class="btn btn-info" style="display:none;">
                                    <i class="fa fa-check"></i> 验证数据（可选）
                                </button>
                                <button type="button" id="btn-start-import" class="btn btn-warning" style="display:none;">
                                    <i class="fa fa-upload"></i> 开始导入（跳过验证）
                                </button>
                                <button type="button" id="btn-save-config" class="btn btn-default">
                                    <i class="fa fa-save"></i> 保存配置
                                </button>
                                <button type="button" id="btn-reset" class="btn btn-default">
                                    <i class="fa fa-refresh"></i> 重置
                                </button>
                                <button type="button" id="btn-clear-cache" class="btn btn-danger">
                                    <i class="fa fa-trash"></i> 清除PHP缓存
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 批量导入进度面板 -->
                <div class="row" id="batch-import-progress" style="display:none; margin-top: 20px;">
                    <div class="col-md-12">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-tasks"></i> 批量导入进度
                                </h3>
                                <div class="box-tools pull-right">
                                    <button type="button" id="btn-stop-batch" class="btn btn-danger btn-sm" style="display:none;">
                                        <i class="fa fa-stop"></i> 停止
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="progress-stats" style="margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div style="text-align: center;">
                                                <div style="font-size: 12px; color: #666;">总城市数</div>
                                                <div style="font-size: 24px; font-weight: bold;" id="batch-total-cities">0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div style="text-align: center;">
                                                <div style="font-size: 12px; color: #666;">已完成</div>
                                                <div style="font-size: 24px; font-weight: bold; color: #00a65a;" id="batch-completed-cities">0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div style="text-align: center;">
                                                <div style="font-size: 12px; color: #666;">进行中</div>
                                                <div style="font-size: 24px; font-weight: bold; color: #3c8dbc;" id="batch-processing-city">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div style="text-align: center;">
                                                <div style="font-size: 12px; color: #666;">总成功</div>
                                                <div style="font-size: 24px; font-weight: bold; color: #00a65a;" id="batch-total-success">0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div style="text-align: center;">
                                                <div style="font-size: 12px; color: #666;">总失败</div>
                                                <div style="font-size: 24px; font-weight: bold; color: #dd4b39;" id="batch-total-errors">0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div style="text-align: center;">
                                                <div style="font-size: 12px; color: #666;">总耗时</div>
                                                <div style="font-size: 24px; font-weight: bold;" id="batch-total-time">0s</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress" style="height: 30px; margin-bottom: 15px;">
                                    <div id="batch-progress-bar" class="progress-bar progress-bar-success progress-bar-striped active"
                                         role="progressbar" style="width: 0%; line-height: 30px; font-size: 14px; font-weight: bold;">
                                        0%
                                    </div>
                                </div>
                                <div id="batch-city-list" style="max-height: 500px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; background: #fff;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 异步任务监控面板 -->
                <div class="row" id="async-task-monitor" style="display:none; margin-top: 20px;">
                    <div class="col-md-12">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-cloud"></i> 异步任务监控
                                </h3>
                                <div class="box-tools pull-right">
                                    <button type="button" id="btn-view-task-details" class="btn btn-sm btn-default">
                                        <i class="fa fa-list"></i> 查看详情
                                    </button>
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <input type="hidden" id="monitor-task-id" value="">
                                <div class="row" style="margin-bottom: 15px;">
                                    <div class="col-md-2">
                                        <div style="text-align: center;">
                                            <div style="font-size: 12px; color: #666;">任务状态</div>
                                            <div style="font-size: 20px; font-weight: bold;" id="task-status-text">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div style="text-align: center;">
                                            <div style="font-size: 12px; color: #666;">进度</div>
                                            <div style="font-size: 20px; font-weight: bold; color: #3c8dbc;" id="task-progress">0%</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div style="text-align: center;">
                                            <div style="font-size: 12px; color: #666;">总城市数</div>
                                            <div style="font-size: 20px; font-weight: bold;" id="task-total-cities">0</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div style="text-align: center;">
                                            <div style="font-size: 12px; color: #666;">已完成</div>
                                            <div style="font-size: 20px; font-weight: bold; color: #00a65a;" id="task-completed-cities">0</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div style="text-align: center;">
                                            <div style="font-size: 12px; color: #666;">总成功</div>
                                            <div style="font-size: 20px; font-weight: bold; color: #00a65a;" id="task-success-count">0</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div style="text-align: center;">
                                            <div style="font-size: 12px; color: #666;">总失败</div>
                                            <div style="font-size: 20px; font-weight: bold; color: #dd4b39;" id="task-error-count">0</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress" style="height: 30px; margin-bottom: 15px;">
                                    <div id="task-progress-bar" class="progress-bar progress-bar-info progress-bar-striped active"
                                         role="progressbar" style="width: 0%; line-height: 30px; font-size: 14px; font-weight: bold;">
                                        0%
                                    </div>
                                </div>
                                <div style="text-align: center; color: #666;">
                                    当前处理城市：<span id="task-current-city">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 统计信息面板 -->
            <div class="stats-panel" id="stats-panel" style="display:none;">
                <div class="stats-item">
                    <strong id="total-count">0</strong> 总记录数
                </div>
                <div class="stats-item">
                    <strong id="valid-count" style="color: #5cb85c;">0</strong> 验证通过
                </div>
                <div class="stats-item">
                    <strong id="error-count" style="color: #d9534f;">0</strong> 验证失败
                </div>
                <div class="stats-item">
                    <strong id="imported-count" style="color: #5bc0de;">0</strong> 已导入
                </div>
            </div>

            <!-- 数据预览表格 -->
            <div class="preview-panel" id="preview-panel" style="display:none;">
                <h4>数据预览</h4>

                <!-- 筛选和搜索工具栏 -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <label>状态筛选：</label>
                        <select id="filter-status" class="form-control">
                            <option value="">全部</option>
                            <option value="P">待处理</option>
                            <option value="S">成功</option>
                            <option value="E">失败</option>
                            <option value="K">跳过</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>搜索关键词：</label>
                        <input type="text" id="filter-search" class="form-control" placeholder="搜索数据内容...">
                    </div>
                    <div class="col-md-5">
                        <label>&nbsp;</label>
                        <div>
                            <button type="button" id="btn-apply-filter" class="btn btn-primary">
                                <i class="fa fa-filter"></i> 应用筛选
                            </button>
                            <button type="button" id="btn-reset-filter" class="btn btn-default">
                                <i class="fa fa-undo"></i> 重置
                            </button>
                            <button type="button" id="btn-retry-failed" class="btn btn-warning" style="display:none;">
                                <i class="fa fa-refresh"></i> 重新执行失败记录
                            </button>
                            <button type="button" id="btn-reset-failed-status" class="btn btn-info" style="display:none;">
                                <i class="fa fa-repeat"></i> 重置失败状态
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="data-table" class="table table-bordered table-striped table-hover">
                        <thead id="table-head">
                        </thead>
                        <tbody id="table-body">
                        </tbody>
                    </table>
                </div>
                <div id="pagination-container" style="text-align: center; margin-top: 15px;"></div>
            </div>

            <!-- 导入进度 -->
            <div class="progress-container" id="progress-container" style="display:none;">
                <h4>导入进度</h4>
                <div class="progress">
                    <div id="progress-bar" class="progress-bar progress-bar-striped active" role="progressbar"
                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                        <span id="progress-text">0%</span>
                    </div>
                </div>
                <div style="margin-top: 10px;">
                    <div id="progress-detail"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 简单自定义遮罩层 -->
<div class="simple-backdrop" id="simple-backdrop"></div>

<!-- 通用消息提示模态框 -->
<div class="modal fade" id="message-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" id="message-modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="message-modal-title">
                    <i class="fa fa-info-circle" id="message-modal-icon"></i> <span id="message-modal-title-text">提示</span>
                </h4>
            </div>
            <div class="modal-body" id="message-modal-body" style="white-space: pre-line;">
                消息内容
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="message-modal-btn-ok">确定</button>
                <button type="button" class="btn btn-default" data-dismiss="modal" id="message-modal-btn-cancel" style="display:none;">取消</button>
            </div>
        </div>
    </div>
</div>

<!-- 任务详情模态框 -->
<div class="modal fade" id="task-details-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-list"></i> 任务详情
                </h4>
            </div>
            <div class="modal-body">
                <div id="task-details-content" style="max-height: 500px; overflow-y: auto;">
                    <p class="text-center text-muted">
                        <i class="fa fa-spinner fa-spin"></i> 加载中...
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<?php
$fetchUrl = Yii::app()->createUrl('dataMigration/fetchData');
$validateUrl = Yii::app()->createUrl('dataMigration/validateData');
$previewUrl = Yii::app()->createUrl('dataMigration/previewData');
$importUrl = Yii::app()->createUrl('dataMigration/importData');
$progressUrl = Yii::app()->createUrl('dataMigration/getProgress');
$csrfToken = Yii::app()->request->csrfToken;

// 异步任务相关 URL
$createAsyncTaskUrl = Yii::app()->createUrl('dataMigration/createAsyncTask');
$getTaskStatusUrl = Yii::app()->createUrl('dataMigration/getTaskStatus');
$getTaskDetailsUrl = Yii::app()->createUrl('dataMigration/getTaskDetails');
$clearCacheUrl = Yii::app()->createUrl('dataMigration/clearCache');

// 转义JavaScript字符串（CJavaScript::encode返回带引号的字符串）
$fetchUrlJs = CJavaScript::encode($fetchUrl);
$validateUrlJs = CJavaScript::encode($validateUrl);
$previewUrlJs = CJavaScript::encode($previewUrl);
$importUrlJs = CJavaScript::encode($importUrl);
$progressUrlJs = CJavaScript::encode($progressUrl);
$csrfTokenJs = CJavaScript::encode($csrfToken);
$createAsyncTaskUrlJs = CJavaScript::encode($createAsyncTaskUrl);
$getTaskStatusUrlJs = CJavaScript::encode($getTaskStatusUrl);
$getTaskDetailsUrlJs = CJavaScript::encode($getTaskDetailsUrl);
$clearCacheUrlJs = CJavaScript::encode($clearCacheUrl);

$js = <<<JS
// 注意：全局变量、getCurrentApiUrl、getBaseApiUrl、loadPreviewData 和 cleanupModalBackdrop 函数已在全局作用域中定义

// 设置Ajax请求默认带CSRF Token，并在每次请求完成后清理遮罩层
$.ajaxSetup({
    data: {
        'YII_CSRF_TOKEN': {$csrfTokenJs}
    },
    beforeSend: function(xhr, settings) {
        if (settings.type == 'POST') {
            if (typeof settings.data === 'string') {
                settings.data += '&YII_CSRF_TOKEN=' + encodeURIComponent({$csrfTokenJs});
            }
        }
    },
    complete: function(xhr, status) {
        // 每次Ajax请求完成后，清理可能残留的遮罩层
        // 使用 setTimeout 确保在其他回调执行后再清理
        setTimeout(function() {
            cleanupModalBackdrop();
        }, 100);
    }
});

// ========== 通用消息提示Modal函数 ==========
// 注意：showMessage 和 showConfirm 函数已在全局作用域中定义

// ========== 配置保存和加载功能 ==========
// 注意：saveConfig、loadConfig 和 clearConfig 函数已在全局作用域中定义

// ========== 城市和负责人选择器功能 ==========

// 数据类型切换
$('#migration_type').on('change', function() {
    var migrationType = $(this).val();
    
    // 显示对应的API配置区域
    $('.api-config-item').hide();
    $('.api-config-item[data-type="' + migrationType + '"]').show();
    
    // ✅ 项目类型对所有迁移类型都适用（客户、门店、主合约、虚拟合约都需要按项目类型过滤）
    $('#project-type-group').show();
    
    // 注意：城市和负责人列表使用基础API，不需要在切换数据类型时重新加载
});

// 导出模式切换
$('#export_mode').on('change', function() {
    var mode = $(this).val();
    if (mode === 'type') {
        // 按项目类型全量导出：不需要选择城市或负责人
        $('#city-selector-row').hide();
        $('#staff-selector-row').hide();
    } else if (mode === 'city') {
        $('#city-selector-row').show();
        $('#staff-selector-row').hide();
        if (cityTree.length === 0) {
            loadCities();
        }
    } else {
        $('#city-selector-row').hide();
        $('#staff-selector-row').show();
        if (staffList.length === 0) {
            loadStaffList();
        }
    }
});

// 加载城市数据
function loadCities() {
    var apiUrl = getBaseApiUrl();
    
    if (!apiUrl) {
        console.warn('基础API地址未设置，无法加载城市数据');
        $('#citySelectText').text('请先输入基础API地址');
        return;
    }
    
    // 显示加载状态
    $('#citySelectText').html('<i class="fa fa-spinner fa-spin"></i> 正在加载城市数据...');
    
    $.ajax({
        url: apiUrl + '/data/test1/getCities',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.code === 200) {
                cityTree = response.data;
                initCitiesSelector();
                console.log('城市树形数据加载完成，共 ' + (cityTree.length || 0) + ' 个分组');
            } else {
                console.error('加载城市数据失败:', response.message);
                $('#citySelectText').text('加载失败，请检查API地址');
                showMessage('加载城市数据失败: ' + (response.message || '未知错误'), 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('加载城市数据出错:', error);
            $('#citySelectText').text('加载失败，请检查API地址');
            showMessage('加载城市数据出错: ' + error + '\\n请检查基础API地址是否正确', 'error');
        }
    });
}

// 加载负责人列表
function loadStaffList() {
    var apiUrl = getBaseApiUrl();
    var container = $('#staffListContainer');
    
    if (!apiUrl) {
        console.warn('基础API地址未设置，无法加载负责人数据');
        container.html('<div style="padding: 20px; text-align: center; color: #999;">请先输入基础API地址</div>');
        return;
    }
    
    // 显示加载状态
    container.html('<div style="padding: 20px; text-align: center;"><i class="fa fa-spinner fa-spin"></i> 正在加载负责人数据...</div>');
    
    $.ajax({
        url: apiUrl + '/data/test1/getKaProjectStaff',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.code === 200) {
                staffList = response.data;
                container.empty(); // 清空加载提示
                initStaffSelector();
                console.log('负责人列表加载完成，共 ' + (staffList.length || 0) + ' 人');
            } else {
                console.error('加载负责人列表失败:', response.message);
                container.html('<div style="padding: 20px; text-align: center; color: #d9534f;">加载失败，请检查API地址</div>');
                showMessage('加载负责人列表失败: ' + (response.message || '未知错误'), 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('加载负责人列表出错:', error);
            container.html('<div style="padding: 20px; text-align: center; color: #d9534f;">加载失败，请检查API地址</div>');
            showMessage('加载负责人列表出错: ' + error + '\\n请检查基础API地址是否正确', 'error');
        }
    });
}

// 渲染城市树节点
function renderNode(node, parentDiv, depth) {
    // 构建显示文本：城市名称 (ID)
    var displayText = node.name + ' (' + node.id + ')';
    
    var option = $('<div></div>').addClass('city-option')
        .attr('data-id', node.id)
        .attr('data-name', node.name.toLowerCase())
        .attr('data-type', node.type || 0)
        .css('padding-left', (depth * 16) + 'px');
    
    var checkbox = $('<input type="checkbox">')
        .attr('id', 'city_' + node.id)
        .val(node.id)
        .on('change', function() {
            if (this.checked) {
                selectedCityIds.add(node.id.toString());
                selectAllChildren($(this).closest('.city-option'));
            } else {
                selectedCityIds.delete(node.id.toString());
                deselectAllChildren($(this).closest('.city-option'));
            }
            updateCityTags();
        });
    
    var label = $('<label></label>')
        .attr('for', 'city_' + node.id)
        .text(displayText)
        .css('flex', '1');
    
    // 如果是办事处（type=2），添加特殊样式标识
    if (node.type == 2) {
        label.css({'color': '#ff8c00', 'font-weight': '500'});
    }
    
    var expandDiv = $('<div></div>')
        .addClass('expand-btn collapsed')
        .css({'width': '20px', 'height': '20px', 'display': 'flex', 'align-items': 'center', 'justify-content': 'center', 'cursor': 'pointer', 'flex-shrink': '0', 'color': '#666', 'font-size': '12px'});
    
    if (node.children && node.children.length > 0) {
        expandDiv.css('display', 'flex');
        expandDiv.on('click', function(e) {
            e.stopPropagation();
            var nextDiv = option.next('.city-children');
            if (nextDiv.length > 0) {
                nextDiv.toggleClass('show');
                expandDiv.toggleClass('collapsed expanded');
            }
        });
    } else {
        expandDiv.css('visibility', 'hidden');
    }
    
    option.append(checkbox).append(label).append(expandDiv);
    parentDiv.append(option);
    
    if (node.children && node.children.length > 0) {
        var childDiv = $('<div></div>').addClass('city-children');
        node.children.forEach(function(child) {
            renderNode(child, childDiv, depth + 1);
        });
        parentDiv.append(childDiv);
    }
}

// 选中所有子节点
function selectAllChildren(parentElement) {
    var nextSibling = parentElement.next('.city-children');
    if (nextSibling.length > 0) {
        nextSibling.find('input[type="checkbox"]').each(function() {
            this.checked = true;
            selectedCityIds.add($(this).val());
            selectAllChildren($(this).closest('.city-option'));
        });
    }
}

// 取消选中所有子节点
function deselectAllChildren(parentElement) {
    var nextSibling = parentElement.next('.city-children');
    if (nextSibling.length > 0) {
        nextSibling.find('input[type="checkbox"]').each(function() {
            this.checked = false;
            selectedCityIds.delete($(this).val());
            deselectAllChildren($(this).closest('.city-option'));
        });
    }
}

// 初始化城市选择器
function initCitiesSelector() {
    var container = $('#cityListContainer');
    var trigger = $('#citySelectTrigger');
    var dropdown = $('#citySelectDropdown');
    var searchInput = $('#citySearchInput');
    
    if (container.children().length > 0) return;
    
    cityTree.forEach(function(group) {
        var groupDiv = $('<div></div>');
        var groupTitle = $('<div></div>')
            .addClass('group-title')
            .text(group.label || group.name)
            .css({'padding': '10px 12px', 'font-weight': '600', 'font-size': '13px', 'color': '#1976d2', 'border-bottom': '1px solid #e0e0e0', 'background-color': '#f5f5f5'});
        groupDiv.append(groupTitle);
        
        if (group.children) {
            group.children.forEach(function(node) {
                renderNode(node, groupDiv, 1);
            });
        }
        container.append(groupDiv);
    });
    
    // 恢复之前选中的城市
    selectedCityIds.forEach(function(cityId) {
        var checkbox = $('#city_' + cityId);
        if (checkbox.length > 0) {
            checkbox.prop('checked', true);
        }
    });
    updateCityTags();
    
    searchInput.on('input', function() {
        var searchText = $(this).val().toLowerCase().trim();
        var cityContainer = $('#cityListContainer');
        var allOptions = cityContainer.find('.city-option');
        var allChildren = cityContainer.find('.city-children');
        var allTitles = cityContainer.find('.group-title');

        if (searchText === '') {
            allOptions.show().css('display', 'flex');
            allChildren.removeClass('show');
            cityContainer.find('.expand-btn').removeClass('expanded').addClass('collapsed');
            allTitles.show();
            return;
        }

        // 隐藏所有
        allOptions.hide();
        allChildren.removeClass('show');
        allTitles.hide();

        allOptions.each(function() {
            var optionItem = $(this);
            var name = optionItem.attr('data-name') || '';
            var id = (optionItem.attr('data-id') || '').toString();
            
            // 同时匹配名称和ID (处理类似 FA 或 数字ID 的搜索)
            if (name.indexOf(searchText) !== -1 || id.indexOf(searchText) !== -1) {
                optionItem.show().css('display', 'flex');
                
                // 向上追溯并展开所有父层级
                optionItem.parents('.city-children').each(function() {
                    var childContainer = $(this);
                    childContainer.addClass('show');
                    var parentOption = childContainer.prev('.city-option');
                    if (parentOption.length) {
                        parentOption.show().css('display', 'flex');
                        parentOption.find('.expand-btn').removeClass('collapsed').addClass('expanded');
                    }
                });
                
                // 显示所属的分组标题
                optionItem.closest('#cityListContainer > div').find('.group-title').show();
            }
        });
    });
    
    trigger.on('click', function() {
        if (dropdown.is(':visible')) {
            dropdown.hide();
            trigger.removeClass('active');
        } else {
            dropdown.show();
            trigger.addClass('active');
            // 清空搜索框并重置显示
            searchInput.val('').trigger('input').focus();
        }
    });
    
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.city-selector').length) {
            dropdown.hide();
            trigger.removeClass('active');
            searchInput.val('').trigger('input');  // 清空搜索并触发重置显示
        }
    });
}

// 初始化负责人选择器
function initStaffSelector() {
    var container = $('#staffListContainer');
    if (container.children().length > 0) return;
    
    staffList.forEach(function(staff) {
        var option = $('<div></div>')
            .addClass('city-option')
            .attr('data-staff-id', staff.id)
            .attr('data-staff-name', (staff.name || staff.code).toLowerCase())
            .css({'padding': '8px 12px', 'font-size': '14px', 'color': '#333', 'cursor': 'pointer', 'border-bottom': '1px solid #f5f5f5', 'display': 'flex', 'align-items': 'center'});
        
        var checkbox = $('<input type="checkbox">')
            .attr('id', 'staff_' + staff.id)
            .val(staff.id)
            .css({'margin-right': '10px', 'width': '16px', 'height': '16px', 'cursor': 'pointer'})
            .on('change', updateStaffTags);
        
        var label = $('<label></label>')
            .attr('for', 'staff_' + staff.id)
            .text(staff.name || staff.code)
            .css({'flex': '1', 'cursor': 'pointer'});
        
        option.append(checkbox).append(label);
        container.append(option);
    });
    
    $('#staffSearchInput').on('input', function() {
        var searchText = $(this).val().toLowerCase();
        $('#staffListContainer .city-option').each(function() {
            var name = $(this).attr('data-staff-name');
            if (name.indexOf(searchText) !== -1) {
                $(this).css('display', 'flex');
            } else {
                $(this).css('display', 'none');
            }
        });
    });
}

// 更新负责人标签
function updateStaffTags() {
    var container = $('#staffSelectTags');
    var checkedStaff = $('input[id^="staff_"]:checked');
    container.empty();
    
    var staffIds = [];
    checkedStaff.each(function() {
        var staffId = $(this).val();
        staffIds.push(staffId);
        var staff = staffList.find(function(s) { return s.id == staffId; });
        if (staff) {
            var tag = $('<div></div>')
                .addClass('city-tag')
                .css({'background-color': '#f0f0f0', 'border': '1px solid #d0d0d0', 'padding': '5px 10px', 'border-radius': '2px', 'font-size': '13px', 'color': '#333', 'display': 'inline-flex', 'align-items': 'center', 'gap': '6px'})
                .html('<span>' + (staff.name || staff.code) + '</span><button type="button" onclick="removeStaff(\'' + 'staff_' + staffId + '\')" style="background: none; border: none; padding: 0; color: #999; cursor: pointer; font-size: 16px; line-height: 1;">×</button>');
            container.append(tag);
        }
    });
    
    $('#staff_ids').val(staffIds.join(','));
}

// 移除负责人
function removeStaff(checkboxId) {
    $('#' + checkboxId).prop('checked', false);
    updateStaffTags();
}

// 监听基础API地址变化
$('#api_base_url').on('blur', function() {
    var apiUrl = $(this).val();
    if (apiUrl) {
        var exportMode = $('#export_mode').val();
        // 重置数据，强制重新加载城市/负责人列表
        cityTree = [];
        staffList = [];
        if (exportMode === 'city') {
            loadCities();
        } else {
            loadStaffList();
        }
    }
});

// 具体数据API地址变化（不影响城市/负责人列表）
$('.api-url-input').on('blur', function() {
    // 具体的数据API地址变化时，不需要重新加载城市/负责人列表
    // 只在点击"获取数据"时使用
    console.log('数据API地址已更新');
});

// 页面加载时自动加载配置
$(function() {
    // 页面加载时清理可能残留的遮罩层
    cleanupModalBackdrop();
    
    var hasConfig = loadConfig();
    loadRequestedData(); // 加载已请求数据记录
    
    if (hasConfig) {
        // 显示提示信息
        $('<div class="alert alert-success" style="margin-top:10px;" id="config-loaded-tip">' +
          '<i class="fa fa-check-circle"></i> 已自动加载上次保存的配置</div>')
          .appendTo('.config-panel')
          .delay(3000)
          .fadeOut();
    }
    
    // 显示当前数据类型对应的API配置
    var migrationType = $('#migration_type').val();
    $('.api-config-item').hide();
    $('.api-config-item[data-type="' + migrationType + '"]').show();
    
    // ✅ 项目类型对所有迁移类型都适用（客户、门店、主合约、虚拟合约都需要按项目类型过滤）
    $('#project-type-group').show();
    
    // 根据导出模式初始化选择器
    var exportMode = $('#export_mode').val();
    if (exportMode === 'type') {
        // 按项目类型：不显示城市和负责人选择器
        $('#city-selector-row').hide();
        $('#staff-selector-row').hide();
    } else if (exportMode === 'city') {
        $('#city-selector-row').show();
        $('#staff-selector-row').hide();
    } else {
        $('#city-selector-row').hide();
        $('#staff-selector-row').show();
    }
    
    // 检查是否有基础API地址，有的话立即加载城市/负责人数据
    var baseApiUrl = getBaseApiUrl();
    if (baseApiUrl && exportMode !== 'type') {
        if (exportMode === 'city') {
            loadCities();
        } else if (exportMode === 'staff') {
            loadStaffList();
        }
    } else if (!baseApiUrl) {
        // 如果没有基础API地址，提示用户输入
        console.log('请先输入基础API地址以加载城市和负责人数据');
    }
});

// 获取数据
$('#btn-fetch-data').on('click', function() {
    // 先清理可能残留的backdrop，确保页面可交互
    cleanupModalBackdrop();
    
    var migrationType = $('#migration_type').val();
    var apiUrl = getCurrentApiUrl();
    var apiToken = $('#api_token').val();
    var exportMode = $('#export_mode').val();
    var officeCodeIds = $('#office_code_ids').val();
    var staffIds = $('#staff_ids').val();
    var searchKeyword = $('#search_keyword').val();
    var projectType = $('#project_type').val(); // ✅ 获取项目类型
    
    var typeNames = {
        'client': '客户',
        'clientStore': '门店',
        'cont': '主合约',
        'vir': '虚拟合约'
    };
    var typeName = typeNames[migrationType] || '数据';
    
    if (!apiUrl) {
        showMessage('请先输入' + typeName + 'API地址', 'warning');
        return;
    }
    
    // 保存配置供下次使用
    saveConfig();
    
    var filterParams = {
        export_mode: exportMode,
        office_code_ids: officeCodeIds ? officeCodeIds.split(',').map(function(id) { return parseInt(id.trim()); }) : [],
        staff_ids: staffIds ? staffIds.split(',').map(function(id) { return parseInt(id.trim()); }) : [],
        search_keyword: searchKeyword || '',
        page: 1,
        page_size: 10000
    };
    
    // ✅ 所有迁移类型都传递 type 参数（客户、门店、主合约、虚拟合约）
    if (projectType) {
        filterParams.type = projectType;
    }
    
    var params = {
        api_url: apiUrl,
        api_config: JSON.stringify({
            auth_type: 'token',
            token: apiToken || ''
        }),
        migration_type: migrationType,
        filter_params: JSON.stringify(filterParams)
    };
    
    console.log('发送的参数:', params);
    console.log('API地址:', apiUrl);
    
    $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 获取中...');
    
    $.ajax({
        url: {$fetchUrlJs},
        type: 'POST',
        data: params,
        dataType: 'json',
        success: function(response) {
            $('#btn-fetch-data').prop('disabled', false).html('<i class="fa fa-download"></i> 获取数据');
            cleanupModalBackdrop(); // 确保清理backdrop
            
            console.log('响应数据:', response);
            
            if (response.status == 1) {
                // ✅ 添加安全检查
                if (response.data && response.data.log_id) {
                currentLogId = response.data.log_id;
                $('#stats-panel').show();
                $('#preview-panel').show();
                $('#btn-validate-data').show();
                $('#btn-start-import').show();
                } else {
                    console.error('响应缺少 log_id:', response);
                    showMessage('获取数据成功，但响应格式异常，请检查后端返回数据', 'warning');
                    return;
                }
                
                // 记录当前类型下已请求过的城市ID
                if (exportMode === 'city' && officeCodeIds && requestedData[migrationType]) {
                    officeCodeIds.split(',').forEach(function(id) {
                        requestedData[migrationType].add(id.trim());
                    });
                    updateCityTags(); // 刷新城市标签显示
                    saveRequestedData(); // 持久化
                }
                
                updateStats(response.data);
                loadPreviewData(1);
            } else {
                var errorMsg = '获取数据失败: ' + (response.message || '未知错误');
                if (response.error) {
                    errorMsg += '\\n错误详情: ' + response.error;
                }
                if (response.file) {
                    errorMsg += '\\n文件: ' + response.file;
                }
                if (response.line) {
                    errorMsg += '\\n行号: ' + response.line;
                }
                showMessage(errorMsg, 'error');
                console.error('错误详情:', response);
            }
        },
        error: function(xhr, status, error) {
            $('#btn-fetch-data').prop('disabled', false).html('<i class="fa fa-download"></i> 获取数据');
            cleanupModalBackdrop(); // 确保清理backdrop
            console.error('AJAX错误:', xhr.responseText);
            console.error('状态:', status);
            console.error('错误:', error);
            showMessage('请求失败: ' + error + '\\n详情请查看Console', 'error');
        }
    });
});

// 验证数据
$('#btn-validate-data').on('click', function() {
    if (!currentLogId) {
        showMessage('请先获取数据', 'warning');
        return;
    }
    
    $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 验证中...');
    
    $.ajax({
        url: {$validateUrlJs},
        type: 'POST',
        data: {
            log_id: currentLogId
        },
        dataType: 'json',
        success: function(response) {
            $('#btn-validate-data').prop('disabled', false).html('<i class="fa fa-check"></i> 验证数据');
            
            if (response.status == 1) {
                updateStats(response.data);
                loadPreviewData(currentPage);
                $('#btn-start-import').show();
            } else {
                showMessage('验证失败: ' + (response.error || '未知错误'), 'error');
            }
        },
        error: function(xhr, status, error) {
            $('#btn-validate-data').prop('disabled', false).html('<i class="fa fa-check"></i> 验证数据');
            showMessage('请求失败: ' + error, 'error');
        }
    });
});

// 开始导入
$('#btn-start-import').on('click', function() {
    if (!currentLogId) {
        showMessage('请先获取数据', 'warning');
        return;
    }
    
    var btnImport = $(this);
    showConfirm('确定要开始导入数据吗？<br><br>注意：将跳过数据验证，直接导入所有数据。<br>导入过程中会自动容错处理，失败的数据会被标记。', function() {
        btnImport.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 导入中...');
        $('#progress-container').show();
        
        $.ajax({
            url: {$importUrlJs},
            type: 'POST',
            data: {
                log_id: currentLogId,
                import_mode: 'all',
                batch_size: 100
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 1) {
                    // 开始轮询进度
                    pollProgress();
                } else {
                    btnImport.prop('disabled', false).html('<i class="fa fa-upload"></i> 开始导入');
                    showMessage('导入失败: ' + (response.error || '未知错误'), 'error');
                }
            },
            error: function(xhr, status, error) {
                btnImport.prop('disabled', false).html('<i class="fa fa-upload"></i> 开始导入');
                showMessage('请求失败: ' + error, 'error');
            }
        });
    });
});

// 按城市批量导入按钮
$('#btn-batch-import-by-city').on('click', function() {
    startBatchImportByCity();
});

// 停止批量导入按钮
$('#btn-stop-batch').on('click', function() {
    stopBatchImport();
});

// 保存配置按钮
$('#btn-save-config').on('click', function() {
    // 先清理可能残留的backdrop
    cleanupModalBackdrop();
    saveConfig();
    showMessage('✅ 配置已保存！<br><br>下次打开页面时将自动加载这些配置。', 'success');
});

// 重置
$('#btn-reset').on('click', function() {
    // 先清理可能残留的backdrop
    cleanupModalBackdrop();
    showConfirm('确定要重置吗？<br><br>⚠️ 所有数据和保存的配置将被清除。', function() {
        clearConfig(); // 清除保存的配置
        location.reload();
    });
});

// 清除所有缓存（统一处理函数）
function clearPHPCache(btn) {
    showConfirm('确定要清除所有缓存吗？<br><br>建议在代码修改或数据更新后执行。', function() {
        var originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 清除中...');
        
        $.ajax({
            url: {$clearCacheUrlJs},
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html(originalHtml);
                showMessage(response.message, response.status == 1 ? 'success' : 'info');
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false).html(originalHtml);
                showMessage('请求失败：' + error, 'error');
            }
        });
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

// 修复界面按钮 - 清理遮罩层和恢复页面交互
$('#btn-fix-ui').on('click', function() {
    console.log('开始修复界面...');
    
    // 1. 清理所有模态框backdrop
    cleanupModalBackdrop();
    
    // 2. 强制关闭所有打开的modal
    $('.modal').modal('hide');
    $('.modal').removeClass('in').css('display', 'none');
    
    // 3. 恢复所有按钮状态（移除disabled和loading状态）
    $('button').each(function() {
        var btn = $(this);
        // 只恢复那些有spinner图标的按钮（说明它们在loading中卡住了）
        if (btn.find('.fa-spinner').length > 0) {
            var btnId = btn.attr('id');
            console.log('恢复按钮:', btnId);
            
            // 根据按钮ID恢复原始文本
            var originalTexts = {
                'btn-fetch-data': '<i class="fa fa-download"></i> 获取数据',
                'btn-batch-import-by-city': '<i class="fa fa-tasks"></i> 按城市批量导入',
                'btn-create-async-task': '<i class="fa fa-cloud-upload"></i> 创建异步任务',
                'btn-validate-data': '<i class="fa fa-check"></i> 验证数据（可选）',
                'btn-start-import': '<i class="fa fa-upload"></i> 开始导入（跳过验证）',
                'btn-save-config': '<i class="fa fa-save"></i> 保存配置',
                'btn-reset': '<i class="fa fa-refresh"></i> 重置',
                'btn-clear-cache': '<i class="fa fa-trash"></i> 清除PHP缓存',
                'btn-clear-cache-top': '<i class="fa fa-trash"></i> 清除缓存'
            };
            
            if (originalTexts[btnId]) {
                btn.html(originalTexts[btnId]);
            }
            btn.prop('disabled', false);
        }
    });
    
    // 4. 移除所有可能的遮罩元素（包括自定义遮罩层）
    $('.modal-backdrop').remove();
    $('#simple-backdrop').removeClass('show');
    $('body').removeClass('modal-open').css({
        'overflow': '',
        'padding-right': ''
    });
    
    // 5. 确保页面可以滚动
    $('html, body').css({
        'overflow': '',
        'height': ''
    });
    
    console.log('界面修复完成');
    showMessage('✅ 界面已修复！<br><br>已清理模态框遮罩层<br>已恢复所有按钮状态<br><br>如果问题仍然存在，请刷新页面。', 'success');
});

// 添加键盘快捷键：Ctrl+Shift+R 修复界面
$(document).on('keydown', function(e) {
    // Ctrl+Shift+R
    if (e.ctrlKey && e.shiftKey && e.keyCode === 82) {
        e.preventDefault();
        $('#btn-fix-ui').click();
    }
});

// 更新统计信息
function updateStats(data) {
    if (data.total_count !== undefined) {
        $('#total-count').text(data.total_count);
    }
    if (data.valid_count !== undefined) {
        $('#valid-count').text(data.valid_count);
    }
    if (data.error_count !== undefined) {
        $('#error-count').text(data.error_count);
    }
    if (data.imported_count !== undefined) {
        $('#imported-count').text(data.imported_count);
    }
}

// 注意：renderTable 和 renderPagination 已在全局作用域中定义

// 轮询进度
function pollProgress() {
    if (!currentLogId) return;
    
    $.ajax({
        url: {$progressUrlJs},
        type: 'GET',
        data: {
            log_id: currentLogId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status == 1) {
                var data = response.data;
                var progress = data.progress || 0;
                
                $('#progress-bar').css('width', progress + '%').attr('aria-valuenow', progress);
                $('#progress-text').text(progress + '%');
                
                var detailHtml = '总记录数: ' + data.total_count + ', ';
                detailHtml += '已处理: ' + data.processed_count + ', ';
                detailHtml += '成功: <span class="text-success"><strong>' + data.success_count + '</strong></span>, ';
                detailHtml += '失败: <span class="text-danger"><strong>' + data.error_count + '</strong></span>, ';
                detailHtml += '跳过: ' + data.skip_count;
                
                // 显示分批次进度
                if (data.total_batches > 0) {
                    detailHtml += '<br><i class="fa fa-tasks"></i> ';
                    detailHtml += '批次进度: <strong>' + data.current_batch + '</strong>/' + data.total_batches;
                    detailHtml += ' （每批 ' + data.batch_size + ' 条）';
                    if (data.current_batch_progress) {
                        detailHtml += ' - ' + data.current_batch_progress;
                    }
                }
                
                if (data.current_row) {
                    detailHtml += '<br>当前: ' + data.current_row;
                }
                
                $('#progress-detail').html(detailHtml);
                
                // 更新统计
                updateStats(data);
                
                // 如果还在处理中，继续轮询
                if (data.status == 'P') {
                    setTimeout(pollProgress, 2000);
                } else {
                    $('#btn-start-import').prop('disabled', false).html('<i class="fa fa-upload"></i> 开始导入');
                    var msg = '成功：' + data.success_count + ' 条\\n失败：' + data.error_count + ' 条';
                    showMessage(msg, 'success', '导入完成');
                    loadPreviewData(currentPage);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('获取进度失败:', error);
            setTimeout(pollProgress, 2000);
        }
    });
}
JS;

// 替换变量（避免heredoc中的变量插值问题）
$js = str_replace(
    array(
        '{$fetchUrlJs}',
        '{$validateUrlJs}',
        '{$previewUrlJs}',
        '{$importUrlJs}',
        '{$progressUrlJs}',
        '{$csrfTokenJs}',
        '{$clearCacheUrlJs}'
    ),
    array(
        $fetchUrlJs,
        $validateUrlJs,
        $previewUrlJs,
        $importUrlJs,
        $progressUrlJs,
        $csrfTokenJs,
        $clearCacheUrlJs
    ),
    $js
);

// 将全局变量和全局函数定义注册为全局脚本（不包裹在 document.ready 中）
$globalJs = <<<GLOBALJS
// 全局变量（供所有函数访问）
var currentLogId = null;
var currentPage = 1;
var pageSize = 50;
var totalPages = 1;
var cityTree = [];
var staffList = [];
var selectedCityIds = new Set();
// 记录每种数据类型下已请求过的城市ID
var requestedData = {
    'client': new Set(),      // 客户
    'clientStore': new Set(), // 门店
    'cont': new Set(),        // 主合约
    'vir': new Set()          // 虚拟合约
};

// ========== 全局清理函数 ==========

/**
 * 清理可能残留的遮罩层和样式
 * 这个函数必须在全局作用域，以便所有地方都能访问
 */
function cleanupModalBackdrop() {
    // 清除所有可能残留的 Bootstrap modal-backdrop（包括fade和in状态）
    $('.modal-backdrop').remove();
    $('.modal-backdrop.fade').remove();
    $('.modal-backdrop.in').remove();
    
    // 移除 body 上的 modal-open class
    $('body').removeClass('modal-open');
    
    // 恢复 body 的样式
    $('body').css({
        'overflow': '',
        'padding-right': ''
    });
    
    // 注意：不清理 simple-backdrop，因为它由 showMessage/showConfirm 自己管理
}

/**
 * 启动全局backdrop监控
 * 定期检查并清理残留的backdrop
 */
function startBackdropMonitor() {
    setInterval(function() {
        // 检查是否有可见的modal
        var hasVisibleModal = false;
        $('.modal').each(function() {
            if ($(this).css('display') === 'block' || $(this).hasClass('in')) {
                hasVisibleModal = true;
                return false; // 跳出循环
            }
        });
        
        // 如果没有可见的modal，但有backdrop，则清理它
        if (!hasVisibleModal) {
            // 清理Bootstrap的backdrop
            if ($('.modal-backdrop').length > 0) {
                console.warn('检测到残留的Bootstrap backdrop，自动清理...');
                cleanupModalBackdrop();
            }
            // 清理自定义的simple-backdrop
            if ($('#simple-backdrop').hasClass('show')) {
                console.warn('检测到残留的simple-backdrop，自动清理...');
                $('#simple-backdrop').removeClass('show');
            }
        }
    }, 1000); // 每秒检查一次
}

// 页面加载完成后启动监控
$(document).ready(function() {
    startBackdropMonitor();
});

// ========== 全局消息提示函数 ==========

/**
 * 显示消息提示
 * @param {string} message - 消息内容
 * @param {string} type - 消息类型：success, error, warning, info
 * @param {string} title - 标题（可选）
 */
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
    
    // 先清理可能残留的所有backdrop
    cleanupModalBackdrop();
    $('#simple-backdrop').removeClass('show');
    
    $('#message-modal-icon').attr('class', 'fa ' + iconClass);
    $('#message-modal-title-text').text(title);
    $('#message-modal-header').attr('class', 'modal-header ' + headerClass);
    $('#message-modal-body').html(message.split('\\n').join('<br>'));
    $('#message-modal-btn-cancel').hide();
    
    // 移除之前的所有事件处理器
    $('#message-modal-btn-ok').off('click');
    $('#message-modal').off('hidden.bs.modal');
    
    // 绑定确定按钮事件
    $('#message-modal-btn-ok').on('click', function() {
        $('#message-modal').modal('hide');
        $('#simple-backdrop').removeClass('show');
    });
    
    // 点击遮罩层关闭对话框
    $('#simple-backdrop').off('click').on('click', function() {
        $('#message-modal').modal('hide');
        $(this).removeClass('show');
    });
    
    // 确保modal关闭时正确清理
    $('#message-modal').on('hidden.bs.modal', function() {
        // 清理事件处理器
        $('#message-modal-btn-ok').off('click');
        $('#simple-backdrop').off('click').removeClass('show');
        // 立即清理backdrop
        cleanupModalBackdrop();
    });
    
    // 显示自定义遮罩层
    $('#simple-backdrop').addClass('show');
    
    // 禁用backdrop，避免遮罩层残留问题
    $('#message-modal').modal({
        backdrop: false,
        keyboard: true,
        show: true
    });
}

/**
 * 显示确认对话框
 * @param {string} message - 消息内容
 * @param {function} onConfirm - 确认回调函数
 * @param {string} title - 标题（可选）
 */
function showConfirm(message, onConfirm, title) {
    title = title || '确认';
    
    // 先清理可能残留的所有backdrop
    cleanupModalBackdrop();
    $('#simple-backdrop').removeClass('show');
    
    $('#message-modal-icon').attr('class', 'fa fa-question-circle');
    $('#message-modal-title-text').text(title);
    $('#message-modal-header').attr('class', 'modal-header bg-warning');
    $('#message-modal-body').html(message.split('\\n').join('<br>'));
    $('#message-modal-btn-cancel').show();
    
    // 移除之前的所有事件处理器（包括确定和取消按钮）
    $('#message-modal-btn-ok').off('click');
    $('#message-modal-btn-cancel').off('click');
    $('#message-modal').off('hidden.bs.modal');
    
    // 绑定新的确认事件
    $('#message-modal-btn-ok').on('click', function() {
        // 立即移除遮罩
        $('#simple-backdrop').removeClass('show');
        $('#message-modal').modal('hide');
        
        if (typeof onConfirm === 'function') {
            // 使用 setTimeout 确保模态框完全关闭后再执行回调
            setTimeout(function() {
                // 在执行回调前，再次清理所有backdrop
                cleanupModalBackdrop();
                $('#simple-backdrop').removeClass('show');
                onConfirm();
            }, 200);
        }
    });
    
    // 绑定取消按钮事件（确保模态框正确关闭）
    $('#message-modal-btn-cancel').on('click', function() {
        $('#message-modal').modal('hide');
        $('#simple-backdrop').removeClass('show');
    });
    
    // 点击遮罩层不关闭确认对话框（防止误操作）
    $('#simple-backdrop').off('click');
    
    // 确保modal关闭时正确清理
    $('#message-modal').on('hidden.bs.modal', function() {
        // 清理所有事件处理器
        $('#message-modal-btn-ok').off('click');
        $('#message-modal-btn-cancel').off('click');
        $('#simple-backdrop').off('click').removeClass('show');
        // 立即清理backdrop
        cleanupModalBackdrop();
    });
    
    // 显示自定义遮罩层
    $('#simple-backdrop').addClass('show');
    
    // 禁用backdrop，避免遮罩层残留问题
    $('#message-modal').modal({
        backdrop: false,
        keyboard: true,
        show: true
    });
}

// ========== 全局工具函数 ==========

// 全局函数：获取当前数据类型对应的API地址（优先使用具体API，否则使用基础API）
function getCurrentApiUrl() {
    var migrationType = $('#migration_type').val();
    var specificApiUrl = $('#api_url_' + migrationType).val();
    var baseApiUrl = $('#api_base_url').val();
    
    // 优先使用具体的API地址，如果为空则使用基础API地址
    return specificApiUrl || baseApiUrl;
}

// 全局函数：获取基础API地址（用于加载城市和负责人）
function getBaseApiUrl() {
    return $('#api_base_url').val();
}

// 全局函数：保存配置到 localStorage
function saveConfig() {
    var config = {
        api_base_url: $('#api_base_url').val(),
        api_url_client: $('#api_url_client').val(),
        api_url_clientStore: $('#api_url_clientStore').val(),
        api_url_cont: $('#api_url_cont').val(),
        api_url_vir: $('#api_url_vir').val(),
        api_token: $('#api_token').val(),
        migration_type: $('#migration_type').val(),
        export_mode: $('#export_mode').val(),
        project_type: $('#project_type').val(), // ✅ 改为 project_type
        office_code_ids: $('#office_code_ids').val(),
        staff_ids: $('#staff_ids').val(),
        search_keyword: $('#search_keyword').val(),
        selected_city_ids: Array.from(selectedCityIds)
    };
    localStorage.setItem('dataMigrationConfig', JSON.stringify(config));
    console.log('配置已保存:', config);
}

// 保存已请求数据记录
function saveRequestedData() {
    var data = {};
    for (var type in requestedData) {
        data[type] = Array.from(requestedData[type]);
    }
    localStorage.setItem('dataMigrationRequestedData', JSON.stringify(data));
    console.log('已请求数据记录已保存:', data);
}

// 加载已请求数据记录
function loadRequestedData() {
    var dataStr = localStorage.getItem('dataMigrationRequestedData');
    if (dataStr) {
        try {
            var data = JSON.parse(dataStr);
            for (var type in data) {
                if (requestedData[type] && Array.isArray(data[type])) {
                    requestedData[type] = new Set(data[type]);
                }
            }
            console.log('已请求数据记录已加载:', data);
            return true;
        } catch(e) {
            console.error('加载已请求数据记录失败:', e);
            return false;
        }
    }
    return false;
}

// 全局函数：从 localStorage 加载配置
function loadConfig() {
    var configStr = localStorage.getItem('dataMigrationConfig');
    if (configStr) {
        try {
            var config = JSON.parse(configStr);
            $('#api_base_url').val(config.api_base_url || '');
            $('#api_url_client').val(config.api_url_client || '');
            $('#api_url_clientStore').val(config.api_url_clientStore || '');
            $('#api_url_cont').val(config.api_url_cont || '');
            $('#api_url_vir').val(config.api_url_vir || '');
            $('#api_token').val(config.api_token || '');
            $('#migration_type').val(config.migration_type || 'client');
            $('#export_mode').val(config.export_mode || 'city');
            $('#project_type').val(config.project_type || ''); // ✅ 改为 project_type
            $('#office_code_ids').val(config.office_code_ids || '');
            $('#staff_ids').val(config.staff_ids || '');
            $('#search_keyword').val(config.search_keyword || '');
            
            // 恢复选中的城市ID
            if (config.selected_city_ids && Array.isArray(config.selected_city_ids)) {
                selectedCityIds = new Set(config.selected_city_ids);
            }
            
            console.log('配置已加载:', config);
            return true;
        } catch(e) {
            console.error('加载配置失败:', e);
            return false;
        }
    }
    return false;
}

// 全局函数：清除保存的配置
function clearConfig() {
    localStorage.removeItem('dataMigrationConfig');
    localStorage.removeItem('dataMigrationRequestedData'); // 同时清除请求记录
    console.log('配置已清除');
    
    // 重置请求记录
    requestedData = {
        'client': new Set(),
        'clientStore': new Set(),
        'cont': new Set(),
        'vir': new Set()
    };
}

// 全局函数：渲染表格
function renderTable(data) {
    console.log('渲染表格，headers:', data.headers, ', rows数量:', data.rows ? data.rows.length : 0);
    
    if (!data.headers || !data.rows) {
        console.warn('表格数据不完整:', data);
        return;
    }
    
    // 渲染表头
    var headerHtml = '<tr>';
    headerHtml += '<th width="50">行号</th>';
    headerHtml += '<th width="50">状态</th>';
    for (var i = 0; i < data.headers.length; i++) {
        headerHtml += '<th>' + data.headers[i] + '</th>';
    }
    headerHtml += '<th width="200">错误信息</th>';
    headerHtml += '</tr>';
    $('#table-head').html(headerHtml);
    
    // 渲染表体
    var bodyHtml = '';
    for (var i = 0; i < data.rows.length; i++) {
        var row = data.rows[i];
        var rowClass = '';
        if (row.status == 'E') {
            rowClass = 'error-row';
        } else if (row.status == 'S') {
            rowClass = 'success-row';
        }
        
        bodyHtml += '<tr class="' + rowClass + '">';
        bodyHtml += '<td>' + row.row_index + '</td>';
        bodyHtml += '<td>' + (row.status == 'S' ? '<span class="label label-success">&#10004;</span>' : 
                              row.status == 'E' ? '<span class="label label-danger">&#10008;</span>' : 
                              '<span class="label label-default">-</span>') + '</td>';
        
        for (var j = 0; j < data.headers.length; j++) {
            var header = data.headers[j];
            var value = row.data[header] || '';
            bodyHtml += '<td>' + value + '</td>';
        }
        
        bodyHtml += '<td>' + (row.error_message || '') + '</td>';
        bodyHtml += '</tr>';
    }
    $('#table-body').html(bodyHtml);
}

// 全局函数：渲染分页
function renderPagination(data) {
    console.log('渲染分页，total_pages:', data.total_pages, ', current_page:', data.page);
    
    if (!data.total_pages || data.total_pages < 1) {
        console.warn('没有分页信息或总页数为0');
        $('#pagination-container').html('');
        return;
    }
    
    totalPages = data.total_pages;
    currentPage = data.page;
    
    var paginationHtml = '';
    if (totalPages > 1) {
        paginationHtml += '<ul class="pagination">';
        
        // 上一页
        if (currentPage > 1) {
            paginationHtml += '<li><a href="javascript:void(0);" onclick="loadPreviewData(' + (currentPage - 1) + ')">上一页</a></li>';
        }
        
        // 页码
        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, currentPage + 2);
        
        if (startPage > 1) {
            paginationHtml += '<li><a href="javascript:void(0);" onclick="loadPreviewData(1)">1</a></li>';
            if (startPage > 2) {
                paginationHtml += '<li class="disabled"><span>...</span></li>';
            }
        }
        
        for (var i = startPage; i <= endPage; i++) {
            if (i == currentPage) {
                paginationHtml += '<li class="active"><span>' + i + '</span></li>';
            } else {
                paginationHtml += '<li><a href="javascript:void(0);" onclick="loadPreviewData(' + i + ')">' + i + '</a></li>';
            }
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHtml += '<li class="disabled"><span>...</span></li>';
            }
            paginationHtml += '<li><a href="javascript:void(0);" onclick="loadPreviewData(' + totalPages + ')">' + totalPages + '</a></li>';
        }
        
        // 下一页
        if (currentPage < totalPages) {
            paginationHtml += '<li><a href="javascript:void(0);" onclick="loadPreviewData(' + (currentPage + 1) + ')">下一页</a></li>';
        }
        
        paginationHtml += '</ul>';
    }
    
    $('#pagination-container').html(paginationHtml);
}

// 全局变量：保存当前的筛选和搜索条件
var currentFilter = {
    status: '',
    search: ''
};

// 全局函数：加载预览数据（供分页 onclick 调用）
function loadPreviewData(page) {
    if (!currentLogId) {
        console.error('currentLogId 为空，无法加载数据');
        return;
    }
    
    console.log('加载预览数据，页码:', page, ', log_id:', currentLogId);
    
    $.ajax({
        url: {$previewUrlJs},
        type: 'GET',
        data: {
            log_id: currentLogId,
            page: page,
            page_size: pageSize,
            status: currentFilter.status,
            search: currentFilter.search
        },
        dataType: 'json',
        success: function(response) {
            console.log('预览数据响应:', response);
            if (response.status == 1) {
                console.log('数据:', response.data);
                console.log('总页数:', response.data.total_pages, '当前页:', response.data.page);
                renderTable(response.data);
                renderPagination(response.data);
                
                // 根据当前筛选状态显示/隐藏重试按钮
                if (currentFilter.status === 'E' && response.data.total_count > 0) {
                    $('#btn-retry-failed').show();
                    $('#btn-reset-failed-status').show();
                } else {
                    $('#btn-retry-failed').hide();
                    $('#btn-reset-failed-status').hide();
                }
            } else {
                console.error('获取预览数据失败:', response.message);
                showMessage('获取预览数据失败: ' + (response.message || '未知错误'), 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('加载预览数据失败:', error);
            console.error('响应内容:', xhr.responseText);
            showMessage('加载预览数据失败: ' + error, 'error');
        }
    });
}

// ========== 城市选择器全局函数 ==========

// 更新城市标签
function updateCityTags() {
    var container = $('#citySelectTags');
    var text = $('#citySelectText');
    var count = selectedCityIds.size;
    container.empty();
    
    if (count === 0) {
        text.text('请选择城市');
        $('#office_code_ids').val('');
    } else {
        text.text(count + '个城市已选择');
        
        var cityIds = Array.from(selectedCityIds);
        var typeNames = {'client': '客户', 'clientStore': '门店', 'cont': '主合约', 'vir': '虚拟合约'};
        
        cityIds.forEach(function(id) {
            var checkbox = $('#city_' + id);
            if (checkbox.length > 0) {
                var label = checkbox.next('label');
                if (label.length > 0) {
                    // 检查该城市在哪些类型下已请求过
                    var requestedTypes = [];
                    for (var type in requestedData) {
                        if (requestedData[type].has(id.toString())) {
                            requestedTypes.push(typeNames[type]);
                        }
                    }
                    
                    var isRequested = requestedTypes.length > 0;
                    var tagStyle = {
                        'background-color': isRequested ? '#e8f5e9' : '#f0f0f0',
                        'border': isRequested ? '1px solid #4caf50' : '1px solid #d0d0d0',
                        'padding': '5px 10px',
                        'border-radius': '2px',
                        'font-size': '13px',
                        'color': '#333',
                        'display': 'inline-flex',
                        'align-items': 'center',
                        'gap': '6px'
                    };
                    
                    var statusIcon = isRequested ? '<i class="fa fa-check-circle" style="color: #4caf50; margin-right: 3px;"></i>' : '';
                    var titleText = isRequested 
                        ? '已请求: ' + requestedTypes.join(', ') 
                        : '未请求数据';
                    
                    var tag = $('<div></div>')
                        .addClass('city-tag')
                        .css(tagStyle)
                        .attr('title', titleText)
                        .html(statusIcon + '<span>' + label.text().trim() + '</span><button type="button" onclick="window.removeCity(' + id + ')" style="background: none; border: none; padding: 0; color: #999; cursor: pointer; font-size: 16px; line-height: 1;">×</button>');
                    container.append(tag);
                }
            }
        });
        
        $('#office_code_ids').val(cityIds.join(','));
    }
}

// 移除城市
window.removeCity = function(id) {
    selectedCityIds.delete(id.toString());
    var checkbox = $('#city_' + id);
    if (checkbox.length > 0) {
        checkbox.prop('checked', false);
    }
    updateCityTags();
};

// 选择所有城市
window.selectAllCities = function() {
    $('#cityListContainer input[id^="city_"]').each(function() {
        if ($(this).closest('.city-option').css('display') !== 'none') {
            this.checked = true;
            selectedCityIds.add($(this).val());
        }
    });
    updateCityTags();
};

// 清除所有城市
window.clearAllCities = function() {
    $('#cityListContainer input[id^="city_"]').each(function() {
        this.checked = false;
    });
    selectedCityIds.clear();
    updateCityTags();
};

// 反选城市
window.invertCities = function() {
    $('#cityListContainer input[id^="city_"]').each(function() {
        if ($(this).closest('.city-option').css('display') !== 'none') {
            this.checked = !this.checked;
            if (this.checked) {
                selectedCityIds.add($(this).val());
            } else {
                selectedCityIds.delete($(this).val());
            }
        }
    });
    updateCityTags();
};

// 应用筛选
$('#btn-apply-filter').on('click', function() {
    currentFilter.status = $('#filter-status').val();
    currentFilter.search = $('#filter-search').val().trim();
    currentPage = 1; // 重置到第一页
    loadPreviewData(1);
});

// 重置筛选
$('#btn-reset-filter').on('click', function() {
    $('#filter-status').val('');
    $('#filter-search').val('');
    currentFilter.status = '';
    currentFilter.search = '';
    currentPage = 1;
    loadPreviewData(1);
});

// 按Enter键应用搜索
$('#filter-search').on('keypress', function(e) {
    if (e.which === 13) { // Enter键
        $('#btn-apply-filter').click();
    }
});

// 重新执行失败记录
$('#btn-retry-failed').on('click', function() {
    if (!currentLogId) {
        showMessage('请先获取数据', 'warning');
        return;
    }
    
    showConfirm('确定要重新执行所有失败的记录吗？', function() {
        var btnRetry = $('#btn-retry-failed');
        btnRetry.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 执行中...');
        
        $.ajax({
            url: '<?php echo Yii::app()->createUrl("dataMigration/retryFailed"); ?>',
            type: 'POST',
            data: {
                log_id: currentLogId
            },
            dataType: 'json',
            success: function(response) {
                btnRetry.prop('disabled', false).html('<i class="fa fa-refresh"></i> 重新执行失败记录');
                
                if (response.status == 1) {
                    var msg = '处理记录：' + (response.failed_count || 0) + ' 条\\n' +
                              '成功：' + (response.success_count || 0) + ' 条\\n' +
                              '失败：' + (response.error_count || 0) + ' 条';
                    showMessage(msg, 'success', '重新执行完成');
                    // 刷新数据
                    loadPreviewData(currentPage);
                } else {
                    showMessage('重新执行失败：' + (response.message || '未知错误'), 'error');
                }
            },
            error: function(xhr, status, error) {
                btnRetry.prop('disabled', false).html('<i class="fa fa-refresh"></i> 重新执行失败记录');
                showMessage('请求失败：' + error, 'error');
            }
        });
    }, '确认重新执行');
});

// 重置失败状态
$('#btn-reset-failed-status').on('click', function() {
    if (!currentLogId) {
        showMessage('请先获取数据', 'warning');
        return;
    }
    
    showConfirm('确定要重置所有失败记录的状态为"待处理"吗？\\n重置后可以重新导入。', function() {
        var btnReset = $('#btn-reset-failed-status');
        btnReset.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 重置中...');
        
        $.ajax({
            url: '<?php echo Yii::app()->createUrl("dataMigration/resetFailed"); ?>',
            type: 'POST',
            data: {
                log_id: currentLogId
            },
            dataType: 'json',
            success: function(response) {
                btnReset.prop('disabled', false).html('<i class="fa fa-repeat"></i> 重置失败状态');
                
                if (response.status == 1) {
                    showMessage('已重置 ' + (response.affected_rows || 0) + ' 条记录', 'success', '状态重置成功');
                    // 刷新数据
                    loadPreviewData(currentPage);
                } else {
                    showMessage('重置失败：' + (response.message || '未知错误'), 'error');
                }
            },
            error: function(xhr, status, error) {
                btnReset.prop('disabled', false).html('<i class="fa fa-repeat"></i> 重置失败状态');
                showMessage('请求失败：' + error, 'error');
            }
        });
    }, '确认重置状态');
});

// ==================== 批量按城市导入功能 ====================
var batchImportState = {
    isRunning: false,
    shouldStop: false,
    totalCities: 0,
    completedCities: 0,
    totalSuccess: 0,
    totalErrors: 0,
    startTime: null,
    cityResults: []
};

// 按城市批量导入
function startBatchImportByCity() {
    var migrationType = $('#migration_type').val();
    var exportMode = $('#export_mode').val();
    var projectType = $('#project_type').val(); // ✅ 项目类型（1=KA, 2=地推）
    
    // ✅ 验证：必须选择项目类型或城市
    if (exportMode === 'type') {
        // 按项目类型导出：必须选择项目类型
        if (!projectType) {
            showMessage('按项目类型导出时，必须选择项目类型（KA或地推）', 'warning');
            return;
        }
    } else if (exportMode === 'city') {
        // 按城市导出：必须选择城市
        var officeCodeIds = $('#office_code_ids').val();
        if (!officeCodeIds) {
            showMessage('按城市导出时，必须选择至少一个城市', 'warning');
            return;
        }
    } else if (exportMode === 'staff') {
        showMessage('批量导入功能不支持"按负责人"模式，请切换为"按项目类型"或"按城市"', 'warning');
        return;
    }
    
    var apiUrl = getCurrentApiUrl();
    if (!apiUrl) {
        showMessage('请先设置API地址', 'warning');
        return;
    }
    
    var officeCodeIds = $('#office_code_ids').val();
    var cityIds = officeCodeIds ? officeCodeIds.split(',').map(function(id) { return id.trim(); }) : [];
    var cityNames = [];
    
    if (cityIds.length > 0) {
    cityIds.forEach(function(id) {
        var checkbox = $('#city_' + id);
        if (checkbox.length > 0) {
            var label = checkbox.next('label');
            if (label.length > 0) {
                cityNames.push({ id: id, name: label.text().trim() });
            }
        }
    });
    } else if (projectType) {
        // ✅ 全量导出模式，不需要城市列表
        var typeText = projectType === '1' ? 'KA' : '地推';
        cityNames.push({ id: 'all', name: '全部' + typeText + '项目' });
    } else {
        showMessage('请先选择要导入的城市或项目类型', 'warning');
        return;
    }
    
    // ✅ 根据是否全量导出，显示不同的确认消息
    var confirmMessage = '';
    if (cityIds.length > 0) {
        confirmMessage = '确定要批量导入 ' + cityIds.length + ' 个城市的数据吗？<br><br>将逐个城市执行：获取数据 → 导入数据<br><br>注意：此过程可能需要较长时间，请耐心等待。';
    } else {
        var typeText = projectType === '1' ? 'KA' : '地推';
        confirmMessage = '确定要批量导入全部【' + typeText + '】项目的数据吗？<br><br>将执行：获取数据 → 导入数据<br><br>注意：此过程可能需要较长时间，请耐心等待。';
    }
    
    showConfirm(confirmMessage, function() {
        // 保存配置
        saveConfig();
        
        // 初始化状态
        var totalCities = cityNames.length;
        batchImportState = {
            isRunning: true,
            shouldStop: false,
            totalCities: totalCities,
            completedCities: 0,
            totalSuccess: 0,
            totalErrors: 0,
            startTime: new Date(),
            cityResults: [],
            lastErrorLogId: null  // 保存最后一个有错误的log_id
        };
        
        // 显示进度面板
        $('#batch-import-progress').show();
        $('#batch-total-cities').text(totalCities);
        $('#batch-completed-cities').text(0);
        $('#batch-processing-city').text('-');
        $('#batch-total-success').text(0);
        $('#batch-total-errors').text(0);
        $('#batch-total-time').text('0s');
        $('#batch-progress-bar').css('width', '0%').text('0%').addClass('active');
        $('#batch-city-list').empty();
        $('#btn-stop-batch').show();
        $('#btn-batch-import-by-city').prop('disabled', true);
        
        // 开始批量处理
        processCitiesBatch(cityNames, 0, migrationType, apiUrl, projectType);
    });
}

// 停止批量导入
function stopBatchImport() {
    showConfirm('确定要停止批量导入吗？<br><br>当前城市会完成处理，之后的城市将被跳过。', function() {
        batchImportState.shouldStop = true;
        $('#btn-stop-batch').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 正在停止...');
    });
}

// 批量处理城市
function processCitiesBatch(cities, index, migrationType, apiUrl, projectType) {
    if (index >= cities.length || batchImportState.shouldStop) {
        // 所有城市处理完成或被停止
        finishBatchImport();
        return;
    }
    
    var city = cities[index];
    var progress = Math.round((index / cities.length) * 100);
    
    $('#batch-progress-bar').css('width', progress + '%').text(progress + '%');
    $('#batch-processing-city').text(city.name);
    
    // 更新耗时
    var elapsed = Math.round((new Date() - batchImportState.startTime) / 1000);
    $('#batch-total-time').text(elapsed + 's');
    
    // 添加城市处理记录
    var cityItem = $('<div></div>')
        .attr('id', 'city-item-' + index)
        .css({
            'padding': '12px',
            'border-bottom': '1px solid #eee',
            'background': '#f9f9f9'
        })
        .html('<strong>[' + (index + 1) + '/' + cities.length + '] ' + city.name + '</strong> <span style="color: #999;"><i class="fa fa-spinner fa-spin"></i> 处理中...</span>');
    $('#batch-city-list').append(cityItem);
    
    // 滚动到最新
    $('#batch-city-list').scrollTop($('#batch-city-list')[0].scrollHeight);
    
    // 处理单个城市
    processSingleCity(city, migrationType, apiUrl, projectType, function(result) {
        batchImportState.completedCities++;
        batchImportState.totalSuccess += result.success;
        batchImportState.totalErrors += result.errors;
        batchImportState.cityResults.push({
            city: city.name,
            logId: result.logId || null,
            ...result
        });
        
        // 保存最后一个有错误的log_id，用于查看失败详情
        if (result.errors > 0 && result.logId) {
            batchImportState.lastErrorLogId = result.logId;
        }
        
        // 更新统计
        $('#batch-completed-cities').text(batchImportState.completedCities);
        $('#batch-total-success').text(batchImportState.totalSuccess);
        $('#batch-total-errors').text(batchImportState.totalErrors);
        
        // 更新城市状态
        var statusHtml = result.status === 'success' 
            ? '<span style="color: #00a65a;"><i class="fa fa-check-circle"></i> 成功: ' + result.success + ', 失败: ' + result.errors + '</span>'
            : '<span style="color: #dd4b39;"><i class="fa fa-times-circle"></i> 错误: ' + result.message + '</span>';
        
        $('#city-item-' + index)
            .css('background', result.status === 'success' ? '#dff0d8' : '#f2dede')
            .html('<strong>[' + (index + 1) + '/' + cities.length + '] ' + city.name + '</strong> ' + statusHtml);
        
        // 继续处理下一个城市
        setTimeout(function() {
            processCitiesBatch(cities, index + 1, migrationType, apiUrl, projectType);
        }, 500); // 每个城市之间间隔0.5秒
    });
}

// 处理单个城市（支持分页）
function processSingleCity(city, migrationType, apiUrl, projectType, callback) {
    var apiToken = $('#api_token').val();
    
    // ✅ 根据城市ID判断是全量导出还是单城市导出
    var isFullExport = (city.id === 'all');
    
    // ✅ 全量导出使用分页，单城市导出一次性拉取
    // 注意：当前是内存分页，增大分页大小可以减少总页数，提高效率
    var pageSize = isFullExport ? 2000 : 10000;
    
    var baseFilterParams = {
        export_mode: isFullExport ? 'type' : 'city',
        office_code_ids: isFullExport ? [] : [parseInt(city.id)],
        staff_ids: [],
        search_keyword: '',
        page_size: pageSize
    };
    
    // ✅ 所有迁移类型都传递 type 参数
    if (projectType) {
        baseFilterParams.type = projectType;
    }
    
    // ✅ 如果是全量导出，使用分页处理
    if (isFullExport) {
        processCityWithPagination(city, migrationType, apiUrl, apiToken, baseFilterParams, callback);
    } else {
        // 单城市导出，直接处理
        processCitySinglePage(city, migrationType, apiUrl, apiToken, baseFilterParams, 1, callback);
    }
}

// 分页处理（用于全量导出）
function processCityWithPagination(city, migrationType, apiUrl, apiToken, baseFilterParams, finalCallback) {
    var totalSuccess = 0;
    var totalErrors = 0;
    var currentPage = 1;
    var totalPages = 1;
    var allLogIds = [];
    
    function processNextPage() {
        var filterParams = Object.assign({}, baseFilterParams, { page: currentPage });
        
        var params = {
            api_url: apiUrl,
            api_config: JSON.stringify({
                auth_type: 'token',
                token: apiToken || ''
            }),
            migration_type: migrationType,
            filter_params: JSON.stringify(filterParams)
        };
        
        // 更新进度提示
        var progressText = totalPages > 1 ? ' (第' + currentPage + '/' + totalPages + '页)' : '';
        $('#batch-processing-city').text(city.name + progressText);
        
        $.ajax({
            url: {$fetchUrlJs},
            type: 'POST',
            data: params,
            dataType: 'json',
            success: function(response) {
                if (response.status == 1) {
                    var logId = response.data.log_id;
                    allLogIds.push(logId);
                    
                    // 第一页时计算总页数
                    if (currentPage === 1 && response.data.total_count) {
                        totalPages = Math.ceil(response.data.total_count / baseFilterParams.page_size);
    }
                    
                    // Step 2: 验证数据
                    $.ajax({
                        url: {$validateUrlJs},
                        type: 'POST',
                        data: { log_id: logId },
                        dataType: 'json',
                        success: function(validateResponse) {
                            // Step 3: 导入数据
                            $.ajax({
                                url: {$importUrlJs},
                                type: 'POST',
                                data: {
                                    log_id: logId,
                                    import_mode: 'all'
                                },
                                dataType: 'json',
                                success: function(importResponse) {
                                    if (importResponse.status == 1) {
                                        totalSuccess += (importResponse.data.success_count || 0);
                                        totalErrors += (importResponse.data.error_count || 0);
                                    }
                                    
                                    // 判断是否还有下一页
                                    if (currentPage < totalPages) {
                                        currentPage++;
                                        setTimeout(processNextPage, 1000); // 每页间隔1秒
                                    } else {
                                        // 所有页处理完成
                                        finalCallback({
                                            status: 'success',
                                            success: totalSuccess,
                                            errors: totalErrors,
                                            logId: allLogIds[allLogIds.length - 1], // 返回最后一个logId
                                            totalPages: totalPages
                                        });
                                    }
                                },
                                error: function(xhr, status, error) {
                                    finalCallback({
                                        status: 'error',
                                        message: '第' + currentPage + '页导入失败: ' + error,
                                        success: totalSuccess,
                                        errors: totalErrors,
                                        logId: logId
                                    });
                                }
                            });
                        },
                        error: function(xhr, status, error) {
                            finalCallback({
                                status: 'error',
                                message: '第' + currentPage + '页验证失败: ' + error,
                                success: totalSuccess,
                                errors: totalErrors,
                                logId: logId
                            });
                        }
                    });
                } else {
                    finalCallback({
                        status: 'error',
                        message: '第' + currentPage + '页获取数据失败: ' + (response.message || '未知错误'),
                        success: totalSuccess,
                        errors: totalErrors
                    });
                }
            },
            error: function(xhr, status, error) {
                finalCallback({
                    status: 'error',
                    message: '第' + currentPage + '页获取数据请求失败: ' + error,
                    success: totalSuccess,
                    errors: totalErrors
                });
            }
        });
    }
    
    // 开始处理第一页
    processNextPage();
}

// 单页处理（用于单城市导出）
function processCitySinglePage(city, migrationType, apiUrl, apiToken, filterParams, page, callback) {
    filterParams.page = page;
    
    var params = {
        api_url: apiUrl,
        api_config: JSON.stringify({
            auth_type: 'token',
            token: apiToken || ''
        }),
        migration_type: migrationType,
        filter_params: JSON.stringify(filterParams)
    };
    
    // Step 1: 获取数据
    $.ajax({
        url: {$fetchUrlJs},
        type: 'POST',
        data: params,
        dataType: 'json',
        success: function(response) {
            if (response.status == 1) {
                var logId = response.data.log_id;
                
                // Step 2: 验证数据
                $.ajax({
                    url: {$validateUrlJs},
                    type: 'POST',
                    data: {
                        log_id: logId
                    },
                    dataType: 'json',
                    success: function(validateResponse) {
                        // Step 3: 导入数据（包括验证失败的，让导入时再次尝试并记录详细错误）
                        $.ajax({
                            url: {$importUrlJs},
                            type: 'POST',
                            data: {
                                log_id: logId,
                                import_mode: 'all'
                            },
                            dataType: 'json',
                            success: function(importResponse) {
                                if (importResponse.status == 1) {
                                    callback({
                                        status: 'success',
                                        success: importResponse.data.success_count || 0,
                                        errors: importResponse.data.error_count || 0,
                                        logId: logId
                                    });
                                } else {
                                    callback({
                                        status: 'error',
                                        message: '导入失败: ' + (importResponse.message || '未知错误'),
                                        success: 0,
                                        errors: 0,
                                        logId: logId
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                callback({
                                    status: 'error',
                                    message: '导入请求失败: ' + error,
                                    success: 0,
                                    errors: 0,
                                    logId: logId
                                });
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        callback({
                            status: 'error',
                            message: '验证请求失败: ' + error,
                            success: 0,
                            errors: 0,
                            logId: logId
                        });
                    }
                });
            } else {
                callback({
                    status: 'error',
                    message: '获取数据失败: ' + (response.message || '未知错误'),
                    success: 0,
                    errors: 0
                });
            }
        },
        error: function(xhr, status, error) {
            callback({
                status: 'error',
                message: '获取数据请求失败: ' + error,
                success: 0,
                errors: 0
            });
        }
    });
}

// 完成批量导入
function finishBatchImport() {
    batchImportState.isRunning = false;
    var elapsed = Math.round((new Date() - batchImportState.startTime) / 1000);
    
    $('#batch-progress-bar')
        .css('width', '100%')
        .text('100%')
        .removeClass('active');
    
    $('#batch-processing-city').text('已完成');
    $('#batch-total-time').text(elapsed + 's');
    $('#btn-stop-batch').hide();
    $('#btn-batch-import-by-city').prop('disabled', false);
    
    var message = '批量导入完成！\\n\\n' +
                  '总城市数: ' + batchImportState.totalCities + '\\n' +
                  '已完成: ' + batchImportState.completedCities + '\\n' +
                  '总成功: ' + batchImportState.totalSuccess + '\\n' +
                  '总失败: ' + batchImportState.totalErrors + '\\n' +
                  '总耗时: ' + elapsed + '秒';
    
    if (batchImportState.shouldStop) {
        message = '批量导入已停止\\n\\n' + message;
    }
    
    // 显示完成提示
    showMessage(message.replace(/\\n/g, '<br>'), 'success');
    
    // 如果有失败记录，提供查看选项
    if (batchImportState.totalErrors > 0) {
        showConfirm('检测到有失败记录。<br><br>是否立即查看失败详情？<br>（也可以稍后在"任务列表"中查看）', function() {
            if (batchImportState.lastErrorLogId) {
                // 显示失败记录详情
                showErrorDetails(batchImportState.lastErrorLogId);
            }
        });
    }
}

// 显示错误详情（筛选失败记录）
function showErrorDetails(logId) {
    if (!logId) {
        showMessage('没有找到导入日志ID', 'error');
        return;
    }
    
    // 加载失败的数据
    currentLogId = logId;
    currentPage = 1;
    
    // 设置筛选状态为失败
    currentFilter.status = 'E';
    currentFilter.search = '';
    $('#filter-status').val('E');
    $('#filter-search').val('');
    
    // 显示预览面板
    $('#preview-panel').show();
    
    // 加载失败记录
    loadPreviewData(1);
    
    // 滚动到表格位置
    $('html, body').animate({
        scrollTop: $('#preview-panel').offset().top - 100
    }, 500);
}

// ==================== 异步导入功能 ====================

// 创建异步导入任务
$('#btn-create-async-task').on('click', function() {
    // 先清理可能残留的backdrop，确保页面可交互
    cleanupModalBackdrop();
    
    var migrationType = $('#migration_type').val();
    var exportMode = $('#export_mode').val();
    var projectType = $('#project_type').val(); // ✅ 项目类型（1=KA, 2=地推）
    
    // ✅ 验证：必须选择项目类型或城市
    if (exportMode === 'type') {
        // 按项目类型导出：必须选择项目类型
        if (!projectType) {
            showMessage('按项目类型导出时，必须选择项目类型（KA或地推）', 'warning');
            return;
        }
    } else if (exportMode === 'city') {
        // 按城市导出：必须选择城市
        var officeCodeIds = $('#office_code_ids').val();
        if (!officeCodeIds) {
            showMessage('按城市导出时，必须选择至少一个城市', 'warning');
            return;
        }
    } else if (exportMode === 'staff') {
        showMessage('异步导入功能不支持"按负责人"模式，请切换为"按项目类型"或"按城市"', 'warning');
        return;
    }
    
    var apiUrl = getCurrentApiUrl();
    if (!apiUrl) {
        showMessage('请先设置API地址', 'warning');
        return;
    }
    
    var officeCodeIds = $('#office_code_ids').val();
    var cityIds = officeCodeIds ? officeCodeIds.split(',').map(function(id) { return parseInt(id.trim()); }) : [];
    var btnAsync = $(this);
    
    // ✅ 根据是否选择城市，显示不同的确认消息
    var confirmMessage = '';
    if (projectType && cityIds.length === 0) {
        var typeText = projectType === '1' ? 'KA' : '地推';
        confirmMessage = '确定要创建异步导入任务吗？<br><br>将导入全部【' + typeText + '】项目的数据<br><br>任务创建后会在后台处理，您可以关闭页面<br>可以稍后查看任务进度';
    } else {
        confirmMessage = '确定要创建异步导入任务吗？<br><br>将导入 ' + cityIds.length + ' 个城市的数据<br><br>任务创建后会在后台处理，您可以关闭页面<br>可以稍后查看任务进度';
    }
    
    showConfirm(confirmMessage, function() {
        var apiToken = $('#api_token').val();
        
        // ✅ 全量导出使用较小的分页大小（2000条/页），单城市导出使用较大的分页大小（10000条/页）
        // 注意：当前是内存分页，增大分页大小可以减少总页数，提高效率
        var isFullExport = (projectType && cityIds.length === 0);
        var pageSize = isFullExport ? 2000 : 10000;
        
        var filterParams = {
            export_mode: cityIds.length > 0 ? 'city' : 'type',
            office_code_ids: cityIds,
            staff_ids: [],
            search_keyword: '',
            page: 1,
            page_size: pageSize
        };
        
        // ✅ 所有迁移类型都传递 type 参数
        if (projectType) {
            filterParams.type = projectType;
        }
        
        var params = {
            migration_type: migrationType,
            api_url: apiUrl,
            api_config: JSON.stringify({
                auth_type: 'token',
                token: apiToken || ''
            }),
            filter_params: JSON.stringify(filterParams),
            priority: 5
        };
        
        // 只禁用当前按钮，不影响其他按钮
        btnAsync.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 创建中...');
        
        $.ajax({
            url: {$createAsyncTaskUrlJs},
            type: 'POST',
            data: params,
            dataType: 'json',
            success: function(response) {
                // 恢复按钮状态
                btnAsync.prop('disabled', false).html('<i class="fa fa-cloud-upload"></i> 创建异步任务');
                
                // 彻底清理所有可能残留的遮罩层
                cleanupModalBackdrop();
                $('#simple-backdrop').removeClass('show');
                
                if (response.status == 1) {
                    // 稍微延时，确保之前的遮罩完全清除后再显示新消息
                    setTimeout(function() {
                        showMessage('✅ 任务创建成功！<br><br>📋 任务编号：' + response.data.task_code + '<br>🏙️ 总城市数：' + response.data.total_cities + '<br><br>💡 任务将在后台自动处理，您可以关闭页面<br>📊 稍后可在"任务列表"中查看进度', 'success');
                        
                        // 记录当前类型下已请求过的城市ID
                        if (officeCodeIds && requestedData[migrationType]) {
                            officeCodeIds.split(',').forEach(function(id) {
                                requestedData[migrationType].add(id.trim());
                            });
                            updateCityTags(); // 刷新城市标签显示
                            saveRequestedData(); // 持久化
                        }
                        
                        // 显示任务监控面板
                        showTaskMonitor(response.data.task_id);
                    }, 100);
                } else {
                    setTimeout(function() {
                        showMessage('❌ 任务创建失败<br><br>' + response.message, 'error');
                    }, 100);
                }
            },
            error: function(xhr, status, error) {
                // 恢复按钮状态
                btnAsync.prop('disabled', false).html('<i class="fa fa-cloud-upload"></i> 创建异步任务');
                // 确保清理backdrop
                cleanupModalBackdrop();
                showMessage('请求失败：' + error, 'error');
            }
        });
    });
});

// 显示任务监控面板
function showTaskMonitor(taskId) {
    // 确保清理所有模态框遮罩（延时确保之前的模态框完全关闭）
    setTimeout(function() {
        cleanupModalBackdrop();
        $('#simple-backdrop').removeClass('show');
    }, 800);
    
    $('#async-task-monitor').show();
    $('#monitor-task-id').val(taskId);
    
    // 开始轮询任务状态
    startTaskPolling(taskId);
}

// 开始轮询任务状态
var taskPollingInterval = null;
function startTaskPolling(taskId) {
    // 清除之前的轮询
    if (taskPollingInterval) {
        clearInterval(taskPollingInterval);
    }
    
    // 立即查询一次
    updateTaskStatus(taskId);
    
    // 每5秒查询一次
    taskPollingInterval = setInterval(function() {
        updateTaskStatus(taskId);
    }, 5000);
}

// 停止轮询
function stopTaskPolling() {
    if (taskPollingInterval) {
        clearInterval(taskPollingInterval);
        taskPollingInterval = null;
    }
}

// 更新任务状态
function updateTaskStatus(taskId) {
    $.ajax({
        url: {$getTaskStatusUrlJs},
        type: 'GET',
        data: { task_id: taskId },
        dataType: 'json',
        success: function(response) {
            if (response.status == 1) {
                var task = response.data;
                
                // 更新界面
                $('#task-status-text').text(task.status_text);
                $('#task-progress').text(task.progress + '%');
                $('#task-progress-bar').css('width', task.progress + '%').text(task.progress + '%');
                $('#task-total-cities').text(task.total_cities);
                $('#task-completed-cities').text(task.completed_cities);
                $('#task-success-count').text(task.success_count);
                $('#task-error-count').text(task.error_count);
                
                if (task.current_city) {
                    $('#task-current-city').text(task.current_city);
                }
                
                // 如果任务已完成或失败，停止轮询
                if (task.task_status == 2 || task.task_status == 3 || task.task_status == 4) {
                    stopTaskPolling();
                    
                    if (task.task_status == 2) {
                        showMessage('任务完成！总成功：' + task.success_count + '总失败：' + task.error_count, 'success');
                    } else if (task.task_status == 3) {
                        showMessage('任务失败：' + task.error_message, 'error');
                    }
                }
            }
        }
    });
}

// 查看任务详情
$('#btn-view-task-details').on('click', function() {
    var taskId = $('#monitor-task-id').val();
    if (!taskId) {
        showMessage('未找到任务ID', 'warning');
        return;
    }
    
    // 显示模态框
    $('#task-details-modal').modal('show');
    $('#task-details-content').html('<p class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> 加载中...</p>');
    
    $.ajax({
        url: {$getTaskDetailsUrlJs},
        type: 'GET',
        data: { task_id: taskId },
        dataType: 'json',
        success: function(response) {
            if (response.status == 1) {
                var details = response.data;
                var html = '<div class="table-responsive">';
                html += '<table class="table table-bordered table-striped table-hover">';
                html += '<thead>';
                html += '<tr>';
                html += '<th style="width: 150px;">城市</th>';
                html += '<th style="width: 80px; text-align: center;">状态</th>';
                html += '<th style="width: 80px; text-align: center;">成功</th>';
                html += '<th style="width: 80px; text-align: center;">失败</th>';
                html += '<th style="width: 100px; text-align: center;">开始时间</th>';
                html += '<th style="width: 100px; text-align: center;">结束时间</th>';
                html += '<th style="width: 80px; text-align: center;">耗时</th>';
                html += '<th>错误信息</th>';
                html += '</tr>';
                html += '</thead>';
                html += '<tbody>';
                
                if (details.length == 0) {
                    html += '<tr><td colspan="8" class="text-center text-muted">暂无数据</td></tr>';
                } else {
                    details.forEach(function(item) {
                        var statusText = ['待处理', '处理中', '成功', '失败'][item.status] || '未知';
                        var statusClass = '';
                        if (item.status == 2) statusClass = 'success';
                        else if (item.status == 3) statusClass = 'danger';
                        else if (item.status == 1) statusClass = 'warning';
                        
                        var elapsed = '-';
                        if (item.start_time && item.end_time) {
                            var start = new Date(item.start_time.replace(/-/g, '/'));
                            var end = new Date(item.end_time.replace(/-/g, '/'));
                            var seconds = Math.round((end - start) / 1000);
                            if (seconds > 60) {
                                elapsed = Math.floor(seconds / 60) + 'm' + (seconds % 60) + 's';
                            } else {
                                elapsed = seconds + 's';
                            }
                        } else if (item.start_time) {
                            elapsed = '处理中...';
                        }
                        
                        var startTime = item.start_time ? item.start_time.substring(11, 19) : '-';
                        var endTime = item.end_time ? item.end_time.substring(11, 19) : '-';
                        var errorMsg = item.error_message || '-';
                        
                        // 成功或失败时高亮显示
                        html += '<tr class="' + statusClass + '">';
                        html += '<td><strong>' + (item.city_name || '未知') + '</strong></td>';
                        html += '<td style="text-align: center;"><span class="label label-' + (item.status == 2 ? 'success' : (item.status == 3 ? 'danger' : (item.status == 1 ? 'warning' : 'default'))) + '">' + statusText + '</span></td>';
                        html += '<td style="text-align: center;"><strong style="color: #00a65a;">' + (item.success_count || 0) + '</strong></td>';
                        html += '<td style="text-align: center;"><strong style="color: #dd4b39;">' + (item.error_count || 0) + '</strong></td>';
                        html += '<td style="text-align: center; font-size: 11px;">' + startTime + '</td>';
                        html += '<td style="text-align: center; font-size: 11px;">' + endTime + '</td>';
                        html += '<td style="text-align: center;">' + elapsed + '</td>';
                        html += '<td style="font-size: 12px; color: ' + (item.error_message ? '#dd4b39' : '#666') + ';">' + errorMsg + '</td>';
                        html += '</tr>';
                    });
                }
                
                html += '</tbody>';
                html += '</table>';
                html += '</div>';
                
                // 添加汇总信息
                var totalSuccess = 0;
                var totalError = 0;
                var completedCount = 0;
                details.forEach(function(item) {
                    totalSuccess += item.success_count || 0;
                    totalError += item.error_count || 0;
                    if (item.status == 2 || item.status == 3) completedCount++;
                });
                
                html += '<div class="alert alert-info" style="margin-top: 15px;">';
                html += '<strong>统计汇总：</strong> ';
                html += '总城市数：' + details.length + ' | ';
                html += '已完成：' + completedCount + ' | ';
                html += '总成功：<span style="color: #00a65a;">' + totalSuccess + '</span> | ';
                html += '总失败：<span style="color: #dd4b39;">' + totalError + '</span>';
                html += '</div>';
                
                $('#task-details-content').html(html);
            } else {
                $('#task-details-content').html('<div class="alert alert-danger">加载失败：' + (response.message || '未知错误') + '</div>');
            }
        },
        error: function(xhr, status, error) {
            $('#task-details-content').html('<div class="alert alert-danger">请求失败：' + error + '</div>');
        }
    });
});

GLOBALJS;

$globalJs = str_replace(
    array(
        '{$previewUrlJs}',
        '{$createAsyncTaskUrlJs}',
        '{$getTaskStatusUrlJs}',
        '{$getTaskDetailsUrlJs}',
        '{$fetchUrlJs}',
        '{$validateUrlJs}',
        '{$importUrlJs}'
    ),
    array(
        $previewUrlJs,
        $createAsyncTaskUrlJs,
        $getTaskStatusUrlJs,
        $getTaskDetailsUrlJs,
        $fetchUrlJs,
        $validateUrlJs,
        $importUrlJs
    ),
    $globalJs
);

// 注册全局脚本（POS_END，不包裹在 document.ready 中）
Yii::app()->clientScript->registerScript('dataMigration-global', $globalJs, CClientScript::POS_END);

// 注册主脚本（POS_READY，包裹在 document.ready 中）
Yii::app()->clientScript->registerScript('dataMigration', $js, CClientScript::POS_READY);
?>


