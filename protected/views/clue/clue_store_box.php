<p>&nbsp;</p>
<legend><?php echo Yii::t("clue","clue service store");?><small>（总金额：<?php echo isset($model->clueServiceRow["total_amt"])?$model->clueServiceRow["total_amt"]:"";?>）</small></legend>

<?php
$service_status = isset($model->clueServiceRow['service_status']) ? $model->clueServiceRow['service_status'] : 0;
$updateBool = $service_status<7&&(Yii::app()->user->validRWFunction('CM02')||Yii::app()->user->validRWFunction('CM10'));
$totalStoreCount = !empty($model->clue_service_id) ? CGetName::getClueSSeCountByClueServiceID($model->clue_service_id) : 0;
$hasRows = !empty($rows);
?>
<?php if ($updateBool): ?>
    <div class="form-group">
        <div class="col-lg-12">
			<div class="btn-group">
				<?php echo TbHtml::button(Yii::t("clue","add clue store"),array(
					'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
					'data-load'=>Yii::app()->createUrl('clueSSE/ajaxShow'),
					'data-submit'=>Yii::app()->createUrl('clueSSE/ajaxSave'),
					'data-serialize'=>"ClueSSEForm[scenario]=new&ClueSSEForm[clue_service_id]=".$model->clue_service_id."&ClueSSEForm[clue_id]=".$model->id,
					'data-obj'=>"#clue_service_store",
					'class'=>'openDialogForm',
					'data-fun'=>'select2SSE',
				));?>
			</div>
		</div>
	</div>
<?php endif ?>
<div class="form-group">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <?php if($updateBool && !empty($rows)): ?>
                    <th width="40px">
                        <input type="checkbox" class="store_check_all" />
                    </th>
                    <?php endif; ?>
                    <th width="1%">&nbsp;</th><!--操作-->
                    <th><?php echo Yii::t("clue","store name")?></th><!--门店名称-->
                    <th><?php echo Yii::t("clue","district")?></th><!--区域-->
                    <th><?php echo Yii::t("clue","address")?></th><!--详细地址-->
                    <th><?php echo Yii::t("clue","customer person")?></th><!--联络人-->
                    <th><?php echo Yii::t("clue","person tel")?></th><!--联系人电话-->
                    <th><?php echo Yii::t("clue","invoice header")?></th><!--开票抬头-->
                    <th><?php echo Yii::t("clue","tax id")?></th><!--税号-->
                    <th><?php echo Yii::t("clue","invoice address")?></th><!--开票地址-->
                    <th><?php echo Yii::t("clue","clue area")?></th><!--门店面积-->
                </tr>
                </thead>
                <tbody id="store_list_tbody">
                <?php
                $html = "";
                if(!empty($rows)){
                    $html.='<tr class="hide"><td colspan="'.($updateBool?'11':'10').'"></td></tr>';
                    foreach ($rows as $row){
                        //$row["update_bool"] = $row["update_bool"]==1&&$row["rec_bool"]==1?1:0;
                        $row["update_bool"] = $updateBool?1:0;
                        $html.="<tr data-id='{$row['a_id']}' class='win_sse_store'>";
                        if($updateBool){
                            $html.="<td><input type='checkbox' class='store_check_one' value='{$row['a_id']}' /></td>";
                        }
                        if($row["update_bool"]==1){
                            $html.="<td>";
                            $html.=TbHtml::button("<span class='fa fa-remove'></span>",array(
                                'data-load'=>Yii::app()->createUrl('clueSSE/ajaxDelete'),
                                'data-submit'=>Yii::app()->createUrl('clueSSE/ajaxSave'),
                                'data-serialize'=>"ClueSSEForm[scenario]=delete&ClueSSEForm[id]={$row['a_id']}&ClueSSEForm[clue_service_id]=".$model->clue_service_id,
                                'data-obj'=>"#clue_service_store",
                                'class'=>'openDialogForm',
                                'data-fun'=>"select2SSE",
                            ));
                            $html.="</td>";
                        }else{
                            $html.="<td>&nbsp;</td>";
                        }
                        $html.="<td>".htmlspecialchars($row["store_name"])."</td>";
                        $html.="<td>".CGetName::getDistrictStrByKey($row["district"])."</td>";
                        $html.="<td>".htmlspecialchars($row["address"])."</td>";
                        $html.="<td>".htmlspecialchars($row["cust_person"])."</td>";
                        $html.="<td>".htmlspecialchars($row["cust_tel"])."</td>";
                        $html.="<td>".htmlspecialchars($row["invoice_header"])."</td>";
                        $html.="<td>".htmlspecialchars($row["tax_id"])."</td>";
                        $html.="<td>".htmlspecialchars($row["invoice_address"])."</td>";
                        $html.="<td>".CGetName::getAreaStrByArea($row["area"])."</td>";
                        $html.="</tr>";
                        $html.="<tr class='win_sse_form active'>";
                        $html.="<td colspan='".($updateBool?'11':'10')."'>";
                        $html.=$this->renderPartial("//clue/sseForm",array('row'=>$row),true);
                        $html.="</td>";
                        $html.="</tr>";
                    }
                }else{
                    $html.="<tr id='storeNoneTr'><td colspan='".($updateBool?'11':'10')."'>没有绑定门店</td></tr>";
                }
                echo $html;
                ?>
                </tbody>
				<?php if ($updateBool): ?>
					<tfoot>
					<tr>
						<th colspan="<?php echo $updateBool?'11':'10'; ?>" class="text-center">
							<div class="btn-group pull-left">
								<?php echo TbHtml::button('<i class="fa fa-check-square-o"></i> 全选', array(
									'color'=>TbHtml::BUTTON_COLOR_DEFAULT,
									'id'=>'btn_select_all_stores',
									'disabled'=>!$hasRows,
								)); ?>
								<?php echo TbHtml::button('<i class="fa fa-square-o"></i> 取消', array(
									'color'=>TbHtml::BUTTON_COLOR_DEFAULT,
									'id'=>'btn_deselect_all_stores',
									'disabled'=>!$hasRows,
								)); ?>
							</div>
							<div class="btn-group pull-left" style="margin-left: 10px;">
								<?php echo TbHtml::button('<i class="fa fa-wrench"></i> 批量设置', array(
									'color'=>TbHtml::BUTTON_COLOR_INFO,
									'id'=>'btn_batch_update',
									'disabled'=>!$hasRows,
								)); ?>
							</div>
							<div class="btn-group pull-right">
								<?php
								echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('clue','save'), array(
									'data-submit'=>Yii::app()->createUrl('clueSSE/ajaxAllSave'),
									'data-obj'=>"#clue_service_store",
									'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
									'class'=>'sse-all-form-save',
									'disabled'=>!$hasRows,
								));
								?>
							</div>
						</th>
					</tr>
					</tfoot>
				<?php endif ?>
            </table>
        </div>
        
        <!-- 显示总数和分页 -->
        <?php if(!empty($rows)): ?>
        <div class="text-center" style="margin: 10px 0;">
            <div id="store_pagination_container"></div>
            <div style="margin-top: 10px;">
                <span>共 <strong><?php echo $totalStoreCount; ?></strong> 个关联门店，每页显示 
                <select id="page_size_selector" style="width: 80px; display: inline-block;">
                    <option value="10">10</option>
                    <option value="20" selected>20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select> 条</span>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- 批量设置对话框 -->
<div class="modal fade" id="batchUpdateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">批量设置</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="batch_device_group">
                    <label>
                        <input type="checkbox" class="batch_apply_type" data-type="device"> 设备
                    </label>
                    <select class="form-control batch_value_select" id="batch_device_value" multiple disabled></select>
                </div>
                <div class="form-group" id="batch_ware_group">
                    <label>
                        <input type="checkbox" class="batch_apply_type" data-type="ware"> 洁具
                    </label>
                    <select class="form-control batch_value_select" id="batch_ware_value" multiple disabled></select>
                </div>
                <div class="form-group" id="batch_pest_group">
                    <label>
                        <input type="checkbox" class="batch_apply_type" data-type="pest"> 标靶（虫害）
                    </label>
                    <select class="form-control batch_value_select" id="batch_pest_value" multiple disabled></select>
                </div>
                <div class="form-group" id="batch_method_group">
                    <label>
                        <input type="checkbox" class="batch_apply_type" data-type="method"> 处理方式
                    </label>
                    <select class="form-control batch_value_select" id="batch_method_value" multiple disabled></select>
                </div>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> 
                    已选择 <strong id="selected_store_count">0</strong> 个门店
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="btn_confirm_batch_update">
                    <i class="fa fa-check"></i> 确认设置
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    <?php
    $batchUpdateUrl = Yii::app()->createUrl('clueSSE/ajaxBatchUpdate');
    $clueServiceId = $model->clue_service_id;
    $totalStoreCount = !empty($model->clue_service_id) ? CGetName::getClueSSeCountByClueServiceID($model->clue_service_id) : 0;
    
    $js = <<<EOF
// 分页相关变量
var currentStorePage = 1;
var pageSize = 20;
var totalStores = {$totalStoreCount};
var allStoreRows = [];

// 初始化时保存所有门店数据
function initStoreData(){
    allStoreRows = [];
    $('#store_list_tbody tr').each(function(){
        if($(this).hasClass('win_sse_store')){
            var storeRow = $(this);
            var formRow = storeRow.next('tr.win_sse_form');
            allStoreRows.push({
                storeHtml: storeRow.prop('outerHTML'),
                formHtml: formRow.prop('outerHTML')
            });
        }
    });
    renderStorePage(1);
}

// 渲染指定页的门店
function renderStorePage(page){
    currentStorePage = page;
    var start = (page - 1) * pageSize;
    var end = start + pageSize;
    
    var html = '<tr class="hide"><td colspan="11"></td></tr>';
    
    if(allStoreRows.length === 0){
        html += '<tr id="storeNoneTr"><td colspan="11">没有绑定门店</td></tr>';
    } else {
        for(var i = start; i < end && i < allStoreRows.length; i++){
            html += allStoreRows[i].storeHtml;
            html += allStoreRows[i].formHtml;
        }
    }
    
    $('#store_list_tbody').html(html);
    
    // 重新初始化select2
    $('.changePestMethod,.changeDevice,.changeWare').select2({
        tags: false,
        multiple: true,
        allowClear: true,
        closeOnSelect: false,
        disabled: false,
        templateSelection: function(state) {
            var rtn = $('<span style="color:black">'+state.text+'</span>');
            return rtn;
        }
    });
    
    // 渲染分页控件
    renderPagination();
}

// 渲染分页控件
function renderPagination(){
    var totalPages = Math.ceil(allStoreRows.length / pageSize);
    
    if(totalPages <= 1){
        $('#store_pagination_container').html('');
        return;
    }
    
    var html = '<ul class="pagination" style="margin: 0;">';
    
    // 上一页
    if(currentStorePage > 1){
        html += '<li><a href="javascript:void(0);" onclick="renderStorePage(' + (currentStorePage - 1) + ')">&laquo; 上一页</a></li>';
    } else {
        html += '<li class="disabled"><span>&laquo; 上一页</span></li>';
    }
    
    // 页码
    var startPage = Math.max(1, currentStorePage - 2);
    var endPage = Math.min(totalPages, currentStorePage + 2);
    
    if(startPage > 1){
        html += '<li><a href="javascript:void(0);" onclick="renderStorePage(1)">1</a></li>';
        if(startPage > 2){
            html += '<li class="disabled"><span>...</span></li>';
        }
    }
    
    for(var i = startPage; i <= endPage; i++){
        if(i === currentStorePage){
            html += '<li class="active"><span>' + i + '</span></li>';
        } else {
            html += '<li><a href="javascript:void(0);" onclick="renderStorePage(' + i + ')">' + i + '</a></li>';
        }
    }
    
    if(endPage < totalPages){
        if(endPage < totalPages - 1){
            html += '<li class="disabled"><span>...</span></li>';
        }
        html += '<li><a href="javascript:void(0);" onclick="renderStorePage(' + totalPages + ')">' + totalPages + '</a></li>';
    }
    
    // 下一页
    if(currentStorePage < totalPages){
        html += '<li><a href="javascript:void(0);" onclick="renderStorePage(' + (currentStorePage + 1) + ')">下一页 &raquo;</a></li>';
    } else {
        html += '<li class="disabled"><span>下一页 &raquo;</span></li>';
    }
    
    html += '</ul>';
    html += '<div style="margin-top: 5px;">第 ' + currentStorePage + ' / ' + totalPages + ' 页</div>';
    
    $('#store_pagination_container').html(html);
}

// 每页显示数量改变
$('body').off('change', '#page_size_selector').on('change', '#page_size_selector', function(){
    pageSize = parseInt($(this).val());
    renderStorePage(1);
});

// 页面加载完成后初始化
$(document).ready(function(){
    if(allStoreRows.length === 0 && $('#store_list_tbody tr.win_sse_store').length > 0){
        initStoreData();
    }
});

$('body').off('click', '.win_sse_store').on('click', '.win_sse_store', function(e){
    // 如果点击的是复选框，不展开
    if($(e.target).is('input[type="checkbox"]') || $(e.target).closest('.store_check_one').length > 0){
        return;
    }
    // 如果点击的是删除按钮，不展开
    if($(e.target).hasClass('fa-remove') || $(e.target).hasClass('openDialogForm')){
        return;
    }
    
    if($(this).next('tr.win_sse_form').hasClass('active')){
        $(this).next('tr.win_sse_form').removeClass('active');
    }else{
        $(this).next('tr.win_sse_form').addClass('active');
    }
});

// 全选/取消全选
$('body').off('change', '.store_check_all').on('change', '.store_check_all', function(){
    var checked = $(this).prop('checked');
    $('.store_check_one').prop('checked', checked);
    updateSelectedCount();
});

$('body').off('change', '.store_check_one').on('change', '.store_check_one', function(){
    updateSelectedCount();
    // 更新全选checkbox状态
    var total = $('.store_check_one').length;
    var checked = $('.store_check_one:checked').length;
    $('.store_check_all').prop('checked', total === checked);
});

// 全选按钮
$('body').off('click', '#btn_select_all_stores').on('click', '#btn_select_all_stores', function(){
    $('.store_check_one').prop('checked', true);
    $('.store_check_all').prop('checked', true);
    updateSelectedCount();
});

// 取消选择按钮
$('body').off('click', '#btn_deselect_all_stores').on('click', '#btn_deselect_all_stores', function(){
    $('.store_check_one').prop('checked', false);
    $('.store_check_all').prop('checked', false);
    updateSelectedCount();
});

// 更新选中数量
function updateSelectedCount(){
    var count = $('.store_check_one:checked').length;
    $('#selected_store_count').text(count);
}

// 打开批量设置对话框
$('body').off('click', '#btn_batch_update').on('click', '#btn_batch_update', function(){
    var selectedCount = $('.store_check_one:checked').length;
    if(selectedCount === 0){
        alert('请先选择要设置的门店');
        return;
    }
    updateSelectedCount();
    $('.batch_apply_type').prop('checked', false).prop('disabled', false);
    $('#batch_device_group,#batch_ware_group,#batch_pest_group,#batch_method_group').show();
    $('.batch_value_select').each(function(){
        if ($(this).data('select2')) {
            $(this).select2('destroy');
        }
        $(this).html('').val(null).prop('disabled', true);
    });
    fillBatchSelectOptions();
    $('#batchUpdateModal').modal('show');
});

function getBatchOptionsBySelector(selector){
    var options = [];
    var firstSelect = $(selector).first();
    if(firstSelect.length > 0){
        firstSelect.find('option').each(function(){
            var v = $(this).val();
            if(v){
                options.push({
                    value: v,
                    text: $(this).text()
                });
            }
        });
    }
    return options;
}

function fillBatchSelect(selectObj, options){
    var html = '';
    $.each(options, function(i, opt){
        html += '<option value="' + opt.value + '">' + opt.text + '</option>';
    });
    if (selectObj.data('select2')) {
        selectObj.select2('destroy');
    }
    selectObj.html(html).val(null);
    selectObj.select2({
        tags: false,
        multiple: true,
        allowClear: true,
        closeOnSelect: false,
        placeholder: '请选择'
    });
}

function setBatchTypeStatus(type, options){
    var groupObj = $('#batch_' + type + '_group');
    var checkboxObj = $('.batch_apply_type[data-type="' + type + '"]');
    var selectObj = $('#batch_' + type + '_value');
    if(!options || options.length === 0){
        checkboxObj.prop('checked', false).prop('disabled', true);
        selectObj.prop('disabled', true);
        groupObj.hide();
    } else {
        checkboxObj.prop('disabled', false);
        selectObj.prop('disabled', true);
        groupObj.show();
    }
}

function fillBatchSelectOptions(){
    var deviceOptions = getBatchOptionsBySelector('.changeDevice');
    var wareOptions = getBatchOptionsBySelector('.changeWare');
    var pestOptions = getBatchOptionsBySelector('.changePest');
    var methodOptions = getBatchOptionsBySelector('.changeMethod');

    fillBatchSelect($('#batch_device_value'), deviceOptions);
    fillBatchSelect($('#batch_ware_value'), wareOptions);
    fillBatchSelect($('#batch_pest_value'), pestOptions);
    fillBatchSelect($('#batch_method_value'), methodOptions);

    setBatchTypeStatus('device', deviceOptions);
    setBatchTypeStatus('ware', wareOptions);
    setBatchTypeStatus('pest', pestOptions);
    setBatchTypeStatus('method', methodOptions);
}

$('body').off('change', '.batch_apply_type').on('change', '.batch_apply_type', function(){
    var type = $(this).data('type');
    var selectId = '#batch_' + type + '_value';
    var selectObj = $(selectId);
    var checked = $(this).prop('checked');
    selectObj.prop('disabled', !checked);
    if (!checked) {
        selectObj.val(null).trigger('change');
    }
});

// 确认批量设置
$('body').off('click', '#btn_confirm_batch_update').on('click', '#btn_confirm_batch_update', function(){
    var storeIds = [];
    var updateValues = {};
    
    $('.store_check_one:checked').each(function(){
        storeIds.push($(this).val());
    });

    $('.batch_apply_type:checked').each(function(){
        var type = $(this).data('type');
        var selectId = '#batch_' + type + '_value';
        var values = $(selectId).val() || [];
        if(!values || values.length === 0){
            var typeName = '';
            if(type === 'device') typeName = '设备';
            if(type === 'ware') typeName = '洁具';
            if(type === 'pest') typeName = '标靶（虫害）';
            if(type === 'method') typeName = '处理方式';
            alert('请选择' + (typeName ? typeName : '设置') + '内容');
            updateValues = null;
            return false;
        }
        updateValues[type] = values;
    });

    if(updateValues === null){
        return;
    }

    var hasType = false;
    for (var k in updateValues) {
        if (updateValues.hasOwnProperty(k)) {
            hasType = true;
            break;
        }
    }
    if(!hasType){
        alert('请至少选择一个设置类型');
        return;
    }
    
    if(storeIds.length === 0){
        alert('请选择门店');
        return;
    }
    
    // 确认操作
    if(!confirm('确定要为 ' + storeIds.length + ' 个门店批量设置吗？')){
        return;
    }
    
    // 显示加载状态
    $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 处理中...');
    
    $.ajax({
        url: '{$batchUpdateUrl}',
        type: 'POST',
        data: {
            clue_service_id: {$clueServiceId},
            update_values: updateValues,
            store_ids: storeIds
        },
        dataType: 'json',
        success: function(response){
            if(response.status === 1){
                alert(response.message || '批量设置成功');
                var doRefresh = function(){
                    if(response.html){
                        $('#clue_service_store').html(response.html);
                        $('#clue_service_store').find('script').each(function(){
                            if(this.text){
                                $.globalEval(this.text);
                            }else if(this.textContent){
                                $.globalEval(this.textContent);
                            }else if(this.innerHTML){
                                $.globalEval(this.innerHTML);
                            }
                        });
                        select2SSE(response);
                    }
                    if($('.modal-backdrop').length){
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right','');
                    }
                };
                
                if($('#batchUpdateModal').length && ($('#batchUpdateModal').hasClass('in') || $('#batchUpdateModal').is(':visible'))){
                    $('#batchUpdateModal').one('hidden.bs.modal', function(){
                        doRefresh();
                    });
                    $('#batchUpdateModal').modal('hide');
                }else{
                    doRefresh();
                }
            } else {
                alert('批量设置失败: ' + (response.error || '未知错误'));
            }
        },
        error: function(){
            alert('网络错误，请重试');
        },
        complete: function(){
            $('#btn_confirm_batch_update').prop('disabled', false).html('<i class="fa fa-check"></i> 确认设置');
        }
    });
});

function select2SSE(response){
    $('.changePestMethod,.changeDevice,.changeWare').select2({
	    tags: false,
        multiple: true,
        allowClear: true,
        closeOnSelect: false,
        disabled: false,
        templateSelection: function(state) {
            var rtn = $('<span style="color:black">'+state.text+'</span>');
            return rtn;
        }
    });
    
    // 重新初始化分页数据
    if($('#store_list_tbody tr.win_sse_store').length > 0){
        allStoreRows = [];
        initStoreData();
    }
}

// 全局函数，供分页使用
window.renderStorePage = renderStorePage;

EOF;
    echo $js;
    ?>
</script>
