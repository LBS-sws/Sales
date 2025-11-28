<?php
class MhCRMCommand extends CConsoleCommand
{
    //执行门户网站待处理的审批记录
    public function actionStartMhCurl($noticeBool=true) {
        $suffix = Yii::app()->params['envSuffix'];
        $curlRow = Yii::app()->db->createCommand()->select("*")->from("datasync{$suffix}.sync_mh_api_curl")
            ->where("status_type='P'")->order("lcd asc")->queryRow();
        if($curlRow){
            Yii::app()->db->createCommand()->update("datasync{$suffix}.sync_mh_api_curl",array(
                "status_type"=>"I"
            ),"id=".$curlRow["id"]);
            $data = json_decode($curlRow['data_content'],true);
            echo "ID:{$curlRow["id"]}\n";

            switch ($curlRow['info_type']) {
                case "cont":
                    $model = new ClueContModel();
                    $list = $model->syncChangeOne($data);
                    break;
                case "rpt":
                    $model = new ClueRptModel();
                    $list = $model->syncChangeOne($data);
                    break;
                case "pro":
                    $model = new ClueProModel();
                    $list = $model->syncChangeOne($data);
                    break;
                case "virPro":
                    $model = new ClueVirProModel();
                    $list = $model->syncChangeOne($data);
                    break;
                case "setFree":
                    $model = new ContVirFreeModel();
                    $list = $model->syncChangeOne($data);
                    break;
                case "call":
                    $model = new ClueCallModel();
                    $list = $model->syncChangeOne($data);
                    break;
                case "setPerPWD":
                    $model = new CluePersonModel();
                    $list = $model->syncChangeOne($data);
                    break;
                default:
                    $list=array("code"=>400,'msg'=>"info_type error:{$curlRow['info_type']}");
            }
            $message = isset($list["msg"])?$list["msg"]:"成功";
            $list["msg"] = mb_strlen($message)>250?mb_substr($message,0,250,'UTF-8'):$message;
            $returnMsg = json_encode($list,JSON_UNESCAPED_UNICODE);
            if(isset($list["code"])&&$list["code"]==200){
                Yii::app()->db->createCommand()->update("datasync{$suffix}.sync_mh_api_curl",array(
                    "status_type"=>"C",
                    "out_content"=>$returnMsg,
                    "message"=>$list["msg"]
                ),"id=".$curlRow["id"]);
                echo "\t-Done (default)\n";
            }else{
                Yii::app()->db->createCommand()->update("datasync{$suffix}.sync_mh_api_curl",array(
                    "status_type"=>"E",
                    "out_content"=>$returnMsg,
                    "message"=>$list["msg"]
                ),"id=".$curlRow["id"]);
                echo "\t-FAIL\n";
            }
        }
    }

    //执行CRM系统发送给派单系统的记录
    public function actionSendToU($noticeBool=true) {
        $suffix = Yii::app()->params['envSuffix'];
        $curlRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_api_curl")
            ->where("status_type='P'")->order("lcd asc")->queryRow();
        if($curlRow){
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_api_curl",array(
                "status_type"=>"I",
                "lcd"=>date("Y-m-d H:i:s"),
            ),"id=".$curlRow["id"]);
            echo "ID:{$curlRow["id"]}\n";

            switch ($curlRow['info_type']) {
                case "client"://客户同步
                    $model = new CurlNotesByClient();
                    $model->data_content=$curlRow["data_content"];
                    $model->setMinUrl($curlRow["min_url"]);
                    $model->sendUByClientData();
                    break;
                case "clientPerson"://客户联系人同步
                    $model = new CurlNotesByClient();
                    $model->data_content=$curlRow["data_content"];
                    $model->setMinUrl($curlRow["min_url"]);
                    $model->sendUByClientPersonData();
                    break;
                case "clientArea"://客户归属区域同步
                    $model = new CurlNotesByClient();
                    $model->data_content=$curlRow["data_content"];
                    $model->setMinUrl($curlRow["min_url"]);
                    $model->sendUByClientAreaData();
                    break;
                case "clientStaff"://客户负责人同步
                    $model = new CurlNotesByClient();
                    $model->data_content=$curlRow["data_content"];
                    $model->setMinUrl($curlRow["min_url"]);
                    $model->sendUByClientStaffData();
                    break;
                case "store"://门店同步
                    $model = new CurlNotesByStore();
                    $model->data_content=$curlRow["data_content"];
                    $model->setMinUrl($curlRow["min_url"]);
                    $model->sendUByStoreData();
                    break;
                case "storePerson"://门店负责人同步
                    $model = new CurlNotesByStore();
                    $model->data_content=$curlRow["data_content"];
                    $model->setMinUrl($curlRow["min_url"]);
                    $model->sendUByStorePersonData();
                    break;
                case "contVir"://虚拟合约同步
                    $model = new CurlNotesByVir();
                    $model->data_content=$curlRow["data_content"];
                    $model->setMinUrl($curlRow["min_url"]);
                    $model->timerOut=0;
                    $model->sendUByVirData();
                    break;
                case "contFile"://合约文件
                    $model = new CurlNotesByVirFile();
                    $model->data_content=$curlRow["data_content"];
                    $model->setMinUrl($curlRow["min_url"]);
                    $model->timerOut=0;
                    $model->sendUByData();
                    break;
                default:
                    $model = new CurlNotesModel();
                    $model->status_type="E";
                    $model->message="info_type error:{$curlRow['info_type']}";
            }
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_api_curl",array(
                "status_type"=>$model->status_type,
                "info_url"=>$model->info_url,
                "out_content"=>$model->out_content,
                "message"=>$model->message
            ),"id=".$curlRow["id"]);
            if($model->status_type=="C"){
                echo "\t-Done (default)\n";
            }else{
                echo "\t-FAIL\n";
                CurlNotesModel::sendWeChatHint($curlRow["id"],$model->message,$curlRow['info_type']);
            }
        }
    }
}