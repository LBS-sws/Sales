<?php

class ContForm extends CFormModel
{
	/* User Fields */
	public $id;
    public $clue_id;
    public $clue_type;
    public $clue_service_id;
    public $city;
    public $lbs_main;
    public $sales_id;
    public $other_sales_id;
    public $yewudalei;
    public $other_yewudalei;
	public $cont_code;
    public $entry_date;
    public $predict_amt;
    public $store_sum;
    public $total_sum;
    public $total_amt;
    public $effect_date;
    public $stop_date;
    public $surplus_num;
    public $surplus_amt;
    public $busine_id;
    public $busine_id_text;
    public $cont_type;
    public $con_v_type;
    public $cont_start_dt;
    public $cont_end_dt;
    public $cont_month_len;
    public $sign_type;
    public $sign_date;
    public $is_seal="Y";
    public $is_renewal;
    public $seal_type_id;
    public $prioritize_service;
    public $prioritize_seal;
    public $group_bool='N';
    public $service_timer;
    public $pay_week;
    public $pay_type;
    public $pay_month;
    public $pay_start;
    public $deposit_need=0.00;
    public $deposit_amt=0.00;
    public $deposit_rmk=0;
    public $fee_type=1;
    public $profit_int;
    public $settle_type;
    public $bill_day;
    public $bill_bool;
    public $receivable_day;
    public $area_bool;
    public $busineList=array();

    public $remark;
    public $mh_remark;
    public $mh_id;
    public $cont_status=0;
    public $lcu;
    public $luu;
    public $lcd;
    public $lud;
    public $areaJson=array();
    public $fileJson=array();
    public $serviceJson;

    public $login_employee_id;

    public $clueHeadRow;
    public $clueServiceRow;
    public $clueSSERow;

    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		$list = array(
            'id'=>Yii::t('clue','contract id'),//合同id
            'cont_code'=>Yii::t('clue','contract code'),//合同编号
            'lbs_main'=>Yii::t('clue','lbs main'),//客户主体
            'yewudalei'=>Yii::t('clue','yewudalei'),//业务大类
            'sales_id'=>Yii::t('clue','charge sales'),//负责销售
            'other_sales_id'=>Yii::t('clue','other sales'),//跨区业务员
            'other_yewudalei'=>Yii::t('clue','other yewudalei'),//跨区业务大类
            'predict_amt'=>Yii::t('clue','dict amt'),//预估金额
            'store_sum'=>Yii::t('clue','store num'),//门店数量
            'total_sum'=>Yii::t('clue','total sum'),//
            'total_amt'=>Yii::t('clue','total amt'),//总金额
            'busine_id'=>Yii::t('clue','service obj'),//服务项目
            'con_v_type'=>Yii::t('clue','cont type'),//合同类型
            'cont_type'=>Yii::t('clue','contract type'),//合约类型
            'cont_start_dt'=>Yii::t('clue','contract start date'),//合约开始时间
            'cont_end_dt'=>Yii::t('clue','contract end date'),//合约结束时间
            'cont_month_len'=>Yii::t('clue','contract month'),//合同月份
            'sign_type'=>Yii::t('clue','sign type'),//签约类型
            'sign_date'=>Yii::t('clue','sign date'),//签约时间
            'is_seal'=>Yii::t('clue','is seal'),//
            'is_renewal'=>Yii::t('clue','is renewal'),//
            'seal_type_id'=>Yii::t('clue','seal type'),//印章类型
            'prioritize_service'=>Yii::t('clue','prioritize service'),//是否优先安排服务
            'prioritize_seal'=>Yii::t('clue','prioritize seal'),//是否客户先用印
            'group_bool'=>Yii::t('clue','group bool'),//是否集团客户
            'service_timer'=>Yii::t('clue','service timer'),//服务时长
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
            'receivable_day'=>Yii::t('clue','receivable day'),//应收期限
            'area_bool'=>Yii::t('clue','area bool'),//是否面积计算价格
            'total_sum'=>Yii::t('clue','total sum'),//总金额
            'effect_date'=>Yii::t('clue','effect date'),//生效日期
            'stop_date'=>Yii::t('clue','stop date'),//终止日期
            'surplus_num'=>Yii::t('clue','surplus num'),//剩余次数
            'surplus_amt'=>Yii::t('clue','surplus amt'),//剩余金额
            'pro_code'=>Yii::t('clue','pro code'),//操作编号
            'pro_type'=>Yii::t('clue','pro type'),//操作类型
            'pro_num'=>Yii::t('clue','pro num'),//同类型操作次数
            'pro_date'=>Yii::t('clue','pro date'),//操作生效时间
            'pro_remark'=>Yii::t('clue','pro remark'),//操作备注
            'pro_status'=>Yii::t('clue','status'),//操作进行中的状态
            'profit_int'=>Yii::t('clue','profit'),//
		);
		return $list;
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
        //draft：草稿
	    $list = array();
        $list[]=array('id,profit_int,settle_type,settle_type,group_bool,service_timer,deposit_need,deposit_amt,deposit_rmk,
        pay_month,pay_start,lbs_main,sales_id,yewudalei,busine_id,cont_type,sign_date,cont_start_dt,cont_end_dt,sign_type,
        seal_type_id,prioritize_seal,prioritize_service,predict_amt,pay_week,pay_type,fee_type,
        is_seal,is_renewal,con_v_type,settle_type,bill_day,receivable_day,bill_bool,area_bool,areaJson,serviceJson,
        total_sum,effect_date,stop_date,surplus_num,surplus_amt,other_sales_id,other_yewudalei','safe');
        $list[]=array('lbs_main','required','on'=>array('draft'));
        $list[]=array('lbs_main,sales_id,yewudalei,cont_type,con_v_type,sign_date,cont_start_dt,cont_end_dt,sign_type,
        prioritize_seal,prioritize_service,predict_amt,pay_week,pay_type,fee_type,
        is_seal,settle_type,bill_day,receivable_day,bill_bool,area_bool','required','on'=>array('audit'));
        $list[]=array('lbs_main','numerical','integerOnly'=>true,'min'=>1,'on'=>array('draft','audit'));
        $list[]=array('is_seal','validateIsSeal','on'=>array('audit'));
        $list[]=array('is_renewal','validateRenewal','on'=>array('audit'));
        $list[]=array('fee_type','validateFeeType','on'=>array('audit'));
        $list[]=array('other_yewudalei','validateOtherSales','on'=>array('audit'));
        $listEx = $this->rulesEx();
	    if(!empty($listEx)){
            $list = array_merge($list,$listEx);
        }
		return $list;
	}

    public function validateOtherSales($attribute, $param) {
	    if(!empty($this->other_sales_id)){
	        if($this->other_sales_id==$this->sales_id){
                //$this->addError($attribute,'负责销售不能与跨区业务员为同一个人');
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

    public function computeAreaBool($attribute, $param) {
        if($this->area_bool!="Y"){
            $this->areaJson=array();
        }
    }

    public function computeGroupBool($attribute, $param) {
        if($this->clue_type==2){
            $this->group_bool = "Y";
        }
    }

    public function validateClueServiceRow($attribute, $param) {
	    if($this->hasErrors()===false){
            if(empty($this->clueSSERow)){
                if(get_class($this)=="ContHeadForm"){
                    $this->addError($attribute, "商机请先关联门店，再发起合同审批");
                }else{
                    $this->addError($attribute, "门店及服务项目不能为空");
                }
                return false;
            }
            if(empty($this->predict_amt)){
                $this->addError($attribute, "预估金额不能为空");
                return false;
            }
            if($this->clueServiceRow["total_num"]<1){
                $this->addError($attribute, "商机未关联门店无法报价");
                return false;
            }
            if($this->clueServiceRow["sign_odds"]!=100){
                $this->addError($attribute, "商机的签约概率必须为100%");
                return false;
            }
        }
    }

    public function validateClueServiceID($attribute, $param) {
        $clueServiceModel = new ClueServiceForm("view");
        if($clueServiceModel->retrieveData($this->clue_service_id)){
            $this->clue_id = $clueServiceModel->clue_id;
            //$this->sales_id=$clueServiceModel->create_staff;
            $this->busine_id = $clueServiceModel->busine_id;
            $this->busine_id_text = $clueServiceModel->busine_id_text;
            $this->busineList = CGetName::getServiceDefListByIDList($this->busine_id);
            $this->predict_amt = $clueServiceModel->clue_type==2?floatval($clueServiceModel->rpt_amt):floatval($clueServiceModel->predict_amt);
            $this->total_amt = floatval($clueServiceModel->total_amt);
            $this->clueServiceRow = $clueServiceModel->getAttributes();
            $clueHeadModel = new ClueHeadForm("view");
            if($clueHeadModel->retrieveData($this->clue_id)){
                $this->clue_type=$clueHeadModel->clue_type;
                $this->city=$clueHeadModel->city;
                //$this->yewudalei=$clueHeadModel->yewudalei;
                $this->clueHeadRow = $clueHeadModel->getAttributes();
            }else{
                $this->addError($attribute, "线索不存在，请刷新重试");
            }
        }else{
            $this->addError($attribute, "商机不存在，请刷新重试");
        }
    }

    public function validateClueServiceIDByView($attribute, $param) {
        $clueServiceModel = new ClueServiceForm("view");
        if($clueServiceModel->retrieveData($this->clue_service_id)){
            $this->clue_id = $clueServiceModel->clue_id;
            //$this->sales_id=$clueServiceModel->create_staff;
            $this->busine_id = $clueServiceModel->busine_id;
            $this->busine_id_text = $clueServiceModel->busine_id_text;
            $this->busineList = CGetName::getServiceDefListByIDList($this->busine_id);
            $this->predict_amt = $clueServiceModel->clue_type==2?floatval($clueServiceModel->rpt_amt):floatval($clueServiceModel->predict_amt);
            $this->total_amt = floatval($clueServiceModel->total_amt);
            $this->clueServiceRow = $clueServiceModel->getAttributes();
            $clueModel = new ClueForm("view");
            if($clueModel->retrieveData($this->clue_id)){
                $this->clue_type=$clueModel->clue_type;
                $this->city=$clueModel->city;
                //$this->yewudalei=$clueModel->yewudalei;
                $this->clueHeadRow = $clueModel->getAttributes();
            }else{
                $this->addError($attribute, "线索不存在，请刷新重试");
            }
        }else{
            $this->addError($attribute, "商机不存在，请刷新重试");
        }
    }

    public function validateID($attribute, $param) {
	    $this->login_employee_id=CGetName::getEmployeeIDByMy();
        $row = Yii::app()->db->createCommand()->select("a.*")->from("sal_contract a")
            ->where("a.clue_service_id=:id",array(":id"=>$this->clue_service_id))->queryRow();
        if($row){
            $this->id = $row["id"];
            $this->total_amt = $row["total_amt"];
            $this->mh_id = $row["mh_id"];
            $this->cont_status = $row["cont_status"];
            $this->clueSSERow = CGetName::getContSSeRowByContID($this->id);
        }else{
            $this->id = null;
            $this->mh_id = null;
            $this->cont_status = 0;
            // 不分页，获取所有关联门店
            $this->clueSSERow = CGetName::getClueSSeRowByClueServiceID($this->clue_service_id,1,0,0);
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

    public function rulesEx(){
        return array();
    }

    public function retrieveDataByNew(){
        $row = Yii::app()->db->createCommand()->select("a.id")->from("sal_contract a")
            ->where("a.clue_service_id=:id",array(":id"=>$this->clue_service_id))->queryRow();
        if($row){
            $this->retrieveData($row["id"]);
            $this->clueSSERow = CGetName::getContSSeRowByContID($this->id);
        }else{
            // 不分页，获取所有关联门店
            $this->clueSSERow = CGetName::getClueSSeRowByClueServiceID($this->clue_service_id,1,0,0);
            $this->sign_type=1;
            $this->cont_status=0;
            $this->sales_id = $this->clueServiceRow["create_staff"];
            $this->is_seal=$this->clueHeadRow["clue_type"]==1?"N":"Y";
            $this->lbs_main = $this->clueServiceRow["lbs_main"];
            $this->yewudalei = $this->clueHeadRow["yewudalei"];
            $this->group_bool = $this->clueHeadRow["group_bool"];
            $this->cont_type = $this->group_bool=="Y"?2:1;
            if($this->clueHeadRow["clue_type"]==1){
                $this->con_v_type = 1;
                $this->bill_bool="N";
                $this->area_bool="N";
                $this->prioritize_seal="N";
                $this->prioritize_service="Y";
                $this->pay_type=1;
                $this->settle_type=2;
                $oneSSERow = current($this->clueSSERow);
                if(isset($oneSSERow["create_staff"])&&$oneSSERow["create_staff"]!=$this->sales_id){
                    $this->other_sales_id = $oneSSERow["create_staff"];
                    $this->other_yewudalei = CGetName::getOneYewudaleiByEmployee($this->other_sales_id);
                }
            }
            //$this->other_yewudalei = $this->clueHeadRow["yewudalei"];
        }
    }

    protected function retrieveSqlEx(){
        return "";
    }

    public function validateSeal(){
        $list =array('status'=>200,'message'=>"");
        if($this->cont_status==19){
            $row = Yii::app()->db->createCommand()->select("id")->from("sal_contract_file")
                ->where("cont_id=:id and group_id=1",array(":id"=>$this->id))->queryRow();
            if(!$row){
                $list["status"]=500;
                $list["message"]="请前往销售系统上传盖章文件";
            }
        }
        return $list;
    }

    public function retrieveData($index)
    {
        $index = empty($index)||!is_numeric($index)?0:$index;
        $sql = "select a.* from sal_contract a where a.id=".$index." ".$this->retrieveSqlEx();
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $this->id = $row['id'];
            $this->clue_id = $row['clue_id'];
            $this->clue_type = $row['clue_type'];
            $this->clue_service_id = $row['clue_service_id'];
            $this->city = $row['city'];
            $this->cont_code = $row['cont_code'];
            $this->sales_id = $row['sales_id'];
            $this->other_sales_id = $row['other_sales_id'];
            $this->other_yewudalei = $row['other_yewudalei'];
            $this->lbs_main = $row['lbs_main'];
            $this->predict_amt = $row['predict_amt'];
            $this->store_sum = $row['store_sum'];
            $this->total_sum = $row['total_sum'];
            $this->total_amt = $row['total_amt'];
            $this->surplus_num = $row['surplus_num'];
            $this->surplus_amt = $row['surplus_amt'];
            $this->cont_status = $row['cont_status'];
            $this->con_v_type = $row['con_v_type'];
            $this->cont_type = $row['cont_type'];
            $this->cont_start_dt = empty($row['cont_start_dt'])?"":General::toDate($row['cont_start_dt']);
            $this->cont_end_dt = empty($row['cont_end_dt'])?"":General::toDate($row['cont_end_dt']);
            $this->sign_date = empty($row['sign_date'])?"":General::toDate($row['sign_date']);
            $this->effect_date = empty($row['effect_date'])?"":General::toDate($row['effect_date']);
            $this->stop_date = empty($row['stop_date'])?"":General::toDate($row['stop_date']);
            $this->cont_month_len = $row['cont_month_len'];
            $this->sign_type = $row['sign_type'];
            $this->is_seal = $row['is_seal'];
            $this->is_renewal = $row['is_renewal'];
            $this->seal_type_id = $row['seal_type_id'];
            $this->prioritize_service = $row['prioritize_service'];
            $this->prioritize_seal = $row['prioritize_seal'];
            $this->group_bool = $row['group_bool'];
            $this->service_timer = $row['service_timer'];
            $this->pay_week = $row['pay_week'];
            $this->pay_type = $row['pay_type'];
            $this->pay_month = $row['pay_month'];
            $this->pay_start = $row['pay_start'];
            $this->deposit_need = $row['deposit_need'];
            $this->deposit_amt = $row['deposit_amt'];
            $this->deposit_rmk = $row['deposit_rmk'];
            $this->fee_type = $row['fee_type'];
            $this->profit_int = $row['profit_int'];
            $this->settle_type = $row['settle_type'];
            $this->bill_day = $row['bill_day'];
            $this->bill_bool = $row['bill_bool'];
            $this->receivable_day = $row['receivable_day'];
            $this->area_bool = $row['area_bool'];
            $this->areaJson = empty($row['area_json'])?array():json_decode($row['area_json'],true);
            $this->remark = $row['remark'];
            $this->mh_remark = $row['mh_remark'];
            $this->yewudalei = $row['yewudalei'];
            $this->mh_id = $row['mh_id'];
            $this->busine_id = empty($row['busine_id'])?array():explode(",",$row['busine_id']);
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


    public function saveData()
    {
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try {
            $this->setScenarioByID();
            $this->historySave($connection);
            $this->save($connection);
            $transaction->commit();
            $this->sendDataToMH();//发送消息至门户网站
            return true;
            /*
            $mhList = $this->sendDataToMH();//发送消息至门户网站
            if($mhList["bool"]){
                $transaction->commit();
                return true;
            }else{
                $this->addError("id",$mhList["msg"]);
                $transaction->rollback();
                return false;
            }
            */
        }catch(Exception $e) {
            $transaction->rollback();
            $errorMsg = isset($e->statusCode)?$e->statusCode:"Cannot update";
            $errorMsg.= "：";
            $errorMsg.= $e->getMessage();
            throw new CHttpException(404,$errorMsg);
        }
    }

    //哪些字段修改后需要记录
    protected static function historyUpdateList(){
        $list = array('lbs_main','yewudalei','other_sales_id','other_yewudalei','predict_amt','total_amt','con_v_type','cont_type','cont_start_dt','cont_end_dt',
            'sign_type','sign_date','is_seal','is_renewal','seal_type_id','prioritize_service','prioritize_seal','group_bool',
            'service_timer','pay_week','pay_month','pay_start','deposit_need','deposit_amt','fee_type',
            'settle_type','settle_type','bill_day','bill_bool','profit_int','receivable_day','area_bool');
        return $list;
    }

    //哪些字段修改后需要记录
    protected static function getNameForValue($type,$value,$modelObj){
        switch ($type){
            case "yewudalei":
            case "other_yewudalei":
                $value = CGetName::getYewudaleiStrByKey($value);
                break;
            case "con_v_type":
                $value = CGetName::getContTypeStrByKey($value);
                break;
            case "sales_id":
            case "other_sales_id":
                $value = CGetName::getEmployeeNameByKey($value);
                break;
            case "cont_type":
                $value = CGetName::getContactTypeStrByKey($value);
                break;
            case "sign_type":
                $value = CGetName::getSignTypeStrByKey($value);
                break;
            case "seal_type_id":
                $value = CGetName::getSealTypeStrByID($value);
                break;
            case "pay_week":
                $value = CGetName::getPayWeekStrByKey($value);
                break;
            case "fee_type":
                $value = CGetName::getFeeTypeStrByKey($value);
                break;
            case "settle_type":
                $value = CGetName::getSettleTypeStrByKey($value);
                break;
            case "profit_int":
                $value = CGetName::getProfitStrByKey($value);
                break;
            case "area_bool":
            case "bill_bool":
            case "group_bool":
            case "prioritize_seal":
            case "prioritize_service":
            case "is_seal":
            case "is_renewal":
                $value = CGetName::getCustVipStrByKey($value);
                break;
        }
        return $value;
    }

    protected function whenEqual($key,$oldArr,$nowArr){
        $valueOne = $oldArr->$key;
        $valueTwo = $nowArr->$key;
        $dateList = array('cont_start_dt','cont_end_dt','sign_date');
        $numberList = array('lbs_main','other_yewudalei','other_sales_id','predict_amt','total_amt','con_v_type','cont_type','cont_month_len',
            'sign_type','seal_type_id','service_timer','pay_week','pay_month','pay_start','deposit_need','deposit_amt','fee_type',
            'settle_type','settle_type','bill_day','receivable_day','profit_int');
        if(key_exists($key,$dateList)){
            $valueOne = General::toDate($valueOne);
            $valueTwo = General::toDate($valueTwo);
        }elseif(key_exists($key,$numberList)){
            $valueOne = CGetName::getNumberNull($valueOne);
            $valueTwo = CGetName::getNumberNull($valueTwo);
        }
        if($valueOne!=$valueTwo){
            return true;
        }
        return false;
    }

    //保存历史记录
    protected function historySave(&$connection){
        $uid = Yii::app()->user->id;
        $list=array("table_type"=>5,"table_id"=>$this->id,"lcu"=>$uid,"history_type"=>2,"history_html"=>array());
        switch ($this->getScenario()){
            case "edit":
                $model = new ContHeadForm();
                $model->retrieveData($this->id);
                $keyArr = self::historyUpdateList();
                foreach ($keyArr as $key){
                    if($this->whenEqual($key,$model,$this)){
                        $list["history_html"][]="<span>".$this->getAttributeLabel($key)."：".self::getNameForValue($key,$model->$key,$model)." 修改为 ".self::getNameForValue($key,$this->$key,$this)."</span>";
                    }
                }
                if(!empty($list["history_html"])){
                    $list["history_html"] = implode("<br/>",$list["history_html"]);
                    $connection->createCommand()->insert("sal_contract_history", $list);
                }
                break;
        }
    }

	protected function save(&$connection)
	{
		return true;
	}

    //发送消息至门户网站
    protected function sendDataToMH(){
        $list = array("bool"=>true,"msg"=>"");//true:成功
        return $list;
    }

	public function computeContCode() {
        $serviceType = is_array($this->clueHeadRow['service_type'])?$this->clueHeadRow['service_type']:array($this->clueHeadRow['service_type']);
        $serviceTypeStr=CGetName::getServiceTypeRptStrByList($serviceType);
        $codePre = $serviceTypeStr.date("Ymd");
        $row = Yii::app()->db->createCommand()->select("count(*) as sum")
            ->from("sal_contract")->where("cont_code like '{$codePre}%'")->queryRow();
        $num = $row?$row["sum"]:0;
        $num++;
        if($num<10000){
            $num = $num<10000?$num+10000:$num;
            $num = "".$num;
            $num = mb_substr($num,1);
        }
        $this->cont_code = $codePre.$num;
        return $this->cont_code;
	}

	public function computeVirCode($cont_id=0,$proNum=1) {
        $cont_id=empty($cont_id)?$this->id:$cont_id;
        $row = Yii::app()->db->createCommand()->select("count(*) as sum")
            ->from("sal_contract_virtual")->where("cont_id=:id",array(":id"=>$cont_id))->queryRow();
        $num = $row?$row["sum"]:0;
        $num+=$proNum;
        if($num<10000){
            $num = $num<10000?$num+10000:$num;
            $num = "".$num;
            $num = mb_substr($num,1);
        }
        $vir_code=$this->cont_code."-".$num;
        return $vir_code;
	}

	public function isOccupied($index) {
		$rtn = true;//默认不允许删除
		if($this->retrieveData($index)){
            $sql = "select a.id from sal_clue_service a where a.clue_id=".$index." ";
            $row = Yii::app()->db->createCommand($sql)->queryRow();
            $rtn = ($row !== false);
        }
		return $rtn;
	}

	public function isReadonly() {
		return $this->getScenario()=='view';
	}
}
