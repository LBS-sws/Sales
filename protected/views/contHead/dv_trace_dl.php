<div class="media">
    <div class="media-left hidden-md hidden-xs hidden-sm">
        <p class="trace_div_left"><?php echo $row["lcd"]?></p>
    </div>
    <div class="media-body <?php echo $row["pro_status"]>=30?'active':'';?>">
        <p><?php echo $row["display_name"]?><span class="hidden-lg" style="padding-left: 10px;"><?php echo $row["lcd"]?></span></p>
        <p>
            <?php
            $label="操作类型：".CGetName::getProTypeStrByKey($row["pro_type"]);
            $label.="；操作状态：";
            if($row["pro_status"]<30){
                $url=Yii::app()->createUrl('contPro/edit',array("index"=>$row['id']));
                $label.=TbHtml::link(CGetName::getContTopStatusStrByKey($row["pro_status"]),$url,array(
                    "target"=>"_blank"
                ));
            }else{
                $label.=CGetName::getContTopStatusStrByKey($row["pro_status"]);
            }
            $url = Yii::app()->createUrl('contPro/compare',array('index'=>$row["id"]));
            $label.="；操作编号：".TbHtml::link($row["pro_code"],$url,array("target"=>"_blank"));
            $label.="；生效日期：".$row["pro_date"];
            $label.="；操作备注：".$row["pro_remark"];
            echo $label;?>
        </p>
    </div>
</div>