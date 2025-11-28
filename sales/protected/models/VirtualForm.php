<?php

class VirtualForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $cont_id;
	public $clue_id;
	public $clue_type;
	public $city="ZH";//限货币使用，其它地方不使用
	public $clue_service_id;
	public $clue_store_id;
    public $vir_code;
    public $vir_status;
    public $busine_id;
    public $busine_id_text;
	public $create_staff;
	public $yewudalei;
	public $other_yewudalei;
	public $sales_id;
	public $other_sales_id;
	public $month_amt;
	public $year_amt;
	public $service_sum;
	public $surplus_num;
	public $surplus_amt;
	public $service_fre_amt;
	public $service_fre_sum;
	public $service_fre_type;
	public $service_fre_json;
	public $service_fre_text;
	public $cont_start_dt;
	public $cont_end_dt;
	public $cont_month_len;
	public $effect_date;
	public $fast_date;
	public $first_date;
	public $stop_date;
	public $need_install;
	public $amt_install;
	public $remark;
    public $service = array();
    public $serviceJson = array();
	public $check;
    public $mh_id;
    public $u_id;
    public $u_service_json;

    public $sign_type;
    public $lbs_main;
    public $service_main;
    public $sign_date;
    public $prioritize_service;
    public $service_timer;
    public $pay_week;
    public $pay_type;
    public $pay_month;
    public $pay_start;
    public $deposit_need;
    public $deposit_amt;
    public $deposit_rmk;
    public $fee_type;
    public $settle_type;
    public $bill_day;
    public $profit_int;
    public $bill_bool;
    public $receivable_day;
    public $seal_type_id;
    public $prioritize_seal;
    public $is_seal;
    public $is_renewal;
    public $con_v_type;
    public $stop_month_amt;//
    public $stop_year_amt;//
    public $stop_sum_amt;//
    public $need_back;//
    public $need_back_json;//
    public $surplus_json;//

    public $update_bool=1;//允许修改
    public $ltNowDate=false;

	public $contHeadRow;
	public $storeHeadRow;

    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		$list = array(
            'vir_id'=>Yii::t('clue','virtual id'),//
            'clue_id'=>Yii::t('clue','clue id'),//线索id
            'vir_code'=>Yii::t('clue','virtual code'),//虚拟合同编号
            'busine_id_text'=>Yii::t('clue','service obj'),//服务项目
            'vir_status'=>Yii::t('clue','status'),//状态
            'yewudalei'=>Yii::t('clue','yewudalei'),//业务大类
            'sign_type'=>Yii::t('clue','sign type'),//签约类型
            'sign_date'=>Yii::t('clue','sign date'),//签约类型
            'city'=>Yii::t('clue','city manger'),//城市
            'other_sales_id'=>Yii::t('clue','other sales'),//跨区业务员
            'other_yewudalei'=>Yii::t('clue','other yewudalei'),//跨区业务大类
            //'year_amt'=>Yii::t('clue','invoice amt'),//预估成交金额
            'cont_code'=>Yii::t('clue','contract top code'),//主合同编号
            'lbs_main'=>Yii::t('clue','lbs main'),//客户主体
            'service_main'=>Yii::t('clue','service main'),//服务主体
            'sales_id'=>Yii::t('clue','sales'),//
            'store_code'=>Yii::t('clue','store code'),//门店编号
            'store_name'=>Yii::t('clue','store name'),//门店名
            'cont_start_dt'=>Yii::t('clue','contract start date'),//合约开始时间
            'cont_end_dt'=>Yii::t('clue','contract end date'),//合约结束时间
            'mh_id'=>Yii::t('clue','report mh id'),//
            'lcd'=>Yii::t('clue','create date'),//
            'lud'=>Yii::t('clue','update date'),//
            'lcu'=>Yii::t('clue','create staff'),//
            'luu'=>Yii::t('clue','update staff'),//
            'prioritize_service'=>Yii::t('clue','prioritize service'),//是否优先安排服务
            'service_timer'=>Yii::t('clue','service timer'),//服务时长
            'predict_amt'=>Yii::t('clue','dict amt'),//预估金额
            'pay_week'=>Yii::t('clue','pay week'),//付款周期
            'pay_type'=>Yii::t('clue','pay type'),//付款方式
            'pay_month'=>Yii::t('clue','pay month'),//预付月数
            'pay_start'=>Yii::t('clue','pay start'),//起始月
            'deposit_need'=>Yii::t('clue','deposit need'),//所需押金
            'deposit_amt'=>Yii::t('clue','deposit amt'),//已收押金
            'deposit_rmk'=>Yii::t('clue','deposit rmk'),//押金备注
            'fee_type'=>Yii::t('clue','fee type'),//收费方式
            'settle_type'=>Yii::t('clue','settle type'),//结算方式
            'bill_day'=>Yii::t('clue','bill day'),//账单日
            'bill_bool'=>Yii::t('clue','bill bool'),//是否对账
            'profit_int'=>Yii::t('clue','profit'),//
            'receivable_day'=>Yii::t('clue','receivable day'),//应收期限
            'con_v_type'=>Yii::t('clue','cont type'),//合同类型
            'is_seal'=>Yii::t('clue','is seal'),//
            'is_renewal'=>Yii::t('clue','is renewal'),//
            'seal_type_id'=>Yii::t('clue','seal type'),//印章类型
            'prioritize_seal'=>Yii::t('clue','prioritize seal'),//是否客户先用印
            'first_date'=>Yii::t('clue','first date'),//首次日期
            'fast_date'=>Yii::t('clue','fast date'),//常规开始日期
            'surplus_num'=>Yii::t('clue','surplus num'),//剩余次数
            'surplus_amt'=>Yii::t('clue','surplus amt'),//剩余金额
            'pro_code'=>Yii::t('clue','pro code'),//操作编号
            'pro_type'=>Yii::t('clue','pro type'),//操作类型
            'pro_num'=>Yii::t('clue','pro num'),//同类型操作次数
            'pro_date'=>Yii::t('clue','pro date'),//操作生效时间
            'pro_remark'=>Yii::t('clue','pro remark'),//操作备注
            'pro_status'=>Yii::t('clue','status'),//操作进行中的状态
            'u_service_json'=>Yii::t('clue','u service json'),//操作进行中的状态
            'stop_set_id'=>Yii::t('clue','stop set id'),//终止、暂停原因id
            'stop_month_amt'=>Yii::t('clue','and month amt'),//涉及月金额
            'stop_year_amt'=>Yii::t('clue','and year amt'),//涉及年金额
            'stop_sum_amt'=>Yii::t('clue','and sum amt'),//涉及总金额
            'service_fre_sum'=>Yii::t('clue','service fre sum'),//
            'service_fre_amt'=>Yii::t('clue','service fre amt'),//
            'service_fre_text'=>Yii::t('clue','service fre text'),//
            'service_fre_type'=>Yii::t('clue','service fre type'),//
            'month_amt'=>Yii::t('clue','month amt'),//
            'year_amt'=>Yii::t('clue','year amt'),//
            'need_back'=>Yii::t('clue','need back'),//
            'need_back_json'=>Yii::t('clue','need back json'),//
		);
		return $list;
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
	    $list = array();
        $list[]=array('id,clue_id,sse_id,cont_id,clue_service_id,clue_store_id,create_staff,sales_id,month_amt,year_amt,service_fre_amt,service_fre_sum,service_fre_type,service_fre_json,service_fre_text,
        cont_start_dt,cont_end_dt,cont_month_len,detail_json,u_id,fast_date,effect_date,stop_date,surplus_num,surplus_amt,service_sum,
        yewudalei,vir_code,vir_status,sign_type,busine_id,busine_id_text,lbs_main,sign_date,cont_start_dt,cont_end_dt,prioritize_service,service_timer,
        con_v_type,is_seal,is_renewal,seal_type_id,prioritize_seal,pay_week,pay_type,deposit_need,deposit_amt,deposit_rmk,fee_type,pay_month,pay_start,settle_type,
        bill_day,receivable_day,bill_bool,profit_int,other_sales_id,other_yewudalei','safe');
        $list[]=array('id','required');
        $list[]=array('id','validateID');
        $list[]=array('cont_id','validateRowByID');
        $list[]=array('lbs_main,sign_date,cont_start_dt,cont_end_dt,prioritize_service,
        pay_week,pay_type,fee_type,settle_type,bill_day,receivable_day,bill_bool','required','on'=>array("batch"));
        //$list[]=array('clue_service_id','validateClueServiceID');
        $list[]=array('is_seal','validateIsSeal','on'=>array('batch'));
        $list[]=array('is_renewal','validateRenewal','on'=>array('batch'));
        $list[]=array('fee_type','validateFeeType','on'=>array('batch'));
        $list[]=array('service','validateServiceAmount','on'=>array("edit","batch"));
        $list[]=array('service','validateServices','on'=>array("edit","batch"));
        $list[]=array('other_yewudalei','validateOtherSales','on'=>array("edit","batch"));

		return $list;
	}

    public function validateOtherSales($attribute, $param) {
        if(!empty($this->other_sales_id)){
            if($this->other_sales_id==$this->sales_id){
                $this->addError($attribute,'负责销售不能与跨区业务员为同一个人');
            }
            if(empty($this->other_yewudalei)){
                $this->addError($attribute,'跨区业务大类不能为空');
            }
        }else{
            $this->other_yewudalei=null;
        }
    }

    public function validateFeeType($attribute, $param) {
        if($this->fee_type==1){
            if(empty($this->pay_month)){
                $this->addError($attribute,'预付月数不能为空');
            }
            if(empty($this->pay_start)){
                $this->addError($attribute,'起始月不能为空');
            }
        }else{
            $this->pay_month=null;
            $this->pay_start=null;
        }
    }

    public function validateRenewal($attribute, $param) {
        if($this->clue_type==1){
            if(empty($this->is_renewal)){
                $this->addError($attribute,'是否到期自动续约不能为空');
            }
        }
    }

    public function validateIsSeal($attribute, $param) {
        if($this->is_seal=="Y"){
            if(empty($this->seal_type_id)){
                $this->addError($attribute,'印章类型不能为空');
            }
        }else{
            $this->seal_type_id=null;
        }
    }

    public function validateServiceAmount($attribute, $param) {
	    $this->amt_install=null;
	    $this->remark=null;
        $total = 0;//门店总金额
        $arr=array();//门店的服务拆分成多个服务
        $services = $this->serviceDefinition();
        $totalKeyList = CGetName::getInfoTotalKeyList();
        foreach ($services as $key=>$value) {
            $this->month_amt=isset($this->service["svc_{$key}"])?$this->service["svc_{$key}"]:null;
            $arr[$key]=array(
                'name'=>$value['name'],
                'month_amt'=>$this->month_amt,
                'year_amt'=>null,
                'service_sum'=>0,
                'service_fre_amt'=>null,
                'service_fre_sum'=>0,
                'service_fre_type'=>0,
                'service_fre_json'=>null,
                'service_fre_text'=>null,
                'items'=>array()
            );
            $fldid = 'svc_'.$key;
            if(isset($this->service[$fldid])){
                $arr[$key]['items'][$fldid]=$this->service[$fldid];
            }
            if (in_array($key, $totalKeyList)) {
                if (isset($this->service[$fldid])) {
                    if (!empty($this->service[$fldid]) && is_numeric($this->service[$fldid])){
                        $total += $this->service[$fldid];
                    }
                }
            }
            foreach ($value['items'] as $k=>$v) {
                $fldid = 'svc_'.$k;
                if(isset($this->service[$fldid])){
                    $arr[$key]['items'][$fldid]=$this->service[$fldid];
                    if($v["type"]=="remark"){//备注
                        $this->remark = $this->service[$fldid];
                    }
                    if($v["type"]=="install_amt"){//安装费
                        $this->amt_install = !empty($this->service[$fldid])&&is_numeric($this->service[$fldid])?floatval($this->service[$fldid]):null;
                    }
                    if (in_array($k, $totalKeyList)) {
                        if (isset($this->service[$fldid])) {
                            if (!empty($this->service[$fldid]) && is_numeric($this->service[$fldid])){
                                $arr[$key]["year_amt"]=$this->service[$fldid];
                                $total += $this->service[$fldid];
                            }
                        }
                    }
                    if(in_array($v["type"],array("device","ware"))){//设备、洁具
                        $fldid = 'svc_'.$k.'_rmk';
                        $arr[$key]['items'][$fldid]=isset($this->service[$fldid])?$this->service[$fldid]:null;
                    }
                }
            }
            //验证服务频次
            $arr[$key]["amt_install"]= empty($this->amt_install)?null:round($this->amt_install,2);
            $arr[$key]["remark"]= $this->remark;
            $arr[$key]["service_fre_type"]= isset($this->service["svc_{$key}FreType"])?$this->service["svc_{$key}FreType"]:0;
            $arr[$key]["service_fre_sum"]= isset($this->service["svc_{$key}FreSum"])?$this->service["svc_{$key}FreSum"]:0;
            $arr[$key]["service_fre_amt"]= isset($this->service["svc_{$key}FreAmt"])?$this->service["svc_{$key}FreAmt"]:null;
            $arr[$key]["service_fre_json"]= isset($this->service["svc_{$key}FreJson"])?$this->service["svc_{$key}FreJson"]:null;
            $arr[$key]["service_fre_text"]= isset($this->service["svc_{$key}FreText"])?$this->service["svc_{$key}FreText"]:null;

            $arr[$key]['items']["svc_{$key}FreType"]=$arr[$key]["service_fre_type"];
            $arr[$key]['items']["svc_{$key}FreSum"]=$arr[$key]["service_fre_sum"];
            $arr[$key]['items']["svc_{$key}FreAmt"]=$arr[$key]["service_fre_amt"];
            $arr[$key]['items']["svc_{$key}FreJson"]=$arr[$key]["service_fre_json"];
            $arr[$key]['items']["svc_{$key}FreText"]=$arr[$key]["service_fre_text"];

            if($this->getScenario()=="audit"&&empty($arr[$key]["service_fre_json"])){
                $this->addError($attribute,'服务频次不能为空');
            }
        }
        $this->year_amt=$total;

        $this->serviceJson=$arr;
        return array("total"=>$total,"list"=>$arr,"amt_install"=>$this->amt_install,"remark"=>$this->remark);
    }

    public function validateServices($attribute, $params) {
        $this->service_fre_sum=0;
        $services = $this->serviceDefinition();
        foreach ($services as $key=>$value) {
            $fldid = 'svc_'.$key;
            if (isset($this->service[$fldid])) {
                switch ($value['type']) {
                    case 'pct':
                        if (!empty($this->service[$fldid])) {
                            if (!is_numeric($this->service[$fldid]) || !is_int($this->service[$fldid]+0) || $this->service[$fldid]+0 > 100 || $this->service[$fldid]+0 < 0)
                                $this->addError($attribute, $value['name'].'-'.Yii::t('sales','Percentage').' '.Yii::t('sales','Invalid value'));
                        }
                        break;
                    case 'qty':
                        if (!empty($this->service[$fldid])) {
                            if (!is_numeric($this->service[$fldid]) || !is_int($this->service[$fldid]+0))
                                $this->addError($attribute, $value['name'].'-'.Yii::t('sales','Qty').' '.Yii::t('sales','Invalid value'));
                        }
                        break;
                    case 'annual':
                        if (!empty($this->service[$fldid]) && !is_numeric($this->service[$fldid]))
                            $this->addError($attribute, $value['name'].'-'.Yii::t('sales','Annual Amount').' '.Yii::t('sales','Invalid value'));
                        break;
                    case 'amount':
                        if (!empty($this->service[$fldid]) && !is_numeric($this->service[$fldid]))
                            $this->addError($attribute, $value['name'].'-'.Yii::t('sales','Amount').' '.Yii::t('sales','Invalid value'));
                }
            }

            foreach ($value['items'] as $k=>$v) {
                $fldid = 'svc_'.$k;
                if (isset($this->service[$fldid])) {
                    switch ($v['type']) {
                        case 'pct':
                            if (!empty($this->service[$fldid])) {
                                if (!is_numeric($this->service[$fldid]) || !is_int($this->service[$fldid]+0) || $this->service[$fldid]+0 > 100 || $this->service[$fldid]+0 < 0)
                                    $this->addError($attribute, $value['name'].'-'.$v['name'].' '.Yii::t('sales','Invalid value'));
                            }
                            break;
                        case 'qty':
                            if (!empty($this->service[$fldid])) {
                                if (!is_numeric($this->service[$fldid]) || !is_int($this->service[$fldid]+0))
                                    $this->addError($attribute, $value['name'].'-'.$v['name'].' '.Yii::t('sales','Invalid value'));
                            }
                            break;
                        case 'annual':
                            if (!empty($this->service[$fldid]) && !is_numeric($this->service[$fldid]))
                                $this->addError($attribute, $value['name'].'-'.$v['name'].' '.Yii::t('sales','Invalid value'));
                            break;
                        case 'amount':
                            if (!empty($this->service[$fldid]) && !is_numeric($this->service[$fldid]))
                                $this->addError($attribute, $value['name'].'-'.$v['name'].' '.Yii::t('sales','Invalid value'));
                    }
                }
            }

            //验证服务频次
            $this->service_fre_sum+= isset($this->service["svc_{$key}FreSum"])?$this->service["svc_{$key}FreSum"]:0;
        }
    }

    public function validateID($attribute, $params){
	    if ($this->getScenario()!='new'){
            $row = Yii::app()->db->createCommand()->select("*")->from("sal_contract_virtual")
                ->where("id=:id",array(":id"=>$this->id))->queryRow();
            if($row){
                $this->id=$row["id"];
                $this->cont_id=$row["cont_id"];
                $this->clue_id=$row["clue_id"];
                $this->clue_type=$row["clue_type"];
                $this->clue_service_id=$row["clue_service_id"];
                $this->clue_store_id=$row["clue_store_id"];
                $this->vir_code=$row["vir_code"];
                $this->busine_id=array($row["busine_id"]);
                $this->busine_id_text=$row["busine_id_text"];
            }
        }
    }

    public function setContHeadRow(){
        $contModel = new  ContHeadForm('view');
        $contModel->retrieveData($this->cont_id);
        $this->contHeadRow = $contModel;
    }

    public function validateRowByID($attribute, $params){
	    $contModel = new  ContHeadForm('view');
	    if($contModel->retrieveData($this->cont_id)){
            $this->contHeadRow = $contModel->getAttributes();
            $storeModel = new ClueStoreForm("view");
            if($storeModel->retrieveData($this->clue_store_id)){
                $this->storeHeadRow = $storeModel->getAttributes();
                $this->city = $this->storeHeadRow["city"];
                /*
                $clueModel = new ClueForm("edit");
                $clueModel->retrieveData($this->clue_id);
                $this->yewudalei = $clueModel->yewudalei;
                */
            }else{
                $this->addError($attribute, "门店信息异常".$this->clue_store_id);
            }
        }else{
            $this->addError($attribute, "主合同信息异常".$this->cont_id);
        }
    }

    protected function setAttrByRow($row){
        $this->id = $row['id'];
        $this->city = $row['city'];
        $this->clue_type = $row['clue_type'];
        $this->vir_code = $row['vir_code'];
        $this->vir_status = $row['vir_status'];
        $this->cont_id = $row['cont_id'];
        $this->clue_id = $row['clue_id'];
        $this->clue_service_id = $row['clue_service_id'];
        $this->clue_store_id = $row['clue_store_id'];
        $this->create_staff = $row['create_staff'];
        $this->other_sales_id = $row['other_sales_id'];
        $this->other_yewudalei = $row['other_yewudalei'];
        $this->month_amt = $row['month_amt'];
        $this->year_amt = $row['year_amt'];
        $this->service_fre_json = $row['service_fre_json'];
        $this->service_fre_sum = $row['service_fre_sum'];
        $this->service_fre_type = $row['service_fre_type'];
        $this->service_fre_amt = $row['service_fre_amt'];
        $this->service_fre_text = $row['service_fre_text'];
        $this->remark = $row['remark'];
        $this->first_date = empty($row['first_date'])?"":General::toDate($row['first_date']);
        $this->fast_date = empty($row['fast_date'])?"":General::toDate($row['fast_date']);
        $this->sign_date = empty($row['sign_date'])?"":General::toDate($row['sign_date']);
        $this->cont_start_dt = empty($row['cont_start_dt'])?"":General::toDate($row['cont_start_dt']);
        $this->cont_end_dt = empty($row['cont_end_dt'])?"":General::toDate($row['cont_end_dt']);
        $this->lbs_main = $row['lbs_main'];
        $this->service_main = $row['service_main'];
        $this->prioritize_service = $row['prioritize_service'];
        $this->service_timer = $row['service_timer'];
        $this->pay_week = $row['pay_week'];
        $this->pay_type = $row['pay_type'];
        $this->sign_type = $row['sign_type'];
        $this->pay_month = $row['pay_month'];
        $this->pay_start = $row['pay_start'];
        $this->seal_type_id = $row['seal_type_id'];
        $this->prioritize_seal = $row['prioritize_seal'];
        $this->is_seal = $row['is_seal'];
        $this->is_renewal = $row['is_renewal'];
        $this->con_v_type = $row['con_v_type'];
        $this->deposit_need = $row['deposit_need'];
        $this->deposit_amt = $row['deposit_amt'];
        $this->deposit_rmk = $row['deposit_rmk'];
        $this->amt_install = $row['amt_install'];
        $this->need_install = $row['need_install'];
        $this->yewudalei = $row['yewudalei'];
        $this->fee_type = $row['fee_type'];
        $this->settle_type = $row['settle_type'];
        $this->bill_day = $row['bill_day'];
        $this->bill_bool = $row['bill_bool'];
        $this->profit_int = $row['profit_int'];
        $this->receivable_day = $row['receivable_day'];
        $this->sales_id = $row['sales_id'];
        $this->busine_id = array($row['busine_id']);
        $this->busine_id_text = $row['busine_id_text'];
        $this->service = empty($row['detail_json'])?array():json_decode($row['detail_json'],true);
        $this->u_service_json = empty($row['u_service_json'])?array():json_decode($row['u_service_json'],true);
    }

	public function retrieveData($index){
        $index = empty($index)||!is_numeric($index)?0:intval($index);
		$sql = "select a.* from sal_contract_virtual a where a.id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
		    $this->setAttrByRow($row);
            return true;
		}else{
		    return false;
        }
	}

	public function serviceDefinition(){
        $list = array();
        if(!empty($this->busine_id)){
            $defList = CGetName::serviceDefinition();
            foreach ($this->busine_id as $item){
                if(isset($defList[$item])){
                    $list[$item]=$defList[$item];
                }
            }
        }
	    return $list;
    }


    public function inAmtFiles($gid){
        $row = Yii::app()->db->createCommand()->select("id")
            ->from("sal_service_type_info")->where("total_bool=1 and id_char=:id_char",array(":id_char"=>$gid))
            ->queryRow();
        if ($row) {
            return 1;
        }else{
            return 0;
        }
    }

    //哪些字段修改后需要记录
    public function historyUpdateList(){
        $list = array(
            'vir_code','vir_status','cont_id','clue_id','clue_service_id','clue_store_id','create_staff','month_amt',
            'year_amt','service_fre_type','service_fre_text','service_fre_amt','service_fre_sum',
            'remark','first_date','fast_date','sign_date','cont_start_dt','cont_end_dt','lbs_main','service_main',
            'prioritize_service','is_seal','is_renewal','seal_type_id','prioritize_seal','con_v_type','pay_week','pay_type','sign_type','fee_type','pay_month','pay_start','deposit_need',
            'deposit_amt','deposit_rmk','sales_id','yewudalei','other_sales_id','other_yewudalei','settle_type','bill_day','profit_int','bill_bool',
            'busine_id_text'
        );
        return $list;
    }

    //哪些字段修改后需要记录
    public function getNameForValue($type,$value,$modelObj){
        switch ($type){
            case "seal_type_id":
                $value = CGetName::getSealTypeStrByID($value);
                break;
            case "con_v_type":
                $value = CGetName::getContTypeStrByKey($value);
                break;
            case "vir_status":
                $value = CGetName::getContVirStatusStrByKey($value);
                break;
            case "sales_id":
            case "create_staff":
            case "other_sales_id":
                $value = CGetName::getEmployeeNameByKey($value);
                break;
            case "service_fre_type":
                $value = CGetName::getServiceFreeStrByKey($value);
                break;
            case "service_main":
            case "lbs_main":
                $value = CGetName::getLbsMainNameByKey($value);
                break;
            case "profit_int":
                $value = CGetName::getProfitStrByKey($value);
                break;
            case "is_seal":
            case "is_renewal":
            case "prioritize_seal":
            case "bill_bool":
            case "deposit_need":
            case "prioritize_service":
                $value = CGetName::getCustVipStrByKey($value);
                break;
            case "pay_week":
                $value = CGetName::getPayWeekStrByKey($value);
                break;
            case "pay_type":
                $value = CGetName::getPayTypeStrByID($value);
                break;
            case "sign_type":
                $value = CGetName::getSignTypeStrByKey($value);
                break;
            case "yewudalei":
            case "other_yewudalei":
                $value = CGetName::getYewudaleiStrByKey($value);
                break;
            case "fee_type":
                $value = CGetName::getFeeTypeStrByKey($value);
                break;
            case "settle_type":
                $value = CGetName::getSettleTypeStrByKey($value);
                break;
        }
        return $value;
    }
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->save($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function save(&$connection)
	{
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city;
	    switch ($this->getScenario()){
            case "new":
                break;
            case "edit":
                $connection->createCommand()->update("sal_clue_sre_soe",array(
                    "store_amt"=>$this->store_amt,
                    //"service_sum"=>$this->service_sum,
                    "service_fre"=>$this->service_fre,
                    "remark"=>$this->remark,
                    "detail_json"=>json_encode($this->service),
                    "luu"=>$uid,
                ),"id=:id",array(":id"=>$this->id));
                break;
            case "delete":
                $connection->createCommand()->delete("sal_clue_sre_soe","id=:id",array(":id"=>$this->id));

        }
		return true;
	}

	public function isOccupied($index) {
		$rtn = true;//默认不允许删除
		if($this->retrieveData($index)){
            $rtn = false;//允许删除
        }
		return $rtn;
	}

	public function isReadonly() {
		return true;
	}
}
