
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <?php
        $html="<thead><tr>";
        $html.="<th width='50%'>".Yii::t("clue","文件名称")."</th>";
        $html.="<th width='50%'>".Yii::t("clue","附件")."</th>";
        $num =count($fileJson);
        $html.="<th width='1%'>";
        if($model->isReadonly()==false){
            $html.=TbHtml::button("+",array(
                "class"=>"table_add",
                "data-temp"=>"temp2",
                "data-num"=>$num,
                'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
            ));
        }
        $tempHtml=$this->renderPartial('//cont/table_temp2',array("model"=>$model,"form"=>$form,"valueStr"=>$valueStr,"num"=>0,"readonly"=>$model->isReadonly()),true);
        $html.=TbHtml::hiddenField("temp2",$tempHtml);
        $html.="</th>";
        $html.="</tr></thead><tbody>";
        if(!empty($fileJson)){
            foreach ($fileJson as $key=>$row){
                $html.=$this->renderPartial('//cont/table_temp2',array("model"=>$model,"form"=>$form,"valueStr"=>$valueStr,"row"=>$row,"num"=>$key,"readonly"=>$model->isReadonly()),true);
            }
        }
        $html.="</tbody>";
        echo $html;
        ?>
    </table>
</div>