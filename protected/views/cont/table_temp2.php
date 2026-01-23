<?php
$readonly = isset($readonly)?$readonly:$model->isReadonly();
$valueStr = isset($valueStr)?$valueStr:"fileJson";
$modelClass = get_class($model);
$num = isset($num)?$num:0;
$row = isset($row)?$row:array(
    "id"=>"",
    "contID"=>"",
    "fileID"=>"",
    "fileVal"=>"",
    "fileName"=>"",
    "uflag"=>"N",
);
?>
<tr>
    <td>
        <?php
        echo TbHtml::textField("{$modelClass}[{$valueStr}][{$num}][fileName]",$row['fileName'],array("class"=>"form-control fileName","readonly"=>$readonly));
        ?>
    </td>
    <td>
        <?php
        if(empty($row["fileID"])){
            // 文件未上传时：只读场景不应出现上传控件（file input 不支持 readonly）
            if($readonly===false){
                echo TbHtml::fileField("{$modelClass}[{$valueStr}][{$num}][fileVal]",$row['fileVal'],array("class"=>"form-control fileVal"));
            }else{
                echo '<span class="text-muted">未上传</span>';
            }
        }else{
            $filePath='';
            if(isset($row['tableName'])){
                $lookFileRow=CGetName::getFilePath($row['id'],$row['tableName']);
                if(!empty($lookFileRow)){
                    $path = $lookFileRow["phy_path_name"]."/".$lookFileRow["phy_file_name"];
                    $url = "https://files.lbsapps.cn/".$path;
                    $filePath = base64_encode($url);
                }
            }
            $html="<div class=\"input-group\">";
            $html.="<span class=\"input-group-btn\">";
            if(!empty($filePath)){
                $html.=TbHtml::button("预览",array(
                    "color"=>"primary",
                    "class"=>"lookFile",
                    "data-id"=>$row['id'],
                    "data-name"=>$row['fileName'],
                    "data-file"=>$filePath,
                    "data-table"=>isset($row['tableName'])?$row['tableName']:"",
                ));
            }
            $html.=TbHtml::button("下载",array(
                "color"=>"primary",
                "class"=>"lookDownFile",
                "data-id"=>$row['id'],
                "data-name"=>$row['fileName'],
                "data-table"=>isset($row['tableName'])?$row['tableName']:"",
            ));
            $html.="</span>";
            if($readonly===false){
                $html.=TbHtml::fileField("{$modelClass}[{$valueStr}][{$num}][fileVal]",$row['fileVal'],array("class"=>"form-control fileVal"));
            }
            $html.="</div>";
            echo $html;
        }
        ?>
    </td>
    <?php
    if($readonly===false){
        $html="<td width='1%'>";
        $html.=TbHtml::button("-",array(
            "class"=>"table_del",
        ));
        $html.=TbHtml::hiddenField("{$modelClass}[{$valueStr}][{$num}][id]",$row["id"]);
        $html.=TbHtml::hiddenField("{$modelClass}[{$valueStr}][{$num}][contID]",$row["contID"]);
        $html.=TbHtml::hiddenField("{$modelClass}[{$valueStr}][{$num}][uflag]",$row["uflag"]);
        $html.="</td>";
        echo  $html;
    }
    ?>
</tr>