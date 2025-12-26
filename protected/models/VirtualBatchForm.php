<?php

class VirtualBatchForm extends CFormModel
{
    public $id;//
    public $pro_code;//操作
    public $pro_type;//操作类型 C：续约
    public $pro_date;//操作时间
    public $pro_remark;//操作备注
    public $pro_status=0;//操作进行中的状态
    public $pro_change=0;//
    public $total_amt=0;//
    public $city;//
    public $create_staff;//
    public $vir_id;//
    public $vir_id_text;//
    public $vir_code_arr;//
    public $busine_id;//
    public $busine_id_text;//
    public $stop_set_id;//
    public $stop_date;//
    public $stop_month_amt;//
    public $stop_year_amt;//
    public $stop_sum_amt;//
    public $need_back;//
    public $need_back_json;//
    public $surplus_num;//
    public $surplus_amt;//
    public $surplus_json;//
    public $contract_ids;//派单系统id
    public $mh_remark;//
    public $mh_id;//
    public $jq_sum;//原机器数量
    public $jq_sum_back;//机器拆回数量
    public $lbsMain;//只能调整同一个服务主体
    public $yewudalei;//只能调整同一个业务大类
    public $salesID;//只能调整同一个销售
    public $cust_name;//只能调整同一个客户

    public $service;
    public $serviceJson;
    public $login_employee_id;//
    public $vir_id_arr;//
    public $virHeadRows;
    public $lookFileRow;
    public $virJson;
    public $fileJson;
    public $docMaxSize = 10485760;//1024*1024*10 = 10M
    public $goMhWebUrl;

    public $compareModel;
    public $compareArr=array();

    public function attributeLabels()
    {
        $list = array(
            'id'=>Yii::t('clue','contract id'),//合同id
            'surplus_num'=>Yii::t('clue','surplus num'),//剩余次数
            'surplus_amt'=>Yii::t('clue','surplus amt'),//剩余金额
            'pro_code'=>Yii::t('clue','pro code'),//操作编号
            'pro_type'=>Yii::t('clue','pro type'),//操作类型
            'pro_num'=>Yii::t('clue','pro num'),//同类型操作次数
            'pro_date'=>Yii::t('clue','pro date'),//操作生效时间
            'pro_remark'=>Yii::t('clue','pro remark'),//操作备注
            'pro_status'=>Yii::t('clue','status'),//操作进行中的状态
            'stop_set_id'=>Yii::t('clue','stop set id'),//终止、暂停原因id
            'stop_month_amt'=>Yii::t('clue','and month amt'),//涉及月金额
            'stop_year_amt'=>Yii::t('clue','and year amt'),//涉及年金额
            'stop_sum_amt'=>Yii::t('clue','and sum amt'),//涉及总金额
            'need_back'=>Yii::t('clue','need back'),//
            'need_back_json'=>Yii::t('clue','need back json'),//
        );
        return $list;
    }

    public function rules(){
        $list=array();
        $list[] = array('pro_code,pro_type,pro_date,pro_remark,pro_status,service','safe');
        $list[] = array('stop_set_id,stop_month_amt,stop_sum_amt,stop_year_amt','safe');
        $list[] = array('need_back,need_back_json,surplus_num,surplus_amt','safe');
        $list[]=array('pro_type,pro_date','required','on'=>array('audit'));
        $list[]=array('stop_set_id,need_back,surplus_num,surplus_amt','validateST','on'=>array('audit'));
        $list[]=array('id','validateID');
        $list[]=array('vir_id_text','required');
        $list[]=array('vir_id_text','validateVirIDText');
        $list[]=array('pro_type','validateStatus');
        $list[]=array('fileJson','validateFileJson');
        $list[]=array('vir_id','validateA','on'=>array('draft','audit'));
        $list[]=array('vir_id','validateNeedBack','on'=>array('draft','audit'));
        $list[]=array('vir_id','computeUIDs');
        $list[]=array('vir_id','validateSurplusJson');
        //$list[]=array('id','validateTest','on'=>array('draft'));
        return $list;
    }

    public function computeUIDs(){
        $this->contract_ids=array();
        $this->surplus_json=array();
        if(is_array($this->vir_id_arr)){
            foreach ($this->vir_id_arr as $id){
                $row = Yii::app()->db->createCommand()->select("id,vir_code,u_id,service_sum,year_amt")->from("sal_contract_virtual")
                    ->where("id=:id",array(":id"=>$id))->queryRow();
                if($row){
                    if(empty($row["u_id"])){
                        $this->addError("test","合约（{$row["vir_code"]}）未同步到派单系统，无法操作");
                        return false;
                    }else{
                        $this->contract_ids[$row["u_id"]]=$row["id"];
                        $this->surplus_json[$row["id"]]=array(
                            "surplus_number"=>empty($row["service_sum"])?0:intval($row["service_sum"]),
                            "surplus_money"=>empty($row["year_amt"])?0:floatval($row["year_amt"]),
                        );
                    }
                }else{
                    $this->addError("test","合约不存在（{$id}）");
                    return false;
                }
            }
        }
    }

    public function validateSurplusJson(){
        if($this->pro_type=="T"&&!empty($this->contract_ids)){
            $this->surplus_amt=0;
            $this->surplus_num=0;
            $model = new CurlNotesModel();
            $model->sendSurplusDataSetByUID();
            $model->setMinUrl($model->min_url);
            $uIDs = array_keys($this->contract_ids);
            $data=array("contract_ids"=>implode(",",$uIDs));
            $list = $model->sendUData($data,"GET",false);
            if($list["status"]){
                $outData = $list["outData"]["data"];
                foreach ($outData as $row){
                    if(isset($this->contract_ids[$row["contract_id"]])){
                        $id = $this->contract_ids[$row["contract_id"]];
                        $this->surplus_json[$id]=$row;
                    }else{
                        //允许强制终止合约
                        //$this->addError("test","派单合约id（{$row["contract_id"]}）不存在");
                        //return false;
                    }
                }
            }else{
                /*
                 * 允许强制终止合约
                $msg = $list["message"];
                if(!empty($list["outData"]["data"])){
                    foreach ($list["outData"]["data"] as $item){
                        if(isset($item["msg"])){
                            $msg.="；".$item["msg"];
                        }
                    }
                }
                $this->addError("test",$msg);
                */
            }
            foreach ($this->surplus_json as $item){
                $this->surplus_num+=$item["surplus_number"];
                $this->surplus_amt+=$item["surplus_money"];
            }
        }
    }

    public function validateNeedBack(){
        $this->jq_sum=0;
        $this->jq_sum_back=0;
        if($this->pro_type=="T"&&$this->need_back=="Y"){
            if(!empty($this->need_back_json)){
                foreach ($this->need_back_json as $row){
                    $this->jq_sum+=$row["field_sum"];
                    $this->jq_sum_back+=$row["field_back"];
                }
            }
        }
    }

    public function validateTest(){
        $this->addError("test",'test');
    }

    public function validateA(){
        if($this->pro_type=="A"){
            $virModel = new VirtualForm("batch");
            if(isset($_POST['VirtualProForm'])){
                $virModel->attributes = $_POST['VirtualProForm'];
            }
            $virModel->id=$this->vir_id;
            $virModel->validate();
            $this->virJson = $virModel->getAttributes();
            if($this->getScenario()=="audit"&&$virModel->hasErrors()){
                $this->addErrors($virModel->getErrors());
            }
        }
    }

    public function validateST(){
        if(in_array($this->pro_type,array("S","T"))){
            if(empty($this->stop_set_id)){
                $this->addError("stop_set_id",'原因不能为空');
            }
            if($this->pro_type=="T"){
                if(empty($this->need_back)){
                    $this->addError("stop_set_id",'是否需要拆机不能为空');
                }
            }
        }
    }

    public function validateFileJson($attribute, $param) {
        $modelClass = get_class($this);
        if(isset($_FILES[$modelClass]['name']['fileJson'])){
            foreach ($_FILES[$modelClass]['name']['fileJson'] as $key=>$row){
                $fileName = $row["fileVal"];
                if(empty($fileName)){
                    continue;
                }
                $fileError = isset($_FILES[$modelClass]['error']['fileJson'][$key]["fileVal"])?$_FILES[$modelClass]['error']['fileJson'][$key]["fileVal"]:100;
                if(empty($fileError)){
                    $fileType = $_FILES[$modelClass]['type']['fileJson'][$key]["fileVal"];
                    $fileSize = floatval($_FILES[$modelClass]['size']['fileJson'][$key]["fileVal"]);
                    $fileTmpName = $_FILES[$modelClass]['tmp_name']['fileJson'][$key]["fileVal"];
                    $ext = pathinfo($fileName,PATHINFO_EXTENSION);
                    if(in_array($ext,array("jpeg","jpg","png","xlsx","xls","pdf","docx","txt","doc","wps"))){
                        if($fileSize>$this->docMaxSize){
                            $this->addError($attribute,'文件大小不能大于10M'.$fileSize);
                            break;
                        }else{
                            $this->fileJson[$key]["file"]=array(
                                "fileTmpName"=>$fileTmpName,
                                "fileSize"=>$fileSize,
                                "fileType"=>$fileType,
                                "fileName"=>$fileName,
                                "fileExt"=>$ext,
                            );
                        }
                    }else{
                        $this->addError($attribute,'文件格式异常，请重试上传');
                        break;
                    }
                }else{
                    $this->addError($attribute,'文件异常，请刷新重试');
                    break;
                }
            }
        }
    }

    public function validateVirIDText($attribute, $param) {
        $id_list = explode(",",$this->vir_id_text);
        if(empty($id_list)||!is_array($id_list)){
            $this->addError("id", "请选择虚拟合约");
        }else{
            $this->lbsMain="";
            $this->stop_month_amt=0;
            $this->stop_year_amt=0;
            $this->cust_name="";
            $clue_id=0;
            $this->city = "";
            $vir_id_arr = array();
            $this->busine_id = array();
            $busine_id_text = array();
            $this->vir_code_arr = array();
            $this->virHeadRows=array();
            foreach ($id_list as $id){
                $this->vir_id=$id;//防止报错后无法跳转
                $row = Yii::app()->db->createCommand()->select("vir_code")->from("sal_contpro_virtual")
                    ->where("pro_status not in (10,30) and vir_batch_id!=:id and vir_id=:vir_id",array(":id"=>$this->id,":vir_id"=>$id))->order("id desc")->queryRow();
                if($this->pro_status!=30&&$row){
                    $this->addError("id", "虚拟合约({$row['vir_code']})已有变更，无法继续变更");
                    return false;
                }
                $virModel = new VirtualHeadForm("edit");
                if($virModel->retrieveData($id)&&in_array($virModel->vir_status,array(10,30,40,50,60))){
                    $this->city = empty($this->city)?$virModel->city:$this->city;
                    $this->lbsMain = empty($this->lbsMain)?$virModel->lbs_main:$this->lbsMain;
                    $this->yewudalei = empty($this->yewudalei)?$virModel->yewudalei:$this->yewudalei;
                    $this->salesID = empty($this->salesID)?$virModel->sales_id:$this->salesID;
                    if(empty($clue_id)){
                        $clue_id = $virModel->clue_id;
                        $this->cust_name = CGetName::getClueNameByID($clue_id);
                    }
                    $clue_id = empty($clue_id)?$virModel->clue_id:$clue_id;
                    if($this->pro_status!=30&&in_array($this->pro_type,array("A","S"))&&!in_array($virModel->vir_status,array(10,30))){
                        $this->addError("id", "虚拟合约({$virModel['vir_code']})不在生效中，无法修改或暂停");
                        return false;
                    }
                    if($this->pro_status!=30&&$this->pro_type=="T"&&!in_array($virModel->vir_status,array(10,30,40))){
                        $this->addError("id", "虚拟合约({$virModel['vir_code']})不在生效中或暂停中，无法终止");
                        return false;
                    }
                    if($this->pro_status!=30&&$this->pro_type=="R"&&!in_array($virModel->vir_status,array(40,50))){
                        $this->addError("id", "虚拟合约({$virModel['vir_code']})没有暂停或终止，无法恢复");
                        return false;
                    }
                    if($this->lbsMain!=$virModel->lbs_main){
                        $this->addError("id", "虚拟合约({$virModel['vir_code']})的主体公司不一致，请分开操作");
                        return false;
                    }
                    if($this->yewudalei!=$virModel->yewudalei){
                        $this->addError("id", "虚拟合约({$virModel['vir_code']})的业务大类不一致，请分开操作");
                        return false;
                    }
                    if($this->salesID!=$virModel->sales_id){
                        $this->addError("id", "虚拟合约({$virModel['vir_code']})的销售不一致，请分开操作");
                        return false;
                    }
                    if($clue_id!=$virModel->clue_id){
                        $this->addError("id", "虚拟合约({$virModel['vir_code']})的主合同不一致，请分开操作");
                        return false;
                    }
                    $busine_id = $virModel->busine_id[0];
                    if(!in_array($busine_id,$this->busine_id)){
                        $this->busine_id[]=$busine_id;
                        $busine_id_text[]=$virModel->busine_id_text;
                    }
                    $this->stop_month_amt+=$virModel->month_amt;
                    $this->stop_year_amt+=$virModel->year_amt;
                    $vir_id_arr[] = $id;
                    $this->vir_code_arr[] = $virModel->vir_code;
                    $this->virHeadRows[$id]=$virModel->getAttributes();
                }
            }
            if(empty($vir_id_arr)){
                $this->addError("id", "虚拟合约异常");
            }else{
                $this->vir_id_arr = $vir_id_arr;
                $this->vir_id = $vir_id_arr[0];
                $this->vir_id_text = implode(",",$vir_id_arr);
                $this->busine_id_text = implode("、",$busine_id_text);
            }
        }
    }

    public function validateStatus($attribute, $param) {
        $proTypeList = CGetName::getProTypeList();
        if(!key_exists($this->pro_type,$proTypeList)){
            $this->addError("id", "操作类型异常（{$this->pro_type}）");
            return false;
        }
        if(!in_array($this->pro_status,array(0,9))){
            $this->addError($attribute, "该状态无法编辑（{$this->pro_status}）");
            return false;
        }
        if($this->pro_type=="A"&&count($this->busine_id)!=1){
            $this->addError($attribute, "合约内容修改只能选择同一个服务项目");
            return false;
        }
    }

    public function validateID($attribute, $param) {
        $this->login_employee_id=CGetName::getEmployeeIDByMy();
        if(!empty($this->id)){
            $row = Yii::app()->db->createCommand()->select("*")->from("sal_virtual_batch")
                ->where("id=:id",array(":id"=>$this->id))->queryRow();//
            if($row){
                $this->pro_type = $row["pro_type"];
                $this->vir_id_text = $row["vir_id_text"];
            }else{
                $this->addError($attribute, "数据异常，请刷新重试");
            }
        }
    }

    public function validateSeal(){
        $list =array('status'=>200,'message'=>"");
        if($this->pro_status==19){
            $row = Yii::app()->db->createCommand()->select("id")->from("sal_virtual_batch_file")
                ->where("vir_batch_id=:id and group_id=1",array(":id"=>$this->id))->queryRow();
            if(!$row){
                $list["status"]=500;
                $list["message"]="请前往销售系统上传盖章文件";
            }
        }
        return $list;
    }

    public function getModelIDByFileID($fileID){
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_virtual_batch_file")
            ->where("id=:id",array(":id"=>$fileID))->queryRow();//
        if($row){
            $this->id=$row["id"];
            $this->lookFileRow = $row;
        }else{
            $this->id=0;
        }
    }

    public function getFileJson(){
        $this->fileJson=array();
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_virtual_batch_file")
            ->where("vir_batch_id=:id",array(":id"=>$this->id))->order("id asc")->queryAll();//
        if($rows){
            foreach ($rows as $row){
                $this->fileJson[]=array(
                    "id"=>$row["id"],
                    "contID"=>$this->id,
                    //"fileID"=>$row["phy_path_name"]."/".$row["phy_file_name"],
                    "fileID"=>$row["phy_file_name"],
                    "fileVal"=>"",
                    "fileName"=>$row["file_name"],
                    "tableName"=>"pro",
                    "uflag"=>"N",
                );
            }
        }
    }

    public function getSealFileJson(){
        $fileJson=array();
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_virtual_batch_file")
            ->where("vir_batch_id=:id and group_id=100",array(":id"=>$this->id))->order("id asc")->queryAll();//
        if($rows){
            foreach ($rows as $row){
                $fileJson[]=array(
                    "id"=>$row["id"],
                    "contID"=>$this->id,
                    //"fileID"=>$row["phy_path_name"]."/".$row["phy_file_name"],
                    "fileID"=>$row["phy_file_name"],
                    "fileVal"=>"",
                    "fileName"=>$row["file_name"],
                    "tableName"=>"pro",
                    "uflag"=>"N",
                );
            }
        }
        return $fileJson;
    }

    public function getNeedBackJson(){
        if(empty($this->need_back_json)){
            $this->need_back_json=array();
            $vir_id_text = empty($this->vir_id_text)?0:$this->vir_id_text;
            $fieldStr="'0'";
            $fileList = array();
            $deviceRows=Yii::app()->db->createCommand()->select("id_char,name")->from("sal_service_type_info")
                ->where("input_type='device' and z_display=1")->queryAll();//
            if($deviceRows){
                foreach ($deviceRows as $deviceRow){
                    $field_id = "svc_{$deviceRow["id_char"]}";
                    $fieldStr.=",'{$field_id}'";
                    $fileList[$field_id]=$deviceRow["name"];
                }
            }
            $rows = Yii::app()->db->createCommand()->select("field_id,sum(0+field_value) as sum")->from("sal_contract_vir_info")
                ->where("virtual_id in ({$vir_id_text}) and field_id in ({$fieldStr}) and field_value!=''")->group("field_id")->queryAll();
            if($rows){
                foreach ($rows as $row){
                    $this->need_back_json[$row["field_id"]]=array(
                        "field_id"=>$row["field_id"],
                        "field_name"=>isset($fileList[$row["field_id"]])?$fileList[$row["field_id"]]:"none",
                        "field_sum"=>$row["sum"],
                        "field_back"=>$row["sum"],
                    );
                }
            }
        }
    }
    public function setCompareModelByAudit(){
        $this->computeCompareArr();
    }

    protected function computeCompareArr(){
        $virProModel = new VirtualProForm();//当前信息
        $virProModel->retrieveDataByBatchIDAndVirID($this->id,$this->vir_id);
        $updateList = $virProModel->historyUpdateList();
        $row = Yii::app()->db->createCommand()->select("id")->from("sal_contpro_virtual")
            ->where("id<:id and vir_id=:vir_id",array(":id"=>$virProModel->id,":vir_id"=>$this->vir_id))
            ->order("id desc")->queryRow();
        $this->compareModel = new VirtualProForm();//上一条信息
        if($row){
            $this->compareModel->retrieveData($row["id"]);
        }
        foreach ($updateList as $item){
            if($virProModel->$item!=$this->compareModel->$item){
                $this->compareArr[]=array(
                    "key"=>$item,
                    "name"=>$virProModel->getAttributeLabel($item),
                    "newText"=>$virProModel->getNameForValue($item,$virProModel->$item,$virProModel),
                    "oldText"=>$virProModel->getNameForValue($item,$this->compareModel->$item,$this->compareModel),
                );
            }
        }
    }

    public function printCompareHtmlByAudit(){
        $html="";
        $html.= "<div class='table-responsive'><table id='compareTable' class='table table-hover table-striped table-bordered table-condensed'>";
        $html.= "<thead><tr><th>被修改字段名称</th><th>修改前信息</th><th>修改后信息</th></tr></thead><tbody>";
        foreach ($this->compareArr as $compareItem){
            $html.= "<tr data-key='{$compareItem["key"]}'>";
            $html.= "<th>".$compareItem["name"]."</th>";
            $html.= "<td>".$compareItem["oldText"]."</td>";
            $html.= "<td>".$compareItem["newText"]."</td>";
            $html.= "</tr>";
        }
        $html.= "</tbody></table></div>";
        return $html;
    }

    public function retrieveData($index)
    {
        $index = empty($index)||!is_numeric($index)?0:$index;
        $sql = "select a.* from sal_virtual_batch a where a.id=".$index;
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $this->id = $row['id'];
            $this->pro_code = $row['pro_code'];
            $this->pro_type = $row['pro_type'];
            $this->pro_date = empty($row['pro_date'])?"":General::toDate($row['pro_date']);
            $this->pro_remark = $row['pro_remark'];
            $this->pro_status = $row['pro_status'];

            $this->city = $row['city'];
            $this->vir_id = $row['vir_id'];
            $this->vir_id_text = $row['vir_id_text'];
            $this->stop_set_id = $row['stop_set_id'];
            $this->stop_date = empty($row['stop_date'])?"":General::toDate($row['stop_date']);
            $this->stop_month_amt = $row['stop_month_amt'];
            $this->stop_year_amt = $row['stop_year_amt'];
            $this->stop_sum_amt = $row['stop_sum_amt'];
            $this->need_back = $row['need_back'];
            $this->need_back_json = empty($row['need_back_json'])?array():json_decode($row['need_back_json'],true);
            $this->surplus_num = $row['surplus_num'];
            $this->surplus_amt = $row['surplus_amt'];
            $this->surplus_json = empty($row['surplus_json'])?array():json_decode($row['surplus_json'],true);
            $this->mh_remark = $row['mh_remark'];
            $this->mh_id = $row['mh_id'];
            /*
            $this->create_staff = $row['create_staff'];
            $this->busine_id = $row['busine_id'];
            $this->busine_id_text = $row['busine_id_text'];
            $this->lcu = $row['lcu'];
            $this->luu = $row['luu'];
            $this->lcd = $row['lcd'];
            $this->lud = $row['lud'];
*/
            return true;
        }else{
            return false;
        }
    }

    public function validateUploadSeal(){
        if(empty($this->fileJson)){
            $this->addError("fileJson",'至少上传一个文件');
            return false;
        }else{
            $this->validateFileJson("fileJson",'');
            if($this->hasErrors()){
                return false;
            }else{
                $fileBool=false;
                foreach ($this->fileJson as $row){
                    if(!empty($row["id"])||isset($row["file"])){
                        $fileBool=true;
                        break;
                    }
                }
                if($fileBool){
                    return true;
                }else{
                    $this->addError("fileJson",'至少上传一个文件');
                    return false;
                }
            }
        }
    }

    public function setScenarioByID(){
        if($this->getScenario()=="delete"){

        }elseif(empty($this->id)){
            $this->setScenario('new');
        }else{
            $this->setScenario('edit');
        }
    }

    public function saveData()
    {
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try {
            $this->setScenarioByID();
            $this->save($connection);
            $transaction->commit();
            $this->sendDataToMH();//发送消息至门户网站
            return true;
            /*
            $mhList = $this->sendDataToMH();//发送消息至门户网站
            if($mhList["bool"]){
                $transaction->commit();
                return true;
            }else{
                $this->addError("id",$mhList["msg"]);
                $transaction->rollback();
                return false;
            }
            */
        }catch(Exception $e) {
            $transaction->rollback();
            $errorMsg = isset($e->statusCode)?$e->statusCode:"Cannot update";
            $errorMsg.= "：";
            $errorMsg.= $e->getMessage();
            throw new CHttpException(404,$errorMsg);
        }
    }

    protected function getMySaveArr(){
        $this->pro_date = empty($this->pro_date)?null:General::toDate($this->pro_date);
        $saveArr= array(
            "pro_type"=>$this->pro_type,
            "pro_date"=>$this->pro_date,
            "pro_remark"=>$this->pro_remark,
            "pro_status"=>$this->pro_status,
            "city"=>$this->city,
            "vir_id"=>$this->vir_id,
            "vir_id_text"=>$this->vir_id_text,
            "vir_code_text"=>implode(",",$this->vir_code_arr),
            "busine_id"=>implode(",",$this->busine_id),
            "busine_id_text"=>$this->busine_id_text,
        );
        switch ($this->pro_type){
            case "A":
                break;
            case "S":
                $this->stop_set_id = CGetName::getNumberNull($this->stop_set_id);
                $this->stop_date = $this->pro_date;
                $this->stop_month_amt = CGetName::getNumberNull($this->stop_month_amt);
                $this->stop_year_amt = CGetName::getNumberNull($this->stop_year_amt);
                $this->stop_sum_amt = CGetName::getNumberNull($this->stop_sum_amt);
                $saveArrExpr = array(
                    "stop_set_id"=>$this->stop_set_id,
                    "stop_date"=>$this->stop_date,
                    "stop_month_amt"=>$this->stop_month_amt,
                    "stop_year_amt"=>$this->stop_year_amt,
                    "stop_sum_amt"=>$this->stop_sum_amt,
                );
                $saveArr = array_merge($saveArr,$saveArrExpr);
                break;
            case "T":
                $this->stop_set_id = CGetName::getNumberNull($this->stop_set_id);
                $this->stop_date = $this->pro_date;
                $this->stop_month_amt = CGetName::getNumberNull($this->stop_month_amt);
                $this->stop_year_amt = CGetName::getNumberNull($this->stop_year_amt);
                $this->stop_sum_amt = CGetName::getNumberNull($this->stop_sum_amt);
                $this->surplus_num = CGetName::getNumberNull($this->surplus_num);
                $this->surplus_amt = CGetName::getNumberNull($this->surplus_amt);
                $this->need_back_json = is_array($this->need_back_json)?json_encode($this->need_back_json,JSON_UNESCAPED_UNICODE):$this->need_back_json;
                $saveArrExpr=array(
                    "stop_set_id"=>$this->stop_set_id,
                    "stop_date"=>$this->stop_date,
                    "stop_month_amt"=>$this->stop_month_amt,
                    "stop_year_amt"=>$this->stop_year_amt,
                    "stop_sum_amt"=>$this->stop_sum_amt,
                    "surplus_num"=>$this->surplus_num,
                    "surplus_amt"=>$this->surplus_amt,
                    "need_back"=>$this->need_back,
                    "need_back_json"=>$this->need_back_json,
                    "surplus_json"=>!empty($this->surplus_json)?json_encode($this->surplus_json,JSON_UNESCAPED_UNICODE):null,
                );
                $saveArr = array_merge($saveArr,$saveArrExpr);
                break;
            case "R":
                break;
        }
        return $saveArr;
    }

    protected function save(&$connection)
    {
        $uid = Yii::app()->user->id;
        //$this->cont_month_len = CGetName::computeMothLenBySE($this->virJson["cont_start_dt"],$this->virJson["cont_end_dt"]);
        //contract_code
        $saveArr = $this->getMySaveArr();
        //pro_change
        switch ($this->getScenario()){
            case "new":
                $saveArr["create_staff"]=CGetName::getEmployeeIDByMy();
                $saveArr["lcu"]=$uid;
                $saveArr["mh_id"]=null;
                $connection->createCommand()->insert("sal_virtual_batch",$saveArr);
                $this->id = Yii::app()->db->getLastInsertID();
                $connection->createCommand()->update("sal_virtual_batch",array(
                    "pro_code"=>"BTH".(10000+$this->id)
                ),"id=:id",array(":id"=>$this->id));
                break;
            case "edit":
                $saveArr["luu"]=$uid;
                $connection->createCommand()->update("sal_virtual_batch",$saveArr,"id=:id",array(":id"=>$this->id));
                break;
            case "delete":
                $connection->createCommand()->delete("sal_virtual_batch","id=:id",array(":id"=>$this->id));
                $connection->createCommand()->delete("sal_virtual_batch_file","vir_batch_id=:vir_batch_id",array(":vir_batch_id"=>$this->id));
                $connection->createCommand()->delete("sal_contpro_virtual","vir_batch_id=:vir_batch_id",array(":vir_batch_id"=>$this->id));
        }
        if($this->getScenario()!="delete"){
            $this->addContractVir();//增加合约
        }
        $this->saveFile();//保存附件
        return true;
    }

    protected function getFilePath(){
        $path="CRM/virbatch_".(Yii::app()->params['envSuffix']==""?"prod":Yii::app()->params['envSuffix']);
        $path.="/".$this->id;
        return $path;
    }

    //保存附件
    protected function saveFile($group_id=0){
        $qiNiuFile = new QiNiuFile();
        $qiNiuFile->start();
        $path = $this->getFilePath();
        $uid = Yii::app()->user->id;
        switch ($this->getScenario()){
            case "new":
            case "edit":
                if(!empty($this->fileJson)){
                    foreach ($this->fileJson as $row){
                        if(!isset($row["uflag"])){
                            continue;
                        }
                        $saveList = array(
                            "vir_batch_id"=>$this->id,
                            "vir_id_text"=>$this->vir_id_text,
                            "file_name"=>$row["fileName"],
                        );
                        if(isset($row["file"])){
                            $file_name = hash_file('md5',$row["file"]["fileTmpName"]);
                            $file_name = $file_name.".".$row["file"]["fileExt"];
                            $saveList["phy_file_name"] = $file_name;//文件名称（系统名）
                            $saveList["phy_path_name"] = $path;//文件地址
                            $saveList["display_name"] = $row["file"]["fileName"];//文件名（上传名）
                            $saveList["file_type"] = $row["file"]["fileType"];
                            $saveList["group_id"] = $group_id;
                            $qiNiuFile->uploadFile($path."/".$file_name,$row["file"]["fileTmpName"]);
                            //move_uploaded_file($row["file"]["fileTmpName"],$path."/".$file_name);
                        }
                        switch ($row["uflag"]){
                            case "Y"://修改，新增
                                if(empty($row["id"])){
                                    $saveList["lcu"]=$uid;
                                    Yii::app()->db->createCommand()->insert("sal_virtual_batch_file",$saveList);
                                }else{
                                    $saveList["luu"]=$uid;
                                    Yii::app()->db->createCommand()->update("sal_virtual_batch_file",$saveList,"id=:id and vir_batch_id=:vir_batch_id",array(":id"=>$row["id"],":vir_batch_id"=>$this->id));
                                }
                                break;
                            case "D"://删除
                                Yii::app()->db->createCommand()->delete("sal_virtual_batch_file","id=:id and vir_batch_id=:vir_batch_id",array(":id"=>$row["id"],":vir_batch_id"=>$this->id));
                                break;
                        }
                    }
                }
                break;

            case "delete"://vir_batch_id
                Yii::app()->db->createCommand()->delete("sal_virtual_batch_file","vir_batch_id=:vir_batch_id",array(":vir_batch_id"=>$this->id));
                /*$dirPath = Yii::app()->params['docmanPath']."/../upload/".Yii::app()->params['systemId'];
                $dirPath.="/virbatch_".(Yii::app()->params['envSuffix']==""?"prod":Yii::app()->params['envSuffix']);
                $dirPath.="/".$this->id;
                $this->deleteDir($dirPath);
                */
                break;
        }
        $qiNiuFile->end();
    }

    protected function deleteDir($dirPath){
        if (!is_dir($dirPath)) {
            return;
        }
        $files = glob($dirPath . '/*');
        foreach ($files as $file) {
            is_dir($file) ? $this->deleteDir($file) : unlink($file);
        }
        rmdir($dirPath);
    }


    protected function getSaveVirExprData($vir_id,$newData){
        $virRow = Yii::app()->db->createCommand()->select("*")->from("sal_contract_virtual")
            ->where("id=:id",array(":id"=>$vir_id))->queryRow();
        if($virRow){
            $vir_id =$virRow["id"];
            $nowYearAmt = $this->pro_type=="A"?$newData["year_amt"]:$virRow["year_amt"];
            $this->total_amt+=$nowYearAmt;
            $pro_change = CGetName::computeProChangeAmt($this->pro_type,$virRow["year_amt"],$nowYearAmt);
            $this->pro_change +=$pro_change;
            $data = $virRow;
            unset($data["id"]);
            unset($data["lcu"]);
            unset($data["lcd"]);
            unset($data["luu"]);
            unset($data["lud"]);
            $dataEx = array(
                "pro_vir_type"=>2,
                "vir_batch_id"=>$this->id,
                "vir_id"=>$vir_id,
                "pro_type"=>$this->pro_type,
                "pro_num"=>CGetName::getProNumByVir($vir_id,$this->pro_type),
                "pro_date"=>$this->pro_date,
                "pro_remark"=>$this->pro_remark,
                "pro_status"=>$this->pro_status,
                "pro_change"=>$pro_change,
                "stop_month_amt"=>$virRow["month_amt"],
                "stop_year_amt"=>$virRow["year_amt"],
            );
            $dataEx = array_merge($dataEx,$newData);
            foreach ($dataEx as $key=>$item){
                $data[$key]=$item;
            }
            return $data;
        }else{
            echo "data error";
            die();
        }
    }

    protected function historyUpdateList(){
        return array("month_amt","year_amt","lbs_main","yewudalei","other_sales_id","other_yewudalei","sign_date","cont_start_dt","cont_end_dt","prioritize_service","service_timer",
            "seal_type_id","prioritize_seal","is_seal","is_renewal","con_v_type","pay_week","pay_type","deposit_need","deposit_amt","deposit_rmk","fee_type","pay_month","pay_start","settle_type",
            "bill_day","profit_int","receivable_day","bill_bool","amt_install","remark");
    }

    protected function getSaveVirData($vir_id){
        $data = array();
        switch ($this->pro_type){
            case "A":
                $list = $this->historyUpdateList();
                $numberList=array("lbs_main","yewudalei","other_sales_id","other_yewudalei","service_timer","seal_type_id","con_v_type","pay_week","pay_type","deposit_need","deposit_amt",
                    "fee_type","profit_int","pay_month","pay_start","settle_type","bill_day","receivable_day","month_amt","year_amt","amt_install"
                );
                foreach ($list as $key){
                    if (in_array($key,$numberList)){
                        $data[$key]=CGetName::getNumberNull($this->virJson[$key]);
                    }else{
                        $data[$key]=$this->virJson[$key];
                    }
                }
                $data["cont_month_len"] = CGetName::computeMothLenBySE($data["cont_start_dt"],$data["cont_end_dt"]);
                $serviceJson = $this->virJson["serviceJson"][$this->busine_id[0]];
                $data["service_fre_amt"]=CGetName::getNumberNull($serviceJson["service_fre_amt"]);
                $data["service_fre_sum"]=empty($serviceJson["service_fre_sum"])?0:$serviceJson["service_fre_sum"];
                $data["service_fre_type"]=empty($serviceJson["service_fre_type"])?0:$serviceJson["service_fre_type"];
                $data["service_fre_json"]=$serviceJson["service_fre_json"];
                $data["service_fre_text"]=$serviceJson["service_fre_text"];
                $data["service_sum"]=$data["service_fre_sum"];
                $data["call_fre_amt"]=$data["service_fre_type"]==3?$data["service_fre_amt"]:0;
                $data["invoice_amount"]=$data["service_fre_type"]==1?$data["month_amt"]:$data["year_amt"];
                $data["detail_json"]=is_array($serviceJson["items"])?json_encode($serviceJson["items"],JSON_UNESCAPED_UNICODE):$serviceJson["items"];
                break;
            case "S":
                $data["stop_set_id"]=$this->stop_set_id;
                $data["stop_date"]=$this->stop_date;
                $data["stop_month_amt"]=0;
                $data["stop_year_amt"]=0;
                $data["stop_sum_amt"]=$this->stop_sum_amt;
                $data["vir_status"]=40;
                break;
            case "T":
                $data["stop_set_id"]=$this->stop_set_id;
                $data["stop_date"]=$this->stop_date;
                $data["stop_month_amt"]=0;
                $data["stop_year_amt"]=0;
                $data["stop_sum_amt"]=$this->stop_sum_amt;
                $data["need_back"]=$this->need_back;
                $data["need_back_json"]=$this->need_back_json;
                $data["surplus_num"]=$this->surplus_json[$vir_id]["surplus_number"];
                $data["surplus_amt"]=$this->surplus_json[$vir_id]["surplus_money"];
                //$data["jq_sum"]=$this->jq_sum;
                //$data["jq_sum_back"]=$this->jq_sum_back;
                $data["vir_status"]=50;
                break;
            case "R":
                $data["vir_status"]=30;
                break;
        }
        return $data;
    }

    protected function addContractVir(){
        $uid = Yii::app()->user->id;
        $this->pro_change=0;
        if(!empty($this->virHeadRows)){//
            foreach ($this->virHeadRows as $virtualRow){
                $updateRow = Yii::app()->db->createCommand()->select("id")->from("sal_contpro_virtual")
                    ->where("pro_vir_type=2 and vir_batch_id=:vir_batch_id and vir_id=:vir_id",array(
                        ":vir_batch_id"=>$this->id,
                        ":vir_id"=>$virtualRow["id"],
                    ))->queryRow();
                $virSaveArrEx = $this->getSaveVirData($virtualRow["id"]);
                $virSaveArr = $this->getSaveVirExprData($virtualRow["id"],$virSaveArrEx);
                $virSaveArr["effect_date"] = $this->pro_date;
                if($updateRow){
                    $virtualId = $updateRow["id"];
                    $virSaveArr["luu"]=$uid;
                    Yii::app()->db->createCommand()->update("sal_contpro_virtual",$virSaveArr,"id=".$virtualId);
                }else{
                    $virSaveArr["lcu"]=$uid;
                    $virSaveArr["lcd"]=date("Y-m-d H:i:s");
                    Yii::app()->db->createCommand()->insert("sal_contpro_virtual",$virSaveArr);
                    $virtualId = Yii::app()->db->getLastInsertID();
                    Yii::app()->db->createCommand()->update("sal_contpro_virtual",array(
                        "pro_code"=>"VPR".(10000+$virtualId)
                    ),"id=".$virtualId);
                }
            }
        }
        Yii::app()->db->createCommand()->update("sal_virtual_batch",array("pro_change"=>$this->pro_change),"id=".$this->id);
    }

    //保存印章
    public function saveSeal($type='save'){
        $list = array("bool"=>true,"msg"=>"");//true:成功
        $uid = Yii::app()->user->id;
        $this->saveFile(100);
        if($type!="save"){
            Yii::app()->db->createCommand()->update("sal_virtual_batch",array(
                "pro_status"=>20,
                'luu'=>$uid
            ),"id=:id",array(":id"=>$this->id));
            Yii::app()->db->createCommand()->update("sal_virtual_batch_file",array(
                "group_id"=>1,
                'luu'=>$uid
            ),"vir_batch_id=:id and group_id=100",array(":id"=>$this->id));//保存的印章文件转生效中
            Yii::app()->db->createCommand()->insert("sal_contract_history",array(
                "table_id"=>$this->id,
                "table_type"=>6,
                "history_type"=>2,
                "history_html"=>"<span>已上传印章</span>",
                "lcu"=>$uid,
            ));
            //印章文件发送给派单系统
            $curlNotesByVirFile = new CurlNotesByVirFile();
            $curlNotesByVirFile->sendVirFileByBatchId($this->id);

            $noticeModel = new CNoticeFlowModel();
            $taskId = $this->getMHTaskID();
            $url="/openApi/runtime/task/v1/complete";
            $data = array(
                "account"=>CGetName::getEmployeeCodeByMy(),
                "actionName"=>"agree",
                "instId"=>$this->mh_id,
                "opinion"=>"CRM系统已上传印章",
                "taskId"=>$taskId,
                //"formKey"=>"LBSxshtsp",
            );
            $outData = $noticeModel->sendMHPostByUrlAndData($url,$data);
            if(!$outData["status"]){//系统自动同意
                $list["bool"] = false;
                $list["msg"]=isset($outData["outData"]["message"])?$outData["outData"]["message"]:$outData["message"];
            }
        }
        return $list;
    }

    //发送消息至门户网站
    protected function sendDataToMH(){
        $list = array("bool"=>true,"msg"=>"");//true:成功
        if($this->pro_status==1){//发送
            if(!empty($this->mh_id)){
                return $this->sendDataToMHByUpdate();
            }else{
                return $this->sendDataToMHByNew();
            }
        }
        return $list;
    }

    protected function sendDataToMHByUpdate(){
        $list = array("bool"=>true,"msg"=>"");//true:成功
        $uid = Yii::app()->user->id;
        $noticeModel = new CNoticeFlowModel();
        $url = "/openApi/runtime/instance/v1/setVariables?instId=".$this->mh_id;
        $data = $this->getMHData();
        $outData = $noticeModel->sendMHPostByUrlAndData($url,$data);
        if(!$outData["status"]){//发送修改数据
            $list["bool"] = false;
            $list["msg"]=isset($outData["outData"]["message"])?$outData["outData"]["message"]:$outData["message"];
        }else{
            $taskId = $this->getMHTaskID();
            $url = CGetName::getMHUrlPrx()."/matter/approvalForm?type=request&taskId={$taskId}&instId={$this->mh_id}&isGetApprovalBtn=true";
            $this->goMhWebUrl =CGetName::getMHWebUrlByUrl($url);
        }
        return $list;
    }

    protected function getMHData(){
        $lbsCityCode = array();
        $lbsMainCityCode="";
        $is_seal=isset($this->virJson['is_seal'])?$this->virJson['is_seal']:"";
        $fee_type=isset($this->virJson['fee_type'])?$this->virJson['fee_type']:0;
        $seal_type_id=isset($this->virJson['seal_type_id'])?$this->virJson['seal_type_id']:"";
        foreach ($this->virHeadRows as $row){
            $is_seal = empty($is_seal)?$row["is_seal"]:$is_seal;
            $seal_type_id = empty($seal_type_id)?$row["seal_type_id"]:$seal_type_id;
            $lbsMainCityCode = empty($lbsMainCityCode)?$row["city"]:$lbsMainCityCode;
            $sales_id = empty($sales_id)?$row["sales_id"]:$sales_id;
            if(!in_array($row["city"],$lbsCityCode)){
                $lbsCityCode[]=$row["city"];
            }
        }
        $list = array(
            "lbsMain"=>CGetName::getLbsMainStrByKeyAndStr($this->lbsMain,'mh_code'),//主体公司编码
            "lbsMainCityCode"=>$lbsMainCityCode,//主城市编码
            "lbsCityCode"=>empty($lbsCityCode)?"":implode(",",$lbsCityCode),//门店城市编码
            "lbsBizCatCode"=>CGetName::getYewudaleiStrByKey($this->yewudalei,'mh_code'),//业务大类编码
            "saleId"=>CGetName::getEmployeeStrByKey('bs_staff_id',$this->salesID),//销售人员北森id
            "isSeal"=>$is_seal,//是否用印
            "isPrepayment"=>$fee_type==1?"Y":"N",//是否预付款(Y:预付款)
            "sealCode"=>$is_seal=="Y"?CGetName::getSealCodeStrByKeyAndStr($seal_type_id,'mh_code'):"",//印章编码
            "customerName"=>$this->cust_name,
        );
        $pro_change=0;
        switch ($this->pro_type){
            case "A":
                if($this->virJson["lbs_main"]!=$this->lbsMain){
                    $list["changeType"]="entityChange";
                }else{
                    $list["changeType"]="svcChange";
                }
                $pro_change=floatval($this->pro_change);
                break;
            case "S":
                $list["changeType"]="suspend";
                break;
            case "T":
                $list["changeType"]="terminate";
                break;
            case "R":
                $list["changeType"]="resume";
                break;
        }
        $list["contractChangeAmt"]=$pro_change;
        $list["contractNowAmt"]=floatval($this->total_amt);
        $list["contractOldAmt"]=$list["contractNowAmt"]-$list["contractChangeAmt"];
        return $list;
    }

    protected function getMHTaskID(){
        $mhModel = new CMHCurlModel();
        $mhModel->printBool=true;
        $taskId = "";
        $outData = $mhModel->sendMHFlowByGet(array("instId"=>$this->mh_id));//
        if($outData["status"]){
            $mhRows = $outData["outData"]["value"]["rows"];
            if($mhRows){
                foreach ($mhRows as $row){
                    $taskId=$row["taskId"];
                    return $taskId;
                }
            }
        }
        return $taskId;
    }

    protected function sendDataToMHByNew(){
        $list = array("bool"=>true,"msg"=>"");//true:成功
        $uid = Yii::app()->user->id;
        $noticeModel = new CNoticeFlowModel();
        $dataEx = array(
            "vars"=>$this->getMHData()
        );
        $businesskey="virPro_".$this->id;
        $outData = $noticeModel->sendMHAuditByDataEx($businesskey,"LBShtbgsp",$dataEx);
        if(!$outData["status"]){
            $list["bool"] = false;
            $list["msg"]=isset($outData["outData"]["message"])?$outData["outData"]["message"]:$outData["message"];
        }else{
            $instId = isset($outData["outData"]["instId"])?$outData["outData"]["instId"]:null;
            $this->mh_id = $instId;
            $taskID = $this->getMHTaskID();
            Yii::app()->db->createCommand()->update("sal_virtual_batch",array(
                "mh_id"=>$instId,
            ),"id=:id",array(":id"=>$this->id));
            $url = CGetName::getMHUrlPrx()."/matter/approvalForm?type=request&taskId={$taskID}&instId={$this->mh_id}&isGetApprovalBtn=true";
            $this->goMhWebUrl =CGetName::getMHWebUrlByUrl($url);
        }
        return $list;
    }

	public function isOccupied($index) {
		$rtn = true;//默认不允许删除
		if($this->retrieveData($index)){
            $rtn = false;//允许删除
        }
		return $rtn;
	}

	public function isReadonly() {
		return $this->getScenario()=='view'||!in_array($this->pro_status,array(0,9));
	}

    public function resetFileToQiNiu(){
        //将旧文件全部发送到七牛空间
        $pathOld=Yii::app()->params['docmanPath'];
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_virtual_batch_file")
            ->where("phy_path_name like '{$pathOld}%'")->order("id asc")->queryAll();//
        echo "start:".count($rows)."<br/>";
        if($rows){
            $qiNiuFile = new QiNiuFile();
            $qiNiuFile->start();
            foreach ($rows as $row){
                $filePath = $row["phy_path_name"]."/".$row["phy_file_name"];
                if (file_exists($filePath)) {
                    $row["phy_path_name"] = str_replace($pathOld,"CRM",$row["phy_path_name"]);
                    $fileBody = file_get_contents($filePath);
                    $key = $row["phy_path_name"]."/".$row["phy_file_name"];
                    $bool = $qiNiuFile->uploadFileBody($key,$fileBody);
                    if($bool){
                        Yii::app()->db->createCommand()->update("sal_virtual_batch_file",array(
                            "phy_path_name"=>$row["phy_path_name"]
                        ),"id=".$row["id"]);
                    }
                }else{
                    var_dump($filePath);
                    echo "<br/>";
                }
            }
            $qiNiuFile->end();
        }
        echo "end!";
    }
}
