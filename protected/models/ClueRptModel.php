<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2025/5/30 0030
 * Time: 10:37
 */
class ClueRptModel
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
        $this->historyArr = array("table_type"=>3,"table_id"=>$data["id"],"history_type"=>5,"lcu"=>$data["username"],"history_html"=>array());
        $this->historyArr["history_html"][]="<span>门户网站</span>";
        $rptStatus = $data["clueRptRow"]['rpt_status'];
        $contractStatus = isset($data["contractStatus"])?$data["contractStatus"]:0;
        switch ($statusType){
            case "startEvent";//启动流程
                if($rptStatus>=10){
                    return array("bool"=>false,"error"=>"该合约已生效无法修改({$rptStatus})");
                }
                $this->historyArr["history_html"][]="<span>启动流程</span>";
                $data["rpt_status"] = 1;
                break;
            case "taskCreate";//合同生效或上传印章
                if($contractStatus=="start"){
                    if($rptStatus>=10){
                        return array("bool"=>false,"error"=>"该合约已生效无法修改({$rptStatus})");
                    }
                    $this->historyArr["history_html"][]="<span>开始</span>";
                    $data["rpt_status"] = 0;
                }else{
                    return array("bool"=>false,"error"=>"taskCreate节点异常({$contractStatus})");
                }
                break;
            case "taskComplete";//任务结束时
                if($rptStatus>=10){
                    return array("bool"=>false,"error"=>"该虚拟合约已生效无法修改({$rptStatus})");
                }elseif ($contractStatus=="end"){
                    $this->historyArr["history_html"][]="<span>启动流程</span>";
                    $data["rpt_status"] = 1;
                }else{
                    return array("bool"=>false,"error"=>"contractStatus异常({$contractStatus})");
                }
                break;
            case "endProcess";//终止
                $this->historyArr["history_html"][]="<span>终止</span>";
                $data["rpt_status"] = 9;
                break;
            case "taskRemove";//流程删除时
                $this->historyArr["history_html"][]="<span>流程删除</span>";
                $data["rpt_status"] = 8;
                break;
            case "endEvent";//流程结束时
                $this->historyArr["history_html"][]="<span>流程结束</span>";
                $this->historyArr["history_type"]=10;
                $row = Yii::app()->db->createCommand()->select("id")
                    ->from("sales{$suffix}.sal_clue_history")
                    ->where("table_type=3 and table_id=:id and history_type=10",array(":id"=>$data["id"]))->queryRow();
                if($row){
                    return array("bool"=>false,"error"=>"该报价已结束，无法重新结束流程");
                }
                $data["rpt_status"] = 10;
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
            ->from("sales{$suffix}.sal_clue_rpt")->where("mh_id=:id",array(":id"=>$id))->queryRow();
        if(!$row){
            $businessKeyList = key_exists("businesskey",$data)?explode("_",$data["businesskey"]):array();
            if(count($businessKeyList)==2){
                $row = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_rpt")
                    ->where("mh_id is null and id=:id",array(":id"=>$businessKeyList[1]))->queryRow();
                if($row){
                    return array("bool"=>false,"error"=>"无效数据");
                }
            }
        }
        if($row){
            if(in_array($row["rpt_status"],array(0,1))){
                $data["id"] = $row["id"];
                $data["clueRptRow"] = $row;
                return array("bool"=>true);
            }else{
                return array("bool"=>false,"error"=>"报价状态异常({$row["rpt_status"]})。");
            }
        }else{
            return array("bool"=>false,"error"=>"报价ID不存在。{$id}");
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
        $clue_service_id = $saveData["clueRptRow"]["clue_service_id"];
        $mhRemark=null;
        if (isset($saveData["remark"])){
            $mhRemark = $saveData["remark"];
        }
        $uid = $saveData["username"];
        switch ($saveData["rpt_status"]){
            case 0;//允许修改
                $db->createCommand()->update("sales{$suffix}.sal_clue_rpt",array(
                    "rpt_status"=>0,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_service",array(
                    "service_status"=>4
                ),"id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_sre_soe",array(
                    "update_bool"=>1
                ),"clue_service_id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_flow",array(
                    "update_bool"=>1
                ),"clue_service_id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_history",$this->historyArr);
                break;
            case 1;//不允许修改
                $db->createCommand()->update("sales{$suffix}.sal_clue_rpt",array(
                    "rpt_status"=>1,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_service",array(
                    "service_status"=>3
                ),"id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_sre_soe",array(
                    "update_bool"=>2
                ),"clue_service_id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_flow",array(
                    "update_bool"=>2
                ),"clue_service_id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_history",$this->historyArr);
                break;
            case 8;//删除
            case 9;//终止
                $db->createCommand()->update("sales{$suffix}.sal_clue_rpt",array(
                    "rpt_status"=>9,
                    "mh_remark"=>$mhRemark,
                    "mh_id"=>null,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_service",array(
                    "service_status"=>4
                ),"id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_sre_soe",array(
                    "update_bool"=>1
                ),"clue_service_id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_flow",array(
                    "update_bool"=>1
                ),"clue_service_id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_history",$this->historyArr);
                break;
            case 10;//报价通过
                $db->createCommand()->update("sales{$suffix}.sal_clue_rpt",array(
                    "rpt_status"=>10,
                    "mh_remark"=>$mhRemark,
                    "luu"=>$uid
                ),"id=".$saveData["id"]);//修改状态
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_service",array(
                    "service_status"=>5
                ),"id=:id",array(":id"=>$clue_service_id));
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue",array(
                    "clue_status"=>3
                ),"id=:id",array(":id"=>$saveData["clueRptRow"]["clue_id"]));
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_history",$this->historyArr);
                break;
            default:
                $returnList=array('code'=>400,'msg'=>"保存数据异常");
        }
        return $returnList;
    }

    public function getLbsMainNameByID($lbs_main_id){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_main_lbs")
            ->where("id=:id",array(":id"=>$lbs_main_id))
            ->queryRow();
        $result = $row?$row["name"]:$lbs_main_id;
        return array("state"=>true,"message"=>"数据请求成功","result"=>$result);
    }

    public function getCustGroupNameByID($cust_group){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_ka_level")
            ->where("id=:id",array(":id"=>$cust_group))
            ->queryRow();
        $result = $row?$row["pro_name"]:$cust_group;
        return array("state"=>true,"message"=>"数据请求成功","result"=>$result);
    }
}