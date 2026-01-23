<?php
$modelClass=get_class($model);
switch ($modelClass){
    case "VirtualBatchForm":
        $urlStr="virtualBatch";
        break;
    case "ContHeadForm":
        $urlStr="contHead";
        break;
    default:
        $urlStr="contPro";
}
//$urlStr = get_class($model)=="ContProForm"?"contPro":"contHead";
$form=$this->beginWidget('TbActiveForm', array(
    'id'=>'seal-form',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
    'htmlOptions'=>array('enctype'=>'multipart/form-data'),
));
?>
<?php echo $form->hiddenField($model, 'id'); ?>

<?php
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'open-seal-Dialog',
    'header'=>"上传印章文件",
    'footer'=>array(
        TbHtml::button("保存", array('submit'=>Yii::app()->createUrl($urlStr.'/saveSeal'))),
        TbHtml::button("完成",array('data-toggle'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY,'data-target'=>'#confirmDialog10')),
    ),
    'show'=>false,
));
?>
<div class="form-group">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <?php
                $html="<thead><tr>";
                $html.="<th width='50%'>".Yii::t("clue","文件名称")."</th>";
                $html.="<th width='50%'>".Yii::t("clue","附件")."</th>";
                $fileJson = $model->getSealFileJson();//获取附件列表
                $num =count($fileJson);
                $html.="<th width='1%'>";
                $html.=TbHtml::button("+",array(
                    "class"=>"table_add",
                    "data-temp"=>"temp2",
                    "data-num"=>$num,
                ));
                $tempHtml=$this->renderPartial('//cont/table_temp2',array("model"=>$model,"form"=>$form,"num"=>0,"readonly"=>false),true);
                $html.=TbHtml::hiddenField("temp2",$tempHtml);
                $html.="</th>";
                $html.="</tr></thead><tbody>";
                if(!empty($fileJson)){
                    foreach ($fileJson as $key=>$row){
                        $html.=$this->renderPartial('//cont/table_temp2',array("model"=>$model,"form"=>$form,"row"=>$row,"num"=>$key,"readonly"=>false),true);
                    }
                }
                $html.="</tbody>";
                echo $html;
                ?>
            </table>
        </div>
    </div>
</div>
<?php $this->endWidget(); ?>


<?php
$this->renderPartial('//site/confirmDialog',array(
    "idNum"=>10,
    "header"=>"完成上传印章",
    "content"=>"<p>请确认上传的是双方盖章合同，确认后将不可修改</p>",
    "submit"=>Yii::app()->createUrl($urlStr.'/saveSeal',array("type"=>"audit")),
));
?>
<?php $this->endWidget(); ?>
<?php
$js = <<<EOF
$('table').on('change','[id^="{$modelClass}"]',function() {
	var n=$(this).attr('id').split('_');
	$('#{$modelClass}_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});
EOF;
Yii::app()->clientScript->registerScript('changeTable',$js,CClientScript::POS_READY);
$js = <<<EOF
$('table').on('click','.table_del', function() {
	$(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
	$(this).closest('tr').hide();
});
EOF;
Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);
$js = <<<EOF
$('table').on('change','.fileVal',function() {
    var fileInput = $(this);
    var filename = fileInput.val();
    var pos = filename.lastIndexOf("\\\\")+1;
    filename = filename.substring(pos, filename.length);
    //验证文件
    if(this.files[0].size>{$model->docMaxSize}){
        showFormErrorHtml("文件大小不能超过15M");
        $(this).val('');
        return false;
    }
    
    var pos = filename.lastIndexOf(".");
    var str = filename.substring(pos, filename.length);
    var str1 = str.toLowerCase();
    var fileType = "jpg|jpeg|png|xlsx|pdf|docx|txt|doc|wps";
    var re = new RegExp("\.(" + fileType + ")$");
    if (!re.test(str1)) {
        showFormErrorHtml("文件格式不正确，只能上传格式为：" + fileType + "的文件。");
        $(this).val('');
        return false;
    }else{
        $(this).parents('tr:first').find('.fileName').val(filename);
    }
});
$('table').on('click','.table_add',function() {
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
Yii::app()->clientScript->registerScript('addRow',$js,CClientScript::POS_READY);
?>
