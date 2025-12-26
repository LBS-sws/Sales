<?php
$this->pageTitle=Yii::app()->name . ' - 确认主合同合并删除';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'cont-merge-confirm-form',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo '确认主合同合并删除'; ?></strong>
    </h1>
</section>

<section class="content">
    <div class="box box-danger">
        <div class="box-header with-border">
            <h3 class="box-title">要删除的主合同信息</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="150">主合同编号：</th>
                            <td><?php echo $model->sourceContRow['cont_code']; ?></td>
                        </tr>
                        <tr>
                            <th>合同状态：</th>
                            <td><?php echo CGetName::getContTopStatusStrByKey($model->sourceContRow['cont_status']); ?></td>
                        </tr>
                        <tr>
                            <th>合同金额：</th>
                            <td class="text-danger"><strong>￥<?php echo number_format($model->sourceContRow['total_amt'], 2); ?></strong></td>
                        </tr>
                        <tr>
                            <th>门店数量：</th>
                            <td><?php echo $model->sourceContRow['store_sum']; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="150">业务大类：</th>
                            <td><?php echo CGetName::getYewudaleiStrByKey($model->sourceContRow['yewudalei'],'name'); ?></td>
                        </tr>
                        <tr>
                            <th>主体公司：</th>
                            <td><?php echo CGetName::getLbsMainNameByKey($model->sourceContRow['lbs_main']); ?></td>
                        </tr>
                        <tr>
                            <th>合约开始时间：</th>
                            <td><?php echo $model->sourceContRow['cont_start_dt']; ?></td>
                        </tr>
                        <tr>
                            <th>合约结束时间：</th>
                            <td><?php echo $model->sourceContRow['cont_end_dt']; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">关联数据统计</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <?php foreach ($model->relatedData as $key => $data): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-<?php echo $data['count'] > 0 ? 'yellow' : 'gray'; ?>">
                            <i class="fa fa-database"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text"><?php echo $data['name']; ?></span>
                            <span class="info-box-number"><?php echo $data['count']; ?> 条</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="box box-warning">
        <div class="box-header with-border">
            <h3 class="box-title">关联数据详情</h3>
        </div>
        <div class="box-body">
            <!-- 虚拟合同列表 -->
            <?php if (!empty($relatedDetail['virtual'])): ?>
            <h4>虚拟合同 (<?php echo count($relatedDetail['virtual']); ?>)</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="bg-info">
                            <th>虚拟合约编号</th>
                            <th>门店编号</th>
                            <th>门店名称</th>
                            <th>服务项目</th>
                            <th>状态</th>
                            <th>合约金额</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($relatedDetail['virtual'] as $vir): ?>
                        <tr>
                            <td><?php echo $vir['vir_code']; ?></td>
                            <td><?php echo $vir['store_code']; ?></td>
                            <td><?php echo $vir['store_name']; ?></td>
                            <td><?php echo $vir['busine_id_text']; ?></td>
                            <td><?php echo CGetName::getContTopStatusStrByKey($vir['vir_status']); ?></td>
                            <td class="text-right">￥<?php echo number_format($vir['year_amt'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- 关联门店列表 -->
            <?php if (!empty($relatedDetail['sse'])): ?>
            <h4>关联门店 (<?php echo count($relatedDetail['sse']); ?>)</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="bg-info">
                            <th>门店编号</th>
                            <th>门店名称</th>
                            <th>服务项目</th>
                            <th>服务金额</th>
                            <th>服务次数</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($relatedDetail['sse'] as $sse): ?>
                        <tr>
                            <td><?php echo $sse['store_code']; ?></td>
                            <td><?php echo $sse['store_name']; ?></td>
                            <td><?php echo $sse['busine_id_text']; ?></td>
                            <td class="text-right">￥<?php echo number_format($sse['store_amt'], 2); ?></td>
                            <td class="text-center"><?php echo $sse['service_sum']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- 合同附件列表 -->
            <?php if (!empty($relatedDetail['file'])): ?>
            <h4>合同附件 (<?php echo count($relatedDetail['file']); ?>)</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="bg-info">
                            <th>文件名称</th>
                            <th>上传时间</th>
                            <th>上传人</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($relatedDetail['file'] as $file): ?>
                        <tr>
                            <td><?php echo $file['file_name']; ?></td>
                            <td><?php echo $file['lcd']; ?></td>
                            <td><?php echo $file['lcu']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- 历史记录 -->
            <?php if (!empty($relatedDetail['history'])): ?>
            <h4>历史记录 (<?php echo count($relatedDetail['history']); ?>)</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="bg-info">
                            <th>操作内容</th>
                            <th>操作时间</th>
                            <th>操作人</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($relatedDetail['history'] as $history): ?>
                        <tr>
                            <td><?php echo $history['history_html']; ?></td>
                            <td><?php echo $history['lcd']; ?></td>
                            <td><?php echo $history['lcu']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- 合同操作记录 -->
            <?php if (!empty($relatedDetail['contpro'])): ?>
            <h4>合同操作记录 (<?php echo count($relatedDetail['contpro']); ?>)</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="bg-info">
                            <th>操作编号</th>
                            <th>操作类型</th>
                            <th>操作日期</th>
                            <th>变更金额</th>
                            <th>状态</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($relatedDetail['contpro'] as $pro): ?>
                        <tr>
                            <td><?php echo $pro['pro_code']; ?></td>
                            <td><?php echo $pro['pro_type']; ?></td>
                            <td><?php echo $pro['pro_date']; ?></td>
                            <td class="text-right">￥<?php echo number_format($pro['pro_change'], 2); ?></td>
                            <td><?php echo CGetName::getContTopStatusStrByKey($pro['pro_status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- 呼叫式服务申请 -->
            <?php if (!empty($relatedDetail['call'])): ?>
            <h4>呼叫式服务申请 (<?php echo count($relatedDetail['call']); ?>)</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="bg-info">
                            <th>申请编号</th>
                            <th>服务项目</th>
                            <th>申请日期</th>
                            <th>门店数量</th>
                            <th>呼叫次数</th>
                            <th>呼叫金额</th>
                            <th>状态</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($relatedDetail['call'] as $call): ?>
                        <tr>
                            <td><?php echo $call['call_code']; ?></td>
                            <td><?php echo $call['busine_id_text']; ?></td>
                            <td><?php echo $call['apply_date']; ?></td>
                            <td class="text-center"><?php echo $call['store_num']; ?></td>
                            <td class="text-center"><?php echo $call['call_sum']; ?></td>
                            <td class="text-right">￥<?php echo number_format($call['call_amt'], 2); ?></td>
                            <td><?php echo CGetName::getContTopStatusStrByKey($call['call_status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title">选择要保留的目标主合同</h3>
        </div>
        <div class="box-body">
            <div class="alert alert-warning">
                <i class="icon fa fa-warning"></i>
                请选择要将上述数据迁移到的目标主合同。选择后，系统将自动：
                <ol>
                    <li>将所有关联数据迁移到目标主合同</li>
                    <li>删除源主合同</li>
                    <li>添加操作日志</li>
                </ol>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr class="bg-success">
                            <th width="80">选择</th>
                            <th>主合同编号</th>
                            <th>合同状态</th>
                            <th>业务大类</th>
                            <th>主体公司</th>
                            <th>合同金额</th>
                            <th>门店数量</th>
                            <th>合约时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($targetContractList as $contract): ?>
                        <tr>
                            <td class="text-center">
                                <?php echo TbHtml::radioButton('ContMergeForm[target_cont_id]', false, array(
                                    'value'=>$contract['id'],
                                    'uncheckValue'=>null
                                )); ?>
                            </td>
                            <td><?php echo $contract['cont_code']; ?></td>
                            <td><?php echo CGetName::getContTopStatusStrByKey($contract['cont_status']); ?></td>
                            <td><?php echo $contract['yewudalei_name']; ?></td>
                            <td><?php echo $contract['lbs_main_name']; ?></td>
                            <td class="text-right">￥<?php echo number_format($contract['total_amt'], 2); ?></td>
                            <td class="text-center"><?php echo $contract['store_sum']; ?></td>
                            <td><?php echo $contract['cont_start_dt'].' 至 '.$contract['cont_end_dt']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php 
            // 传递多个源合同ID
            if (!empty($model->source_cont_ids) && is_array($model->source_cont_ids)) {
                echo CHtml::hiddenField('ContMergeForm[source_cont_ids]', implode(',', $model->source_cont_ids));
            } else {
                echo CHtml::hiddenField('ContMergeForm[source_cont_id]', $model->source_cont_id);
            }
            ?>
            <?php echo CHtml::hiddenField('ContMergeForm[clue_id]', $model->sourceContRow['clue_id']); ?>
            <?php echo CHtml::hiddenField('ContMergeForm[step]', 'merge'); ?>

            <div class="form-group">
                <div class="text-center">
                    <?php echo TbHtml::submitButton('确认合并并删除', array(
                        'color'=>TbHtml::BUTTON_COLOR_DANGER,
                        'size'=>TbHtml::BUTTON_SIZE_LARGE,
                        'onclick'=>'return confirmMerge();'
                    )); ?>
                    <?php echo TbHtml::button('返回上一步', array(
                        'color'=>TbHtml::BUTTON_COLOR_DEFAULT,
                        'size'=>TbHtml::BUTTON_SIZE_LARGE,
                        'onclick'=>'history.back();'
                    )); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $this->endWidget(); ?>

<script>
function confirmMerge() {
    var selected = $('input[name="ContMergeForm[target_cont_id]"]:checked').val();
    if (!selected) {
        alert('请选择要保留的目标主合同');
        return false;
    }
    
    var msg = '确认要执行以下操作吗？\n\n';
    <?php if (!empty($model->source_cont_ids) && is_array($model->source_cont_ids)): ?>
    msg += '1. 将 <?php echo count($model->source_cont_ids); ?> 个主合同（ID: <?php echo implode(', ', $model->source_cont_ids); ?>）的所有关联数据迁移到主合同 #' + selected + '\n';
    msg += '2. 删除这 <?php echo count($model->source_cont_ids); ?> 个主合同\n\n';
    <?php else: ?>
    msg += '1. 将主合同 #<?php echo $model->source_cont_id; ?> 的所有关联数据迁移到主合同 #' + selected + '\n';
    msg += '2. 删除主合同 #<?php echo $model->source_cont_id; ?>\n\n';
    <?php endif; ?>
    msg += '此操作不可撤销，请谨慎操作！';
    
    return confirm(msg);
}

$(document).ready(function(){
    $('#cont-merge-confirm-form').attr('action', '<?php echo Yii::app()->createUrl('contHead/mergeSave'); ?>');
});
</script>

