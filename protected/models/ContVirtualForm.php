<?php

class ContVirtualForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $cont_id;
	public $clue_id;
	public $clue_type;
	public $city="ZH";//限货币使用，其它地方不使用
	public $clue_service_id;
	public $clue_store_id;
    public $busine_id;
    public $busine_id_text;
	public $create_staff;
	public $store_amt;
	public $service_sum;
	public $service_fre;
	public $service_fre_text;
	public $remark;
    public $service = array();
	public $check;
    public $u_id;
    public $update_bool=1;//允许修改
    public $ltNowDate=false;

	public $contHeadRow;
	public $clueHeadRow;
	public $clueServiceRow;

    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		$list = array(
            'visit_date'=>Yii::t('clue','flow date'),
		    'rpt_bool'=>Yii::t('clue','rpt bool'),
		    'visit_text'=>Yii::t('clue','flow text'),
		    'visit_obj'=>Yii::t('clue','visit obj'),
		    'sign_odds'=>Yii::t('clue','sign odds'),
		    'clue_service_id'=>Yii::t('clue','clue service'),
		);
		return $list;
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
	    $list = array();
        $list[]=array('id,clue_id,create_staff,store_amt,service_sum,service_fre,remark,check,update_bool','safe');
        $list[]=array('clue_service_id','required');
        $list[]=array('service','required','on'=>array("edit"));
        $list[]=array('clue_service_id','validateClueServiceID');
        $list[]=array('id','validateID');
        $list[]=array('service','validateServiceAmount','on'=>array("edit"));
        $list[]=array('service','validateServices','on'=>array("edit"));
		return $list;
	}

    public function validateServiceAmount($attribute, $param) {
        $total = 0;
        $services = $this->serviceDefinition();
        foreach ($services as $key=>$value) {
            if (in_array($key, VisitForm::$amount_fields)) {
                $fldid = 'svc_'.$key;
                if (isset($this->service[$fldid])) {
                    if (!empty($this->service[$fldid]) && is_numeric($this->service[$fldid])) $total += $this->service[$fldid];
                }
            }

            foreach ($value['items'] as $k=>$v) {
                if (in_array($k, VisitForm::$amount_fields)) {
                    $fldid = 'svc_'.$k;
                    if (isset($this->service[$fldid])) {
                        if (!empty($this->service[$fldid]) && is_numeric($this->service[$fldid])) $total += $this->service[$fldid];
                    }
                }
            }
        }
        $this->store_amt = $total;
    }

    public function validateServices($attribute, $params) {
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
        }
    }

	public function retrieveDataByScenario($index,$busine_id=0){
	    if($this->getScenario()=="new"){
	        $this->retrieveDataNew($index,$busine_id);
        }else{
            $this->retrieveData($index);
        }
    }

	public function retrieveDataNew($index,$busine_id){
        $this->id=null;
    }

	public function retrieveData($index){
		$sql = "select a.* from sal_contract_virtual a where a.id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
            $this->cont_id = $row['cont_id'];
            $this->clue_id = $row['clue_id'];
            $this->clue_service_id = $row['clue_service_id'];
            $this->clue_store_id = $row['clue_store_id'];
            $this->create_staff = $row['create_staff'];
            $this->store_amt = $row['store_amt'];
            $this->service_sum = $row['service_sum'];
            $this->service_fre = $row['service_fre'];
            $this->remark = $row['remark'];
            $this->busine_id = $row['busine_id'];
            $this->busine_id_text = $row['busine_id_text'];
            $this->service = empty($row['detail_json'])?array():json_decode($row['detail_json'],true);
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
        $key = "".$gid;
        if (in_array($key, VisitForm::$amount_fields)) {
            return 1;
        }else{
            return 0;
        }
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
                if(!empty($this->check)&&is_array($this->check)){
                    foreach ($this->check as $store_id){
                        $this->clue_store_id = $store_id;
                        $connection->createCommand()->insert("sal_clue_sre_soe",array(
                            "clue_id"=>$this->clue_id,
                            "clue_service_id"=>$this->clue_service_id,
                            "clue_store_id"=>$store_id,
                            "create_staff"=>$this->create_staff,
                            "busine_id"=>is_array($this->busine_id)?implode(",",$this->busine_id):$this->busine_id,
                            "busine_id_text"=>$this->busine_id_text,
                            "lcu"=>$uid,
                        ));
                        //刷新门店最新的状态
                        $this->resetClueStore();
                    }
                }
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
                //刷新门店最新的状态
                $this->resetClueStore();
        }
        //刷新商机最新的金额信息
        $this->resetClueService();
		return true;
	}

    //刷新门店最新的状态
    protected function resetClueStore(){
        $endRow = Yii::app()->db->createCommand()
            ->select("max(b.service_status) as endStatus")
            ->from("sal_clue_sre_soe a")
            ->leftJoin("sal_clue_service b","a.clue_service_id=b.id")
            ->where("a.clue_store_id=:id",array(":id"=>$this->clue_store_id))
            ->queryRow();//
        if($endRow&&$endRow["endStatus"]!==""){
            switch ($endRow["endStatus"]){
                case 100://已生成合同
                    $store_status = 2;
                    break;
                default://未生成合同
                    $store_status = 1;
                    break;
            }
            Yii::app()->db->createCommand()->update("sal_clue_store",array(
                "store_status"=>$store_status,
            ),"id=:id",array(":id"=>$this->clue_store_id));
        }else{
            Yii::app()->db->createCommand()->update("sal_clue_store",array(
                "store_status"=>0,
            ),"id=:id",array(":id"=>$this->clue_store_id));
        }
    }

    //刷新商机最新的金额信息
    protected function resetClueService(){
        $totalRow = Yii::app()->db->createCommand()
            ->select("sum(store_amt) as amt_sum,count(id) as count_num")
            ->from("sal_clue_sre_soe")
            ->where("clue_service_id=:id",array(":id"=>$this->clue_service_id))
            ->queryRow();//
        if($totalRow){
            Yii::app()->db->createCommand()->update("sal_clue_service",array(
                "total_amt"=>$totalRow["amt_sum"],
                "total_num"=>$totalRow["count_num"],
            ),"id=:id",array(":id"=>$this->clue_service_id));
        }else{
            Yii::app()->db->createCommand()->update("sal_clue_service",array(
                "total_amt"=>null,
                "total_num"=>0,
            ),"id=:id",array(":id"=>$this->clue_service_id));
        }
    }

	public function isOccupied($index) {
		$rtn = true;//默认不允许删除
		if($this->retrieveData($index)){
            $rtn = false;//允许删除
        }
		return $rtn;
	}

	public function isReadonly() {
		return $this->update_bool!==1;
	}
}
