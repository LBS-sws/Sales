<?php
$this->pageTitle=Yii::app()->name . ' - 手动同步';
?>

<section class="content-header">
	<h1>
		<strong>手动同步到派单系统</strong>
	</h1>
</section>

<section class="content">
	<div class="box box-info">
		<div class="box-header with-border">
			<h3 class="box-title">选择客户</h3>
		</div>
		<div class="box-body">
			<div class="form-group">
				<label class="col-lg-2 control-label">客户ID/编号/名称：</label>
				<div class="col-lg-4">
					<input type="text" class="form-control" id="clue_search" placeholder="输入客户ID、编号或名称搜索">
					<input type="hidden" id="clue_id" value="">
				</div>
				<div class="col-lg-2">
					<button type="button" class="btn btn-primary" id="btn_search">搜索</button>
				</div>
			</div>
			<div class="form-group" id="client_info" style="display:none;">
				<div class="col-lg-12">
					<div class="alert alert-info">
						<strong>客户信息：</strong>
						<span id="client_name"></span> 
						<span id="client_code"></span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="box box-success" id="data_selection_box" style="display:none;">
		<div class="box-header with-border">
			<h3 class="box-title">选择要同步的数据</h3>
		</div>
		<div class="box-body">
			<div style="margin-bottom: 15px;">
				<button type="button" class="btn btn-success" id="btn_load_store">
					<i class="fa fa-building"></i> 查看门店列表
				</button>
				<button type="button" class="btn btn-info" id="btn_load_store_person">
					<i class="fa fa-users"></i> 查看门店联络人列表
				</button>
				<button type="button" class="btn btn-warning" id="btn_load_contract">
					<i class="fa fa-file-text"></i> 查看虚拟合约列表
				</button>
			</div>
			
			<!-- 统一的数据列表容器 -->
			<div id="data_list_container" style="display:none; margin-top: 20px;">
				<div style="margin-bottom: 10px;">
					<label style="margin-right: 15px;">
						<input type="checkbox" id="check_all_data"> 全选/取消全选
					</label>
					<button type="button" class="btn btn-primary pull-right" id="btn_sync_selected" style="display:none;">
						<i class="fa fa-send"></i> 同步选中的数据
					</button>
					<div class="clearfix"></div>
				</div>
				<div class="table-responsive">
					<table class="table table-bordered table-striped">
						<thead id="data_list_thead">
						</thead>
						<tbody id="data_list_tbody">
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div class="box box-default">
		<div class="box-header with-border">
			<h3 class="box-title">说明</h3>
		</div>
		<div class="box-body">
			<ul>
				<li><strong>操作流程：</strong>选择客户 → 查看数据列表 → 勾选要同步的数据 → 确认同步</li>
				<li><strong>操作类型：</strong>新增（u_id为空）表示首次同步，更新（u_id不为空）表示重新同步</li>
				<li><strong>门店联络人：</strong>只显示手机号不为空的联络人，且需要门店已同步（u_id不为空）</li>
				<li><strong>虚拟合约：</strong>按合同分组显示，同步时会同步该合同下的所有虚拟合约</li>
				<li>同步任务提交后，可以在"同步记录"页面查看同步状态</li>
			</ul>
		</div>
	</div>

	<div id="sync_result" style="margin-top: 20px;"></div>
</section>

<style>
/* 确保列表容器正确显示 */
#store_list_container,
#store_person_list_container,
#contract_list_container {
	display: block !important;
	visibility: visible !important;
	opacity: 1 !important;
	min-height: 100px;
}
#store_list_container table,
#store_person_list_container table,
#contract_list_container table {
	width: 100%;
	margin-bottom: 0;
}
</style>

<script>
$(document).ready(function(){
	var searchUrl = '<?php echo Yii::app()->createUrl('ajax/getClientInfo'); ?>';
	var getStoreListUrl = '<?php echo Yii::app()->createUrl('manualSync/getStoreList'); ?>';
	var getStorePersonListUrl = '<?php echo Yii::app()->createUrl('manualSync/getStorePersonList'); ?>';
	var getContractListUrl = '<?php echo Yii::app()->createUrl('manualSync/getContractList'); ?>';
	var syncStoreUrl = '<?php echo Yii::app()->createUrl('manualSync/syncStore'); ?>';
	var syncStorePersonUrl = '<?php echo Yii::app()->createUrl('manualSync/syncStorePerson'); ?>';
	var syncContractUrl = '<?php echo Yii::app()->createUrl('manualSync/syncContract'); ?>';

	// 搜索客户
	$('#btn_search').click(function(){
		var keyword = $('#clue_search').val().trim();
		if(!keyword){
			alert('请输入客户ID、编号或名称');
			return;
		}

		$.ajax({
			type: 'POST',
			url: searchUrl,
			data: {keyword: keyword},
			dataType: 'json',
			success: function(data){
				if(data.status == 1){
					$('#clue_id').val(data.clue_id);
					$('#client_name').text('客户名称：' + data.cust_name);
					$('#client_code').text('客户编号：' + data.clue_code);
					$('#client_info').show();
					$('#data_selection_box').show();
					$('#sync_result').html('');
					// 隐藏所有列表
					$('#store_list_container').hide();
					$('#store_person_list_container').hide();
					$('#contract_list_container').hide();
				}else{
					alert(data.message || '未找到客户');
					$('#client_info').hide();
					$('#data_selection_box').hide();
				}
			},
			error: function(){
				alert('搜索失败，请重试');
			}
		});
	});

	// 回车搜索
	$('#clue_search').keypress(function(e){
		if(e.which == 13){
			$('#btn_search').click();
		}
	});

	// 查看门店列表
	$('#btn_load_store').click(function(){
		var clue_id = $('#clue_id').val();
		if(!clue_id){
			alert('请先选择客户');
			return;
		}

		$(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 加载中...');
		$.ajax({
			type: 'POST',
			url: getStoreListUrl,
			data: {clue_id: clue_id},
			dataType: 'json',
			success: function(data){
				$('#btn_load_store').prop('disabled', false).html('<i class="fa fa-building"></i> 查看门店列表');
				if(data && data.status == 1){
					currentDataType = 'store';
					
					// 设置表头
					var thead = '<tr>' +
						'<th style="width: 40px;">选择</th>' +
						'<th>门店编号</th>' +
						'<th>门店名称</th>' +
						'<th>地址</th>' +
						'<th>操作类型</th>' +
						'<th>状态</th>' +
						'</tr>';
					$('#data_list_thead').html(thead);
					
					// 设置表体
					var html = '';
					if(!data.data || data.count == 0){
						html = '<tr><td colspan="6" class="text-center">没有需要同步的门店</td></tr>';
					}else{
						$.each(data.data, function(index, item){
							var statusText = item.status == '新增' ? '<span class="label label-success">新增</span>' : '<span class="label label-info">更新</span>';
							html += '<tr>';
							html += '<td><input type="checkbox" class="data_checkbox" data-type="store" value="' + item.id + '" data-u_id="' + (item.u_id || '') + '"></td>';
							html += '<td>' + (item.store_code || '') + '</td>';
							html += '<td>' + (item.store_name || '') + '</td>';
							html += '<td>' + (item.address || '') + '</td>';
							html += '<td>' + statusText + '</td>';
							html += '<td>' + (item.store_status || '') + '</td>';
							html += '</tr>';
						});
					}
					$('#data_list_tbody').html(html);
					
					// 设置同步按钮
					$('#btn_sync_selected').removeClass('btn-success btn-info btn-warning').addClass('btn-success')
						.html('<i class="fa fa-send"></i> 同步选中的门店').show();
					
					// 显示容器
					$('#data_selection_box').show();
					$('#data_list_container').show();
					$('#check_all_data').prop('checked', false);
				}else{
					var errorMsg = (data && data.message) ? data.message : '加载失败';
					alert(errorMsg);
				}
			},
			error: function(xhr, status, error){
				$('#btn_load_store').prop('disabled', false).html('<i class="fa fa-building"></i> 查看门店列表');
				console.error('AJAX错误:', status, error);
				alert('加载失败，请重试。错误信息：' + error);
			}
		});
	});

	// 查看门店联络人列表
	$('#btn_load_store_person').click(function(){
		var clue_id = $('#clue_id').val();
		if(!clue_id){
			alert('请先选择客户');
			return;
		}

		$(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 加载中...');
		$.ajax({
			type: 'POST',
			url: getStorePersonListUrl,
			data: {clue_id: clue_id},
			dataType: 'json',
			success: function(data){
				$('#btn_load_store_person').prop('disabled', false).html('<i class="fa fa-users"></i> 查看门店联络人列表');
				if(data && data.status == 1){
					currentDataType = 'store_person';
					
					// 设置表头
					var thead = '<tr>' +
						'<th style="width: 40px;">选择</th>' +
						'<th>门店名称</th>' +
						'<th>联络人姓名</th>' +
						'<th>手机号码</th>' +
						'<th>邮箱</th>' +
						'<th>职务</th>' +
						'<th>操作类型</th>' +
						'</tr>';
					$('#data_list_thead').html(thead);
					
					// 设置表体
					var html = '';
					var validCount = 0;
					if(!data.data || data.count == 0){
						html = '<tr><td colspan="7" class="text-center">没有需要同步的门店联络人</td></tr>';
					}else{
						$.each(data.data, function(index, item){
							var statusText = item.status == '新增' ? '<span class="label label-success">新增</span>' : '<span class="label label-info">更新</span>';
							var disabledText = !item.store_u_id ? '<span class="text-danger">(门店未同步)</span>' : '';
							html += '<tr' + (!item.store_u_id ? ' class="warning"' : '') + '>';
							if(item.store_u_id){
								html += '<td><input type="checkbox" class="data_checkbox" data-type="store_person" value="' + item.id + '" data-u_id="' + (item.u_id || '') + '"></td>';
							}else{
								html += '<td><input type="checkbox" disabled title="门店未同步，无法同步此联络人"></td>';
							}
							html += '<td>' + (item.store_name || '') + ' ' + disabledText + '</td>';
							html += '<td>' + (item.person_name || '') + '</td>';
							html += '<td>' + (item.person_tel || '') + '</td>';
							html += '<td>' + (item.person_email || '') + '</td>';
							html += '<td>' + (item.person_role || '') + '</td>';
							html += '<td>' + statusText + '</td>';
							html += '</tr>';
							if(item.store_u_id){
								validCount++;
							}
						});
						if(validCount == 0 && data.count > 0){
							html = '<tr><td colspan="7" class="text-center text-danger">该客户下没有已同步的门店，请先同步门店</td></tr>';
						}
					}
					$('#data_list_tbody').html(html);
					
					// 设置同步按钮
					$('#btn_sync_selected').removeClass('btn-success btn-info btn-warning').addClass('btn-info')
						.html('<i class="fa fa-send"></i> 同步选中的联络人').show();
					
					// 显示容器
					$('#data_selection_box').show();
					$('#data_list_container').show();
					$('#check_all_data').prop('checked', false);
				}else{
					var errorMsg = (data && data.message) ? data.message : '加载失败';
					alert(errorMsg);
				}
			},
			error: function(xhr, status, error){
				$('#btn_load_store_person').prop('disabled', false).html('<i class="fa fa-users"></i> 查看门店联络人列表');
				alert('加载失败，请重试');
			}
		});
	});

	// 查看虚拟合约列表
	$('#btn_load_contract').click(function(){
		var clue_id = $('#clue_id').val();
		if(!clue_id){
			alert('请先选择客户');
			return;
		}

		$(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 加载中...');
		$.ajax({
			type: 'POST',
			url: getContractListUrl,
			data: {clue_id: clue_id},
			dataType: 'json',
			success: function(data){
				$('#btn_load_contract').prop('disabled', false).html('<i class="fa fa-file-text"></i> 查看虚拟合约列表');
				if(data && data.status == 1){
					currentDataType = 'contract';
					
					// 设置表头
					var thead = '<tr>' +
						'<th style="width: 40px;">选择</th>' +
						'<th>合同编号</th>' +
						'<th>虚拟合约编号</th>' +
						'<th>开始日期</th>' +
						'<th>结束日期</th>' +
						'<th>操作类型</th>' +
						'<th>状态</th>' +
						'</tr>';
					$('#data_list_thead').html(thead);
					
					// 设置表体
					var html = '';
					if(!data.data || data.count == 0){
						html = '<tr><td colspan="7" class="text-center">没有需要同步的虚拟合约</td></tr>';
					}else{
						// 按合同分组
						var contractMap = {};
						$.each(data.data, function(index, item){
							if(!contractMap[item.cont_id]){
								contractMap[item.cont_id] = {
									cont_code: item.cont_code,
									cont_start_dt: item.cont_start_dt,
									cont_end_dt: item.cont_end_dt,
									virs: []
								};
							}
							contractMap[item.cont_id].virs.push(item);
						});

						$.each(contractMap, function(contId, contData){
							var rowspan = contData.virs.length;
							var firstRow = true;
							$.each(contData.virs, function(virIndex, virItem){
								var statusText = virItem.status == '新增' ? '<span class="label label-success">新增</span>' : '<span class="label label-info">更新</span>';
								html += '<tr>';
								if(firstRow){
									html += '<td rowspan="' + rowspan + '"><input type="checkbox" class="data_checkbox" data-type="contract" value="' + contId + '"></td>';
									html += '<td rowspan="' + rowspan + '">' + (contData.cont_code || '') + '</td>';
									firstRow = false;
								}
								html += '<td>' + (virItem.vir_code || '') + '</td>';
								html += '<td>' + (contData.cont_start_dt || '') + '</td>';
								html += '<td>' + (contData.cont_end_dt || '') + '</td>';
								html += '<td>' + statusText + '</td>';
								html += '<td>' + (virItem.vir_status || '') + '</td>';
								html += '</tr>';
							});
						});
					}
					$('#data_list_tbody').html(html);
					
					// 设置同步按钮
					$('#btn_sync_selected').removeClass('btn-success btn-info btn-warning').addClass('btn-warning')
						.html('<i class="fa fa-send"></i> 同步选中的合同').show();
					
					// 显示容器
					$('#data_selection_box').show();
					$('#data_list_container').show();
					$('#check_all_data').prop('checked', false);
				}else{
					var errorMsg = (data && data.message) ? data.message : '加载失败';
					alert(errorMsg);
				}
			},
			error: function(xhr, status, error){
				$('#btn_load_contract').prop('disabled', false).html('<i class="fa fa-file-text"></i> 查看虚拟合约列表');
				alert('加载失败，请重试');
			}
		});
	});


	// 统一全选
	$('#check_all_data').change(function(){
		$('.data_checkbox:not(:disabled)').prop('checked', $(this).prop('checked'));
	});

	// 统一同步按钮
	$('#btn_sync_selected').click(function(){
		var clue_id = $('#clue_id').val();
		if(!clue_id){
			alert('请先选择客户');
			return;
		}

		if(!currentDataType){
			alert('请先加载数据列表');
			return;
		}

		var selectedIds = [];
		$('.data_checkbox[data-type="' + currentDataType + '"]:checked').each(function(){
			var val = $(this).val();
			if($.inArray(val, selectedIds) == -1){
				selectedIds.push(val);
			}
		});

		if(selectedIds.length == 0){
			var typeName = currentDataType == 'store' ? '门店' : (currentDataType == 'store_person' ? '门店联络人' : '合同');
			alert('请至少选择一个' + typeName);
			return;
		}

		var typeName = currentDataType == 'store' ? '门店' : (currentDataType == 'store_person' ? '门店联络人' : '合同');
		if(!confirm('确定要同步选中的 ' + selectedIds.length + ' 个' + typeName + '吗？')){
			return;
		}

		var $btn = $(this);
		$btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 同步中...');
		
		var url, dataParam, refreshBtn;
		if(currentDataType == 'store'){
			url = syncStoreUrl;
			dataParam = {clue_id: clue_id, store_ids: selectedIds};
			refreshBtn = '#btn_load_store';
		}else if(currentDataType == 'store_person'){
			url = syncStorePersonUrl;
			dataParam = {clue_id: clue_id, person_ids: selectedIds};
			refreshBtn = '#btn_load_store_person';
		}else{
			url = syncContractUrl;
			dataParam = {clue_id: clue_id, cont_ids: selectedIds};
			refreshBtn = '#btn_load_contract';
		}

		$.ajax({
			type: 'POST',
			url: url,
			data: dataParam,
			dataType: 'json',
			success: function(data){
				var btnText = currentDataType == 'store' ? '<i class="fa fa-send"></i> 同步选中的门店' : 
					(currentDataType == 'store_person' ? '<i class="fa fa-send"></i> 同步选中的联络人' : '<i class="fa fa-send"></i> 同步选中的合同');
				$btn.prop('disabled', false).html(btnText);
				if(data.status == 1){
					$('#sync_result').html('<div class="alert alert-success">' + data.message + '</div>');
					// 刷新列表
					$(refreshBtn).click();
				}else{
					$('#sync_result').html('<div class="alert alert-danger">' + data.message + '</div>');
				}
			},
			error: function(){
				var btnText = currentDataType == 'store' ? '<i class="fa fa-send"></i> 同步选中的门店' : 
					(currentDataType == 'store_person' ? '<i class="fa fa-send"></i> 同步选中的联络人' : '<i class="fa fa-send"></i> 同步选中的合同');
				$('#sync_result').html('<div class="alert alert-danger">同步失败，请重试</div>');
				$btn.prop('disabled', false).html(btnText);
			}
		});
	});

	// 同步选中的虚拟合约（保留兼容，但已废弃）
	$('#btn_sync_contract_selected').click(function(){
		var clue_id = $('#clue_id').val();
		if(!clue_id){
			alert('请先选择客户');
			return;
		}

		var selectedIds = [];
		$('.contract_checkbox:checked').each(function(){
			var contId = $(this).val();
			if($.inArray(contId, selectedIds) == -1){
				selectedIds.push(contId);
			}
		});

		if(selectedIds.length == 0){
			alert('请至少选择一个合同');
			return;
		}

		if(!confirm('确定要同步选中的 ' + selectedIds.length + ' 个合同的虚拟合约吗？')){
			return;
		}

		$(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 同步中...');
		$.ajax({
			type: 'POST',
			url: syncContractUrl,
			data: {clue_id: clue_id, cont_ids: selectedIds},
			dataType: 'json',
			success: function(data){
				$('#btn_sync_contract_selected').prop('disabled', false).html('<i class="fa fa-send"></i> 同步选中的合同');
				if(data.status == 1){
					$('#sync_result').html('<div class="alert alert-success">' + data.message + '</div>');
					// 刷新列表
					$('#btn_load_contract').click();
				}else{
					$('#sync_result').html('<div class="alert alert-danger">' + data.message + '</div>');
				}
			},
			error: function(){
				$('#sync_result').html('<div class="alert alert-danger">同步失败，请重试</div>');
				$('#btn_sync_contract_selected').prop('disabled', false).html('<i class="fa fa-send"></i> 同步选中的合同');
			}
		});
	});
});
</script>
