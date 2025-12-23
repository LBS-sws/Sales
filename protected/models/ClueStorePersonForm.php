<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2025/9/19 0019
 * Time: 18:24
 */
class ClueStorePersonForm extends ClientPersonForm{

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        $list = array();
        $list[]=array('id,person_code,cust_email,person_pws,z_display','safe');
        $list[]=array('clue_id','required');
        $list[]=array('clue_store_id,cust_person,cust_tel,cust_person_role,sex','required','on'=>array("new","edit"));
        $list[]=array('clue_id','validateStoreID');
        $list[]=array('id','validateID');
        return $list;
    }

    public function validateID($attribute, $param) {
        $scenario = $this->getScenario();
        if($scenario=="new"){
            $storeRow = Yii::app()->db->createCommand()->select("id")
                ->from("sal_clue_person")
                ->where("cust_person=:cust_person and clue_store_id=:clue_store_id",array(
                    ":cust_person"=>$this->cust_person,
                    ":clue_store_id"=>$this->clue_store_id
                ))->queryRow();
            if($storeRow){
                $this->addError($attribute, "联系人名称已存在，无法重复添加");
            }
        }else{
            $storeRow = Yii::app()->db->createCommand()->select("a.*")
                ->from("sal_clue_person a")
                ->where("a.id=:id",array(":id"=>$this->id))->queryRow();
            if($storeRow){
            }else{
                $this->addError($attribute, "数据异常，请刷新重试");
            }
        }
    }

    public function validateStoreID($attribute, $param) {
        $clueStoreModel = new ClueStoreForm("view");
        if($clueStoreModel->retrieveData($this->clue_store_id)){
            $this->clientStoreRow = $clueStoreModel->getAttributes();
        }else{
            $this->addError($attribute, "门店不存在，请刷新重试");
        }
    }

    //发送数据至派单系统
    public function sendDataByU(){
        if(in_array($this->getScenario(),array("new","edit","delete"))){
            $uStoreModel = new CurlNotesByStore();
            if(empty($this->clientStoreRow["u_id"])){//客户未同步，则同步客户信息
                //$uStoreModel->putDataByClientID($this->clue_id);
            }else{
                if($this->getScenario()==="delete"){
                    $personRow = Yii::app()->db->createCommand()->select("u_id")->from("sal_clue_person")
                        ->where("id=:id",array(":id"=>$this->id))->queryRow();
                    if(empty($personRow) || empty($personRow["u_id"])){
                        return;
                    }
                }
                $uStoreModel->putPersonDataByPersonID($this->id,$this->clientStoreRow);
            }
            $uStoreModel->setOutContentByData();
            $uStoreModel->saveCurlToApi();
        }
    }
}
