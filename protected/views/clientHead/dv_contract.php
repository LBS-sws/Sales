<?php
/**
 * 合约
 */
?>
<div  style="padding-top: 15px;">
    <!-- 搜索框 -->
    <div class="input-group" style="margin-bottom: 10px; max-width: 400px;">
        <input type="text" class="form-control" id="contractSearch" placeholder="搜索合约编号" />
        <span class="input-group-btn">
            <button class="btn btn-default" type="button" id="contractSearchBtn">
                <i class="fa fa-search"></i> 搜索
            </button>
            <button class="btn btn-default" type="button" id="contractClearBtn">
                <i class="fa fa-remove"></i> 清空
            </button>
        </span>
    </div>
    
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue',"virtual code"); ?></th><!--虚拟合约编号-->
                <th><?php echo Yii::t('clue',"store name"); ?></th><!--门店名称-->
                <th><?php echo Yii::t('clue',"city"); ?></th><!--城市-->
                <th><?php echo Yii::t('clue',"service obj"); ?></th><!--服务项目-->
                <th><?php echo Yii::t('clue',"status"); ?></th><!--状态-->
                <th><?php echo Yii::t('clue',"sign type"); ?></th><!--签约类型-->
                <th><?php echo Yii::t('clue',"sales"); ?></th><!--销售-->
                <th><?php echo Yii::t('clue',"total amt"); ?></th><!--总金额-->
                <th><?php echo Yii::t('clue',"sign date"); ?></th><!--签约时间-->
                <th><?php echo Yii::t('clue',"contract start date"); ?></th><!--合约开始时间-->
                <th><?php echo Yii::t('clue',"contract end date"); ?></th><!--合约结束时间-->
                <th><?php echo Yii::t('clue',"first date"); ?></th><!--首次日期-->
            </tr>
            </thead>
            <tbody id="dv_contract_body">
            <tr><td colspan="12" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>
            </tbody>
        </table>
    </div>
    
    <!-- 分页 -->
    <div id="contractPagination" style="text-align: center; margin-top: 10px;"></div>
</div>

<?php
$ajaxUrl = Yii::app()->createUrl('clientHead/ajaxLoadContract');
$clueId = $model->id;
$js = <<<EOF
var contractLoaded = false;
$('a[href="#clue_dv_contract"]').on('shown.bs.tab', function (e) {
    if(!contractLoaded){
        loadClientContract(1, '');
        contractLoaded = true;
    }
});

// 搜索按钮
$('#contractSearchBtn').on('click', function(){
    var search = $('#contractSearch').val();
    loadClientContract(1, search);
});

// 清空按钮
$('#contractClearBtn').on('click', function(){
    $('#contractSearch').val('');
    loadClientContract(1, '');
});

// 回车搜索
$('#contractSearch').on('keypress', function(e){
    if(e.which == 13){
        var search = $(this).val();
        loadClientContract(1, search);
    }
});

$('#contractPagination').on('click', 'a[data-page]', function(e){
    e.preventDefault();
    var page = parseInt($(this).data('page'), 10);
    var search = decodeURIComponent($(this).data('search') || '');
    if(page){
        loadClientContract(page, search);
    }
});

$('#contractPagination').on('click', '.contractPageGo', function(e){
    e.preventDefault();
    var maxPages = parseInt($(this).data('max'), 10) || 1;
    var search = decodeURIComponent($(this).data('search') || '');
    gotoContractPage(maxPages, search);
});

$('#contractPagination').on('keypress', '#contractPageJump', function(e){
    if(e.which == 13){
        $('#contractPagination .contractPageGo').trigger('click');
    }
});

function loadClientContract(page, search){
    page = page || 1;
    search = search || '';
    
    var tbody = $('#dv_contract_body');
    tbody.html('<tr><td colspan="12" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>');
    
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
                        html += '<td><a href="' + (row.detail_url || '#') + '">' + (row.vir_code || '') + '</a></td>';
                        html += '<td>' + (row.store_name || '') + '</td>';
                        html += '<td>' + (row.city || '') + '</td>';
                        html += '<td>' + (row.busine_id_text || '') + '</td>';
                        html += '<td>' + (row.vir_status || '') + '</td>';
                        html += '<td>' + (row.sign_type || '') + '</td>';
                        html += '<td>' + (row.sales_id || '') + '</td>';
                        html += '<td>' + (row.year_amt || '') + '</td>';
                        html += '<td>' + (row.sign_date || '') + '</td>';
                        html += '<td>' + (row.cont_start_dt || '') + '</td>';
                        html += '<td>' + (row.cont_end_dt || '') + '</td>';
                        html += '<td>' + (row.first_date || '') + '</td>';
                        html += '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="12" style="text-align:center;">暂无数据</td></tr>';
                }
                tbody.html(html);
                
                // 渲染分页
                renderContractPagination(response.pageNum, response.noOfPages, response.totalRow, search);
            } else {
                tbody.html('<tr><td colspan="12" style="text-align:center; color: red;">加载失败: ' + (response.error || '未知错误') + '</td></tr>');
            }
        },
        error: function(xhr, status, error){
            var errorMsg = '加载失败，请刷新页面重试';
            if(xhr.responseJSON && xhr.responseJSON.error){
                errorMsg = '加载失败: ' + xhr.responseJSON.error;
            } else if(error){
                errorMsg = '加载失败: ' + error;
            }
            tbody.html('<tr><td colspan="12" style="text-align:center; color: red;">' + errorMsg + '</td></tr>');
        }
    });
}

function renderContractPagination(pageNum, noOfPages, totalRow, search){
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
        html += '跳转到 <input type="number" id="contractPageJump" min="1" max="' + noOfPages + '" style="width:50px;" /> 页 ';
        html += '<button type="button" class="contractPageGo" data-max="' + noOfPages + '" data-search="' + encodedSearch + '">跳转</button>';
        html += '</div>';
    }
    $('#contractPagination').html(html);
}

function gotoContractPage(maxPages, search){
    var p = parseInt(document.getElementById('contractPageJump').value);
    if(p >= 1 && p <= maxPages){
        loadClientContract(p, search);
    }
}
EOF;
Yii::app()->clientScript->registerScript('loadClientContract',$js,CClientScript::POS_READY);
?>
