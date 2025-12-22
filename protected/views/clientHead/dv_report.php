<?php
/**
 * 报价
 */
?>

<div  style="padding-top: 15px;">
    <!-- 搜索框 -->
    <div class="input-group" style="margin-bottom: 10px; max-width: 400px;">
        <input type="text" class="form-control" id="reportSearch" placeholder="搜索线索编号/客户名称" />
        <span class="input-group-btn">
            <button class="btn btn-default" type="button" id="reportSearchBtn">
                <i class="fa fa-search"></i> 搜索
            </button>
            <button class="btn btn-default" type="button" id="reportClearBtn">
                <i class="fa fa-remove"></i> 清空
            </button>
        </span>
    </div>
    
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue','clue code'); ?></th>
                <th><?php echo Yii::t('clue','clue name'); ?></th>
                <th><?php echo Yii::t('clue','clue type'); ?></th>
                <th><?php echo Yii::t('clue','city manger'); ?></th>
                <th><?php echo Yii::t('clue','trade type'); ?></th>
                <th><?php echo Yii::t('clue','level name'); ?></th>
                <th><?php echo Yii::t('clue',"clue service id"); ?></th>
                <th><?php echo Yii::t('clue',"service obj"); ?></th>
                <th><?php echo Yii::t('clue','status'); ?></th>
                <th><?php echo Yii::t('clue',"lcd"); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody id="dv_report_body">
            <tr><td colspan="11" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>
            </tbody>
        </table>
    </div>
    
    <!-- 分页 -->
    <div id="reportPagination" style="text-align: center; margin-top: 10px;"></div>
</div>

<?php
$ajaxUrl = Yii::app()->createUrl('clientHead/ajaxLoadReport');
$clueId = $model->id;
$lookText = Yii::t('clue','look');
$js = <<<EOF
var reportLoaded = false;
$('a[href="#clue_dv_report"]').on('shown.bs.tab', function (e) {
    if(!reportLoaded){
        loadClientReport(1, '');
        reportLoaded = true;
    }
});

// 搜索按钮
$('#reportSearchBtn').on('click', function(){
    var search = $('#reportSearch').val();
    loadClientReport(1, search);
});

// 清空按钮
$('#reportClearBtn').on('click', function(){
    $('#reportSearch').val('');
    loadClientReport(1, '');
});

// 回车搜索
$('#reportSearch').on('keypress', function(e){
    if(e.which == 13){
        var search = $(this).val();
        loadClientReport(1, search);
    }
});

function loadClientReport(page, search){
    page = page || 1;
    search = search || '';
    
    var tbody = $('#dv_report_body');
    tbody.html('<tr><td colspan="11" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>');
    
    $.ajax({
        url: '{$ajaxUrl}',
        type: 'GET',
        data: {
            clue_id: {$clueId},
            page: page,
            search: search
        },
        dataType: 'json',
        success: function(response){
            if(response.status === 1){
                var html = '';
                if(response.data && response.data.length > 0){
                    $.each(response.data, function(i, row){
                        html += '<tr>';
                        html += '<td>' + (row.clue_code || '') + '</td>';
                        html += '<td>' + (row.cust_name || '') + '</td>';
                        html += '<td>' + (row.clue_type || '') + '</td>';
                        html += '<td>' + (row.city || '') + '</td>';
                        html += '<td>' + (row.cust_class || '') + '</td>';
                        html += '<td>' + (row.cust_level || '') + '</td>';
                        html += '<td>' + (row.clue_service_id || '') + '</td>';
                        html += '<td>' + (row.busine_id_text || '') + '</td>';
                        html += '<td>' + (row.rpt_status || '') + '</td>';
                        html += '<td>' + (row.lcd || '') + '</td>';
                        html += '<td><a href="' + (row.view_url || '#') + '">{$lookText}</a></td>';
                        html += '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="11" style="text-align:center;">暂无数据</td></tr>';
                }
                tbody.html(html);
                
                // 渲染分页
                renderReportPagination(response.pageNum, response.noOfPages, response.totalRow, search);
            } else {
                tbody.html('<tr><td colspan="11" style="text-align:center; color: red;">加载失败: ' + (response.error || '未知错误') + '</td></tr>');
            }
        },
        error: function(xhr, status, error){
            var errorMsg = '加载失败，请刷新页面重试';
            if(xhr.responseJSON && xhr.responseJSON.error){
                errorMsg = '加载失败: ' + xhr.responseJSON.error;
            } else if(error){
                errorMsg = '加载失败: ' + error;
            }
            tbody.html('<tr><td colspan="11" style="text-align:center; color: red;">' + errorMsg + '</td></tr>');
        }
    });
}

function renderReportPagination(pageNum, noOfPages, totalRow, search){
    var html = '';
    if(totalRow > 0){
        html += '<div style="display: inline-block;">';
        html += '共 ' + totalRow + ' 条记录 ';
        
        // 上一页
        if(pageNum > 1){
            html += '<a href="javascript:void(0);" onclick="loadClientReport(' + (pageNum-1) + ', \'' + search + '\')"><上一页</a> ';
        }
        
        // 页码
        for(var i = 1; i <= noOfPages; i++){
            if(i == pageNum){
                html += '<strong>' + i + '</strong> ';
            } else {
                html += '<a href="javascript:void(0);" onclick="loadClientReport(' + i + ', \'' + search + '\')">' + i + '</a> ';
            }
        }
        
        // 下一页
        if(pageNum < noOfPages){
            html += '<a href="javascript:void(0);" onclick="loadClientReport(' + (pageNum+1) + ', \'' + search + '\')">下一页></a> ';
        }
        
        // 页码跳转
        html += '跳转到 <input type="number" id="reportPageJump" min="1" max="' + noOfPages + '" style="width:50px;" /> 页 ';
        html += '<button onclick="gotoReportPage(' + noOfPages + ', \'' + search + '\')">跳转</button>';
        html += '</div>';
    }
    $('#reportPagination').html(html);
}

function gotoReportPage(maxPages, search){
    var p = parseInt(document.getElementById('reportPageJump').value);
    if(p >= 1 && p <= maxPages){
        loadClientReport(p, search);
    }
}
EOF;
Yii::app()->clientScript->registerScript('loadClientReport',$js,CClientScript::POS_READY);
?>

