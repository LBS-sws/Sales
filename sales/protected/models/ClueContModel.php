<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2025/5/30 0030
 * Time: 10:37
 */
class ClueContModel
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
        $this->historyArr = array("table_type"=>5,"table_id"=>$data["id"],"history_type"=>5,"lcu"=>$data["username"],"history_html"=>array());
        $this->historyArr["history_html"][]="<span>门户网站</span>";
        $nowContStatus = $data["clueContRow"]['cont_status'];
        $contractStatus = isset($data["contractStatus"])?$data["contractStatus"]:0;
        switch ($statusType){
            /*
            case "agree";//同意
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>同意</span>";
                $data["cont_status"] = 1;
                break;
            case "reject";//驳回
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>驳回</span>";
                if($nodeId==self::$noteID){
                    $data["cont_status"] = 0;
                }else{
                    $data["cont_status"] = 1;
                }
                break;
            case "taskRevoke";//任务撤回
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>任务撤回</span>";
                if($nodeId==self::$noteID){
                    $data["cont_status"] = 0;
                }else{
                    $data["cont_status"] = 1;
                }
                break;
            case "revoke";//撤回
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>撤回</span>";
                if($nodeId==self::$noteID){
                    $data["cont_status"] = 0;
                }else{
                    $data["cont_status"] = 1;
                }
                break;
            */
            case "startEvent";//启动流程
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>启动流程</span>";
                $data["cont_status"] = 1;
                break;
            case "endProcess";//终止
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>终止</span>";
                $data["cont_status"] = 9;
                break;
            case "taskCreate";//合同生效或上传印章或启动流程
                if($contractStatus==1){
                    if($nowContStatus>=21||$nowContStatus==10){
                        return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                    }
                    $row = Yii::app()->db->createCommand()->select("id")
                        ->from("sales{$suffix}.sal_contract_history")
                        ->where("table_type=5 and table_id=:id and history_type=10",array(":id"=>$data["id"]))->queryRow();
                    if($row){
                        return array("bool"=>false,"error"=>"该合约已生效过，无法继续生效");
                    }
                    $this->historyArr["history_type"]=10;
                    $this->historyArr["history_html"][]="<span>合同开始生效</span>";
                    $data["cont_status"] = 10;
                }elseif ($contractStatus==2){
                    if($nowContStatus>=20){
                        return array("bool"=>false,"error"=>"该合约已上传盖章文件({$nowContStatus})");
                    }
                    $this->historyArr["history_html"][]="<span>需要上传盖章文件</span>";
                    $data["cont_status"] = 19;
                }elseif($contractStatus=="start"){
                    if($nowContStatus>=10){
                        return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                    }
                    $this->historyArr["history_html"][]="<span>开始</span>";
                    $data["cont_status"] = 0;
                }else{
                    return array("bool"=>false,"error"=>"taskCreate节点异常({$contractStatus})");
                }
                break;
            case "taskComplete";//任务结束时
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                }elseif ($contractStatus=="end"){
                    $this->historyArr["history_html"][]="<span>启动流程</span>";
                    $data["cont_status"] = 1;
                }else{
                    return array("bool"=>false,"error"=>"contractStatus异常({$contractStatus})");
                }
                break;
            case "taskRemove";//流程删除时
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>流程删除</span>";
                $data["cont_status"] = 8;
                break;
            case "endEvent";//流程结束时
                if($nowContStatus<10){
                    return array("bool"=>false,"error"=>"该合约状态异常，无法结束({$nowContStatus})");
                }
                $row = Yii::app()->db->createCommand()->select("id")
                    ->from("sales{$suffix}.sal_contract_history")
                    ->where("table_type=5 and table_id=:id and history_type=10",array(":id"=>$data["id"]))->queryRow();
                if(!$row){
                    return array("bool"=>false,"error"=>"该合约未生效，无法结束流程");
                }
                $this->historyArr["history_html"][]="<span>流程结束</span>";
                $data["cont_status"] = 30;
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
            ->from("sales{$suffix}.sal_contract")->where("mh_id=:id",array(":id"=>$id))->queryRow();
        if(!$row){
            $businessKeyList = key_exists("businesskey",$data)?explode("_",$data["businesskey"]):array();
            if(count($businessKeyList)==2){
                $row = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract")
                    ->where("mh_id is null and id=:id",array(":id"=>$businessKeyList[1]))->queryRow();
                if($row){
                    return array("bool"=>false,"error"=>"无效数据");
                }
            }
        }
        if($row){
            if($row["cont_status"]!=30){
                $data["id"] = $row["id"];
                $data["clueContRow"] = $row;
                return array("bool"=>true);
            }else{
                return array("bool"=>false,"error"=>"合同状态异常({$row["cont_status"]})。");
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
        $clue_service_id = $saveData["clueContRow"]["clue_service_id"];
        $mhRemark=null;
        if (isset($saveData["remark"])){
            $mhRemark = $saveData["remark"];
        }
        $uid = $saveData["username"];
        switch ($saveData["cont_status"]){
            case 0;//允许修改
                $db->createCommand()->update("sales{$suffix}.sal_contract",array(
                    "cont_status"=>0,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_service",array(
                    "service_status"=>6
                ),"id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_flow",array(
                    "update_bool"=>1
                ),"clue_service_id=:id and update_bool=3",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 1;//不允许修改
                $db->createCommand()->update("sales{$suffix}.sal_contract",array(
                    "cont_status"=>1,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_service",array(
                    "service_status"=>7
                ),"id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 8;//删除
            case 9;//终止
                $db->createCommand()->update("sales{$suffix}.sal_contract",array(
                    "cont_status"=>9,
                    "mh_remark"=>$mhRemark,
                    "mh_id"=>null,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_service",array(
                    "service_status"=>8
                ),"id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_flow",array(
                    "update_bool"=>1
                ),"clue_service_id=:id and update_bool=3",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 10;//合同已生效
                $db->createCommand()->update("sales{$suffix}.sal_contract",array(
                    "cont_status"=>10,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                $db->createCommand()->update("sales{$suffix}.sal_contract_virtual",array(
                    "vir_status"=>10,
                    "luu"=>$uid
                ),"cont_id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_service",array(
                    "service_status"=>10,
                    "visit_obj"=>"10",
                    "visit_obj_text"=>"签单",
                    "total_num"=>$saveData["clueContRow"]["store_sum"],
                    "total_amt"=>$saveData["clueContRow"]["total_amt"],
                ),"id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue",array(
                    "clue_status"=>30,
                    "group_bool"=>$saveData["clueContRow"]["group_bool"],
                    "table_type"=>2,
                ),"id=:id",array(":id"=>$saveData["clueContRow"]["clue_id"]));
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                $this->addHistoryBy10($saveData);//生成历史记录
                $this->copyContractProForVir($saveData);//合同正式生效允许续约变更等操作
                $this->sendContractVirForU($saveData);//虚拟合约发送给派单系统
                break;
            case 19;//待印章
                $db->createCommand()->update("sales{$suffix}.sal_contract",array(
                    "cont_status"=>19,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_service",array(
                    "service_status"=>19
                ),"id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 30;//报价通过
                $db->createCommand()->update("sales{$suffix}.sal_contract",array(
                    "cont_status"=>30,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                $db->createCommand()->update("sales{$suffix}.sal_contract_virtual",array(
                    "vir_status"=>30,
                    "luu"=>$uid
                ),"cont_id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_service",array(
                    "service_status"=>30
                ),"id=:id",array(":id"=>$clue_service_id));
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
    }

    protected function sendStoreyByVir($saveData){
        $uVirModel = new CurlNotesByVir();
        $uVirModel->update_effective_date = $saveData["clueContRow"]["effect_date"];
        $uVirModel->sendAllVirByContID($saveData["id"]);
    }

    protected function sendStoreyByClient($saveData){//clue_store_id
        $suffix = Yii::app()->params['envSuffix'];
        $cont_id = $saveData["clueContRow"]["id"];
        $storeRows = Yii::app()->db->createCommand()->select("a.clue_store_id,b.u_id")
            ->from("sales{$suffix}.sal_contract_sse a")
            ->leftJoin("sales{$suffix}.sal_clue_store b","a.clue_store_id=b.id")
            ->where("a.cont_id=:cont_id",array(":cont_id"=>$cont_id))->group("a.clue_store_id,b.u_id")->queryAll();//
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
        $clue_id = $saveData["clueContRow"]["clue_id"];
        $suffix = Yii::app()->params['envSuffix'];
        $updateArr=array("clue_status"=>ClueVirProModel::getClientStatusByClueID($clue_id),"table_type"=>2);
        if($saveData["clueContRow"]["group_bool"]=="Y"){
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

    //增加KA签单
    protected function serviceKAQian($clue_service_id,$saveData,$serviceRow){
        $suffix = Yii::app()->params['envSuffix'];
        $kaRow = Yii::app()->db->createCommand()->select("id")->from("sales{$suffix}.sal_ka_bot")
            ->where("id=:id",array(":id"=>$serviceRow["ka_id"]))->queryRow();
        if($kaRow){
            $link_id = Yii::app()->db->createCommand()->select("id")->from("sales{$suffix}.sal_ka_link")
                ->order("rate_num desc")->queryRow();
            $sign_month = intval($saveData["clueContRow"]["cont_month_len"]/12);
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_ka_bot",array(
                "sign_date"=>$saveData["clueContRow"]["cont_start_dt"],
                "sign_end_date"=>$saveData["clueContRow"]["cont_end_dt"],
                "sign_month"=>$sign_month,
                "link_id"=>$link_id?$link_id["id"]:0,
                "sign_odds"=>100,
            ),"id=:id",array(":id"=>$kaRow["id"]));
            Yii::app()->db->createCommand()->insert("sal_ka_bot_ava",array(
                "bot_id"=>$kaRow["id"],
                "ava_date"=>date("Y-m-01",strtotime($saveData["clueContRow"]["cont_start_dt"])),
                "ava_fact_amt"=>$saveData["clueContRow"]["total_amt"],
                "ava_amt"=>$saveData["clueContRow"]["total_amt"],
                "ava_num"=>$saveData["clueContRow"]["store_sum"],
                "ava_note"=>"CRM自动生成",
                "ava_rate"=>90,
            ));
            $ka_ava_id = Yii::app()->db->getLastInsertID();
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_service",array(
                "ka_ava_id"=>$ka_ava_id
            ),"id=".$clue_service_id);
            $this->computeKABotStoreAndAmt($kaRow["id"]);
        }
    }

    protected function serviceVisitQian($saveData,$serviceRow){
        //未开发
        $suffix = Yii::app()->params['envSuffix'];
        $sseRows = Yii::app()->db->createCommand()->select("b.*,a.*")
            ->from("sales{$suffix}.sal_contract_sse a")
            ->leftJoin("sales{$suffix}.sal_clue_store b","a.clue_store_id=b.id")
            ->where("a.cont_id=:id",array(":id"=>$saveData["clueContRow"]["id"]))->queryAll();
        if($sseRows){
            $date = date("Y-m-d",strtotime($saveData["clueContRow"]["cont_start_dt"]));
            foreach ($sseRows as $sseRow){
                $visit_info_text = array();
                $virRows = Yii::app()->db->createCommand()->select("month_amt,busine_id_text")
                    ->from("sales{$suffix}.sal_contract_virtual")
                    ->where("cont_id=:cont_id and clue_store_id=:clue_store_id and FIND_IN_SET(busine_id,'{$sseRow["busine_id"]}')",array(
                        ":cont_id"=>$saveData["clueContRow"]["id"],
                        ":clue_store_id"=>$sseRow["clue_store_id"],
                    ))->queryAll();
                if($virRows){
                    foreach ($virRows as $virRow){
                        $virRow['month_amt'] = floatval($virRow['month_amt']);
                        $visit_info_text[]="{$virRow['month_amt']}({$virRow["busine_id_text"]})";
                    }
                }
                $username = CGetName::getUserNameByEmployeeID($saveData["clueContRow"]['sales_id']);
                if(CGetName::getUserNameHasAccess($username,"HK01")) {//有销售拜访的读写权限
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_visit",array(
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
                        "total_amt"=>$sseRow["store_amt"],
                        "busine_id"=>$sseRow["busine_id"],
                        "busine_id_text"=>$sseRow["busine_id_text"],
                        "lcu"=>$username,
                        "status"=>'N',
                        "status_dt"=>null,
                    ));
                    $visitId = Yii::app()->db->getLastInsertID();
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_flow",array(
                        "clue_id"=>$serviceRow["clue_id"],
                        "clue_type"=>$serviceRow["clue_type"],
                        "clue_service_id"=>$saveData["clueContRow"]["clue_service_id"],
                        "visit_date"=>$date,
                        "create_staff"=>$saveData["clueContRow"]['sales_id'],
                        "store_num"=>$saveData["clueContRow"]["store_sum"],
                        "update_bool"=>4,
                        "rpt_bool"=>empty($serviceRow["rpt_amt"])?0:1,
                        "lbs_main"=>$serviceRow["lbs_main"],
                        "predict_date"=>$serviceRow["predict_date"],
                        "predict_amt"=>$saveData["clueContRow"]["predict_amt"],
                        "sign_odds"=>100,
                        "visit_text"=>"CRM自动生成",
                        "visit_obj"=>'10',
                        "visit_obj_text"=>"签单",
                        "table_id"=>$visitId,
                        "lcu"=>$username,
                    ));
                    $vir_ids = Yii::app()->db->createCommand()->select("GROUP_CONCAT(id) as ids")
                        ->from("sales{$suffix}.sal_contract_virtual")
                        ->where("sse_id=:id",array(":id"=>$sseRow["id"]))->queryRow();
                    $vir_ids = $vir_ids?$vir_ids["ids"]:"0";
                    $insertSQL = "INSERT INTO sal_visit_info (visit_id,field_id,field_value,lcu,luu,lcd,lud)
                                SELECT '{$visitId}',field_id,field_value,lcu,luu,lcd,lud 
                                FROM sal_contract_vir_info WHERE virtual_id in ({$vir_ids})";
                    Yii::app()->db->createCommand($insertSQL)->execute();

                    $model= new VisitForm('edit');//首页需要提示大神签单
                    $model->addNotificationByQian($visitId);
                }
            }
        }
    }

    protected function addHistoryBy10($saveData){
        $suffix = Yii::app()->params['envSuffix'];
        //KA项目生成成交记录
        $clue_service_id = $saveData["clueContRow"]["clue_service_id"];
        $serviceRow = Yii::app()->db->createCommand()
            ->select("a.*,b.*")
            ->from("sales{$suffix}.sal_clue_service a")
            ->leftJoin("sales{$suffix}.sal_clue b","a.clue_id=b.id")
            ->where("a.id=:id",array(":id"=>$clue_service_id))->queryRow();
        if($serviceRow){
            if($serviceRow["clue_type"]==2){//增加KA签单
                $this->serviceKAQian($clue_service_id,$saveData,$serviceRow);
            }else{//增加KA签单
                $this->serviceVisitQian($saveData,$serviceRow);
            }
        }

        //修改门店状态
        $storeRows = Yii::app()->db->createCommand()->select("clue_store_id")->from("sales{$suffix}.sal_clue_sre_soe")
            ->where("clue_service_id=:id",array(":id"=>$clue_service_id))->queryAll();
        foreach ($storeRows as $storeRow){
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_store",array(
                "store_status"=>2
            ),"id=:id",array(":id"=>$storeRow["clue_store_id"]));//
            Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_history",array(
                "table_id"=>$storeRow["clue_store_id"],
                "table_type"=>2,
                "history_type"=>2,
                "history_html"=>"<span>服务中</span>",
                "lcu"=>$saveData["username"],
            ));
        }
        $virRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
            ->where("cont_id=:id",array(":id"=>$saveData["id"]))->queryAll();
        $historyArr = array(
            "table_id"=>0,
            "table_type"=>7,
            "history_type"=>1,
            "history_html"=>"<span>新增</span>",
            "lcu"=>0,
        );
        if($virRows){
            foreach ($virRows as $virRow){
                $historyArr["table_id"] = $virRow['id'];
                $historyArr["lcu"] = $virRow['luu'];
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$historyArr);//修改状态
            }
        }
    }

    public static function computeKABotStoreAndAmt($ka_id){
        $suffix = Yii::app()->params['envSuffix'];
        $sumRow = Yii::app()->db->createCommand()->select("sum(ava_num) as store_sum,sum(ava_fact_amt) as amt_sum")
            ->from("sales{$suffix}.sal_ka_bot_ava")
            ->where("bot_id=:id",array(":id"=>$ka_id))->queryRow();
        if($sumRow){
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_ka_bot",array(
                "ava_sum"=>empty($sumRow["store_sum"])?null:floatval($sumRow["store_sum"]),
                "sum_amt"=>empty($sumRow["amt_sum"])?null:floatval($sumRow["amt_sum"]),
            ),"id=".$ka_id);
        }
    }

    //合同正式生效允许续约变更等操作
    protected function copyContractProForVir($saveData){
        $nowDate = date_format(date_create(),"Y/m/d");
        $suffix = Yii::app()->params['envSuffix'];
        $contRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract")
            ->where("id=:id",array(":id"=>$saveData["id"]))->queryRow();
        if($contRow){
            $proRow = $contRow;
            unset($proRow['id']);
            $proRow["cont_id"]=$contRow["id"];
            $proRow["pro_type"]="N";
            $proRow["pro_num"]=$this->getProNumByCont($proRow["cont_id"],$proRow["pro_type"]);
            $proRow["pro_date"]=$nowDate;
            $proRow["pro_status"]=30;
            $proRow["cont_status"]=30;
            $proRow["pro_change"]=empty($proRow["total_amt"])?0:$proRow["total_amt"];
            Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contpro",$proRow);
            $proRow["id"] = Yii::app()->db->getLastInsertID();
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_history",array(
                "opr_id"=>$proRow["id"]
            ),"table_type=5 and history_type=1 and opr_id=0 and table_id=:id",array(":id"=>$contRow["id"]));//
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contpro",array(
                "pro_code"=>"CPR".(10000+$proRow["id"])
            ),"id=:id",array(":id"=>$proRow["id"]));//
            $fileRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_file")
                ->where("cont_id=:id",array(":id"=>$saveData["id"]))->queryAll();
            if($fileRows){
                foreach ($fileRows as $fileRow){
                    $proFileRow=$fileRow;
                    unset($proFileRow["id"]);
                    $proFileRow["pro_id"]=$proRow["id"];
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contpro_file",$proFileRow);
                }
            }
            $sseRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_sse")
                ->where("cont_id=:id",array(":id"=>$saveData["id"]))->queryAll();
            if($sseRows){
                foreach ($sseRows as $sseRow){
                    $proSSERow=$sseRow;
                    unset($proSSERow["id"]);
                    $proSSERow["pro_id"]=$proRow["id"];
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contpro_sse",$proSSERow);
                }
            }
            $virRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
                ->where("cont_id=:id",array(":id"=>$saveData["id"]))->queryAll();
            if($virRows){
                foreach ($virRows as $virRow){
                    $proVirRow=$virRow;
                    unset($proVirRow["id"]);
                    $proVirRow["pro_id"]=$proRow["id"];
                    $proVirRow["vir_id"]=$virRow["id"];
                    $proVirRow["pro_type"]="N";
                    $proVirRow["pro_num"]=$this->getProNumByVir($proVirRow["vir_id"],$proVirRow["pro_type"]);
                    $proVirRow["pro_date"]=$nowDate;
                    $proVirRow["pro_status"]=30;
                    $proVirRow["vir_status"]=30;
                    $proVirRow["pro_change"]=empty($proVirRow["year_amt"])?0:$proVirRow["year_amt"];
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contpro_virtual",$proVirRow);
                    $proVirRow["id"] = Yii::app()->db->getLastInsertID();
                    Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_history",array(
                        "opr_id"=>$proVirRow["id"]
                    ),"table_type=7 and history_type=1 and opr_id=0 and table_id=:id",array(":id"=>$virRow["id"]));//
                    Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contpro_virtual",array(
                        "pro_code"=>"VPR".(10000+$proVirRow["id"])
                    ),"id=:id",array(":id"=>$proVirRow["id"]));//
                }
            }
        }
    }

    protected function getProNumByCont($cont_id,$pro_type){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("count(id) as num")->from("sales{$suffix}.sal_contpro")
            ->where("cont_id=:id and pro_type=:pro_type",array(":id"=>$cont_id,":pro_type"=>$pro_type))->queryRow();
        $num = $row?$row["num"]:0;
        return $num+1;
    }

    protected function getProNumByVir($vir_id,$pro_type){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("count(id) as num")->from("sales{$suffix}.sal_contpro_virtual")
            ->where("vir_id=:id and pro_type=:pro_type",array(":id"=>$vir_id,":pro_type"=>$pro_type))->queryRow();
        $num = $row?$row["num"]:0;
        return $num+1;
    }
	
}