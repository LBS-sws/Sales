<?php

class MarketForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $number_no;
	public $company_name;
	public $city;
	public $city_name;
	public $area;
	public $legal_user;
	public $company_size;
	public $company_state;
	public $sign_address;
	public $company_date;
	public $run_address;
	public $company_type;
	public $company_note;
	public $allot_type;
	public $allot_city;
	public $allot_employee;
	public $start_date;
	public $end_date;
	public $end_state_id;
	public $status_type=0;
	public $z_index=0;
	public $ready_bool=1;
	public $back_note;
	public $reject_note;
	public $person_phone;
	public $company_web;

    public $employee_id;
    public $employee_code;
    public $employee_name;

    protected $emailTop=false;//管理员邮件 false：关闭
    protected $emailCity=false;//城市邮件 false：关闭
    protected $emailSales=false;//销售邮件 false：关闭

    public $detail = array(
        array('id'=>0,
            'market_id'=>0,
            'state_id'=>0,
            'lcu'=>'',
            'info_date'=>'',
            'info_text'=>'',
            'uflag'=>'N',
        ),
    );

    public $userDetail = array(
        array('id'=>0,
            'market_id'=>0,
            'user_name'=>'',
            'user_dept'=>'',
            'user_phone'=>'',
            'user_email'=>'',
            'user_wechat'=>'',
            'user_text'=>'',
            'uflag'=>'N',
        ),
    );


    public $files;

    public $docMasterId = array(
        'market'=>0,
    );
    public $removeFileId = array(
        'market'=>0,
    );
    public $no_of_attm = array(
        'market'=>0,
    );
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'number_no'=>Yii::t('market','number no'),
            'company_name'=>Yii::t('market','company name'),
            'allot_city'=>Yii::t('market','allot city'),
            'allot_employee'=>Yii::t('market','allot employee'),
            'start_date'=>Yii::t('market','market start date'),
            'end_date'=>Yii::t('market','market end date'),
            'status_type'=>Yii::t('market','status type'),

            'city_name'=>Yii::t('market','city'),
            'area'=>Yii::t('market','area'),
            'legal_user'=>Yii::t('market','legal user'),
            'company_size'=>Yii::t('market','company size'),
            'company_state'=>Yii::t('market','company state'),
            'sign_address'=>Yii::t('market','sign address'),
            'company_date'=>Yii::t('market','company date'),
            'run_address'=>Yii::t('market','run address'),
            'company_type'=>Yii::t('market','company type'),
            'company_note'=>Yii::t('market','company note'),
            'allot_type'=>Yii::t('market','allot type'),
            'back_note'=>Yii::t('market','back note'),
            'reject_note'=>Yii::t('market','reject note'),
            'company_web'=>Yii::t('market','company web'),
            'person_phone'=>Yii::t('market','person phone'),

            'remark'=>Yii::t('market','remark'),
            'info_lcu'=>Yii::t('market','info user'),
            'state_id'=>Yii::t('market','info state'),
            'info_date'=>Yii::t('market','info date'),
            'info_text'=>Yii::t('market','info text'),

            'user_name'=>Yii::t('market','user name'),
            'user_dept'=>Yii::t('market','user dept'),
            'user_phone'=>Yii::t('market','user phone'),
            'user_email'=>Yii::t('market','user email'),
            'user_wechat'=>Yii::t('market','user wechat'),
            'user_text'=>Yii::t('market','user text'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,number_no,company_name,city,city_name,area,legal_user,company_date,
                company_size,company_state,sign_address,run_address,company_type,company_note,
                allot_type,allot_city,company_web,person_phone,allot_employee,start_date,end_date,
                status_type,back_note,reject_note,z_index,detail,userDetail','safe'),
            array('files, removeFileId, docMasterId, no_of_attm','safe'),
            array('company_name','required'),
            array('id','validateID'),
            array('detail','validateDetail'),
            array('userDetail','validateUserDetail'),
            array('city_name','computeCity'),
		);
	}

    public function validateID($attribute, $params) {
    }

    public function validateUserDetail($attribute, $params) {
	    $this->person_phone="";
	    foreach ($this->userDetail as $row){
            if($row["uflag"]=="Y"){
                if(empty($row["user_name"])){
                    $this->addError($attribute, "联系人不能为空");
                }
            }
            if(in_array($row["uflag"],array("N","Y"))){
                $this->person_phone.=empty($this->person_phone)?"":"\r\n";
                $this->person_phone.=$row["user_name"]."/".$row["user_phone"].";";
            }
        }
    }

    public function validateDetail($attribute, $params) {
        $uid = Yii::app()->user->id;
        $end_date = 0;
	    foreach ($this->detail as $row){
	        if(in_array($row["uflag"],array("Y","N"))){
	            if(!empty($row["info_date"])&&$end_date<strtotime($row["info_date"])){
                    $end_date = strtotime($row["info_date"]);
	                $this->end_date = $row["info_date"];
	                $this->end_state_id = $row["state_id"];
                }
            }
            if($row["uflag"]=="Y"){
                if(empty($row["state_id"])){
                    $this->addError($attribute, "跟进状态不能为空");
                }
                if(empty($row["info_date"])){
                    $this->addError($attribute, "跟进时间不能为空");
                }
                if(empty($row["market_id"])){
                    $row["lcu"]=$uid;
                }
            }elseif($row["uflag"]=="D"){
                if(empty($row["market_id"])){
                    $row["lcu"]=$uid;
                }
            }
        }
    }

    public function computeCity($attribute, $params) {
	    $cityList = MarketFun::getMarketCityList();
	    if(!key_exists($this->city,$cityList['list'])){
            $this->city = null;
        }
    }

    protected function getMyAttr(){
        return $arr = array(
            "id"=>1,"number_no"=>1,"company_name"=>1,"city"=>1,"company_web"=>1,
            "city_name"=>1,"area"=>1,"legal_user"=>1,"company_size"=>1,"person_phone"=>1,
            "company_state"=>1,"sign_address"=>1,"company_date"=>2,"run_address"=>1,"company_type"=>1,
            "company_note"=>1,"allot_type"=>3,"allot_city"=>1,"allot_employee"=>3,
            "start_date"=>2,"end_date"=>2,"ready_bool"=>3,"reject_note"=>1,
            "status_type"=>3,"z_index"=>3,"back_note"=>1
        );
    }

	public function retrieveData($index){
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = " ";
        //$whereSql = " and a.status_type not in (8,10)";
		$sql = "select a.*,docman$suffix.countdoc('market',a.id) as marketcountdoc from sal_market a where a.id='{$index}' {$whereSql}";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
        $this->no_of_attm['market'] = $row['marketcountdoc'];
        $arr = $this->getMyAttr();
		if ($row!==false) {
			foreach ($arr as $key => $type){
			    switch ($type){
                    case 1://原值
                        $this->$key = $row[$key];
                        break;
                    case 2://日期
                        $this->$key = empty($row[$key])?null:General::toDate($row[$key]);
                        break;
                    case 3://数字
                        $this->$key = $row[$key]===null?null:floatval($row[$key]);
                        break;
                    default:
                }
            }

            $infoRows = Yii::app()->db->createCommand()
                ->select("a.id,a.market_id,a.lcu,a.info_date,a.info_text,a.state_id")
                ->from("sal_market_info a")
                ->where("a.market_id={$index} and a.del_bool=0")
                ->order("a.info_date asc")->queryAll();
            if($infoRows){
                $this->detail=array();
                foreach ($infoRows as $infoRow){
                    $temp = array();
                    $temp["id"] = $infoRow["id"];
                    $temp["market_id"] = $infoRow["market_id"];
                    $temp["state_id"] = $infoRow["state_id"];
                    $temp["lcu"] = $infoRow["lcu"];
                    $temp["info_date"] = General::toDate($infoRow["info_date"]);
                    $temp["info_text"] = $infoRow["info_text"];
                    $temp['uflag'] = 'N';
                    $this->detail[$temp["id"]] = $temp;
                }
            }

            $userRows = Yii::app()->db->createCommand()->select("a.*")
                ->from("sal_market_user a")
                ->where("a.market_id={$index} and a.del_bool=0")
                ->order("a.id asc")->queryAll();
            if($userRows){
                $this->userDetail=array();
                foreach ($userRows as $userRow){
                    $temp = array();
                    $temp["id"] = $userRow["id"];
                    $temp["market_id"] = $userRow["market_id"];
                    $temp["user_name"] = $userRow["user_name"];
                    $temp["user_dept"] = $userRow["user_dept"];
                    $temp["user_phone"] = $userRow["user_phone"];
                    $temp["user_email"] = $userRow["user_email"];
                    $temp["user_wechat"] = $userRow["user_wechat"];
                    $temp["user_text"] = $userRow["user_text"];
                    $temp['uflag'] = 'N';
                    $this->userDetail[$temp["id"]] = $temp;
                }
            }
            return true;
		}else{
		    return false;
        }
	}

	public function setModelData($index){
		$sql = "select a.* from sal_market a where a.id='{$index}'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
        $arr = $this->getMyAttr();
		if ($row!==false) {
			foreach ($arr as $key => $type){
			    switch ($type){
                    case 1://原值
                        $this->$key = $row[$key];
                        break;
                    case 2://日期
                        $this->$key = empty($row[$key])?null:General::toDate($row[$key]);
                        break;
                    case 3://数字
                        $this->$key = $row[$key]===null?null:floatval($row[$key]);
                        break;
                    default:
                }
            }
            return true;
		}else{
		    return false;
        }
	}

    public function setModelDataForRow($row){
        $arr = $this->getMyAttr();
        if ($row!==false) {
            foreach ($arr as $key => $type){
                switch ($type){
                    case 1://原值
                        $this->$key = $row[$key];
                        break;
                    case 2://日期
                        $this->$key = empty($row[$key])?null:General::toDate($row[$key]);
                        break;
                    case 3://数字
                        $this->$key = $row[$key]===null?null:floatval($row[$key]);
                        break;
                    default:
                }
            }
            return true;
        }else{
            return false;
        }
    }

	public function getUpdateJson(){
        $list = array();
        foreach (self::historyUpdateList() as $key){
            $list[$key] = $this->$key;
        }
        return json_encode($list);
    }

	public static function getMarketHistoryRows($market_id){
        $rows = Yii::app()->db->createCommand()->select("id,update_html,lcu,lcd")
            ->from("sal_market_history")
            ->where("market_id=:market_id",array(":market_id"=>$market_id))->order("lcd desc")->queryAll();
        return $rows;
    }
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
            $model = new MarketCompanyForm();
            $model->retrieveData($this->id);
			$this->save($connection);
            $this->historySave($connection,$model);
            $this->saveDetail($connection);
            $this->saveUserDetail($connection);
            $this->updateDocman($connection,'MARKET');
            if($this->getScenario()=="new"){
                $this->setScenario("edit");
            }
			$transaction->commit();
		}catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,$e->getMessage());
		}
	}

    protected function updateDocman(&$connection, $doctype) {
        if ($this->scenario=='new') {
            $docidx = strtolower($doctype);
            if ($this->docMasterId[$docidx] > 0) {
                $docman = new DocMan($doctype,$this->id,get_class($this));
                $docman->masterId = $this->docMasterId[$docidx];
                $docman->updateDocId($connection, $this->docMasterId[$docidx]);
            }
        }
    }

    //哪些字段修改后需要记录
    protected static function historyUpdateList(){
        return array("company_name","city_name","area","legal_user","company_web",
            "company_size","company_state","sign_address","company_date","run_address",
            "company_type","company_note"
        );
    }

    protected static function getNameForValue($type,$value){
        switch ($type){
            case "area":
                $value = MarketFun::getAreaNameToType($value);
                break;
            case "company_size":
                $value = MarketFun::getCompanySizeNameToType($value);
                break;
            case "company_state":
                $value = MarketFun::getCompanyStateNameToType($value);
                break;
            case "company_type":
                $value = MarketFun::getCompanyTypeNameToType($value);
                break;
            case "support_user":
                $value = MarketFun::getEmployeeNameForId($value);
                break;
        }
        return $value;
    }

	//保存历史记录
    protected function historySave(&$connection,$model){
        switch ($this->getScenario()){
            case "delete":
                $connection->createCommand()->delete("sal_market_history", "market_id=:id", array(":id" => $this->id));
                break;
            case "edit":
                $uid = Yii::app()->user->id;
                $keyArr = self::historyUpdateList();
                $list=array("market_id"=>$this->id,"lcu"=>$uid,"update_type"=>1,"update_html"=>array());
                foreach ($keyArr as $key){
                    if($model->$key!=$this->$key){
                        $list["update_html"][]="<span>".$this->getAttributeLabel($key)."：".self::getNameForValue($key,$model->$key)." 修改为 ".self::getNameForValue($key,$this->$key)."</span>";
                    }
                }
                $this->getHistoryDetail($list["update_html"],$model);
                $this->getHistoryUserDetail($list["update_html"],$model);
                if(!empty($list["update_html"])){
                    $list["update_html"] = implode("<br/>",$list["update_html"]);
                    $list["update_json"] = $this->getUpdateJson();
                    $connection->createCommand()->insert("sal_market_history", $list);
                }
                break;
            case "new"://新增
                $uid = Yii::app()->user->id;
                $list=array(
                    "market_id"=>$this->id,
                    "lcu"=>$uid,
                    "update_type"=>2,
                    "update_html"=>"<span>新增</span>",
                    "update_json"=>$this->getUpdateJson(),
                    "lcd"=>$this->start_date,
                );
                $connection->createCommand()->insert("sal_market_history", $list);
                break;
        }
    }

    protected function getHistoryDetail(&$list,$model){
        $followDate = empty($this->end_date)?0:strtotime($this->end_date);
        if(!empty($this->detail)){
            foreach ($this->detail as $row) {
                if(in_array($row['uflag'],array("N","Y"))&&strtotime($row['info_date'])>=$followDate){
                    $this->end_date = $row["info_date"];
                }
                switch ($row['uflag']){
                    case "Y"://修改
                        if(!empty($row['id'])){
                            if(key_exists($row["id"],$model->detail)){
                                $updateArr = array("state_id","info_date","info_text");
                                foreach ($updateArr as $item){
                                    if($model->detail[$row["id"]][$item]!=$row[$item]){
                                        $list[]="<span>".$this->getAttributeLabel($item)."({$row['id']})：".$model->detail[$row["id"]][$item]." 修改为 {$row[$item]}</span>";
                                    }
                                }
                            }else{
                                $list[]="<span>修改了跟进事项：id:{$row['id']} 时间:".$row['info_date']."</span>";
                            }
                        }
                        break;
                    case "D"://刪除
                        $list[]="<span>删除了跟进事项：id:{$row['id']} ".$row['info_date']."</span>";
                        break;
                }
            }
        }
        return $list;
    }

    protected function getHistoryUserDetail(&$list,$model){
        if(!empty($this->userDetail)){
            foreach ($this->userDetail as $row) {
                switch ($row['uflag']){
                    case "Y"://修改
                        if(!empty($row['id'])){
                            if(key_exists($row["id"],$model->userDetail)){
                                $updateArr = array("user_name","user_dept","user_phone","user_email","user_wechat","user_text");
                                foreach ($updateArr as $item){
                                    if($model->userDetail[$row["id"]][$item]!=$row[$item]){
                                        $list[]="<span>".$this->getAttributeLabel($item)."({$row["id"]})：".$model->userDetail[$row["id"]][$item]." 修改为 {$row[$item]}</span>";
                                    }
                                }
                            }else{
                                $list[]="<span>修改了联系人：".$row['user_name']."/{$row['user_phone']}</span>";
                            }
                        }
                        break;
                    case "D"://刪除
                        $list[]="<span>删除了联系人：".$row['user_name']."/{$row['user_phone']}</span>";
                        break;
                }
            }
        }
        return $list;
    }

    protected function saveUserDetail(&$connection)
    {
        $uid = Yii::app()->user->id;
        if(!empty($this->userDetail)){
            foreach ($this->userDetail as $row) {
                $sql = '';
                switch ($this->scenario) {
                    case 'delete':
                        $sql = "delete from sal_market_user where market_id = :market_id";
                        break;
                    case 'new':
                        if ($row['uflag']=='Y') {
                            $sql = "insert into sal_market_user(
									market_id, user_name, user_dept, user_phone, user_email, user_wechat, user_text,lcu
								) values (
									:market_id,:user_name,:user_dept,:user_phone,:user_email,:user_wechat,:user_text,:lcu
								)";
                        }
                        break;
                    case 'edit':
                        switch ($row['uflag']) {
                            case 'D':
                                $sql = "update sal_market_user set del_bool=1 where id = :id and lcu=:lcu";
                                break;
                            case 'Y':
                                $sql = ($row['id']==0)
                                    ?
                                    "insert into sal_market_user(
                                        market_id, user_name, user_dept, user_phone, user_email, user_wechat, user_text,lcu
                                    ) values (
                                        :market_id,:user_name,:user_dept,:user_phone,:user_email,:user_wechat,:user_text,:lcu
                                    )"
                                    :
                                    "update sal_market_user set
										user_name = :user_name, 
										user_dept = :user_dept, 
										user_phone = :user_phone,
										user_email = :user_email,
										user_wechat = :user_wechat,
										user_text = :user_text,
										luu = :luu 
									where id = :id
									";
                                break;
                        }
                        break;
                }

                if ($sql != '') {
//                print_r('<pre>');
//                print_r($sql);exit();
                    $command=$connection->createCommand($sql);
                    if (strpos($sql,':id')!==false)
                        $command->bindParam(':id',$row['id'],PDO::PARAM_INT);
                    if (strpos($sql,':market_id')!==false)
                        $command->bindParam(':market_id',$this->id,PDO::PARAM_INT);
                    if (strpos($sql,':user_name')!==false)
                        $command->bindParam(':user_name',$row['user_name'],PDO::PARAM_STR);
                    if (strpos($sql,':user_dept')!==false)
                        $command->bindParam(':user_dept',$row['user_dept'],PDO::PARAM_STR);
                    if (strpos($sql,':user_phone')!==false)
                        $command->bindParam(':user_phone',$row['user_phone'],PDO::PARAM_STR);
                    if (strpos($sql,':user_email')!==false)
                        $command->bindParam(':user_email',$row['user_email'],PDO::PARAM_STR);
                    if (strpos($sql,':user_wechat')!==false)
                        $command->bindParam(':user_wechat',$row['user_wechat'],PDO::PARAM_STR);
                    if (strpos($sql,':user_text')!==false)
                        $command->bindParam(':user_text',$row['user_text'],PDO::PARAM_STR);
                    if (strpos($sql,':luu')!==false)
                        $command->bindParam(':luu',$uid,PDO::PARAM_STR);
                    if (strpos($sql,':lcu')!==false)
                        $command->bindParam(':lcu',$uid,PDO::PARAM_STR);
                    $command->execute();
                }
            }
        }
        return true;
    }

    protected function saveDetail(&$connection)
    {
        $uid = Yii::app()->user->id;
        if(!empty($this->detail)){
            foreach ($this->detail as $row) {
                $sql = '';
                switch ($this->scenario) {
                    case 'delete':
                        $sql = "delete from sal_market_info where market_id = :market_id";
                        break;
                    case 'new':
                        if ($row['uflag']=='Y') {
                            $sql = "insert into sal_market_info(
									market_id, state_id, info_date, info_text,lcu
								) values (
									:market_id,:state_id,:info_date,:info_text,:lcu
								)";
                        }
                        break;
                    case 'edit':
                        switch ($row['uflag']) {
                            case 'D':
                                $sql = "update sal_market_info set del_bool=1 where id = :id and lcu=:lcu";
                                break;
                            case 'Y':
                                $sql = ($row['id']==0)
                                    ?
                                    "insert into sal_market_info(
									  market_id, state_id, info_date, info_text,lcu
									) values (
									  :market_id,:state_id,:info_date,:info_text,:lcu
									)"
                                    :
                                    "update sal_market_info set
										state_id = :state_id, 
										info_date = :info_date, 
										info_text = :info_text,
										luu = :luu 
									where id = :id and lcu=:lcu
									";
                                break;
                        }
                        break;
                }

                if ($sql != '') {
//                print_r('<pre>');
//                print_r($sql);exit();
                    $command=$connection->createCommand($sql);
                    if (strpos($sql,':id')!==false)
                        $command->bindParam(':id',$row['id'],PDO::PARAM_INT);
                    if (strpos($sql,':market_id')!==false)
                        $command->bindParam(':market_id',$this->id,PDO::PARAM_INT);
                    if (strpos($sql,':state_id')!==false)
                        $command->bindParam(':state_id',$row['state_id'],PDO::PARAM_INT);
                    if (strpos($sql,':info_date')!==false){
                        $row['info_date']=empty($row['info_date'])?null:$row['info_date'];
                        $command->bindParam(':info_date',$row['info_date'],PDO::PARAM_STR);
                    }
                    if (strpos($sql,':info_text')!==false)
                        $command->bindParam(':info_text',$row['info_text'],PDO::PARAM_STR);
                    if (strpos($sql,':luu')!==false)
                        $command->bindParam(':luu',$uid,PDO::PARAM_STR);
                    if (strpos($sql,':lcu')!==false)
                        $command->bindParam(':lcu',$uid,PDO::PARAM_STR);
                    $command->execute();
                }
            }
        }
        return true;
    }

	protected function save(&$connection)
	{
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
	    $list=array();
        $arr = array(
            "company_name"=>1,"city"=>1,
            "city_name"=>1,"area"=>1,"legal_user"=>1,"company_size"=>1,"person_phone"=>1,"company_web"=>1,
            "company_state"=>1,"sign_address"=>1,"company_date"=>2,"run_address"=>1,"company_type"=>1,
            "company_note"=>1
        );
        foreach ($arr as $key=>$type){
            $value=$this->$key;
            switch ($type){
                case 1://原值
                    break;
                case 2://日期
                    $value = empty($value)?null:General::toDate($value);
                    break;
                case 3://数字
                    $value = $value===""?null:floatval($value);
                    break;
            }
            $this->$key=$value;
            $list[$key] = $value;
        }
        $this->status_type = MarketFun::getStatusTypeForStateId($this->end_state_id,$this->status_type);
        $list["status_type"] = $this->status_type;
        switch ($this->scenario) {
            case 'delete':
                $connection->createCommand()->delete("sal_market", "id=:id", array(":id" => $this->id));
                break;
            case 'new':
                $this->start_date = date("Y/m/d H:i:s");
                $list["start_date"] = $this->start_date;
                $list["lcu"] = $uid;
                $connection->createCommand()->insert("sal_market", $list);
                break;
            case 'edit':
                $list["luu"] = $uid;
                $list["end_date"] = empty($this->end_date)?null:$this->end_date;
                $connection->createCommand()->update("sal_market", $list, "id=:id", array(":id" => $this->id));
                break;
        }

		if ($this->scenario=='new'){
            $this->id = Yii::app()->db->getLastInsertID();
            $this->lenStr();
            Yii::app()->db->createCommand()->update('sal_market', array(
                'number_no'=>$this->number_no
            ), 'id=:id', array(':id'=>$this->id));
        }
		return true;
	}

	public function validateReject(){
        $bool=true;
        $assign_id = key_exists("assign_id", $_POST) ? $_POST["assign_id"] : "";
        $rejectNote = key_exists("reject_note", $_POST) ? $_POST["reject_note"] : "";
        $typeNum = key_exists("type_num", $_POST) ? $_POST["type_num"] : "";
        if(empty($rejectNote)){
            $this->addError("reject_note", "拒绝原因不能为空");
            $bool = false;
        }
        $ids=array();
        $assign_list = explode(",",$assign_id);
        foreach ($assign_list as $id){
            $id = is_numeric($id)?$id:0;
            $this->setModelData($id);
            //未分配，或者被退回
            if(!empty($this->id)&&!in_array($this->status_type,array(8,10))){
                $ids[]=$this->getAttributes();
            }
        }
        if (count($ids)<=0) {
            $this->addError("city_name", "请选择客户资料!");
            $bool = false;
        }
        if(count($ids)>=200){
            $this->addError("city_name", "选择的数量不能大于200!");
            $bool = false;
        }
        $this->reject_note=$rejectNote;
        return array("bool"=>$bool,"typeNum"=>$typeNum,"data"=>array("list_id"=>$ids,"rejectNote"=>$rejectNote));
    }

	public function validateSuccess(){
        $bool = true;
        $assign_id = key_exists("assign_id", $_POST) ? $_POST["assign_id"] : "";
        $typeNum = key_exists("type_num", $_POST) ? $_POST["type_num"] : "";

        $ids=array();
        $assign_list = explode(",",$assign_id);
        foreach ($assign_list as $id){
            $id = is_numeric($id)?$id:0;
            $this->setModelData($id);
            //未分配，或者被退回
            if(!empty($this->id)&&!in_array($this->status_type,array(8,10))){
                $ids[]=$this->getAttributes();
            }
        }
        if (count($ids)<=0) {
            $this->addError("city_name", "请选择客户资料!");
            $bool = false;
        }
        if(count($ids)>=200){
            $this->addError("city_name", "选择的数量不能大于200!");
            $bool = false;
        }
        return array("bool"=>$bool,"typeNum"=>$typeNum,"data"=>array("list_id"=>$ids));
    }

    protected function saveSuccess(){
        $uid = Yii::app()->user->id;
        Yii::app()->db->createCommand()->update("sal_market", array(
            "luu"=>$uid,
            "status_type"=>10,
            "z_index"=>$this->getStaticIndex(),
            "ready_bool"=>0,//设置成未读
        ), "id=:id", array(":id" => $this->id));

        $update_html=array();
        $update_html[]="<span><b>已完成</b></span>";
        $update_html = implode("<br/>",$update_html);
        $backHisSQL = array(
            "market_id" => $this->id,
            "lcu" => $uid,
            "update_type" => 1,
            "update_html" => $update_html
        );
        Yii::app()->db->createCommand()->insert("sal_market_history", $backHisSQL);
    }

    public function saveSuccessAll($data){
        foreach ($data["list_id"] as $row){
            $this->id = $row["id"];
            $this->saveSuccess();
        }
        $this->sendEmail($data["list_id"],'success');
    }

    public function saveRejectAll($data){
        foreach ($data["list_id"] as $row){
            $this->id = $row["id"];
            $this->saveReject();
        }
        $this->sendEmail($data["list_id"],"reject");
    }

    protected function saveReject(){
        $uid = Yii::app()->user->id;
        Yii::app()->db->createCommand()->update("sal_market", array(
            "luu"=>$uid,
            "status_type"=>8,
            "z_index"=>$this->getStaticIndex(),
            "reject_note"=>$this->reject_note,
            "ready_bool"=>0,//设置成未读
        ), "id=:id", array(":id" => $this->id));

        $update_html=array();
        $update_html[]="<span><b>已拒绝</b></span>";
        $update_html[]="<span>拒绝原因：".$this->reject_note."</span>";
        $update_html = implode("<br/>",$update_html);
        $backHisSQL = array(
            "market_id" => $this->id,
            "lcu" => $uid,
            "update_type" => 1,
            "update_html" => $update_html
        );
        Yii::app()->db->createCommand()->insert("sal_market_history", $backHisSQL);
    }

    private function lenStr(){
        $code = strval($this->id);
        $this->number_no = "MK";
        for($i = 0;$i < 5-strlen($code);$i++){
            $this->number_no.="0";
        }
        $this->number_no .= $code;
    }

    public function isReadOnly(){
	    return $this->getScenario()=='view';
    }

    protected function sendEmail($rows,$type=""){
        switch ($type){
            case "success":
                $preFix="MT05";
                $action = "marketSuccess";
                $title="市场营销的客户资料已完成";
                $tableHeader="<p><b>以下客户资料已完成：</b></p>";
                break;
            case "reject":
                $preFix="MT04";
                $action = "marketReject";
                $title="市场营销的客户资料被拒绝";
                $tableHeader="<p><b>以下客户资料被拒绝：</b></p>";
                break;
            case "assign":
                $preFix="MT01";
                $action = "marketCompany";
                $title="您有新的市场营销的需要跟进";
                $tableHeader="<p><b>以下客户资料需要跟进：</b></p>";
                break;
            case "back":
                $preFix="MT01";
                $action = "marketCompany";
                $title="市场营销的客户资料被退回";
                $tableHeader="<p><b>以下客户资料被退回：</b></p>";
                break;
            default:
                return;
        }
        $emailModel = new Email($title,'',$title);
        $cityEmailList=array();
        $salesEmailList=array();
        $topEmailList="";
        foreach ($rows as $row){
            $city = $row["allot_city"];
            $employee_id = "".$row["allot_employee"];
            $trTemp = $this->getTrHtmlForRow($row);
            if($this->emailTop){
                $topEmailList.=str_replace("{:Action:}",$action,$trTemp);
            }
            if($this->emailCity&&!empty($city)){
                if(!key_exists($city,$cityEmailList)){
                    $cityEmailList[$city]="";
                }
                $cityEmailList[$city].=str_replace("{:Action:}","marketArea",$trTemp);
            }
            if($this->emailSales&&!empty($employee_id)){
                if(!key_exists($employee_id,$salesEmailList)){
                    $salesEmailList[$employee_id]="";
                }
                $salesEmailList[$employee_id].=str_replace("{:Action:}","marketSales",$trTemp);
            }
        }
        $tableHeader.=$this->getEmailTableForHeader();
        foreach ($cityEmailList as $city=>$cityHtml){ //发送给分配的城市
            $message = $tableHeader."<tbody>".$cityHtml."</tbody></table>";
            $emailModel->resetToAddr();
            $emailModel->setMessage($message);
            $emailModel->addEmailToPrefixAndCity("MT02",$city);
            $emailModel->sent();
        }
        foreach ($salesEmailList as $employee_id=>$salesHtml){ //发送给分配的销售
            $message = $tableHeader."<tbody>".$salesHtml."</tbody></table>";
            $emailModel->resetToAddr();
            $emailModel->setMessage($message);
            $emailModel->addEmailToStaffId($employee_id);
            $emailModel->sent();
        }
        if(!empty($topEmailList)){//发送给资料管理人
            $message = $tableHeader."<tbody>".$topEmailList."</tbody></table>";
            $emailModel->resetToAddr();
            $emailModel->setMessage($message);
            $emailModel->addEmailToPrefixNullCity($preFix);
            $emailModel->sent();
        }
    }

    protected function sendEmailForSystem($rows,$type=""){
        $systemId = Yii::app()->params['systemId'];
        switch ($type){
            case "systemArea"://系统退回（地区）
                $preFix="MT01";
                $action = "marketCompany";
                $title="市场营销超过15天已自动退回（地区）";
                $tableHeader="<p><b>以下客户资料超过15天已自动退回：</b></p>";
                break;
            case "systemSales"://系统退回（销售）
                $preFix="MT01";
                $action = "marketCompany";
                $title="市场营销超过15天已自动退回（销售）";
                $tableHeader="<p><b>以下客户资料超过15天已自动退回：</b></p>";
                break;
            case "systemKASales"://系统退回（KA销售）
                $preFix="MT01";
                $action = "marketCompany";
                $title="市场营销超过15天已自动退回（KA销售）";
                $tableHeader="<p><b>以下客户资料超过15天已自动退回（KA销售）：</b></p>";
                break;
            default:
                return;
        }
        $emailModel = new Email($title,'',$title);
        $cityEmailList=array();
        $salesEmailList=array();
        $topEmailList="";
        foreach ($rows as $row){
            $city = $row["allot_city"];
            $employee_id = "".$row["allot_employee"];
            $trTemp = $this->getTrHtmlForRowAndSystem($row);
            if($this->emailTop){
                $topEmailList.=str_replace("{:Action:}",$action,$trTemp);
            }
            if($this->emailCity&&!empty($city)){
                if(!key_exists($city,$cityEmailList)){
                    $cityEmailList[$city]="";
                }
                $cityEmailList[$city].=str_replace("{:Action:}","marketArea",$trTemp);
            }
            if($this->emailSales&&!empty($employee_id)){
                if(!key_exists($employee_id,$salesEmailList)){
                    $salesEmailList[$employee_id]="";
                }
                $salesEmailList[$employee_id].=str_replace("{:Action:}","marketSales",$trTemp);
            }
        }
        $tableHeader.=$this->getEmailTableForHeader();
        foreach ($cityEmailList as $city=>$cityHtml){ //发送给分配的城市
            $message = $tableHeader."<tbody>".$cityHtml."</tbody></table>";
            $emailModel->resetToAddr();
            $emailModel->setMessage($message);
            $emailModel->addEmailToPrefixAndCity("MT02",$city);
            $emailModel->sent("admin",$systemId);
        }
        foreach ($salesEmailList as $employee_id=>$salesHtml){ //发送给分配的销售
            $message = $tableHeader."<tbody>".$salesHtml."</tbody></table>";
            $emailModel->resetToAddr();
            $emailModel->setMessage($message);
            $emailModel->addEmailToStaffId($employee_id);
            $emailModel->sent("admin",$systemId);
        }
        if(!empty($topEmailList)){//发送给资料管理人
            $message = $tableHeader."<tbody>".$topEmailList."</tbody></table>";
            $emailModel->resetToAddr();
            $emailModel->setMessage($message);
            $emailModel->addEmailToPrefixNullCity($preFix);
            $emailModel->sent("admin",$systemId);
        }
    }

    protected function getEmailTableForHeader(){
        $html = "";
        $html.= '<table border="1" cellpadding="0" cellspacing="0">';
        $html.= '<thead><tr>';
        $html.='<th>编号</th>';
        $html.='<th>企业名称</th>';
        $html.='<th>分配城市</th>';
        $html.='<th>分配员工</th>';
        $html.='<th>建档日期</th>';
        $html.='<th>最后跟进日期</th>';
        $html.='<th>&nbsp;</th>';
        $html.='</tr></thead>';
        return $html;
    }

    protected function getTrHtmlForRow($row,$action=""){
        $action = empty($action)?'{:Action:}':$action;
        $html="";
        $url = "index.php/{$action}/edit?index=".$row['id'];
        $url = Yii::app()->getBaseUrl(true)."/".$url;
        $html.= '<tr>';
        $html.='<td>'.$row['number_no'].'</td>';
        $html.='<td>'.$row['company_name'].'</td>';
        $html.='<td>'.General::getCityName($row['allot_city']).'</td>';
        $html.='<td>'.MarketFun::getEmployeeNameForId($row['allot_employee']).'</td>';
        $html.='<td>'.$row['start_date'].'</td>';
        $html.='<td>'.$row['end_date'].'</td>';
        $html.="<td><a target='_blank' href='{$url}'>查看</a></td>";
        $html.='</tr>';
        return $html;
    }

    protected function getTrHtmlForRowAndSystem($row,$action=""){
        $action = empty($action)?'{:Action:}':$action;
        $html="";
        $url = Yii::app()->params['webroot'];
        $url.="/{$action}/edit?index=".$row['id'];
        $html.= '<tr>';
        $html.='<td>'.$row['number_no'].'</td>';
        $html.='<td>'.$row['company_name'].'</td>';
        $html.='<td>'.General::getCityName($row['allot_city']).'</td>';
        $html.='<td>'.MarketFun::getEmployeeNameForId($row['allot_employee']).'</td>';
        $html.='<td>'.$row['start_date'].'</td>';
        $html.='<td>'.$row['end_date'].'</td>';
        $html.="<td><a target='_blank' href='{$url}'>查看</a></td>";
        $html.='</tr>';
        return $html;
    }

    public function getStaticIndex(){
        return 0;
    }
}
