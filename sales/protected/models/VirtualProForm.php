<?php

class VirtualProForm extends VirtualHeadForm
{
    public $vir_id;//
    public $pro_code;//操作
    public $pro_type;//操作类型 C：续约
    public $pro_date;//操作时间
    public $pro_remark;//操作备注
    public $pro_status=0;//操作进行中的状态
    public $pro_num;//
    public $stop_set_id;//
    public $pro_change;//
    public $pro_vir_type;//
    public $pro_id;//
    public $vir_batch_id;//

    public $compareModel;
    public $compareArr=array();

    public function setCompareModelByVirID($index){
        $this->compareModel = new VirtualForm();
        $this->compareModel->retrieveData($index);
        $this->computeCompareArr();
    }

    protected function computeCompareArr(){
        $updateList = $this->historyUpdateList();
        foreach ($updateList as $item){
            if($this->$item!=$this->compareModel->$item){
                $this->compareArr[]=array(
                    "key"=>$item,
                    "name"=>$this->getAttributeLabel($item),
                    "oldText"=>$this->getNameForValue($item,$this->$item,$this),
                    "newText"=>$this->getNameForValue($item,$this->compareModel->$item,$this->compareModel),
                );
            }
        }
    }

    public function printCompareHtml(){
        $html="";
        $html.= "<div class='compare-bottom-div visible-lg'><table class='table table-hover table-bordered table-condensed'>";
        $html.= "<thead><tr class='danger'><th>被修改字段名称</th><th>历史信息</th><th>最新信息</th></tr></thead><tbody>";
        foreach ($this->compareArr as $compareItem){
            $html.= "<tr class='warning'>";
            $html.= "<th>".$compareItem["name"]."</th>";
            $html.= "<th>".$compareItem["oldText"]."</th>";
            $html.= "<th>".$compareItem["newText"]."</th>";
            $html.= "</tr>";
        }
        $html.= "</tbody></table></div>";
        return $html;
    }

	public function retrieveData($index)
    {
        $index = empty($index)||!is_numeric($index)?0:intval($index);
        $sql = "select a.* from sal_contpro_virtual a where a.id=".$index." ";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $this->setAttrByRow($row);
            $this->vir_id=$row["vir_id"];
            $this->pro_code=$row["pro_code"];
            $this->pro_type=$row["pro_type"];
            $this->pro_date=$row["pro_date"];
            $this->pro_remark=$row["pro_remark"];
            $this->pro_status=$row["pro_status"];
            $this->pro_num=$row["pro_num"];
            $this->pro_change=$row["pro_change"];
            $this->pro_vir_type=$row["pro_vir_type"];
            $this->pro_id=$row["pro_id"];
            $this->vir_batch_id=$row["vir_batch_id"];
            return true;
        }else{
            return false;
        }
    }

	public function retrieveDataByBatchIDAndVirID($batch_id,$vir_id)
    {
        if(empty($batch_id)){
            return $this->retrieveDataByVirID($vir_id);
        }else{
            $row = Yii::app()->db->createCommand()->select("*")->from("sal_contpro_virtual")
                ->where("vir_id=:vir_id and vir_batch_id=:vir_batch_id",array(
                    ":vir_id"=>$vir_id,
                    ":vir_batch_id"=>$batch_id,
                ))->queryRow();
            if($row){
                $this->setAttrByRow($row);
                $this->vir_id=$row["vir_id"];
                $this->pro_code=$row["pro_code"];
                $this->pro_type=$row["pro_type"];
                $this->pro_date=$row["pro_date"];
                $this->pro_remark=$row["pro_remark"];
                $this->pro_status=$row["pro_status"];
                $this->pro_num=$row["pro_num"];
                $this->pro_change=$row["pro_change"];
                $this->pro_vir_type=$row["pro_vir_type"];
                $this->pro_id=$row["pro_id"];
                $this->vir_batch_id=$row["vir_batch_id"];
                return true;
            }else{
                return false;
            }
        }
    }

	public function retrieveDataByVirID($vir_id)
    {
        return parent::retrieveData($vir_id);
    }

    public function isOccupied($index) {
        $rtn = true;//默认不允许删除
        if($this->retrieveData($index)){
            $rtn = false;//允许删除
        }
        return $rtn;
    }

    public function isReadonly() {
		return $this->getScenario()=="view";
	}

	public function isReadonlyAndStatus() {
		return $this->getScenario()=="view"||!in_array($this->vir_status,array(0,9));
	}
}
