<?php
/**
 * 门店
 */
?>
<div  style="padding-top: 15px;">
    <div class="btn-group">
        <?php
        if(in_array($model->cont_status,array(10,30))){
            echo TbHtml::button(Yii::t('clue','add cont store'), array(
                'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                'submit'=>Yii::app()->createUrl('contPro/new',array("cont_id"=>$model->id,"type"=>"NA"))
            ));
            if(Yii::app()->user->validRWFunction('CS01')) {
                echo TbHtml::button(Yii::t('clue', 'call service'), array(
                    'color' => TbHtml::BUTTON_COLOR_PRIMARY,
                    'data-url' => Yii::app()->createUrl('callService/new', array("cont_id" => $model->id)),
                    'id' => "newCallService"
                ));
            }
        }
        ?>
    </div>
    
    <!-- 搜索框 -->
    <div class="input-group" style="margin-top: 10px; margin-bottom: 10px; max-width: 400px;">
        <input type="text" class="form-control" id="contStoreSearch" placeholder="搜索门店名称/编号" />
        <span class="input-group-btn">
            <button class="btn btn-default" type="button" id="contStoreSearchBtn">
                <i class="fa fa-search"></i> 搜索
            </button>
            <button class="btn btn-default" type="button" id="contStoreClearBtn">
                <i class="fa fa-remove"></i> 清空
            </button>
        </span>
    </div>
    
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th width="1%"><?php echo TbHtml::checkBox("allBox",false,array("class"=>"allBox"))?></th>
                <th><?php echo Yii::t('clue',"store code"); ?></th>
                <th><?php echo Yii::t('clue',"store name"); ?></th>
                <th><?php echo Yii::t('clue',"trade type"); ?></th>
                <th><?php echo Yii::t('clue',"district"); ?></th>
                <th><?php echo Yii::t('clue',"address"); ?></th>
                <th><?php echo Yii::t('clue',"client person"); ?></th>
                <th><?php echo Yii::t('clue',"status"); ?></th>
                <th style="width: 185px;"><?php echo Yii::t('clue',"virtual code"); ?></th>
            </tr>
            </thead>
            <tbody id="contStoreTableBody">
            <tr><td colspan="9" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>
            </tbody>
        </table>
    </div>
    
    <!-- 分页 -->
    <div id="contStorePagination" style="text-align: center; margin-top: 10px;"></div>
</div>

<?php
$ajaxUrl = Yii::app()->createUrl('contHead/ajaxLoadStores');
$contId = $model->id;
$callServiceUrl = Yii::app()->createUrl('callService/new', array("cont_id" => $contId));
$virtualDetailUrl = Yii::app()->createUrl('virtualHead/detail');
$js = <<<EOF
var contStoreCurrentPage = 1;
var contStoreTotalPages = 1;
var contStoreCallShow = false;

// 加载门店数据
function loadContStores(page, search) {
    page = page || 1;
    search = search || '';
    
    var tbody = $('#contStoreTableBody');
    tbody.html('<tr><td colspan="9" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>');
    
    $.ajax({
        url: '{$ajaxUrl}',
        type: 'GET',
        data: {
            cont_id: {$contId},
            page: page,
            search: search
        },
        dataType: 'json',
        success: function(response){
            if(response.status === 'success'){
                var html = '';
                contStoreCallShow = false;
                
                if(response.data && response.data.length > 0){
                    $.each(response.data, function(index, row){
                        html += '<tr>';
                        html += '<td>';
                        if(row.can_check){
                            contStoreCallShow = true;
                            html += '<input type="checkbox" class="checkOne" data-val="' + row.check_id + '" />';
                        }else{
                            html += '&nbsp;';
                        }
                        html += '</td>';
                        html += '<td>' + (row.store_code || '') + '</td>';
                        html += '<td>' + (row.store_name || '') + '</td>';
                        html += '<td>' + (row.cust_class || '') + '</td>';
                        html += '<td>' + (row.district || '') + '</td>';
                        html += '<td>' + (row.address || '') + '</td>';
                        html += '<td>' + (row.person || '') + '</td>';
                        html += '<td>' + (row.store_status || '') + '</td>';
                        html += '<td><ul class="list-unstyled">';
                        if(row.virtual_codes && row.virtual_codes.length > 0){
                            $.each(row.virtual_codes, function(i, vir){
                                html += '<li><a href="{$virtualDetailUrl}?index=' + vir.id + '" target="_blank">' + vir.code + '</a></li>';
                            });
                        }
                        html += '</ul></td>';
                        html += '</tr>';
                    });
                }else{
                    html = '<tr><td colspan="9" style="text-align:center;">没有关联门店</td></tr>';
                }
                
                tbody.html(html);
                
                // 更新分页
                contStoreCurrentPage = response.pageNum || 1;
                contStoreTotalPages = response.noOfPages || 1;
                renderContStorePagination(response.totalRow || 0);
                
                // 控制呼叫服务按钮和全选框显示
                if(contStoreCallShow){
                    $('#newCallService').show();
                    $('.allBox').show();
                }else{
                    $('#newCallService').hide();
                    $('.allBox').hide();
                }
            }else{
                tbody.html('<tr><td colspan="9" style="color:red;text-align:center;">加载失败：' + response.message + '</td></tr>');
            }
        },
        error: function(xhr, status, error){
            console.error('Ajax错误:', status, error);
            tbody.html('<tr><td colspan="9" style="color:red;text-align:center;">加载失败，请重试</td></tr>');
        }
    });
}

// 渲染分页
function renderContStorePagination(totalRow) {
    var html = '';
    if (contStoreTotalPages > 1) {
        html += '<div style="display:inline-block;">';
        
        // 上一页
        if (contStoreCurrentPage > 1) {
            html += '<a href="javascript:void(0);" class="cont-store-page-link" data-page="' + (contStoreCurrentPage - 1) + '">上一页</a> ';
        }
        
        // 页码
        var startPage = Math.max(1, contStoreCurrentPage - 2);
        var endPage = Math.min(contStoreTotalPages, contStoreCurrentPage + 2);
        
        for (var i = startPage; i <= endPage; i++) {
            if (i == contStoreCurrentPage) {
                html += '<span style="margin:0 5px;font-weight:bold;">' + i + '</span>';
            } else {
                html += '<a href="javascript:void(0);" class="cont-store-page-link" data-page="' + i + '" style="margin:0 5px;">' + i + '</a>';
            }
        }
        
        // 下一页
        if (contStoreCurrentPage < contStoreTotalPages) {
            html += ' <a href="javascript:void(0);" class="cont-store-page-link" data-page="' + (contStoreCurrentPage + 1) + '">下一页</a>';
        }
        
        html += '</div>';
        html += ' <span style="margin-left:15px;">共 ' + totalRow + ' 条记录，' + contStoreTotalPages + ' 页</span>';
        
        // 页码跳转
        html += ' <span style="margin-left:15px;">';
        html += '跳转到 <input type="text" id="contStoreJumpPage" style="width:50px;text-align:center;" /> 页 ';
        html += '<button type="button" class="btn btn-xs btn-default" id="contStoreJumpBtn">确定</button>';
        html += '</span>';
    } else if (totalRow > 0) {
        html = '<span>共 ' + totalRow + ' 条记录</span>';
    }
    
    $('#contStorePagination').html(html);
}

// 页面加载时自动加载第一页
$(document).ready(function(){
    loadContStores(1, '');
});

// 搜索
$('#contStoreSearchBtn').on('click', function(){
    var search = $('#contStoreSearch').val();
    loadContStores(1, search);
});

// 回车搜索
$('#contStoreSearch').on('keypress', function(e){
    if(e.which == 13){
        var search = $(this).val();
        loadContStores(1, search);
    }
});

// 清空
$('#contStoreClearBtn').on('click', function(){
    $('#contStoreSearch').val('');
    loadContStores(1, '');
});

// 分页点击
$(document).on('click', '.cont-store-page-link', function(){
    var page = $(this).data('page');
    var search = $('#contStoreSearch').val();
    loadContStores(page, search);
});

// 页码跳转
$(document).on('click', '#contStoreJumpBtn', function(){
    var page = parseInt($('#contStoreJumpPage').val());
    if(page > 0 && page <= contStoreTotalPages){
        var search = $('#contStoreSearch').val();
        loadContStores(page, search);
    }else{
        alert('请输入有效页码（1-' + contStoreTotalPages + '）');
    }
});

// 页码跳转回车
$(document).on('keypress', '#contStoreJumpPage', function(e){
    if(e.which == 13){
        $('#contStoreJumpBtn').click();
    }
});

// 全选
$(".allBox").click(function(){
    var bool = $(this).is(':checked');
    $('.checkOne').prop('checked',bool);
});

// 呼叫服务
$("#newCallService").click(function(){
    var url = $(this).data('url');
    var ids=[];
    $(".checkOne:checked").each(function(){
        ids.push($(this).data('val'));
    });
    ids=ids.join(',');
    if(ids==""){
        showFormErrorHtml('请至少选择一个门店');
    }else{
        url+="&store_ids="+ids;
        jQuery.yii.submitForm(this,url,{});
    }
});

EOF;
Yii::app()->clientScript->registerScript('contStoreList',$js,CClientScript::POS_READY);
?>
