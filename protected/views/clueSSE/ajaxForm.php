
<!-- 搜索框 -->
<div class="form-group" style="margin-bottom: 15px;">
    <div class="col-lg-8">
        <div class="input-group">
            <?php echo TbHtml::textField("store_search", isset($search) ? $search : '', array(
                "class"=>"form-control",
                "placeholder"=>"搜索门店名称、地址、联系人、电话",
                "id"=>"store_search_input"
            )); ?>
            <span class="input-group-btn">
                <?php echo TbHtml::button('<i class="fa fa-search"></i> 搜索', array(
                    'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                    'id'=>'btn_store_search'
                )); ?>
            </span>
        </div>
    </div>
    <div class="col-lg-4">
        <?php echo TbHtml::button('<i class="fa fa-refresh"></i> 重置', array(
            'color'=>TbHtml::BUTTON_COLOR_DEFAULT,
            'id'=>'btn_store_reset'
        )); ?>
    </div>
</div>

<div class="table-responsive" style="width: 100%;">
    <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
            <th style="width: 40px;">
                <?php
                echo TbHtml::checkBox("checkAll",false,array("class"=>"win_check_all"));
                echo TbHtml::hiddenField("ClueSSEForm[scenario]",$model->scenario);
                echo TbHtml::hiddenField("ClueSSEForm[clue_service_id]",$model->clue_service_id);
                echo TbHtml::hiddenField("ClueSSEForm[clue_id]",$model->clue_id);
                ?>
            </th>
            <th><?php echo Yii::t("clue","store name"); ?></th>
            <th><?php echo Yii::t("clue","store address"); ?></th>
            <th><?php echo Yii::t("clue","customer person"); ?></th>
            <th><?php echo Yii::t("clue","person tel"); ?></th>
            <th><?php echo Yii::t("clue","invoice header"); ?></th>
            <th><?php echo Yii::t("clue","tax id"); ?></th>
            <th><?php echo Yii::t("clue","invoice address"); ?></th>
        </tr>
        </thead>
        <tbody id="store_list_tbody">
        <?php
        $currentPage = isset($page) ? $page : 1;
        $pageSize = 10;
        $searchTerm = isset($search) ? $search : '';
        
        $list = CGetName::getClueStoreNotSSERows($model->clue_id,$model->clue_service_id,$searchTerm,$currentPage,$pageSize);
        $totalCount = CGetName::getClueStoreNotSSECount($model->clue_id,$model->clue_service_id,$searchTerm);
        $totalPages = ceil($totalCount / $pageSize);
        
        $html ="";
        if($list){
            foreach ($list as $row){
                $html.="<tr>";
                $html.="<td>";
                $html.=TbHtml::checkBox("ClueSSEForm[check][]",false,array("class"=>"win_check_one","value"=>$row["id"]));
                $html.="</td>";
                $html.="<td>".htmlspecialchars($row["store_name"])."</td>";
                $html.="<td>".htmlspecialchars($row["address"])."</td>";
                $html.="<td>".htmlspecialchars($row["cust_person"])."</td>";
                $html.="<td>".htmlspecialchars($row["cust_tel"])."</td>";
                $html.="<td>".htmlspecialchars($row["invoice_header"])."</td>";
                $html.="<td>".htmlspecialchars($row["tax_id"])."</td>";
                $html.="<td>".htmlspecialchars($row["invoice_address"])."</td>";
                $html.="</tr>";
            }
        }else{
            if(empty($searchTerm)){
                $html.="<tr><td colspan='8' style='text-align:center;'>";
                $html.="<p>暂无可添加的门店</p>";
                $html.=TbHtml::button(Yii::t('clue','add store'), array(
                    'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                    'data-load'=>Yii::app()->createUrl('clueStore/ajaxShow'),
                    'data-submit'=>Yii::app()->createUrl('clueStore/ajaxSave'),
                    'data-serialize'=>"ClueStoreForm[scenario]=new&ClueStoreForm[city]={$model->city}&ClueStoreForm[clue_id]=".$model->clue_id,
                    'data-obj'=>"#clue_dv_store",
                    'class'=>'openDialogForm',
                ));
                $html.="</td></tr>";
            }else{
                $html.="<tr><td colspan='8' style='text-align:center;'>没有找到符合条件的门店</td></tr>";
            }
        }
        echo $html;
        ?>
        </tbody>
    </table>
</div>

<!-- 分页 -->
<?php if($totalPages > 1): ?>
<div class="text-center">
    <ul class="pagination" id="store_pagination" style="margin: 10px 0;">
        <?php if($currentPage > 1): ?>
            <li><a href="javascript:void(0);" data-page="<?php echo $currentPage - 1; ?>">&laquo; 上一页</a></li>
        <?php endif; ?>
        
        <?php 
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);
        
        for($i = $startPage; $i <= $endPage; $i++): 
        ?>
            <li class="<?php echo $i == $currentPage ? 'active' : ''; ?>">
                <a href="javascript:void(0);" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        
        <?php if($currentPage < $totalPages): ?>
            <li><a href="javascript:void(0);" data-page="<?php echo $currentPage + 1; ?>">下一页 &raquo;</a></li>
        <?php endif; ?>
    </ul>
    <div style="margin: 10px 0;">
        共 <?php echo $totalCount; ?> 条记录，第 <?php echo $currentPage; ?> / <?php echo $totalPages; ?> 页
    </div>
</div>
<?php endif; ?>
<script>
    <?php
    $ajaxShowUrl = Yii::app()->createUrl('clueSSE/ajaxShow');
    $clueServiceId = $model->clue_service_id;
    $clueId = intval($model->clue_id);
    $scenario = $model->scenario;
    
    $js = <<<EOF
var currentPage = {$currentPage};
var currentSearch = '{$searchTerm}';

$('.win_check_all').on('click',function(){
	var val = $(this).prop('checked');
	$('.win_check_one').prop('checked',val);
});

$('.clue_store_delete').on('click',function(){
    $('#win_clue_sse_id').val($(this).data('id')).trigger('change');
    $('#confirmDialog2').modal('show');
});

// 搜索功能 - 使用事件委托
$(document).off('click', '#btn_store_search').on('click', '#btn_store_search', function(){
    var search = $('#store_search_input').val();
    loadStorePage(1, search);
});

// 回车搜索 - 使用事件委托
$(document).off('keypress', '#store_search_input').on('keypress', '#store_search_input', function(e){
    if(e.which == 13){
        var search = $(this).val();
        loadStorePage(1, search);
        return false;
    }
});

// 重置搜索 - 使用事件委托
$(document).off('click', '#btn_store_reset').on('click', '#btn_store_reset', function(){
    $('#store_search_input').val('');
    loadStorePage(1, '');
});

// 分页点击 - 使用事件委托
$(document).off('click', '#store_pagination a').on('click', '#store_pagination a', function(){
    var page = $(this).data('page');
    if(page){
        loadStorePage(page, currentSearch);
    }
});

// 加载指定页的门店列表
function loadStorePage(page, search){
    // 这里的弹窗容器来自 `//clue/openForm.php`
    // 内容放在 #open-form-div 内，而不是 #extendModal
    var dialog = $('#open-form-Dialog');
    var dialogBody = $('#open-form-div');

    dialogBody.html('<div style="text-align:center; padding: 40px;"><i class="fa fa-spinner fa-spin fa-3x"></i><p>加载中...</p></div>');
    
    $.ajax({
        url: '{$ajaxShowUrl}',
        type: 'POST',
        data: {
            'ClueSSEForm[scenario]': '{$scenario}',
            'ClueSSEForm[clue_service_id]': {$clueServiceId},
            'ClueSSEForm[clue_id]': {$clueId},
            'page': page,
            'search': search
        },
        dataType: 'json',
        success: function(response){
            if(response && response.html){
                if(response.title){
                    dialog.find('.modal-title').html(response.title);
                }
                dialogBody.html(response.html);
                currentPage = page;
                currentSearch = search;
            } else {
                dialogBody.html('<div class="alert alert-danger">加载失败，请重试</div>');
            }
        },
        error: function(){
            dialogBody.html('<div class="alert alert-danger">网络错误，请重试</div>');
        }
    });
}
EOF;
    echo $js;
    ?>
</script>
