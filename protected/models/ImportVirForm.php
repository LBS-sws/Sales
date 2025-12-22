<?php

class ImportVirForm extends ImportForm
{
    public $visit_type;
    public $visit_obj;
    public $visit_obj_text;
    
    /**
     * 缓存所有预加载的参考数据
     * 结构：
     * - fee_type: 费用类型列表
     * - bill_day: 账单日列表
     * - settle_type: 结算方式列表
     * - pay_week: 付款周期列表
     * - receivable_day: 应收期限列表
     * - pay_type: 付款方式列表
     * - service_type: 服务项目(按名称索引)
     * - yewudalei: 业务大类(按名称索引)
     * - main_lbs: 主体公司(按编码索引)
     * - stop_set: 停止原因(按"类型_名称"索引)
     */
    protected $cacheData = array();
    protected $batchInsertData = array();

    protected $eveList = array(
        array("name"=>"主合同编号","key"=>"cont_code","fun"=>"valContCode","requite"=>false),
        array("name"=>"虚拟合同编号","key"=>"vir_code","fun"=>"valVirCode","requite"=>true),
        array("name"=>"服务项目","key"=>"busine_name","fun"=>"valBusine","requite"=>true),
        array("name"=>"门店编号","key"=>"clue_store_id","fun"=>"valStoreCode","requite"=>true),
        array("name"=>"虚拟合同状态","key"=>"vir_status","fun"=>"valVirStatus","requite"=>true),
        array("name"=>"签约时间","key"=>"sign_date","fun"=>"valDate","requite"=>true),
        //service_type,city,office_id,busine_id,busine_id_text,create_staff,cont_month_len,technician_id_text
        //service_fre_amt,service_fre_sum,service_fre_type,service_fre_json,service_fre_text
        array("name"=>"合约开始时间","key"=>"cont_start_dt","fun"=>"valDate","requite"=>true),
        array("name"=>"合约结束时间","key"=>"cont_end_dt","fun"=>"valContEndDate","requite"=>true),
        array("name"=>"业务大类","key"=>"yewudalei","fun"=>"valYewudalei","requite"=>true),
        array("name"=>"主体公司","key"=>"lbs_main","fun"=>"valCodeMain","requite"=>true),
        array("name"=>"销售员工编号","key"=>"sales_id","fun"=>"valEmployee","requite"=>true),
        array("name"=>"销售关联合约的id","key"=>"sales_u_id","fun"=>"valEmptyInt","requite"=>true),
        array("name"=>"合约月金额","key"=>"month_amt","fun"=>"valEmptyNumber","requite"=>true),
        array("name"=>"合约年金额","key"=>"year_amt","fun"=>"valEmptyNumber","requite"=>true),
        array("name"=>"服务总次数","key"=>"service_sum","fun"=>"valEmptyInt","requite"=>true),
        array("name"=>"服务频次类型","key"=>"service_fre_type","fun"=>"valFreeType","requite"=>true),
        array("name"=>"服务频次(文字)","key"=>"u_service_title","fun"=>"","requite"=>false),
        array("name"=>"服务频次详情","key"=>"u_service_info","fun"=>"valUServiceJson","requite"=>false),
        array("name"=>"服务项目详情","key"=>"serviceTypeInfo","fun"=>"valServiceInfo","requite"=>false),
        array("name"=>"结算方式","key"=>"settle_type","fun"=>"valSettleType","requite"=>false),
        array("name"=>"付款方式","key"=>"pay_type","fun"=>"valPayType","requite"=>false),
        array("name"=>"押金备注","key"=>"deposit_rmk","fun"=>"","requite"=>false),
        array("name"=>"已收押金","key"=>"deposit_amt","fun"=>"valEmptyNumber","requite"=>false),
        array("name"=>"所需押金","key"=>"deposit_need","fun"=>"valEmptyNumber","requite"=>false),
        array("name"=>"收费方式","key"=>"fee_type","fun"=>"valFeeType","requite"=>false),
        array("name"=>"预付月数","key"=>"pay_month","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"起始月","key"=>"pay_start","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"是否对账","key"=>"bill_bool","fun"=>"valEmptyYes","requite"=>false),
        array("name"=>"账单日","key"=>"bill_day","fun"=>"valBillDay","requite"=>false),
        array("name"=>"付款周期","key"=>"pay_week","fun"=>"valPayWeek","requite"=>false),
        array("name"=>"服务时长(分钟)","key"=>"service_timer","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"是否优先安排服务","key"=>"prioritize_service","fun"=>"valEmptyYes","requite"=>false),
        array("name"=>"应收期限","key"=>"receivable_day","fun"=>"valReceivableDay","requite"=>false),
        array("name"=>"剩余次数","key"=>"surplus_num","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"剩余金额","key"=>"surplus_amt","fun"=>"valEmptyNumber","requite"=>false),
        array("name"=>"服务主体","key"=>"service_main","fun"=>"valCodeMainAll","requite"=>false),
        array("name"=>"首次日期","key"=>"first_date","fun"=>"valDate","requite"=>false),
        array("name"=>"常规开始日期","key"=>"fast_date","fun"=>"valDate","requite"=>false),
        array("name"=>"是否需安装费","key"=>"need_install","fun"=>"valEmptyYes","requite"=>false),
        array("name"=>"安装金额","key"=>"amt_install","fun"=>"valEmptyNumber","requite"=>false),
        array("name"=>"被跨区业务员","key"=>"other_sales_id","fun"=>"valEmployee","requite"=>false),
        array("name"=>"被跨区业务员关联合约的id","key"=>"other_sales_u_id","fun"=>"valOtherSalesUID","requite"=>false),
        array("name"=>"被跨区业务员业务大类","key"=>"other_yewudalei","fun"=>"valOtherYewudalei","requite"=>false),
        array("name"=>"首次技术员","key"=>"first_tech_id","fun"=>"valEmployee","requite"=>false),
        array("name"=>"负责技术员","key"=>"technician_id_str","fun"=>"valTechnicianList","requite"=>false),
        array("name"=>"外部数据来源","key"=>"external_source","fun"=>"","requite"=>false),
        array("name"=>"终止或暂停原因","key"=>"stop_set_id","fun"=>"valStopSet","requite"=>false),
        array("name"=>"终止或暂停日期","key"=>"stop_date","fun"=>"valDate","requite"=>false),
        array("name"=>"发票金额","key"=>"invoice_amount","fun"=>"valEmptyNumber","requite"=>false),
        array("name"=>"派单系统id","key"=>"u_id","fun"=>"valEmptyInt","requite"=>false),
    );

    protected function valOtherSalesUID(&$data,$keyStr,$item){
        $other_sales_u_id = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        $other_sales_id = key_exists("other_sales_id", $data) ? $data["other_sales_id"] : '';
        if(empty($other_sales_id)&&!empty($other_sales_u_id)){
            $this->status="E";
            $this->message="被跨区业务员关联合约的id填写后，被跨区业务员不能为空";
            return false;
        }
        if(!empty($other_sales_id)&&empty($other_sales_u_id)){
            $this->status="E";
            $this->message="被跨区业务员填写后，被跨区业务员关联合约的id不能为空";
            return false;
        }
        $this->valEmptyInt($data,$keyStr,$item);
    }

    /**
     * 验证费用类型字段
     * 将导入数据中的费用类型名称转换为系统ID
     * 优化：使用预加载缓存替代每次数据库查询
     */
    protected function valFeeType(&$data,$keyStr,$item){
        $feeType = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(empty($feeType)){
            $data[$keyStr]=null;  // 空值允许
        }else{
            // 从缓存中查找，而非数据库
            $list=$this->cacheData['fee_type'];
            $key = array_search($feeType, $list);
            if($key!==false){
                $data[$keyStr]=$key;  // 转换为系统ID
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$feeType})";
            }
        }
    }

    /**
     * 验证账单日字段
     * 优化：改为CGetName调用敀为缓存查找，防止重复整合数据库
     */
    protected function valBillDay(&$data,$keyStr,$item){
        $billDay = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(empty($billDay)){
            $data[$keyStr]=null;
        }else{
            $list=$this->cacheData['bill_day'];
            $key = array_search($billDay, $list);
            if($key!==false){
                $data[$keyStr]=$key;
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$billDay})";
            }
        }
    }

    /**
     * 验证结算方式字段
     * 优化：改为CGetName调用敀为缓存查找
     */
    protected function valSettleType(&$data,$keyStr,$item){
        $settleType = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(empty($settleType)){
            $data[$keyStr]=null;
        }else{
            $list=$this->cacheData['settle_type'];
            $key = array_search($settleType, $list);
            if($key!==false){
                $data[$keyStr]=$key;
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$settleType})";
            }
        }
    }

    /**
     * 验证付款周期字段
     * 优化：使用缓存替代CGetName方法调用
     */
    protected function valPayWeek(&$data,$keyStr,$item){
        $payWeek = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(empty($payWeek)){
            $data[$keyStr]=null;
        }else{
            $list=$this->cacheData['pay_week'];
            $key = array_search($payWeek, $list);
            if($key!==false){
                $data[$keyStr]=$key;
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$payWeek})";
            }
        }
    }

    /**
     * 验证应收期限字段
     * 优化：使用缓存替代CGetName方法调用
     */
    protected function valReceivableDay(&$data,$keyStr,$item){
        $receivableDay = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(empty($receivableDay)){
            $data[$keyStr]=null;
        }else{
            $list=$this->cacheData['receivable_day'];
            $key = array_search($receivableDay, $list);
            if($key!==false){
                $data[$keyStr]=$key;
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$receivableDay})";
            }
        }
    }

    /**
     * 验证付款方式字段
     * 优化：使用缓存替代CGetName方法调用
     */
    protected function valPayType(&$data,$keyStr,$item){
        $payType = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(empty($payType)){
            $data[$keyStr]=null;
        }else{
            $list=$this->cacheData['pay_type'];
            $key = array_search($payType, $list);
            if($key!==false){
                $data[$keyStr]=$key;
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$payType})";
            }
        }
    }

    /**
     * 验证需技本员字段
     * 功能：根据技本员体编码，查询技本员ID列表
     * 注意：此方法仍使用数据库查询（体编码数量少），暂既不优化
     */
    protected function valTechnicianList(&$data,$keyStr,$item){
        $codeStr = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(!empty($codeStr)){
            $ids=array();
            $names=array();
            // 按逗号分解多个技本员体编码
            $codeList = explode(",",$codeStr);
            foreach ($codeList as $code){
                if(!empty($code)){
                    // 查询技本员信息信息
                    $row = $this->getEmployeeListByCode($code);
                    if($row){
                        $ids[]=$row["id"];
                        $names[]=$row["name"]." ({$row["code"]})";
                    }else{
                        $this->status="E";
                        $this->message=$item['name']."员工编号不存在({$code})";
                    }
                }
            }
            if(!empty($ids)){
                // 将ID和名称以逻号分隔存储
                $data[$keyStr]=implode(",",$ids);
                $data["technician_id_text"]=implode(",",$names);
            }else{
                $data[$keyStr]=null;
            }
        }else{
            $data[$keyStr]=null;
        }
    }

    /**
     * 验证终止或暂停原因字段
     * 逻辑：
     * - 仅在虚拟合约状态为"暂停"(40)或"终止"(50)时处理
     * - 根据状态类型(1=暂停 2=终止)匹配对应的停止原因
     * - 若指定原因不存在，自动使用同类型的首个默认原因
     * 优化：使用缓存替代多次数据库查询
     */
    protected function valStopSet(&$data,$keyStr,$item){
        $stopName = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        // 只在合约被暂停或终止时处理该字段
        if(empty($stopName)||!in_array($data["vir_status"],array(50,40))){
            $data[$keyStr]=null;  // 其他状态不需要停止原因
        }else{
            // 根据虚拟合约状态确定停止类型: 暂停=1, 终止=2
            $s_type=$data["vir_status"]==40?1:2;
            $key = $s_type.'_'.$stopName;
            
            // 首先尝试查找精确匹配的停止原因
            if(isset($this->cacheData['stop_set'][$key])){
                $data[$keyStr]=$this->cacheData['stop_set'][$key];
            }else{
                // 若不存在指定的停止原因，使用同类型的第一个默认原因
                $found = false;
                foreach($this->cacheData['stop_set'] as $cacheKey=>$id){
                    if(strpos($cacheKey, $s_type.'_')===0){  // 检查是否属于同停止类型
                        $data[$keyStr]=$id;
                        $found = true;
                        break;
                    }
                }
                if(!$found){
                    $data[$keyStr]=null;  // 该类型无任何停止原因
                }
            }
        }
    }

    protected function valServiceInfo(&$data,$keyStr,$item){
        $serviceText = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        $virDetail=array();
        $virInfo=array();
        $virInfo[]=array("field_id"=>"svc_{$data['busine_id']}","field_value"=>$data["month_amt"]);
        $virDetail["svc_{$data['busine_id']}"]=$data["month_amt"];
        $freeStrList=array(
            "FreType"=>"service_fre_type",
            "FreSum"=>"service_fre_sum",
            "FreAmt"=>"service_fre_amt",
            "FreJson"=>"service_fre_json",
            "FreText"=>"service_fre_text",
        );
        foreach ($freeStrList as $keyName=>$itemName){
            if(isset($data[$itemName])){
                $virInfo[]=array("field_id"=>"svc_{$data['busine_id']}{$keyName}","field_value"=>$data[$itemName]);
                $virDetail["svc_{$data['busine_id']}{$keyName}"]=$data[$itemName];
            }
        }
        $yearRow=Yii::app()->db->createCommand()->select("*")->from("sal_service_type_info")
            ->where("type_id=:id and input_type='yearAmount'",array(":id"=>$data["busine_id_int"]))->queryRow();
        if($yearRow){
            $virInfo[]=array("service_type_id"=>$yearRow["id"],"field_id"=>"svc_{$yearRow['id_char']}","field_value"=>$data["year_amt"]);
            $virDetail["svc_{$yearRow['id_char']}"]=$data["year_amt"];
        }
        if(!empty($serviceText)){
            $serviceText = str_replace("'","\'",$serviceText);
            $serviceText="'".str_replace(";","','",$serviceText)."'";
            $rows = Yii::app()->db->createCommand()->select("*")->from("sal_service_type_info")
                ->where("type_id=:id and input_type in ('checkbox','device','method') and name in ({$serviceText})",array(":id"=>$data["busine_id_int"]))->queryAll();
            if($rows){
                foreach ($rows as $row){
                    $virInfo[]=array("service_type_id"=>$row["id"],"field_id"=>"svc_{$row['id_char']}","field_value"=>"Y");
                    $virDetail["svc_{$row['id_char']}"]="Y";
                }
            }
        }
        $data["virInfo"]=$virInfo;
        $data["detail_json"]=json_encode($virDetail,JSON_UNESCAPED_UNICODE);
    }

    public static function calcPeriodByMonth($cycle,$max=12) {
        for ($i=1;$i<=$max;$i++){
            if(pow(2,$i-1)==$cycle){
                return $i;
            }
        }
        return 1;
    }

    protected function valUServiceJson(&$data,$keyStr,$item){
        $u_service_title = $data["u_service_title"];
        $u_service_json=array("title"=>$u_service_title,"list"=>array());
        $freeJson = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        $freeJson = empty($freeJson)?array():json_decode($freeJson,true);
        $freeJson = is_array($freeJson)?$freeJson:array();
        $monthPriceList=array();
        $call_fre_amt = 0;
        foreach ($freeJson as $freeRow){
            if(isset($freeRow["month_cycle"])&&is_numeric($freeRow["month_cycle"])&&isset($freeRow["unit_price"])&&is_numeric($freeRow["unit_price"])){
                $freeRow["month_cycle"] = intval($freeRow["month_cycle"]);
                $freeRow["unit_price"] = floatval($freeRow["unit_price"]);
                $call_fre_amt = $freeRow["unit_price"];
                $monthNum = $this->calcPeriodByMonth($freeRow["month_cycle"]);
                $monthKey = "".$monthNum."_".$freeRow["unit_price"];
                if(!key_exists($monthKey,$monthPriceList)){//合并同月同金额
                    $monthPriceList[$monthKey]=array("month"=>$monthNum,"price"=>$freeRow["unit_price"],"num"=>0);
                }
                $monthPriceList[$monthKey]["num"]++;
                $temp=array(
                    "month_cycle"=>$freeRow["month_cycle"],
                    "week_cycle"=>isset($freeRow["week_cycle"])?intval($freeRow["week_cycle"]):null,
                    "day_cycle"=>isset($freeRow["day_cycle"])?intval($freeRow["day_cycle"]):null,
                    "unit_price"=>$freeRow["unit_price"],
                    "cycle_text"=>isset($freeRow["cycle_text"])?$freeRow["cycle_text"]:null,
                );
                $u_service_json["list"][]=$temp;
            }
        }
        if(!empty($monthPriceList)){
            $monthPriceList = $this->computeFreeByU($monthPriceList);
            $data["service_fre_text"]="";
            $data["service_fre_json"]=array(
                "fre_amt"=>0,//频次总年金额
                "fre_month"=>0,//频次总月金额
                "fre_sum"=>0,//频次总次数
                "fre_type"=>2,//频次类型
                "fre_list"=>array(),//频次详情
            );
            //{"fre_amt":72000,"fre_month":6000,"fre_sum":24,"fre_type":1,"fre_list":[{"month":[1,2,3,4,5,6,7,8,9,10,11,12],"fre_num":2,"type_sum":"1","fre_amt":3000}]}
            if(count($monthPriceList)>=2){
                $data["service_fre_type"]=2;
                $data["service_fre_json"]["fre_type"]=2;
            }
            foreach ($monthPriceList as $monthPriceRow){
                $data["service_fre_text"].=implode("、",$monthPriceRow["month"])."月,每月服务{$monthPriceRow["fre_num"]}次,每次金额{$monthPriceRow["fre_amt"]};";
                $data["service_fre_json"]["fre_list"][]=$monthPriceRow;
                $data["service_fre_json"]["fre_sum"]+=$monthPriceRow["fre_num"]*count($monthPriceRow["month"]);
                $data["service_fre_json"]["fre_amt"]+=$data["service_fre_json"]["fre_sum"]*$monthPriceRow["fre_amt"];
            }
            $data["service_fre_json"]["fre_month"]=empty($data["service_fre_json"]["fre_sum"])?0:round($data["service_fre_json"]["fre_amt"]/$data["service_fre_json"]["fre_sum"],2);
            $data["service_fre_json"] = json_encode($data["service_fre_json"],JSON_UNESCAPED_UNICODE);
        }
        $data["u_service_json"]=$u_service_json;
        $data["service_fre_amt"]=$data["year_amt"];
        $data["service_fre_sum"]=$data["service_sum"];
        $data["call_fre_amt"]=isset($data["service_fre_type"])&&$data["service_fre_type"]==3?$call_fre_amt:0;
    }

    protected function computeFreeByU($monthPriceList){
        $list=array();
        foreach ($monthPriceList as $monthPriceRow){
            //array("month"=>$monthNum,"price"=>$freeRow["unit_price"],"num"=>0);
            $keyStr= "".$monthPriceRow["price"]."_".$monthPriceRow["num"];
            if(!key_exists($keyStr,$list)){//合并同次数同金额
                $list[$keyStr]=array(
                    "month"=>array(),//频次包含月份
                    "fre_num"=>$monthPriceRow["num"],//每月次数
                    "type_sum"=>3,//次数类型：3:每月
                    "fre_amt"=>$monthPriceRow["price"],//每次金额
                    "type_amt"=>1,//金额类型：1
                );
            }
            $list[$keyStr]["month"][]=$monthPriceRow["month"];
        }
        return $list;
    }

    protected function valFreeType(&$data,$keyStr,$item){
        $free_type = key_exists($keyStr,$data)?$data[$keyStr]:'';
        switch ($free_type){
            case "固定":
                $data[$keyStr]=1;
                break;
            case "非固定":
                $data[$keyStr]=2;
                break;
            case "呼叫式":
                $data[$keyStr]=3;
                break;
            default:
                $this->status="E";
                $this->message=$item['name']."异常({$free_type})";
        }
    }

    protected function valVirStatus(&$data,$keyStr,$item){
        $vir_status = key_exists($keyStr,$data)?$data[$keyStr]:'';
        switch ($vir_status){
            case "生效中":
                $data[$keyStr]=30;
                break;
            case "暂停":
                $data[$keyStr]=40;
                break;
            case "终止":
                $data[$keyStr]=50;
                break;
            default:
                $this->status="E";
                $this->message=$item['name']."异常({$vir_status})";
        }
    }

    /**
     * 验证服务项目字段
     * 功能：
     * 1. 查询服务项目基本信息(ID、类型、编码)
     * 2. 若存在主合同，验证该服务项目是否属于主合同
     * 3. 提取相关字段(service_type, busine_id, busine_id_int, busine_id_text)
     * 优化：使用预加载缓存替代数据库查询
     */
    protected function valBusine(&$data,$keyStr,$item){
        $busineName = key_exists($keyStr,$data)?$data[$keyStr]:'';
        $contRow = key_exists("contRow",$data)?$data["contRow"]:array();
        if(!empty($busineName)){
            // 从缓存中快速查找，而非执行SQL查询
            $row = isset($this->cacheData['service_type'][$busineName]) ? $this->cacheData['service_type'][$busineName] : null;
            if($row){
                // 如果存在关联的主合同，需要验证该服务项目是否在主合同的服务范围内
                if(!empty($contRow)){
                    $contRow["busine_id"] = explode(",",$contRow["busine_id"]);
                    if(!in_array($row["id_char"],$contRow["busine_id"])){
                        $this->status="E";
                        $this->message=$item['name']."异常，主合同({$contRow['cont_code']})不存在该服务项目({$busineName})";
                    }
                }
                // 提取服务项目的关键属性到数据对象
                $data["service_type"]=$row["service_type"];
                $data["busine_id"]=$row["id_char"];      // 服务项目编码
                $data["busine_id_int"]=$row["id"];        // 服务项目ID
                $data["busine_id_text"]=$busineName;      // 服务项目名称
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$busineName})";
            }
        }else{
            $this->status="E";
            $this->message=$item['name']."不能为空";
        }
    }

    protected function valContCode(&$data,$keyStr,$item){
        $cont_code = key_exists($keyStr,$data)?$data[$keyStr]:'';
        if(!empty($cont_code)){
            $row = Yii::app()->db->createCommand()->select("*")->from("sal_contract")
                ->where("cont_code=:cont_code",array(":cont_code"=>$cont_code))->queryRow();
            if($row){
                $data["cont_id"]=$row["id"];
                $data["contRow"]=$row;
                $data["clue_service_id"] = $row["clue_service_id"];
                $proRow = Yii::app()->db->createCommand()->select("id")->from("sal_contpro")
                    ->where("cont_id=:cont_id",array(":cont_id"=>$row["id"]))->order("id asc")->queryRow();
                $data["pro_id"] = $proRow["id"];
            }
        }
    }

    protected function valVirCode(&$data,$keyStr,$item){
        $vir_code = key_exists($keyStr,$data)?$data[$keyStr]:'';
        if(!empty($vir_code)){
            $row = Yii::app()->db->createCommand()->select("*")->from("sal_contract_virtual")
                ->where("vir_code=:vir_code",array(":vir_code"=>$vir_code))->queryRow();
            if($row){
                $this->status="E";
                $this->message=$item['name']."已存在({$vir_code})";
            }
        }else{
            $this->status="E";
            $this->message=$item['name']."不能为空";
        }
    }

    protected function valStoreCode(&$data,$keyStr,$item){
        $store_code = key_exists($keyStr,$data)?$data[$keyStr]:'';
        $contRow = key_exists("contRow",$data)?$data["contRow"]:array();
        $busine_id = key_exists("busine_id",$data)?$data["busine_id"]:0;
        if(!empty($store_code)){
            $row = Yii::app()->db->createCommand()->select("*")->from("sal_clue_store")
                ->where("store_code=:store_code",array(":store_code"=>$store_code))->queryRow();
            if($row){
                if (strpos($this->city_allow,"'{$row['city']}'")!==false){
                    if(!empty($contRow)){//如果存在主合同需要验证主合同内已存在本门店
                        $virRow = Yii::app()->db->createCommand()->select("vir_code")->from("sal_contract_virtual")
                            ->where("cont_id=:cont_id and clue_store_id=:clue_store_id and busine_id=:busine_id",array(
                                ":cont_id"=>$contRow["id"],
                                ":clue_store_id"=>$row["id"],
                                ":busine_id"=>$busine_id,
                            ))->queryRow();
                        if($virRow){
                            $this->status="E";
                            $this->message=$item['name']."异常，主合约{$contRow['cont_code']}已存在虚拟合约{$virRow['vir_code']}";
                        }
                    }
                    $data[$keyStr]=$row["id"];
                    $data["clue_id"]=$row["clue_id"];
                    $data["clue_type"]=$row["clue_type"];
                    $data["city"]=$row["city"];
                    $data["office_id"]=$row["office_id"];
                    $data["storeRow"]=$row;
                }else{
                    $this->status="E";
                    $this->message="你没有权限添加城市({$row["city"]})的虚拟合约({$store_code})";
                }
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$store_code})";
            }
        }else{
            $this->status="E";
            $this->message=$item['name']."不能为空";
        }
    }

    protected function valContEndDate(&$data,$keyStr,$item){
        $this->valDate($data,$keyStr,$item);
        if($this->status!="E"){
            $startDate = $data["cont_start_dt"];
            $endDate = $data["cont_end_dt"];
            $data["cont_month_len"] = CGetName::computeMothLenBySE($startDate,$endDate);
        }
    }

    /**
     * 验证业务大类字段
     * 将业务大类名称转换为系统ID
     * 优化：使用缓存哈希表查找替代数据库查询
     */
    protected function valYewudalei(&$data,$keyStr,$item){
        $yewudalei = isset($data[$keyStr])?$data[$keyStr]:"-1";
        // 直接从缓存索引中查找，O(1)时间复杂度
        if(isset($this->cacheData['yewudalei'][$yewudalei])){
            $data[$keyStr]=$this->cacheData['yewudalei'][$yewudalei];
        }else{
            $this->status="E";
            $this->message=$item['name']."不存在";
        }
    }

    /**
     * 验证主体公司字段（城市关联版本）
     * 功能：查询符合条件的主体公司
     * 查询逻辑：
     * - show_type=2: 全国显示
     * - show_type=1: 仅在指定城市显示
     * - show_type=3: 在多个城市显示(show_city字段)
     * 优化：使用缓存替代复杂SQL查询
     */
    protected function valCodeMain(&$data,$keyStr,$item){
        $codeMain = isset($data[$keyStr])?$data[$keyStr]:"";
        $city = isset($data["city"])?$data["city"]:"";
        if(!empty($codeMain)){
            $found = false;
            // 从缓存中查找匹配的编码记录
            if(isset($this->cacheData['main_lbs'][$codeMain])){
                // 遍历该编码对应的所有记录(可能有多个)
                foreach($this->cacheData['main_lbs'][$codeMain] as $row){
                    // 判断该记录是否对当前城市可见
                    if($row['show_type']==2 || 
                       ($row['show_type']==1 && $row['city']==$city) || 
                       ($row['show_type']==3 && strpos($row['show_city'],$city)!==false)){
                        $data[$keyStr]=$row["id"];
                        $found = true;
                        break;  // 找到第一条匹配记录后退出
                    }
                }
            }
            if(!$found){
                $this->status="E";
                $this->message="城市{$city}没有{$item['name']}编号：{$codeMain}";
            }
        }else{
            $data[$keyStr]=null;  // 允许空值
        }
    }

    /**
     * 验证主体公司字段（无城市限制版本）
     * 不考虑城市限制，直接返回找到的第一条记录
     * 用于service_main等非城市关联的主体公司字段
     * 优化：缓存查找，O(1)时间复杂度
     */
    protected function valCodeMainAll(&$data,$keyStr,$item){
        $codeMain = isset($data[$keyStr])?$data[$keyStr]:"";
        if(!empty($codeMain)){
            // 检查缓存中是否存在该编码的记录
            if(isset($this->cacheData['main_lbs'][$codeMain]) && !empty($this->cacheData['main_lbs'][$codeMain])){
                $data[$keyStr]=$this->cacheData['main_lbs'][$codeMain][0]["id"];  // 取第一条记录
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在";
            }
        }else{
            $data[$keyStr]=null;  // 允许空值
        }
    }

    protected function initForm(){
        $typeRow = Yii::app()->db->createCommand()->select("id")->from("sal_visit_type")
            ->order("id asc")->queryRow();
        if($typeRow){
            $this->visit_type=$typeRow["id"];
        }else{
            $this->status="E";
            $this->message="系统配置异常：销售拜访没有拜访类别";
        }
        $objRow = Yii::app()->db->createCommand()->select("id,name")->from("sal_visit_obj")
            ->where("rpt_type='DEAL'")->queryRow();
        if($objRow){
            $this->visit_obj=$objRow["id"];
            $this->visit_obj_text=$objRow["name"];
        }else{
            $this->status="E";
            $this->message="系统配置异常：销售拜访没有签单类型";
        }
        // 初始化缓存数据
        $this->initCacheData();
    }
    
    /**
     * 初始化所有参考数据缓存
     * 目的：在导入前一次性加载所有需要的维表数据到内存，防止后续循环中重复查库
     * 性能优化：减少数据库查询从N次降低到常数次，显著提升大批量导入性能
     * 调用时机：initForm()方法中，在业务逻辑处理之前
     */
    protected function initCacheData(){
        // 一次性加载所有枚举类型数据（这些数据通过CGetName静态方法返回）
        $this->cacheData['fee_type'] = CGetName::getFeeTypeList();
        $this->cacheData['bill_day'] = CGetName::getBillDayList();
        $this->cacheData['settle_type'] = CGetName::getSettleTypeList();
        $this->cacheData['pay_week'] = CGetName::getPayWeekList();
        $this->cacheData['receivable_day'] = CGetName::getReceivableDayList();
        $this->cacheData['pay_type'] = CGetName::getPayTypeList();
        
        // 加载服务项目表数据，按服务名称构建索引以支持快速查找
        // 返回结构：["服务名" => {id, name, service_type, id_char}]
        $serviceRows = Yii::app()->db->createCommand()->select("id,name,service_type,id_char")->from("sal_service_type")->queryAll();
        $this->cacheData['service_type'] = array();
        foreach($serviceRows as $row){
            $this->cacheData['service_type'][$row['name']] = $row;  // 按名称索引便于查询
        }
        
        // 加载业务大类数据，按名称构建ID索引
        // 返回结构：["大类名" => 大类ID]
        $yewuRows = Yii::app()->db->createCommand()->select("id,name")->from("sal_yewudalei")->queryAll();
        $this->cacheData['yewudalei'] = array();
        foreach($yewuRows as $row){
            $this->cacheData['yewudalei'][$row['name']] = $row['id'];
        }
        
        // 加载主体公司数据，按编码构建索引
        // 返回结构：["编码" => [{id, mh_code, show_type, city, show_city}, ...]]
        // 一个编码可能对应多条记录（不同城市/显示类型），故使用数组存储
        $mainRows = Yii::app()->db->createCommand()->select("id,mh_code,show_type,city,show_city")->from("sal_main_lbs")->queryAll();
        $this->cacheData['main_lbs'] = array();
        foreach($mainRows as $row){
            $this->cacheData['main_lbs'][$row['mh_code']][] = $row;
        }
        
        // 加载停止原因数据，按"停止类型_停止原因名"构建联合索引
        // 返回结构：["1_暂停" => ID, "2_终止" => ID]
        $stopRows = Yii::app()->db->createCommand()->select("id,name,str_type")->from("sal_cont_str")->queryAll();
        $this->cacheData['stop_set'] = array();
        foreach($stopRows as $row){
            $key = $row['str_type'].'_'.$row['name'];
            $this->cacheData['stop_set'][$key] = $row['id'];
        }
    }

    protected function proTypeByStatus($status){
        switch ($status){
            case 30:
                return "N";
            case 40:
                return "S";
            case 50:
                return "T";
            default:
                return "N";
        }
    }

    /**
     * 计算本次虚拟合约关联的主合同
     * 功能：
     * 1. 创建销售回访记录(sal_clue_service)
     * 2. 创建主合同记录(sal_contract)
     * 3. 创建主合同变更记录(sal_contpro)
     * 注意：外部方法会检查$data["cont_id"]是否为空来决定是否需要执行此方法
     */
    protected function computeContID(&$data){
        // 创建销售回访纪录(不是每次都需要)
        Yii::app()->db->createCommand()->insert("sal_clue_service",array(
            'clue_id'=>$data["storeRow"]["clue_id"],
            'clue_type'=>$data["storeRow"]["clue_type"],
            'visit_type'=>$this->visit_type,
            'visit_obj'=>$this->visit_obj,
            'visit_obj_text'=>$this->visit_obj_text,
            'create_staff'=>$data["sales_id"],
            'busine_id'=>$data["busine_id"],
            'busine_id_text'=>$data["busine_id_text"],
            'sign_odds'=>100,
            'lbs_main'=>$data["lbs_main"],
            'predict_date'=>$data["sign_date"],
            'predict_amt'=>$data["year_amt"],
            'total_amt'=>$data["year_amt"],
            'total_num'=>1,
            'service_status'=>$data["vir_status"],
            "lcu"=>$this->username,
            'report_id'=>$this->id,
        ));
        $data["clue_service_id"] = Yii::app()->db->getLastInsertID();
        
        // 创建主合同
        $contArr = array(
            'clue_id'=>$data["storeRow"]["clue_id"],
            'clue_type'=>$data["storeRow"]["clue_type"],
            'clue_service_id'=>$data["clue_service_id"],
            'city'=>$data["storeRow"]["city"],
            'cont_code'=>"DL-".$data["vir_code"],
            'sales_id'=>$data["sales_id"],
            'lbs_main'=>$data["lbs_main"],
            'predict_amt'=>$data["year_amt"],
            'store_sum'=>1,
            'cont_type'=>1,
            'sign_type'=>1,
            'total_sum'=>$data["service_sum"],
            'total_amt'=>$data["year_amt"],
            'cont_status'=>$data["vir_status"],
            'stop_date'=>$data["stop_date"],
            'surplus_num'=>$data["surplus_num"],
            'surplus_amt'=>$data["surplus_amt"],
            'cont_start_dt'=>$data["cont_start_dt"],
            'cont_end_dt'=>$data["cont_end_dt"],
            'cont_month_len'=>$data["cont_month_len"],
            'sign_date'=>$data["sign_date"],
            'area_bool'=>"N",
            'group_bool'=>"N",
            'prioritize_service'=>$data["prioritize_service"],
            'service_timer'=>$data["service_timer"],
            'pay_type'=>$data["pay_type"],
            'pay_week'=>$data["pay_week"],
            'pay_month'=>$data["pay_month"],
            'pay_start'=>$data["pay_start"],
            'deposit_need'=>$data["deposit_need"],
            'deposit_amt'=>$data["deposit_amt"],
            'deposit_rmk'=>$data["deposit_rmk"],
            'fee_type'=>$data["fee_type"],
            'settle_type'=>$data["settle_type"],
            'bill_day'=>$data["bill_day"],
            'bill_bool'=>$data["bill_bool"],
            'receivable_day'=>$data["receivable_day"],
            'yewudalei'=>$data["yewudalei"],
            'other_sales_id'=>$data["other_sales_id"],
            'other_yewudalei'=>$data["other_yewudalei"],
            'busine_id'=>$data["busine_id"],
            'busine_id_text'=>$data["busine_id_text"],
            'report_id'=>$this->id,
            "lcu"=>$this->username,
        );
        Yii::app()->db->createCommand()->insert("sal_contract",$contArr);
        $data["cont_id"] = Yii::app()->db->getLastInsertID();
        
        // 创建主合同变更记录(初始执行状态)
        $contArr["cont_id"]=$data["cont_id"];
        $contArr["pro_code"]="PDL-".$data["vir_code"];
        $contArr["pro_type"]=$this->proTypeByStatus($data["vir_status"]);
        $contArr["pro_date"]=$data["sign_date"];
        $contArr["pro_remark"]="导入虚拟合约自动生成\n导入id：{$this->id}";
        $contArr["pro_status"]=30;
        $contArr["pro_change"]=$data["vir_status"]==30?$data["year_amt"]:$data["surplus_amt"];
        $contArr["pro_change"]=empty($contArr["pro_change"])?0:$contArr["pro_change"];
        Yii::app()->db->createCommand()->insert("sal_contpro",$contArr);
        $data["pro_id"] = Yii::app()->db->getLastInsertID();
    }

    protected function computeContSSEID(&$data){
        $sseArr=array(
            "clue_id"=>$data["clue_id"],
            "clue_service_id"=>$data["clue_service_id"],
            "clue_store_id"=>$data["clue_store_id"],
            "create_staff"=>$data["sales_id"],
            "store_amt"=>$data["year_amt"],
            "service_sum"=>$data["service_sum"],
            "update_bool"=>3,
            "busine_id"=>$data["busine_id"],
            "busine_id_text"=>$data["busine_id_text"],
            "detail_json"=>$data["detail_json"],
            "lcu"=>$this->username,
        );
        $clueSSE = Yii::app()->db->createCommand()->select("*")->from("sal_clue_sre_soe")
            ->where("clue_service_id=:clue_service_id and clue_store_id=:clue_store_id",array(
                ":clue_service_id"=>$data["clue_service_id"],
                ":clue_store_id"=>$data["clue_store_id"],
            ))->queryRow();
        if($clueSSE){
            $thisArr=$this->mergeSSERow($sseArr,$clueSSE);
            Yii::app()->db->createCommand()->update("sal_clue_sre_soe",$thisArr,"id=".$clueSSE["id"]);
        }else{
            Yii::app()->db->createCommand()->insert("sal_clue_sre_soe",$sseArr);
        }
        $contSSE = Yii::app()->db->createCommand()->select("*")->from("sal_contract_sse")
            ->where("cont_id=:cont_id and clue_store_id=:clue_store_id",array(
                ":cont_id"=>$data["cont_id"],
                ":clue_store_id"=>$data["clue_store_id"],
            ))->queryRow();
        if($contSSE){
            $thisArr=$this->mergeSSERow($sseArr,$contSSE);
            Yii::app()->db->createCommand()->update("sal_contract_sse",$thisArr,"id=".$contSSE["id"]);
            $data["sse_id"]=$contSSE["id"];
            $sseArr["cont_id"]=$data["cont_id"];
        }else{
            $sseArr["cont_id"]=$data["cont_id"];
            Yii::app()->db->createCommand()->insert("sal_contract_sse",$sseArr);
            $data["sse_id"]=Yii::app()->db->getLastInsertID();
        }
        $contProSSE = Yii::app()->db->createCommand()->select("*")->from("sal_contpro_sse")
            ->where("pro_id=:pro_id and clue_store_id=:clue_store_id",array(
                ":pro_id"=>$data["pro_id"],
                ":clue_store_id"=>$data["clue_store_id"],
            ))->queryRow();
        if($contProSSE){
            $thisArr=$this->mergeSSERow($sseArr,$contProSSE);
            Yii::app()->db->createCommand()->update("sal_contpro_sse",$thisArr,"id=".$contProSSE["id"]);
        }else{
            $sseArr["pro_id"]=$data["pro_id"];
            Yii::app()->db->createCommand()->insert("sal_contpro_sse",$sseArr);
        }
    }

    protected function mergeSSERow($sseArr,$clueSSE){
        $clueSSE["detail_json"]=empty($clueSSE["detail_json"])?array():json_decode($clueSSE["detail_json"],true);
        $sseArr["detail_json"]=empty($sseArr["detail_json"])?array():json_decode($sseArr["detail_json"],true);
        $sseArr["busine_id"]=$clueSSE["busine_id"].",".$sseArr["busine_id"];
        $sseArr["busine_id_text"]=$clueSSE["busine_id_text"]."、".$sseArr["busine_id_text"];
        $sseArr["detail_json"]=array_merge($clueSSE["detail_json"],$sseArr["detail_json"]);
        $sseArr["detail_json"]=json_encode($sseArr["detail_json"],JSON_UNESCAPED_UNICODE);
        $sseArr["store_amt"]+=$clueSSE["store_amt"];
        $sseArr["service_sum"]+=$clueSSE["service_sum"];
        return $sseArr;
    }

    /**
     * 保存单条虚拟合约数据
     * 流程：
     * 1. 若不存在主合同，自动生成(computeContID)
     * 2. 生成或更新SSE关联数据(computeContSSEID)
     * 3. 收集数据用于后续批量或单条插入
     * 性能：预留批量操作结构，便于后续扩展批量插入优化
     */
    protected function saveOneData($data){
        // 若不存在主合同ID，说明此虚拟合约需新建主合同和关联信息
        if(empty($data["cont_id"])){
            $this->computeContID($data);  // 自动创建主合同及相关记录
        }
        // 生成或更新SSE(主合同、服务、门店)三层关联数据
        $this->computeContSSEID($data);
        // 将处理后的数据收集，便于后续批量操作优化
        $this->batchInsertData[] = $data;
        $data["create_staff"]=$data["sales_id"];
        $data["report_id"]=$this->id;
        $saveKey=array(
            'cont_id','sse_id','clue_id','clue_type','clue_service_id','clue_store_id','vir_code','vir_status',
            'city','office_id','busine_id','service_type','receivable_day','bill_bool','bill_day','settle_type',
            'fee_type','deposit_rmk','deposit_amt','deposit_need','pay_start','pay_month','pay_type','pay_week',
            'service_timer','prioritize_service','sign_date','yewudalei','lbs_main','service_main','busine_id_text',
            'sales_id','create_staff','month_amt','year_amt','service_sum','surplus_num','surplus_amt',
            'call_fre_amt','service_fre_amt','service_fre_sum','service_fre_type','service_fre_json','service_fre_text',
            'cont_start_dt','cont_end_dt','cont_month_len','fast_date','first_date','need_install','amt_install',
            'other_sales_id','other_yewudalei','first_tech_id','technician_id_str','technician_id_text','external_source','stop_set_id',
            'stop_date','stop_month_amt','stop_year_amt','invoice_amount','detail_json','u_id','u_service_json','report_id',
        );
        $saveList=array();
        foreach ($saveKey as $key){
            if(key_exists($key,$data)){
                $saveList[$key]=is_array($data[$key])?json_encode($data[$key],JSON_UNESCAPED_UNICODE):$data[$key];
            }
        }
        $saveList["lcu"]=$this->username;
        Yii::app()->db->createCommand()->insert("sal_contract_virtual",$saveList);
        $vir_id = Yii::app()->db->getLastInsertID();
        $saveList["pro_vir_type"]=1;
        $saveList["cont_id"]=$data["cont_id"];
        $saveList["pro_id"]=$data["pro_id"];
        $saveList["vir_id"]=$vir_id;
        $saveList["pro_code"]="VDL-".$data["vir_code"];
        $saveList["pro_type"]=$this->proTypeByStatus($data["vir_status"]);
        $saveList["pro_date"]=$data["sign_date"];
        $saveList["pro_remark"]="导入虚拟合约\n导入id：{$this->id}";
        $saveList["pro_status"]=30;
        $saveList["pro_change"]=$data["vir_status"]==30?$data["year_amt"]:$data["surplus_amt"];
        $saveList["pro_change"] = empty($saveList["pro_change"])?0:$saveList["pro_change"];
        Yii::app()->db->createCommand()->insert("sal_contpro_virtual",$saveList);
        if(!empty($data["virInfo"])){
            foreach ($data["virInfo"] as $virInfo){
                $virInfo["virtual_id"]=$vir_id;
                $virInfo["lcu"]=$this->username;
                Yii::app()->db->createCommand()->insert("sal_contract_vir_info",$virInfo);
            }
        }
        Yii::app()->db->createCommand()->insert("sal_contract_vir_staff",array(
            "vir_id"=>$vir_id,
            "type"=>4,
            "employee_id"=>$data['sales_id'],
            "u_yewudalei"=>$data['yewudalei'],
            "role"=>1,
            "u_id"=>isset($data['sales_u_id'])?$data['sales_u_id']:null,
            "lcu"=>$this->username,
        ));
        if(!empty($data["other_sales_u_id"])){
            Yii::app()->db->createCommand()->insert("sal_contract_vir_staff",array(
                "vir_id"=>$vir_id,
                "type"=>5,
                "employee_id"=>$data['other_sales_id'],
                "u_yewudalei"=>$data['other_yewudalei'],
                "role"=>0,
                "u_id"=>$data['other_sales_u_id'],
                "lcu"=>$this->username,
            ));
        }
        if(!empty($data["u_service_json"]["list"])){
            foreach ($data["u_service_json"]["list"] as $weekList){
                $weekList["vir_id"]=$vir_id;
                $weekList["lcu"]=$this->username;
                Yii::app()->db->createCommand()->insert("sal_contract_vir_week",$weekList);
            }
        }
        Yii::app()->db->createCommand()->update("sal_clue",array(
            "clue_status"=>$this->getClientStatusByClueID($data["clue_id"]),
        ),"id=:id",array(":id"=>$data["clue_id"]));
        Yii::app()->db->createCommand()->update("sal_clue_store",array(
            "store_status"=>$this->getStoreStatusByStoreID($data["clue_store_id"]),
        ),"id=:id",array(":id"=>$data["clue_store_id"]));
    }

    protected function getClientStatusByClueID($clue_id){
        $suffix = Yii::app()->params['envSuffix'];
        $statusRow = Yii::app()->db->createCommand()->select("min(a.vir_status) as min_status,max(a.vir_status) as max_status")->from("sales{$suffix}.sal_contract_virtual a")
            ->where("a.clue_id={$clue_id} and a.vir_status in (10,30,40,50)")->group("a.vir_status")->queryRow();//
        $status=1;
        if($statusRow){
            $status=$statusRow["min_status"];
        }
        return $status;
    }

    protected function getStoreStatusByStoreID($store_id){
        $suffix = Yii::app()->params['envSuffix'];
        $statusRow = Yii::app()->db->createCommand()->select("min(a.vir_status) as min_status,max(a.vir_status) as max_status")->from("sales{$suffix}.sal_contract_virtual a")
            ->where("a.clue_store_id={$store_id} and a.vir_status in (10,30,40,50)")->group("a.vir_status")->queryRow();//
        $status=1;
        if($statusRow){
            $status=$statusRow["min_status"];
        }
        return $status;
    }
}
