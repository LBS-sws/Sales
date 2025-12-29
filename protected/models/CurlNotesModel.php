<?php
//2024年9月28日09:28:46

class CurlNotesModel extends CurlNotesApi {

    public function __construct()
    {
        $this->_key =Yii::app()->params['uCRMKey'];
        $this->uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        //md5(md5('C222748E'.date('Y-m-d',time()).'0000'));
    }

    public function setData($data_content){
        $this->data_content = $data_content;
        $this->data = json_decode($data_content,true);
    }

    public function setMinUrl($min_url){
        $this->min_url = $min_url;
        $this->info_url = $this->getBaseUrl($min_url);
    }

    protected function getComputeKey(){
        return md5(md5($this->_key.date('Y-m-d',time()).'0000'));
    }

    public function getBaseUrl($url){
        return Yii::app()->params['uCRMUrl'].$url;
    }

    public static function getUGenderBySex($sex){
        switch ($sex){
            case "wuman":
                return 2;
            case "man":
                return 1;
            default:
                return 3;
        }
    }

    public static function getUStatusByStoreStatus($store_status){
        //派单系统状态: 1 服务中 2 停止服务 3 其他
        //CRM门店状态: 0未生效 1未服务 2服务中 3已停止 10服务中 30服务中 40已暂停 50已终止
        switch ($store_status){
            case 0:  // 未生效
            case 1:  // 未服务
            case 2:  // 服务中
            case 10: // 服务中
            case 30: // 服务中
                return 1; // 派单: 服务中
            case 3:  // 已停止
            case 40: // 已暂停
            case 50: // 已终止
                return 2; // 派单: 停止服务
//                return 3; // 派单: 其他（未开始服务）
            default:
                // 未知状态，记录日志
                Yii::log("未知的门店状态: {$store_status}，默认返回状态3(其他)", 'warning', 'CurlNotesModel');
                return 3; // 派单: 其他
        }
    }

    protected static function getUStatusByVirStatus($vir_status){
        //合约状态 1 生效中 2 暂停 3 结束 4 删除 5 暂停生效
        switch ($vir_status){
            case 30:
                return 1;
            case 40:
                return 2;
            case 50:
                return 3;
            default:
                return 1;
        }
    }

    public function sendCurl($errorBool=false){
        $data = $this->sendCurlForPost($this->info_url,$this->data_content,$errorBool);
        if($data["status"]){
            $this->outData = $data["outData"];
            $this->status_type="C";
        }else{
            $this->status_type="E";
        }
        $message = mb_strlen($data["message"])>250?mb_substr($data["message"],0,250,'UTF-8'):$data["message"];
        $this->message = $message;
    }

    public function sendUData($data,$type="POST",$errorBool=false){
        //$this->printBool=true;
        if($type=="POST"){
            return $this->sendCurlForPost($this->info_url,$data,$errorBool);
        }else{
            $queryString = http_build_query($data);
            $url=$this->info_url."?".$queryString;
            return $this->sendCurlForGet($url,$errorBool);
        }
    }

    public function setOutContentByData(){
        $this->data_content = json_encode($this->data,JSON_UNESCAPED_UNICODE);
    }

    protected function insertAPICURL($dataContent){
        Yii::app()->db->createCommand()->insert("sal_api_curl",array(
            "expr_date"=>empty($this->expr_date)?date_format(date_create(),"Y/m/d"):"",
            "status_type"=>$this->status_type,
            "min_url"=>$this->min_url,
            "info_url"=>empty($this->info_url)?null:$this->info_url,
            "info_type"=>$this->info_type,
            "data_content"=>$dataContent,
            "out_content"=>empty($this->out_content)?null:$this->out_content,
            "message"=>$this->message,
            "lcu"=>$this->uid,
        ));
        if($this->status_type=="E"){
            $id = Yii::app()->db->getLastInsertID();
            CurlNotesModel::sendWeChatHint($id,$this->message,$this->info_type);
        }
    }

    public static function sendWeChatHint($id,$message='',$info_type="U"){
        $weChatUrl="https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=";
        $weChatUrl.=Yii::app()->params['weChatHintKey'];
        $html="同步失败,ID:<font color=\"warning\">{$id}</font>";
        $name=Yii::t('app',Yii::app()->name);
        $html.="\n><font color=\"comment\">服务器：{$name}</font>";
        /*
<font color="info">绿色</font>
<font color="comment">灰色</font>
<font color="warning">橙红色</font>
         */
        $phoneList=array("17722039238");
        switch ($info_type){
            case "client":
                $html.="\n><font color=\"comment\">模块：CRM系统的客户发给派单系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlNotes/index");
                break;
            case "clientPerson"://客户联系人同步
                $html.="\n><font color=\"comment\">模块：CRM系统的客户联系人发给派单系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlNotes/index");
                break;
            case "clientArea"://客户归属区域同步
                $html.="\n><font color=\"comment\">模块：CRM系统的客户区域发给派单系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlNotes/index");
                break;
            case "clientStaff"://客户负责人同步
                $html.="\n><font color=\"comment\">模块：CRM系统的客户负责人发给派单系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlNotes/index");
                break;
            case "store"://门店同步
                $html.="\n><font color=\"comment\">模块：CRM系统的门店信息发给派单系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlNotes/index");
                break;
            case "storePerson"://门店负责人同步
                $html.="\n><font color=\"comment\">模块：CRM系统的门店联系人发给派单系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlNotes/index");
                break;
            case "contVir"://虚拟合约同步
                $html.="\n><font color=\"comment\">模块：CRM系统的虚拟合约发给派单系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlNotes/index");
                break;
            case "contFile"://虚拟合约同步
                $html.="\n><font color=\"comment\">模块：CRM系统的合约文件发给派单系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlNotes/index");
                break;
            case "cont":
                $html.="\n><font color=\"comment\">模块：门户网站的合同审核发给CRM系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlReceive/index");
                break;
            case "rpt":
                $html.="\n><font color=\"comment\">模块：门户网站的报价审核发给CRM系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlReceive/index");
                break;
            case "pro":
                $html.="\n><font color=\"comment\">模块：门户网站的变更审核发给CRM系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlReceive/index");
                break;
            case "virPro":
                $html.="\n><font color=\"comment\">模块：门户网站的变更审核发给CRM系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlReceive/index");
                break;
            case "call":
                $html.="\n><font color=\"comment\">模块：门户网站的呼叫审核发给CRM系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlReceive/index");
                break;
            case "setFree":
                $html.="\n><font color=\"comment\">模块：派单系统的首次等内容发送给CRM系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlReceive/index");
                break;
            case "setPerPWD":
                $html.="\n><font color=\"comment\">模块：派单系统的联系人设置密码发送给CRM系统</font>";
                $lbsUrl= self::getAbsoluteUrl("curlReceive/index");
                break;
            default:
                $html.="\n><font color=\"comment\">模块：{$info_type}</font>";
                $lbsUrl= self::getAbsoluteUrl("curlReceive/index");
        }
        $html.="\n><font color=\"comment\">{$message}</font>";
        $html.="\n><font color=\"comment\">{$lbsUrl}</font>";
        $data=array(
            "msgtype"=>"markdown",
            "markdown"=>array(
                "content"=>$html,
                //"mentioned_mobile_list"=>$phoneList
            )
        );
        self::sendWeChatCurlForPost($weChatUrl,$data);
    }

    public function saveCurlToApi(){
        if($this->status_type=="P"){//待处理时分段
            if(isset($this->data["data"])){
                $countArr = is_array($this->data["data"])?$this->data["data"]:json_decode($this->data["data"],true);
                $count = count($countArr);
                if($count>self::$maxCount){
                    $dataContent = $this->data;
                    $page=ceil($count/self::$maxCount);
                    for ($i=0;$i<$page;$i++){
                        $start = $i*self::$maxCount;
                        $data=array_slice($countArr,$start,self::$maxCount);
                        $dataContent["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
                        $data=json_encode($dataContent,JSON_UNESCAPED_UNICODE);
                        $this->insertAPICURL($data);
                    }
                }else{
                    $this->insertAPICURL($this->data_content);
                }
            }else{
                $this->insertAPICURL($this->data_content);
            }
        }elseif(in_array($this->status_type,array("I","C","E"))){
            $this->insertAPICURL($this->data_content);
        }
    }

    public function sendCurlForPost($url,$data,$errorBool=false){
        $rtn = array('outData'=>'', 'status'=>false,'message'=>'404');
        $data_string = is_array($data)?json_encode($data,JSON_UNESCAPED_UNICODE):$data;
        $token = $this->getComputeKey();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Content-Length:'.strlen($data_string),
            'token:'.$token,
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if(!empty($this->timerOut)){
            //设置连接超时时间为5秒
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timerOut);
            // 设置最大执行时间为10秒
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timerOut);
        }
        $out = curl_exec($ch);
        $this->out_content = $out;
        $logMessage="CURL内容\r\n";
        $logMessage.="请求地址:".$url;
        $logMessage.="\r\n";
        $logMessage.="请求参数:".$data_string;
        $logMessage.="\r\n";
        $logMessage.="返回参数:".$out;
        $logMessage.="\r\n";
        if ($out!==false) {
            $rtn['message'] = strip_tags($out);
            $json = json_decode($out, true);
            if(is_array($json)&&key_exists("code",$json)){
                if($json['code']==200){
                    $rtn["message"] = isset($json["msg"])?$json["msg"]:"未知";
                    $rtn["status"] = true;
                    $rtn["outData"] = $json;
                    if($this->printBool){
                        Yii::log($logMessage,CLogger::LEVEL_ERROR,'application');
                    }
                }else{
                    $rtn["message"] = isset($json["msg"])?$json["msg"]:"失败";
                    $rtn["outData"] = $json;
                    Yii::log($logMessage,CLogger::LEVEL_ERROR,'application');
                    if($errorBool){
                        throw new CHttpException("派单系统异常",$logMessage);
                    }
                }
            }else{
                $rtn["message"] = is_array($json)&&isset($json["msg"])?$json["msg"]:"失败";
                Yii::log($logMessage,CLogger::LEVEL_ERROR,'application');
                if($errorBool){
                    throw new CHttpException("派单系统异常",$logMessage);
                }
            }
        }else{
            if($errorBool){
                throw new CHttpException("派单系统异常","流程API异常:\r\n".$logMessage);
            }
        }
        return $rtn;
    }

    public static function sendWeChatCurlForPost($url,$data){
        $data_string = is_array($data)?json_encode($data,JSON_UNESCAPED_UNICODE):$data;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Content-Length:'.strlen($data_string),
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_exec($ch);
    }

    public function sendCurlForGet($url,$errorBool=false){
        $rtn = array('outData'=>'', 'status'=>false,'message'=>'404');
        $token = $this->getComputeKey();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'token:'.$token,
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if(!empty($this->timerOut)){
            //设置连接超时时间为5秒
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timerOut);
            // 设置最大执行时间为10秒
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timerOut);
        }
        $out = curl_exec($ch);
        $this->out_content = $out;
        $logMessage="CURL内容\r\n";
        $logMessage.="请求地址:".$url;
        $logMessage.="\r\n";
        $logMessage.="返回参数:".$out;
        $logMessage.="\r\n";
        if ($out!==false) {
            $rtn['message'] = strip_tags($out);
            $json = json_decode($out, true);
            if(is_array($json)&&key_exists("code",$json)){
                if($json['code']==200){
                    $rtn["message"] = isset($json["msg"])?$json["msg"]:"未知";
                    $rtn["status"] = true;
                    $rtn["outData"] = $json;
                    if($this->printBool){
                        Yii::log($logMessage,CLogger::LEVEL_ERROR,'application');
                    }
                }else{
                    $rtn["message"] = isset($json["msg"])?$json["msg"]:"失败";
                    $rtn["outData"] = $json;
                    Yii::log($logMessage,CLogger::LEVEL_ERROR,'application');
                }
            }else{
                $rtn["message"] = is_array($json)&&isset($json["msg"])?$json["msg"]:"失败";
                Yii::log($logMessage,CLogger::LEVEL_ERROR,'application');
                if($errorBool){
                    throw new CHttpException("派单系统异常",$logMessage);
                }
            }
        }else{
            if($errorBool){
                throw new CHttpException("派单系统异常","流程API异常:\r\n".$logMessage);
            }
        }
        return $rtn;
    }


    protected static function getDistrictStrByKey($key,$str="area_name"){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("sales{$suffix}.sal_national_area")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $str=="*"?$row:$row[$str];
        }else{
            return "";
        }
    }

    protected static function getOfficeStrByKey($key,$str="name") {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("hr{$suffix}.hr_office")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if ($key) {
            return $row[$str];
        }
        return $str=="name"?"本部":"";
    }

    protected static function getCustClassStrByKey($key,$str="name"){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("swoper{$suffix}.swo_nature_type")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return $key;
        }
    }

    protected static function getServiceTypeStrByKey($key,$str="description"){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("swoper{$suffix}.swo_customer_type")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return $key;
        }
    }

    protected static function getUServiceTypeByIDChar($id_char,$str="u_code"){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("sales{$suffix}.sal_service_type")
            ->where("id_char=:id_char",array(":id_char"=>$id_char))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return "";
        }
    }

    protected static function getPayWeekStrByKey($key,$str="description"){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("swoper{$suffix}.swo_payweek")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return $key;
        }
    }

    protected static function getPayTypeStrByKey($key,$str="name"){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("sales{$suffix}.sal_pay")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return $key;
        }
    }

    protected static function getEmployeeStrByKey($str,$key){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("hr$suffix.hr_employee")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return $str=="code"?"403527":$key;
        }
    }

    protected static function getEmployeeStrByUsername($str,$username){
        $employee_id = self::getEmployeeIDByUserName($username);
        return self::getEmployeeStrByKey($str,$employee_id);
    }

    protected static function getEmployeeIDByUserName($username){
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select employee_id from hr$suffix.hr_binding WHERE user_id='{$username}'";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if($row){
            return $row["employee_id"];
        }else{
            return 0;
        }
    }

    protected static function getYewudaleiStrByKey($key,$str="name"){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("sales{$suffix}.sal_yewudalei")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return $key;
        }
    }

    protected static function getStopRemarkByKey($key,$str="name"){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("sales{$suffix}.sal_cont_str")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return $key;
        }
    }

    protected static function getMhCodeByLbsMainKey($lbs_main,$str="mh_code"){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("sales{$suffix}.sal_main_lbs")
            ->where("id=:id",array(":id"=>$lbs_main))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return $lbs_main;
        }
    }


    public static function getAbsoluteUrl($url,$arr=array()){
        if(Yii::app()->getComponent('user')===null){
            $lbsUrl= Yii::app()->params['webroot']."/".$url;
            if(!empty($arr)){
                $queryString = http_build_query($arr);
                $lbsUrl.="?".$queryString;
            }
        }else{
            $lbsUrl= Yii::app()->createAbsoluteUrl($url,$arr);
        }

        return $lbsUrl;
    }
}
