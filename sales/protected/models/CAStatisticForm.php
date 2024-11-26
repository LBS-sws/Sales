<?php

class CAStatisticForm extends KAStatisticForm
{
    protected $function_id='CN17';
    protected $table_pre='_ca_';

    public function retrieveData() {
        $this->data=array();
        $listVQS = $this->getAmtNumForVQS();//获取拜访、报价、本月的金额及数量
        $list90 = $this->getAmtNumFor90();//获取未来90天的金额及数量
        $listYM = $this->getAmtNumForYM();//获取YTD、MTD的金额及数量
        $kaManList = $this->getKaManForKaBot();//KA所有员工
        $kaGroupList = $this->getKASalesGroup();//KA分组
        $kaIDXList = $this->getKAIndicatorList($this->start_date);//KA个人指标金额
        $renewalList = $this->getAmtNumForRenewal();//获取续约金额及数量
        $data=array("group"=>array(),"staff"=>array());//排序，分组的员工置顶
        foreach ($kaManList as $row){
            $temp = $this->getTemp();
            $ka_id = $row["id"];
            $city = $row["city"];
            if(key_exists($ka_id,$kaGroupList)){
                $keyStr = "group";
                $group_id = $kaGroupList[$ka_id]["group_id"];
                $temp["group_name"] = $kaGroupList[$ka_id]["group_name"];
            }else{
                $keyStr = "staff";
                $group_id = $city."_".$ka_id;
            }
            $temp["employee_id"] = $ka_id;
            $temp["entry_date"] = General::toDate($row["entry_time"]);
            $temp["kam_name"] = $row["name"]." ({$row["code"]})";
            $this->addTempForList($temp,$listVQS,$ka_id);
            $this->addTempForList($temp,$list90,$ka_id);
            $this->addTempForList($temp,$listYM,$ka_id);
            $this->addTempForList($temp,$kaIDXList,$ka_id);
            $this->addTempForList($temp,$renewalList,$ka_id);

            $data[$keyStr][$group_id][$ka_id] = $temp;
        }
        $this->data = $data["group"];
        if(!empty($data["staff"])){
            foreach ($data["staff"] as $key=>$row){
                $this->data[$key]=$row;
            }
        }

        $session = Yii::app()->session;
        $session['cAStatistic_c01'] = $this->getCriteria();
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
        $excel->SetHeaderTitle(Yii::t("app","CA Statistic"));
        $excel->SetHeaderString($this->start_date." ~ ".$this->end_date);
        $excel->init();
        $excel->setKAHeader($headList);
        $excel->setKAData($excelData);
        $excel->outExcel(Yii::t("app","CA Statistic"));
    }

}