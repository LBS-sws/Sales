<?php

class CurlNotesByVirPro extends CurlNotesByVir {
    public function sendAllVirByProID($pro_id){//
	//sal_contpro_virtual
        $suffix = Yii::app()->params['envSuffix'];
        $virProRows = Yii::app()->db->createCommand()->select("vir_id")->from("sales{$suffix}.sal_contpro_virtual")
            ->where("pro_id=:id",array(":id"=>$pro_id))->queryAll();
		$idList = array();
		if($virProRows){
			foreach($virProRows as $virProRow){
				$idList[]=$virProRow['vir_id'];
			}
		}
		$virIDs = implode(',',$idList);
        $this->sendAllVirByIDsAndNew($virIDs);
        $this->sendAllVirByIDsAndUpdate($virIDs);
    }

    public function sendAllVirByIDsAndNew($virIDs){
        $suffix = Yii::app()->params['envSuffix'];
        $addVirRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
            ->where("id in ({$virIDs}) and u_id is null")->queryAll();
        if($addVirRows){
            $uCurlModel = new CurlNotesModel();
            $uCurlModel->data=array(
                "operation_type"=>"insert",
                "data"=>array(),
            );
            $data=array();
            $contIDs=array();
            foreach ($addVirRows as $addVirRow){
                if(!in_array($addVirRow["cont_id"],$contIDs)){
                    $contIDs[]=$addVirRow["cont_id"];
                }
                $data[]=$this->getDataByVirRow($addVirRow);
            }
            $uCurlModel->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
            $uCurlModel->sendDataSetByAddContract();
            $uCurlModel->setOutContentByData();
            $uCurlModel->saveCurlToApi();

            $contIDs = implode(",",$contIDs);
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_file",array(
                "sent_bool"=>1,
            ),"cont_id in ({$contIDs}) and sent_bool=0");//将合同内的附件设置为已发送
        }
    }

    public function sendAllVirByIDsAndUpdate($virIDs){
        $suffix = Yii::app()->params['envSuffix'];
        $addVirRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
            ->where("id in ({$virIDs}) and u_id is not null")->queryAll();
        if($addVirRows){
            $uCurlModel = new CurlNotesModel();
            $uCurlModel->data=array(
                "operation_type"=>"update",
                "data"=>array(),
            );
            $data=array();
            foreach ($addVirRows as $addVirRow){
                $data[]=$this->getDataByVirRow($addVirRow);
            }
            $uCurlModel->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
            $uCurlModel->sendDataSetByUpdateContract();
            $uCurlModel->setOutContentByData();
            $uCurlModel->saveCurlToApi();
        }
    }

}
