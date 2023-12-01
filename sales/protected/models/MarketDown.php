<?php

class MarketDown extends CFormModel{
    public $id;
    public $file;
    protected $data;//excel的所有内容

    public $_errorList=array();//导入失败的所有资料
    public $_successList=array();//导入成功的所有资料
    public $_errorSum=0;//失败数量

    private $titleKey=array();//页头对应的列
    private $currentRow=0;

    private $_cityList=array();
    private $_areaList=array();
    private $_comSizeList=array();
    private $_comStateList=array();
    private $_comTypeList=array();

    private $downDateTime=null;

    public function insertStaticList(){
        $this->_cityList = MarketFun::getMarketCityList()["list"];
        $this->_areaList = MarketFun::getAreaList();
        $this->_comSizeList = MarketFun::getCompanySizeList();
        $this->_comStateList = MarketFun::getCompanyTypeList();
        $this->_comTypeList = MarketFun::getCompanyStateList();
    }
    /**
     *
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('id,file','safe'),
            array('file', 'file', 'types'=>'xlsx,xls', 'allowEmpty'=>false, 'maxFiles'=>1),
        );
    }

    public static function getTopArr(){
        return array(
            array("name"=>"企业名称","sql"=>"company_name","background"=>"#305496","color"=>"#ffffff"),//
            array("name"=>"城市","sql"=>"city","background"=>"#305496","color"=>"#ffffff"),//
            array("name"=>"区域","sql"=>"area","background"=>"#305496","color"=>"#ffffff"),//
            array("name"=>"企业成立日期","sql"=>"company_date","background"=>"#305496","color"=>"#ffffff"),//
            array("name"=>"企业规模","sql"=>"company_size","background"=>"#305496","color"=>"#ffffff"),//
            array("name"=>"企业分类","sql"=>"company_type","background"=>"#305496","color"=>"#ffffff"),//
            array("name"=>"企业状态","sql"=>"company_state","background"=>"#305496","color"=>"#ffffff"),//
            array("name"=>"法定代表人","sql"=>"legal_user","background"=>"#305496","color"=>"#ffffff"),//
            array("name"=>"企业网址","sql"=>"company_web","background"=>"#305496","color"=>"#ffffff"),//
            array("name"=>"注册地址","sql"=>"sign_address","background"=>"#305496","color"=>"#ffffff"),//
            array("name"=>"经营地址","sql"=>"run_address","background"=>"#305496","color"=>"#ffffff"),//
            array("name"=>"企业介绍","sql"=>"company_note","background"=>"#305496","color"=>"#ffffff"),//

            array("name"=>"联系人","sql"=>"user_name","background"=>"#2A6BA4","color"=>"#ffffff"),//
            array("name"=>"职位","sql"=>"user_dept","background"=>"#2A6BA4","color"=>"#ffffff"),//
            array("name"=>"电话","sql"=>"user_phone","background"=>"#2A6BA4","color"=>"#ffffff"),//
            array("name"=>"邮箱","sql"=>"user_email","background"=>"#2A6BA4","color"=>"#ffffff"),//
            array("name"=>"微信号","sql"=>"user_wechat","background"=>"#2A6BA4","color"=>"#ffffff"),//
            array("name"=>"备注","sql"=>"user_text","background"=>"#2A6BA4","color"=>"#ffffff"),//
            //array("name"=>"附件","sql"=>"attr","background"=>"#2A6BA4","color"=>"#ffffff"),//
        );
    }

    public static function getGroup(){
        return array(
            "company_name","city","area","company_date","company_size",
            "company_type","company_state","legal_user","company_web","sign_address",
            "run_address","company_note",
            "userList"=>array(
                'user_name','user_dept','user_phone','user_email',
                'user_wechat','user_text'
            )
        );
    }

    public function loadData($excelArr){
        $this->downDateTime = date("Y-m-d H:i:s");
        $this->data = key_exists("listBody",$excelArr)?$excelArr["listBody"]:array();

        if($this->validateHeader()){
            $this->addDataBody();
            return true;
        }else{
            return false;
        }
    }

    private function addDataBody(){
        $data = $this->data;
        if(isset($data[0])){
            unset($data[0]);
        }
        $headList = self::getTopArr();
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        $uid = Yii::app()->user->id;
        try {
            foreach ($data as $row){
                $temp = array("sqlType"=>"add","lcu"=>$uid,"userList"=>array());
                $bool=true;
                foreach ($headList as $title){
                    $column = $this->titleKey[$title["sql"]]["column"];
                    $value = $row[$column];
                    if($title["sql"]=="company_name"&&empty($value)){
                        if(empty($this->id)){
                            $this->_errorList[]=$row;//错误客户的多个联系人需要保存
                            $bool=false;
                        }else{
                            $temp["sqlType"]="addUser";
                        }
                    }else{
                        $bool = $this->resetTempForTitle($temp,$value,$title,$row);
                    }
                    if($bool===false){ //数据有异常
                        break;
                    }
                }
                if($bool){
                    $this->saveData($connection,$temp,$row);
                }
                $this->currentRow++;
            }
            $transaction->commit();
        }catch(Exception $e) {
            $transaction->rollback();
            throw new CHttpException(404,$e->getMessage());
        }
    }

    private function saveData(&$connection,$temp,$dataBody){
        $person_phone="";
        $updateEnd=array();
        if($temp["sqlType"]=="add"){
            $addList = $temp;
            $addList["start_date"] = $this->downDateTime;
            unset($addList["sqlType"]);
            unset($addList["userList"]);
            $connection->createCommand()->insert("sal_market", $addList);
            $this->id = Yii::app()->db->getLastInsertID();
            $connection->createCommand()->insert("sal_market_history", array(
                "market_id"=>$this->id,
                "lcu" => Yii::app()->user->id,
                "update_type"=>2,
                "update_html"=>"新增（导入）",
            ));
            $updateEnd["number_no"]=$this->lenStr($this->id);
            if(!isset($this->_successList[$this->id])){
                $this->_successList[$this->id]=array();
            }
            $this->_successList[$this->id] = $temp;
            $this->_successList[$this->id]["userList"] = array();
        }
        if(in_array($temp["sqlType"],array("add","addUser"))&&!empty($temp["userList"])){
            foreach ($temp["userList"] as $row){
                if(isset($row["user_name"])&&isset($row["user_phone"])){
                    $row["market_id"]=$this->id;
                    $row["lcu"]=Yii::app()->user->id;
                    $connection->createCommand()->insert("sal_market_user", $row);
                    $this->_successList[$this->id]["userList"][] = $row;
                }
            }
            $person_phone="";
            foreach ($this->_successList[$this->id]["userList"] as $user){
                $person_phone.=empty($person_phone)?"":"\r\n";
                $person_phone.=$user["user_name"]."/".$user["user_phone"].";";
            }
            if(!empty($person_phone)){
                $updateEnd["person_phone"]=$person_phone;
            }
        }
        if(!empty($updateEnd)){
            $connection->createCommand()->update("sal_market", $updateEnd, "id=:id", array(":id" => $this->id));
        }

    }

    private function lenStr($id){
        $code = strval($id);
        $number_no = "IMP";
        for($i = 0;$i < 5-strlen($code);$i++){
            $number_no.="0";
        }
        $number_no .= $code;
        return $number_no;
    }

    private function resetTempForTitle(&$temp,$value,$title,$dataRow){
        $bool = true;
        switch ($title["sql"]){
            case "company_name"://企业名称
                $this->id=0;
                $row = Yii::app()->db->createCommand()
                    ->select('id,number_no')->from("sal_market")
                    ->where("company_name=:name",array(":name"=>$value))->queryRow();
                if($row){
                    $dataRow["error"]="企业名称已存在，编号：".$row["number_no"];
                    $this->_errorList[]=$dataRow;
                    $this->_errorSum++;
                    $bool = false;
                }else{
                    $temp["sqlType"]="add";
                    $temp["company_name"]=$value;
                }
                break;
            case "city"://城市
                $city = array_search($value,$this->_cityList);
                if($city){
                    $temp["city"]=$city;
                    $temp["city_name"]=$city;
                }else{
                    $temp["city_name"]=$value;
                }
                break;
            case "area"://区域
                $area = array_search($value,$this->_areaList);
                if($area){
                    $temp["area"]=$area;
                }
                break;
            case "company_date"://企业成立日期
                if(!empty($value)){
                    $temp["company_date"]=date("Y-m-d",strtotime($value));
                }
                break;
            case "company_size"://企业规模
                $size = array_search($value,$this->_comSizeList);
                if($size){
                    $temp["company_size"]=$size;
                }
                break;
            case "company_type"://企业分类
                $type = array_search($value,$this->_comTypeList);
                if($type){
                    $temp["company_type"]=$type;
                }
                break;
            case "company_state"://企业状态
                $state = array_search($value,$this->_comStateList);
                if($state){
                    $temp["company_state"]=$state;
                }
                break;
            case "legal_user"://法定代表人
            case "company_web"://企业网址
            case "sign_address"://注册地址
            case "run_address"://经营地址
            case "company_note"://企业介绍
                if(!empty($value)){
                    $temp[$title["sql"]]=$value;
                }
                break;
            case "user_name"://联系人
            case "user_phone"://电话
            case "user_dept"://职位
            case "user_email"://邮箱
            case "user_wechat"://微信号
            case "user_text"://备注
                if(!empty($value)){
                    $temp["userList"][$this->currentRow][$title["sql"]]=$value;
                }
                break;
            case "attr"://附件
                break;

        }
        return $bool;
    }

    private function validateHeader(){
        $bool = true;
        $headList = reset($this->data);
        $rows = self::getTopArr();
        foreach ($rows as $row){
            $key = array_search($row["name"],$headList);
            if($key===false){
                $this->addError("file", "Excel的第四行没有找到 ".$row["name"]);
                $bool = false;
            }else{
                $row['column'] = $key;
                $this->titleKey[$row["sql"]]=$row;
            }
        }
        return $bool;
    }

    //下载导入模板
    public function downTemp(){
        $excelData=array(
            array(
                "company_name"=>"例子1",
                "city"=>"珠海",
                "area"=>"华南",
                "company_date"=>"2023-11-22",
                "company_size"=>"小",
                "company_type"=>"食品加工厂",
                "company_state"=>"续存",
                "legal_user"=>"代表人1",
                "company_web"=>"",
                "sign_address"=>"地址1",
                "run_address"=>"地址2",
                "company_note"=>"说明",
                "userList"=>array(
                    array(
                        "user_name"=>"联系人1",
                        "user_dept"=>"",
                        "user_phone"=>"电话1",
                        "user_email"=>"",
                        "user_wechat"=>"",
                        "user_text"=>""
                    ),
                    array(
                        "user_name"=>"联系人2",
                        "user_dept"=>"",
                        "user_phone"=>"电话2",
                        "user_email"=>"",
                        "user_wechat"=>"",
                        "user_text"=>""
                    ),
                ),
            ),
            array(
                "company_name"=>"例子2",
                "city"=>"",
                "area"=>"",
                "company_date"=>"2023-11-22",
                "company_size"=>"",
                "company_type"=>"",
                "company_state"=>"",
                "legal_user"=>"",
                "company_web"=>"",
                "sign_address"=>"",
                "run_address"=>"",
                "company_note"=>"",
                "userList"=>array(),
            ),
            array(
                "company_name"=>"例子3",
                "city"=>"珠海",
                "area"=>"华南",
                "company_date"=>"2023-11-22",
                "company_size"=>"大",
                "company_type"=>"学校",
                "company_state"=>"续存",
                "legal_user"=>"代表人3",
                "company_web"=>"",
                "sign_address"=>"地址3",
                "run_address"=>"地址4",
                "company_note"=>"说明4",
                "userList"=>array(
                    array(
                        "user_name"=>"联系人4",
                        "user_dept"=>"",
                        "user_phone"=>"电话4",
                        "user_email"=>"",
                        "user_wechat"=>"",
                        "user_text"=>""
                    ),
                ),
            ),
        );
        $headList = self::getTopArr();
        $group = self::getGroup();
        $group["group"][]='attr';
        $excel = new DownKAExcel();
        $excel->colTwo=1;
        $excel->SetHeaderTitle("导入模板");
        $str="注：从第5行开始（例子可以删除）\n";
        $str.="列宽、行高可以拉伸（不影响导入）";
        $excel->SetHeaderString($str);
        $excel->init();
        $excel->setHeaderForOneList($headList);
        $excel->setGroupData($excelData,$group);
        $excel->outExcel("导入模板");
    }

    //下载失败列表
    public function downErrorList(){
        $error = array(
            array("name"=>"失败原因","sql"=>"error","width"=>"30","background"=>"#f0ff9d","color"=>"#a94442"),//
        );
        $headList = reset($this->data);
        $headList = array_merge($error,$headList);
        $group["group"][]='attr';
        $excel = new DownKAExcel();
        $excel->colTwo=1;
        $excel->SetHeaderTitle("导入失败");
        $str="注：导入失败的文档修改后可以重新导入\n";
        $str.="只需要修改单元格内容";
        $excel->SetHeaderString($str);
        $excel->init();
        $excel->setHeaderForErrorList($headList);
        $excel->setErrorData($this->_errorList,$error);
        $excel->outExcel("导入失败");
    }
}
