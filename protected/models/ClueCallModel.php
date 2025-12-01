<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2025/5/30 0030
 * Time: 10:37
 */
class ClueCallModel
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
        $this->historyArr = array("table_type"=>9,"table_id"=>$data["id"],"history_type"=>5,"lcu"=>$data["username"],"history_html"=>array());
        $this->historyArr["history_html"][]="<span>门户网站</span>";
        $callStatus = $data["contCallRow"]['call_status'];
        $contractStatus = isset($data["contractStatus"])?$data["contractStatus"]:0;
        switch ($statusType){
            case "startEvent";//启动流程
                if($callStatus>=10){
                    return array("bool"=>false,"error"=>"该呼叫服务已生效无法修改({$callStatus})");
                }
                $this->historyArr["history_html"][]="<span>启动流程</span>";
                $data["call_status"] = 1;
                break;
            case "taskCreate";//合同生效或上传印章
                if($contractStatus=="start"){
                    if($callStatus>=10){
                        return array("bool"=>false,"error"=>"该呼叫服务已生效无法修改({$callStatus})");
                    }
                    $this->historyArr["history_html"][]="<span>开始</span>";
                    $data["call_status"] = 0;
                }else{
                    return array("bool"=>false,"error"=>"taskCreate节点异常({$contractStatus})");
                }
                break;
            case "taskComplete";//任务结束时
                if($callStatus>=10){
                    return array("bool"=>false,"error"=>"该呼叫服务已生效无法修改({$callStatus})");
                }elseif ($contractStatus=="end"){
                    $this->historyArr["history_html"][]="<span>启动流程</span>";
                    $data["call_status"] = 1;
                }else{
                    return array("bool"=>false,"error"=>"contractStatus异常({$contractStatus})");
                }
                break;
            case "endProcess";//终止
                $this->historyArr["history_html"][]="<span>终止</span>";
                $data["call_status"] = 9;
                break;
            case "taskRemove";//流程删除时
                $this->historyArr["history_html"][]="<span>流程删除</span>";
                $data["call_status"] = 8;
                break;
            case "endEvent";//流程结束时
                $this->historyArr["history_html"][]="<span>流程结束</span>";
                $this->historyArr["history_type"]=10;
                /*
                $row = Yii::app()->db->createCommand()->select("id")
                    ->from("sales{$suffix}.sal_contract_history")
                    ->where("table_type=9 and table_id=:id and history_type=10",array(":id"=>$data["id"]))->queryRow();
                if($row){
                    return array("bool"=>false,"error"=>"该呼叫已生效，无法重新生效");
                }
                */
                $data["call_status"] = 30;
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

    public function validateInstID(&$data,$keyStr,$sleep=true){//审核id
        $id = key_exists($keyStr,$data)?$data[$keyStr]:0;
        $id = "".$id;
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("*")
            ->from("sales{$suffix}.sal_contract_call")->where("mh_id=:id",array(":id"=>$id))->queryRow();
        if(!$row){
            $businessKeyList = key_exists("businesskey",$data)?explode("_",$data["businesskey"]):array();
            if(count($businessKeyList)==2){
                $row = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_call")
                    ->where("mh_id is null and id=:id",array(":id"=>$businessKeyList[1]))->queryRow();
                if($row){
                    return array("bool"=>false,"error"=>"无效数据");
                }
            }
        }
        if($row){
            if(in_array($row["call_status"],array(0,1))){
                $data["id"] = $row["id"];
                $data["contCallRow"] = $row;
                return array("bool"=>true);
            }else{
                return array("bool"=>false,"error"=>"呼叫服务状态异常({$row["rpt_status"]})。");
            }
        }else{
            return array("bool"=>false,"error"=>"呼叫服务ID不存在。{$id}");
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
        switch ($saveData["call_status"]){
            case 0;//允许修改
                $db->createCommand()->update("sales{$suffix}.sal_contract_call",array(
                    "call_status"=>0,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                $db->createCommand()->update("sales{$suffix}.sal_contpro_virtual",array(
                    "pro_status"=>0,
                    "luu"=>$uid
                ),"pro_vir_type=3 and call_id=:call_id",array(":call_id"=>$saveData["id"]));//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 1;//不允许修改
                $db->createCommand()->update("sales{$suffix}.sal_contract_call",array(
                    "call_status"=>1,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                $db->createCommand()->update("sales{$suffix}.sal_contpro_virtual",array(
                    "pro_status"=>1,
                    "luu"=>$uid
                ),"pro_vir_type=3 and call_id=:call_id",array(":call_id"=>$saveData["id"]));//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 8;//删除
            case 9;//终止
                $db->createCommand()->update("sales{$suffix}.sal_contract_call",array(
                    "call_status"=>9,
                    "mh_remark"=>$mhRemark,
                    "mh_id"=>null,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                $db->createCommand()->update("sales{$suffix}.sal_contpro_virtual",array(
                    "pro_status"=>0,
                    "luu"=>$uid
                ),"pro_vir_type=3 and call_id=:call_id",array(":call_id"=>$saveData["id"]));//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                break;
            case 30;//通过
                $db->createCommand()->update("sales{$suffix}.sal_contract_call",array(
                    "call_status"=>30,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                $db->createCommand()->update("sales{$suffix}.sal_contpro_virtual",array(
                    "pro_status"=>30,
                    "luu"=>$uid
                ),"pro_vir_type=3 and call_id=:call_id",array(":call_id"=>$saveData["id"]));//修改状态
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",$this->historyArr);
                $this->copyContractProForVir($saveData);//合同正式生效允许续约变更等操作
                $this->sendContractVirForU($saveData);//虚拟合约发送给派单系统
                break;
            default:
                $returnList=array('code'=>400,'msg'=>"保存数据异常");
        }
        return $returnList;
    }

    public function copyContractProForVir($saveData){
        $suffix = Yii::app()->params['envSuffix'];
        $call_id = $saveData["id"];
        //每个合约呼叫的总次数
        $callNum = empty($saveData["contCallRow"]["store_num"])?0:$saveData["contCallRow"]["call_sum"];
        $monthArr = json_decode($saveData["contCallRow"]["call_month_json"],true);
        $virRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
            ->where("id in ({$saveData["contCallRow"]["vir_ids"]})")->queryAll();
        $callText = "";
        $fre_list = array();
        $month_char = "svc_".$saveData["contCallRow"]["busine_id"];
        $infoRow = Yii::app()->db->createCommand()->select("a.id_char")->from("sal_service_type_info a")
            ->leftJoin("sal_service_type b","a.type_id=b.id")
            ->where("b.id_char=:id_char and input_type='yearAmount'",array(":id_char"=>$saveData["contCallRow"]["busine_id"]))
            ->queryRow();
        $year_char=$infoRow?"svc_".$infoRow["id_char"]:"";
        foreach ($monthArr as $month=>$num){
            $monthNum = date("Y年n",strtotime($month));
            $yearMonthList = explode("年",$monthNum);
            $fre_list[]=array(
                "year"=>$yearMonthList[0],
                "month"=>$yearMonthList[1],
                "fre_num"=>$num,
                "type_sum"=>1,
                "fre_amt"=>0,
                "type_amt"=>1,
            );
            $callText.="{$monthNum}月/{$num}次;";
        }
        if($virRows){
            foreach ($virRows as $virRow){
                $virPrice = floatval($virRow["call_fre_amt"]);//单次呼叫金额
                $totalPrice = $virPrice*$callNum;//本次呼叫总金额
                $serviceSum= $virRow["service_sum"]+$callNum;//
                $oldPrice= empty($virRow["service_sum"])?0:floatval($virRow["year_amt"]);//已存在的金额
                $amt = $totalPrice+$oldPrice;
                $service_fre_text = $virRow["service_fre_text"].$callText;
                $service_fre_json = empty($virtualRow["service_fre_json"])?array():json_decode($virtualRow["service_fre_json"],true);
                $service_fre_json["fre_amt"]=$amt;
                $service_fre_json["fre_sum"]=$serviceSum;
                $service_fre_json["fre_list"]=array();//只生成最新的频次
                $detail_json = empty($virtualRow["detail_json"])?array():json_decode($virtualRow["detail_json"],true);
                $detailArr = array(
                    "{$month_char}"=>$amt,
                    "{$month_char}FreAmt"=>$amt,
                    "{$month_char}FreSum"=>$serviceSum,
                    "{$month_char}FreText"=>$service_fre_text,
                );
                if(!empty($year_char)){
                    $detailArr[$year_char]=$amt;
                }
                foreach ($detailArr as $key=>$value){
                    $detail_json[$key] = $value;
                    $this->resetVirInfo($virRow["id"],$key,$value);
                }
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_virtual",array(
                    "month_amt"=>$amt,//月金额
                    "year_amt"=>$amt,//年金额
                    "service_sum"=>$serviceSum,//服务总次数
                    "service_fre_amt"=>$amt,//服务频次总金额
                    "service_fre_sum"=>$serviceSum,//服务频次总次数
                    "service_fre_json"=>json_encode($service_fre_json,JSON_UNESCAPED_UNICODE),//服务频次
                    "service_fre_text"=>$service_fre_text,//服务频次(文字)
                    "invoice_amount"=>$amt,//发票金额
                    "detail_json"=>json_encode($detail_json,JSON_UNESCAPED_UNICODE),//
                ),"id=".$virRow["id"]);
                //生成销售拜访
                $this->addHistoryBy10($saveData,$virRow["id"],$fre_list);

                //生成频次数据
                Yii::app()->db->createCommand()->delete("sales{$suffix}.sal_contract_vir_week","vir_id={$virRow["id"]}");//全部清空
                foreach ($fre_list as $freArr){
                    $freArr["fre_amt"]=$virPrice;
                    $forNum = $freArr["fre_num"];
                    $is_del = 0;
                    if($forNum<0){
                        $is_del=1;
                        $forNum*=-1;
                    }
                    for($i=0;$i<$forNum;$i++){
                        $oneMonth=pow(2,$freArr["month"]-1);
                        Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_vir_week",array(
                            "vir_id"=>$virRow["id"],
                            "unit_price"=>$virPrice,
                            "month_cycle"=>$oneMonth,
                            "year_cycle"=>$freArr["year"],
                            "is_del"=>$is_del,
                            "lcu"=>$saveData["contCallRow"]["lcu"],
                            "luu"=>$saveData["contCallRow"]["lcu"],
                        ));
                    }
                }
            }
        }
    }

    //生成销售拜访
    protected function addHistoryBy10($saveData,$vir_id,$fre_list){
        $suffix = Yii::app()->params['envSuffix'];
        $virRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
            ->where("id=".$vir_id)->queryRow();
        //KA项目生成成交记录
        $clue_service_id = $virRow["clue_service_id"];
        $serviceRow = Yii::app()->db->createCommand()
            ->select("a.*,b.*")
            ->from("sales{$suffix}.sal_clue_service a")
            ->leftJoin("sales{$suffix}.sal_clue b","a.clue_id=b.id")
            ->where("a.id=:id",array(":id"=>$clue_service_id))->queryRow();
        if($serviceRow){
            if($serviceRow["clue_type"]==2){//增加KA签单
                $this->serviceKAQian($clue_service_id,$virRow,$serviceRow,$fre_list);
            }else{//增加KA签单
                $this->serviceVisitQian($saveData,$virRow,$serviceRow);
            }
        }
    }

    //增加KA签单
    protected function serviceKAQian($clue_service_id,$virRow,$serviceRow,$fre_list){
        $suffix = Yii::app()->params['envSuffix'];
        $kaRow = Yii::app()->db->createCommand()->select("id")->from("sales{$suffix}.sal_ka_bot")
            ->where("id=:id",array(":id"=>$serviceRow["ka_id"]))->queryRow();
        if($kaRow){
            $link_id = Yii::app()->db->createCommand()->select("id")->from("sales{$suffix}.sal_ka_link")
                ->order("rate_num desc")->queryRow();
            $sign_month = intval($virRow["cont_month_len"]/12);
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_ka_bot",array(
                "sign_date"=>$virRow["cont_start_dt"],
                "sign_end_date"=>$virRow["cont_end_dt"],
                "sign_month"=>$sign_month,
                "link_id"=>$link_id?$link_id["id"]:0,
                "sign_odds"=>100,
            ),"id=:id",array(":id"=>$kaRow["id"]));
            Yii::app()->db->createCommand()->insert("sal_ka_bot_ava",array(
                "bot_id"=>$kaRow["id"],
                "ava_date"=>date("Y-m-01",strtotime($virRow["cont_start_dt"])),
                "ava_fact_amt"=>$virRow["year_amt"],
                "ava_amt"=>$virRow["year_amt"],
                "ava_num"=>1,
                "ava_note"=>"CRM呼叫生成",
                "ava_rate"=>90,
            ));
            $ka_ava_id = Yii::app()->db->getLastInsertID();
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_service",array(
                "ka_ava_id"=>$ka_ava_id
            ),"id=".$clue_service_id);
            $this->computeKABotStoreAndAmt($kaRow["id"]);
        }
    }

    protected function serviceVisitQian($saveData,$virRow,$serviceRow){
        $suffix = Yii::app()->params['envSuffix'];
        $username = CGetName::getUserNameByEmployeeID($virRow['sales_id']);
        if(!CGetName::getUserNameHasAccess($username,"HK01")) {//有销售拜访的读写权限
            return false;
        }
        $virProRows = Yii::app()->db->createCommand()->select("*")
            ->from("sales{$suffix}.sal_contpro_virtual")
            ->where("pro_vir_type=3 and call_id=:call_id and vir_id=:vir_id",array(
                ":call_id"=>$saveData["contCallRow"]["id"],
                ":vir_id"=>$virRow["id"],
            ))->queryAll();
        $storeRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_store")
            ->where("id=:id",array(":id"=>$virRow["clue_store_id"]))->queryRow();
        if($virProRows){
            foreach ($virProRows as $virProRow){
                $virProRow['month_amt'] = floatval($virProRow['month_amt']);
                $visit_info_text="{$virProRow['month_amt']}({$virProRow["busine_id_text"]})";
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_visit",array(
                    "username"=>$username,
                    "visit_dt"=>$virProRow["cont_start_dt"],
                    "visit_type"=>$serviceRow["visit_type"],
                    "visit_obj"=>'["10"]',
                    "visit_obj_name"=>"签单",
                    "quotation"=>"是",
                    "visit_info_text"=>$visit_info_text,
                    "service_type"=>$serviceRow["service_type"],//json
                    "cust_type"=>$storeRow["cust_class"],
                    "cust_name"=>$storeRow["store_name"],
                    "cust_person"=>$storeRow["cust_person"],
                    "cust_tel"=>$storeRow["cust_tel"],
                    "district"=>CGetName::getVisitDistrictIDByNalID($storeRow["district"],$storeRow["city"]),
                    //"street"=>$sseRow["street"],
                    "remarks"=>"CRM呼叫生成",
                    "sign_odds"=>100,
                    "city"=>$storeRow["city"],
                    "total_amt"=>$virProRow['month_amt'],
                    "busine_id"=>$virProRow["busine_id"],
                    "busine_id_text"=>$virProRow["busine_id_text"],
                    "lcu"=>$username,
                    "status"=>'N',
                    "status_dt"=>null,
                ));
                $visitId = Yii::app()->db->getLastInsertID();
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_flow",array(
                    "clue_id"=>$serviceRow["clue_id"],
                    "clue_type"=>$serviceRow["clue_type"],
                    "clue_service_id"=>$virRow["clue_service_id"],
                    "visit_date"=>$virProRow["cont_start_dt"],
                    "create_staff"=>$virRow['sales_id'],
                    "update_bool"=>4,
                    "rpt_bool"=>empty($serviceRow["rpt_amt"])?0:1,
                    "lbs_main"=>$serviceRow["lbs_main"],
                    "predict_date"=>$serviceRow["predict_date"],
                    "predict_amt"=>$serviceRow["predict_amt"],
                    "sign_odds"=>100,
                    "visit_text"=>"CRM呼叫生成",
                    "visit_obj"=>'10',
                    "visit_obj_text"=>"签单",
                    "table_id"=>$visitId,
                    "lcu"=>$username,
                ));
                $detailJson = json_decode($virProRow["detail_json"],true);
                if(is_array($detailJson)){
                    foreach ($detailJson as $field_id=>$field_value){
                        Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_visit_info",array(
                            "field_id"=>$field_id,
                            "field_value"=>$field_value,
                            "visit_id"=>$visitId,
                            "lcu"=>$username,
                        ));
                    }
                }
            }
        }
    }

    public function sendContractVirForU($saveData){
        $uVirModel = new CurlNotesByVirPro();
        $uVirModel->pro_type="A";
        $uVirModel->update_effective_date = $saveData["contCallRow"]["lcd"];
        $uVirModel->sendAllVirByIDsAndUpdate($saveData["contCallRow"]["vir_ids"]);
    }

    protected function resetVirInfo($vir_id,$field_id,$field_value){
        $suffix = Yii::app()->params['envSuffix'];
        $virInfoRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_vir_info")
            ->where("virtual_id=:vir_id and field_id=:field_id",array(":vir_id"=>$vir_id,":field_id"=>$field_id))->queryRow();
        if($virInfoRow){
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_vir_info",array(
                "field_value"=>$field_value,
            ),"id=".$virInfoRow["id"]);
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
}