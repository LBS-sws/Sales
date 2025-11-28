<?php

class ClueUAreaForm extends CFormModel
{
    /* User Fields */
    public $id;
    public $clue_id;
    public $city_code;
    public $city_type;

    public $clueHeadRow;

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        $list = array(
            'city_code'=>Yii::t('clue','city manger'),//城市
            'city_type'=>Yii::t('clue','client u area'),//
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
        $list[]=array('city_code,city_type','required','on'=>array("new","edit"));
        $list[]=array('clue_id','validateClueID');
        $list[]=array('id','validateID');
        return $list;
    }

    public function validateID($attribute, $param) {
        $scenario = $this->getScenario();
        if($scenario=="new"){
            $storeRow = Yii::app()->db->createCommand()->select("id")
                ->from("sal_clue_u_area")
                ->where("city_code=:city_code and clue_id=:clue_id",array(
                    ":city_code"=>$this->city_code,
                    ":clue_id"=>$this->clue_id
                ))->queryRow();
            if($storeRow){
                $this->addError($attribute, "城市已存在，无法重复添加");
            }
        }else{
            $storeRow = Yii::app()->db->createCommand()->select("a.*")
                ->from("sal_clue_u_area a")
                ->where("a.id=:id",array(":id"=>$this->id))->queryRow();
            if($storeRow){
                if($scenario=="delete"){
                    if(!empty($storeRow["city_type"])){
                        $this->addError($attribute, "本部区域无法删除");
                    }
                }else{
                    if(!empty($storeRow["city_type"])){
                        $this->city_type = $storeRow["city_type"];
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
        $sql = "select a.* from sal_clue_u_area a where a.id=".$index." ";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $this->id = $row['id'];
            $this->clue_id = $row['clue_id'];
            $this->city_code = $row['city_code'];
            $this->city_type = $row['city_type'];

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
                $connection->createCommand()->insert("sal_clue_u_area",array(
                    "clue_id"=>$this->clue_id,
                    "city_code"=>$this->city_code,
                    "city_type"=>$this->city_type,
                    "lcu"=>$uid,
                ));
                $this->id = Yii::app()->db->getLastInsertID();
                $this->setScenario("edit");
                break;
            case "edit":
                $this->resetLocal();
                $connection->createCommand()->update("sal_clue_u_area",array(
                    "city_code"=>$this->city_code,
                    "city_type"=>$this->city_type,
                    "luu"=>$uid,
                ),"id=:id",array(":id"=>$this->id));
                break;
            case "delete":
                $connection->createCommand()->delete("sal_clue_u_area","id=:id",array(":id"=>$this->id));
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
                $uClientModel->putAreaDataByAreaID($this->id,$this->clueHeadRow);
            }
            $uClientModel->setOutContentByData();
            $uClientModel->saveCurlToApi();
        }
    }

    public static function saveUAreaData($clue_id,$city_code,$uid=''){
        $suffix = Yii::app()->params['envSuffix'];
        $inCityCode=array();
        if($city_code=="KAH"){//Holdco需要显示全国
            $rows = Yii::app()->db->createCommand()->select("a.code")->from("swoper{$suffix}.swo_city_set a")
                ->leftJoin("security{$suffix}.sec_city b","a.code=b.code")
                ->where("a.show_type=1 and b.ka_bool=1",array(":region_code"=>$city_code))->queryAll();
        }else{
            $rows = Yii::app()->db->createCommand()->select("a.code")->from("swoper{$suffix}.swo_city_set a")
                ->leftJoin("security{$suffix}.sec_city b","a.code=b.code")
                ->where("(a.region_code=:region_code or a.code='{$city_code}') and a.show_type=1 and b.ka_bool!=2",array(":region_code"=>$city_code))->queryAll();
        }
        if($rows){
            foreach ($rows as $row){
                $inCityCode[]=$row["code"];
                self::saveUAreaDataByCity($clue_id,$row["code"],0,$uid);
            }
        }
        $ids = "'".implode("','",$inCityCode)."'";
        Yii::app()->db->createCommand()->delete("sal_clue_u_area","clue_id=:clue_id and city_code not in ({$ids})",array(":clue_id"=>$clue_id));
    }

    public static function saveUAreaDataByCity($clue_id,$city_code,$city_type=0,$uid=''){
        $uid = empty($uid)?Yii::app()->user->id:$uid;
        $topRow = Yii::app()->db->createCommand()->select("id")->from("sal_clue_u_area")
            ->where("clue_id=:clue_id and city_code=:city_code",array(":clue_id"=>$clue_id,":city_code"=>$city_code))->queryRow();
        if($topRow){
            Yii::app()->db->createCommand()->update("sal_clue_u_area",array(
                "city_type"=>$city_type,
                "luu"=>$uid,
            ),"id=:id",array(":id"=>$clue_id));
        }else{
            Yii::app()->db->createCommand()->insert("sal_clue_u_area",array(
                "clue_id"=>$clue_id,
                "city_code"=>$city_code,
                "city_type"=>$city_type,
                "lcu"=>$uid,
            ));
        }
    }

    public static function insertUAreaData($clue_id,$city_code){
        $uid = Yii::app()->user->id;
        Yii::app()->db->createCommand()->insert("sal_clue_u_area",array(
            "clue_id"=>$clue_id,
            "city_code"=>$city_code,
            "city_type"=>1,
            "lcu"=>$uid,
        ));
    }

    public static function updateUAreaData($clue_id,$city_code){
        $uid = Yii::app()->user->id;
        Yii::app()->db->createCommand()->update("sal_clue_u_area",array(
            "city_code"=>$city_code,
            "luu"=>$uid,
        ),"city_type=1 and clue_id=:id",array(":id"=>$clue_id));
    }

    protected function resetLocal(){
        if(!empty($this->city_type)){
            Yii::app()->db->createCommand()->update("sal_clue_u_area",array(
                "city_type"=>0
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
