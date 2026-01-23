<?php

class ContHeadForm extends ContForm
{
    public $goMhWebUrl;//
    public $docMaxSize = 15728640;//1024*1024*15 = 15M
    protected $virtual=array();//虚拟合同
    public $lookFileRow=array();

    public $inVirList=array(0);
    public $inSSEList=array(0);
    //draft：草稿
    public function rulesEx(){
        $list = array();
        $list[]=array('clue_service_id','required');
        $list[]=array('clue_service_id','validateClueServiceID');
        $list[]=array('group_bool','computeGroupBool');
        $list[]=array('area_bool','computeAreaBool');
        $list[]=array('id','validateID');
        $list[]=array('clueServiceRow','validateClueServiceRow');
        $list[]=array('areaJson','validateAreaJson');
        $list[]=array('fileJson','validateFileJson');
        $list[]=array('serviceJson','validateServiceJson');
        $list[]=array('cont_status','validateStatus');
        $list[]=array('areaJson','validateAllJson','on'=>array('audit'));
        return $list;
    }

    public function validateAllJson($attribute, $param) {
    }

    public function validateStatus($attribute, $param) {
        if(!in_array($this->cont_status,array(0,9))){
            $this->addError($attribute, "该状态无法编辑（{$this->cont_status}）");
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

    public function validateAreaJson($attribute, $param) {
        if(!empty($this->areaJson)){
            $areaJson = array();
            foreach ($this->areaJson as $row){
                if($row["uflag"]!="D"){
                    $areaJson[]=$row;
                }
            }
            $this->areaJson = $areaJson;
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
                            $this->addError($attribute,'文件大小不能大于15M'.$fileSize);
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

    public function validateInvoice(){
        if(!empty($this->clueSSERow)){
            foreach ($this->clueSSERow as $row){
                if(empty($row["invoice_header"])){
                    $html=TbHtml::link($row["store_name"],'javascript:void(0);',array(
                        'data-load'=>Yii::app()->createUrl('clueStore/ajaxShow'),
                        'data-submit'=>Yii::app()->createUrl('clueStore/ajaxSave'),
                        'data-serialize'=>"ClueStoreForm[scenario]=edit&ClueStoreForm[id]=".$row["clue_store_id"],
                        'data-obj'=>"",
                        'class'=>'openDialogForm',
                    ));
                    $this->addError("id","门店（{$html}）未填写税号");
                    //$this->addError("id","门店（{$row["store_name"]}）未填写税号");
                }
            }
        }
        if($this->hasErrors()){
            return false;
        }else{
            return true;
        }
    }

    public function validateServiceJson($attribute, $param) {
        $this->total_amt=0;
        $this->total_sum=0;
        $this->store_sum=0;
        $serviceJson = json_decode($this->serviceJson,true);
        if(empty($serviceJson)||!is_array($serviceJson)){
            //$this->addError($attribute, "门店及服务项目的数据异常");
        }else{
            $type = $this->getScenario()=="audit"?"audit":"edit";
            $model = new VirtualHeadForm($type);
            $model->busine_id = $this->busine_id;
            foreach ($this->clueSSERow as $row){//将门店拆分，服务拆分在虚拟合同内
                $keyStr = "".$row["clue_store_id"];
                if(key_exists($keyStr,$serviceJson)){
                    $virtualTemp = array(
                        "cont_id"=>0,
                        "clue_id"=>$this->clue_id,
                        "clue_service_id"=>$this->clue_service_id,
                        "clue_store_id"=>$row["clue_store_id"],
                        "create_staff"=>$row["create_staff"],
                        "busine_id"=>$row["busine_id"],
                        "busine_id_text"=>$row["busine_id_text"],
                        "detail_json"=>$serviceJson[$keyStr]['detail'],
                        "store_amt"=>0,//门店总金额
                        "service_sum"=>0,//总次数
                        "list"=>array(),//门店内被拆分的服务
                    );
                    $model->service = $serviceJson[$keyStr]['detail'];
                    $storeList=$model->validateServiceAmount("service","");
                    $model->validateServices("service","");
                    if($model->hasErrors()){
                        $this->addErrors($model->getErrors());
                        return false;
                    }
                    $this->store_sum++;
                    $this->total_amt+=$storeList["total"];
                    $this->total_sum+=$model->service_fre_sum;
                    $virtualTemp["store_amt"]=$storeList["total"];
                    $virtualTemp["service_sum"]=$model->service_fre_sum;
                    $virtualTemp["list"]=$storeList["list"];
                    $virtualTemp["amt_install"]=$storeList["amt_install"];
                    $virtualTemp["remark"]=$storeList["remark"];
                    $this->virtual[]=$virtualTemp;//将门店拆分，服务拆分在虚拟合同内
                }
            }
        }
    }

    public function getFileJson(){
        $this->fileJson=array();
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_contract_file")
            ->where("cont_id=:id",array(":id"=>$this->id))->order("id asc")->queryAll();//
        if($rows){
            foreach ($rows as $row){
                $this->fileJson[]=array(
                    "id"=>$row["id"],
                    "contID"=>$this->id,
                    //"fileID"=>$row["phy_path_name"]."/".$row["phy_file_name"],
                    "fileID"=>$row["phy_file_name"],
                    "groupID"=>$row["group_id"],
                    "fileVal"=>"",
                    "fileName"=>$row["file_name"],
                    "tableName"=>"cont",
                    "uflag"=>"N",
                );
            }
        }
    }

    public function getSealFileJson(){
        $fileJson=array();
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_contract_file")
            ->where("cont_id=:id and group_id=100",array(":id"=>$this->id))->order("id asc")->queryAll();//
        if($rows){
            foreach ($rows as $row){
                $fileJson[]=array(
                    "id"=>$row["id"],
                    "contID"=>$this->id,
                    //"fileID"=>$row["phy_path_name"]."/".$row["phy_file_name"],
                    "fileID"=>$row["phy_file_name"],
                    "fileVal"=>"",
                    "fileName"=>$row["file_name"],
                    "tableName"=>"cont",
                    "uflag"=>"N",
                );
            }
        }
        return $fileJson;
    }

    public function getModelIDByFileID($fileID){
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_contract_file")
            ->where("id=:id",array(":id"=>$fileID))->queryRow();//
        if($row){
            $this->id=$row["cont_id"];
            $this->lookFileRow = $row;
        }else{
            $this->id=0;
        }
    }

    protected function getMySaveArr(){
        $this->sales_id = CGetName::getNumberNull($this->sales_id);
        $this->yewudalei = CGetName::getNumberNull($this->yewudalei);
        $this->other_sales_id = CGetName::getNumberNull($this->other_sales_id);
        $this->other_yewudalei = CGetName::getNumberNull($this->other_yewudalei);
        return array(
            "clue_id"=>$this->clue_id,
            "clue_type"=>$this->clue_type,
            "clue_service_id"=>$this->clue_service_id,
            "city"=>$this->city,
            "sales_id"=>$this->sales_id,
            "other_sales_id"=>$this->other_sales_id,
            "other_yewudalei"=>$this->other_yewudalei,
            "lbs_main"=>CGetName::getNumberNull($this->lbs_main),
            "predict_amt"=>$this->predict_amt,
            "total_amt"=>CGetName::getNumberNull($this->total_amt),
            "total_sum"=>CGetName::getNumberNull($this->total_sum),
            "con_v_type"=>CGetName::getNumberNull($this->con_v_type),
            "cont_type"=>CGetName::getNumberNull($this->cont_type),
            "cont_start_dt"=>empty($this->cont_start_dt)?null:General::toDate($this->cont_start_dt),
            "cont_end_dt"=>empty($this->cont_end_dt)?null:General::toDate($this->cont_end_dt),
            "cont_month_len"=>$this->cont_month_len,
            "sign_type"=>CGetName::getNumberNull($this->sign_type),
            "sign_date"=>empty($this->sign_date)?null:General::toDate($this->sign_date),
            "is_seal"=>$this->is_seal,
            "is_renewal"=>$this->is_renewal,
            "seal_type_id"=>CGetName::getNumberNull($this->seal_type_id),
            "prioritize_service"=>$this->prioritize_service,
            "prioritize_seal"=>$this->prioritize_seal,
            "group_bool"=>$this->group_bool,
            "service_timer"=>CGetName::getNumberNull($this->service_timer),
            "pay_week"=>CGetName::getNumberNull($this->pay_week),
            "pay_type"=>CGetName::getNumberNull($this->pay_type),
            "pay_month"=>CGetName::getNumberNull($this->pay_month),
            "pay_start"=>CGetName::getNumberNull($this->pay_start),
            "deposit_need"=>CGetName::getNumberNull($this->deposit_need),
            "deposit_amt"=>CGetName::getNumberNull($this->deposit_amt),
            "deposit_rmk"=>$this->deposit_rmk,
            "fee_type"=>CGetName::getNumberNull($this->fee_type),
            "profit_int"=>CGetName::getNumberNull($this->profit_int),
            "settle_type"=>CGetName::getNumberNull($this->settle_type),
            "bill_day"=>CGetName::getNumberNull($this->bill_day),
            "bill_bool"=>$this->bill_bool,
            "receivable_day"=>CGetName::getNumberNull($this->receivable_day),
            "area_bool"=>$this->area_bool,
            "busine_id_text"=>$this->busine_id_text,
            "busine_id"=>is_array($this->busine_id)?implode(",",$this->busine_id):$this->busine_id,
            "area_json"=>is_array($this->areaJson)?json_encode($this->areaJson,JSON_UNESCAPED_UNICODE):$this->areaJson,
            "mh_remark"=>null,
            "cont_status"=>$this->cont_status,
            "yewudalei"=>$this->yewudalei,
            "store_sum"=>CGetName::getNumberNull($this->store_sum),
        );
    }

	protected function save(&$connection)
	{
        $uid = Yii::app()->user->id;
        $this->cont_month_len = CGetName::computeMothLenBySE($this->cont_start_dt,$this->cont_end_dt);
        //contract_code
        $saveArr = $this->getMySaveArr();
	    switch ($this->getScenario()){
            case "new":
                $saveArr["lcu"]=$uid;
                $connection->createCommand()->insert("sal_contract",$saveArr);
                $this->id = Yii::app()->db->getLastInsertID();
                $this->computeContCode();
                $connection->createCommand()->update("sal_contract",array(
                    "cont_code"=>$this->cont_code,
                ),"id=:id",array(":id"=>$this->id));
                $connection->createCommand()->insert("sal_contract_history",array(
                    "table_id"=>$this->id,
                    "table_type"=>5,
                    "history_type"=>1,
                    "history_html"=>"<span>新增</span>",
                    "lcu"=>$uid,
                ));
                break;
            case "edit":
                $saveArr["luu"]=$uid;
                $connection->createCommand()->update("sal_contract",$saveArr,"id=:id",array(":id"=>$this->id));
                break;
            case "delete":
                $vir_id = CGetName::getVirSqlIDByContID($this->id);
                $connection->createCommand()->delete("sal_contract","id=:id",array(":id"=>$this->id));
                $connection->createCommand()->delete("sal_contract_sse","cont_id=:cont_id",array(":cont_id"=>$this->id));
                $connection->createCommand()->delete("sal_contract_virtual","cont_id=:cont_id",array(":cont_id"=>$this->id));
                $connection->createCommand()->delete("sal_contract_history","table_type=5 and table_id=:table_id",array(":table_id"=>$this->id));
                $connection->createCommand()->delete("sal_contract_vir_staff","vir_id in ({$vir_id})");
                $connection->createCommand()->delete("sal_contract_vir_week","vir_id in ({$vir_id})");
                $connection->createCommand()->delete("sal_contract_vir_info","virtual_id in ({$vir_id})");
        }
        $this->addContractSSE();//增加合约关联的门店
        $this->saveFile();//保存附件
        $this->resetClueService();//
		return true;
	}

	protected function resetClueService(){
        if($this->cont_status==1){
            Yii::app()->db->createCommand()->update("sal_clue_service",array(
                "service_status"=>7
            ),"id=:id",array(":id"=>$this->clue_service_id));
            Yii::app()->db->createCommand()->update("sal_clue_flow",array(
                "update_bool"=>3
            ),"clue_service_id=:id",array(":id"=>$this->clue_service_id));
            Yii::app()->db->createCommand()->update("sal_clue_sre_soe",array(
                "update_bool"=>3
            ),"clue_service_id=:id",array(":id"=>$this->clue_service_id));
        }
    }

	protected function getFilePath(){
        $path="CRM/cont_".(Yii::app()->params['envSuffix']==""?"prod":Yii::app()->params['envSuffix']);
        $path.="/".$this->id;
        return $path;
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

    //保存印章
    public function saveSeal($type='save'){
        $list = array("bool"=>true,"msg"=>"");//true:成功
        $uid = Yii::app()->user->id;
	    $this->saveFile(100);
        if($type!="save"){
            Yii::app()->db->createCommand()->update("sal_contract",array(
                "cont_status"=>20,
                'luu'=>$uid
            ),"id=:id",array(":id"=>$this->id));
            Yii::app()->db->createCommand()->update("sal_clue_service",array(
                "service_status"=>20,
                'luu'=>$uid
            ),"id=:id",array(":id"=>$this->clue_service_id));
            Yii::app()->db->createCommand()->update("sal_contract_file",array(
                "group_id"=>1,
                'luu'=>$uid
            ),"cont_id=:id and group_id=100",array(":id"=>$this->id));//保存的印章文件转生效中
            Yii::app()->db->createCommand()->insert("sal_contract_history",array(
                "table_id"=>$this->id,
                "table_type"=>5,
                "history_type"=>2,
                "history_html"=>"<span>已上传印章</span>",
                "lcu"=>$uid,
            ));
            //印章文件发送给派单系统
            $curlNotesByVirFile = new CurlNotesByVirFile();
            $curlNotesByVirFile->sendVirFileByContID($this->id);

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
                        $saveList = array(
                            "clue_id"=>$this->clue_id,
                            "clue_service_id"=>$this->clue_service_id,
                            "cont_id"=>$this->id,
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
                                    Yii::app()->db->createCommand()->insert("sal_contract_file",$saveList);
                                }else{
                                    $saveList["luu"]=$uid;
                                    Yii::app()->db->createCommand()->update("sal_contract_file",$saveList,"id=:id and cont_id=:cont_id",array(":id"=>$row["id"],":cont_id"=>$this->id));
                                }
                                break;
                            case "D"://删除
                                Yii::app()->db->createCommand()->delete("sal_contract_file","id=:id and cont_id=:cont_id",array(":id"=>$row["id"],":cont_id"=>$this->id));
                                break;
                        }
                    }
                }
                break;

            case "delete":
                Yii::app()->db->createCommand()->delete("sal_contract_file","cont_id=:cont_id",array(":cont_id"=>$this->id));
                /*$dirPath = Yii::app()->params['docmanPath']."/../upload/".Yii::app()->params['systemId'];
                $dirPath.="/cont_".(Yii::app()->params['envSuffix']==""?"prod":Yii::app()->params['envSuffix']);
                $dirPath.="/".$this->id;
                $this->deleteDir($dirPath);
                */
                break;
        }
        $qiNiuFile->end();
    }

    //增加合约关联的门店
    protected function addContractSSE(){
        $uid = Yii::app()->user->id;
        switch ($this->getScenario()){
            case "new":
            case "edit":
                foreach ($this->virtual as $row){
                    $sseRow = Yii::app()->db->createCommand()->select("id")->from("sal_contract_sse")
                        ->where("cont_id=:cont_id and clue_store_id=:clue_store_id",array(
                            ":cont_id"=>$this->id,
                            ":clue_store_id"=>$row["clue_store_id"],
                        ))->queryRow();
                    if(!$sseRow){//新增关联门店
                        Yii::app()->db->createCommand()->insert("sal_contract_sse",array(
                            "cont_id"=>$this->id,
                            "clue_id"=>$this->clue_id,
                            "clue_service_id"=>$this->clue_service_id,
                            "clue_store_id"=>$row["clue_store_id"],
                            "create_staff"=>$row["create_staff"],
                            "busine_id"=>$row["busine_id"],
                            "busine_id_text"=>$row["busine_id_text"],
                            "store_amt"=>$row["store_amt"],
                            "detail_json"=>is_array($row["detail_json"])?json_encode($row["detail_json"],JSON_UNESCAPED_UNICODE):$row["detail_json"],
                            "service_sum"=>$row["service_sum"],//服务总次数
                            "lcu"=>$uid,
                        ));
                        $sseId = Yii::app()->db->getLastInsertID();
                    }else{
                        $sseId = $sseRow["id"];
                        Yii::app()->db->createCommand()->update("sal_contract_sse",array(
                            "create_staff"=>$row["create_staff"],
                            "busine_id"=>$row["busine_id"],
                            "busine_id_text"=>$row["busine_id_text"],
                            "store_amt"=>$row["store_amt"],
                            "detail_json"=>is_array($row["detail_json"])?json_encode($row["detail_json"],JSON_UNESCAPED_UNICODE):$row["detail_json"],
                            "service_sum"=>$row["service_sum"],//服务总次数
                            "luu"=>$uid,
                        ),"id=".$sseId);
                    }
                    $this->inSSEList[]=$sseId;
                    $this->addVirtual($row,$sseId);
                }
                break;
            default:
                return false;
        }
        $inSSEList = implode(",",$this->inSSEList);
        Yii::app()->db->createCommand()->delete("sal_contract_sse","cont_id=:cont_id and id not in ({$inSSEList})",array(
            ":cont_id"=>$this->id,
        ));//删除多余的关联门店
        $inVirList = implode(",",$this->inVirList);
        Yii::app()->db->createCommand()->delete("sal_contract_virtual","cont_id=:cont_id and id not in ({$inVirList})",array(
            ":cont_id"=>$this->id,
        ));//删除多余的关联门店
    }

    protected function getSaveVirData($row,$virtualRow){
        $month_amt = CGetName::getNumberNull($virtualRow["month_amt"]);
        $year_amt = CGetName::getNumberNull($virtualRow["year_amt"]);
        $amt_install = isset($virtualRow["amt_install"])?$virtualRow["amt_install"]:null;
        $saveData= array(
            "sign_type"=>CGetName::getNumberNull($this->sign_type),
            "cont_start_dt"=>empty($this->cont_start_dt)?null:General::toDate($this->cont_start_dt),
            "cont_end_dt"=>empty($this->cont_end_dt)?null:General::toDate($this->cont_end_dt),
            "cont_month_len"=>$this->cont_month_len,
            "create_staff"=>$row["create_staff"],
            "sales_id"=>$this->clue_type==1?$this->sales_id:$row["create_staff"],
            "other_sales_id"=>$this->other_sales_id,
            "other_yewudalei"=>$this->other_yewudalei,
            "month_amt"=>$month_amt,
            "year_amt"=>$year_amt,
            "invoice_amount"=>$virtualRow["service_fre_type"]==1?$month_amt:$year_amt,
            "service_fre_amt"=>CGetName::getNumberNull($virtualRow["service_fre_amt"]),
            "service_fre_sum"=>empty($virtualRow["service_fre_sum"])?0:$virtualRow["service_fre_sum"],
            "service_fre_type"=>empty($virtualRow["service_fre_type"])?0:$virtualRow["service_fre_type"],
            "service_fre_json"=>$virtualRow["service_fre_json"],
            "service_fre_text"=>$virtualRow["service_fre_text"],
            "lbs_main"=>intval($this->lbs_main),
            "sign_date"=>empty($this->sign_date)?null:General::toDate($this->sign_date),
            "prioritize_service"=>$this->prioritize_service,
            "prioritize_seal"=>$this->prioritize_seal,
            "is_seal"=>$this->is_seal,
            "is_renewal"=>$this->is_renewal,
            "seal_type_id"=>CGetName::getNumberNull($this->seal_type_id),
            "con_v_type"=>CGetName::getNumberNull($this->con_v_type),
            "service_timer"=>CGetName::getNumberNull($this->service_timer),
            "pay_week"=>CGetName::getNumberNull($this->pay_week),
            "pay_type"=>CGetName::getNumberNull($this->pay_type),
            "pay_month"=>CGetName::getNumberNull($this->pay_month),
            "pay_start"=>CGetName::getNumberNull($this->pay_start),
            "deposit_need"=>CGetName::getNumberNull($this->deposit_need),
            "deposit_amt"=>CGetName::getNumberNull($this->deposit_amt),
            "deposit_rmk"=>$this->deposit_rmk,
            "fee_type"=>CGetName::getNumberNull($this->fee_type),
            "profit_int"=>CGetName::getNumberNull($this->profit_int),
            "settle_type"=>CGetName::getNumberNull($this->settle_type),
            "bill_day"=>CGetName::getNumberNull($this->bill_day),
            "bill_bool"=>$this->bill_bool,
            "yewudalei"=>$this->yewudalei,
            "receivable_day"=>CGetName::getNumberNull($this->receivable_day),
            "remark"=>isset($virtualRow["remark"])?$virtualRow["remark"]:null,
            "amt_install"=>$amt_install,
            "need_install"=>empty($amt_install)?"N":"Y",
            "detail_json"=>is_array($virtualRow["items"])?json_encode($virtualRow["items"],JSON_UNESCAPED_UNICODE):$virtualRow["items"],
        );
        $saveData["service_sum"]=$saveData["service_fre_sum"];
        $saveData["call_fre_amt"]=$saveData["service_fre_type"]==3?$saveData["service_fre_amt"]:0;
        return $saveData;
    }

    protected function addVirtual($row,$sseId){
        $uid = Yii::app()->user->id;
        if(!empty($row["list"])){//sal_contract_virtual
            foreach ($row["list"] as $busine_id=>$virtualRow){
                $updateRow = Yii::app()->db->createCommand()->select("id")->from("sal_contract_virtual")
                    ->where("cont_id=:cont_id and clue_store_id=:clue_store_id and busine_id=:busine_id",array(
                        ":cont_id"=>$this->id,
                        ":clue_store_id"=>$row["clue_store_id"],
                        ":busine_id"=>$busine_id,
                    ))->queryRow();
                $virSaveArr = $this->getSaveVirData($row,$virtualRow);
                $virSaveArr["vir_status"]=$this->cont_status;
                if($updateRow){
                    $virtualId = $updateRow["id"];
                    $virSaveArr["luu"]=$uid;
                    Yii::app()->db->createCommand()->update("sal_contract_virtual",$virSaveArr,"id=".$virtualId);
                }else{
                    // 不显式加锁：依赖数据库唯一索引拦截重复，冲突则重试取号
                    $virCode = '';
                    $storeRow = Yii::app()->db->createCommand()->select("*")->from("sal_clue_store")
                        ->where("id=:id",array(":id"=>$row["clue_store_id"]))->queryRow();
                    $service_type = Yii::app()->db->createCommand()->select("service_type")->from("sal_service_type")
                        ->where("id_char=:id_char",array(":id_char"=>$busine_id))->queryRow();
                    $virSaveExpr=array(
                        "cont_id"=>$this->id,
                        "sse_id"=>$sseId,
                        "busine_id"=>$busine_id,
                        "clue_id"=>$this->clue_id,
                        "clue_type"=>$this->clue_type,
                        "clue_service_id"=>$this->clue_service_id,
                        "clue_store_id"=>$row["clue_store_id"],
                        "city"=>$storeRow["city"],
                        "office_id"=>$storeRow["office_id"],
                        "service_type"=>$service_type?$service_type["service_type"]:0,
                        "busine_id_text"=>$virtualRow["name"],
                        "lcu"=>$uid,
                    );
                    $virSaveArrBase = array_merge($virSaveArr,$virSaveExpr);
                    $virtualId = 0;
                    for ($try=0; $try<5; $try++) {
                        $virCode = $this->computeVirCode($this->id, $try+1);
                        $virSaveArr = $virSaveArrBase;
                        $virSaveArr["vir_code"] = $virCode;
                        try {
                            Yii::app()->db->createCommand()->insert("sal_contract_virtual",$virSaveArr);
                            $virtualId = Yii::app()->db->getLastInsertID();
                            break;
                        } catch (CDbException $e) {
                            // 需要配合数据库唯一索引(cont_id, vir_code)；冲突则重试
                            if (strpos($e->getMessage(), 'Duplicate') === false && strpos($e->getMessage(), '23000') === false) {
                                throw $e;
                            }
                        }
                    }
                    if (empty($virtualId)) {
                        throw new Exception("生成虚拟合约编号失败：请稍后重试");
                    }
                }
                $this->inVirList[]=$virtualId;
                CGetName::resetVirStaffAndWeek($virtualId);
                $this->addVirtualInfo($virtualRow["items"],$virtualId);
            }
        }
    }

    protected function addVirtualInfo($rows,$virtualId){
        $uid = Yii::app()->user->id;
        Yii::app()->db->createCommand()->delete("sal_contract_vir_info","virtual_id=".$virtualId);//全部清空
        if(!empty($rows)){
            $charToIDList=CGetName::getServiceInfoListByChar($rows);
            foreach ($rows as $field_id=>$field_value){
                Yii::app()->db->createCommand()->insert("sal_contract_vir_info",array(
                    "virtual_id"=>$virtualId,
                    "field_id"=>$field_id,
                    "service_type_id"=>isset($charToIDList[$field_id])?$charToIDList[$field_id]:null,
                    "field_value"=>$field_value,
                    "lcu"=>$uid,
                ));
            }
        }
    }

    //发送消息至门户网站
    protected function sendDataToMH(){
        $list = array("bool"=>true,"msg"=>"");//true:成功
        if($this->cont_status==1){//发送
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
            $historyList=array("table_type"=>5,"table_id"=>$this->id,"lcu"=>$uid,"history_type"=>30,"history_html"=>"<span>重新发起</span>");
            Yii::app()->db->createCommand()->insert("sal_contract_history",$historyList);
        }
        return $list;
    }

    protected function getMHData(){
        $lbsCityCode = CGetName::getLbsCityCodeByContID($this->id);
        return array(
            "lbsMain"=>CGetName::getLbsMainStrByKeyAndStr($this->lbs_main,'mh_code'),//主体公司编码
            "lbsMainCityCode"=>$this->city,//主城市编码
            "lbsCityCode"=>$lbsCityCode,//门店城市编码
            "lbsBizCatCode"=>CGetName::getYewudaleiStrByKey($this->yewudalei,'mh_code'),//业务大类编码
            "saleId"=>CGetName::getEmployeeStrByKey('bs_staff_id',$this->sales_id),//销售人员北森id
            "totalAmt"=>floatval($this->total_amt),
            "contractType"=>CGetName::getContTypeStrByKey($this->con_v_type,'mh_code'),//合同类型
            "isSeal"=>$this->is_seal,//是否用印
            "isPrepayment"=>$this->fee_type==1?"Y":"N",//是否预付款(Y:预付款)
            "sealCode"=>$this->is_seal=="Y"?CGetName::getSealCodeStrByKeyAndStr($this->seal_type_id,'mh_code'):"",//印章编码
            "signType"=>CGetName::getMHSignTypeBySignType($this->sign_type),
            "isPriorityArranged"=>$this->prioritize_service,
            "customerName"=>$this->clueHeadRow["cust_name"],
        );
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
        $businesskey="cont_".$this->id;
        $outData = $noticeModel->sendMHAuditByDataEx($businesskey,"LBSxshtsp",$dataEx);
        if(!$outData["status"]){
            $list["bool"] = false;
            $list["msg"]=isset($outData["outData"]["message"])?$outData["outData"]["message"]:$outData["message"];
        }else{
            $instId = isset($outData["outData"]["instId"])?$outData["outData"]["instId"]:null;
            $this->mh_id = $instId;
            $taskID = $this->getMHTaskID();
            Yii::app()->db->createCommand()->update("sal_contract",array(
                "mh_id"=>$instId,
            ),"id=:id",array(":id"=>$this->id));
            /*
            $historyList=array("table_type"=>5,"table_id"=>$this->id,"lcu"=>$uid,"history_type"=>30,"history_html"=>"<span>发起审批</span>");
            $historyList["expr_data"]=$instId;
            Yii::app()->db->createCommand()->insert("sal_contract_history",$historyList);
            */
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
		return $this->getScenario()=='view'||!in_array($this->cont_status,array(0,9));
	}

    public function resetFileToQiNiu(){
        //将旧文件全部发送到七牛空间
        $pathOld=Yii::app()->params['docmanPath'];
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_contract_file")
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
                        Yii::app()->db->createCommand()->update("sal_contract_file",array(
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
