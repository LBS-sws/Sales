<?php
/**
 * 门店
 */
?>
<div  style="padding-top: 15px;">
    <div>
        <?php
        echo TbHtml::button(Yii::t('clue','add store'), array(
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
            'data-load'=>Yii::app()->createUrl('clueStore/ajaxShow'),
            'data-submit'=>Yii::app()->createUrl('clueStore/ajaxSave'),
            'data-serialize'=>"ClueStoreForm[scenario]=new&ClueStoreForm[city]={$model->city}&ClueStoreForm[clue_id]=".$model->id,
            'data-obj'=>"#clue_dv_store_dummy",
            'data-fun'=>'refreshStoreData',
            'class'=>'openDialogForm',
        ));
        ?>
    </div>
    
    <!-- 搜索框 -->
    <div class="input-group" style="margin-top: 10px; margin-bottom: 10px; max-width: 400px;">
        <input type="text" class="form-control" id="storeSearch" placeholder="搜索门店名称/编号" />
        <span class="input-group-btn">
            <button class="btn btn-default" type="button" id="storeSearchBtn">
                <i class="fa fa-search"></i> 搜索
            </button>
            <button class="btn btn-default" type="button" id="storeClearBtn">
                <i class="fa fa-remove"></i> 清空
            </button>
        </span>
    </div>
    
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue',"store code"); ?></th>
                <th><?php echo Yii::t('clue',"store name"); ?></th>
                <th><?php echo Yii::t('clue',"trade type"); ?></th>
                <th><?php echo Yii::t('clue',"district"); ?></th>
                <th><?php echo Yii::t('clue',"address"); ?></th>
                <th><?php echo Yii::t('clue',"client person"); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody id="dv_store_body">
            <tr><td colspan="7" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>
            </tbody>
        </table>
    </div>
    
    <!-- 分页 -->
    <div id="storePagination" style="text-align: center; margin-top: 10px;"></div>
</div>

<?php
$ajaxUrl = Yii::app()->createUrl('clientHead/ajaxLoadStore');
$editUrl = Yii::app()->createUrl('clueStore/ajaxShow');
$saveUrl = Yii::app()->createUrl('clueStore/ajaxSave');
$clueId = $model->id;
$js = <<<EOF
var storeLoaded = false;
$('a[href="#clue_dv_store"]').on('shown.bs.tab', function (e) {
    if(!storeLoaded){
        loadClientStore(1, '');
        storeLoaded = true;
    }
});

// 搜索按钮
$('#storeSearchBtn').on('click', function(){
    var search = $('#storeSearch').val();
    loadClientStore(1, search);
});

// 清空按钮
$('#storeClearBtn').on('click', function(){
    $('#storeSearch').val('');
    loadClientStore(1, '');
});

// 回车搜索
$('#storeSearch').on('keypress', function(e){
    if(e.which == 13){
        var search = $(this).val();
        loadClientStore(1, search);
    }
});

$('#storePagination').on('click', 'a[data-page]', function(e){
    e.preventDefault();
    var page = parseInt($(this).data('page'), 10);
    var search = decodeURIComponent($(this).data('search') || '');
    if(page){
        loadClientStore(page, search);
    }
});

$('#storePagination').on('click', '.storePageGo', function(e){
    e.preventDefault();
    var maxPages = parseInt($(this).data('max'), 10) || 1;
    var search = decodeURIComponent($(this).data('search') || '');
    gotoStorePage(maxPages, search);
});

$('#storePagination').on('keypress', '#storePageJump', function(e){
    if(e.which == 13){
        $('#storePagination .storePageGo').trigger('click');
    }
});

function loadClientStore(page, search){
    page = page || 1;
    search = search || '';
    
    var tbody = $('#dv_store_body');
    tbody.html('<tr><td colspan="7" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>');
    
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
                        html += '<td>' + (row.store_code || '') + '</td>';
                        html += '<td>' + (row.store_name || '') + '</td>';
                        html += '<td>' + (row.cust_class || '') + '</td>';
                        html += '<td>' + (row.district || '') + '</td>';
                        html += '<td>' + (row.address || '') + '</td>';
                        html += '<td>' + (row.person || '') + '</td>';
                        html += '<td>';
                        if(row.can_edit){
                            html += '<a href="javascript:void(0);" class="openDialogForm" data-load="{$editUrl}" data-submit="{$saveUrl}" data-serialize="ClueStoreForm[scenario]=edit&ClueStoreForm[id]=' + row.id + '" data-obj="#clue_dv_store_dummy" data-fun="refreshStoreData"><span class="glyphicon glyphicon-pencil"></span></a>';
                        }
                        html += '</td>';
                        html += '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="7" style="text-align:center;">暂无数据</td></tr>';
                }
                tbody.html(html);
                
                // 渲染分页
                renderStorePagination(response.pageNum, response.noOfPages, response.totalRow, search);
            } else {
                tbody.html('<tr><td colspan="7" style="text-align:center; color: red;">加载失败: ' + (response.error || '未知错误') + '</td></tr>');
            }
        },
        error: function(xhr, status, error){
            var errorMsg = '加载失败，请刷新页面重试';
            if(xhr.responseJSON && xhr.responseJSON.error){
                errorMsg = '加载失败: ' + xhr.responseJSON.error;
            } else if(error){
                errorMsg = '加载失败: ' + error;
            }
            tbody.html('<tr><td colspan="7" style="text-align:center; color: red;">' + errorMsg + '</td></tr>');
        }
    });
}

function renderStorePagination(pageNum, noOfPages, totalRow, search){
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
        html += '跳转到 <input type="number" id="storePageJump" min="1" max="' + noOfPages + '" style="width:50px;" /> 页 ';
        html += '<button type="button" class="storePageGo" data-max="' + noOfPages + '" data-search="' + encodedSearch + '">跳转</button>';
        html += '</div>';
    }
    $('#storePagination').html(html);
}

function gotoStorePage(maxPages, search){
    var p = parseInt(document.getElementById('storePageJump').value);
    if(p >= 1 && p <= maxPages){
        loadClientStore(p, search);
    }
}

function refreshStoreData(response){
    // 保存成功后重新加载数据
    loadClientStore(1, '');
}
EOF;
Yii::app()->clientScript->registerScript('loadClientStore',$js,CClientScript::POS_READY);
?>
