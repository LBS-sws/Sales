<?php

class LoadExcel {

    private $excelList;
    private $file_url;
	public function __construct($filePath) {
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $this->file_url = $filePath;
        $listBody=array();
        /**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($filePath)){
            $PHPReader = new PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($filePath)){
                echo 'no Excel';
                return false;
            }
        }
        $PHPExcel = $PHPReader->load($filePath);
        /**读取excel文件中的第一个工作表*/
        $currentSheet = $PHPExcel->getSheet(0);
        /**取得最大的列号*/
        $allColumn = $currentSheet->getHighestColumn();
        $allColumn = $this->getColumnToNum($allColumn);
        /**取得一共有多少行*/
        $allRow = $currentSheet->getHighestRow();
        /**从第二行开始输出，因为excel表中第一行为列名*/
        for($currentRow = 4;$currentRow <= $allRow;$currentRow++){
            /**从第A列开始输出*/
            $arr = array();
            for($currentColumn= 0;$currentColumn<= $allColumn; $currentColumn++){
                //$val = $currentSheet->getCellByColumnAndRow($currentColumn,$currentRow)->getValue();/**ord()将字符转为十进制数*/
                $val = $currentSheet->getCellByColumnAndRow($currentColumn,$currentRow)->getCalculatedValue();//獲取公式后的結果
                array_push($arr,$val);
            }
            array_push($listBody,$arr);
        }
        $this->excelList =  array(
            "maxColumn"=>$allColumn,
            "maxRow"=>$allRow,
            "listBody"=>$listBody,
        );
        $PHPExcel->disconnectWorksheets();
        unset($PHPExcel);
        unset($PHPReader);
        unset($currentSheet);
    }

    public function getExcelList(){
        unlink($this->file_url);
        return $this->excelList;
    }

    private function getColumnToNum($str){
        if(strlen($str)==1){
            return ord($str)-65;
        }elseif(strlen($str)==2){
            $num = ord($str)-65;
            $num = 26*($num+1);
            $newStr = $str[1];
            $num += ord($newStr)-65;
            return $num;
        }
        return 60;
    }
}
?>