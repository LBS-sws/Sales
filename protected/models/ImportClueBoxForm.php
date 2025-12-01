<?php

class ImportClueBoxForm extends ImportClientForm
{
    protected $pinyin;
    protected $eveList = array(
        array("name"=>"客户名称","key"=>"cust_name","fun"=>"valClueName","requite"=>true),
        array("name"=>"线索类别","key"=>"clue_type","fun"=>"valClueType","requite"=>true),
        array("name"=>"线索录入时间","key"=>"entry_date","fun"=>"valDate","requite"=>true),
        array("name"=>"业务管理单元","key"=>"city","fun"=>"valCity","requite"=>true),
        array("name"=>"行政区域","key"=>"district","fun"=>"valDistrict","requite"=>false),
        array("name"=>"行业类别","key"=>"cust_class","fun"=>"valCustClass","requite"=>false),
        array("name"=>"客户简称","key"=>"full_name","fun"=>"valCode","requite"=>false),
        array("name"=>"服务类型","key"=>"service_type","fun"=>"valServiceType","requite"=>false),
        array("name"=>"业务大类","key"=>"yewudalei","fun"=>"valYewudalei","requite"=>false),
        array("name"=>"是否集团客户","key"=>"group_bool","fun"=>"valGroupBool","requite"=>false),
        array("name"=>"重点客户","key"=>"cust_vip","fun"=>"valVip","requite"=>false),
        array("name"=>"客户来源","key"=>"clue_source","fun"=>"valSource","requite"=>false),
        array("name"=>"详细地址","key"=>"address","fun"=>"","requite"=>false),
        array("name"=>"街道","key"=>"street","fun"=>"","requite"=>false),
        array("name"=>"联系人名称","key"=>"cust_person","fun"=>"","requite"=>false),
        array("name"=>"联系人电话","key"=>"cust_tel","fun"=>"","requite"=>false),
        array("name"=>"联系人邮箱","key"=>"cust_email","fun"=>"","requite"=>false),
        array("name"=>"联系人职务","key"=>"cust_person_role","fun"=>"","requite"=>false),
        array("name"=>"联系人地址","key"=>"cust_address","fun"=>"","requite"=>false),
        array("name"=>"面积","key"=>"area","fun"=>"valNumber","requite"=>false),
        array("name"=>"客户备注","key"=>"clue_remark","fun"=>"","requite"=>false),
    );

    protected function saveOneData($data){
        $saveKey=array(
            'clue_type','service_type','cust_name','full_name','clue_code','abbr_code','entry_date','yewudalei','group_bool','clue_source',
            'cust_vip','cust_class','cust_class_group','city','address','district','street','cust_person','cust_tel','cust_email','cust_person_role','cust_address','area','clue_remark',
        );
        $saveList=array();
        foreach ($saveKey as $key){
            if(key_exists($key,$data)){
                $saveList[$key]=$data[$key];
            }
        }
        if(key_exists("area",$saveList)&&empty($saveList["area"])){
            $saveList["area"]=null;
        }
        $saveList["report_id"]=$this->id;
        $saveList["rec_type"]=2;
        $saveList["table_type"]=1;
        $saveList["box_type"]=1;
        $saveList["lcu"]=$this->username;
        Yii::app()->db->createCommand()->insert("sal_clue",$saveList);
        $id = Yii::app()->db->getLastInsertID();
        Yii::app()->db->createCommand()->insert("sal_clue_history",array(
            "table_id"=>$id,
            "table_type"=>1,
            "history_type"=>1,
            "history_html"=>"<span>线索池导入，导入id：{$this->id}</span>",
            "lcu"=>$this->username,
        ));
        $clueModel = new ClueForm("view");
        $clueModel->retrieveData($id);
        ClueUAreaForm::saveUAreaData($clueModel->id,$clueModel->city,$this->username);
        ClientPersonForm::saveUPersonDataByClueModel($clueModel,$this->username);
    }
}
