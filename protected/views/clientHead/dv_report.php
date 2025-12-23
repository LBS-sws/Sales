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

$('#reportPagination').on('click', 'a[data-page]', function(e){
    e.preventDefault();
    var page = parseInt($(this).data('page'), 10);
    var search = decodeURIComponent($(this).data('search') || '');
    if(page){
        loadClientReport(page, search);
    }
});

$('#reportPagination').on('click', '.reportPageGo', function(e){
    e.preventDefault();
    var maxPages = parseInt($(this).data('max'), 10) || 1;
    var search = decodeURIComponent($(this).data('search') || '');
    gotoReportPage(maxPages, search);
});

$('#reportPagination').on('keypress', '#reportPageJump', function(e){
    if(e.which == 13){
        $('#reportPagination .reportPageGo').trigger('click');
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
        var encodedSearch = encodeURIComponent(search || '');
        html += '<div style="display: inline-block;">';
        html += '共 ' + totalRow + ' 条记录 ';
        
        // 上一页
        if(pageNum > 1){
            html += '<a href="javascript:void(0);" data-page="' + (pageNum-1) + '" data-search="' + encodedSearch + '"><上一页</a> ';
        }
        
        // 页码
        var maxVisible = 7;
        var half = Math.floor(maxVisible / 2);
        var start = pageNum - half;
        var end = pageNum + half;
        if(start < 1){
            end += (1 - start);
            start = 1;
        }
        if(end > noOfPages){
            start -= (end - noOfPages);
            end = noOfPages;
        }
        if(start < 1){
            start = 1;
        }
        if(noOfPages <= maxVisible){
            start = 1;
            end = noOfPages;
        }
        if(start > 1){
            html += '<a href="javascript:void(0);" data-page="1" data-search="' + encodedSearch + '">1</a> ';
            if(start > 2){
                html += '<span>...</span> ';
            }
        }
        for(var i = start; i <= end; i++){
            if(i == pageNum){
                html += '<strong>' + i + '</strong> ';
            } else {
                html += '<a href="javascript:void(0);" data-page="' + i + '" data-search="' + encodedSearch + '">' + i + '</a> ';
            }
        }
        if(end < noOfPages){
            if(end < noOfPages - 1){
                html += '<span>...</span> ';
            }
            html += '<a href="javascript:void(0);" data-page="' + noOfPages + '" data-search="' + encodedSearch + '">' + noOfPages + '</a> ';
        }
        
        // 下一页
        if(pageNum < noOfPages){
            html += '<a href="javascript:void(0);" data-page="' + (pageNum+1) + '" data-search="' + encodedSearch + '">下一页></a> ';
        }
        
        // 页码跳转
        html += '跳转到 <input type="number" id="reportPageJump" min="1" max="' + noOfPages + '" style="width:50px;" /> 页 ';
        html += '<button type="button" class="reportPageGo" data-max="' + noOfPages + '" data-search="' + encodedSearch + '">跳转</button>';
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

