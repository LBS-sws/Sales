<?php

class ImportVirForm extends ImportForm
{
    public $visit_type;
    public $visit_obj;
    public $visit_obj_text;

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

    protected function valFeeType(&$data,$keyStr,$item){
        $feeType = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(empty($feeType)){
            $data[$keyStr]=null;
        }else{
            $list=CGetName::getFeeTypeList();
            $key = array_search($feeType, $list);
            if($key!==false){
                $data[$keyStr]=$key;
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$feeType})";
            }
        }
    }

    protected function valBillDay(&$data,$keyStr,$item){
        $billDay = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(empty($billDay)){
            $data[$keyStr]=null;
        }else{
            $list=CGetName::getBillDayList();
            $key = array_search($billDay, $list);
            if($key!==false){
                $data[$keyStr]=$key;
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$billDay})";
            }
        }
    }

    protected function valSettleType(&$data,$keyStr,$item){
        $settleType = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(empty($settleType)){
            $data[$keyStr]=null;
        }else{
            $list=CGetName::getSettleTypeList();
            $key = array_search($settleType, $list);
            if($key!==false){
                $data[$keyStr]=$key;
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$settleType})";
            }
        }
    }

    protected function valPayWeek(&$data,$keyStr,$item){
        $payWeek = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(empty($payWeek)){
            $data[$keyStr]=null;
        }else{
            $list=CGetName::getPayWeekList();
            $key = array_search($payWeek, $list);
            if($key!==false){
                $data[$keyStr]=$key;
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$payWeek})";
            }
        }
    }

    protected function valReceivableDay(&$data,$keyStr,$item){
        $receivableDay = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(empty($receivableDay)){
            $data[$keyStr]=null;
        }else{
            $list=CGetName::getReceivableDayList();
            $key = array_search($receivableDay, $list);
            if($key!==false){
                $data[$keyStr]=$key;
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$receivableDay})";
            }
        }
    }

    protected function valPayType(&$data,$keyStr,$item){
        $payType = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(empty($payType)){
            $data[$keyStr]=null;
        }else{
            $list=CGetName::getPayTypeList();
            $key = array_search($payType, $list);
            if($key!==false){
                $data[$keyStr]=$key;
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$payType})";
            }
        }
    }

    protected function valTechnicianList(&$data,$keyStr,$item){
        $codeStr = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(!empty($codeStr)){
            $ids=array();
            $names=array();
            $codeList = explode(",",$codeStr);
            foreach ($codeList as $code){
                if(!empty($code)){
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
                $data[$keyStr]=implode(",",$ids);
                $data["technician_id_text"]=implode(",",$names);
            }else{
                $data[$keyStr]=null;
            }
        }else{
            $data[$keyStr]=null;
        }
    }

    protected function valStopSet(&$data,$keyStr,$item){
        $stopName = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(empty($stopName)||!in_array($data["vir_status"],array(50,40))){
            $data[$keyStr]=null;
        }else{
            $s_type=$data["vir_status"]==40?1:2;
            $stopRow=Yii::app()->db->createCommand()->select("*")->from("sal_cont_str")
                ->where("name=:name and str_type={$s_type}",array(":name"=>$stopName))->queryRow();
            if($stopRow){
                $data[$keyStr]=$stopRow["id"];
            }else{
                $stopRow=Yii::app()->db->createCommand()->select("*")->from("sal_cont_str")
                    ->where("str_type={$s_type}")->order("id desc")->queryRow();
                if($stopRow){
                    $data[$keyStr]=$stopRow["id"];
                }else{
                    $data[$keyStr]=null;
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

    protected function valBusine(&$data,$keyStr,$item){
        $busineName = key_exists($keyStr,$data)?$data[$keyStr]:'';
        $contRow = key_exists("contRow",$data)?$data["contRow"]:array();
        if(!empty($busineName)){
            $row = Yii::app()->db->createCommand()->select("id,service_type,id_char")->from("sal_service_type")
                ->where("name=:name",array(":name"=>$busineName))->queryRow();
            if($row){
                if(!empty($contRow)){
                    $contRow["busine_id"] = explode(",",$contRow["busine_id"]);
                    if(!in_array($row["id_char"],$contRow["busine_id"])){
                        $this->status="E";
                        $this->message=$item['name']."异常，主合同({$contRow['cont_code']})不存在该服务项目({$busineName})";
                    }
                }
                $data["service_type"]=$row["service_type"];
                $data["busine_id"]=$row["id_char"];
                $data["busine_id_int"]=$row["id"];
                $data["busine_id_text"]=$busineName;
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

    protected function valYewudalei(&$data,$keyStr,$item){
        $yewudalei = isset($data[$keyStr])?$data[$keyStr]:"-1";
        $row = Yii::app()->db->createCommand()->select("id")->from("sal_yewudalei")
            ->where("name=:name",array(":name"=>$yewudalei))->queryRow();
        if($row){
            $data[$keyStr]=$row["id"];
        }else{
            $this->status="E";
            $this->message=$item['name']."不存在";
        }
    }

    protected function valCodeMain(&$data,$keyStr,$item){
        $codeMain = isset($data[$keyStr])?$data[$keyStr]:"";
        $city = isset($data["city"])?$data["city"]:"";
        if(!empty($codeMain)){
            $row = Yii::app()->db->createCommand()->select("id")->from("sal_main_lbs")
                ->where("(show_type=2 or (show_type=1 AND city='{$city}') or (show_type=3 AND FIND_IN_SET('{$city}',show_city))) and mh_code=:mh_code",
                    array(":mh_code"=>$codeMain)
                )->queryRow();
            if($row){
                $data[$keyStr]=$row["id"];
            }else{
                $this->status="E";
                $this->message="城市{$city}没有{$item['name']}编号：{$codeMain}";
            }
        }else{
            $data[$keyStr]=null;
        }
    }

    protected function valCodeMainAll(&$data,$keyStr,$item){
        $codeMain = isset($data[$keyStr])?$data[$keyStr]:"";
        if(!empty($codeMain)){
            $row = Yii::app()->db->createCommand()->select("id")->from("sal_main_lbs")
                ->where("mh_code=:mh_code",
                    array(":mh_code"=>$codeMain)
                )->queryRow();
            if($row){
                $data[$keyStr]=$row["id"];
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在";
            }
        }else{
            $data[$keyStr]=null;
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

    protected function computeContID(&$data){
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
        //sal_contract_sse
        $data["cont_id"] = Yii::app()->db->getLastInsertID();
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

    protected function saveOneData($data){
        if(empty($data["cont_id"])){
            $this->computeContID($data);
        }
        $this->computeContSSEID($data);
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
            "clue_status"=>ClueVirProModel::getClientStatusByClueID($data["clue_id"]),
        ),"id=:id",array(":id"=>$data["clue_id"]));
        Yii::app()->db->createCommand()->update("sal_clue_store",array(
            "store_status"=>ClueVirProModel::getStoreStatusByStoreID($data["clue_store_id"]),
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
