<?php
$this->pageTitle=Yii::app()->name . ' - Contract List';
?>
<style>
    .table-fixed { table-layout: fixed;}
</style>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Contract List'); ?></strong>
	</h1>
</section>

<section class="content">
    <div class="box">
        <div class="box-body">
            <div class="btn-group pull-left" role="group">
                <?php
                echo TbHtml::button('<i class="fa fa-object-group"></i> 批量合并删除', array(
                    'color'=>TbHtml::BUTTON_COLOR_DANGER,
                    'id'=>'batch-merge-btn',
                    'disabled'=>true
                ));
                ?>
                <span id="selected-count" class="text-muted" style="margin-left:10px;"></span>
            </div>
            <div class="btn-group pull-right" role="group">
                <?php
                echo TbHtml::button(Yii::t('app','Contract Update List'), array(
                    'submit'=>Yii::app()->createUrl('contPro/index')
                ));
                ?>
            </div>
        </div>
    </div>
	<?php
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('clue','Contract List'),
        'model'=>$model,
        'viewhdr'=>'//contHead/_listhdr',
        'viewdtl'=>'//contHead/_listdtl',
        'advancedSearch'=>true,
        'hasDateButton'=>true,
        'tableClass'=>"table table-hover table-fixed table-condensed",
    ));
	?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
	echo $form->hiddenField($model,'filter');
	echo $form->hiddenField($model,'flow_odds');
?>
<?php $this->endWidget(); ?>

<?php

$url = Yii::app()->createUrl('contHead/index',array("pageNum"=>1));
$mergeUrl = Yii::app()->createUrl('contHead/mergeConfirm');
$js = "
    $('.btn_submit').on('click',function(){
        var key=$(this).data('key');
        $(\"#ContHeadList_orderField\").val(\"\");
        $(\"#ContHeadList_flow_odds\").val(key);
        jQuery.yii.submitForm(this,'{$url}',{});
    });
    
    // 全选/取消全选
    $('#select-all-contracts').on('change', function(){
        $('.select-contract-item').prop('checked', $(this).prop('checked'));
        updateBatchButton();
    });
    
    // 单个复选框变化
    $(document).on('change', '.select-contract-item', function(){
        updateBatchButton();
        // 更新全选状态
        var total = $('.select-contract-item').length;
        var checked = $('.select-contract-item:checked').length;
        $('#select-all-contracts').prop('checked', total > 0 && total === checked);
    });
    
    // 更新批量操作按钮状态
    function updateBatchButton() {
        var checked = $('.select-contract-item:checked');
        var count = checked.length;
        
        if (count > 0) {
            // 检查是否都属于同一个客户
            var clueIds = [];
            checked.each(function(){
                var clueId = $(this).data('clue-id');
                if (clueIds.indexOf(clueId) === -1) {
                    clueIds.push(clueId);
                }
            });
            
            if (clueIds.length === 1) {
                $('#batch-merge-btn').prop('disabled', false);
                $('#selected-count').html('<span class=\"text-success\">已选择 <strong>' + count + '</strong> 个主合同</span>');
            } else {
                $('#batch-merge-btn').prop('disabled', true);
                $('#selected-count').html('<span class=\"text-danger\">只能选择同一个客户下的主合同（当前选中了 ' + clueIds.length + ' 个不同客户）</span>');
            }
        } else {
            $('#batch-merge-btn').prop('disabled', true);
            $('#selected-count').text('');
        }
    }
    
    // 批量合并删除按钮点击
    $('#batch-merge-btn').on('click', function(){
        var checked = $('.select-contract-item:checked');
        if (checked.length === 0) {
            alert('请至少选择一个主合同');
            return false;
        }
        
        // 获取所有选中的合同ID和客户ID
        var contractIds = [];
        var clueId = null;
        checked.each(function(){
            contractIds.push($(this).val());
            if (clueId === null) {
                clueId = $(this).data('clue-id');
            }
        });
        
        // 创建表单并提交
        var form = $('<form method=\"post\" action=\"{$mergeUrl}\"></form>');
        form.append('<input type=\"hidden\" name=\"ContMergeForm[clue_id]\" value=\"' + clueId + '\" />');
        form.append('<input type=\"hidden\" name=\"ContMergeForm[step]\" value=\"confirm\" />');
        $.each(contractIds, function(i, id){
            form.append('<input type=\"hidden\" name=\"ContMergeForm[source_cont_ids][]\" value=\"' + id + '\" />');
        });
        $('body').append(form);
        form.submit();
    });
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
<?php
echo TbHtml::button("",array("submit"=>"","class"=>"hide"));
$this->renderPartial('//clue/select_clue',array("actionUrl"=>Yii::app()->createUrl('contHead/new')));
?>
