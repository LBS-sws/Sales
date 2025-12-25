<?php
/**
 * 商机
 */
?>
<!--商机-->
<div class="bg_clue_service">
    <div class="clue_service">
        <div class="row" id="clue_service_row">
            <div style="text-align:center; padding: 20px;"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12 text-center" id="clue_service_pagination"></div>
</div>
<!--商机跟进记录、关联门店-->
<div id="clueFlowAndStore">
    <div style="text-align:center; padding: 20px;"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>
</div>

<?php
$ajaxUrl = Yii::app()->createUrl('clientHead/ajaxLoadService');
$flowStoreAjaxUrl = Yii::app()->createUrl('clientHead/ajaxLoadFlowAndStore');
$clueId = $model->id;
$addServiceUrl = Yii::app()->createUrl('clueService/ajaxShow');
$saveServiceUrl = Yii::app()->createUrl('clueService/ajaxSave');
$js = <<<EOF
\$(document).ready(function(){
    // 页面加载时自动加载商机数据
    loadClientService();
});

function loadClientService(){
    var page = arguments.length > 0 ? arguments[0] : 0;
    var serviceRow = $('#clue_service_row');
    var flowDiv = $('#clueFlowAndStore');
    var paginationDiv = $('#clue_service_pagination');
	var serviceId = (function(){
		var search = window.location.search || '';
		var match = search.match(/[?&]service_id=(\d+)/);
		return match ? parseInt(match[1], 10) : 0;
	})();
    
    serviceRow.html('<div style="text-align:center; padding: 20px;"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>');
    flowDiv.html('<div style="text-align:center; padding: 20px;"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>');
    paginationDiv.html('');
    
    $.ajax({
        url: '{$ajaxUrl}',
        type: 'GET',
        data: {
			clue_id: {$clueId},
			service_id: serviceId,
            page: page
        },
        dataType: 'json',
        success: function(response){
            if(response && response.status === 1){
                // 直接使用服务器端渲染的HTML，保持原有样式
                if(response.html && response.html.trim() !== ''){
                    serviceRow.html(response.html);
                    renderServicePagination(response.totalRow || 0, response.pageNum || 1, response.noOfPages || 1);
                    // 触发原有的初始化逻辑
                    if(typeof select2SSE === 'function'){
                        select2SSE(response);
                    }
                } else {
                    serviceRow.html('<div style="text-align:center; padding: 20px; color: #999;">暂无商机数据</div>');
                }
                // 加载商机跟进记录和关联门店
                loadFlowAndStore();
            } else {
                var errorMsg = '加载失败';
                if(response && response.error){
                    errorMsg += ': ' + response.error;
                } else if(!response){
                    errorMsg += ': 服务器无响应';
                } else {
                    errorMsg += ': 未知错误';
                }
                serviceRow.html('<div style="text-align:center; padding: 20px; color: red;">' + errorMsg + '</div>');
            }
        },
        error: function(xhr, status, error){
            var errorMsg = '加载失败，请刷新页面重试';
            if(xhr.responseJSON && xhr.responseJSON.error){
                errorMsg = '加载失败: ' + xhr.responseJSON.error;
            } else if(error){
                errorMsg = '加载失败: ' + error;
            }
            serviceRow.html('<div style="text-align:center; padding: 20px; color: red;">' + errorMsg + '</div>');
        }
    });
}

function renderServicePagination(totalRow, pageNum, noOfPages){
    totalRow = parseInt(totalRow || 0);
    pageNum = parseInt(pageNum || 1);
    noOfPages = parseInt(noOfPages || 1);
    var paginationDiv = $('#clue_service_pagination');

    var html = '';
    if(noOfPages > 1){
        html += '<div style="display:inline-block;">';
        if(pageNum > 1){
            html += '<a href="javascript:void(0);" class="client-service-page-link" data-page="' + (pageNum - 1) + '">上一页</a> ';
        }
        var startPage = Math.max(1, pageNum - 2);
        var endPage = Math.min(noOfPages, pageNum + 2);
        for(var i = startPage; i <= endPage; i++){
            if(i === pageNum){
                html += '<span style="margin:0 5px;font-weight:bold;">' + i + '</span>';
            }else{
                html += '<a href="javascript:void(0);" class="client-service-page-link" data-page="' + i + '" style="margin:0 5px;">' + i + '</a>';
            }
        }
        if(pageNum < noOfPages){
            html += ' <a href="javascript:void(0);" class="client-service-page-link" data-page="' + (pageNum + 1) + '">下一页</a>';
        }
        html += '</div>';
        html += ' <span style="margin-left:15px;">共 ' + totalRow + ' 条记录，' + noOfPages + ' 页</span>';
    }else if(totalRow > 0){
        html = '<span>共 ' + totalRow + ' 条记录</span>';
    }
    paginationDiv.html(html);
}

$(document).off('click', '.client-service-page-link').on('click', '.client-service-page-link', function(){
    var page = parseInt($(this).data('page'));
    if(page > 0){
        loadClientService(page);
    }
});

function loadFlowAndStore(){
    var flowDiv = $('#clueFlowAndStore');
	var serviceId = (function(){
		var search = window.location.search || '';
		var match = search.match(/[?&]service_id=(\d+)/);
		return match ? parseInt(match[1], 10) : 0;
	})();
    flowDiv.html('<div style="text-align:center; padding: 20px;"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>');
    
    $.ajax({
        url: '{$flowStoreAjaxUrl}',
        type: 'GET',
        data: {
			clue_id: {$clueId},
			service_id: serviceId
        },
        dataType: 'json',
        success: function(response){
            if(response && response.status === 1){
                if(response.html && response.html.trim() !== ''){
                    flowDiv.html(response.html);
                    flowDiv.find('script').each(function(){
                        if(this.text){
                            $.globalEval(this.text);
                        }else if(this.textContent){
                            $.globalEval(this.textContent);
                        }else if(this.innerHTML){
                            $.globalEval(this.innerHTML);
                        }
                    });
                    if(typeof select2SSE === 'function'){
                        select2SSE(response);
                    }
                } else {
                    flowDiv.html('<div style="text-align:center; padding: 20px; color: #999;">暂无商机数据，请先点击上方商机卡片查看详情</div>');
                }
            } else {
                var errorMsg = '加载失败';
                if(response && response.error){
                    errorMsg += ': ' + response.error;
                } else if(!response){
                    errorMsg += ': 服务器无响应';
                } else {
                    errorMsg += ': 未知错误';
                }
                flowDiv.html('<div style="text-align:center; padding: 20px; color: red;">' + errorMsg + '</div>');
            }
        },
        error: function(xhr, status, error){
            var errorMsg = '加载失败，请刷新页面重试';
            if(xhr.responseJSON && xhr.responseJSON.error){
                errorMsg = '加载失败: ' + xhr.responseJSON.error;
            } else if(error){
                errorMsg = '加载失败: ' + error;
            }
            flowDiv.html('<div style="text-align:center; padding: 20px; color: red;">' + errorMsg + '</div>');
        }
    });
}
EOF;
Yii::app()->clientScript->registerScript('loadClientService',$js,CClientScript::POS_READY);
?>
