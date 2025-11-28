<?php

class ImportClueStoreForm extends ImportClientStoreForm
{
    protected $pinyin;
    protected $eveList = array(
        array("name"=>"门店类别","key"=>"clue_type","fun"=>"valClueType","requite"=>true),
        array("name"=>"线索编号","key"=>"clue_code","fun"=>"valCode","requite"=>false),
        array("name"=>"门店名称","key"=>"store_name","fun"=>"valStoreName","requite"=>true),
        array("name"=>"跟进销售的员工编号","key"=>"create_staff","fun"=>"valEmployee","requite"=>true),
        array("name"=>"行业类别","key"=>"cust_class","fun"=>"valCustClass","requite"=>true),
        array("name"=>"业务管理单元","key"=>"city","fun"=>"valCity","requite"=>true),
        array("name"=>"行政区域","key"=>"district","fun"=>"valDistrict","requite"=>true),
        array("name"=>"业务大类","key"=>"yewudalei","fun"=>"valYewudalei","requite"=>false),
        array("name"=>"税号","key"=>"tax_id","fun"=>"","requite"=>false),
        array("name"=>"开票地址","key"=>"invoice_address","fun"=>"","requite"=>false),
        array("name"=>"开票开户行","key"=>"invoice_number","fun"=>"","requite"=>false),
        array("name"=>"开票账号","key"=>"invoice_user","fun"=>"","requite"=>false),
        array("name"=>"开票备注","key"=>"invoice_rmk","fun"=>"","requite"=>false),
        array("name"=>"开票抬头","key"=>"invoice_header","fun"=>"valInvoice","requite"=>false),
        array("name"=>"门店简称","key"=>"store_full_name","fun"=>"","requite"=>false),
        array("name"=>"办事处","key"=>"office_id","fun"=>"valOffice","requite"=>false),
        array("name"=>"详细地址","key"=>"address","fun"=>"","requite"=>false),
        array("name"=>"联系人名称","key"=>"cust_person","fun"=>"","requite"=>false),
        array("name"=>"联系人电话","key"=>"cust_tel","fun"=>"","requite"=>false),
        array("name"=>"联系人邮箱","key"=>"cust_email","fun"=>"","requite"=>false),
        array("name"=>"联系人职务","key"=>"cust_person_role","fun"=>"","requite"=>false),
        array("name"=>"面积","key"=>"area","fun"=>"valNumber","requite"=>false),
        array("name"=>"门店备注","key"=>"store_remark","fun"=>"","requite"=>false),
    );

    protected function valCode(&$data,$keyStr,$item){
        $clueCode = key_exists($keyStr,$data)?$data[$keyStr]:'';
        if(!empty($clueCode)){
            $row = Yii::app()->db->createCommand()->select("id,clue_code,clue_type,table_type")->from("sal_clue")
                ->where("clue_code=:clue_code",array(":clue_code"=>$clueCode))->queryRow();
            if(!$row){
                $this->status="E";
                $this->message=$item['name']."没找到({$clueCode})";
            }else{
                if($row["table_type"]==2){
                    $this->status="E";
                    $this->message=$item['name'].",该线索已转化成客户，无法导入客户门店({$clueCode})";
                }else{
                    if($row["clue_type"]!=$data["clue_type"]){
                        $this->status="E";
                        $this->message="门店类别(".CGetName::getClueTypeStr($data["clue_type"]).")与客户类别（".CGetName::getClueTypeStr($row["clue_type"])."）不一致";
                    }else{
                        $data["clue_id"]=$row["id"];
                    }
                }
            }
        }else{
            $data["clue_id"]=null;
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
            $data["entry_date"]=$this->req_dt;
            $data["cust_name"]=$data["store_name"];
            $data["full_name"]=$data["store_full_name"];
            $data["rec_employee_id"]=$data["create_staff"];
            $data["clue_remark"]="门店导入自动生成";
            $full_name = !empty($data['full_name'])?$data['full_name']:$data['cust_name'];
            $computeList = CGetName::computeClueCode($this->pinyin,$full_name);
            $data["clue_code"]=$computeList["clue_code"];
            $data["abbr_code"]=$computeList["abbr_code"];
            $saveKey=array(
                'clue_type','cust_name','full_name','clue_code','abbr_code','entry_date','rec_employee_id',
                'cust_class','cust_class_group','city','address','district','yewudalei',
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
            $saveList["table_type"]=1;
            $saveList["lcu"]=$this->username;
            Yii::app()->db->createCommand()->insert("sal_clue",$saveList);
            $clue_id = Yii::app()->db->getLastInsertID();
            Yii::app()->db->createCommand()->insert("sal_clue_history",array(
                "table_id"=>$clue_id,
                "table_type"=>1,
                "history_type"=>1,
                "history_html"=>"<span>门店导入自动生成线索，导入id：{$this->id}</span>",
                "lcu"=>$this->username,
            ));
            $data["clue_id"] = $clue_id;
            $clueModel = new ClueForm("view");
            $clueModel->retrieveData($clue_id);
            ClueUAreaForm::saveUAreaData($clueModel->id,$clueModel->city,$this->username);
            ClientPersonForm::saveUPersonDataByClueModel($clueModel,$this->username);
        }
    }

    protected function saveOneData($data){
        $this->computeClueID($data);
        $this->computeInvoiceID($data);
        $this->computeStoreCode($data);
        $saveKey=array(
            'clue_id','clue_type','store_code','store_name','store_full_name','create_staff','yewudalei',
            'cust_class_group','cust_class','city','office_id','address','district','invoice_id',
            'cust_person','cust_tel','cust_email','cust_person_role','area','store_remark'
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
            "history_html"=>"<span>门店导入，导入id：{$this->id}</span>",
            "lcu"=>$this->username,
        ));
        if(!empty($saveList['cust_person'])&&!empty($saveList['cust_tel'])){
            Yii::app()->db->createCommand()->insert("sal_clue_person",array(
                "clue_id"=>$data["clue_id"],
                "clue_store_id"=>$clue_store_id,
                "cust_person"=>$saveList['cust_person'],
                "cust_tel"=>$saveList['cust_tel'],
                "lcu"=>$this->username,
                "lcd"=>$this->req_dt,
            ));
            $cust_id = Yii::app()->db->getLastInsertID();
            Yii::app()->db->createCommand()->update("sal_clue_person",array(
                "person_code"=>ClientPersonForm::computeCodeX($saveList['clue_id'],$clue_store_id,$cust_id),
            ),"id=:id",array(":id"=>$cust_id));
        }
    }
}
