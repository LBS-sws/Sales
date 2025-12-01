<?php

class CurlNotesByVirBatch extends CurlNotesByVir {
    public function sendAllVirByVirIDs($virIDs){//
        $this->sendAllVirByVirIDsAndNew($virIDs);
        $this->sendAllVirByVirIDsAndUpdate($virIDs);
    }

    public function sendAllVirByVirIDsAndNew($virIDs){
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
            foreach ($addVirRows as $addVirRow){
                $data[]=$this->getDataByVirRow($addVirRow);
            }
            $uCurlModel->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
            $uCurlModel->sendDataSetByAddContract();
            $uCurlModel->setOutContentByData();
            $uCurlModel->saveCurlToApi();
        }
    }

    public function sendAllVirByVirIDsAndUpdate($virIDs){
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
