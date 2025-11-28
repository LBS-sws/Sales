<?php

class CurlNotesByVirFile extends CurlNotesModel {
    public function sendVirFileByContID($cont_id){//
	//sal_contpro_virtual
        $suffix = Yii::app()->params['envSuffix'];
        $fileRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_file")
            ->where("cont_id=:cont_id and sent_bool=0 and phy_path_name like 'CRM%'",array(":cont_id"=>$cont_id))->queryAll();
        if(!empty($fileRows)){
            $virRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
                ->where("cont_id=:cont_id and u_id is not null",array(":cont_id"=>$cont_id))->queryAll();
            if($virRows){
                $sendData=array();
                foreach ($fileRows as $fileRow){
                    $temp=array(
                        "lbs_id"=>$fileRow["id"],
                        "contract_number"=>"",
                        "contract_file_url"=>$fileRow["phy_path_name"]."/".$fileRow["phy_file_name"],
                        "create_time"=>$fileRow["lcd"],
                        "create_uid"=>self::getEmployeeStrByUsername("code",$fileRow["lcu"]),
                    );
                    foreach ($virRows as $virRow){
                        $temp["contract_number"]=$virRow["vir_code"];
                        $sendData[]=$temp;
                    }
                }

                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_file",array(
                    "sent_bool"=>1,
                ),"cont_id=:cont_id and sent_bool=0",array(":cont_id"=>$cont_id));
                $uCurlModel = new CurlNotesModel();
                $uCurlModel->data=array(
                    "data"=>json_encode($sendData,JSON_UNESCAPED_UNICODE),
                );
                $uCurlModel->sendDataSetByAddContractFile();
                $uCurlModel->setOutContentByData();
                $uCurlModel->saveCurlToApi();
            }
        }
    }

    public function sendAllVirByProID($pro_id){//
	//sal_contpro_virtual
        $suffix = Yii::app()->params['envSuffix'];
        $proRow = Yii::app()->db->createCommand()->select("cont_id")->from("sales{$suffix}.sal_contpro")
            ->where("id=:id",array(":id"=>$pro_id))->queryRow();
        if(!$proRow){
            return false;
        }
        $row = Yii::app()->db->createCommand()->select("id")
            ->from("sales{$suffix}.sal_contract_history")
            ->where("table_type=6 and table_id=:id and history_type=10",array(":id"=>$pro_id))->queryRow();
        if(!$row){//操作未生效不发送
            return false;
        }
        $fileRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contpro_file")
            ->where("pro_id=:pro_id and sent_bool=0 and phy_path_name like 'CRM%'",array(":pro_id"=>$pro_id))->queryAll();
        if(!empty($fileRows)){
            $virRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contpro_virtual")
                ->where("pro_vir_type=1 and pro_id=:pro_id and u_id is not null",array(":pro_id"=>$pro_id))->queryAll();
            if($virRows){
                $sendData=array();
                foreach ($fileRows as $fileRow){
                    $fileTemp = $fileRow;
                    $fileTemp["sent_bool"]=1;
                    unset($fileTemp["id"]);
                    unset($fileTemp["pro_id"]);
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_file",$fileTemp);
                    $temp=array(
                        "lbs_id"=>$fileRow["id"],
                        "contract_number"=>"",
                        "contract_file_url"=>$fileRow["phy_path_name"]."/".$fileRow["phy_file_name"],
                        "create_time"=>$fileRow["lcd"],
                        "create_uid"=>self::getEmployeeStrByUsername("code",$fileRow["lcu"]),
                    );
                    foreach ($virRows as $virRow){
                        $temp["contract_number"]=$virRow["vir_code"];
                        $sendData[]=$temp;
                    }
                }

                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contpro_file",array(
                    "sent_bool"=>1,
                ),"pro_id=:pro_id and sent_bool=0",array(":pro_id"=>$pro_id));
                $uCurlModel = new CurlNotesModel();
                $uCurlModel->data=array(
                    "data"=>json_encode($sendData,JSON_UNESCAPED_UNICODE),
                );
                $uCurlModel->sendDataSetByAddContractFile();
                $uCurlModel->setOutContentByData();
                $uCurlModel->saveCurlToApi();
            }
        }
    }

    public function sendVirFileByBatchId($batch_id){//
	//sal_contpro_virtual
        $suffix = Yii::app()->params['envSuffix'];
        $batchRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_virtual_batch")
            ->where("id=:id",array(":id"=>$batch_id))->queryRow();
        if(!$batchRow){
            return false;
        }
        $row = Yii::app()->db->createCommand()->select("id")
            ->from("sales{$suffix}.sal_contract_history")
            ->where("table_type=8 and table_id=:id and history_type=10",array(":id"=>$batch_id))->queryRow();
        if(!$row){//操作未生效不发送
            return false;
        }
        $fileRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_virtual_batch_file")
            ->where("vir_batch_id=:vir_batch_id and sent_bool=0 and phy_path_name like 'CRM%'",array(":vir_batch_id"=>$batch_id))->queryAll();
        if(!empty($fileRows)){
            $virRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
                ->where("id in ({$batchRow['vir_id_text']}) and u_id is not null")->queryAll();
            if($virRows){
                $sendData=array();
                foreach ($fileRows as $fileRow){
                    $temp=array(
                        "lbs_id"=>$fileRow["id"],
                        "contract_number"=>"",
                        "contract_file_url"=>$fileRow["phy_path_name"]."/".$fileRow["phy_file_name"],
                        "create_time"=>$fileRow["lcd"],
                        "create_uid"=>self::getEmployeeStrByUsername("code",$fileRow["lcu"]),
                    );
                    foreach ($virRows as $virRow){
                        $temp["contract_number"]=$virRow["vir_code"];
                        $sendData[]=$temp;
                    }
                }

                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_virtual_batch_file",array(
                    "sent_bool"=>1,
                ),"vir_batch_id=:vir_batch_id and sent_bool=0",array(":vir_batch_id"=>$batch_id));
                $uCurlModel = new CurlNotesModel();
                $uCurlModel->data=array(
                    "data"=>json_encode($sendData,JSON_UNESCAPED_UNICODE),
                );
                $uCurlModel->sendDataSetByAddContractFile();
                $uCurlModel->setOutContentByData();
                $uCurlModel->saveCurlToApi();
            }
        }
    }

    public function sendAllVirFileByOldData(){//
        //sal_contpro_virtual
        $suffix = Yii::app()->params['envSuffix'];
        $fileRows = Yii::app()->db->createCommand()->select("a.*")->from("sales{$suffix}.sal_contract_file a")
            ->leftJoin("sales{$suffix}.sal_contract b","a.cont_id=b.id")
            ->where("b.cont_status>=10 and a.sent_bool=0 and a.phy_path_name like 'CRM%'")
            ->order("a.cont_id asc")->queryAll();
        if(!empty($fileRows)){
            $updateIDs = array();
            $cont_id=0;
            $virRows=false;
            $sendData=array();
            foreach ($fileRows as $fileRow){
                $boolRow = Yii::app()->db->createCommand()->select("id")
                    ->from("sales{$suffix}.sal_contract_history")
                    ->where("table_type=5 and table_id=:id and history_type=10",array(":id"=>$fileRow["cont_id"]))->queryRow();
                if(!$boolRow){
                    continue;//合约未生效不发送
                }
                $updateIDs[]=$fileRow["id"];
                if($cont_id!=$fileRow["cont_id"]){
                    $cont_id = $fileRow["cont_id"];
                    $virRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
                        ->where("cont_id=:cont_id and u_id is not null",array(":cont_id"=>$cont_id))->queryAll();
                }
                $temp=array(
                    "lbs_id"=>$fileRow["id"],
                    "contract_number"=>"",
                    "contract_file_url"=>$fileRow["phy_path_name"]."/".$fileRow["phy_file_name"],
                    "create_time"=>$fileRow["lcd"],
                    "create_uid"=>self::getEmployeeStrByUsername("code",$fileRow["lcu"]),
                );
                if($virRows){
                    foreach ($virRows as $virRow){
                        $temp["contract_number"]=$virRow["vir_code"];
                        $sendData[]=$temp;
                    }
                }
            }

            if(empty($sendData)){
                return false;
            }
            $updateIDs=implode(",",$updateIDs);
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_file",array(
                "sent_bool"=>1,
            ),"sent_bool=0 and id in ({$updateIDs})");
            $uCurlModel = new CurlNotesModel();
            $uCurlModel->data=array(
                "data"=>json_encode($sendData,JSON_UNESCAPED_UNICODE),
            );
            $uCurlModel->sendDataSetByAddContractFile();
            $uCurlModel->setOutContentByData();
            $uCurlModel->saveCurlToApi();
        }
    }

    //发送（且保存回传id）
    public function sendUByData($errorBool=false){
        if(!empty($this->data)){
            if(isset($this->data["data"])) {
                $countArr = is_array($this->data["data"]) ? $this->data["data"] : json_decode($this->data["data"], true);
                $count = count($countArr);
                if ($count > self::$maxCount) {
                    $dataContent = $this->data;
                    $page = ceil($count / self::$maxCount);
                    for ($i = 0; $i < $page; $i++) {
                        $start = $i * self::$maxCount;
                        $data = array_slice($countArr, $start, self::$maxCount);
                        $dataContent["data"] = json_encode($data, JSON_UNESCAPED_UNICODE);
                        $this->data_content = json_encode($dataContent, JSON_UNESCAPED_UNICODE);
                        if($i!=$page-1){//分段
                            $this->sendCurl($errorBool);
                            $this->endData();//保存返回结果
                            $this->insertAPICURL($this->data_content);
                        }
                    }
                }
            }
        }
        $this->sendCurl($errorBool);//发送数据
        $this->endData();//保存返回结果
    }

    public function endData(){
    }
}
