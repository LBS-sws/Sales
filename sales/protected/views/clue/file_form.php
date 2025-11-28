
<div class="form-group" id="clue-file-div">
    <?php echo TbHtml::label(Yii::t("clue","Attachment"),'cust_tel',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-8">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <?php
                $html="<thead><tr>";
                $html.="<th width='50%'>".Yii::t("clue","文件名称")."</th>";
                $html.="<th width='50%'>".Yii::t("clue","附件")."</th>";
                if($model->isReadonly()===false){
                    $num =count($model->fileJson);
                    $html.="<th width='1%'>";
                    $html.=TbHtml::button("+",array(
                        "class"=>"table_add",
                        "data-temp"=>"temp2",
                        "data-num"=>$num,
                        'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                    ));
                    $tempHtml=$this->renderPartial('//cont/table_temp2',array("model"=>$model,"form"=>$form,"num"=>0),true);
                    $html.=TbHtml::hiddenField("temp2",$tempHtml);
                    $html.="</th>";
                }
                $html.="</tr></thead><tbody>";
                $model->getFileJson();
                if(!empty($model->fileJson)){
                    foreach ($model->fileJson as $key=>$row){
                        $readonly = isset($row['readyOnly'])?$row['readyOnly']:$model->isReadonly();
                        $html.=$this->renderPartial('//cont/table_temp2',array("model"=>$model,"form"=>$form,"row"=>$row,"num"=>$key,"readonly"=>$readonly),true);
                    }
                }
                $html.="</tbody>";
                echo $html;
                ?>
            </table>
        </div>
    </div>
</div>
<?php
$modelClass=get_class($model);
$url = Yii::app()->createUrl('lookFile/show');
$downUrl = Yii::app()->createUrl('lookFile/down');
$lookUrl = Yii::app()->params['fileLookUrl'];
$js = <<<EOF
    $('body').on('click','.lookFile',function(){
        window.open('{$lookUrl}/onlinePreview?url='+$(this).data('file'));
    });
    $('body').on('click','.lookDownFile',function(){
        var url = "{$downUrl}?index="+$(this).data('id')+"&tableName="+$(this).data('table');
        window.open(url);
    });
$('#clue-file-div').on('change','[id^="{$modelClass}"]',function() {
	var n=$(this).attr('id').split('_');
	$('#{$modelClass}_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});
$('#clue-file-div').on('click','.table_del', function() {
	$(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
	$(this).closest('tr').hide();
});
$('#clue-file-div').on('change','.fileVal',function() {
    var fileInput = $(this);
    var filename = fileInput.val();
    var pos = filename.lastIndexOf("\\\\")+1;
    filename = filename.substring(pos, filename.length);
    //验证文件
    if(this.files[0].size>{$model->docMaxSize}){
        showFormErrorHtml("文件大小不能超过10M");
        $(this).val('');
        return false;
    }
    
    var pos = filename.lastIndexOf(".");
    var str = filename.substring(pos, filename.length);
    var str1 = str.toLowerCase();
    var fileType = "jpg|jpeg|png|xlsx|pdf|docx|txt";
    var re = new RegExp("\.(" + fileType + ")$");
    if (!re.test(str1)) {
        showFormErrorHtml("文件格式不正确，只能上传格式为：" + fileType + "的文件。");
        $(this).val('');
        return false;
    }else{
        $(this).parents('tr:first').find('.fileName').val(filename);
    }
});
$('#clue-file-div').on('click','.table_add',function() {
	var r = $(this).data('num');
	if (r>=0) {
	    r++;
	    $(this).data('num',r);
		var nid = '';
		var ct = $(this).next('input').val();
		$(this).parents('thead').eq(0).next('tbody').append(ct);
		$(this).parents('table').eq(0).find('tbody>tr').eq(-1).find('[id*=\"{$modelClass}_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);
		});
	}
});
EOF;
Yii::app()->clientScript->registerScript('clueFileDialog',$js,CClientScript::POS_READY);
?>
