<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2025/5/30 0030
 * Time: 10:37
 */
class ClueVirProModel
{

    public $historyArr=array();
    protected $vir_id_arr=array();

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
        $this->historyArr = array("table_type"=>8,"table_id"=>$data["id"],"history_type"=>5,"lcu"=>$data["username"],"history_html"=>array());
        $this->historyArr["history_html"][]="<span>门户网站</span>";
        $nowContStatus = $data["virBatchRow"]['pro_status'];
        $contractStatus = isset($data["contractStatus"])?$data["contractStatus"]:0;
        switch ($statusType){
            /*
            case "agree";//同意
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该虚拟合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>同意</span>";
                $data["pro_status"] = 1;
                break;
            case "reject";//驳回
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该虚拟合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>驳回</span>";
                if($nodeId==self::$noteID){
                    $data["pro_status"] = 0;
                }else{
                    $data["pro_status"] = 1;
                }
                break;
            case "taskRevoke";//任务撤回
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该虚拟合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>任务撤回</span>";
                if($nodeId==self::$noteID){
                    $data["pro_status"] = 0;
                }else{
                    $data["pro_status"] = 1;
                }
                break;
            case "revoke";//撤回
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该虚拟合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>撤回</span>";
                if($nodeId==self::$noteID){
                    $data["pro_status"] = 0;
                }else{
                    $data["pro_status"] = 1;
                }
                break;
            */
            case "startEvent";//启动流程
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该虚拟合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>启动流程</span>";
                $data["pro_status"] = 1;
                break;
            case "endProcess";//终止
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该虚拟合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>终止</span>";
                $data["pro_status"] = 9;
                break;
            case "taskCreate";//合同生效或上传印章或启动流程
                if($contractStatus==1){
                    if($nowContStatus>=21||$nowContStatus==10){
                        return array("bool"=>false,"error"=>"该虚拟合约已生效无法修改({$nowContStatus})");
                    }
                    $row = Yii::app()->db->createCommand()->select("id")
                        ->from("sales{$suffix}.sal_contract_history")
                        ->where("table_type=8 and table_id=:id and history_type=10",array(":id"=>$data["id"]))->queryRow();
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
                        return array("bool"=>false,"error"=>"该虚拟合约已生效无法修改({$nowContStatus})");
                    }
                    $this->historyArr["history_html"][]="<span>开始</span>";
                    $data["pro_status"] = 0;
                }else{
                    return array("bool"=>false,"error"=>"taskCreate节点异常({$contractStatus})");
                }
                break;
            case "taskComplete";//任务结束时
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该虚拟合约已生效无法修改({$nowContStatus})");
                }elseif ($contractStatus=="end"){
                    $this->historyArr["history_html"][]="<span>启动流程</span>";
                    $data["pro_status"] = 1;
                }else{
                    return array("bool"=>false,"error"=>"contractStatus异常({$contractStatus})");
                }
                break;
            case "taskRemove";//流程删除时
                if($nowContStatus>=10){
                    return array("bool"=>false,"error"=>"该虚拟合约已生效无法修改({$nowContStatus})");
                }
                $this->historyArr["history_html"][]="<span>流程删除</span>";
                $data["pro_status"] = 8;
                break;
            case "endEvent";//流程结束时
                if($nowContStatus>20){
                    return array("bool"=>false,"error"=>"该虚拟合约状态异常，无法结束({$nowContStatus})");
                }
                $row = Yii::app()->db->createCommand()->select("id")
                    ->from("sales{$suffix}.sal_contract_history")
                    ->where("table_type=8 and table_id=:id and history_type=10",array(":id"=>$data["id"]))->queryRow();
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
            ->from("sales{$suffix}.sal_virtual_batch")->where("mh_id=:id",array(":id"=>$id))->queryRow();
        if(!$row){
            $businessKeyList = key_exists("businesskey",$data)?explode("_",$data["businesskey"]):array();
            if(count($businessKeyList)==2){
                $row = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_virtual_batch")
                    ->where("mh_id is null and id=:id",array(":id"=>$businessKeyList[1]))->queryRow();
                if($row){
                    return array("bool"=>false,"error"=>"无效数据");
                }
            }
        }
        if($row){
            if($row["pro_status"]<30){
                $data["id"] = $row["id"];
                $data["virBatchRow"] = $row;
                return array("bool"=>true);
            }else{
                return array("bool"=>false,"error"=>"批量合同状态异常({$row["pro_status"]})。");
            }
        }else{
            return array("bool"=>false,"error"=>"批量合同ID不存在。{$id}");
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
        $vir_id_text = $saveData["virBatchRow"]["vir_id_text"];
        $this->vir_id_arr = explode(",",$vir_id_text);
        $mhRemark=null;
        if (isset($saveData["remark"])){
            $mhRemark = $saveData["remark"];
        }
        $uid = $saveData["username"];
        switch ($saveData["pro_status"]){
            case 0;//允许修改
                $db->createCommand()->update("sales{$suffix}.sal_virtual_batch",array(
                    "pro_status"=>0,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 1;//不允许修改
                $db->createCommand()->update("sales{$suffix}.sal_virtual_batch",array(
                    "pro_status"=>1,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 8;//删除
                $db->createCommand()->update("sales{$suffix}.sal_virtual_batch",array(
                    "mh_id"=>null
                ),"id=".$saveData["id"]);//修改状态
            case 9;//终止
                $db->createCommand()->update("sales{$suffix}.sal_virtual_batch",array(
                    "pro_status"=>9,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 10;//合同已生效
                $db->createCommand()->update("sales{$suffix}.sal_virtual_batch",array(
                    "pro_status"=>10,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                $this->copyContractProForVir($saveData);//合同正式生效允许续约变更等操作
                $this->sendContractVirForU($saveData);//虚拟合约发送给派单系统
                break;
            case 19;//待印章
                $db->createCommand()->update("sales{$suffix}.sal_virtual_batch",array(
                    "pro_status"=>19,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 30;//报价通过
                $db->createCommand()->update("sales{$suffix}.sal_virtual_batch",array(
                    "pro_status"=>30,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                // 恢复操作审批通过后立即生效
                if($saveData["virBatchRow"]["pro_type"]=="R"){
                    $this->copyContractProForVir($saveData);//合同正式生效允许续约变更等操作
                    $this->sendContractVirForU($saveData);//虚拟合约发送给派单系统
                }
                break;
            default:
                $returnList=array('code'=>400,'msg'=>"保存数据异常");
        }
        return $returnList;
    }

    protected static function getProTypeStrByKey($key) {
        $list = array(
            "N"=>"新增",
            "A"=>"更改",
            "C"=>"续约",
            "S"=>"暂停",
            "T"=>"终止",
            "R"=>"恢复",
        );
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    //虚拟合约发送给派单系统
    protected function sendContractVirForU($saveData){
        $suffix = Yii::app()->params['envSuffix'];
        $virRow = Yii::app()->db->createCommand()->select("a.*,b.group_bool")
            ->from("sales{$suffix}.sal_contract_virtual a")
            ->leftJoin("sales{$suffix}.sal_contract b","a.cont_id=b.id")
            ->where("a.id=:id",array(":id"=>$saveData["virBatchRow"]["vir_id"]))->queryRow();//
        //发送客户信息
        $this->sendClient($saveData["virBatchRow"]["vir_id_text"],$virRow);
        //发送门店信息
        $this->sendStoreyByVirIDS($saveData["virBatchRow"]["vir_id_text"]);
        //发送合约信息
        $this->sendStoreyByVir($saveData);
        //发送合约附件
        $this->sendVirFileByVir($saveData);
    }

    protected function sendVirFileByVir($saveData){
        $uVirFileModel = new CurlNotesByVirFile();
        $uVirFileModel->sendVirFileByBatchId($saveData["id"]);
    }

    protected function sendStoreyByVir($saveData){
        $uVirBatchModel = new CurlNotesByVirBatch();
        $uVirBatchModel->pro_type=$saveData["virBatchRow"]["pro_type"];
        $uVirBatchModel->update_effective_date = $saveData["virBatchRow"]["pro_date"];
        $uVirBatchModel->sendAllVirByVirIDs($saveData["virBatchRow"]["vir_id_text"]);
    }

    protected function sendStoreyByVirIDS($vir_id_text){//clue_store_id
        $suffix = Yii::app()->params['envSuffix'];
        $storeRows = Yii::app()->db->createCommand()->select("a.clue_store_id,b.u_id")->from("sales{$suffix}.sal_contract_virtual a")
			->leftJoin("sales{$suffix}.sal_clue_store b","a.clue_store_id=b.id")
            ->where("a.id in ({$vir_id_text})")->group("a.clue_store_id,b.u_id")->queryAll();//
        if($storeRows){
			$putStore=array();
			$updateStore=array();
			foreach($storeRows as $storeRow){
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_store",array(
                    "store_status"=>$this->getStoreStatusByStoreID($storeRow["clue_store_id"]),
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

    protected function sendClient($vir_id_text,$virRow){
        $suffix = Yii::app()->params['envSuffix'];
        $clientRows = Yii::app()->db->createCommand()->select("a.clue_id,b.u_id")->from("sales{$suffix}.sal_contract_virtual a")
			->leftJoin("sales{$suffix}.sal_clue b","a.clue_id=b.id")
            ->where("a.id in ({$vir_id_text}) and b.u_id is null")->group("a.clue_id,b.u_id")->queryAll();//
        if($clientRows){
			$putClient=array();
			$updateClient=array();
			foreach($clientRows as $clientRow){
                $updateArr=array("clue_status"=>$this->getClientStatusByClueID($clientRow["clue_id"]),"table_type"=>2);
                if($virRow["group_bool"]=="Y"){
                    $updateArr["group_bool"]="Y";
                }
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue",$updateArr,"id=:id",array(":id"=>$clientRow["clue_id"]));
				if(empty($clientRow['u_id'])&&$virRow["group_bool"]=="Y"){
					$putClient[]=array('clue_id'=>$clientRow["clue_id"]);
				}
				if(!empty($clientRow['u_id'])){
					$updateClient[]=array('clue_id'=>$clientRow["clue_id"]);
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
    }

    public static function getStoreStatusByStoreID($store_id){
        $suffix = Yii::app()->params['envSuffix'];
        $statusRow = Yii::app()->db->createCommand()->select("min(a.vir_status) as min_status,max(a.vir_status) as max_status")->from("sales{$suffix}.sal_contract_virtual a")
            ->where("a.clue_store_id={$store_id} and a.vir_status in (10,30,40,50)")->group("a.vir_status")->queryRow();//
		$status=1;
		if($statusRow){
			//  修复：将虚拟合约状态映射为门店状态
			// 虚拟合约状态：10=待生效, 30=生效中, 40=已暂停, 50=已终止
			// 门店状态：0=未生效, 1=未服务, 2=服务中, 3=已停止, 4=其他
			$virStatus = $statusRow["min_status"];
			switch($virStatus) {
				case 10:  // 待生效
					$status = 1;  // 未服务
					break;
				case 30:  // 生效中
					$status = 2;  // 服务中
					break;
				case 40:  // 已暂停
					$status = 3;  // 已停止
					break;
				case 50:  // 已终止
					$status = 3;  // 已停止
					break;
				default:
					Yii::log("门店{$store_id}的虚拟合约状态异常: {$virStatus}，已设置为默认值1(未服务)", 'warning', 'ClueVirProModel');
					$status = 1;  // 默认未服务
			}
		}
		return $status;
    }

    public static function getClientStatusByClueID($clue_id){
        $suffix = Yii::app()->params['envSuffix'];
        $statusRow = Yii::app()->db->createCommand()->select("min(a.vir_status) as min_status,max(a.vir_status) as max_status")->from("sales{$suffix}.sal_contract_virtual a")
            ->where("a.clue_id={$clue_id} and a.vir_status in (10,30,40,50)")->group("a.vir_status")->queryRow();//
		$status=1;
		if($statusRow){
			$status=$statusRow["min_status"];
		}
		return $status;
    }


    //合同正式生效允许续约变更等操作
    protected function copyContractProForVir($saveData){
        $nowDate = date_format(date_create(),"Y/m/d");
        $proRow = $saveData["virBatchRow"];
        $suffix = Yii::app()->params['envSuffix'];
        $virRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contpro_virtual")
            ->where("pro_vir_type=2 and vir_batch_id=:id and vir_id in ({$saveData["virBatchRow"]["vir_id_text"]})",array(
                ":id"=>$saveData["id"]
            ))->queryAll();
        $historyArr = array(
            "table_id"=>0,
            "table_type"=>7,
            "history_type"=>2,
            "history_html"=>"<span>".self::getProTypeStrByKey($saveData["virBatchRow"]["pro_type"])."</span>",
            "lcu"=>0,
        );
        if($virRows) {
            $oldVirRows=array();//旧合约信息
            foreach ($virRows as $virRow) {
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contpro_virtual",array(
                    "pro_status"=>30
                ),"id=".$virRow["id"]);

                $oldVir = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
                    ->where("id=:id",array(":id"=>$virRow["vir_id"]))->queryRow();

                $oldVirRows[$virRow["vir_id"]]=$oldVir;
                $updateRow = array();
                $notSaveKey = array("id", "vir_code"); // 续约时不应修改虚拟合同编号
                foreach ($oldVir as $keyStr=>$value){
                    if (!in_array($keyStr,$notSaveKey)&&key_exists($keyStr,$virRow)){
                        $updateRow[$keyStr]=$virRow[$keyStr];
                    }
                }
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
                    // 如果 service_fre_json 中没有 fre_month，且 service_fre_type=1（固定频次），从 year_amt 计算
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
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_virtual",$updateRow,"id=".$virRow["vir_id"]);

                $historyArr["table_id"] = $virRow['vir_id'];
                $historyArr["lcu"] = $virRow['lcu'];
                $historyArr["expr_data"] = $virRow['id'];
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$historyArr);
                $this->saveVirInfo($virRow);
				CGetName::resetVirStaffAndWeek($virRow['vir_id']);
            }

            $this->proTypeChange($proRow,$virRows,$oldVirRows,$saveData);
        }
    }

    protected function proTypeChange($proRow,$virRows,$oldVirRows,$saveData=null){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = isset($saveData["username"]) ? $saveData["username"] : "admin";
        switch ($proRow["pro_type"]){
            case "A"://合同内容调整
                if($proRow['pro_change']>0){//金额增加
                    $virRow = Yii::app()->db->createCommand()->select("*")
                        ->from("sales{$suffix}.sal_contract_virtual")
                        ->where("id=:id",array(":id"=>$proRow["vir_id"]))->queryRow();
                    if($virRow&&$virRow["clue_type"]==1){//地推
                        $this->serviceVisitQian($proRow,$virRows,$oldVirRows);
                    }
                }
                break;
            case "R"://恢复
                // 恢复虚拟合约后，更新对应的主合约和门店状态为"生效中"
                Yii::log("[恢复操作] 开始处理恢复逻辑，virRows数量: ".count($virRows), 'info', 'ClueVirProModel.Resume');
                $contIds = array();//主合约ID集合
                $storeIds = array();//门店ID集合
                foreach ($virRows as $virRow) {
                    Yii::log("[恢复操作] 处理虚拟合约ID: ".$virRow["vir_id"], 'info', 'ClueVirProModel.Resume');
                    $virInfo = Yii::app()->db->createCommand()->select("cont_id,clue_store_id")
                        ->from("sales{$suffix}.sal_contract_virtual")
                        ->where("id=:id",array(":id"=>$virRow["vir_id"]))->queryRow();
                    if($virInfo){
                        Yii::log("[恢复操作] 查询到虚拟合约信息 - cont_id: ".$virInfo["cont_id"].", clue_store_id: ".$virInfo["clue_store_id"], 'info', 'ClueVirProModel.Resume');
                        if(!empty($virInfo["cont_id"]) && !in_array($virInfo["cont_id"],$contIds)){
                            $contIds[] = $virInfo["cont_id"];
                        }
                        if(!empty($virInfo["clue_store_id"]) && !in_array($virInfo["clue_store_id"],$storeIds)){
                            $storeIds[] = $virInfo["clue_store_id"];
                        }
                    }else{
                        Yii::log("[恢复操作] 警告：未找到虚拟合约ID ".$virRow["vir_id"]." 的信息", 'warning', 'ClueVirProModel.Resume');
                    }
                }
                // 更新主合约状态为30（生效中）
                if(!empty($contIds)){
                    $contIdStr = implode(",",$contIds);
                    Yii::log("[恢复操作] 更新主合约状态为30(生效中)，合约IDs: ".$contIdStr, 'info', 'ClueVirProModel.Resume');
                    Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract",array(
                        "cont_status"=>30,
                        "luu"=>$uid
                    ),"id in ({$contIdStr})");
                }else{
                    Yii::log("[恢复操作] 警告：没有找到需要更新的主合约", 'warning', 'ClueVirProModel.Resume');
                }
                // 更新门店状态为2（服务中）
                if(!empty($storeIds)){
                    $storeIdStr = implode(",",$storeIds);
                    Yii::log("[恢复操作] 更新门店状态为2(服务中)，门店IDs: ".$storeIdStr, 'info', 'ClueVirProModel.Resume');
                    Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_store",array(
                        "store_status"=>2
                    ),"id in ({$storeIdStr})");
                }else{
                    Yii::log("[恢复操作] 警告：没有找到需要更新的门店", 'warning', 'ClueVirProModel.Resume');
                }
                break;
        }
    }

    protected function serviceVisitQian($proRow,$virRows,$oldVirRows){
        $suffix = Yii::app()->params['envSuffix'];
        $date = date("Y-m-d");
        if($virRows){
            $buChar=array();//月金额，年金额主键
            foreach ($virRows as $virRow){
                $updateList=array();
                $username = CGetName::getUserNameByEmployeeID($virRow['sales_id']);
                if(CGetName::getUserNameHasAccess($username,"HK01")){//有销售拜访的读写权限
                    $virRow['month_amt'] = floatval($virRow['month_amt']);
                    $virRow['year_amt'] = floatval($virRow['year_amt']);
                    if (isset($oldVirRows[$virRow["vir_id"]])) {
                        $virRow['month_amt'] -= floatval($oldVirRows[$virRow["vir_id"]]['month_amt']);
                        $virRow['year_amt'] -= floatval($oldVirRows[$virRow["vir_id"]]['year_amt']);
                        $updateList[] = array(
                            "virtual_id" => $virRow["vir_id"],
                            "field_id" => "svc_" . $virRow["busine_id"],//月金额
                            "field_value" => $virRow['month_amt'],//月金额
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
                                "virtual_id"=>$virRow["vir_id"],
                                "field_id"=>$buChar[$virRow["busine_id"]],//年金额
                                "field_value"=>$virRow['year_amt'],//年金额
                            );
                        }
                    }
                    $serviceRow = Yii::app()->db->createCommand()->select("b.service_type,b.city,a.*")
                        ->from("sales{$suffix}.sal_clue_service a")
                        ->leftJoin("sales{$suffix}.sal_clue b","a.clue_id=b.id")
                        ->where("a.id=:id",array(":id"=>$virRow["clue_service_id"]))->queryRow();
                    $storeRow = Yii::app()->db->createCommand()->select("*")
                        ->from("sales{$suffix}.sal_clue_store")
                        ->where("id=:id",array(":id"=>$virRow["clue_store_id"]))->queryRow();
                    $visit_info_text = floatval($virRow["month_amt"]);
                    $visit_info_text.= "({$virRow["busine_id_text"]})";
                    Yii::app()->db->createCommand()->insert("sal_visit",array(
                        "username"=>$username,
                        "visit_dt"=>$date,
                        "visit_type"=>$serviceRow["visit_type"],
                        "visit_obj"=>'["10"]',
                        "visit_obj_name"=>"签单",
                        "quotation"=>"是",
                        "visit_info_text"=>empty($visit_info_text)?null:$visit_info_text,
                        "service_type"=>$serviceRow["service_type"],//json
                        "cust_type"=>CGetName::getVisitCustTypeIDByCustClassID($storeRow["cust_class"]),
                        "cust_name"=>$storeRow["store_name"],
                        "cust_person"=>$storeRow["cust_person"],
                        "cust_tel"=>$storeRow["cust_tel"],
                        "district"=>CGetName::getVisitDistrictIDByNalID($storeRow["district"],$storeRow["city"]),
                        "remarks"=>"CRM自动生成",
                        "sign_odds"=>100,
                        "city"=>$storeRow["city"],
                        "total_amt"=>$virRow["year_amt"],
                        "busine_id"=>$virRow["busine_id"],
                        "busine_id_text"=>$virRow["busine_id_text"],
                        "lcu"=>$username,
                        "status"=>'N',
                        "status_dt"=>null,
                    ));
                    $visitId = Yii::app()->db->getLastInsertID();
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_flow",array(
                        "clue_id"=>$virRow["clue_id"],
                        "clue_type"=>$virRow["clue_type"],
                        "clue_service_id"=>$virRow["clue_service_id"],
                        "visit_date"=>$date,
                        "create_staff"=>$virRow['sales_id'],
                        "store_num"=>1,
                        "update_bool"=>4,
                        "rpt_bool"=>empty($serviceRow["rpt_amt"])?0:1,
                        "lbs_main"=>$serviceRow["lbs_main"],
                        "predict_date"=>$serviceRow["predict_date"],
                        "predict_amt"=>$serviceRow["predict_amt"],
                        "sign_odds"=>100,
                        "visit_text"=>"CRM自动生成",
                        "visit_obj"=>'10',
                        "visit_obj_text"=>"签单",
                        "table_id"=>$visitId,
                        "lcu"=>$username,
                    ));
                    $vir_ids = $virRow["vir_id"];
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
