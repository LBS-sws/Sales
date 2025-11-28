
<?php
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'virTraceDialog',
    'header'=>"",
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal'
        )),
        TbHtml::button(Yii::t('dialog','OK'), array(
            'id'=>"open-form-btn-ok",
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY
        )),
    ),
    'show'=>false,
    'size'=>" modal-lg",
));
?>
<div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>操作时间</th>
                <th>操作人</th>
                <th>操作类型</th>
                <th>操作状态</th>
                <th>操作编号</th>
                <th>生效日期</th>
                <th>操作备注</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $rows = CGetName::getContVirProRows($model->id);
            if($rows){
                $html="";
                foreach ($rows as $row){
                    $html.="<tr>";
                    $html.="<td>".$row["lcd"]."</td>";
                    $html.="<td>".$row["display_name"]."</td>";
                    $html.="<td>".CGetName::getProTypeStrByKey($row["pro_type"])."</td>";
                    $html.="<td>";
                    if($row["pro_status"]<30){
                        if(!empty($row['vir_batch_id'])){
                            $url=Yii::app()->createUrl('virtualBatch/edit',array("index"=>$row['vir_batch_id']));
                        }else{
                            $url=Yii::app()->createUrl('contPro/edit',array("index"=>$row['pro_id']));
                        }
                        $html.=TbHtml::link(CGetName::getContTopStatusStrByKey($row["pro_status"]),$url,array(
                            "target"=>"_blank"
                        ));
                    }else{
                        $html.=CGetName::getContTopStatusStrByKey($row["pro_status"]);
                    }
                    $url = Yii::app()->createUrl('virtualHead/compare',array('index'=>$row["id"]));
                    $html.="</td>";
                    $html.="<td>";
                    $html.=TbHtml::link($row["pro_code"],$url,array("target"=>"_blank"));
                    $html.="</td>";
                    $html.="<td>".$row["pro_date"]."</td>";
                    $html.="<td>".$row["pro_remark"]."</td>";
                    $html.="</tr>";
                }
                echo $html;
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->endWidget(); ?>

