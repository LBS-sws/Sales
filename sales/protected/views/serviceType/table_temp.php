<?php
$modelClass = get_class($model);
$num = isset($num)?$num:0;
$row = isset($row)?$row:array(
    "id"=>"",
    "name"=>"",
    "uCode"=>"",
    "uType"=>"",
    "inputType"=>"",
    "func"=>"",
    "eolBool"=>0,
    "zIndex"=>1,
    "zDisplay"=>1,
    "totalBool"=>0,
    "defaultValue"=>null,
    "uflag"=>"N",
);
?>
<tr>
    <td>
        <?php
        echo TbHtml::textField("{$modelClass}[infoJson][{$num}][name]",$row['name'],array("class"=>"form-control","readonly"=>$model->isReadonly()));
        ?>
    </td>
    <td>
        <?php
        echo TbHtml::dropDownList("{$modelClass}[infoJson][{$num}][inputType]",$row['inputType'],CGetName::getInputTypeList(),array("class"=>"form-control inputType","readonly"=>$model->isReadonly()));
        ?>
    </td>
    <td>
        <?php
        echo TbHtml::dropDownList("{$modelClass}[infoJson][{$num}][func]",$row['func'],CGetName::getSelectList(),array("class"=>"form-control func","empty"=>"","readonly"=>$model->isReadonly()||$row['inputType']!="select"));
        ?>
    </td>
    <td>
        <?php
        echo TbHtml::textField("{$modelClass}[infoJson][{$num}][defaultValue]",$row['defaultValue'],array("class"=>"form-control","readonly"=>$model->isReadonly()));
        ?>
    </td>
    <td>
        <?php
        echo TbHtml::textField("{$modelClass}[infoJson][{$num}][uCode]",$row['uCode'],array("class"=>"form-control","readonly"=>$model->isReadonly()));
        ?>
    </td>
    <td>
        <?php
        echo TbHtml::textField("{$modelClass}[infoJson][{$num}][uType]",$row['uType'],array("class"=>"form-control","readonly"=>$model->isReadonly()));
        ?>
    </td>
    <td>
        <?php
        echo TbHtml::dropDownList("{$modelClass}[infoJson][{$num}][eolBool]",$row['eolBool'],CGetName::getDisplayList(),array("class"=>"form-control","readonly"=>$model->isReadonly()));
        ?>
    </td>
    <td>
        <?php
        echo TbHtml::numberField("{$modelClass}[infoJson][{$num}][zIndex]",$row['zIndex'],array("class"=>"form-control","readonly"=>$model->isReadonly()));
        ?>
    </td>
    <td>
        <?php
        echo TbHtml::dropDownList("{$modelClass}[infoJson][{$num}][zDisplay]",$row['zDisplay'],CGetName::getDisplayList(),array("class"=>"form-control","readonly"=>$model->isReadonly()));
        ?>
    </td>
    <td>
        <?php
        echo TbHtml::dropDownList("{$modelClass}[infoJson][{$num}][totalBool]",$row['totalBool'],CGetName::getDisplayList(),array("class"=>"form-control","readonly"=>$model->isReadonly()));
        ?>
    </td>
    <?php
    if($model->isReadonly()===false){
        $html="<td width='1%'>";
        $html.=TbHtml::button("-",array(
            "class"=>"table_del",
        ));
        $html.=TbHtml::hiddenField("{$modelClass}[infoJson][{$num}][id]",$row["id"]);
        $html.=TbHtml::hiddenField("{$modelClass}[infoJson][{$num}][uflag]",$row["uflag"]);
        $html.="</td>";
        echo  $html;
    }
    ?>
</tr>