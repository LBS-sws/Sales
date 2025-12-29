<?php
$this->pageTitle=Yii::app()->name . ' - 主合同合并处理中';
?>

<style>
.progress-container {
    max-width: 800px;
    margin: 50px auto;
}
.log-box {
    max-height: 400px;
    overflow-y: auto;
    background: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
    margin-top: 20px;
}
.log-item {
    padding: 8px;
    border-left: 3px solid #3c8dbc;
    margin-bottom: 8px;
    background: white;
}
.log-item.success {
    border-left-color: #00a65a;
}
.log-item.error {
    border-left-color: #dd4b39;
}
</style>

<section class="content">
    <div class="progress-container">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-cog fa-spin"></i> 正在处理主合同合并...
                </h3>
            </div>
            <div class="box-body">
                <div class="alert alert-info">
                    <p><strong>任务ID：</strong><?php echo $taskId; ?></p>
                    <p><strong>目标主合同ID：</strong><?php echo $model->target_cont_id; ?></p>
                    <p><strong>来源主合同数量：</strong><?php echo count($model->source_cont_ids); ?> 个</p>
                </div>
                
                <h4>处理进度</h4>
                <div class="progress">
                    <div id="progress-bar" class="progress-bar progress-bar-striped active" role="progressbar" 
                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                        <span id="progress-text">0%</span>
                    </div>
                </div>
                
                <p id="current-step" class="text-muted">正在准备...</p>
                
                <div class="log-box" id="log-container" style="display:none;">
                    <h5><i class="fa fa-list"></i> 操作日志：</h5>
                    <div id="log-content"></div>
                </div>
                
                <div id="result-box" style="display:none; margin-top:20px;">
                    <div class="text-center">
                        <button class="btn btn-primary btn-lg" id="btn-view-target">
                            <i class="fa fa-eye"></i> 查看目标主合同
                        </button>
                        <button class="btn btn-default btn-lg" onclick="window.location.href='<?php echo Yii::app()->createUrl('contHead/index'); ?>'">
                            <i class="fa fa-list"></i> 返回主合同列表
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
var taskId = <?php echo $taskId; ?>;
var executeUrl = '<?php echo Yii::app()->createUrl('contHead/ajaxExecuteMerge'); ?>';
var statusUrl = '<?php echo Yii::app()->createUrl('contHead/ajaxGetTaskStatus'); ?>';
var detailUrl = '<?php echo Yii::app()->createUrl('contHead/detail'); ?>';
var polling = null;

$(document).ready(function(){
    // 启动任务执行
    $.ajax({
        type: 'POST',
        url: executeUrl,
        data: {task_id: taskId},
        dataType: 'json',
        success: function(response){
            console.log('任务已提交');
            // 开始轮询状态
            startPolling();
        },
        error: function(){
            alert('任务提交失败，请重试');
            window.location.href = '<?php echo Yii::app()->createUrl('contHead/index'); ?>';
        }
    });
    
    // 开始轮询任务状态
    function startPolling(){
        polling = setInterval(checkTaskStatus, 1000); // 每秒查询一次
    }
    
    // 查询任务状态
    function checkTaskStatus(){
        $.ajax({
            type: 'GET',
            url: statusUrl,
            data: {task_id: taskId},
            dataType: 'json',
            success: function(data){
                if (data.status == 'not_found') {
                    clearInterval(polling);
                    alert('任务不存在');
                    return;
                }
                
                // 更新进度
                updateProgress(data.progress, data.current_step);
                
                // 更新日志
                if (data.logs) {
                    try {
                        var logs = typeof data.logs === 'string' ? JSON.parse(data.logs) : data.logs;
                        updateLogs(logs);
                    } catch (e) {
                        console.error('日志解析失败', e);
                    }
                }
                
                // 检查是否完成
                if (data.status == 'completed') {
                    clearInterval(polling);
                    showSuccess(data);
                } else if (data.status == 'failed') {
                    clearInterval(polling);
                    showError(data);
                }
            },
            error: function(){
                console.error('查询任务状态失败');
            }
        });
    }
    
    // 更新进度条
    function updateProgress(progress, step){
        progress = progress || 0;
        $('#progress-bar').css('width', progress + '%');
        $('#progress-bar').attr('aria-valuenow', progress);
        $('#progress-text').text(progress + '%');
        $('#current-step').text(step || '处理中...');
    }
    
    // 更新日志显示
    function updateLogs(logs){
        if (!logs || logs.length === 0) return;
        
        $('#log-container').show();
        var html = '';
        $.each(logs, function(i, log){
            var statusClass = log.status == 'success' ? 'success' : 'error';
            var icon = log.status == 'success' ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>';
            html += '<div class="log-item ' + statusClass + '">';
            html += icon + ' <strong>' + log.step + '</strong>: ' + log.count + '条';
            if (log.msg) {
                html += ' <small class="text-muted">(' + log.msg + ')</small>';
            }
            html += '</div>';
        });
        $('#log-content').html(html);
        
        // 滚动到底部
        var logBox = document.getElementById('log-container');
        logBox.scrollTop = logBox.scrollHeight;
    }
    
    // 显示成功结果
    function showSuccess(data){
        $('.box-header h3').html('<i class="fa fa-check-circle text-success"></i> 合并完成！');
        $('#progress-bar').removeClass('active').addClass('progress-bar-success');
        $('#current-step').html('<span class="text-success"><i class="fa fa-check"></i> 所有操作已成功完成</span>');
        $('#result-box').show();
        $('#btn-view-target').attr('onclick', 'window.location.href="' + detailUrl + '?index=' + data.target_cont_id + '"');
    }
    
    // 显示失败结果
    function showError(data){
        $('.box-header h3').html('<i class="fa fa-times-circle text-danger"></i> 合并失败');
        $('#progress-bar').removeClass('active').addClass('progress-bar-danger');
        var errorMsg = data.error_message || '未知错误';
        $('#current-step').html('<span class="text-danger"><i class="fa fa-times"></i> ' + errorMsg + '</span>');
        $('#result-box').show();
        $('#btn-view-target').hide();
    }
});
</script>

