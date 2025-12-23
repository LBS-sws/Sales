<?php
/**
 * 联系人
 */
?>
<div  style="padding-top: 15px;">
    <div>
        <?php
        echo TbHtml::button(Yii::t('clue','add person'), array(
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
            'data-load'=>Yii::app()->createUrl('clientPerson/ajaxShow'),
            'data-submit'=>Yii::app()->createUrl('clientPerson/ajaxSave'),
            'data-serialize'=>"ClientPersonForm[scenario]=new&ClientPersonForm[clue_id]=".$model->id,
            'data-obj'=>"#clue_dv_person_dummy",
            'data-fun'=>'refreshPersonData',
            'class'=>'openDialogForm',
        ));
        ?>
    </div>
    
    <!-- 搜索框 -->
    <div class="input-group" style="margin-top: 10px; margin-bottom: 10px; max-width: 400px;">
        <input type="text" class="form-control" id="personSearch" placeholder="搜索联系人姓名/电话/职位" />
        <span class="input-group-btn">
            <button class="btn btn-default" type="button" id="personSearchBtn">
                <i class="fa fa-search"></i> 搜索
            </button>
            <button class="btn btn-default" type="button" id="personClearBtn">
                <i class="fa fa-remove"></i> 清空
            </button>
        </span>
    </div>
    
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue',"person code"); ?></th>
                <th><?php echo Yii::t('clue',"person name"); ?></th>
                <th><?php echo Yii::t('clue',"person sex"); ?></th>
                <th><?php echo Yii::t('clue',"person role"); ?></th>
                <th><?php echo Yii::t('clue',"person tel"); ?></th>
                <th><?php echo Yii::t('clue',"person email"); ?></th>
                <th><?php echo Yii::t('clue',"person pws"); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody id="dv_person_body">
            <tr><td colspan="8" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>
            </tbody>
        </table>
    </div>
    
    <!-- 分页 -->
    <div id="personPagination" style="text-align: center; margin-top: 10px;"></div>
</div>

<?php
$ajaxUrl = Yii::app()->createUrl('clientHead/ajaxLoadPerson');
$editUrl = Yii::app()->createUrl('clientPerson/ajaxShow');
$saveUrl = Yii::app()->createUrl('clientPerson/ajaxSave');
$clueId = $model->id;
$js = <<<EOF
var personLoaded = false;
$('a[href="#clue_dv_person"]').on('shown.bs.tab', function (e) {
    if(!personLoaded){
        loadClientPerson(1, '');
        personLoaded = true;
    }
});

// 搜索按钮
$('#personSearchBtn').on('click', function(){
    var search = $('#personSearch').val();
    loadClientPerson(1, search);
});

// 清空按钮
$('#personClearBtn').on('click', function(){
    $('#personSearch').val('');
    loadClientPerson(1, '');
});

// 回车搜索
$('#personSearch').on('keypress', function(e){
    if(e.which == 13){
        var search = $(this).val();
        loadClientPerson(1, search);
    }
});

$('#personPagination').on('click', 'a[data-page]', function(e){
    e.preventDefault();
    var page = parseInt($(this).data('page'), 10);
    var search = decodeURIComponent($(this).data('search') || '');
    if(page){
        loadClientPerson(page, search);
    }
});

$('#personPagination').on('click', '.personPageGo', function(e){
    e.preventDefault();
    var maxPages = parseInt($(this).data('max'), 10) || 1;
    var search = decodeURIComponent($(this).data('search') || '');
    gotoPersonPage(maxPages, search);
});

$('#personPagination').on('keypress', '#personPageJump', function(e){
    if(e.which == 13){
        $('#personPagination .personPageGo').trigger('click');
    }
});

function loadClientPerson(page, search){
    page = page || 1;
    search = search || '';
    
    var tbody = $('#dv_person_body');
    tbody.html('<tr><td colspan="8" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>');
    
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
                        html += '<td>' + (row.person_code || '') + '</td>';
                        html += '<td>' + (row.cust_person || '') + '</td>';
                        html += '<td>' + (row.sex || '') + '</td>';
                        html += '<td>' + (row.cust_person_role || '') + '</td>';
                        html += '<td>' + (row.cust_tel || '') + '</td>';
                        html += '<td>' + (row.cust_email || '') + '</td>';
                        html += '<td>' + (row.person_pws || '') + '</td>';
                        html += '<td>';
                        if(row.can_edit){
                            html += '<a href="javascript:void(0);" class="openDialogForm" data-load="{$editUrl}" data-submit="{$saveUrl}" data-serialize="ClientPersonForm[scenario]=edit&ClientPersonForm[id]=' + row.id + '" data-obj="#clue_dv_person_dummy" data-fun="refreshPersonData"><span class="glyphicon glyphicon-pencil"></span></a>';
                        }
                        html += '</td>';
                        html += '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="8" style="text-align:center;">暂无数据</td></tr>';
                }
                tbody.html(html);
                
                // 渲染分页
                renderPersonPagination(response.pageNum, response.noOfPages, response.totalRow, search);
            } else {
                tbody.html('<tr><td colspan="8" style="text-align:center; color: red;">加载失败: ' + (response.error || '未知错误') + '</td></tr>');
            }
        },
        error: function(xhr, status, error){
            var errorMsg = '加载失败，请刷新页面重试';
            if(xhr.responseJSON && xhr.responseJSON.error){
                errorMsg = '加载失败: ' + xhr.responseJSON.error;
            } else if(error){
                errorMsg = '加载失败: ' + error;
            }
            tbody.html('<tr><td colspan="8" style="text-align:center; color: red;">' + errorMsg + '</td></tr>');
        }
    });
}

function renderPersonPagination(pageNum, noOfPages, totalRow, search){
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
        html += '跳转到 <input type="number" id="personPageJump" min="1" max="' + noOfPages + '" style="width:50px;" /> 页 ';
        html += '<button type="button" class="personPageGo" data-max="' + noOfPages + '" data-search="' + encodedSearch + '">跳转</button>';
        html += '</div>';
    }
    $('#personPagination').html(html);
}

function gotoPersonPage(maxPages, search){
    var p = parseInt(document.getElementById('personPageJump').value);
    if(p >= 1 && p <= maxPages){
        loadClientPerson(p, search);
    }
}

function refreshPersonData(response){
    // 保存成功后重新加载数据
    loadClientPerson(1, '');
}
EOF;
Yii::app()->clientScript->registerScript('loadClientPerson',$js,CClientScript::POS_READY);
?>
