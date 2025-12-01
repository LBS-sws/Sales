<?php
//结算信息
$modelClass = get_class($model);
?>

<div class="information-header">
    <h4>
        <strong><?php echo Yii::t("clue","Attachment Information");?></strong>
    </h4>
</div>
<div class="form-group" id="fileJsonDiv" >
    <div class="col-lg-8 col-lg-offset-2">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <?php
                $html="<thead><tr>";
                $html.="<th width='50%'>".Yii::t("clue","文件名称")."</th>";
                $html.="<th width='50%'>".Yii::t("clue","附件")."</th>";
                $model->getFileJson();//获取附件列表
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

