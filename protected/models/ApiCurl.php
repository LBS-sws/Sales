<?php
class ApiCurl
{
	//CRM系统同步
    public function addrecordForMH($ip, $type, $data,$lcd="",$rtn=array(),$status_type="P") {
        $rtn = empty($rtn)?array('code'=>200,'msg'=>'成功'):$rtn;
        $suffix = Yii::app()->params['envSuffix'];
        if (!empty($ip) && !empty($type) && !empty($data)) {
            try {
                $message = key_exists("msg",$rtn)?$rtn["msg"]:"成功";
                $message = mb_strlen($message)>250?mb_substr($message,0,250,'UTF-8'):$message;
                $code = key_exists("code",$rtn)?$rtn["code"]:200;
                if($status_type!="P"){
                    $status_type=$code==200?$status_type:"E";
                }
                Yii::app()->db->createCommand()->insert("datasync{$suffix}.sync_mh_api_curl",array(
                    "source"=>$ip,
                    "status_type"=>$status_type,
                    "info_type"=>$type,
                    "data_content"=>json_encode($data, JSON_UNESCAPED_UNICODE),
                    "out_content"=>json_encode($rtn, JSON_UNESCAPED_UNICODE),
                    "message"=>$message,
                    "lcu"=>"admin",
                    "lcd"=>empty($lcd)?date("Y-m-d H:i:s"):$lcd,
                ));
                if($status_type=="E"){
                    $id = Yii::app()->db->getLastInsertID();
                    CurlNotesModel::sendWeChatHint($id,$message,$type);
                }
            } catch(Exception $e) {
                $rtn = array('code'=>400,'msg'=>$e->getMessage());
            }
        } else {
            $rtn = array('code'=>400,'msg'=>'Invalid Parameter');
        }
        return $rtn;
    }
}
?>
