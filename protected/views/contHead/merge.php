<?php
$this->pageTitle=Yii::app()->name . ' - 主合同合并删除';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'cont-merge-form',
    'action'=>Yii::app()->createUrl('contHead/mergeConfirm'),
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo '主合同合并删除'; ?></strong>
    </h1>
</section>

<section class="content">
    <div class="box">
        <div class="box-body">
            <div class="alert alert-info">
                <h4><i class="icon fa fa-info-circle"></i> 说明：</h4>
                <p>1. 此功能用于删除错误的主合同，并将其关联数据迁移到正确的主合同</p>
                <p>2. 请先选择要删除的主合同，系统会显示该主合同下的所有关联数据</p>
                <p>3. 确认后，选择要保留的目标主合同，系统会自动迁移数据并删除错误的主合同</p>
                <p class="text-danger"><strong>注意：只能删除草稿状态（未生效）的主合同！</strong></p>
            </div>

            <h3>客户信息</h3>
            <?php if (!empty($contractList)): ?>
                <?php 
                $firstContract = $contractList[0];
                ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label">客户编号：</label>
                    <div class="col-sm-4">
                        <p class="form-control-static"><?php echo $firstContract['clue_code']; ?></p>
                    </div>
                    <label class="col-sm-2 control-label">客户名称：</label>
                    <div class="col-sm-4">
                        <p class="form-control-static"><?php echo $firstContract['cust_name']; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <hr/>

            <h3>选择要删除的主合同</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr class="bg-primary">
                            <th width="80">
                                <input type="checkbox" id="select-all" />
                                全选
                            </th>
                            <th width="120">主合同编号</th>
                            <th width="100">合同状态</th>
                            <th width="150">业务大类</th>
                            <th width="150">主体公司</th>
                            <th width="100">销售员</th>
                            <th width="100">合同金额</th>
                            <th width="100">门店数量</th>
                            <th width="150">合约开始时间</th>
                            <th width="150">合约结束时间</th>
                            <th>备注</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contractList as $contract): ?>
                        <tr>
                            <td class="text-center">
                                <?php if ($contract['cont_status'] < 10): ?>
                                    <?php echo TbHtml::checkBox('ContMergeForm[source_cont_ids][]', false, array(
                                        'value'=>$contract['id'],
                                        'class'=>'select-contract',
                                        'uncheckValue'=>null
                                    )); ?>
                                <?php else: ?>
                                    <span class="text-muted">不可选</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $contract['cont_code']; ?></td>
                            <td>
                                <?php 
                                echo CGetName::getContTopStatusStrByKey($contract['cont_status']);
                                ?>
                            </td>
                            <td><?php echo $contract['yewudalei_name']; ?></td>
                            <td><?php echo $contract['lbs_main_name']; ?></td>
                            <td><?php echo $contract['employee_name']; ?></td>
                            <td class="text-right"><?php echo number_format($contract['total_amt'], 2); ?></td>
                            <td class="text-center"><?php echo $contract['store_sum']; ?></td>
                            <td><?php echo $contract['cont_start_dt']; ?></td>
                            <td><?php echo $contract['cont_end_dt']; ?></td>
                            <td>
                                <?php if ($contract['cont_status'] >= 10): ?>
                                    <span class="text-danger">已生效，不可删除</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php echo CHtml::hiddenField('ContMergeForm[clue_id]', $model->clue_id); ?>
            <?php echo CHtml::hiddenField('ContMergeForm[step]', 'confirm'); ?>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <?php echo TbHtml::submitButton('下一步：查看关联数据', array(
                        'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                        'size'=>TbHtml::BUTTON_SIZE_LARGE
                    )); ?>
                    <?php echo TbHtml::button('返回', array(
                        'color'=>TbHtml::BUTTON_COLOR_DEFAULT,
                        'size'=>TbHtml::BUTTON_SIZE_LARGE,
                        'onclick'=>'window.location.href="'.Yii::app()->createUrl('contHead/index').'"; return false;'
                    )); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $this->endWidget(); ?>

<script>
$(document).ready(function(){
    // 全选/取消全选
    $('#select-all').click(function(){
        $('.select-contract').prop('checked', $(this).prop('checked'));
    });
    
    // 单个选择框变化时，更新全选状态
    $('.select-contract').click(function(){
        var total = $('.select-contract').length;
        var checked = $('.select-contract:checked').length;
        $('#select-all').prop('checked', total === checked);
    });
    
    // 表单提交验证
    $('#cont-merge-form').submit(function(){
        var selected = $('input[name="ContMergeForm[source_cont_ids][]"]:checked');
        if (selected.length === 0) {
            alert('请至少选择一个要删除的主合同');
            return false;
        }
        return true;
    });
});
</script>

