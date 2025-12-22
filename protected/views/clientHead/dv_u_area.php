<?php
/**
 * 项目所属区域
 */
?>
<div  style="padding-top: 15px;">
    <div>
        <?php
        echo TbHtml::button(Yii::t('clue','add u area'), array(
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
            'data-load'=>Yii::app()->createUrl('clueUArea/ajaxShow'),
            'data-submit'=>Yii::app()->createUrl('clueUArea/ajaxSave'),
            'data-serialize'=>"ClueUAreaForm[scenario]=new&ClueUAreaForm[city]={$model->city}&ClueUAreaForm[clue_id]=".$model->id,
            'data-obj'=>"#clue_dv_u_area_dummy",
            'data-fun'=>'refreshUAreaData',
            'class'=>'openDialogForm',
        ));
        ?>
    </div>
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue',"city"); ?></th>
                <th><?php echo Yii::t('clue',"client u area"); ?></th>
                <th><?php echo Yii::t('clue',"u id"); ?></th>
                <th><?php echo Yii::t('clue',"lcu"); ?></th>
                <th><?php echo Yii::t('clue',"luu"); ?></th>
                <th><?php echo Yii::t('clue',"lcd"); ?></th>
                <th><?php echo Yii::t('clue',"lud"); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody id="dv_u_area_body">
            <tr><td colspan="8" style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> 加载中...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php
$ajaxUrl = Yii::app()->createUrl('clientHead/ajaxLoadUArea');
$editUrl = Yii::app()->createUrl('clueUArea/ajaxShow');
$saveUrl = Yii::app()->createUrl('clueUArea/ajaxSave');
$clueId = $model->id;
$js = <<<EOF
var uAreaLoaded = false;
$('a[href="#clue_dv_u_area"]').on('shown.bs.tab', function (e) {
    if(!uAreaLoaded){
        loadClientUArea();
        uAreaLoaded = true;
    }
});

function loadClientUArea(){
    var tbody = $('#dv_u_area_body');
    
    $.ajax({
        url: '{$ajaxUrl}',
        type: 'GET',
        data: {
            clue_id: {$clueId}
        },
        dataType: 'json',
        success: function(response){
            if(response.status === 1){
                var html = '';
                if(response.data && response.data.length > 0){
                    $.each(response.data, function(i, row){
                        html += '<tr>';
                        html += '<td>' + (row.city_code || '') + '</td>';
                        html += '<td>' + (row.city_type || '') + '</td>';
                        html += '<td>' + (row.u_id || '') + '</td>';
                        html += '<td>' + (row.lcu || '') + '</td>';
                        html += '<td>' + (row.luu || '') + '</td>';
                        html += '<td>' + (row.lcd || '') + '</td>';
                        html += '<td>' + (row.lud || '') + '</td>';
                        html += '<td>';
                        if(row.can_edit){
                            html += '<a href="javascript:void(0);" class="openDialogForm" data-load="{$editUrl}" data-submit="{$saveUrl}" data-serialize="ClueUAreaForm[scenario]=edit&ClueUAreaForm[id]=' + row.id + '" data-obj="#clue_dv_u_area_dummy" data-fun="refreshUAreaData"><span class="glyphicon glyphicon-pencil"></span></a>';
                        }
                        html += '</td>';
                        html += '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="8" style="text-align:center;">暂无数据</td></tr>';
                }
                tbody.html(html);
            } else {
                tbody.html('<tr><td colspan="8" style="text-align:center; color: red;">加载失败: ' + (response.error || '未知错误') + '</td></tr>');
            }
        },
        error: function(xhr, status, error){
            var errorMsg = '加载失败，请刷新页面重试';
            if(xhr.responseJSON && xhr.responseJSON.error){
                errorMsg = '加载失败: ' + xhr.responseJSON.error;
            } else if(error){
                errorMsg = '加载失败: ' + error;
            }
            tbody.html('<tr><td colspan="8" style="text-align:center; color: red;">' + errorMsg + '</td></tr>');
        }
    });
}

function refreshUAreaData(response){
    // 保存成功后重新加载数据
    loadClientUArea();
}
EOF;
Yii::app()->clientScript->registerScript('loadClientUArea',$js,CClientScript::POS_READY);
?>