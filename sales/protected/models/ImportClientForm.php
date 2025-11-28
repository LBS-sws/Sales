<?php

class ImportClientForm extends ImportForm
{
    protected $pinyin;
    protected $eveList = array(
        array("name"=>"客户类别","key"=>"clue_type","fun"=>"valClueType","requite"=>true),
        array("name"=>"服务类型","key"=>"service_type","fun"=>"valServiceType","requite"=>false,"default"=>"IA客户"),
        array("name"=>"客户名称","key"=>"cust_name","fun"=>"valClueName","requite"=>true),
        array("name"=>"客户简称","key"=>"full_name","fun"=>"","requite"=>false),
        array("name"=>"客户编号","key"=>"clue_code","fun"=>"valCode","requite"=>false),
        array("name"=>"客户录入时间","key"=>"entry_date","fun"=>"valDate","requite"=>true),
        array("name"=>"跟进销售的员工编号","key"=>"rec_employee_id","fun"=>"valEmployee","requite"=>true),
        array("name"=>"业务大类","key"=>"yewudalei","fun"=>"valYewudalei","requite"=>false),
        array("name"=>"是否集团客户","key"=>"group_bool","fun"=>"valGroupBool","requite"=>false),
        array("name"=>"重点客户","key"=>"cust_vip","fun"=>"valVip","requite"=>false),
        array("name"=>"行业类别","key"=>"cust_class","fun"=>"valCustClass","requite"=>false),
        array("name"=>"城市","key"=>"city","fun"=>"valCity","requite"=>true),
        array("name"=>"详细地址","key"=>"address","fun"=>"","requite"=>false),
        array("name"=>"区域","key"=>"district","fun"=>"valDistrict","requite"=>true),
        array("name"=>"街道","key"=>"street","fun"=>"","requite"=>false),
        array("name"=>"经度","key"=>"latitude","fun"=>"valEmptyNumber","requite"=>false),
        array("name"=>"纬度","key"=>"longitude","fun"=>"valEmptyNumber","requite"=>false),
        array("name"=>"派单系统客户id","key"=>"u_id","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"派单系统客户关联主要负责人id","key"=>"u_staff_id","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"派单系统客户关联城市id","key"=>"u_area_id","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"派单系统客户关联联系人id","key"=>"u_person_id","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"派单系统客户关联联系人分组id","key"=>"u_group_id","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"联系人编号","key"=>"person_code","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"联系人名称","key"=>"cust_person","fun"=>"","requite"=>false),
        array("name"=>"联系人电话","key"=>"cust_tel","fun"=>"","requite"=>false),
        array("name"=>"联系人邮箱","key"=>"cust_email","fun"=>"","requite"=>false),
        array("name"=>"联系人职务","key"=>"cust_person_role","fun"=>"","requite"=>false),
        array("name"=>"联系人地址","key"=>"cust_address","fun"=>"","requite"=>false),
        array("name"=>"面积","key"=>"area","fun"=>"valNumber","requite"=>false),
        array("name"=>"客户备注","key"=>"clue_remark","fun"=>"","requite"=>false),
        array("name"=>"其它销售","key"=>"u_staff_list","fun"=>"valUStaff","requite"=>false),
        array("name"=>"其它联系人","key"=>"u_person_list","fun"=>"valUPerson","requite"=>false),
        array("name"=>"其它城市","key"=>"u_area_list","fun"=>"valUArea","requite"=>false),
    );

    protected function valCode(&$data,$keyStr,$item){
        $clueCode = key_exists("clue_code",$data)?$data["clue_code"]:'';
        if(empty($clueCode)){
            $full_name = !empty($data['full_name'])?$data['full_name']:$data['cust_name'];
            $computeList = CGetName::computeClueCode($this->pinyin,$full_name);
            $data["clue_code"]=$computeList["clue_code"];
            $data["abbr_code"]=$computeList["abbr_code"];
        }else{
            $row = Yii::app()->db->createCommand()->select("id,clue_code")->from("sal_clue")
                ->where("clue_code=:clue_code",array(":clue_code"=>$clueCode))->queryRow();
            if($row){
                $this->status="E";
                $this->message=$item['name']."已存在({$row['clue_code']})";
            }
        }
    }

    protected function saveBodyList(){
        if(!empty($this->bodyList)){
            $phpExcelPath = Yii::getPathOfAlias('ext.pinyin');
            include($phpExcelPath . DIRECTORY_SEPARATOR . 'Autoloader.php');
            $this->pinyin = new Pinyin(); // 默认
            foreach ($this->bodyList as $i=>$row){
                $this->status="P";
                $data=array();
                foreach ($this->eveList as $eveRow){
                    $key = $this->keyList[$eveRow['key']];
                    $data[$eveRow['key']]=$row[$key];
                }
                $this->validateRowData($data);
                if($this->status!="P"){
                    $this->errorList[]=array("list"=>$row,"message"=>$this->message);
                }else{
                    $this->successList[]=$i+5;
                    $this->saveOneData($data);
                }
            }
        }
    }

    protected function saveOneData($data){
        $saveKey=array(
            'clue_type','service_type','cust_name','full_name','clue_code','abbr_code','entry_date','rec_employee_id','yewudalei','group_bool',
            'cust_vip','cust_class','cust_class_group','city','address','district','street','latitude','longitude',
            'u_id','ka_id','u_group_id','cust_person','cust_tel','cust_email','cust_person_role','cust_address','area','clue_remark',
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
        $saveList["table_type"]=2;
        $saveList["lcu"]=$this->username;
        Yii::app()->db->createCommand()->insert("sal_clue",$saveList);
        $clue_id = Yii::app()->db->getLastInsertID();
        Yii::app()->db->createCommand()->insert("sal_clue_history",array(
            "table_id"=>$clue_id,
            "table_type"=>1,
            "history_type"=>1,
            "history_html"=>"<span>派单数据导入，导入id：{$this->id}</span>",
            "lcu"=>$this->username,
        ));
        Yii::app()->db->createCommand()->insert("sal_clue_u_area",array(
            "clue_id"=>$clue_id,
            "city_code"=>$saveList['city'],
            "city_type"=>1,
            "u_id"=>!empty($data['u_area_id'])?$data['u_area_id']:null,
            "lcu"=>$this->username,
            "lcd"=>$this->req_dt,
        ));
        Yii::app()->db->createCommand()->insert("sal_clue_u_staff",array(
            "clue_id"=>$clue_id,
            "employee_id"=>$saveList['rec_employee_id'],
            "employee_type"=>1,
            "u_id"=>!empty($data['u_staff_id'])?$data['u_staff_id']:null,
            "lcu"=>$this->username,
            "lcd"=>$this->req_dt,
        ));
        if(!empty($saveList['cust_person'])&&!empty($saveList['cust_tel'])){
            Yii::app()->db->createCommand()->insert("sal_clue_person",array(
                "clue_id"=>$clue_id,
                "clue_store_id"=>0,
                "person_code"=>$data['person_code'],
                "person_pws"=>empty($data['person_code'])?null:1,
                "cust_person"=>$saveList['cust_person'],
                "cust_tel"=>$saveList['cust_tel'],
                "u_id"=>!empty($data['u_person_id'])?$data['u_person_id']:null,
                "u_group_id"=>!empty($data['u_group_id'])?$data['u_group_id']:null,
                "lcu"=>$this->username,
                "lcd"=>$this->req_dt,
            ));
            $cust_id = Yii::app()->db->getLastInsertID();
            if(empty($data['person_code'])){
                Yii::app()->db->createCommand()->update("sal_clue_person",array(
                    "person_code"=>ClientPersonForm::computeCodeX($clue_id,0,$cust_id),
                ),"id=:id",array(":id"=>$cust_id));
            }
        }
        if(!empty($data["uAreaData"])){
            foreach ($data["uAreaData"] as $uArea){
                $uArea["clue_id"]=$clue_id;
                $uArea["city_type"]=0;
                $uArea["lcu"]=$this->username;
                $uArea["lcd"]=$this->req_dt;
                Yii::app()->db->createCommand()->insert("sal_clue_u_area",$uArea);
            }
        }
        if(!empty($data["uStaffData"])){
            foreach ($data["uStaffData"] as $uStaff){
                $uStaff["clue_id"]=$clue_id;
                $uStaff["employee_type"]=0;
                $uStaff["lcu"]=$this->username;
                $uStaff["lcd"]=$this->req_dt;
                Yii::app()->db->createCommand()->insert("sal_clue_u_staff",$uStaff);
            }
        }
        if(!empty($data["uPersonData"])){
            foreach ($data["uPersonData"] as $uPerson){
                $uPerson["clue_id"]=$clue_id;
                $uPerson["clue_store_id"]=0;
                $uPerson["person_pws"]=empty($uPerson['person_code'])?null:1;
                $uPerson["lcu"]=$this->username;
                $uPerson["lcd"]=$this->req_dt;
                Yii::app()->db->createCommand()->insert("sal_clue_person",$uPerson);
                $cust_id = Yii::app()->db->getLastInsertID();
                if(empty($uPerson['person_code'])){
                    Yii::app()->db->createCommand()->update("sal_clue_person",array(
                        "person_code"=>ClientPersonForm::computeCodeX($clue_id,0,$cust_id),
                    ),"id=:id",array(":id"=>$cust_id));
                }
            }
        }
    }
}
