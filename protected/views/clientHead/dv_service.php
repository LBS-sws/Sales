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
    var serviceRow = $('#clue_service_row');
    var flowDiv = $('#clueFlowAndStore');
	var serviceId = (function(){
		var search = window.location.search || '';
		var match = search.match(/[?&]service_id=(\d+)/);
		return match ? parseInt(match[1], 10) : 0;
	})();
    
    serviceRow.html('<div style="text-align:center; padding: 20px;"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>');
    flowDiv.html('<div style="text-align:center; padding: 20px;"><i class="fa fa-spinner fa-spin"></i> 加载中...</div>');
    
    $.ajax({
        url: '{$ajaxUrl}',
        type: 'GET',
        data: {
			clue_id: {$clueId},
			service_id: serviceId
        },
        dataType: 'json',
        success: function(response){
            if(response && response.status === 1){
                // 直接使用服务器端渲染的HTML，保持原有样式
                if(response.html && response.html.trim() !== ''){
                    serviceRow.html(response.html);
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
