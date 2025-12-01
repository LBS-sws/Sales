<?php

class ClueStoreForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $clue_id;
	public $clue_type;
	public $store_code;
	public $store_name;
	public $store_full_name;
	public $create_staff;
	public $cust_class_group;
	public $cust_class;
	public $city;
	public $office_id;
	public $area;
	public $district;
	public $address;
	public $cust_person;
	public $cust_tel;
	public $cust_email;
	public $cust_person_role;
	public $invoice_id;
    public $invoice_type;
    public $invoice_header;
    public $tax_id;
    public $invoice_address;
    public $invoice_number;
    public $invoice_user;
	public $latitude;
	public $longitude;
	public $store_remark;
	public $z_display=1;
	public $u_id;
	public $u_group_id;
	public $yewudalei;

	public $clueHeadRow;

    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		$list = array(
            'store_code'=>Yii::t('clue','store code'),//门店名称
            'store_name'=>Yii::t('clue','store name'),//门店名称
            'store_full_name'=>Yii::t('clue','full name'),//门店名称
            'cust_class_group'=>Yii::t('clue','trade type'),//行业类别
            'cust_class'=>Yii::t('clue','trade type'),//行业类别
            'city'=>Yii::t('clue','city manger'),//城市
            'create_staff'=>Yii::t('clue','sales'),//城市
            'office_id'=>Yii::t('clue','office id'),//城市
            'district'=>Yii::t('clue','district'),//区域
            'area'=>Yii::t('clue','area'),//面积
            'address'=>Yii::t('clue','address'),//详细地址
            'cust_person'=>Yii::t('clue','customer person'),//联络人
            'cust_person_role'=>Yii::t('clue','person role'),//联络人
            'cust_tel'=>Yii::t('clue','person tel'),//联络人电话
            'cust_email'=>Yii::t('clue','person email'),//联络人邮箱
            'invoice_id'=>Yii::t('clue','select invoice'),//开票抬头
            'invoice_type'=>Yii::t('clue','invoice type'),//
            'invoice_header'=>Yii::t('clue','invoice header'),//开票抬头
            'invoice_number'=>Yii::t('clue','invoice number'),//
            'invoice_user'=>Yii::t('clue','invoice user'),//
            'tax_id'=>Yii::t('clue','tax id'),//税号
            'invoice_address'=>Yii::t('clue','invoice address'),//开票地址
            'store_remark'=>Yii::t('clue','store remark'),//开票地址
            'z_display'=>Yii::t('clue','z display'),//开票地址
            'latitude'=>Yii::t('clue','punctuation'),//位置标点
            'yewudalei'=>Yii::t('clue','yewudalei'),//
		);
		return $list;
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
	    $list = array();
        $list[]=array('id,clue_id,office_id,store_full_name,clue_type,create_staff,store_code,city,yewudalei','safe');
        $list[]=array('area,district,address,cust_person,cust_tel,cust_email,cust_person_role','safe');
        $list[]=array('latitude,longitude,store_remark,invoice_type,invoice_header,tax_id,invoice_address,invoice_number,invoice_user','safe');
        $list[]=array('clue_id,create_staff,yewudalei','required');
        $list[]=array('store_name,district,cust_class_group,cust_class,z_display','required','on'=>array("new","edit"));
        $list[]=array('clue_id','validateClueID');
        $list[]=array('id','validateID');
        $list[]=array('invoice_id','validateInvoiceID','on'=>array("new","edit"));
        $list[]=array('district','validateDistrict','on'=>array("new","edit"));
		return $list;
	}

    public function validateDistrict($attribute, $param) {
	    $districtType = CGetName::getDistrictStrByKey($this->district,"type");
	    if($districtType!=3){
            $this->addError($attribute, "行政区域异常，请重新选择");
        }
    }
    public function validateID($attribute, $param) {
        $scenario = $this->getScenario();
        if($scenario!="new"){
            $storeRow = Yii::app()->db->createCommand()->select("a.*")
                ->from("sal_clue_store a")
                ->where("a.id=:id",array(":id"=>$this->id))->queryRow();
            if($storeRow){
                if($scenario=="delete"){
                    $sreStoreRow = Yii::app()->db->createCommand()->select("*")
                        ->from("sal_contpro_virtual")
                        ->where("clue_store_id=:id",array(":id"=>$this->id))->queryRow();
                    if($sreStoreRow){
                        $this->addError($attribute, "该门店已关联商机，无法删除");
                    }
                }else{
                    $this->u_id = $storeRow["u_id"];
                }
            }else{
                $this->addError($attribute, "数据异常，请刷新重试");
            }
        }
        $id=empty($this->id)||!is_numeric($this->id)?0:intval($this->id);
        $storeRow = Yii::app()->db->createCommand()->select("id,store_code")
            ->from("sal_clue_store")
            ->where("store_name=:store_name and id!={$id}",array(
                ":store_name"=>$this->store_name
            ))->queryRow();
        if($storeRow){
            $this->addError($attribute, "门店名称已存在({$storeRow['store_code']})，无法重复添加");
        }
    }

    public function validateInvoiceID($attribute, $param) {
	    if(empty($this->invoice_id)){
            if(!empty($this->invoice_type)){
                if(empty($this->invoice_header)){
                    $this->addError("invoice_header", "开票抬头不能为空");
                }
                if(in_array($this->invoice_type,array(1,2))&&empty($this->tax_id)){
                    $this->addError("tax_id", "税号不能为空");
                }
                if($this->invoice_type==2&&empty($this->invoice_address)){
                    $this->addError("invoice_address", "开票地址不能为空");
                }
                if($this->invoice_type==2&&empty($this->invoice_number)){
                    $this->addError("invoice_address", "开户行不能为空");
                }
                if($this->invoice_type==2&&empty($this->invoice_user)){
                    $this->addError("invoice_address", "账号不能为空");
                }
            }
        }else{
            $clueInvoiceModel = new ClueInvoiceForm("view");
            if($clueInvoiceModel->retrieveData($this->invoice_id)){
                $this->invoice_type = $clueInvoiceModel->invoice_type;
                $this->invoice_header = $clueInvoiceModel->invoice_header;
                $this->tax_id = $clueInvoiceModel->tax_id;
                $this->invoice_address = $clueInvoiceModel->invoice_address;
                $this->invoice_number = $clueInvoiceModel->invoice_number;
                $this->invoice_user = $clueInvoiceModel->invoice_user;
            }else {
                $this->addError("invoice_header", $this->getAttributeLabel("invoice_id")."异常");
            }
        }
        $clueInvoiceModel = new ClueInvoiceForm("view");
        if($clueInvoiceModel->retrieveData($this->invoice_id)){
            $this->invoice_type = $clueInvoiceModel->invoice_type;
            $this->invoice_header = $clueInvoiceModel->invoice_header;
            $this->tax_id = $clueInvoiceModel->tax_id;
            $this->invoice_address = $clueInvoiceModel->invoice_address;
            $this->invoice_number = $clueInvoiceModel->invoice_number;
            $this->invoice_user = $clueInvoiceModel->invoice_user;
        }else{
            /*
            if(empty($this->invoice_header)){
                $this->addError("invoice_header", "开票抬头不能为空");
            }
            if(empty($this->tax_id)){
                $this->addError("tax_id", "税号不能为空");
            }
            if(empty($this->invoice_address)){
                $this->addError("invoice_address", "开票地址不能为空");
            }
            */
        }
    }

    public function validateClueID($attribute, $param) {
        $clueHeadModel = new ClueHeadForm("view");
        if($clueHeadModel->retrieveData($this->clue_id)){
            if($clueHeadModel->clue_type==1){
                $this->city=$clueHeadModel->city;
            }else{
                if(empty($this->city)){
                    $this->addError($attribute, "城市不能为空");
                }
            }
            $this->clue_type=$clueHeadModel->clue_type;
            //$this->create_staff=CGetName::getEmployeeIDByMy();
            $this->clueHeadRow = $clueHeadModel->getAttributes();
        }else{
            $this->addError($attribute, "线索不存在，请刷新重试");
        }
    }

	public function fastDataByClueHead(){
	    $this->address=$this->clueHeadRow["address"];
	    $this->store_name=$this->clueHeadRow["cust_name"];
	    $this->store_full_name=$this->clueHeadRow["full_name"];
	    $this->district=$this->clueHeadRow["district"];
	    $this->area=$this->clueHeadRow["area"];
	    $this->cust_person=$this->clueHeadRow["cust_person"];
	    $this->cust_person_role=$this->clueHeadRow["cust_person_role"];
	    $this->cust_tel=$this->clueHeadRow["cust_tel"];
	    $this->cust_email=$this->clueHeadRow["cust_email"];
	    $this->cust_class=$this->clueHeadRow["cust_class"];
	    $this->cust_class_group=$this->clueHeadRow["cust_class_group"];
	    $this->latitude=$this->clueHeadRow["latitude"];
	    $this->longitude=$this->clueHeadRow["longitude"];
	    $this->create_staff=$this->clueHeadRow["rec_employee_id"];
	    $this->yewudalei=$this->clueHeadRow["yewudalei"];
	    $this->store_remark=$this->clueHeadRow["clue_remark"];
    }

	public function retrieveData($index)
	{
		$row = Yii::app()->db->createCommand()->select("a.*,b.invoice_type,b.invoice_header,b.tax_id,b.invoice_address,b.invoice_number,b.invoice_user")
            ->from("sal_clue_store a")
            ->leftJoin("sal_clue_invoice b","a.invoice_id=b.id")
            ->where("a.id=:id",array(":id"=>$index))
            ->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
            $this->clue_id = $row['clue_id'];
            $this->clue_type = $row['clue_type'];
            $this->city = $row['city'];
            $this->office_id = $row['office_id'];
            $this->create_staff = $row['create_staff'];
            $this->yewudalei = $row['yewudalei'];
            $this->store_code = $row['store_code'];
            $this->store_name = $row['store_name'];
            $this->store_full_name = $row['store_full_name'];
            $this->area = $row['area'];
            $this->district = $row['district'];
            $this->address = $row['address'];
            $this->cust_person = $row['cust_person'];
            $this->cust_tel = $row['cust_tel'];
            $this->cust_email = $row['cust_email'];
            $this->cust_person_role = $row['cust_person_role'];
            $this->invoice_id = $row['invoice_id'];
            $this->invoice_type = $row['invoice_type'];
            $this->invoice_header = $row['invoice_header'];
            $this->tax_id = $row['tax_id'];
            $this->invoice_address = $row['invoice_address'];
            $this->invoice_number = $row['invoice_number'];
            $this->invoice_user = $row['invoice_user'];
            $this->store_remark = $row['store_remark'];
            $this->z_display = $row['z_display'];
            $this->cust_class_group = $row['cust_class_group'];
            $this->cust_class = $row['cust_class'];
            $this->latitude = $row['latitude'];
            $this->longitude = $row['longitude'];
            $this->u_group_id = $row['u_group_id'];
            $this->u_id = $row['u_id'];
            return true;
		}else{
		    return false;
        }
	}

    //哪些字段修改后需要记录
    protected static function historyUpdateList($status){
        $list = array('store_name','create_staff','yewudalei','store_full_name','district','cust_class_group','cust_class','area','address','cust_person','cust_person_role',
            'cust_tel','cust_email','invoice_type','invoice_header','tax_id','invoice_address','invoice_number','invoice_user','z_display','store_remark');
        return $list;
    }

    //哪些字段修改后需要记录
    protected static function getNameForValue($type,$value,$modelObj){
        switch ($type){
            case "invoice_type":
                $value = CGetName::getInvoiceTypeStrByKey($value);
                break;
            case "city":
                $value = General::getCityName($value);
                break;
            case "create_staff":
                $value = CGetName::getEmployeeNameByKey($value);
                break;
            case "yewudalei":
                $value = CGetName::getYewudaleiStrByKey($value);
                break;
            case "cust_class_group":
                $value = CGetName::getCustClassGroupStrByKey($value);
                break;
            case "cust_class":
                $value = CGetName::getCustClassStrByKey($value);
                break;
            case "district":
                $value = CGetName::getDistrictStrByKey($value);
                break;
            case "z_display":
                $value = CGetName::getDisplayStrByKey($value);
                break;
        }
        return $value;
    }

    protected function whenEqual($key,$oldArr,$nowArr){
        $valueOne = $oldArr->$key;
        $valueTwo = $nowArr->$key;
        $numberList = array("district","cust_class_group","cust_class","z_display","yewudalei");
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
        $list=array("table_type"=>2,"table_id"=>$this->id,"lcu"=>$uid,"history_type"=>2,"history_html"=>array());
        switch ($this->getScenario()){
            case "edit":
                $model = new ClueStoreForm();
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
			$this->saveInvoice($connection);
			$this->save($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	public function computeSaveStore(){
        $connection = Yii::app()->db;
        $this->historySave($connection);
        $this->saveInvoice($connection);
        $this->save($connection);
    }

	protected function saveInvoice(&$connection)
	{
        $uid = Yii::app()->user->id;
	    if(empty($this->invoice_id)&&!empty($this->invoice_type)&&!empty($this->invoice_header)&&$this->getScenario()!='delete'){//自定义税号
            $invoice_name=$this->store_name."_sys_".time();
            $connection->createCommand()->insert("sal_clue_invoice",array(
                "clue_id"=>$this->clue_id,
                "clue_type"=>$this->clue_type,
                "invoice_name"=>$invoice_name,
                "city"=>$this->city,
                "invoice_type"=>$this->invoice_type,
                "invoice_header"=>$this->invoice_header,
                "tax_id"=>$this->tax_id,
                "invoice_address"=>$this->invoice_address,
                "invoice_number"=>$this->invoice_number,
                "invoice_user"=>$this->invoice_user,
                "lcu"=>$uid,
            ));
            $this->invoice_id = Yii::app()->db->getLastInsertID();
            $connection->createCommand()->insert("sal_clue_history",array(
                "table_id"=>$this->invoice_id,
                "table_type"=>4,
                "history_type"=>1,
                "history_html"=>"<span>新增</span>",
                "lcu"=>$uid,
            ));
        }
    }

	protected function save(&$connection)
	{
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city;
        $this->district=empty($this->district)?null:$this->district;
	    switch ($this->getScenario()){
            case "new":
                $connection->createCommand()->insert("sal_clue_store",array(
                    "clue_id"=>$this->clue_id,
                    "clue_type"=>$this->clue_type,
                    "store_name"=>$this->store_name,
                    "store_full_name"=>$this->store_full_name,
                    "create_staff"=>$this->create_staff,
                    "yewudalei"=>$this->yewudalei,
                    "city"=>$this->city,
                    "office_id"=>empty($this->office_id)?0:$this->office_id,
                    "area"=>$this->area,
                    "district"=>$this->district,
                    "cust_class_group"=>$this->cust_class_group,
                    "cust_class"=>$this->cust_class,
                    "address"=>$this->address,
                    "cust_person"=>$this->cust_person,
                    "cust_tel"=>$this->cust_tel,
                    "cust_email"=>$this->cust_email,
                    "cust_person_role"=>$this->cust_person_role,
                    "invoice_id"=>empty($this->invoice_id)?null:$this->invoice_id,
                    "latitude"=>$this->latitude,
                    "longitude"=>$this->longitude,
                    "store_remark"=>$this->store_remark,
                    "z_display"=>$this->z_display,
                    "lcu"=>$uid,
                ));
                $this->id = Yii::app()->db->getLastInsertID();
                $connection->createCommand()->update("sal_clue_store",array(
                    "store_code"=>$this->computeStoreCode(),
                ),"id=:id",array(":id"=>$this->id));
                $this->setScenario("edit");
                $connection->createCommand()->insert("sal_clue_history",array(
                    "table_id"=>$this->id,
                    "table_type"=>2,
                    "history_type"=>1,
                    "history_html"=>"<span>新增</span>",
                    "lcu"=>$uid,
                ));
                ClientPersonForm::saveUPersonDataByStoreModel($this);
                break;
            case "edit":
                $connection->createCommand()->update("sal_clue_store",array(
                    "store_name"=>$this->store_name,
                    "store_full_name"=>$this->store_full_name,
                    /* 暂时不支持修改城市及办事处
                    "city"=>$this->city,
                    "office_id"=>empty($this->office_id)?0:$this->office_id,
                    */
                    "area"=>$this->area,
                    "district"=>$this->district,
                    "create_staff"=>$this->create_staff,
                    "yewudalei"=>$this->yewudalei,
                    "cust_class_group"=>$this->cust_class_group,
                    "cust_class"=>$this->cust_class,
                    "address"=>$this->address,
                    "cust_person"=>$this->cust_person,
                    "cust_tel"=>$this->cust_tel,
                    "cust_email"=>$this->cust_email,
                    "cust_person_role"=>$this->cust_person_role,
                    "invoice_id"=>empty($this->invoice_id)?null:$this->invoice_id,
                    "latitude"=>$this->latitude,
                    "longitude"=>$this->longitude,
                    "store_remark"=>$this->store_remark,
                    "z_display"=>$this->z_display,
                    "luu"=>$uid,
                ),"id=:id",array(":id"=>$this->id));
                ClientPersonForm::saveUPersonDataByStoreModel($this);
                break;
            case "delete":
                $connection->createCommand()->delete("sal_clue_store","id=:id",array(":id"=>$this->id));
                $connection->createCommand()->delete("sal_clue_history","table_id=:id and table_type=2",array(":id"=>$this->id));
        }
        $this->sendDataByU();
		return true;
	}

    //发送数据至派单系统
    public function sendDataByU(){
        if($this->clueHeadRow['table_type']==2&&in_array($this->getScenario(),array("new","edit"))){
            $uStoreModel = new CurlNotesByStore();
            $uStoreModel->saveDataByStoreID($this,$this->clueHeadRow);
        }
    }

    //不使用
	protected function addClueUArea(){
        if($this->city!=$this->clueHeadRow["city"]){
            $row = Yii::app()->db->createCommand()->select("id")->from("sal_clue_u_area")
                ->where("clue_id=:clue_id and city_code=:city_code",array(
                    ":clue_id"=>$this->clue_id,
                    ":city_code"=>$this->city,
                ))->queryRow();
            if(!$row){
                $uid = Yii::app()->user->id;
                Yii::app()->db->createCommand()->insert("sal_clue_u_area",array(
                    "clue_id"=>$this->clue_id,
                    "city_code"=>$this->city,
                    "city_type"=>0,
                    "lcu"=>$uid,
                ));
            }
        }
    }

    protected function computeStoreCode(){
        $row = Yii::app()->db->createCommand()->select("count(*) as sum")
            ->from("sal_clue_store")->where("clue_id=:clue_id",array(":clue_id"=>$this->clue_id))->queryRow();
        $num = $row?$row["sum"]:0;
        $num--;
        $charNum = floor($num/1000)+65;
        $num = floor($num%1000);
        $num = "".(1000+$num);
        $num = mb_substr($num,1);
        $this->store_code=$this->clueHeadRow["clue_code"]."-".chr($charNum).$num;
        return $this->store_code;
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
