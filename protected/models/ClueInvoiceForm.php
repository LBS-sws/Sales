<?php

class ClueInvoiceForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $clue_id;
	public $clue_type;
    public $invoice_type=1;
	public $invoice_name;
	public $city;
	public $invoice_header;
	public $tax_id;
	public $invoice_address;
	public $invoice_number;
	public $invoice_user;
	public $invoice_rmk;
	public $invoice_phone;
	public $show_pay;
	public $show_cpy;
	public $show_opy;
	public $z_display=1;

	public $clueHeadRow;

    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		$list = array(
            'invoice_name'=>Yii::t('clue','invoice name'),//门店名称
            'city'=>Yii::t('clue','city'),//城市
            'invoice_header'=>Yii::t('clue','invoice header'),//开票抬头
            'tax_id'=>Yii::t('clue','tax id'),//税号
            'invoice_address'=>Yii::t('clue','invoice address'),//开票地址
            'invoice_type'=>Yii::t('clue','invoice type'),//
            'invoice_number'=>Yii::t('clue','invoice number'),//
            'invoice_user'=>Yii::t('clue','invoice user'),//
            'invoice_rmk'=>Yii::t('clue','invoice remark'),//
            'invoice_phone'=>Yii::t('clue','invoice phone'),//
            'show_pay'=>Yii::t('clue','invoice pay'),//
            'show_cpy'=>Yii::t('clue','invoice cpy'),//
            'show_opy'=>Yii::t('clue','invoice opy'),//
            'z_display'=>Yii::t('clue','z display'),//开票地址
		);
		return $list;
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
	    $list = array();
        $list[]=array('id,city,tax_id,invoice_address,invoice_type,invoice_number,invoice_user,invoice_rmk','safe');
        $list[]=array('invoice_phone,show_pay,show_cpy,show_opy','safe');
        $list[]=array('clue_id','required');
        $list[]=array('invoice_name,invoice_type,invoice_header,z_display','required','on'=>array("new","edit"));
        $list[]=array('clue_id','validateClueID');
        $list[]=array('id','validateID');
        $list[]=array('invoice_type','validateType');
		return $list;
	}

    public function validateType($attribute, $param) {
	    switch ($this->invoice_type){
            case 1:
                if(empty($this->tax_id)){
                    $this->addError($attribute, "税号不能为空");
                }
                break;
            case 2:
                if(empty($this->tax_id)){
                    $this->addError($attribute, "税号不能为空");
                }
                if(empty($this->invoice_address)){
                    $this->addError($attribute, "开票地址不能为空");
                }
                if(empty($this->invoice_number)){
                    $this->addError($attribute, "开户行不能为空");
                }
                if(empty($this->invoice_user)){
                    $this->addError($attribute, "账号不能为空");
                }
                break;
            case 3:
                break;
        }
    }

    public function validateID($attribute, $param) {
        $scenario = $this->getScenario();
        if($scenario=="new"){
            $storeRow = Yii::app()->db->createCommand()->select("id")
                ->from("sal_clue_invoice")
                ->where("invoice_name=:invoice_name and clue_id=:clue_id",array(
                    ":invoice_name"=>$this->invoice_name,
                    ":clue_id"=>$this->clue_id
                ))->queryRow();
            if($storeRow){
                $this->addError($attribute, "税号名称已存在，无法重复添加");
            }
        }else{
            $storeRow = Yii::app()->db->createCommand()->select("a.*")
                ->from("sal_clue_invoice a")
                ->where("a.id=:id",array(":id"=>$this->id))->queryRow();
            if($storeRow){
                if($scenario=="delete"){
                    $sreStoreRow = Yii::app()->db->createCommand()->select("*")
                        ->from("sal_clue_store")
                        ->where("invoice_id=:id",array(":id"=>$this->id))->queryRow();
                    if($sreStoreRow){
                        $this->addError($attribute, "该税号已关联门店，无法删除");
                    }
                }
            }else{
                $this->addError($attribute, "数据异常，请刷新重试");
            }
        }
    }

    public function validateClueID($attribute, $param) {
        $clueHeadModel = new ClueHeadForm("view");
        if($clueHeadModel->retrieveData($this->clue_id)){
            $this->city=$clueHeadModel->city;
            $this->clue_type=$clueHeadModel->clue_type;
            $this->clueHeadRow = $clueHeadModel->getAttributes();
        }else{
            $this->addError($attribute, "线索不存在，请刷新重试");
        }
    }

	public function retrieveData($index)
	{
        $index = empty($index)||!is_numeric($index)?0:intval($index);
		$sql = "select a.* from sal_clue_invoice a where a.id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
            $this->clue_id = $row['clue_id'];
            $this->clue_type = $row['clue_type'];
            $this->city = $row['city'];
            $this->invoice_name = $row['invoice_name'];
            $this->invoice_header = $row['invoice_header'];
            $this->tax_id = $row['tax_id'];
            $this->invoice_address = $row['invoice_address'];
            $this->invoice_type = $row['invoice_type'];
            $this->invoice_number = $row['invoice_number'];
            $this->invoice_user = $row['invoice_user'];
            $this->invoice_rmk = $row['invoice_rmk'];
            $this->invoice_phone = $row['invoice_phone'];
            $this->show_pay = $row['show_pay'];
            $this->show_cpy = $row['show_cpy'];
            $this->show_opy = $row['show_opy'];
            $this->z_display = $row['z_display'];

            return true;
		}else{
		    return false;
        }
	}

    //哪些字段修改后需要记录
    protected static function historyUpdateList($status){
        $list = array('invoice_name','invoice_type','invoice_header','tax_id','invoice_address','invoice_number','invoice_user','z_display',
            'invoice_phone','show_pay','show_cpy','show_opy',
        );
        return $list;
    }

    //哪些字段修改后需要记录
    protected static function getNameForValue($type,$value,$modelObj){
        switch ($type){
            case "invoice_type":
                $value = CGetName::getInvoiceTypeStrByKey($value);
                break;
            case "z_display":
                $value = CGetName::getDisplayStrByKey($value);
                break;
            case "show_pay":
            case "show_cpy":
            case "show_opy":
                $value = CGetName::getCustVipStrByKey($value);
                break;
        }
        return $value;
    }

    protected function whenEqual($key,$oldArr,$nowArr){
        $valueOne = $oldArr->$key;
        $valueTwo = $nowArr->$key;
        $numberList = array("z_display");
        if(key_exists($key,$numberList)){
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
        $list=array("table_type"=>4,"table_id"=>$this->id,"lcu"=>$uid,"history_type"=>2,"history_html"=>array());
        switch ($this->getScenario()){
            case "edit":
                $model = new ClueInvoiceForm();
                $model->retrieveData($this->id);
                $keyArr = self::historyUpdateList($model->clue_type);
                foreach ($keyArr as $key){
                    if($this->whenEqual($key,$model,$this)){
                        $list["history_html"][]="<span>".$this->getAttributeLabel($key)."：".self::getNameForValue($key,$model->$key,$model)." 修改为 ".self::getNameForValue($key,$this->$key,$this)."</span>";
                    }
                }
                if(!empty($list["history_html"])){
                    $list["history_html"] = implode("<br/>",$list["history_html"]);
                    $connection->createCommand()->insert("sal_clue_history", $list);
                }
                break;
        }
    }
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
            $this->historySave($connection);
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
                $connection->createCommand()->insert("sal_clue_invoice",array(
                    "clue_id"=>$this->clue_id,
                    "clue_type"=>$this->clue_type,
                    "city"=>$this->city,
                    "invoice_type"=>$this->invoice_type,
                    "invoice_name"=>$this->invoice_name,
                    "invoice_header"=>$this->invoice_header,
                    "tax_id"=>$this->tax_id,
                    "invoice_address"=>$this->invoice_address,
                    "invoice_number"=>$this->invoice_number,
                    "invoice_user"=>$this->invoice_user,
                    "invoice_rmk"=>$this->invoice_rmk,
                    "invoice_phone"=>$this->invoice_phone,
                    "show_pay"=>$this->show_pay,
                    "show_cpy"=>$this->show_cpy,
                    "show_opy"=>$this->show_opy,
                    "z_display"=>$this->z_display,
                    "lcu"=>$uid,
                ));
                $this->id = Yii::app()->db->getLastInsertID();
                $this->setScenario("edit");
                $connection->createCommand()->insert("sal_clue_history",array(
                    "table_id"=>$this->id,
                    "table_type"=>4,
                    "history_type"=>1,
                    "history_html"=>"<span>新增</span>",
                    "lcu"=>$uid,
                ));
                break;
            case "edit":
                $connection->createCommand()->update("sal_clue_invoice",array(
                    "invoice_name"=>$this->invoice_name,
                    "city"=>$this->city,
                    "invoice_type"=>$this->invoice_type,
                    "invoice_header"=>$this->invoice_header,
                    "tax_id"=>$this->tax_id,
                    "invoice_address"=>$this->invoice_address,
                    "invoice_number"=>$this->invoice_number,
                    "invoice_user"=>$this->invoice_user,
                    "invoice_rmk"=>$this->invoice_rmk,
                    "invoice_phone"=>$this->invoice_phone,
                    "show_pay"=>$this->show_pay,
                    "show_cpy"=>$this->show_cpy,
                    "show_opy"=>$this->show_opy,
                    "z_display"=>$this->z_display,
                    "luu"=>$uid,
                ),"id=:id",array(":id"=>$this->id));
                $this->sendInvoiceStoreToU();
                break;
            case "delete":
                $connection->createCommand()->delete("sal_clue_invoice","id=:id",array(":id"=>$this->id));
                $connection->createCommand()->delete("sal_clue_history","table_id=:id and table_type=4",array(":id"=>$this->id));
        }
		return true;
	}

	public function sendInvoiceStoreToU(){
        if($this->clueHeadRow["table_type"]==2){//修改客户内的发票
            $storeRows = Yii::app()->db->createCommand()->select("id")->from("sal_clue_store")
                ->where("invoice_id=:id and u_id is not null",array(":id"=>$this->id))->queryAll();
            if($storeRows){
                $putStore=array();
                foreach ($storeRows as $storeRow){
                    $putStore[]=$storeRow["id"];
                }
                if(!empty($putStore)){
                    $uStoreModel = new CurlNotesByStore();
                    $uStoreModel->sendDataSetByAddStore();
                    $uStoreModel->putAllStoreByStoreIDs($putStore);
                    $uStoreModel->setOutContentByData();
                    $uStoreModel->saveCurlToApi();
                }
            }
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
		return $this->getScenario()=='view';
	}
}
