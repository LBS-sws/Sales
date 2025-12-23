<?php
/**
 * 操作
 */
?>
<div  style="padding-top: 15px;">
    <!-- 搜索框 -->
    <div class="input-group" style="margin-bottom: 10px; max-width: 400px;">
        <input type="text" class="form-control" id="operationSearch" placeholder="搜索操作内容/操作人" />
        <span class="input-group-btn">
            <button class="btn btn-default" type="button" id="operationSearchBtn">
                <i class="fa fa-search"></i> 搜索
            </button>
            <button class="btn btn-default" type="button" id="operationClearBtn">
                <i class="fa fa-remove"></i> 清空
            </button>
        </span>
    </div>
    
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue',"Operator User"); ?></th>
                <th><?php echo Yii::t('clue',"Operator Time"); ?></th>
                <th><?php echo Yii::t('clue',"Operator Text"); ?></th>
            </tr>
            </thead>
            <tbody id="dv_operation_body">
            <tr><td colspan="3" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>
            </tbody>
        </table>
    </div>
    
    <!-- 分页 -->
    <div id="operationPagination" style="text-align: center; margin-top: 10px;"></div>
</div>

<?php
$ajaxUrl = Yii::app()->createUrl('clientHead/ajaxLoadOperation');
$clueId = $model->id;
$js = <<<EOF
var operationLoaded = false;
$('a[href="#clue_dv_operation"]').on('shown.bs.tab', function (e) {
    if(!operationLoaded){
        loadClientOperation(1, '');
        operationLoaded = true;
    }
});

// 搜索按钮
$('#operationSearchBtn').on('click', function(){
    var search = $('#operationSearch').val();
    loadClientOperation(1, search);
});

// 清空按钮
$('#operationClearBtn').on('click', function(){
    $('#operationSearch').val('');
    loadClientOperation(1, '');
});

// 回车搜索
$('#operationSearch').on('keypress', function(e){
    if(e.which == 13){
        var search = $(this).val();
        loadClientOperation(1, search);
    }
});

$('#operationPagination').on('click', 'a[data-page]', function(e){
    e.preventDefault();
    var page = parseInt($(this).data('page'), 10);
    var search = decodeURIComponent($(this).data('search') || '');
    if(page){
        loadClientOperation(page, search);
    }
});

$('#operationPagination').on('click', '.operationPageGo', function(e){
    e.preventDefault();
    var maxPages = parseInt($(this).data('max'), 10) || 1;
    var search = decodeURIComponent($(this).data('search') || '');
    gotoOperationPage(maxPages, search);
});

$('#operationPagination').on('keypress', '#operationPageJump', function(e){
    if(e.which == 13){
        $('#operationPagination .operationPageGo').trigger('click');
    }
});

function loadClientOperation(page, search){
    page = page || 1;
    search = search || '';
    
    var tbody = $('#dv_operation_body');
    tbody.html('<tr><td colspan="3" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>');
    
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
                        html += '<td>' + (row.username || '') + '</td>';
                        html += '<td>' + (row.lcd || '') + '</td>';
                        html += '<td>' + (row.history_html || '') + '</td>';
                        html += '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="3" style="text-align:center;">暂无数据</td></tr>';
                }
                tbody.html(html);
                
                // 渲染分页
                renderOperationPagination(response.pageNum, response.noOfPages, response.totalRow, search);
            } else {
                tbody.html('<tr><td colspan="3" style="text-align:center; color: red;">加载失败: ' + (response.error || '未知错误') + '</td></tr>');
            }
        },
        error: function(xhr, status, error){
            var errorMsg = '加载失败，请刷新页面重试';
            if(xhr.responseJSON && xhr.responseJSON.error){
                errorMsg = '加载失败: ' + xhr.responseJSON.error;
            } else if(error){
                errorMsg = '加载失败: ' + error;
            }
            tbody.html('<tr><td colspan="3" style="text-align:center; color: red;">' + errorMsg + '</td></tr>');
        }
    });
}

function renderOperationPagination(pageNum, noOfPages, totalRow, search){
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
        html += '跳转到 <input type="number" id="operationPageJump" min="1" max="' + noOfPages + '" style="width:50px;" /> 页 ';
        html += '<button type="button" class="operationPageGo" data-max="' + noOfPages + '" data-search="' + encodedSearch + '">跳转</button>';
        html += '</div>';
    }
    $('#operationPagination').html(html);
}

function gotoOperationPage(maxPages, search){
    var p = parseInt(document.getElementById('operationPageJump').value);
    if(p >= 1 && p <= maxPages){
        loadClientOperation(p, search);
    }
}
EOF;
Yii::app()->clientScript->registerScript('loadClientOperation',$js,CClientScript::POS_READY);
?>

