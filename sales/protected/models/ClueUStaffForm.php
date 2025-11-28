<?php

class ClueUStaffForm extends CFormModel
{
    /* User Fields */
    public $id;
    public $clue_id;
    public $employee_id;
    public $employee_type;

    public $clueHeadRow;

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        $list = array(
            'employee_id'=>Yii::t('clue','staff'),//城市
            'employee_type'=>Yii::t('clue','client u staff'),//
        );
        return $list;
    }

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        $list = array();
        $list[]=array('id','safe');
        $list[]=array('clue_id','required');
        $list[]=array('employee_id,employee_type','required','on'=>array("new","edit"));
        $list[]=array('clue_id','validateClueID');
        $list[]=array('id','validateID');
        return $list;
    }

    public function validateID($attribute, $param) {
        $scenario = $this->getScenario();
        if($scenario=="new"){
            $storeRow = Yii::app()->db->createCommand()->select("id")
                ->from("sal_clue_u_staff")
                ->where("employee_id=:employee_id and clue_id=:clue_id",array(
                    ":employee_id"=>$this->employee_id,
                    ":clue_id"=>$this->clue_id
                ))->queryRow();
            if($storeRow){
                $this->addError($attribute, "员工已存在，无法重复添加");
            }
        }else{
            $storeRow = Yii::app()->db->createCommand()->select("a.*")
                ->from("sal_clue_u_staff a")
                ->where("a.id=:id",array(":id"=>$this->id))->queryRow();
            if($storeRow){
                if($scenario=="delete"){
                    if(!empty($storeRow["employee_type"])){
                        $this->addError($attribute, "主要负责人无法删除");
                    }
                }else{
                    if(!empty($storeRow["employee_type"])){
                        $this->employee_type = $storeRow["employee_type"];
                    }
                }
            }else{
                $this->addError($attribute, "数据异常，请刷新重试");
            }
        }
    }

    public function validateClueID($attribute, $param) {
        $clueHeadModel = new ClueHeadForm("view");
        if($clueHeadModel->retrieveData($this->clue_id)){
            $this->clueHeadRow = $clueHeadModel->getAttributes();
        }else{
            $this->addError($attribute, "线索不存在，请刷新重试");
        }
    }

    public function retrieveData($index)
    {
        $index = empty($index)||!is_numeric($index)?0:intval($index);
        $sql = "select a.* from sal_clue_u_staff a where a.id=".$index." ";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $this->id = $row['id'];
            $this->clue_id = $row['clue_id'];
            $this->employee_id = $row['employee_id'];
            $this->employee_type = $row['employee_type'];

            return true;
        }else{
            return false;
        }
    }

    public function saveData()
    {
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try {
            $this->save($connection);
            $transaction->commit();
        }
        catch(Exception $e) {
            var_dump($e);
            $transaction->rollback();
            throw new CHttpException(404,'Cannot update.');
        }
    }

    protected function save(&$connection)
    {
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city;
        switch ($this->getScenario()){
            case "new":
                $this->resetLocal();
                $connection->createCommand()->insert("sal_clue_u_staff",array(
                    "clue_id"=>$this->clue_id,
                    "employee_id"=>$this->employee_id,
                    "employee_type"=>$this->employee_type,
                    "lcu"=>$uid,
                ));
                $this->id = Yii::app()->db->getLastInsertID();
                $this->setScenario("edit");
                break;
            case "edit":
                $this->resetLocal();
                $connection->createCommand()->update("sal_clue_u_staff",array(
                    "employee_id"=>$this->employee_id,
                    "employee_type"=>$this->employee_type,
                    "luu"=>$uid,
                ),"id=:id",array(":id"=>$this->id));
                break;
            case "delete":
                $connection->createCommand()->delete("sal_clue_u_staff","id=:id",array(":id"=>$this->id));
        }
        $this->sendDataByU();
        return true;
    }

    //发送数据至派单系统
    public function sendDataByU(){
        if(in_array($this->getScenario(),array("new","edit"))){
            $uClientModel = new CurlNotesByClient();
            if(empty($this->clueHeadRow["u_id"])){//客户未同步，则同步客户信息
                $uClientModel->putDataByClientID($this->clue_id);
            }else{
                $uClientModel->putStaffDataByStaffID($this->id,$this->clueHeadRow);
            }
            $uClientModel->setOutContentByData();
            $uClientModel->saveCurlToApi();
        }
    }

    public static function insertUStaffData($clue_id,$employee_id){
        $uid = Yii::app()->user->id;
        Yii::app()->db->createCommand()->insert("sal_clue_u_staff",array(
            "clue_id"=>$clue_id,
            "employee_id"=>$employee_id,
            "employee_type"=>1,
            "lcu"=>$uid,
        ));
    }

    public static function saveUStaffData($clue_id,$employee_id){
        $row = Yii::app()->db->createCommand()->select("id")->from("sal_clue_u_staff")
            ->where("employee_type=1 and clue_id=:id",array(":id"=>$clue_id))->queryRow();
        if($row){
            self::updateUStaffData($clue_id,$employee_id);
        }else{
            self::insertUStaffData($clue_id,$employee_id);
        }
    }

    public static function updateUStaffData($clue_id,$employee_id){
        $uid = Yii::app()->user->id;
        Yii::app()->db->createCommand()->update("sal_clue_u_staff",array(
            "employee_id"=>$employee_id,
            "luu"=>$uid,
        ),"employee_type=1 and clue_id=:id",array(":id"=>$clue_id));
    }

    protected function resetLocal(){
        if(!empty($this->employee_type)){
            Yii::app()->db->createCommand()->update("sal_clue_u_staff",array(
                "employee_type"=>0
            ),"clue_id=:id",array(":id"=>$this->clue_id));
        }
    }

    public function isOccupied($index) {
        $rtn = true;//默认不允许删除
        if($this->retrieveData($index)){
            $rtn = false;//允许删除
        }
        return $rtn;
    }

    public function isReadonly() {
        return $this->getScenario()=='view';
    }
}
