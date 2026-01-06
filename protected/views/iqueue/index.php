<?php
$this->pageTitle=Yii::app()->name . ' - Import Manager';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'queue-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('import','Import Manager'); ?></strong> 

	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
				echo TbHtml::button('<span class="fa fa-refresh"></span> '.Yii::t('misc','Refresh'), array(
					'submit'=>Yii::app()->createUrl('iqueue/index'), 
				)); 
		?>
	</div>
            <div class="pull-right">
                <p style="margin: 7px 0px;">未进行：P，完成：C，错误：E。</p>
            </div>
	</div></div>
	<?php $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('queue','Queue List'),
			'model'=>$model,
				'viewhdr'=>'//iqueue/_listhdr',
				'viewdtl'=>'//iqueue/_listdtl',
				'search'=>array(
							'import_type',
							'import_name',
							'status',
							'username',
						),
		));
	?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
?>

<?php
	$link = Yii::app()->createUrl('iqueue/downExcel');
	$removeUrl = Yii::app()->createUrl('iqueue/remove');
	$js = <<<EOF
function downExcel(id,type) {
    var url = '{$link}';
    url+='?index='+id+'&type='+type;
    window.location.href=url;
}

// 获取会被删除的表列表
function getTablesToDelete(importType) {
    var tables = [];
    if (importType === '导入派单客户' || importType === '导入派单门店' || importType === '线索池导入客户' || importType === '线索导入客户') {
        tables.push('sal_clue (客户主表)');
        tables.push('sal_clue_u_staff (客户负责人)');
        tables.push('sal_clue_u_area (客户区域)');
        tables.push('sal_clue_person (客户联系人)');
        tables.push('sal_clue_rpt (客户报表)');
        tables.push('sal_clue_rpt_file (客户报表文件)');
        tables.push('sal_clue_file (客户文件)');
        tables.push('sal_clue_flow (客户流程)');
        tables.push('sal_clue_invoice (客户发票)');
        tables.push('sal_clue_sre_soe (客户商机门店)');
        tables.push('sal_clue_history (客户历史记录)');
        tables.push('sal_clue_tag_map (客户标签映射)');
        tables.push('sal_clue_service (商机)');
    }
    if (importType === '导入派单门店') {
        tables.push('sal_clue_store (门店主表)');
        tables.push('sal_clue_person (门店联系人)');
        tables.push('sal_clue_sre_soe (门店商机门店)');
        tables.push('sal_clue_history (门店历史记录)');
    }
    if (importType === '导入派单主合约' || importType === '导入派单虚拟合约') {
        tables.push('sal_contract (合约主表)');
        tables.push('sal_contract_virtual (虚拟合约)');
        tables.push('sal_contpro (合约变更)');
        tables.push('sal_contpro_virtual (合约变更虚拟)');
        tables.push('sal_contract_file (合约文件)');
        tables.push('sal_contpro_file (合约变更文件)');
        tables.push('sal_contpro_sse (合约变更门店)');
        tables.push('sal_contract_sse (合约门店)');
        tables.push('sal_contract_call (合约呼叫)');
        tables.push('sal_contract_history (合约历史记录)');
        tables.push('sal_clue_service (关联商机)');
    }
    if (importType === '导入派单虚拟合约') {
        tables.push('sal_contract_vir_info (虚拟合约信息)');
        tables.push('sal_contract_vir_staff (虚拟合约员工)');
        tables.push('sal_contract_vir_week (虚拟合约周期)');
    }
    tables.push('sal_import_queue (导入队列记录)');
    return tables;
}

// 删除确认对话框
var removeIqueueId = 0;
$('.btn-remove').on('click', function() {
    removeIqueueId = $(this).data('id');
    var importName = $(this).data('name');
    var importType = $(this).data('type');
    var username = $(this).data('username') || '未知';
    var reqDt = $(this).data('req-dt') || '未知';
    var finDt = $(this).data('fin-dt') || '未完成';
    var successNum = $(this).data('success-num') || 0;
    var errorNum = $(this).data('error-num') || 0;
    var status = $(this).data('status') || '未知';
    var message = $(this).data('message') || '';
    
    var statusText = '';
    switch(status) {
        case 'P': statusText = '未进行'; break;
        case 'C': statusText = '完成'; break;
        case 'E': statusText = '错误'; break;
        default: statusText = status;
    }
    
    var tablesToDelete = getTablesToDelete(importType);
    
    var html = '<div class="alert alert-warning"><strong>确定要删除导入记录吗？</strong></div>';
    html += '<table class="table table-bordered table-striped" style="margin-bottom: 0;">';
    html += '<tr><td style="width: 120px;"><strong>记录ID：</strong></td><td>' + removeIqueueId + '</td></tr>';
    html += '<tr><td><strong>文件名：</strong></td><td>' + importName + '</td></tr>';
    html += '<tr><td><strong>导入类型：</strong></td><td>' + importType + '</td></tr>';
    html += '<tr><td><strong>导入用户：</strong></td><td>' + username + '</td></tr>';
    html += '<tr><td><strong>请求时间：</strong></td><td>' + reqDt + '</td></tr>';
    html += '<tr><td><strong>完成时间：</strong></td><td>' + finDt + '</td></tr>';
    html += '<tr><td><strong>状态：</strong></td><td>' + statusText + '</td></tr>';
    html += '<tr><td><strong>成功数量：</strong></td><td>' + successNum + '</td></tr>';
    html += '<tr><td><strong>错误数量：</strong></td><td>' + errorNum + '</td></tr>';
    if (message) {
        html += '<tr><td><strong>备注信息：</strong></td><td>' + message + '</td></tr>';
    }
    html += '</table>';
    
    html += '<div style="margin-top: 15px;"><strong>将被删除的数据表（共 ' + tablesToDelete.length + ' 个表）：</strong></div>';
    html += '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-top: 5px;">';
    html += '<ul style="margin-bottom: 0;">';
    for (var i = 0; i < tablesToDelete.length; i++) {
        html += '<li>' + tablesToDelete[i] + '</li>';
    }
    html += '</ul>';
    html += '</div>';
    
    html += '<div class="alert alert-danger" style="margin-top: 15px; margin-bottom: 0;"><strong>警告：此操作将删除上述所有表的数据，且无法恢复！</strong></div>';
    
    $('#removeIqueueDialog .modal-body').html(html);
});

$('#btnConfirmRemove').on('click', function() {
    if (removeIqueueId > 0) {
        var btn = $(this);
        btn.prop('disabled', true).text('删除中...');
        $.ajax({
            type: 'GET',
            url: '{$removeUrl}',
            data: {index: removeIqueueId},
            dataType: 'json',
            success: function(data) {
                $('#removeIqueueDialog').modal('hide');
                if (data.status === 'success') {
                    // 显示成功消息
                    var successMsg = '删除成功：' + data.message;
                    // 使用更友好的提示方式
                    if (typeof toastr !== 'undefined') {
                        toastr.success(successMsg);
                    } else {
                        alert(successMsg);
                    }
                    // 提交表单刷新列表（保持搜索条件和分页状态）
                    $('#queue-list').submit();
                } else {
                    alert('删除失败：' + (data.message || '未知错误'));
                    btn.prop('disabled', false).text('确定删除');
                }
            },
            error: function(xhr, status, error) {
                alert('删除失败，请重试。错误信息：' + error);
                btn.prop('disabled', false).text('确定删除');
            }
        });
    }
});
EOF;
	Yii::app()->clientScript->registerScript('iqueueActions',$js,CClientScript::POS_READY);
?>

<?php
// 删除确认对话框
$this->widget('bootstrap.widgets.TbModal', array(
    'id'=>'removeIqueueDialog',
    'header'=>'删除导入记录',
    'content'=>'<div class="modal-body"></div>',
    'footer'=>array(
        TbHtml::button('确定删除', array('id'=>'btnConfirmRemove','color'=>TbHtml::BUTTON_COLOR_DANGER)),
        TbHtml::button('取消', array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_DEFAULT)),
    ),
    'show'=>false,
));
?>

<?php $this->endWidget(); ?>

