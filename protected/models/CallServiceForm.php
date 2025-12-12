<?php

class CallServiceForm extends CFormModel
{
    public $id;//
    public $pro_type="CAll";//呼叫式服务
    public $call_remark;//操作备注
    public $call_status=0;//操作进行中的状态
    public $clue_id;//
    public $clue_type;//
    public $cont_id;//
    public $busine_id;//
    public $month_char;//月金额字段
    public $year_char;//年金额字段
    public $busine_id_text;//
    public $apply_date;//
    public $call_json;//
    public $call_month_json;//
    public $call_text;//
    public $call_sum=0;//
    public $call_amt=0;//
    public $store_ids;//
    public $store_num;//
    public $vir_json;//
    public $vir_ids;//
    public $pro_ids;//
    public $city;//
    public $mh_id;//
    public $mh_remark;//
    public $lcu;//
    public $luu;//
    public $lcd;//
    public $lud;//

    public $goMhWebUrl;//

    public $contHeadRow=array();

    public function attributeLabels()
    {
        $list = array(
            'id'=>Yii::t('clue','call id'),//呼叫id
            'call_code'=>Yii::t('clue','call code'),//
            'call_remark'=>Yii::t('clue','call remark'),//
            'call_status'=>Yii::t('clue','call status'),//
            'busine_id'=>Yii::t('clue','busine id'),//
            'call_json'=>Yii::t('clue','call json'),//
            'store_ids'=>Yii::t('clue','store ids'),//
            'mh_id'=>Yii::t('clue','mh id'),//
        );
        return $list;
    }

    public function rules(){
        $list=array();
        $list[] = array('id,cont_id,apply_date,busine_id,call_json,store_ids','safe');
        $list[]=array('id','validateID');
        $list[]=array('cont_id','required');
        $list[]=array('cont_id','validateContID');
        $list[]=array('busine_id','validateBusineID');
        $list[]=array('call_json','validateCallJson');
        $list[]=array('store_ids','validateStore');
        $list[]=array('call_sum','computeCallSum','on'=>array('audit'));
        return $list;
    }

    public function computeCallSum($attribute, $param){
        if(empty($this->busine_id)){
            $this->addError($attribute,"请选择服务项目");
        }
        if(empty($this->call_month_json)){
            $this->addError($attribute,"请选择服务频次");
        }
        if(empty($this->vir_json)){
            $this->addError($attribute,"请选择呼叫的门店");
        }else{
            foreach ($this->vir_json as $row){
                if(empty($row["u_id"])){
                    $this->addError($attribute,"合约({$row["vir_code"]})还未同步到派单系统，无法呼叫");
                }
            }
        }
    }

    public function validateCallJson($attribute, $param){
        $this->call_sum=0;
        $call_text=array();
        $this->call_month_json=array();
        if(!empty($this->call_json)){
            foreach ($this->call_json as $year=>$monthList){
                $monthText=array();
                foreach ($monthList as $month=>$item){
                    if(!empty($item)&&is_numeric($item)){
                        $item = intval($item);
                        if(!isset($monthText[$month])){
                            $monthText[]="{$item}次/{$month}月;";
                            $key = date("Y/m/01",strtotime("{$year}/{$month}/01"));
                            $this->call_month_json[$key]=$item;
                        }
                        $this->call_sum+=$item;
                    }
                }
                if(!empty($monthText)){
                    $call_text[]="{$year}:".implode("",$monthText);
                }
            }
        }
        $this->call_text = implode("<br/>",$call_text);
    }

    public function validateStore($attribute, $param){
        $store_ids=explode(",",$this->store_ids);
        $storeList=array();
        $virList=array();
        $vir_json=array();
        $this->call_amt=0;
        $this->store_num=0;
        if(!empty($store_ids)){
            foreach ($store_ids as $store_id){
                $row = Yii::app()->db->createCommand()->select("*")->from("sal_contract_virtual")
                    ->where("vir_status in (10,30)  and service_fre_type=3 and cont_id=:cont_id and clue_store_id=:clue_store_id and busine_id=:busine_id",array(
                        ":clue_store_id"=>$store_id,
                        ":busine_id"=>$this->busine_id,
                        ":cont_id"=>$this->cont_id,
                    ))->queryRow();//虚拟合约生效中且是呼叫式服务
                if($row){
                    $virBool = Yii::app()->db->createCommand()->select("*")->from("sal_contpro_virtual")
                        ->where("vir_id=:vir_id and pro_status!=0 and pro_vir_type!=3",array(
                            ":vir_id"=>$row["id"],
                        ))->order("id desc")->queryRow();//虚拟合约最后状态
                    if(in_array($virBool["pro_status"],array(10,30))){
                        $storeList[]=$store_id;
                        $virList[]=$row["id"];
                        $vir_json[]=$row;
                        $amt = $this->call_sum*$row["call_fre_amt"];//总次数*每次价格
                        $this->call_amt+=$amt;
                    }
                }
            }
        }
        $this->store_num = count($storeList);
        $this->store_ids = implode(",",$storeList);
        $this->vir_ids = implode(",",$virList);
        $this->vir_json = $vir_json;
    }

    public function validateContID($attribute, $param){
        if(!empty($this->cont_id)){
            $contModel = new ContForm("view");
            if($contModel->retrieveData($this->cont_id)){
                $this->clue_id=$contModel->clue_id;
                $this->clue_type=$contModel->clue_type;
                $this->city=$contModel->city;
                $this->contHeadRow = $contModel->getAttributes();
            }else{
                $this->addError($attribute,"合同不存在（{$this->cont_id}）");
            }
        }
    }

    public function validateBusineID($attribute, $param){
        if(!empty($this->busine_id)){
            $busine_ids=$this->contHeadRow["busine_id"];
            if(in_array($this->busine_id,$busine_ids)){
                $row = Yii::app()->db->createCommand()->select("*")->from("sal_service_type")
                    ->where("id_char=:id_char",array(":id_char"=>$this->busine_id))
                    ->queryRow();
                if(!empty($row)){
                    $this->busine_id_text=$row["name"];
                    $this->month_char="svc_".$row["id_char"];
                    $infoRow = Yii::app()->db->createCommand()->select("a.id_char")->from("sal_service_type_info a")
                        ->leftJoin("sal_service_type b","a.type_id=b.id")
                        ->where("b.id_char=:id_char and input_type='yearAmount'",array(":id_char"=>$this->busine_id))
                        ->queryRow();
                    $this->year_char=$infoRow?"svc_".$infoRow["id_char"]:"";
                }else{
                    $this->addError($attribute,"数据异常（busine_id：{$this->busine_id}）");
                }
            }
        }
    }

    public function validateID($attribute, $param) {
        if(!empty($this->id)){
            $row = Yii::app()->db->createCommand()->select("*")->from("sal_contract_call")
                ->where("id=:id",array(":id"=>$this->id))->queryRow();//
            if($row){
                $this->cont_id = $row["cont_id"];
                $this->apply_date = date("Y/m/d",strtotime($row["apply_date"]));
            }else{
                $this->addError($attribute, "数据异常，请刷新重试");
            }
        }
    }

    public function showCallView(){
        $this->validateContID("cont_id","");
        $this->validateStore("cont_id","");
    }

    public function retrieveData($index)
    {
        $index = empty($index)||!is_numeric($index)?0:$index;
        $sql = "select a.* from sal_contract_call a where a.id=".$index;
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $this->id = $row['id'];
            $this->clue_id = $row['clue_id'];
            $this->clue_type = $row['clue_type'];
            $this->cont_id = $row['cont_id'];
            $this->apply_date = date("Y/m/d",strtotime($row['apply_date']));
            $this->call_status = $row['call_status'];
            $this->call_json = json_decode($row['call_json'],true);
            $this->call_month_json = json_decode($row['call_month_json'],true);
            $this->call_text = $row['call_text'];
            $this->call_sum = $row['call_sum'];
            $this->call_amt = $row['call_amt'];
            $this->store_num = $row['store_num'];
            $this->store_ids = $row['store_ids'];
            $this->vir_ids = $row['vir_ids'];
            $this->pro_ids = $row['pro_ids'];
            $this->call_remark = $row['call_remark'];

            $this->city = $row['city'];
            $this->mh_remark = $row['mh_remark'];
            $this->mh_id = $row['mh_id'];
            $this->busine_id = $row['busine_id'];
            $this->busine_id_text = $row['busine_id_text'];
            $this->lcu = $row['lcu'];
            $this->luu = $row['luu'];
            $this->lcd = $row['lcd'];
            $this->lud = $row['lud'];
            return true;
        }else{
            return false;
        }
    }

    public function setScenarioByID(){
        if($this->getScenario()=="delete"){

        }elseif(empty($this->id)){
            $this->setScenario('new');
        }else{
            $this->setScenario('edit');
        }
    }

    public function saveData()
    {
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try {
            $this->setScenarioByID();
            $this->save($connection);
            $transaction->commit();
            $this->sendDataToMH();//发送消息至门户网站
            return true;
        }catch(Exception $e) {
            $transaction->rollback();
            $errorMsg = isset($e->statusCode)?$e->statusCode:"Cannot update";
            $errorMsg.= "：";
            $errorMsg.= $e->getMessage();
            throw new CHttpException(404,$errorMsg);
        }
    }

    protected function getMySaveArr(){
        $saveArr= array(
            "clue_id"=>$this->clue_id,
            "clue_type"=>$this->clue_type,
            "cont_id"=>$this->cont_id,
            "busine_id"=>$this->busine_id,
            "busine_id_text"=>$this->busine_id_text,
            "apply_date"=>$this->apply_date,
            "call_status"=>$this->call_status,
            "call_json"=>empty($this->call_json)?null:json_encode($this->call_json,JSON_UNESCAPED_UNICODE),
            "call_month_json"=>empty($this->call_month_json)?null:json_encode($this->call_month_json,JSON_UNESCAPED_UNICODE),
            "call_text"=>$this->call_text,
            "store_num"=>CGetName::getNumberNull($this->store_num),
            "call_sum"=>CGetName::getNumberNull($this->call_sum),
            "call_amt"=>CGetName::getNumberNull($this->call_amt),
            "store_ids"=>$this->store_ids,
            "vir_ids"=>$this->vir_ids,
            "pro_ids"=>$this->pro_ids,
            "call_remark"=>$this->call_remark,
            "mh_remark"=>$this->mh_remark,
            "city"=>$this->city,
        );
        return $saveArr;
    }

    protected function save(&$connection)
    {
        $uid = Yii::app()->user->id;
        //$this->cont_month_len = CGetName::computeMothLenBySE($this->virJson["cont_start_dt"],$this->virJson["cont_end_dt"]);
        //contract_code
        $saveArr = $this->getMySaveArr();
        //pro_change
        switch ($this->getScenario()){
            case "new":
                $saveArr["lcu"]=$uid;
                $saveArr["mh_id"]=null;
                $connection->createCommand()->insert("sal_contract_call",$saveArr);
                $this->id = Yii::app()->db->getLastInsertID();
                $connection->createCommand()->update("sal_contract_call",array(
                    "call_code"=>"CALL".(10000+$this->id)
                ),"id=:id",array(":id"=>$this->id));
                $connection->createCommand()->insert("sal_contract_history",array(
                    "table_id"=>$this->id,
                    "table_type"=>9,
                    "history_type"=>1,
                    "history_html"=>"<span>新增</span>",
                    "lcu"=>$uid,
                ));
                break;
            case "edit":
                $saveArr["luu"]=$uid;
                $connection->createCommand()->update("sal_contract_call",$saveArr,"id=:id",array(":id"=>$this->id));
                break;
            case "delete":
                $connection->createCommand()->delete("sal_contract_call","id=:id",array(":id"=>$this->id));
                $connection->createCommand()->delete("sal_contpro_virtual","pro_vir_type=3 and call_id=:call_id",array(":call_id"=>$this->id));
                $connection->createCommand()->delete("sal_contract_history","table_type=9 and table_id=:table_id",array(":table_id"=>$this->id));
        }
        if($this->getScenario()!="delete"){
            $this->addContractVir();//增加合约
        }
        return true;
    }

    protected function getSaveVirData($virtualRow,$contStart,$serviceSum){
        $data = $virtualRow;
        unset($data["id"]);
        unset($data["lcu"]);
        unset($data["lcd"]);
        unset($data["luu"]);
        unset($data["lud"]);
        $amt = $virtualRow["call_fre_amt"]*$serviceSum;
        $monthNum = date("Y年n",strtotime($contStart));
        $service_fre_text="呼叫式;";
        $service_fre_text.="{$monthNum}月/{$serviceSum}次;";
        $detail_json = empty($virtualRow["detail_json"])?array():json_decode($virtualRow["detail_json"],true);
        $detailArr = array(
            "{$this->month_char}"=>$amt,
            "{$this->month_char}FreAmt"=>$amt,
            "{$this->month_char}FreSum"=>$serviceSum,
            "{$this->month_char}FreText"=>$service_fre_text,
        );
        if(!empty($this->year_char)){
            $detailArr[$this->year_char]=$amt;
        }
        foreach ($detailArr as $key=>$value){
            $detail_json[$key] = $value;
        }
        $service_fre_json = empty($virtualRow["service_fre_json"])?array():json_decode($virtualRow["service_fre_json"],true);
        $service_fre_json["fre_amt"]=$amt;
        $service_fre_json["fre_sum"]=$serviceSum;
        $service_fre_json["fre_list"]=array(
            //{"month":[],"fre_num":1,"type_sum":1,"fre_amt":200,"type_amt":1}
            array(
                "month"=>array($monthNum),
                "fre_num"=>$serviceSum,
                "type_sum"=>1,
                "fre_amt"=>$virtualRow["call_fre_amt"],
                "type_amt"=>1,
            )
        );
        $dataEx = array(
            "pro_vir_type"=>3,
            "call_id"=>$this->id,
            "vir_id"=>$virtualRow["id"],
            "pro_type"=>$this->pro_type,
            "pro_num"=>CGetName::getProNumByCall($virtualRow["id"]),
            "pro_date"=>$contStart,
            "pro_remark"=>$this->call_remark,
            "pro_status"=>$this->call_status,
            "pro_change"=>$amt,
            "vir_status"=>30,
            "sign_type"=>1,
            "month_amt"=>$amt,//月金额
            "year_amt"=>$amt,//年金额
            "service_sum"=>$serviceSum,//服务总次数
            "service_fre_amt"=>$amt,//服务频次总金额
            "service_fre_sum"=>$serviceSum,//服务频次总次数
            "service_fre_json"=>json_encode($service_fre_json,JSON_UNESCAPED_UNICODE),//服务频次
            "service_fre_text"=>$service_fre_text,//服务频次(文字)
            "cont_start_dt"=>$contStart,//合约开始时间
            "cont_end_dt"=>date("Y/m/t",strtotime($contStart)),//合约结束时间
            "cont_month_len"=>1,//合同月份
            "invoice_amount"=>$amt,//发票金额
            "detail_json"=>json_encode($detail_json,JSON_UNESCAPED_UNICODE),//
        );
        foreach ($dataEx as $key=>$item){
            $data[$key]=$item;
        }
        return $data;
    }

    protected function addVirDataByMonthJson($virtualRow){
        $uid = Yii::app()->user->id;
        if(!empty($this->call_month_json)){
            foreach ($this->call_month_json as $contStart=>$serviceSum){
                $saveData = $this->getSaveVirData($virtualRow,$contStart,$serviceSum);
                $virSaveArr["lcu"]=$uid;
                $virSaveArr["lcd"]=date("Y-m-d H:i:s");
                Yii::app()->db->createCommand()->insert("sal_contpro_virtual",$saveData);
                $virtualId = Yii::app()->db->getLastInsertID();
                Yii::app()->db->createCommand()->update("sal_contpro_virtual",array(
                    "pro_code"=>"CALL".(10000+$virtualId)
                ),"id=".$virtualId);
            }
        }
    }

    protected function addContractVir(){
        Yii::app()->db->createCommand()->delete("sal_contpro_virtual","pro_vir_type=3 and call_id=:call_id",array(":call_id"=>$this->id));
        if(!empty($this->vir_json)){//
            foreach ($this->vir_json as $virtualRow){
                $this->addVirDataByMonthJson($virtualRow);
            }
        }
    }

    //发送消息至门户网站
    protected function sendDataToMH(){
        $list = array("bool"=>true,"msg"=>"");//true:成功
        if($this->call_status==1){//发送
            if(!empty($this->mh_id)){
                return $this->sendDataToMHByUpdate();
            }else{
                return $this->sendDataToMHByNew();
            }
        }
        return $list;
    }

    protected function sendDataToMHByUpdate(){
        $list = array("bool"=>true,"msg"=>"");//true:成功
        $uid = Yii::app()->user->id;
        $noticeModel = new CNoticeFlowModel();
        $url = "/openApi/runtime/instance/v1/setVariables?instId=".$this->mh_id;
        $data = $this->getMHData();
        $outData = $noticeModel->sendMHPostByUrlAndData($url,$data);
        if(!$outData["status"]){//发送修改数据
            $list["bool"] = false;
            $list["msg"]=isset($outData["outData"]["message"])?$outData["outData"]["message"]:$outData["message"];
        }else{
            $taskId = $this->getMHTaskID();
            $url = CGetName::getMHUrlPrx()."/matter/approvalForm?type=request&taskId={$taskId}&instId={$this->mh_id}&isGetApprovalBtn=true";
            $this->goMhWebUrl =CGetName::getMHWebUrlByUrl($url);
            $historyList=array("table_type"=>9,"table_id"=>$this->id,"lcu"=>$uid,"history_type"=>30,"history_html"=>"<span>重新发起</span>");
            Yii::app()->db->createCommand()->insert("sal_contract_history",$historyList);
        }
        return $list;
    }

    protected function getMHData(){
        $lbsCityCode = array();
        $rows = Yii::app()->db->createCommand()->select("city")->from("sal_clue_store")
            ->where("id in ({$this->store_ids})")->queryAll();//
        if($rows){
            foreach ($rows as $row){
                $lbsCityCode[]=$row["city"];
            }
        }
        $list = array(
            "lbsMain"=>CGetName::getLbsMainStrByKeyAndStr($this->contHeadRow['lbs_main'],'mh_code'),//主体公司编码
            "lbsMainCityCode"=>$this->contHeadRow['city'],//主城市编码
            "lbsCityCode"=>empty($lbsCityCode)?"":implode(",",$lbsCityCode),//门店城市编码
            "lbsBizCatCode"=>CGetName::getYewudaleiStrByKey($this->contHeadRow['yewudalei'],'mh_code'),//业务大类编码
            "saleId"=>CGetName::getEmployeeStrByKey('bs_staff_id',$this->contHeadRow['sales_id']),//销售人员北森id
            "totalAmt"=>$this->call_amt,//
            "customerName"=>CGetName::getClueNameByID($this->clue_id),
        );
        return $list;
    }

    protected function getMHTaskID(){
        $mhModel = new CMHCurlModel();
        $mhModel->printBool=true;
        $taskId = "";
        $outData = $mhModel->sendMHFlowByGet(array("instId"=>$this->mh_id));//
        if($outData["status"]){
            $mhRows = $outData["outData"]["value"]["rows"];
            if($mhRows){
                foreach ($mhRows as $row){
                    $taskId=$row["taskId"];
                    return $taskId;
                }
            }
        }
        return $taskId;
    }

    protected function sendDataToMHByNew(){
        $list = array("bool"=>true,"msg"=>"");//true:成功
        $uid = Yii::app()->user->id;
        $noticeModel = new CNoticeFlowModel();
        $dataEx = array(
            "vars"=>$this->getMHData()
        );
        $businesskey="call_".$this->id;
        $outData = $noticeModel->sendMHAuditByDataEx($businesskey,"LBShjfwsp",$dataEx);
        if(!$outData["status"]){
            $list["bool"] = false;
            $list["msg"]=isset($outData["outData"]["message"])?$outData["outData"]["message"]:$outData["message"];
        }else{
            $instId = isset($outData["outData"]["instId"])?$outData["outData"]["instId"]:null;
            $this->mh_id = $instId;
            $taskID = $this->getMHTaskID();
            Yii::app()->db->createCommand()->update("sal_contract_call",array(
                "mh_id"=>$instId,
            ),"id=:id",array(":id"=>$this->id));
            $historyList=array("table_type"=>9,"table_id"=>$this->id,"lcu"=>$uid,"history_type"=>30,"history_html"=>"<span>发起审批</span>");
            $historyList["expr_data"]=$instId;
            Yii::app()->db->createCommand()->insert("sal_contract_history",$historyList);
            $url = CGetName::getMHUrlPrx()."/matter/approvalForm?type=request&taskId={$taskID}&instId={$this->mh_id}&isGetApprovalBtn=true";
            $this->goMhWebUrl =CGetName::getMHWebUrlByUrl($url);
        }
        return $list;
    }

	public function isOccupied($index) {
		$rtn = true;//默认不允许删除
		if($this->retrieveData($index)){
            $rtn = false;//允许删除
        }
		return $rtn;
	}

	public function isReadonly() {
		return $this->getScenario()=='view'||!in_array($this->call_status,array(0,9));
	}

	protected function getMonthStr($monthKey){
        $list=array(
            1=>"一月",
            2=>"二月",
            3=>"三月",
            4=>"四月",
            5=>"五月",
            6=>"六月",
            7=>"七月",
            8=>"八月",
            9=>"九月",
            10=>"十月",
            11=>"十一月",
            12=>"十二月",
        );
        if (isset($list[$monthKey])){
            return $list[$monthKey];
        }else{
            return $monthKey;
        }
    }

	public function getMonthTempHtml($year,$month){
	    $thisDate = date("Y/m/t",strtotime("{$year}/{$month}/01"));
        $ready = $this->isReadonly()?true:false;
	    if($thisDate<$this->apply_date){
	        $ready=true;
        }
        $modelClass = get_class($this);
        $val = isset($this->call_json[$year][$month])?$this->call_json[$year][$month]:'';
        $tmp='<div class="col-md-2 col-xs-4 col-sm-4">';
        $rowClass="row";
        if(!empty($val)){
            $rowClass.=" active";
        }
        if($ready){
            $rowClass.=" disabled";
        }
        $tmp.="<div class='{$rowClass}'>";
        $tmp.='<div class="free-month-text">'.$this->getMonthStr($month).'</div>';
        $tmp.='<div class="free-month-input">';
        $monthStr = date("Y/m/01",strtotime("{$year}/{$month}/01"));
        $tmp.=TbHtml::numberField("{$modelClass}[call_json][{$year}][{$month}]",$val,array("class"=>"change_free_num","placeholder"=>"服务次数","readonly"=>$ready,"data-month"=>"{$monthStr}"));
        $tmp.='</div>';
        $tmp.='</div>';
        $tmp.='</div>';
        return $tmp;
    }

	public function getAjaxStoreList(){
	    $storeIds = explode(",",$this->store_ids);
        $ids=array(0);
        if(!empty($storeIds)){
            foreach ($storeIds as $id){
                if(!empty($id)&&is_numeric($id)){
                    $ids[]=$id;
                }
            }
        }
        $ids = implode(",",$ids);
        $rows = Yii::app()->db->createCommand()->select("a.id,a.clue_store_id")->from("sal_contract_virtual a")
            ->where("a.vir_status in (10,30) and a.service_fre_type=3 and a.cont_id=:cont_id and a.clue_store_id not in({$ids}) and a.busine_id=:busine_id",array(
                ":busine_id"=>$this->busine_id,
                ":cont_id"=>$this->cont_id,
            ))->queryAll();//
        $idStr=array(0);
        if($rows){
            foreach ($rows as $row){
                $virBool = Yii::app()->db->createCommand()->select("*")->from("sal_contpro_virtual")
                    ->where("vir_id=:vir_id and pro_status!=0 and pro_vir_type!=3",array(
                        ":vir_id"=>$row["id"],
                    ))->order("id desc")->queryRow();//虚拟合约最后状态
                if(in_array($virBool["pro_status"],array(10,30))){
                    $idStr[]=$row["clue_store_id"];
                }
            }
        }
        $idStr = implode(",",$idStr);
        $rows = Yii::app()->db->createCommand()->select("a.*,b.invoice_header,b.tax_id,b.invoice_address")
            ->from("sal_clue_store a")
            ->leftJoin("sal_clue_invoice b","a.invoice_id=b.id")
            ->where("a.id in ($idStr) and a.z_display=1")->order("lcd asc")->queryAll();
        return $rows;
    }
}
