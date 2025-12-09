<?php

class ImportClientStoreForm extends ImportForm
{
    protected $pinyin;
    
    /**
     * 缓存所有查询过的客户编码，避免重复数据库查询
     * 结构：["clue_code" => {id, clue_type, ...}]
     * 用途：在valCode()中快速查找已有客户，减少数据库查询
     */
    protected $cluecodeCache = array();
    
    /**
     * 缓存所有查询过的门店编码，避免重复检查重复
     * 结构：["store_code" => true]
     * 用途：在valStoreCode()中快速检测门店编码是否已存在
     */
    protected $storeCoreCache = array();
    
    /**
     * 缓存各客户的门店计数，避免多次COUNT查询
     * 结构：[clue_id => count]
     * 用途：在computeStoreCode()中快速获取门店数量，生成门店编码
     */
    protected $clueStoreCountCache = array();
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
            // 从缓存中查找，而非执行数据库查询
            if(isset($this->cluecodeCache[$clueCode])){
                $row = $this->cluecodeCache[$clueCode];
                if($row["clue_type"]!=$data["clue_type"]){
                    $this->status="E";
                    $this->message="门店类别(".CGetName::getClueTypeStr($data["clue_type"])."）与客户类别（".CGetName::getClueTypeStr($row["clue_type"])."）不一致";
                }else{
                    $data["clue_id"]=$row["id"];
                }
            }else{
                $this->status="E";
                $this->message=$item['name']."没找到({$clueCode})";
            }
        }else{
            $data["clue_id"]=null;
        }
    }

    /**
     * 验证门店编号字段
     * 功能：检测门店编号是否已存在（避免重复）
     * 优化：使用预加载缓存替代数据库查询
     */
    protected function valStoreCode(&$data,$keyStr,$item){
        $store_code = key_exists($keyStr,$data)?$data[$keyStr]:'';
        if(!empty($store_code)){
            // 从缓存中查找，而非执行数据库查询
            if(isset($this->storeCoreCache[$store_code])){
                $this->status="E";
                $this->message=$item['name']."已存在({$store_code})";
            }
        }else{
            $data["store_code"]=null;
        }
    }

    /**
     * 初始化缓存数据
     * 在导入前一次性加载所有需要的参考数据到内存
     * 目的：避免后续循环中重复数据库查询
     */
    public function initCacheData(){
        // 预加载所有已存在的客户编码和门店编码，用于快速查重
        // 这样validateRowData中的valCode()和valStoreCode()就不需要查库了
        $clueRows = Yii::app()->db->createCommand()->select("id,clue_code,clue_type")->from("sal_clue")->queryAll();
        foreach($clueRows as $row){
            $this->cluecodeCache[$row['clue_code']] = $row;
        }
        
        // 预加载所有已存在的门店编码
        $storeRows = Yii::app()->db->createCommand()->select("store_code")->from("sal_clue_store")->queryAll();
        foreach($storeRows as $row){
            $this->storeCoreCache[$row['store_code']] = true;
        }
        
        // 预计算每个客户的门店数量，避免后续computeStoreCode()中频繁COUNT查询
        $countRows = Yii::app()->db->createCommand()->select("clue_id,count(*) as sum")->from("sal_clue_store")->group("clue_id")->queryAll();
        foreach($countRows as $row){
            $this->clueStoreCountCache[$row['clue_id']] = $row['sum'];
        }
    }
    
    protected function saveBodyList(){
        if(!empty($this->bodyList)){
            // 在处理数据前初始化缓存，避免循环内重复查库
            $this->initCacheData();
            
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

    /**
     * 计算新客户ID
     * 功能：
     * 1. 自动生成客户编号和简称（基于转接拼音）
     * 2. 创建所属客户记录(sal_clue)
     * 3. 创建客户历史记录、城市前程、君员懒上笼一签
     * 4. 如果存在罖厶人，自动创建罖厶人记录
     * 注意：外部saveOneData会检查clue_id是否为空来决定是否执行
     */
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

    /**
     * 计算开票信息
     * 功能：根据客户信息创建开票档
     * 注意：仅有invoice_header不为空且clue_id存在时才需要创建
     */
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

    /**
     * 计算门店编号
     * 功能：根据客户编号和现有门店数量生成新的门店编码
     * 编码规则：[客户编号]-[字符(A-Z)][数字(001-999)]
     * 优化：使用缓存的门店计数替代COUNT查询
     */
    protected function computeStoreCode(&$data){
        if(empty($data["store_code"])){
            // 从缓存中获取门店计数，而非执行COUNT查询
            $num = isset($this->clueStoreCountCache[$data["clue_id"]]) ? $this->clueStoreCountCache[$data["clue_id"]] : 0;
            
            // 计算字符和数字组合
            $charNum = floor($num/1000)+65;     // A=65, B=66, ...
            $num = floor($num%1000);
            $num = "".(1000+$num);
            $num = mb_substr($num,1);            // 去掉千位数字，得到001-999
            
            $store_code=$data["clue_code"]."-".chr($charNum).$num;
            $data["store_code"]=$store_code;
            
            // 同时更新缓存，为后续的新门店做准备
            if(!isset($this->clueStoreCountCache[$data["clue_id"]])){
                $this->clueStoreCountCache[$data["clue_id"]] = 0;
            }
            $this->clueStoreCountCache[$data["clue_id"]]++;
            // 同时更新门店编码缓存，避免后续重复
            $this->storeCoreCache[$store_code] = true;
        }
    }

    /**
     * 保存单条门店数据
     * 流程：
     * 1. 自动创建客户记录(computeClueID)
     * 2. 创建开票信息(computeInvoiceID)
     * 3. 自动生成门店编号(computeStoreCode)
     * 4. 插入门店、罖厶人等多条记录
     */
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
