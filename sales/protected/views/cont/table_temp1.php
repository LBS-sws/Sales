<?php
$modelClass = get_class($model);
$num = isset($num)?$num:0;
$row = isset($row)?$row:array(
    "id"=>"",
    "contID"=>"",
    "busineID"=>"",
    "areaMin"=>"",
    "areaMax"=>"",
    "serviceFreType"=>"",
    "serviceFreAmt"=>"",
    "serviceFreMonth"=>"",
    "serviceFreSum"=>"",
    "serviceFreJson"=>"",
    "serviceFreText"=>"",
    "serviceSum"=>"",
    "monthAmt"=>"",
    "uflag"=>"N",
);
?>
<tr>
    <td>
        <?php
        echo TbHtml::dropDownList("{$modelClass}[areaJson][{$num}][busineID]",$row['busineID'],$model->busineList,array('readonly'=>$model->isReadonly(),"class"=>"form-control serviceFreArea"));
        ?>
    </td>
    <td>
        <div class="input-group">
            <?php
            echo TbHtml::numberField("{$modelClass}[areaJson][{$num}][areaMin]",$row['areaMin'],array("class"=>"form-control serviceFreArea serviceFreArea_min",'readonly'=>$model->isReadonly(),"min"=>0));
            ?>
            <div class="input-group-addon">至</div>
            <?php
            echo TbHtml::numberField("{$modelClass}[areaJson][{$num}][areaMax]",$row['areaMax'],array("class"=>"form-control serviceFreArea serviceFreArea_max",'readonly'=>$model->isReadonly(),"min"=>0));
            ?>
        </div>
    </td>
    <td>
        <?php
        echo TbHtml::hiddenField("{$modelClass}[areaJson][{$num}][serviceFreType]",$row["serviceFreType"],array("class"=>"serviceFreType"));
        echo TbHtml::hiddenField("{$modelClass}[areaJson][{$num}][serviceFreAmt]",$row["serviceFreAmt"],array("class"=>"serviceFreAmt"));
        if(isset($row["serviceFreMonth"])){//后期增加，所以需要判断
            echo TbHtml::hiddenField("{$modelClass}[areaJson][{$num}][serviceFreMonth]",$row["serviceFreMonth"],array("class"=>"serviceFreMonth"));
        }
        echo TbHtml::hiddenField("{$modelClass}[areaJson][{$num}][serviceFreSum]",$row["serviceFreSum"],array("class"=>"serviceFreSum"));
        echo TbHtml::hiddenField("{$modelClass}[areaJson][{$num}][serviceFreJson]",$row["serviceFreJson"],array("class"=>"serviceFreJson"));
        echo TbHtml::textField("{$modelClass}[areaJson][{$num}][serviceFreText]",$row['serviceFreText'],array("class"=>"form-control serviceFreText serviceFreArea","placeholder"=>"请选择服务频次","readonly"=>$model->isReadonly()));
        ?>
    </td>
    <td class="text-center serviceFreArea_sum">
        <?php
        echo $row['serviceFreSum'];
        ?>
    </td>
    <td>
        <?php
        echo TbHtml::numberField("{$modelClass}[areaJson][{$num}][monthAmt]",$row['monthAmt'],array("class"=>"form-control serviceFreArea_month",'readonly'=>$model->isReadonly(),"min"=>0));
        ?>
    </td>
    <?php
    if($model->isReadonly()===false){
        $html="<td width='1%'>";
        $html.=TbHtml::button("-",array(
            "class"=>"table_del",
        ));
        $html.=TbHtml::hiddenField("{$modelClass}[areaJson][{$num}][id]",$row["id"]);
        $html.=TbHtml::hiddenField("{$modelClass}[areaJson][{$num}][contID]",$row["contID"]);
        $html.=TbHtml::hiddenField("{$modelClass}[areaJson][{$num}][uflag]",$row["uflag"]);
        $html.="</td>";
        echo  $html;
    }
    ?>
</tr>