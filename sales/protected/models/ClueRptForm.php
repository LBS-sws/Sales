<?php

class ClueRptForm extends CFormModel
{
	/* User Fields */
    public $id;
    public $clue_service_id;
    public $clue_id;
    public $clue_type;
    public $city;
    public $sales_id;
    public $lbs_main;
    public $cust_name;
    public $cust_level;
    public $cust_class;
    public $total_amt;
    public $rpt_status=0;
    public $file_type;
    public $is_seal="Y";
    public $seal_type_id;
    public $cont_type_id;
    public $service_type_id;
    public $bill_week;
    public $audit_type;
    public $cut_type;
    public $fee_add;
    public $remark;
    public $mh_remark;
    public $mh_id;
    public $lcu;
    public $luu;
    public $lcd;
    public $lud;
    public $docMaxSize = 10485760;//1024*1024*10 = 10M

    public $rptFileJson;
    public $rptFileSum=0;
    public $contFileJson;
    public $contFileSum=0;
    public $fileJson;

	public $clueHeadRow;
	public $clueServiceRow;
	public $staffRow;

	public $lookFileRow;
	public $goMhWebUrl;

    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
        return array(
            'clue_id'=>Yii::t('clue','clue id'),//线索id
            'clue_service_id'=>Yii::t('clue','clue service id'),//商机id
            'clue_code'=>Yii::t('clue','clue code'),//线索编号
            'clue_type'=>Yii::t('clue','clue type'),//线索类型
            'cust_name'=>Yii::t('clue','clue name'),//客户名
            'city'=>Yii::t('clue','city manger'),//城市
            'cust_class'=>Yii::t('clue','trade type'),//行业类别
            'cust_level'=>Yii::t('clue','level name'),//客户分级
            'rpt_status'=>Yii::t('clue','status'),//状态
            'total_amt'=>Yii::t('clue','rpt amt'),//预估成交金额
            'file_type'=>Yii::t('clue','file type'),// 文件类型
            'is_seal'=>Yii::t('clue','is seal'),//
            'seal_type_id'=>Yii::t('clue','seal type'),//印章类型
            'cont_type_id'=>Yii::t('clue','cont type'),//合同类型
            'service_type_id'=>Yii::t('clue','service type free'),//服务频次
            'bill_week'=>Yii::t('clue','bill week'),//账期
            'audit_type'=>Yii::t('clue','t audit type'),//技术审核
            'cut_type'=>Yii::t('clue','t cut type'),//扣款条款
            'fee_add'=>Yii::t('clue','fee add'),//附加费用
            'yewudalei'=>Yii::t('clue','yewudalei'),//
            'mh_remark'=>Yii::t('clue','mh remark'),//
            'lbs_main'=>Yii::t('clue','lbs main'),//
            'sales_id'=>Yii::t('clue','sales'),//
            'lcd'=>Yii::t('clue','create date'),//
            'mh_id'=>Yii::t('clue','report mh id'),//
            'rptFileJson'=>"附件1投标文件合同模板报价单",//
            'contFileJson'=>"附件2成本核算表",//
        );
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
	    $list = array();
        $list[]=array('id,clue_id,clue_type,city,sales_id,lbs_main,cust_name,cust_class,cust_level,
        total_amt,rpt_status,mh_remark,mh_id,file_type,is_seal,seal_type_id,cont_type_id,service_type_id,bill_week,audit_type,cut_type,fee_add','safe');
        $list[]=array('clue_service_id','required');
        $list[]=array('lbs_main,total_amt,file_type,is_seal,cont_type_id,service_type_id,bill_week,audit_type,cut_type,fee_add','required',"on"=>array("audit"));
        $list[]=array('clue_service_id','validateClueServiceID');
        $list[]=array('cust_class,cust_level','required');
        $list[]=array('id','validateID');
        $list[]=array('is_seal','validateIsSeal',"on"=>array("audit"));
        $list[]=array('rptFileJson','validateFileJson');
        $list[]=array('contFileJson','validateFileJson');
        $list[]=array('allFileJson','validateAllFile',"on"=>array("audit"));
		return $list;
	}

    public function validateAllFile($attribute, $param) {
	    if(empty($this->rptFileSum)){
	        $id=empty($this->id)?0:$this->id;
            $this->rptFileSum = Yii::app()->db->createCommand()->select("count(id)")
                ->from("sal_clue_rpt_file")->where("group_id=0 and clue_id=:clue_id and clue_service_id=:clue_service_id and rpt_id=:rpt_id",array(
                    ":clue_id"=>$this->clue_id,
                    ":clue_service_id"=>$this->clue_service_id,
                    ":rpt_id"=>$id,
                ))->queryScalar();
        }
	    if(empty($this->contFileSum)){
	        $id=empty($this->id)?0:$this->id;
            $this->contFileSum = Yii::app()->db->createCommand()->select("count(id)")
                ->from("sal_clue_rpt_file")->where("group_id=1 and clue_id=:clue_id and clue_service_id=:clue_service_id and rpt_id=:rpt_id",array(
                    ":clue_id"=>$this->clue_id,
                    ":clue_service_id"=>$this->clue_service_id,
                    ":rpt_id"=>$id,
                ))->queryScalar();
        }
        if(empty($this->rptFileSum)){
            $this->addError($attribute,'附件1投标文件合同模板报价单至少上传一个文件');
        }
        if(empty($this->contFileSum)){
            $this->addError($attribute,'附件2成本核算表至少上传一个文件');
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

    public function validateFileJson($attribute, $param) {
        if(isset($_FILES['ClueRptForm']['name'][$attribute])){
            foreach ($_FILES['ClueRptForm']['name'][$attribute] as $key=>$row){
                $fileName = $row["fileVal"];
                if(empty($fileName)){
                    continue;
                }
                $fileError = isset($_FILES['ClueRptForm']['error'][$attribute][$key]["fileVal"])?$_FILES['ClueRptForm']['error'][$attribute][$key]["fileVal"]:100;
                if(empty($fileError)){
                    $fileType = $_FILES['ClueRptForm']['type'][$attribute][$key]["fileVal"];
                    $fileSize = floatval($_FILES['ClueRptForm']['size'][$attribute][$key]["fileVal"]);
                    $fileTmpName = $_FILES['ClueRptForm']['tmp_name'][$attribute][$key]["fileVal"];
                    $ext = pathinfo($fileName,PATHINFO_EXTENSION);
                    if(in_array($ext,array("jpeg","jpg","png","xlsx","xls","pdf","docx","txt"))){
                        if($fileSize>$this->docMaxSize){
                            $this->addError($attribute,'文件大小不能大于10M'.$fileSize);
                            break;
                        }else{
                            $label = &$this->$attribute;
                            $label[$key]["file"]=array(
                                "fileTmpName"=>$fileTmpName,
                                "fileSize"=>$fileSize,
                                "fileType"=>$fileType,
                                "fileName"=>$fileName,
                                "fileExt"=>$ext,
                            );
                            if($attribute=="rptFileJson"){
                                $this->rptFileSum++;
                            }else{
                                $this->contFileSum++;
                            }
                        }
                    }else{
                        $this->addError($attribute,'文件格式异常，请重试上传');
                        break;
                    }
                }else{
                    $this->addError($attribute,'文件异常，请刷新重试');
                    break;
                }
            }
        }
    }

    public function validateID($attribute, $param) {
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_clue_rpt")
            ->where("clue_service_id=:id",array(":id"=>$this->clue_service_id))
            ->queryRow();
        if($row){
            $this->id = $row["id"];
            $this->mh_id = $row["mh_id"];
        }else{
            $this->mh_id = null;
        }
    }

    public function validateClueServiceID($attribute, $param) {
	    $clueServiceModel = new ClueServiceForm("view");
        if($clueServiceModel->retrieveData($this->clue_service_id)){
            $this->clue_id = $clueServiceModel->clue_id;
            $this->clueServiceRow = $clueServiceModel->getAttributes();
            $clueHeadModel = new ClueHeadForm("view");
            if($clueHeadModel->retrieveData($this->clue_id)){
                $this->cust_level=$clueHeadModel->cust_level;
                $this->cust_class=$clueHeadModel->cust_class;
                $this->cust_name=$clueHeadModel->cust_name;
                $this->clue_type=$clueHeadModel->clue_type;
                $this->sales_id=$clueHeadModel->rec_employee_id;
                $this->city=$clueHeadModel->city;
                $this->clueHeadRow = $clueHeadModel->getAttributes();
                if(empty($clueHeadModel->yewudalei)){
                    $this->addError($attribute, "请先填写客户的业务大类");
                }
            }else{
                $this->addError($attribute, "线索不存在，请刷新重试");
            }
        }else{
            $this->addError($attribute, "商机不存在，请刷新重试");
        }
    }

    public function getModelIDByFileID($fileID){
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_clue_rpt_file")
            ->where("id=:id",array(":id"=>$fileID))->queryRow();//
        if($row){
            $this->id=$row["rpt_id"];
            $this->lookFileRow = $row;
        }else{
            $this->id=0;
        }
    }

    public function getAllFileJson(){
        $this->rptFileJson=array();
        $this->contFileJson=array();
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_clue_rpt_file")
            ->where("rpt_id=:id",array(":id"=>$this->id))->order("id asc")->queryAll();//
        if($rows){
            foreach ($rows as $row){
                $temp = array(
                    "id"=>$row["id"],
                    "contID"=>$this->id,
                    //"fileID"=>$row["phy_path_name"]."/".$row["phy_file_name"],
                    "fileID"=>$row["phy_file_name"],
                    "fileVal"=>"",
                    "fileName"=>$row["file_name"],
                    "tableName"=>"rpt",
                    "uflag"=>"N",
                );
                if($row["group_id"]==0){
                    $this->rptFileJson[]=$temp;
                }else{
                    $this->contFileJson[]=$temp;
                }
            }
        }
    }

	public function retrieveData($index)
	{
		$sql = "select a.* from sal_clue_rpt a where a.id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
            $this->clue_id = $row['clue_id'];
            $this->clue_service_id = $row['clue_service_id'];
            $this->city = $row['city'];
            $this->sales_id = $row['sales_id'];
            $this->lbs_main = $row['lbs_main'];
            $this->cust_name = $row['cust_name'];
            $this->cust_level = $row['cust_level'];
            $this->cust_class = $row['cust_class'];
            $this->total_amt = $row['total_amt'];
            $this->rpt_status = $row['rpt_status'];
            $this->remark = $row['remark'];
            $this->mh_remark = $row['mh_remark'];
            $this->mh_id = $row['mh_id'];

            $this->file_type = $row['file_type'];
            $this->is_seal = $row['is_seal'];
            $this->seal_type_id = $row['seal_type_id'];
            $this->cont_type_id = $row['cont_type_id'];
            $this->service_type_id = $row['service_type_id'];
            $this->bill_week = $row['bill_week'];
            $this->audit_type = $row['audit_type'];
            $this->cut_type = $row['cut_type'];
            $this->fee_add = $row['fee_add'];
            return true;
		}else{
		    return false;
        }
	}

    //哪些字段修改后需要记录
    protected static function historyUpdateList($status){
        $list = array('city','sales_id','lbs_main','cust_name','cust_level','total_amt','file_type','is_seal',
            'seal_type_id','cont_type_id','service_type_id','bill_week','audit_type','cut_type','fee_add');
        return $list;
    }

    //哪些字段修改后需要记录
    protected static function getNameForValue($type,$value,$modelObj){
        switch ($type){
            case "city":
                $value = General::getCityName($value);
                break;
            case "sales_id":
                $value = CGetName::getEmployeeNameByKey($value);
                break;
            case "lbs_main":
                $value = CGetName::getLbsMainNameByKey($value);
                break;
            case "cust_class":
                $value = CGetName::getCustClassStrByKey($value);
                break;
            case "cust_level":
                $value = CGetName::getCustLevelStrByKey($value);
                break;
//file_type,seal_type_id,cont_type_id,service_type_id,bill_week,audit_type,cut_type,fee_add
            case "file_type":
                $value = CGetName::getFileTypeStrByKey($value);
                break;
            case "is_seal":
                $value = CGetName::getCustVipStrByKey($value);
                break;
            case "seal_type_id":
                $value = CGetName::getSealTypeStrByID($value);
                break;
            case "cont_type_id":
                $value = CGetName::getContTypeStrByKey($value);
                break;
            case "service_type_id":
                $value = CGetName::getServiceFreeStrByKey($value);
                break;
            case "bill_week":
                $value = CGetName::getBillWeekStrByKey($value);
                break;
            case "audit_type":
                $value = CGetName::getAuditTypeStrByKey($value);
                break;
            case "cut_type":
            case "fee_add":
                $value = CGetName::getHasAndNotStrByKey($value);
                break;
        }
        return $value;
    }

    protected function whenEqual($key,$oldArr,$nowArr){
        $valueOne = $oldArr->$key;
        $valueTwo = $nowArr->$key;
        $numberList = array('sales_id','lbs_main','cust_class','cust_level','total_amt','file_type','is_seal',
            'seal_type_id','cont_type_id','service_type_id','bill_week','audit_type','cut_type','fee_add');
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
        $list=array("table_type"=>3,"table_id"=>$this->id,"lcu"=>$uid,"history_type"=>2,"history_html"=>array());
        switch ($this->getScenario()){
            case "edit":
                $model = new ClueRptForm();
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
            $this->historySave($connection);
			$this->save($connection);
			$this->saveAllFile();
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

	protected function save(&$connection)
	{
        $uid = Yii::app()->user->id;
        $saveArr = array(
            "clue_id"=>$this->clue_id,
            "clue_type"=>$this->clue_type,
            "clue_service_id"=>$this->clue_service_id,
            "city"=>$this->city,
            "sales_id"=>$this->sales_id,
            "lbs_main"=>CGetName::getNumberNull($this->lbs_main),
            "cust_name"=>$this->cust_name,
            "cust_class"=>CGetName::getNumberNull($this->cust_class),
            "cust_level"=>CGetName::getNumberNull($this->cust_level),
            "total_amt"=>CGetName::getNumberNull($this->total_amt),
            "file_type"=>CGetName::getNumberNull($this->file_type),
            "is_seal"=>$this->is_seal,
            "seal_type_id"=>CGetName::getNumberNull($this->seal_type_id),
            "cont_type_id"=>CGetName::getNumberNull($this->cont_type_id),
            "service_type_id"=>CGetName::getNumberNull($this->service_type_id),
            "bill_week"=>CGetName::getNumberNull($this->bill_week),
            "audit_type"=>CGetName::getNumberNull($this->audit_type),
            "cut_type"=>CGetName::getNumberNull($this->cut_type),
            "fee_add"=>CGetName::getNumberNull($this->fee_add),
            "mh_remark"=>null,
            "rpt_status"=>$this->rpt_status,
        );
	    switch ($this->getScenario()){
            case "new":
                $saveArr["lcu"]=$uid;
                $connection->createCommand()->insert("sal_clue_rpt",$saveArr);
                $this->id = Yii::app()->db->getLastInsertID();
                $this->resetClueAll();//刷新商机/跟进/关联门店信息
                break;
            case "edit":
                $saveArr["luu"]=$uid;
                $connection->createCommand()->update("sal_clue_rpt",$saveArr,"id=:id",array(":id"=>$this->id));
                $this->resetClueAll();//刷新商机/跟进/关联门店信息
                break;
            case "delete":
                $connection->createCommand()->delete("sal_clue_rpt","id=:id",array(":id"=>$this->id));
                $connection->createCommand()->delete("sal_clue_history","table_id=:id and table_type=3",array(":id"=>$this->id));
        }
		return true;
	}

    protected function getFilePath(){
        $path="CRM/rpt_".(Yii::app()->params['envSuffix']==""?"prod":Yii::app()->params['envSuffix']);
        $path.="/".$this->id;
        return $path;
    }

    protected function deleteDir($dirPath){
        if (!is_dir($dirPath)) {
            return;
        }
        $files = glob($dirPath . '/*');
        foreach ($files as $file) {
            is_dir($file) ? $this->deleteDir($file) : unlink($file);
        }
        rmdir($dirPath);
    }

    protected function saveAllFile(){
        $this->fileJson = $this->rptFileJson;
        $this->saveFile();
        $this->fileJson = $this->contFileJson;
        $this->saveFile(1);
    }

    //保存附件
    protected function saveFile($group_id=0){
        $qiNiuFile = new QiNiuFile();
        $qiNiuFile->start();
        $path = $this->getFilePath();
        $uid = Yii::app()->user->id;
        switch ($this->getScenario()){
            case "new":
            case "edit":
                if(!empty($this->fileJson)){
                    foreach ($this->fileJson as $row){
                        $saveList = array(
                            "clue_id"=>$this->clue_id,
                            "clue_service_id"=>$this->clue_service_id,
                            "rpt_id"=>$this->id,
                            "file_name"=>$row["fileName"],
                        );
                        if(isset($row["file"])){
                            $file_name = hash_file('md5',$row["file"]["fileTmpName"]);
                            $file_name = $file_name.".".$row["file"]["fileExt"];
                            $saveList["phy_file_name"] = $file_name;//文件名称（系统名）
                            $saveList["phy_path_name"] = $path;//文件地址
                            $saveList["display_name"] = $row["file"]["fileName"];//文件名（上传名）
                            $saveList["file_type"] = $row["file"]["fileType"];
                            $saveList["group_id"] = $group_id;
                            $qiNiuFile->uploadFile($path."/".$file_name,$row["file"]["fileTmpName"]);
                            //move_uploaded_file($row["file"]["fileTmpName"],$path."/".$file_name);
                        }
                        switch ($row["uflag"]){
                            case "Y"://修改，新增
                                if(empty($row["id"])){
                                    $saveList["lcu"]=$uid;
                                    Yii::app()->db->createCommand()->insert("sal_clue_rpt_file",$saveList);
                                }else{
                                    $saveList["luu"]=$uid;
                                    Yii::app()->db->createCommand()->update("sal_clue_rpt_file",$saveList,"id=:id and rpt_id=:rpt_id",array(":id"=>$row["id"],":rpt_id"=>$this->id));
                                }
                                break;
                            case "D"://删除
                                Yii::app()->db->createCommand()->delete("sal_clue_rpt_file","id=:id and rpt_id=:rpt_id",array(":id"=>$row["id"],":rpt_id"=>$this->id));
                                break;
                        }
                    }
                }
                break;

            case "delete":
                Yii::app()->db->createCommand()->delete("sal_clue_rpt_file","rpt_id=:rpt_id",array(":rpt_id"=>$this->id));
                //$qiNiuFile->removeFile($path);//暂时不做删除功能
                //$dirPath = Yii::app()->params['docmanPath']."/../upload/".Yii::app()->params['systemId'];
                //$dirPath.="/rpt_".(Yii::app()->params['envSuffix']==""?"prod":Yii::app()->params['envSuffix']);
                //$dirPath.="/".$this->id;
                //$this->deleteDir($dirPath);
                break;
        }
        $qiNiuFile->end();
    }

    //刷新商机/跟进/关联门店信息
    protected function resetClueAll(){
        if($this->rpt_status==1){//发送
            Yii::app()->db->createCommand()->update("sal_clue_flow",array(
                "update_bool"=>2
            ),"clue_service_id=:id",array(":id"=>$this->clue_service_id));
            Yii::app()->db->createCommand()->update("sal_clue_service",array(
                "service_status"=>3,
                "rpt_amt"=>$this->total_amt,
            ),"id=:id",array(":id"=>$this->clue_service_id));
        }
    }

    //发送消息至门户网站
    protected function sendDataToMH(){
        $list = array("bool"=>true,"msg"=>"");
        if($this->rpt_status==1){//发送
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
        }
        return $list;
    }

    protected function getMHData(){
        $lbsCityCode = CGetName::getLbsCityCodeByClueService($this->clue_service_id);
        return array(
            "lbsMain"=>CGetName::getLbsMainStrByKeyAndStr($this->lbs_main,'mh_code'),//主体公司编码
            "lbsMainCityCode"=>$this->city,//主城市编码
            "lbsCityCode"=>$lbsCityCode,//门店城市编码
            "lbsBizCatCode"=>CGetName::getYewudaleiStrByKey($this->clueHeadRow['yewudalei'],'mh_code'),//业务大类编码
            "saleId"=>CGetName::getEmployeeStrByKey('bs_staff_id',$this->clueServiceRow['create_staff']),//销售人员北森id
            "totalAmt"=>floatval($this->total_amt),//报价总金额
            "contractType"=>CGetName::getContTypeStrByKey($this->cont_type_id,'mh_code'),//合同类型
            "isSeal"=>$this->is_seal,//是否用印
            "sealCode"=>$this->is_seal=="Y"?CGetName::getSealCodeStrByKeyAndStr($this->seal_type_id,'mh_code'):"",//印章编码
            "customerName"=>$this->clueHeadRow["cust_name"],
        );
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
        $uid = Yii::app()->user->id;
        $list = array("bool"=>true,"msg"=>"");
        $noticeModel = new CNoticeFlowModel();
        $dataEx = array(
            "vars"=>$this->getMHData()
        );
        $businesskey="rpt_".$this->id;
        $outData = $noticeModel->sendMHAuditByDataEx($businesskey,"LBStbbxsp",$dataEx);
        if(!$outData["status"]){
            $list["bool"] = false;
            $list["msg"]=isset($outData["outData"]["message"])?$outData["outData"]["message"]:$outData["message"];
        }else{
            $instId = isset($outData["outData"]["instId"])?$outData["outData"]["instId"]:null;
            $this->mh_id = $instId;
            Yii::app()->db->createCommand()->update("sal_clue_rpt",array(
                "mh_id"=>$instId,
            ),"id=:id",array(":id"=>$this->id));
            Yii::app()->db->createCommand()->insert("sal_clue_history",array(
                "table_id"=>$this->id,
                "table_type"=>3,
                "history_type"=>30,
                "history_html"=>"<span>发起报价</span>",
                "expr_data"=>$instId,
                "lcu"=>$uid,
            ));
            $taskId = $this->getMHTaskID();
            $url = CGetName::getMHUrlPrx()."/matter/approvalForm?type=request&taskId={$taskId}&instId={$this->mh_id}&isGetApprovalBtn=true";
            $this->goMhWebUrl =CGetName::getMHWebUrlByUrl($url);
        }
        return $list;
    }

	public function isOccupied($index) {
		$rtn = true;//默认不允许删除
		if($this->retrieveData($index)){
		    if(in_array($this->rpt_status,array(0,9))){
                $rtn = false;//允许删除
            }
        }
		return $rtn;
	}

	public function isReadonly() {
		return $this->getScenario()=='view'||!in_array($this->rpt_status,array(0,9));
	}

	public function resetFileToQiNiu(){
        //将旧文件全部发送到七牛空间
        $pathOld=Yii::app()->params['docmanPath'];
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_clue_rpt_file")
            ->where("phy_path_name like '{$pathOld}%'")->order("id asc")->queryAll();//
        echo "start:".count($rows)."<br/>";
        if($rows){
            $qiNiuFile = new QiNiuFile();
            $qiNiuFile->start();
            foreach ($rows as $row){
                $filePath = $row["phy_path_name"]."/".$row["phy_file_name"];
                if (file_exists($filePath)) {
                    $row["phy_path_name"] = str_replace($pathOld,"CRM",$row["phy_path_name"]);
                    $fileBody = file_get_contents($filePath);
                    $key = $row["phy_path_name"]."/".$row["phy_file_name"];
                    $bool = $qiNiuFile->uploadFileBody($key,$fileBody);
                    if($bool){
                        Yii::app()->db->createCommand()->update("sal_clue_rpt_file",array(
                            "phy_path_name"=>$row["phy_path_name"]
                        ),"id=".$row["id"]);
                    }
                }else{
                    var_dump($filePath);
                    echo "<br/>";
                }
            }
            $qiNiuFile->end();
        }
        echo "end!";
    }
}
