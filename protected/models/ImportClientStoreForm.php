<?php

class ImportClientStoreForm extends ImportForm
{
    protected $pinyin;
    protected $eveList = array(
        array("name"=>"门店类别","key"=>"clue_type","fun"=>"valClueType","requite"=>true),
        array("name"=>"服务类型","key"=>"service_type","fun"=>"valServiceType","requite"=>false),
        array("name"=>"客户编号","key"=>"clue_code","fun"=>"valCode","requite"=>false),
        array("name"=>"门店编号","key"=>"store_code","fun"=>"valStoreCode","requite"=>false),
        array("name"=>"门店名称","key"=>"store_name","fun"=>"valStoreName","requite"=>true),
        array("name"=>"门店简称","key"=>"store_full_name","fun"=>"","requite"=>false),
        array("name"=>"跟进销售的员工编号","key"=>"create_staff","fun"=>"valEmployee","requite"=>true),
        array("name"=>"业务大类","key"=>"yewudalei","fun"=>"valYewudalei","requite"=>false),
        array("name"=>"是否集团客户","key"=>"group_bool","fun"=>"valGroupBool","requite"=>false),
        array("name"=>"重点客户","key"=>"cust_vip","fun"=>"valVip","requite"=>false),
        array("name"=>"客户录入时间","key"=>"entry_date","fun"=>"valDate","requite"=>false),
        array("name"=>"行业类别","key"=>"cust_class","fun"=>"valCustClass","requite"=>true),
        array("name"=>"城市","key"=>"city","fun"=>"valCity","requite"=>true),
        array("name"=>"办事处","key"=>"office_id","fun"=>"valOffice","requite"=>false),
        array("name"=>"详细地址","key"=>"address","fun"=>"","requite"=>false),
        array("name"=>"区域","key"=>"district","fun"=>"valDistrict","requite"=>true),
        array("name"=>"税号","key"=>"tax_id","fun"=>"","requite"=>false),
        array("name"=>"开票地址","key"=>"invoice_address","fun"=>"","requite"=>false),
        array("name"=>"开票开户行","key"=>"invoice_number","fun"=>"","requite"=>false),
        array("name"=>"开票账号","key"=>"invoice_user","fun"=>"","requite"=>false),
        array("name"=>"开票备注","key"=>"invoice_rmk","fun"=>"","requite"=>false),
        array("name"=>"开票抬头","key"=>"invoice_header","fun"=>"valInvoice","requite"=>true),
        array("name"=>"经度","key"=>"latitude","fun"=>"valEmptyNumber","requite"=>false),
        array("name"=>"纬度","key"=>"longitude","fun"=>"valEmptyNumber","requite"=>false),
        array("name"=>"派单系统门店id","key"=>"u_id","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"派单系统门店关联联系人id","key"=>"u_person_id","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"联系人编号","key"=>"person_code","fun"=>"","requite"=>false),
        array("name"=>"联系人名称","key"=>"cust_person","fun"=>"","requite"=>false),
        array("name"=>"联系人电话","key"=>"cust_tel","fun"=>"","requite"=>false),
        array("name"=>"联系人邮箱","key"=>"cust_email","fun"=>"","requite"=>false),
        array("name"=>"联系人职务","key"=>"cust_person_role","fun"=>"","requite"=>false),
        array("name"=>"面积","key"=>"area","fun"=>"valNumber","requite"=>false),
        array("name"=>"门店备注","key"=>"store_remark","fun"=>"","requite"=>false),
        array("name"=>"其它联系人","key"=>"u_person_list","fun"=>"valUPerson","requite"=>false),
    );

    protected function valCode(&$data,$keyStr,$item){
        $clueCode = key_exists($keyStr,$data)?$data[$keyStr]:'';
        if(!empty($clueCode)){
            $row = Yii::app()->db->createCommand()->select("id,clue_type")->from("sal_clue")
                ->where("clue_code=:clue_code",array(":clue_code"=>$clueCode))->queryRow();
            if(!$row){
                $this->status="E";
                $this->message=$item['name']."没找到({$clueCode})";
            }else{
                if($row["clue_type"]!=$data["clue_type"]){
                    $this->status="E";
                    $this->message="门店类别(".CGetName::getClueTypeStr($data["clue_type"]).")与客户类别（".CGetName::getClueTypeStr($row["clue_type"])."）不一致";
                }else{
                    $data["clue_id"]=$row["id"];
                }
            }
        }else{
            $data["clue_id"]=null;
        }
    }

    protected function valStoreCode(&$data,$keyStr,$item){
        $store_code = key_exists($keyStr,$data)?$data[$keyStr]:'';
        if(!empty($store_code)){
            $row = Yii::app()->db->createCommand()->select("id")->from("sal_clue_store")
                ->where("store_code=:store_code",array(":store_code"=>$store_code))->queryRow();
            if($row){
                $this->status="E";
                $this->message=$item['name']."已存在({$store_code})";
            }
        }else{
            $data["store_code"]=null;
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

    protected function computeClueID(&$data){
        if(empty($data["clue_id"])){
            $data["entry_date"]=empty($data["entry_date"])?$this->req_dt:$data["entry_date"];
            $data["cust_name"]=$data["store_name"];
            $data["full_name"]=$data["store_full_name"];
            $data["rec_employee_id"]=$data["create_staff"];
            $data["clue_remark"]="门店导入自动生成";
            $full_name = !empty($data['full_name'])?$data['full_name']:$data['cust_name'];
            $computeList = CGetName::computeClueCode($this->pinyin,$full_name);
            $data["clue_code"]=$computeList["clue_code"];
            $data["abbr_code"]=$computeList["abbr_code"];
            $saveKey=array(
                'clue_type','service_type','cust_name','full_name','clue_code','abbr_code','entry_date','rec_employee_id','yewudalei','group_bool',
                'cust_vip','cust_class','cust_class_group','city','address','district','latitude','longitude',
                'cust_person','cust_tel','cust_email','cust_person_role','area','clue_remark',
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
            $data["clue_id"] = $clue_id;
            Yii::app()->db->createCommand()->insert("sal_clue_history",array(
                "table_id"=>$data["clue_id"],
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
                    "cust_person"=>$saveList['cust_person'],
                    "cust_tel"=>$saveList['cust_tel'],
                    "u_id"=>null,
                    "u_group_id"=>null,
                    "lcu"=>$this->username,
                    "lcd"=>$this->req_dt,
                ));
                $cust_id = Yii::app()->db->getLastInsertID();
                Yii::app()->db->createCommand()->update("sal_clue_person",array(
                    "person_code"=>ClientPersonForm::computeCodeX($clue_id,0,$cust_id),
                ),"id=:id",array(":id"=>$cust_id));
            }
        }
    }

    protected function computeInvoiceID(&$data){
        if(!empty($data["clue_id"])&&empty($data["invoice_id"])){
            if(!empty($data["invoice_header"])){
                $invoice_name=$data["store_name"]."_sys_".time();
                Yii::app()->db->createCommand()->insert("sal_clue_invoice",array(
                    "clue_id"=>$data["clue_id"],
                    "clue_type"=>$data["clue_type"],
                    "invoice_name"=>$invoice_name,
                    "city"=>$data["city"],
                    "invoice_type"=>$data["invoice_type"],
                    "invoice_header"=>$data["invoice_header"],
                    "tax_id"=>$data["tax_id"],
                    "invoice_address"=>$data["invoice_address"],
                    "invoice_number"=>$data["invoice_number"],
                    "invoice_user"=>$data["invoice_user"],
                    "invoice_rmk"=>$data["invoice_rmk"],
                    "lcu"=>$this->username,
                    "lcd"=>$this->req_dt,
                ));
                $data["invoice_id"] = Yii::app()->db->getLastInsertID();
            }
        }
    }

    protected function computeStoreCode(&$data){
        if(empty($data["store_code"])){
            $row = Yii::app()->db->createCommand()->select("count(*) as sum")
                ->from("sal_clue_store")->where("clue_id=:clue_id",array(":clue_id"=>$data["clue_id"]))->queryRow();
            $num = $row&&!empty($row["sum"])?$row["sum"]:0;
            $charNum = floor($num/1000)+65;
            $num = floor($num%1000);
            $num = "".(1000+$num);
            $num = mb_substr($num,1);
            $store_code=$data["clue_code"]."-".chr($charNum).$num;
            $data["store_code"]=$store_code;
        }
    }

    protected function saveOneData($data){
        $this->computeClueID($data);
        $this->computeInvoiceID($data);
        $this->computeStoreCode($data);
        $saveKey=array(
            'clue_id','clue_type','store_code','store_name','store_full_name','create_staff','yewudalei',
            'cust_class_group','cust_class','city','office_id','address','district','invoice_id',
            'latitude','longitude','u_id','cust_person',
            'cust_tel','cust_email','cust_person_role','area','store_remark'
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
        $saveList["lcu"]=$this->username;
        Yii::app()->db->createCommand()->insert("sal_clue_store",$saveList);
        $clue_store_id = Yii::app()->db->getLastInsertID();
        Yii::app()->db->createCommand()->insert("sal_clue_history",array(
            "table_id"=>$clue_store_id,
            "table_type"=>2,
            "history_type"=>1,
            "history_html"=>"<span>派单数据导入，导入id：{$this->id}</span>",
            "lcu"=>$this->username,
        ));
        if(!empty($saveList['cust_person'])&&!empty($saveList['cust_tel'])){
            Yii::app()->db->createCommand()->insert("sal_clue_person",array(
                "clue_id"=>$data["clue_id"],
                "clue_store_id"=>$clue_store_id,
                "person_code"=>$data['person_code'],
                "person_pws"=>empty($data['u_id'])?null:1,
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
                    "person_code"=>ClientPersonForm::computeCodeX($data["clue_id"],$clue_store_id,$cust_id),
                ),"id=:id",array(":id"=>$cust_id));
            }
        }
        if(!empty($data["uPersonData"])){
            foreach ($data["uPersonData"] as $uPerson){
                $uPerson["clue_id"]=$data["clue_id"];
                $uPerson["clue_store_id"]=$clue_store_id;
                $uPerson["person_pws"]=empty($uPerson['u_id'])?null:1;
                $uPerson["lcu"]=$this->username;
                $uPerson["lcd"]=$this->req_dt;
                Yii::app()->db->createCommand()->insert("sal_clue_person",$uPerson);
                $cust_id = Yii::app()->db->getLastInsertID();
                if(empty($uPerson['person_code'])){
                    Yii::app()->db->createCommand()->update("sal_clue_person",array(
                        "person_code"=>ClientPersonForm::computeCodeX($data["clue_id"],$clue_store_id,$cust_id),
                    ),"id=:id",array(":id"=>$cust_id));
                }
            }
        }
    }
}
