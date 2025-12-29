<?php
$this->pageTitle=Yii::app()->name . ' - 主合同合并删除结果';
?>

<style>
.result-container {
    max-height: calc(100vh - 250px);
    overflow-y: auto;
    overflow-x: hidden;
}
.log-table-container {
    max-height: 400px;
    overflow-y: auto;
}
.collapsible-section {
    cursor: pointer;
}
.collapsible-section:hover {
    background-color: #f5f5f5;
}
</style>

<section class="content-header">
    <h1>
        <strong>主合同合并删除结果</strong>
    </h1>
</section>

<section class="content">
    <div class="box">
        <div class="box-body result-container">
            <?php if ($successCount > 0): ?>
            <div class="alert alert-success">
                <h4><i class="icon fa fa-check"></i> 成功！</h4>
                <p>成功合并删除 <strong><?php echo $successCount; ?></strong> 个主合同</p>
                <?php if (!empty($failedContracts)): ?>
                <p class="text-warning">失败的主合同ID：<?php echo implode(', ', $failedContracts); ?></p>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-danger">
                <h4><i class="icon fa fa-ban"></i> 失败！</h4>
                <p>所有主合同合并操作失败</p>
            </div>
            <?php endif; ?>

            <h3>操作详情</h3>
            
            <?php foreach ($allLogs as $logGroup): ?>
            <div class="box box-<?php echo $logGroup['status'] == 'success' ? 'success' : 'danger'; ?>">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-<?php echo $logGroup['status'] == 'success' ? 'check-circle' : 'times-circle'; ?>"></i>
                        批量合并操作
                        <?php if (!empty($logGroup['source_ids'])): ?>
                            (处理 <?php echo count($logGroup['source_ids']); ?> 个主合同)
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="box-body">
                    <?php if (!empty($logGroup['logs'])): ?>
                        <div class="log-table-container">
                            <table class="table table-bordered table-striped table-condensed">
                                <thead style="position: sticky; top: 0; background: white; z-index: 10;">
                                    <tr>
                                        <th width="40">#</th>
                                        <th width="200">操作步骤</th>
                                        <th width="80">数量</th>
                                        <th width="80">状态</th>
                                        <th>备注</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $index = 1; ?>
                                    <?php foreach ($logGroup['logs'] as $log): ?>
                                    <tr class="<?php echo $log['status'] == 'success' ? 'success' : 'danger'; ?>">
                                        <td><?php echo $index++; ?></td>
                                        <td><strong><?php echo $log['step']; ?></strong></td>
                                        <td class="text-center">
                                            <?php if ($log['count'] > 0): ?>
                                                <span class="badge bg-blue"><?php echo $log['count']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($log['status'] == 'success'): ?>
                                                <span class="label label-success"><i class="fa fa-check"></i> 成功</span>
                                            <?php else: ?>
                                                <span class="label label-danger"><i class="fa fa-times"></i> 失败</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($log['msg'])): ?>
                                                <?php 
                                                // 如果消息太长，折叠显示
                                                $msg = $log['msg'];
                                                if (mb_strlen($msg, 'UTF-8') > 100) {
                                                    $shortMsg = mb_substr($msg, 0, 100, 'UTF-8') . '...';
                                                    echo '<span class="msg-short">' . $shortMsg . '</span>';
                                                    echo '<span class="msg-full" style="display:none;">' . $msg . '</span>';
                                                    echo ' <a href="javascript:void(0);" class="toggle-msg text-primary"><small>[展开]</small></a>';
                                                } else {
                                                    echo $msg;
                                                }
                                                ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($logGroup['status'] == 'error' && !empty($logGroup['errors'])): ?>
                        <div class="alert alert-danger">
                            <strong>错误信息：</strong>
                            <?php 
                            foreach ($logGroup['errors'] as $field => $errors) {
                                if (is_array($errors)) {
                                    echo implode('<br/>', $errors);
                                }
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="form-group text-center">
                <?php echo TbHtml::button('查看目标主合同', array(
                    'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                    'size'=>TbHtml::BUTTON_SIZE_LARGE,
                    'onclick'=>'window.location.href="'.Yii::app()->createUrl('contHead/detail', array('index'=>$model->target_cont_id)).'";'
                )); ?>
                <?php echo TbHtml::button('返回主合同列表', array(
                    'color'=>TbHtml::BUTTON_COLOR_DEFAULT,
                    'size'=>TbHtml::BUTTON_SIZE_LARGE,
                    'onclick'=>'window.location.href="'.Yii::app()->createUrl('contHead/index').'";'
                )); ?>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function(){
    // 切换消息展开/折叠
    $('.toggle-msg').on('click', function(){
        var $this = $(this);
        var $short = $this.siblings('.msg-short');
        var $full = $this.siblings('.msg-full');
        
        if ($full.is(':visible')) {
            $full.hide();
            $short.show();
            $this.html('<small>[展开]</small>');
        } else {
            $short.hide();
            $full.show();
            $this.html('<small>[折叠]</small>');
        }
    });
});
</script>

