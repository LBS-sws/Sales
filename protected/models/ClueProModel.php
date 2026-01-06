<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2025/5/30 0030
 * Time: 10:37
 */
class ClueProModel
{

    public $historyArr=array();

    public static $noteID="UserTask_043do24";//起始节点id
    public static $data=array(
        "instId"=>array("requite"=>true,"function"=>"validateInstID"),//门户id
        "candidate"=>array("requite"=>false,"function"=>"validateCandidate"),//审核人
        "Option"=>array("requite"=>false,"function"=>"validateOption"),//审核备注
        "contractStatus"=>array("requite"=>false),//
        "eventType"=>array("requite"=>true,"function"=>"validateStatusType"),//审核状态
        //"timestamp"=>array("requite"=>true,"function"=>"validateTimestamp"),//审核时间
    );

    public function validateOption(&$data,$keyStr){//审核备注
        $option = key_exists($keyStr,$data)?$data[$keyStr]:null;
        $data["remark"]=$option;
    }

    public function validateCandidate(&$data,$keyStr){//审核人
        $candidate = key_exists($keyStr,$data)?$data[$keyStr]:array();
        $luu="admin";
        if(!empty($candidate)&&is_array($candidate)){
            $candidate = current($candidate);
            if (isset($candidate["id"])){//北森id
                $suffix = Yii::app()->params['envSuffix'];
                $row = Yii::app()->db->createCommand()->select("a.user_id")
                    ->from("hr{$suffix}.hr_binding a")
                    ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
                    ->where("b.bs_staff_id=:id",array(":id"=>$candidate["id"]))->queryRow();
                if($row){
                    $luu=$row["user_id"];
                }
            }
        }
        $data["username"]=$luu;
    }

    public function validateStatusType(&$data,$keyStr){//审核状态
        $suffix = Yii::app()->params['envSuffix'];
        $statusType = key_exists($keyStr,$data)?$data[$keyStr]:0;
        $this->historyArr = array("table_type"=>6,"table_id"=>$data["id"],"history_type"=>5,"lcu"=>$data["username"],"history_html"=>array());
        $this->historyArr["history_html"][]="<span>门户网站</span>";
        $nowContStatus = $data["contProRow"]['pro_status'];
        $contractStatus = isset($data["contractStatus"])?$data["contractStatus"]:0;
        switch ($statusType){
            case "endProcess";//终止
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>终止</span>";
                $data["pro_status"] = 9;
                break;
            case "startEvent";//启动流程
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>启动流程</span>";
                $data["pro_status"] = 1;
                break;
            case "taskCreate";//合同生效或上传印章
                if($contractStatus==1){
                    if($nowContStatus>=21||$nowContStatus==10){
                        return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                    }
                    $row = Yii::app()->db->createCommand()->select("id")
                        ->from("sales{$suffix}.sal_contract_history")
                        ->where("table_type=6 and table_id=:id and history_type=10",array(":id"=>$data["id"]))->queryRow();
                    if($row){
                        return array("bool"=>false,"error"=>"该合约已生效过，无法继续生效");
                    }
                    $this->historyArr["history_type"]=10;
                    $this->historyArr["history_html"][]="<span>合同开始生效</span>";
                    $data["pro_status"] = 10;
                }elseif ($contractStatus==2){
                    if($nowContStatus>20){
                        return array("bool"=>false,"error"=>"该合约已上传盖章文件({$nowContStatus})");
                    }
                    $this->historyArr["history_html"][]="<span>需要上传盖章文件</span>";
                    $data["pro_status"] = 19;
                }elseif($contractStatus=="start"){
                    if($nowContStatus>=10){
                        return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                    }
                    $this->historyArr["history_html"][]="<span>开始</span>";
                    $data["pro_status"] = 0;
                }else{
                    return array("bool"=>false,"error"=>"taskCreate节点异常({$contractStatus})");
                }
                break;
            case "taskComplete";//任务结束时
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                }elseif ($contractStatus=="end"){
                    $this->historyArr["history_html"][]="<span>启动流程</span>";
                    $data["pro_status"] = 1;
                }else{
                    return array("bool"=>false,"error"=>"contractStatus异常({$contractStatus})");
                }
                break;
            case "taskRemove";//流程删除时
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>流程删除</span>";
                $data["pro_status"] = 8;
                break;
            case "endEvent";//流程结束时
                if($nowContStatus<10){
                    return array("bool"=>false,"error"=>"该合约状态异常，无法结束({$nowContStatus})");
                }
                $row = Yii::app()->db->createCommand()->select("id")
                    ->from("sales{$suffix}.sal_contract_history")
                    ->where("table_type=6 and table_id=:id and history_type=10",array(":id"=>$data["id"]))->queryRow();
                if(!$row){
                    return array("bool"=>false,"error"=>"该合约未生效，无法结束流程");
                }
                $this->historyArr["history_html"][]="<span>流程结束</span>";
                $data["pro_status"] = 30;
                break;
            default:
                return array("bool"=>false,"error"=>"状态异常：".$statusType);
        }
        if(!empty($data["remark"])){
            $this->historyArr["history_html"][]="<span>{$data["remark"]}</span>";
        }
        $this->historyArr["history_html"]=implode("<br/>",$this->historyArr["history_html"]);
        return array("bool"=>true,"error"=>"");
    }

    public function validateInstID(&$data,$keyStr){//审核id
        $id = key_exists($keyStr,$data)?$data[$keyStr]:0;
        $id = "".$id;
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("*")
            ->from("sales{$suffix}.sal_contpro")->where("mh_id=:id",array(":id"=>$id))->queryRow();

        if(!$row){
            $businessKeyList = key_exists("businesskey",$data)?explode("_",$data["businesskey"]):array();
            if(count($businessKeyList)==2){
                $row = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contpro")
                    ->where("mh_id is null and id=:id",array(":id"=>$businessKeyList[1]))->queryRow();
                if($row){
                    return array("bool"=>false,"error"=>"无效数据");
                }
            }
        }

        if($row){
            if($row["pro_status"]!=30){
                $data["id"] = $row["id"];
                $data["contProRow"] = $row;
                return array("bool"=>true);
            }else{
                return array("bool"=>false,"error"=>"合同状态异常({$row["pro_status"]})。");
            }
        }else{
            return array("bool"=>false,"error"=>"合同ID不存在。{$id}");
        }
    }

    //验证数据
    public function validateRow(&$data){
        foreach (self::$data as $keyStr=>$item){
            $requite = key_exists("requite",$item)?$item["requite"]:false;
            $maxLen = key_exists("maxLen",$item)?$item["maxLen"]:0;
            $fun = key_exists("function",$item)?$item["function"]:"";
            $keyStr = key_exists("keyStr",$item)?$item["keyStr"]:$keyStr;
            if($requite&&(!key_exists($keyStr,$data)||$data[$keyStr]===""||$data[$keyStr]===null)){
                return array("bool"=>false,"error"=>$keyStr."不能为空");
            }
            if($maxLen>0&&key_exists($keyStr,$data)&&mb_strlen($data[$keyStr],'UTF-8')>$maxLen){
                $data[$keyStr] = mb_substr($data[$keyStr],0,$maxLen,'UTF-8');
            }
            if(!empty($fun)){//函数验证及自动完成
                $result = $this->$fun($data,$keyStr);
                if($result["bool"]===false){ //验证失败不继续验证
                    return $result;
                }
            }
        }
        return array("bool"=>true,"saveData"=>$data);
    }

    public function syncChangeOne($row) {
        $suffix = Yii::app()->params['envSuffix'];
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try {
            $result = $this->validateRow($row);
            if($result["bool"]){
                $saveData = $result["saveData"];
                $msgList = $this->saveTableForSaveData($connection,$saveData);
                $transaction->commit();
                return $msgList;
            }else{
                $transaction->commit();//失败也需要结束事务
                return array('code'=>$result["error"]=="无效数据"?200:400,'msg'=>$result["error"]);
            }
        }catch(Exception $e) {
            $transaction->rollback();
            return array('code'=>400,'msg'=>$e->getMessage());
        }
    }

    //保存的数据
    protected function saveTableForSaveData($db,&$saveData){
        $returnList=array('code'=>200,'msg'=>"成功");
        $suffix = Yii::app()->params['envSuffix'];
        $mhRemark=null;
        if (isset($saveData["remark"])){
            $mhRemark = $saveData["remark"];
        }
        $uid = $saveData["username"];
        switch ($saveData["pro_status"]){
            case 0;//允许修改
                $db->createCommand()->update("sales{$suffix}.sal_contpro",array(
                    "pro_status"=>0,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                $this->historyArr["table_type"]=5;
                $this->historyArr["table_id"]=$saveData["contProRow"]["cont_id"];
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 1;//不允许修改
                $db->createCommand()->update("sales{$suffix}.sal_contpro",array(
                    "pro_status"=>1,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                $this->historyArr["table_type"]=5;
                $this->historyArr["table_id"]=$saveData["contProRow"]["cont_id"];
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 8;//删除
            case 9;//终止
                $db->createCommand()->update("sales{$suffix}.sal_contpro",array(
                    "pro_status"=>9,
                    "mh_id"=>null,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                $this->historyArr["table_type"]=5;
                $this->historyArr["table_id"]=$saveData["contProRow"]["cont_id"];
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 10;//合同已生效
                $db->createCommand()->update("sales{$suffix}.sal_contpro",array(
                    "pro_status"=>10,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                $db->createCommand()->update("sales{$suffix}.sal_contpro_virtual",array(
                    "pro_status"=>10,
                    "luu"=>$uid
                ),"pro_id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                $this->historyArr["table_type"]=5;
                $this->historyArr["table_id"]=$saveData["contProRow"]["cont_id"];
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                $this->copyContractProForVir($saveData);//合同正式生效允许续约变更等操作
                $this->sendContractVirForU($saveData);//虚拟合约发送给派单系统
                break;
            case 19;//待印章
                $db->createCommand()->update("sales{$suffix}.sal_contpro",array(
                    "pro_status"=>19,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                $this->historyArr["table_type"]=5;
                $this->historyArr["table_id"]=$saveData["contProRow"]["cont_id"];
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 30;//报价通过
                $db->createCommand()->update("sales{$suffix}.sal_contpro",array(
                    "pro_status"=>30,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                $db->createCommand()->update("sales{$suffix}.sal_contpro_virtual",array(
                    "pro_status"=>30,
                    "luu"=>$uid
                ),"pro_id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                $this->historyArr["table_type"]=5;
                $this->historyArr["table_id"]=$saveData["contProRow"]["cont_id"];
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            default:
                $returnList=array('code'=>400,'msg'=>"保存数据异常");
        }
        return $returnList;
    }

    //虚拟合约发送给派单系统
    protected function sendContractVirForU($saveData){
        //发送客户信息（group_bool=="Y"）
        $this->sendClient($saveData);
        //发送门店信息
        $this->sendStoreyByClient($saveData);
        //发送合约信息
        $this->sendStoreyByVir($saveData);
        //发送合约附件
        $this->sendVirFileByVir($saveData);
    }

    protected function sendVirFileByVir($saveData){
        $uVirFileModel = new CurlNotesByVirFile();
        $uVirFileModel->sendAllVirByProID($saveData["id"]);
    }

    protected function sendStoreyByVir($saveData){
        $uVirModel = new CurlNotesByVirPro();
		$uVirModel->pro_type=$saveData["contProRow"]["pro_type"];
        $uVirModel->update_effective_date = $saveData["contProRow"]["pro_date"];
        $uVirModel->sendAllVirByProID($saveData["id"]);
    }

    protected function sendStoreyByClient($saveData){//clue_store_id
        $suffix = Yii::app()->params['envSuffix'];
        $pro_id = $saveData["contProRow"]["id"];
        $storeRows = Yii::app()->db->createCommand()->select("a.clue_store_id,b.u_id")
            ->from("sales{$suffix}.sal_contpro_virtual a")
            ->leftJoin("sales{$suffix}.sal_clue_store b","a.clue_store_id=b.id")
            ->where("a.pro_id=:pro_id",array(":pro_id"=>$pro_id))->group("a.clue_store_id,b.u_id")->queryAll();//
        if($storeRows){
            $putStore=array();
            $updateStore=array();
            foreach($storeRows as $storeRow){
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_store",array(
                    "store_status"=>ClueVirProModel::getStoreStatusByStoreID($storeRow["clue_store_id"]),
                ),"id=:id",array(":id"=>$storeRow["clue_store_id"]));
                if(empty($storeRow['u_id'])){
                    $putStore[]=$storeRow["clue_store_id"];
                }else{
                    $updateStore[]=$storeRow["clue_store_id"];
                }
            }
            if(!empty($putStore)){
                $uStoreModel = new CurlNotesByStore();
                $uStoreModel->sendDataSetByAddStore();
                $uStoreModel->putAllStoreByStoreIDs($putStore);
                $uStoreModel->setOutContentByData();
                if($uStoreModel->status_type=="P"){
                    $uStoreModel->setMinUrl($uStoreModel->min_url);
                    $uStoreModel->sendUByStoreData();
                }
                $uStoreModel->saveCurlToApi();
                $uStoreModel->putPersonDataByNewStoreIDs($putStore);//发送门店联系人
            }
            if(!empty($updateStore)){
                $uStoreModel = new CurlNotesByStore();
                $uStoreModel->sendDataSetByUpdateStore();
                $uStoreModel->putAllStoreByStoreIDs($updateStore);
                $uStoreModel->setOutContentByData();
                if($uStoreModel->status_type=="P"){
                    $uStoreModel->setMinUrl($uStoreModel->min_url);
                    $uStoreModel->sendUByStoreData();
                }
                $uStoreModel->saveCurlToApi();
            }
        }
    }

    protected function sendClient($saveData){
        $clue_id = $saveData["contProRow"]["clue_id"];
        $suffix = Yii::app()->params['envSuffix'];
        $updateArr=array("clue_status"=>ClueVirProModel::getClientStatusByClueID($clue_id),"table_type"=>2);
        if($saveData["contProRow"]["group_bool"]=="Y"){
            $updateArr["group_bool"] = "Y";
        }
        Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue",$updateArr,"id=:id",array(":id"=>$clue_id));

        $clientRow = Yii::app()->db->createCommand()->select("id,u_id,group_bool")->from("sales{$suffix}.sal_clue")
            ->where("id=:id",array(":id"=>$clue_id))->queryRow();//如果集团没有同步
        $putClient=array();
        $updateClient=array();
        if($clientRow){
            if(empty($clientRow['u_id'])&&$clientRow["group_bool"]=="Y"){
                $putClient[]=array('clue_id'=>$clientRow["id"]);
            }
            if(!empty($clientRow['u_id'])){
                $updateClient[]=array('clue_id'=>$clientRow["id"]);
            }
        }
        if(!empty($putClient)){//增加
            $uClientModel = new CurlNotesByClient();
            $uClientModel->sendDataSetByAddClient();
            $uClientModel->putDataByClientID($putClient);
            $uClientModel->setOutContentByData();
            if($uClientModel->status_type=="P"){
                $uClientModel->setMinUrl($uClientModel->min_url);
                $uClientModel->sendUByClientData();
            }
            $uClientModel->saveCurlToApi();
        }
        if(!empty($updateClient)){//修改
            $uClientModel = new CurlNotesByClient();
            $uClientModel->sendDataSetByUpdateClient();
            $uClientModel->putDataByClientID($updateClient);
            $uClientModel->setOutContentByData();
            if($uClientModel->status_type=="P"){
                $uClientModel->setMinUrl($uClientModel->min_url);
                $uClientModel->sendUByClientData();
            }
            $uClientModel->saveCurlToApi();
        }
    }

    //合同正式生效允许续约变更等操作
    protected function copyContractProForVir($saveData){
        $nowDate = date_format(date_create(),"Y/m/d");
        $suffix = Yii::app()->params['envSuffix'];
        $proRow = $saveData["contProRow"];
        $contRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract")
            ->where("id=:id",array(":id"=>$proRow["cont_id"]))->queryRow();
        $contUpdateList=array("store_sum"=>empty($contRow["store_sum"])?0:$contRow["store_sum"],"total_amt"=>empty($contRow["total_amt"])?0:$contRow["total_amt"]);
        if($contRow){
            $updateRow = array();
            $notSaveKey = array("id");
            foreach ($contRow as $keyStr=>$value){
                if (!in_array($keyStr,$notSaveKey)&&key_exists($keyStr,$proRow)){
                    $updateRow[$keyStr]=$proRow[$keyStr];
                }
            }
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract",$updateRow,"id=".$proRow["cont_id"]);
        }
        $fileRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contpro_file")
            ->where("pro_id=:id",array(":id"=>$proRow["id"]))->queryAll();
        if($fileRows){
            foreach ($fileRows as $fileRow){
                $fileTemp = $fileRow;
                unset($fileTemp["id"]);
                unset($fileTemp["pro_id"]);
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_file",$fileTemp);
            }
        }
        $sseRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contpro_sse")
            ->where("pro_id=:id",array(":id"=>$proRow["id"]))->queryAll();
        $oldSeeIDToNowSseID=array();
        $inSeeID=array(0);
        if($sseRows){
            foreach ($sseRows as $sseRow){
                $oldSeeIDToNowSseID[$sseRow["id"]]=$sseRow["id"];
                $sseTemp = $sseRow;
                unset($sseTemp["id"]);
                unset($sseTemp["pro_id"]);
                $sseBool = Yii::app()->db->createCommand()->select("id")->from("sales{$suffix}.sal_contract_sse")
                    ->where("cont_id=:cont_id and clue_store_id=:clue_store_id",array(
                        ":cont_id"=>$sseTemp["cont_id"],
                        ":clue_store_id"=>$sseTemp["clue_store_id"],
                    ))->queryRow();
                if($sseBool){
                    $oldSeeIDToNowSseID[$sseRow["id"]]=$sseBool["id"];
                    Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_sse",$sseTemp,"id=".$sseBool["id"]);
                }else{
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_sse",$sseTemp);
                    $oldSeeIDToNowSseID[$sseRow["id"]]=Yii::app()->db->getLastInsertID();
                }
                $inSeeID[]=$oldSeeIDToNowSseID[$sseRow["id"]];
            }
        }
        if($proRow["pro_type"]!="NA"){//不是增加门店，则需要删除多余的关联
            $inSeeID = implode(",",$inSeeID);
            Yii::app()->db->createCommand()->delete("sales{$suffix}.sal_contract_sse","cont_id='{$proRow["cont_id"]}' and id not in ({$inSeeID})");
        }

        $virRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contpro_virtual")
            ->where("pro_id=:id",array(":id"=>$proRow["id"]))->queryAll();
        if($virRows) {
            $oldVirRows=array();//旧合约信息
            foreach ($virRows as $virRow) {
                $oldVir = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
                    ->where("id>0")->order("id asc")->queryRow();//获取虚拟表的结构
                $updateRow = array();
                $notSaveKey = array("id");
                foreach ($oldVir as $keyStr=>$value){
                    if (!in_array($keyStr,$notSaveKey)&&key_exists($keyStr,$virRow)){
                        $updateRow[$keyStr]=$virRow[$keyStr];
                    }
                }
                $updateRow["sse_id"]=$oldSeeIDToNowSseID[$virRow["sse_id"]];
                if(empty($virRow["vir_id"])){
                    // 新增虚拟合约：按照原逻辑，vir_code 如果存在会被包含
                    $updateRow["vir_status"]=30;
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_virtual",$updateRow);
                    $vir_id = Yii::app()->db->getLastInsertID();
                    $virRow['vir_id']=$vir_id;
                    Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contpro_virtual",array(
                        "vir_id"=>$vir_id,
                        "vir_status"=>30,
                    ),"id=".$virRow["id"]);
                    Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_store",array(
                        "store_status"=>2
                    ),"id=".$virRow["clue_store_id"]);
                    $contUpdateList["store_sum"]++;
                    $contUpdateList["total_amt"]+=$updateRow["year_amt"];
                }else{
                    // 更新已存在的虚拟合约（续约）：绝对不能修改 vir_code
                    $oldVirRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
                        ->where("id=:id",array(":id"=>$virRow["vir_id"]))->order("id asc")->queryRow();//
                    if($oldVirRow){
                        $oldVirRows[$virRow["vir_id"]]=$oldVirRow;
                        // 续约操作（pro_type="C"），需要重新计算 month_amt
                        // 或者如果 updateRow 中没有 month_amt，也需要计算
                        if($proRow["pro_type"]=="C" || empty($updateRow["month_amt"]) || $updateRow["month_amt"] === null){
                            $month_amt = null;
                            // 优先从 service_fre_json 中解析 fre_month
                            $service_fre_json = !empty($virRow["service_fre_json"]) ? $virRow["service_fre_json"] : (!empty($updateRow["service_fre_json"]) ? $updateRow["service_fre_json"] : null);
                            if(!empty($service_fre_json)){
                                $freJson = json_decode($service_fre_json, true);
                                if(isset($freJson["fre_month"]) && !empty($freJson["fre_month"])){
                                    $month_amt = floatval($freJson["fre_month"]);
                                }
                            }
                            // 如果 service_fre_json 中没有 fre_month，且 service_fre_type=1（固定频次），尝试从 year_amt 计算
                            $service_fre_type = isset($virRow["service_fre_type"]) ? intval($virRow["service_fre_type"]) : (isset($updateRow["service_fre_type"]) ? intval($updateRow["service_fre_type"]) : 0);
                            if($month_amt === null && $service_fre_type == 1){
                                // 固定频次：month_amt = year_amt / 12
                                if(!empty($virRow["year_amt"])){
                                    $month_amt = round(floatval($virRow["year_amt"]) / 12, 2);
                                } elseif(!empty($updateRow["year_amt"])){
                                    $month_amt = round(floatval($updateRow["year_amt"]) / 12, 2);
                                }
                            }
                            // 如果计算出了 month_amt，则添加到更新数据中
                            if($month_amt !== null){
                                $updateRow["month_amt"] = $month_amt;
                            }
                        }
                        // 续约时绝对不能修改 vir_code
                        unset($updateRow["vir_code"]);
                        Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_virtual",$updateRow,"id=".$virRow["vir_id"]);
                    }
                }
                $this->saveVirInfo($virRow);
				CGetName::resetVirStaffAndWeek($virRow['vir_id']);
            }

            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract",$contUpdateList,"id=".$proRow["cont_id"]);
            $this->proTypeChange($proRow,$contRow,$oldVirRows);
        }
    }

    protected function proTypeChange($proRow,$contOldRow,$oldVirRows){
        $suffix = Yii::app()->params['envSuffix'];
        switch ($proRow["pro_type"]){
            case "NA"://增加门店
                $contRow = Yii::app()->db->createCommand()->select("a.*,b.ka_ava_id")
                    ->from("sales{$suffix}.sal_contract a")
                    ->leftJoin("sales{$suffix}.sal_clue_service b","a.clue_service_id=b.id")
                    ->where("a.id=:id",array(":id"=>$proRow["cont_id"]))->queryRow();
                if($contRow&&$contRow["clue_type"]==2){//KA
                    $this->serviceKAQian($contRow,$contOldRow);
                }
                if($contRow&&$contRow["clue_type"]==1){//地推
                    $this->serviceVisitQian($proRow,$contRow,$oldVirRows);
                }
                break;
            case "A"://合同内容调整
                if($proRow['pro_change']>0){//金额增加
                    $contRow = Yii::app()->db->createCommand()->select("a.*,b.ka_ava_id")
                        ->from("sales{$suffix}.sal_contract a")
                        ->leftJoin("sales{$suffix}.sal_clue_service b","a.clue_service_id=b.id")
                        ->where("a.id=:id",array(":id"=>$proRow["cont_id"]))->queryRow();
                    if($contRow&&$contRow["clue_type"]==1){//地推
                        $proRow["cont_start_dt"]=date("Y-m-d");//强制设置为当前时间
                        $this->serviceVisitQian($proRow,$contRow,$oldVirRows);
                    }
                }
                break;
        }
    }

    protected function serviceKAQian($contRow,$contOldRow){
        $suffix = Yii::app()->params['envSuffix'];
        $avaRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_ka_bot_ava")
            ->where("id=:id",array(":id"=>$contRow["ka_ava_id"]))->queryRow();
        if($avaRow){//ka项目有商机记录
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_ka_bot_ava",array(
                "ava_num"=>empty($contRow["store_sum"])?null:floatval($contRow["store_sum"]),
                "ava_fact_amt"=>empty($contRow["total_amt"])?null:floatval($contRow["total_amt"]),
            ),"id=".$contRow["ka_ava_id"]);
            ClueContModel::computeKABotStoreAndAmt($avaRow["bot_id"]);
        }else if(!empty($contRow["ka_id"])){//ka项目有客户
            $ava_num = empty($contRow["store_sum"])?0:floatval($contRow["store_sum"]);
            $ava_num-= empty($contOldRow["store_sum"])?0:floatval($contOldRow["store_sum"]);
            $ava_fact_amt = empty($contRow["total_amt"])?0:floatval($contRow["total_amt"]);
            $ava_fact_amt-= empty($contOldRow["total_amt"])?0:floatval($contOldRow["total_amt"]);
            Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_ka_bot_ava",array(
                "ava_num"=>$ava_num,
                "ava_fact_amt"=>$ava_fact_amt,
                "bot_id"=>$contRow["ka_id"],
                "ava_date"=>date("Y-m-01"),
                "ava_note"=>"CRM自动生成",
                "ava_rate"=>90,
            ));
            $avaId = Yii::app()->db->getLastInsertID();
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_service",array(
                "ka_ava_id"=>$avaId,
            ),"id=".$contRow["clue_service_id"]);
        }
    }

    protected function serviceVisitQian($proRow,$contRow,$oldVirRows=array()){
        $suffix = Yii::app()->params['envSuffix'];
        $serviceRow = Yii::app()->db->createCommand()->select("b.service_type,b.city,a.*")
            ->from("sales{$suffix}.sal_clue_service a")
            ->leftJoin("sales{$suffix}.sal_clue b","a.clue_id=b.id")
            ->where("a.id=:id",array(":id"=>$proRow["clue_service_id"]))->queryRow();
        $sseRows = Yii::app()->db->createCommand()->select("b.*,a.*")
            ->from("sales{$suffix}.sal_contpro_sse a")
            ->leftJoin("sales{$suffix}.sal_clue_store b","a.clue_store_id=b.id")
            ->where("a.pro_id=:id",array(":id"=>$proRow["id"]))->queryAll();
        if($serviceRow&&$sseRows){
            $date = date("Y-m-d",strtotime($proRow["cont_start_dt"]));
            $buChar=array();//月金额，年金额主键
            foreach ($sseRows as $sseRow){
                $visit_info_text = array();
                $virRows = Yii::app()->db->createCommand()->select("id,month_amt,year_amt,busine_id,busine_id_text")
                    ->from("sales{$suffix}.sal_contract_virtual")
                    ->where("cont_id=:cont_id and clue_store_id=:clue_store_id and FIND_IN_SET(busine_id,'{$sseRow["busine_id"]}')",array(
                        ":cont_id"=>$proRow["cont_id"],
                        ":clue_store_id"=>$sseRow["clue_store_id"],
                    ))->queryAll();
                $total_amt=0;
                $updateList=array();
                if($virRows){
                    foreach ($virRows as $virRow){
                        $virRow['month_amt'] = floatval($virRow['month_amt']);
                        $virRow['year_amt'] = floatval($virRow['year_amt']);
                        if (isset($oldVirRows[$virRow["id"]])){
                            $virRow['month_amt']-= floatval($oldVirRows[$virRow["id"]]['month_amt']);
                            $virRow['year_amt']-= floatval($oldVirRows[$virRow["id"]]['year_amt']);
                            $updateList[]=array(
                                "virtual_id"=>$virRow["id"],
                                "field_id"=>"svc_".$virRow["busine_id"],//月金额
                                "field_value"=>$virRow['month_amt'],//月金额
                            );
                            if(!isset($buChar[$virRow["busine_id"]])){
                                $charRow = Yii::app()->db->createCommand()->select("a.id_char")
                                    ->from("sales{$suffix}.sal_service_type_info a")
                                    ->leftJoin("sales{$suffix}.sal_service_type b","a.type_id=b.id")
                                    ->where("a.input_type='yearAmount' and b.id_char=:id_char",array(
                                        ":id_char"=>$virRow["busine_id"],
                                    ))->queryRow();
                                if($charRow){
                                    $buChar[$virRow["busine_id"]]="svc_".$charRow["id_char"];
                                }else{
                                    $buChar[$virRow["busine_id"]]="";
                                }
                            }
                            if(!empty($buChar[$virRow["busine_id"]])){
                                $updateList[]=array(
                                    "virtual_id"=>$virRow["id"],
                                    "field_id"=>$buChar[$virRow["busine_id"]],//年金额
                                    "field_value"=>$virRow['year_amt'],//年金额
                                );
                            }
                        }
                        $total_amt+= floatval($virRow['year_amt']);
                        $visit_info_text[]="{$virRow['month_amt']}({$virRow["busine_id_text"]})";
                    }
                }
                $username = CGetName::getUserNameByEmployeeID($contRow['sales_id']);
                if(CGetName::getUserNameHasAccess($username,"HK01")){//有销售拜访的读写权限
                    Yii::app()->db->createCommand()->insert("sal_visit",array(
                        "username"=>$username,
                        "visit_dt"=>$date,
                        "visit_type"=>$serviceRow["visit_type"],
                        "visit_obj"=>'["10"]',
                        "visit_obj_name"=>"签单",
                        "quotation"=>"是",
                        "visit_info_text"=>empty($visit_info_text)?null:implode("/",$visit_info_text),
                        "service_type"=>$serviceRow["service_type"],//json
                        "cust_type"=>CGetName::getVisitCustTypeIDByCustClassID($sseRow["cust_class"]),
                        "cust_name"=>$sseRow["store_name"],
                        "cust_person"=>$sseRow["cust_person"],
                        "cust_tel"=>$sseRow["cust_tel"],
                        "district"=>CGetName::getVisitDistrictIDByNalID($sseRow["district"],$sseRow["city"]),
                        //"street"=>$sseRow["street"],
                        "remarks"=>"CRM自动生成",
                        "sign_odds"=>100,
                        "city"=>$sseRow["city"],
                        "total_amt"=>$total_amt,
                        "busine_id"=>$sseRow["busine_id"],
                        "busine_id_text"=>$sseRow["busine_id_text"],
                        "lcu"=>$username,
                        "status"=>'N',
                        "status_dt"=>null,
                    ));
                    $visitId = Yii::app()->db->getLastInsertID();
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_flow",array(
                        "clue_id"=>$contRow["clue_id"],
                        "clue_type"=>$contRow["clue_type"],
                        "clue_service_id"=>$contRow["clue_service_id"],
                        "visit_date"=>$date,
                        "create_staff"=>$contRow['sales_id'],
                        "store_num"=>$contRow["store_sum"],
                        "update_bool"=>4,
                        "rpt_bool"=>empty($serviceRow["rpt_amt"])?0:1,
                        "lbs_main"=>$serviceRow["lbs_main"],
                        "predict_date"=>$serviceRow["predict_date"],
                        "predict_amt"=>$contRow["predict_amt"],
                        "sign_odds"=>100,
                        "visit_text"=>"CRM自动生成",
                        "visit_obj"=>'10',
                        "visit_obj_text"=>"签单",
                        "table_id"=>$visitId,
                        "lcu"=>$username,
                    ));
                    $vir_ids = Yii::app()->db->createCommand()->select("GROUP_CONCAT(id) as ids")
                        ->from("sales{$suffix}.sal_contract_virtual")
                        ->where("cont_id=:cont_id and clue_store_id=:clue_store_id",array(
                            ":cont_id"=>$proRow["cont_id"],
                            ":clue_store_id"=>$sseRow["clue_store_id"],
                        ))->queryRow();
                    $vir_ids = $vir_ids?$vir_ids["ids"]:"0";
                    $insertSQL = "INSERT INTO sal_visit_info (visit_id,field_id,field_value,lcu,luu,lcd,lud)
                                SELECT '{$visitId}',field_id,field_value,lcu,luu,lcd,lud 
                                FROM sal_contract_vir_info WHERE virtual_id in ({$vir_ids})";
                    Yii::app()->db->createCommand($insertSQL)->execute();
                    if(!empty($updateList)){
                        foreach ($updateList as $update){
                            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_visit_info",array(
                                "field_value"=>$update["field_value"]
                            ),"visit_id=:visit_id and field_id=:field_id",array(":visit_id"=>$visitId,":field_id"=>$update["field_id"]));
                        }
                    }

                    $model= new VisitForm('edit');//首页需要提示大神签单
                    $model->addNotificationByQian($visitId);
                }
            }
        }
    }

    protected function saveVirInfo($virRow){
        $rows = json_decode($virRow["detail_json"],true);
        $virtualId = $virRow["vir_id"];
        $uid = "admin";
        Yii::app()->db->createCommand()->delete("sal_contract_vir_info","virtual_id=".$virtualId);//全部清空
        if(!empty($rows)){
            $charToIDList=CGetName::getServiceInfoListByChar($rows);
            foreach ($rows as $field_id=>$field_value){
                Yii::app()->db->createCommand()->insert("sal_contract_vir_info",array(
                    "virtual_id"=>$virtualId,
                    "service_type_id"=>isset($charToIDList[$field_id])?$charToIDList[$field_id]:null,
                    "field_id"=>$field_id,
                    "field_value"=>$field_value,
                    "lcu"=>$uid,
                ));
            }
        }
    }

}
