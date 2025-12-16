<?php

class ClueForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $table_type=1;
	public $clue_code;
	public $entry_date;
	public $cust_name;
	public $full_name;
	public $abbr_code;
	public $service_type;
	public $cust_class_group;
	public $cust_class;
	public $cust_ka_class;
	public $cust_type;
	public $cust_level;
	public $cust_address;
	public $cust_person;
	public $cust_tel;
	public $cust_email;
	public $cust_person_role;
	public $cont_person;
	public $cont_tel;
	public $cont_email;
	public $cont_person_role;
	public $district;
	public $group_bool="N";
	public $cust_vip="N";
	public $street;
	public $address;
	public $area;
	public $clue_source;
	public $clue_type;
	public $clue_status;
	public $rec_type;
	public $rec_employee_id;
	public $extra_user;
	public $last_date;
	public $end_date;
	public $end_employee_id;
	public $end_flow_id;
	public $clue_remark;
	public $city;
	public $del_num=0;
    public $talk_city_id;
    public $support_user;
    public $busine_id;
    public $clue_tag;
    public $latitude;
    public $longitude;
    public $yewudalei;
    public $box_type;
    public $ka_id;
    public $clue_level_id;   // 客户等级ID (新增字段 - 关联sal_clue_level表)
    public $clue_tag_ids = array();   // 客户标签IDs (新增字段 - 多个标签ID数组)
    public $u_id;
    public $u_group_id;
    public $lcu;
    public $luu;
    public $lcd;
    public $lud;

    public $login_employee_id;

    public $docMaxSize = 10485760;//1024*1024*10 = 10M
    public $fileJson=array();
    public $file_num=0;
    public $lookFileRow;

    protected $update_group_bool=false;

    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		$list = array(
            'clue_code'=>Yii::t('clue','clue code'),//线索编号
            'clue_tag'=>Yii::t('clue','clue label'),//线索标签（未使用)
            'cust_name'=>Yii::t('clue','customer name'),//客户名
            'full_name'=>Yii::t('clue','full name'),//客户名
            'clue_type'=>Yii::t('clue','clue type'),//线索类型
            'cust_ka_class'=>Yii::t('clue','customer class'),//客户类型
            'cust_type'=>Yii::t('clue','customer type'),//客户类型
            'cust_level'=>Yii::t('clue','level name'),//客户分级
            'cust_class_group'=>Yii::t('clue','trade type'),//行业类别
            'cust_class'=>Yii::t('clue','trade type'),//行业类别
            'cust_address'=>Yii::t('clue','person address'),//联系人地址
            'cust_person'=>Yii::t('clue','customer person'),//联系人
            'city'=>Yii::t('clue','city manger'),//城市
            'district'=>Yii::t('clue','district'),//区域
            'clue_source'=>Yii::t('clue','clue source'),//线索来源
            'last_date'=>Yii::t('clue','last flow date'),//下次跟进时间
            'rec_employee_id'=>Yii::t('clue','rec employee'),//跟进员工
            'extra_user'=>Yii::t('clue','extra username'),//额外跟进的员工
            'end_date'=>Yii::t('clue','end flow date'),//最近跟进时间
            'group_bool'=>Yii::t('clue','group bool'),//是否集团客户

            'entry_date'=>Yii::t('clue','entry date'),//线索录入时间
            'service_type'=>Yii::t('clue','service type'),//服务类型
            'cust_tel'=>Yii::t('clue','person tel'),//联系人电话
            'cust_email'=>Yii::t('clue','person email'),//联系人电话
            'cust_person_role'=>Yii::t('clue','person role'),//联系人职务
            'cont_person'=>Yii::t('clue','cont person'),//合同联系人
            'cont_tel'=>Yii::t('clue','cont tel'),//合同联系人电话
            'cont_email'=>Yii::t('clue','cont email'),//合同联系人电话
            'cont_person_role'=>Yii::t('clue','cont role'),//合同联系人职务
            'street'=>Yii::t('clue','street'),//街道
            'address'=>Yii::t('clue','address'),//详细地址
            'area'=>Yii::t('clue','area'),//面积
            'clue_status'=>Yii::t('clue','clue status'),//线索状态
            'rec_type'=>Yii::t('clue','rec type'),//接收类型
            'end_employee_id'=>Yii::t('clue','end employee'),//最后跟进员工
            'end_flow_id'=>Yii::t('clue','end flow id'),//最后跟进流程的id
            'clue_remark'=>Yii::t('clue','clue remark'),//线索备注
            'busine_id'=>Yii::t('clue','busine name'),//业务模式
            'support_user'=>Yii::t('clue','support user'),//区域支持者
            'talk_city_id'=>Yii::t('clue','talk city'),//洽谈地区
            'latitude'=>Yii::t('clue','punctuation'),//位置标点
            'yewudalei'=>Yii::t('clue','yewudalei'),//
            'clue_level_id'=>'客户等级',//客户等级
            'clue_tag_ids'=>'客户标签',//客户标签
            'cust_vip'=>Yii::t('clue','customer vip'),//
            'u_id'=>Yii::t('clue','u id'),//
		);
		return $list;
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
	    $list = array();
        $list[]=array('id,full_name,end_date,table_type,last_date,rec_type,rec_employee_id,latitude,longitude,yewudalei,group_bool,cust_vip,clue_remark,clue_level_id,clue_tag_ids','safe');
        $list[]=array('clue_type,city,entry_date,cust_name,service_type,cust_class_group,cust_class','required');
        $list[]=array('clue_status,clue_code,street,address,clue_source,area,cust_person,cust_tel,cust_email,cust_address,cust_person_role,cont_email','safe');
	    if($this->clue_type==1){//地推
            $list[]=array('district','required');
        }else{
            $list[]=array('talk_city_id,cont_person,cont_tel,cont_email,cont_person_role,support_user,busine_id,district','safe');
            $list[]=array('cust_level,cust_type,cust_ka_class','required');
        }
        $list[]=array('id','validateID');
        $list[]=array('clue_type','validateClueType');
        $list[]=array('fileJson','validateFileJson');
        $listEx = $this->rulesEx();
	    if(!empty($listEx)){
            $list = array_merge($list,$listEx);
        }
		return $list;
	}

    public function validateFileJson($attribute, $param) {
        $modelClass = get_class($this);
        if(isset($_FILES[$modelClass]['name']['fileJson'])){
            foreach ($_FILES[$modelClass]['name']['fileJson'] as $key=>$row){
                $fileName = $row["fileVal"];
                if(empty($fileName)){
                    continue;
                }
                $fileError = isset($_FILES[$modelClass]['error']['fileJson'][$key]["fileVal"])?$_FILES[$modelClass]['error']['fileJson'][$key]["fileVal"]:100;
                if(empty($fileError)){
                    $fileType = $_FILES[$modelClass]['type']['fileJson'][$key]["fileVal"];
                    $fileSize = floatval($_FILES[$modelClass]['size']['fileJson'][$key]["fileVal"]);
                    $fileTmpName = $_FILES[$modelClass]['tmp_name']['fileJson'][$key]["fileVal"];
                    $ext = pathinfo($fileName,PATHINFO_EXTENSION);
                    if(in_array($ext,array("jpeg","jpg","png","xlsx","xls","pdf","docx","txt"))){
                        if($fileSize>$this->docMaxSize){
                            $this->addError($attribute,'文件大小不能大于10M'.$fileSize);
                            break;
                        }else{
                            $this->fileJson[$key]["file"]=array(
                                "fileTmpName"=>$fileTmpName,
                                "fileSize"=>$fileSize,
                                "fileType"=>$fileType,
                                "fileName"=>$fileName,
                                "fileExt"=>$ext,
                            );
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

    public function validateClueType($attribute, $param) {
	    $clueList = CGetName::getAllClueTypeList();
	    $keyStr = "".$this->clue_type;
	    if(!key_exists($keyStr,$clueList)){
            $this->addError($attribute, "线索类型异常({$keyStr})");
        }
    }
    public function validateID($attribute, $param) {
	    $this->login_employee_id=CGetName::getEmployeeIDByMy();
        if ($this->getScenario()!='new') {
            $row = Yii::app()->db->createCommand()->select("a.*")->from("sal_clue a")
                ->where("a.id=:id ".$this->retrieveSqlEx(),array(":id"=>$this->id))->queryRow();
            if ($row) {
                $this->u_id = $row["u_id"];
                $this->clue_type = $row["clue_type"];
                //$this->cust_name = $row["cust_name"];
                //$this->service_type = $row["service_type"];
            }else{
                $this->addError($attribute, "线索不存在，请刷新重试");
            }
        }
        $id=empty($this->id)||!is_numeric($this->id)?0:intval($this->id);
        $row = Yii::app()->db->createCommand()->select("a.clue_code")->from("sal_clue a")
            ->where("a.cust_name=:cust_name and a.id!={$id}",array(
                ":cust_name"=>$this->cust_name,
            ))->queryRow();
        if($row){
            $this->addError($attribute, "该线索与线索（{$row['clue_code']}）重复");
        }
    }

    public function rulesEx(){
        return array();
    }

    protected function retrieveSqlEx(){
        return "";
    }

    public function getFileJson(){
        $this->fileJson=array();
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_clue_file")
            ->where("clue_id=:id",array(":id"=>$this->id))->order("id asc")->queryAll();//
        if($rows){
            foreach ($rows as $row){
                $this->fileJson[]=array(
                    "id"=>$row["id"],
                    "contID"=>$this->id,
                    "fileID"=>$row["phy_file_name"],
                    "fileVal"=>"",
                    "fileName"=>$row["file_name"],
                    "tableName"=>"clue",
                    "uflag"=>"N",
                );
            }
        }
    }

    public function getModelIDByFileID($fileID){
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_clue_file")
            ->where("id=:id",array(":id"=>$fileID))->queryRow();//
        if($row){
            $this->id=$row["clue_id"];
            $this->lookFileRow = $row;
        }else{
            $this->id=0;
        }
    }

	public function retrieveData($index)
	{
        $index = !empty($index)&&is_numeric($index)?intval($index):0;
		$sql = "select a.* from sal_clue a where a.id=".$index." ".$this->retrieveSqlEx();
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->entry_date = General::toDate($row['entry_date']);
			$this->end_date = General::toDateTime($row['end_date']);
			$this->last_date = General::toDateTime($row['last_date']);
			$this->city = $row['city'];
			$this->table_type = $row['table_type'];
			$this->box_type = $row['box_type'];
			$this->clue_type = $row['clue_type'];
			$this->cust_name = $row['cust_name'];
			$this->full_name = $row['full_name'];
			$this->clue_tag = $row['clue_tag'];
            $this->service_type = empty($row['service_type'])?array():json_decode($row['service_type']);
			$this->clue_status = $row['clue_status'];
			$this->clue_code = $row['clue_code'];
			$this->cust_class_group = $row['cust_class_group'];
			$this->cust_ka_class = $row['cust_ka_class'];
			$this->cust_class = $row['cust_class'];
			$this->cust_type = $row['cust_type'];
			$this->cust_level = $row['cust_level'];
			$this->cust_address = $row['cust_address'];
			$this->district = $row['district'];
			$this->cust_vip = $row['cust_vip'];
			$this->group_bool = $row['group_bool'];
			$this->street = $row['street'];
			$this->address = $row['address'];
			$this->clue_source = $row['clue_source'];
			$this->area = $row['area'];
			$this->cust_person = $row['cust_person'];
			$this->cust_tel = $row['cust_tel'];
			$this->cust_email = $row['cust_email'];
			$this->cust_person_role = $row['cust_person_role'];
			$this->cont_person = $row['cont_person'];
			$this->cont_tel = $row['cont_tel'];
			$this->file_num = $row['file_num'];
			$this->cont_email = $row['cont_email'];
			$this->cont_person_role = $row['cont_person_role'];
			$this->rec_type = $row['rec_type'];
			$this->rec_employee_id = $row['rec_employee_id'];
			$this->extra_user = $row['extra_user'];
			$this->support_user = $row['support_user'];
			$this->end_flow_id = $row['end_flow_id'];
			$this->latitude = $row['latitude'];
			$this->longitude = $row['longitude'];
			$this->yewudalei = $row['yewudalei'];
			$this->clue_remark = $row['clue_remark'];
			$this->clue_level_id = $row['clue_level_id'];
			// 从 clue_tag 字段读取逗号分隔的标签ID
			$this->clue_tag_ids = !empty($row['clue_tag']) ? explode(',', $row['clue_tag']) : array();
			$this->u_id = $row['u_id'];
			$this->u_group_id = $row['u_group_id'];
			$this->talk_city_id = $row['talk_city_id']===null?null:json_decode($row['talk_city_id'],true);
			$this->busine_id = $row['busine_id']===null?null:json_decode($row['busine_id'],true);

            $this->ka_id = $row['ka_id'];
            $this->lcu = $row['lcu'];
            $this->luu = $row['luu'];
            $this->lcd = $row['lcd'];
            $this->lud = $row['lud'];

            return true;
		}else{
		    return false;
        }
	}

	public static function addExtraUserByMy($clue_id){
        $uid = Yii::app()->user->id;
        $clueModel = new ClueForm("view");
        if($clueModel->retrieveData($clue_id)){
            $citylist = Yii::app()->user->city_allow();
            if (strpos($citylist,"'{$clueModel->city}'")!==false){
                $extra_user=empty($clueModel->extra_user)?array():explode(",",$clueModel->extra_user);
                if(!in_array($uid,$extra_user)){
                    $extra_user[]=$uid;
                    Yii::app()->db->createCommand()->update("sal_clue",array(
                        "extra_user"=>implode(",",$extra_user),
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$clueModel->id));
                }
            }
        }
    }

	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->historySave($connection);
			$this->save($connection);
			$this->saveFile();
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

    protected function getFilePath(){
        $path="CRM/clue_".(Yii::app()->params['envSuffix']==""?"prod":Yii::app()->params['envSuffix']);
        $path.="/".$this->id;
        return $path;
    }

    //保存附件
    protected function saveFile(){
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
                            "clue_id"=>$this->id,
                            "file_name"=>$row["fileName"],
                        );
                        if(isset($row["file"])){
                            $file_name = hash_file('md5',$row["file"]["fileTmpName"]);
                            $file_name = $file_name.".".$row["file"]["fileExt"];
                            $saveList["phy_file_name"] = $file_name;//文件名称（系统名）
                            $saveList["phy_path_name"] = $path;//文件地址
                            $saveList["display_name"] = $row["file"]["fileName"];//文件名（上传名）
                            $saveList["file_type"] = $row["file"]["fileType"];
                            $qiNiuFile->uploadFile($path."/".$file_name,$row["file"]["fileTmpName"]);
                            //move_uploaded_file($row["file"]["fileTmpName"],$path."/".$file_name);
                        }
                        switch ($row["uflag"]){
                            case "Y"://修改，新增
                                if(empty($row["id"])){
                                    $saveList["lcu"]=$uid;
                                    Yii::app()->db->createCommand()->insert("sal_clue_file",$saveList);
                                }else{
                                    $saveList["luu"]=$uid;
                                    Yii::app()->db->createCommand()->update("sal_clue_file",$saveList,"id=:id and clue_id=:clue_id",array(":id"=>$row["id"],":clue_id"=>$this->id));
                                }
                                break;
                            case "D"://删除
                                Yii::app()->db->createCommand()->delete("sal_clue_file","id=:id and clue_id=:clue_id",array(":id"=>$row["id"],":clue_id"=>$this->id));
                                break;
                        }
                    }
                }
                break;

            case "delete":
                Yii::app()->db->createCommand()->delete("sal_clue_file","clue_id=:clue_id",array(":clue_id"=>$this->id));
                /*$dirPath = Yii::app()->params['docmanPath']."/../upload/".Yii::app()->params['systemId'];
                $dirPath.="/cont_".(Yii::app()->params['envSuffix']==""?"prod":Yii::app()->params['envSuffix']);
                $dirPath.="/".$this->id;
                $this->deleteDir($dirPath);
                */
                break;
        }
        $qiNiuFile->end();
        $sql = "update sal_clue a set file_num=(SELECT count(b.id) FROM sal_clue_file b WHERE b.clue_id=a.id)";
        Yii::app()->db->createCommand($sql)->execute();
    }

    //哪些字段修改后需要记录
    protected static function historyUpdateList($status){
        $list = array('entry_date','cust_name','full_name','service_type','cust_class_group','cust_class','street','address','clue_source','area','cust_person','cust_tel','cust_email',
            'cust_vip','group_bool','cust_address','cust_person_role','yewudalei');
        if($status==1){//地推
            $expr = array('district');
            $list=array_merge($list,$expr);
        }else{
            //'talk_city_id','busine_id',
            $expr = array('district','support_user','cust_type','cust_ka_class','cust_level','cont_person','cont_tel','cont_email','cont_person_role');
            $list=array_merge($list,$expr);
        }
        return $list;
    }

    //哪些字段修改后需要记录
    protected static function getNameForValue($type,$value,$modelObj){
        switch ($type){
            case "city":
                $value = General::getCityName($value);
                break;
            case "clue_type":
                $value = CGetName::getClueTypeStr($value);
                break;
            case "service_type":
                $value = CGetName::getServiceTypeStrByList($value);
                break;
            case "district":
                $value = CGetName::getDistrictStrByKey($value);
                break;
            case "clue_source":
                $value = CGetName::getClueSourceStrByKey($value);
                break;
            case "support_user":
                $value = CGetName::getEmployeeNameByKey($value);
                break;
            case "cust_class_group":
                $value = CGetName::getCustClassGroupStrByKey($value);
                break;
            case "cust_class":
                $value = CGetName::getCustClassStrByKey($value);
                break;
            case "cust_level":
                $value = CGetName::getCustLevelStrByKey($value);
                break;
            case "cust_ka_class":
                $value = CGetName::getCustKAClassStrByKey($value);
                break;
            case "cust_type":
                $value = CGetName::getCustTypeKAStrByKey($value);
                break;
            case "group_bool":
            case "cust_vip":
                $value = CGetName::getCustVipStrByKey($value);
                break;
            case "yewudalei":
                $value = CGetName::getYewudaleiStrByKey($value);
                break;
        }
        return $value;
    }

    protected function whenEqual($key,$oldArr,$nowArr){
        $valueOne = $oldArr->$key;
        $valueTwo = $nowArr->$key;
        if($key=="entry_date"){
            $valueOne = General::toDate($valueOne);
            $valueTwo = General::toDate($valueTwo);
        }
        $numberList = array("yewudalei","cust_class_group","cust_class","cust_ka_class","cust_type","cust_level","district","clue_source","rec_employee_id","support_user");
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
        $list=array("table_type"=>1,"table_id"=>$this->id,"lcu"=>$uid,"history_type"=>2,"history_html"=>array());
        switch ($this->getScenario()){
            case "edit":
                $model = new ClueForm();
                $model->retrieveData($this->id);
                $keyArr = self::historyUpdateList($model->clue_type);
                foreach ($keyArr as $key){
                    if($this->whenEqual($key,$model,$this)){
                        if($key=="group_bool"){
                            $this->update_group_bool=true;
                        }
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

	protected function save(&$connection)
	{
		return true;
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

    protected function computeClueCode(){
        $phpExcelPath = Yii::getPathOfAlias('ext.pinyin');
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'Autoloader.php');
        $pinyin = new Pinyin(); // 默认
        $full_name = empty($this->full_name)?$this->cust_name:$this->full_name;
        $computeList = CGetName::computeClueCode($pinyin,$full_name);
        $this->clue_code=$computeList["clue_code"];
        $this->abbr_code=$computeList["abbr_code"];
        return $this->clue_code;
    }

    /**
     * 获取客户等级列表
     * 用于前端下拉框展示
     * 支持生产和测试环境的数据库前缀自动区分
     *
     * @return array 格式: array(id => level_name)
     */
    public static function getClueLevelList()
    {
        $suffix = Yii::app()->params['envSuffix'];
        $levelList = Yii::app()->db->createCommand()
            ->select('id, level_name')
            ->from("sales{$suffix}.sal_clue_level")
            ->where('status = :status', array(':status' => 1))
            ->order('sort ASC')
            ->queryAll();

        $options = array('' => '-- 选择等级 --');
        if (!empty($levelList)) {
            foreach ($levelList as $level) {
                $options[$level['id']] = $level['level_name'];
            }
        }
        return $options;
    }

    /**
     * 获取所有可用的客户标签列表
     * 用于表单下拉框
     *
     * @return array 格式: array(id => tag_name)
     */
    public static function getClueTagList()
    {
        $suffix = Yii::app()->params['envSuffix'];
        $tagList = Yii::app()->db->createCommand()
            ->select('id, tag_name')
            ->from("sales{$suffix}.sal_clue_tag")
            ->where('status = :status', array(':status' => 1))
            ->order('sort ASC')
            ->queryAll();

        $options = array();
        if (!empty($tagList)) {
            foreach ($tagList as $tag) {
                $options[$tag['id']] = $tag['tag_name'];
            }
        }
        return $options;
    }

    /**
     * 获取所有可用的客户标签列表（带颜色）
     * 用于前端显示
     *
     * @return array 格式: array(id => array('name' => tag_name, 'color' => tag_color))
     */
    public static function getClueTagListWithColor()
    {
        $suffix = Yii::app()->params['envSuffix'];
        $tagList = Yii::app()->db->createCommand()
            ->select('id, tag_name, tag_color')
            ->from("sales{$suffix}.sal_clue_tag")
            ->where('status = :status', array(':status' => 1))
            ->order('sort ASC')
            ->queryAll();

        $options = array();
        if (!empty($tagList)) {
            foreach ($tagList as $tag) {
                $options[$tag['id']] = array(
                    'name' => $tag['tag_name'],
                    'color' => $tag['tag_color']
                );
            }
        }
        return $options;
    }
}
