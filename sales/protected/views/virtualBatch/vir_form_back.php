<?php
//结算信息
$modelClass = get_class($model);
?>
<div class="box box-info <?php echo $model->need_back=="Y"?'':'hide';?>" id="needBackDiv">
    <div class="box-body">
        <div class="information-header">
            <h4>
                <strong><?php echo Yii::t("clue","设备数量");?></strong>
            </h4>
        </div>
        <div class="form-group" id="fileJsonDiv" >
            <div class="col-lg-6 col-lg-offset-3">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <?php
                        $html="<thead><tr>";
                        $html.="<th>".Yii::t("clue","设备")."</th>";
                        $html.="<th>".Yii::t("clue","设备数量")."</th>";
                        $html.="<th>".Yii::t("clue","需拆回数量")."</th>";
                        $model->getNeedBackJson();//
                        $html.="</tr></thead><tbody>";
                        if(!empty($model->need_back_json)){
                            foreach ($model->need_back_json as $key=>$row){
                                $html.="<tr>";
                                $html.="<td>".$row["field_name"]."</td>";
                                $html.="<td>".$row["field_sum"]."</td>";
                                $html.="<td>";
                                $html.=TbHtml::hiddenField("{$modelClass}[need_back_json][{$key}][field_id]",$row["field_id"]);
                                $html.=TbHtml::hiddenField("{$modelClass}[need_back_json][{$key}][field_name]",$row["field_name"]);
                                $html.=TbHtml::hiddenField("{$modelClass}[need_back_json][{$key}][field_sum]",$row["field_sum"]);
                                $html.=TbHtml::numberField("{$modelClass}[need_back_json][{$key}][field_back]",$row["field_back"],array(
                                    "min"=>0,
                                    "max"=>$row["field_sum"],
                                    "readonly"=>$model->isReadonly()
                                ));
                                $html.="</td>";
                                $html.="</tr>";
                            }
                        }
                        $html.="</tbody>";
                        echo $html;
                        ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

$js = <<<EOF
$('#VirtualBatchForm_need_back input').change(function(){
    if($(this).val()=='Y'){
        $('#needBackDiv').removeClass('hide');
    }else{
        $('#needBackDiv').addClass('hide');
    }
});
EOF;
Yii::app()->clientScript->registerScript('needBack',$js,CClientScript::POS_READY);
