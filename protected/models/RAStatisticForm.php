<?php

class RAStatisticForm extends KAStatisticForm
{
    protected $function_id='CN16';
    protected $table_pre='_ra_';

    public function retrieveData() {
        $this->data=array();
        $listVQS = $this->getAmtNumForVQS();//获取拜访、报价、本月的金额及数量
        $list90 = $this->getAmtNumFor90();//获取未来90天的金额及数量
        $listYM = $this->getAmtNumForYM();//获取YTD、MTD的金额及数量
        $kaManList = $this->getKaManForKaBot();//KA所有员工
        foreach ($kaManList as $row){
            $temp = $this->getTemp();
            $ka_id = $row["id"];
            $city = $row["city"];
            $temp["employee_id"] = $ka_id;
            $temp["kam_name"] = $row["name"]." ({$row["code"]})";
            $this->addTempForList($temp,$listVQS,$ka_id);
            $this->addTempForList($temp,$list90,$ka_id);
            $this->addTempForList($temp,$listYM,$ka_id);

            $this->data[$city][$ka_id] = $temp;
        }

        $session = Yii::app()->session;
        $session['rAStatistic_c01'] = $this->getCriteria();
        return true;
    }

    //下載
    public function downExcel($excelData){
        if(!is_array($excelData)){
            $excelData = json_decode($excelData,true);
            $excelData = empty($excelData)?array():$excelData;
            $excelData = key_exists("excel",$excelData)?$excelData["excel"]:array();
        }
        $this->validateDate("","");
        $headList = $this->getTopArr();
        $excel = new DownKAExcel();
        $excel->colTwo=1;
        $excel->SetHeaderTitle(Yii::t("app","RA Statistic"));
        $excel->SetHeaderString($this->start_date." ~ ".$this->end_date);
        $excel->init();
        $excel->setKAHeader($headList);
        $excel->setKAData($excelData);
        $excel->outExcel(Yii::t("app","RA Statistic"));
    }

}