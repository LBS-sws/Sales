<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2025/9/9 0009
 * Time: 19:06
 */
class LookFileForm extends CFormModel
{
    public $lookFileRow;

    public function lookFile(){
        $list = array("file"=>"error","html"=>"没有相关文件","style"=>"","title"=>$this->lookFileRow["phy_file_name"]);
        if($this->hasErrors()===false){
            $path = $this->lookFileRow["phy_path_name"]."/".$this->lookFileRow["phy_file_name"];
            switch ($this->lookFileRow["file_type"]){
                case "application/vnd.openxmlformats-officedocument.wordprocessingml.document"://docx
                    $list["html"]="docx";
                    break;
                case "application/vnd.ms-excel"://xls
                case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"://xlsx
                    $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
                    include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
                    $objReader  = PHPExcel_IOFactory::createReader('Excel2007');
                    $objPHPExcel = $objReader->load($path);
                    $objWriter = new PHPExcel_Writer_HTML($objPHPExcel);
                    $list["style"] = $objWriter->generateStyles();
                    $list["html"] = $objWriter->save('php://output');
                    $list["file"] = "excel";
                    break;
                case "application/pdf"://pdf
                    $file = $path;
                    $fp = fopen($file, 'rb');
                    header('Content-Type: application/pdf');
                    header('Content-Length: ' . filesize($file));
                    fpassthru($fp);
                    fclose($fp);
                    exit;
                    break;
                case "image/png"://png
                    $imageData = base64_encode(file_get_contents($path));
                    $dataUrl = 'data:image/png;base64,' . $imageData;
                    $list["html"]="<img src='{$dataUrl}' width='100%'>";
                    $list["file"] = "image";
                    break;
                case "image/jpg"://jpeg
                case "image/jpeg"://jpeg
                    $imageData = base64_encode(file_get_contents($path));
                    $dataUrl = 'data:image/jpeg;base64,' . $imageData;
                    $list["html"]="<img src='{$dataUrl}' width='100%'>";
                    $list["file"] = "image";
                    break;
                default:
                    $list["html"]="没有相关文件";
            }
        }else{
            $message = CHtml::errorSummary($this);
            $list["html"]=$message;
        }
        return $list;
    }
}