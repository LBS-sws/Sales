<?php

class KABotForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $apply_date;
	public $follow_date;
	public $customer_no;
	public $customer_name;
	public $kam_id;
	public $head_city_id;
	public $talk_city_id;
	public $contact_user;
	public $contact_phone;
	public $contact_email;
	public $contact_dept;
	public $source_text;
	public $source_id;
	public $area_id;
	public $level_id;
	public $class_id;
	public $busine_id;
	public $link_id;
	public $year_amt;
	public $quarter_amt;
	public $month_amt;
	public $sign_date;
	public $sign_month;
	public $sign_amt;
	public $sum_amt;
	public $available_date;
	public $available_amt;
	public $remark;
	public $support_user;
	public $sign_odds;
	public $city;

	public $status_type;
    public $reject_id;

    //2024-1-25 年新增字段
    public $ava_show_date;//可成交日期，列表需要
    public $contact_adr;//联系人地址
    public $work_user;//业务联系人
    public $work_phone;//业务联系人电话
    public $work_email;//业务联系人邮箱
    public $class_other;//当客户类别为其它时

    public $employee_id;
    public $employee_code;
    public $employee_name;
    public $espe_type=0;//修改重要数据时，改成1

    public $detail = array(
        array('id'=>0,
            'bot_id'=>0,
            'info_date'=>'',
            'info_text'=>'',
            'uflag'=>'N',
        ),
    );

    public $avaInfo = array(
        array('id'=>0,
            'bot_id'=>0,
            'ava_date'=>'',//可成交日期
            'ava_amt'=>'',//可成交金额
            'ava_num'=>'',//门店数量
            'ava_city'=>'',//城市
            'ava_rate'=>'',//签约概率
            'ava_note'=>'',//備註
            'ava_fact_amt'=>'',//实际成交金额
            'uflag'=>'N',
        ),
    );

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'apply_date'=>Yii::t('ka','apply date'),
            'customer_no'=>Yii::t('ka','customer no'),
            'customer_name'=>Yii::t('ka','customer name'),
            'contact_user'=>Yii::t('ka','contact user'),
            'source_id'=>Yii::t('ka','source name'),
            'class_id'=>Yii::t('ka','class name'),
            'kam_id'=>Yii::t('ka','KAM'),
            'link_id'=>Yii::t('ka','link name'),
            'available_date'=>Yii::t('ka','available date'),
            'available_amt'=>Yii::t('ka','available amt'),

            'head_city_id'=>Yii::t('ka','head city'),
            'talk_city_id'=>Yii::t('ka','talk city'),
            'area_id'=>Yii::t('ka','area city'),
            'contact_phone'=>Yii::t('ka','contact phone'),
            'contact_email'=>Yii::t('ka','contact email'),
            'contact_dept'=>Yii::t('ka','contact dept'),
            'source_text'=>Yii::t('ka','source name(A)'),
            'level_id'=>Yii::t('ka','level name'),
            'busine_id'=>Yii::t('ka','busine name'),
            'month_amt'=>Yii::t('ka','month amt'),
            'quarter_amt'=>Yii::t('ka','quarter amt'),
            'year_amt'=>Yii::t('ka','year amt'),
            'sign_date'=>Yii::t('ka','sign date'),
            'sign_month'=>Yii::t('ka','sign month'),
            'sign_amt'=>Yii::t('ka','sign amt'),
            'sum_amt'=>Yii::t('ka','sum amt'),
            'support_user'=>Yii::t('ka','support user'),
            'sign_odds'=>Yii::t('ka','sign odds'),
            'remark'=>Yii::t('ka','remark'),
            'info_date'=>Yii::t('ka','info date'),
            'info_text'=>Yii::t('ka','info text'),

            'contact_adr'=>Yii::t('ka','contact address'),
            'work_user'=>Yii::t('ka','work user'),
            'work_phone'=>Yii::t('ka','work phone'),
            'work_email'=>Yii::t('ka','work email'),
            'class_other'=>Yii::t('ka','class name'),
            'ava_date'=>Yii::t('ka','ava date'),
            'ava_amt'=>Yii::t('ka','ava amt'),
            'ava_rate'=>Yii::t('ka','ava rate'),
            'ava_num'=>Yii::t('ka','ava num'),
            'ava_city'=>Yii::t('ka','ava city'),
            'ava_note'=>Yii::t('ka','ava note'),
            'ava_fact_amt'=>Yii::t('ka','ava fact amt'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,apply_date,customer_no,customer_name,kam_id,head_city_id,talk_city_id,
                contact_user,contact_phone,contact_email,source_text,source_id,
                area_id,level_id,class_id,busine_id,link_id,support_user,sign_odds,city,
                available_date,available_amt,avaInfo,
                contact_adr,work_user,work_phone,work_email,class_other,
                sign_date,sign_month,sign_amt,sum_amt,remark','safe'),
            array('apply_date,work_user,contact_adr,available_date,customer_name,kam_id,link_id','required'),
            array('apply_date','validateDate'),
            array('link_id','validateLinkID'),
            array('sign_amt','computeSignAmt'),
		);
	}

    public function validateDate($attribute, $params) {
	    if(!empty($this->apply_date)&&!empty($this->available_date)){
	        $minDate = strtotime($this->apply_date);
	        $maxDate = strtotime($this->available_date);
	        if($maxDate<$minDate){
                $this->addError($attribute, "可成交日期不能小于录入日期");
            }
        }
    }

    public function validateLinkID($attribute, $params) {
	    if(empty($this->link_id)){
	        return false;
        }
	    $model = new KALinkForm();
	    $model->retrieveData($this->link_id);
	    if(empty($model->id)){
            $this->addError($attribute, "沟通阶段不存在，请刷新重试");
            return false;
        }
        $list = array();
        $emptyList = array();
        $avaDateList = array();
        $avaDateBool = false;//判断月份是否重复
        if(!empty($this->avaInfo)){
            foreach ($this->avaInfo as $row){
                if(!empty($row["ava_date"])){
                    $list[]=$row;
                    if($row["uflag"]!="D"){
                        $emptyList[]=$row;
                        if(!$avaDateBool&&in_array($row["ava_date"],$avaDateList)){
                            $avaDateBool = true;
                        }
                        $avaDateList[]=$row["ava_date"];
                    }
                }
            }
        }
        //$this->avaInfo = $list;
	    if($model->rate_num==100){
	        if(empty($this->sign_date)){
                $this->addError($attribute, "合同签约日期不能为空");
            }
	        if(empty($this->sign_month)){
                $this->addError($attribute, "合同周期(年)不能为空");
            }
	        if(empty($emptyList)){
                $this->addError($attribute, "签约详情不能为空");
            }elseif(!isset($emptyList[0]["ava_rate"])||$emptyList[0]["ava_rate"]<=80){
                $this->addError($attribute, "签约详情第一条的签约概率必须大于80");
            }
            if($avaDateBool){
                $this->addError($attribute, "签约详情的月份不能重复");
            }
            $this->sign_odds=100;
        }else{
	        $this->sign_date=null;
	        $this->sign_month=null;
	        if(is_numeric($this->sign_odds)&&$this->sign_odds==100){
                $this->sign_odds=null;
            }
        }
    }

	public function computeSignAmt($attribute, $params){
        $this->sum_amt = 0;
        $this->sum_amt+=empty($this->sign_amt)?0:$this->sign_amt;
        if(isset($_POST['KABotForm']['avaInfo'])) {
            foreach ($_POST['KABotForm']['avaInfo'] as $row) {
                if(empty($row["ava_date"])){
                    continue;
                }
                if(isset($row["uflag"])&&$row["uflag"]!="D"){
                    $this->sum_amt+=!empty($row["ava_fact_amt"])?$row["ava_fact_amt"]:0;
                }
            }
        }
        $this->follow_date = $this->apply_date;
    }

	public function retrieveData($index){
		$city = Yii::app()->user->city();
        $suffix = Yii::app()->params['envSuffix'];
        $city_allow = Yii::app()->user->city_allow();
        if(Yii::app()->user->validFunction('CN15')){
            //$whereSql = " and (a.kam_id='{$this->employee_id}' or a.support_user='{$this->employee_id}' or h.city in ({$city_allow}))";
            $whereSql = "";//2023/06/16 改為可以看的所有記錄
        }else{
            $whereSql = " and (a.kam_id='{$this->employee_id}' or a.support_user='{$this->employee_id}')";
        }
		$sql = "select a.* from sal_ka_bot a left join hr{$suffix}.hr_employee h ON a.kam_id=h.id where a.id=".$index." {$whereSql}";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
        //contact_adr,work_user,work_phone,work_email,class_other

        $arr = array(
            "id"=>1,"apply_date"=>2,"available_date"=>2,"customer_no"=>1,"customer_name"=>1,"kam_id"=>1,
            "head_city_id"=>1,"talk_city_id"=>1,"contact_user"=>1,"contact_phone"=>1,
            "contact_email"=>1,"source_text"=>1,"source_id"=>1,
            "area_id"=>1,"level_id"=>1,"class_id"=>1,"busine_id"=>4,"link_id"=>1,
            "support_user"=>3,"sign_odds"=>1,"city"=>1,"remark"=>1,"available_amt"=>3,
            "sign_date"=>2,"sign_month"=>1,"sign_amt"=>3,"sum_amt"=>3,
            "contact_adr"=>1,
            "work_user"=>1,"work_phone"=>1,"work_email"=>1,"class_other"=>1,
        );
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
                    case 4://数组
                        if($row[$key]===null){
                            $this->$key=null;
                        }elseif (is_numeric($row[$key])){//老版只能单选，需要兼容
                            $this->$key=array($row[$key]);
                        }else{
                            $this->$key=json_decode($row[$key],true);
                        }
                        break;
                    default:
                }
            }
            $this->kam_id = self::getEmployeeNameForId($this->kam_id);
            $sql = "select * from sal_ka_bot_info where bot_id=".$index." ";
            $infoRows = Yii::app()->db->createCommand($sql)->queryAll();
            if($infoRows){
                $this->detail=array();
                foreach ($infoRows as $infoRow){
                    $temp = array();
                    $temp["id"] = $infoRow["id"];
                    $temp["bot_id"] = $infoRow["bot_id"];
                    $temp["info_date"] = General::toDate($infoRow["info_date"]);
                    $temp["info_text"] = $infoRow["info_text"];
                    $temp['uflag'] = 'N';
                    $this->detail[] = $temp;
                }
            }
            $sql = "select * from sal_ka_bot_ava where bot_id=".$index." ";
            $avaRows = Yii::app()->db->createCommand($sql)->queryAll();
            if($avaRows){
                $this->avaInfo=array();
                foreach ($avaRows as $avaRow){
                    $temp = array();
                    $temp["id"] = $avaRow["id"];
                    $temp["bot_id"] = $avaRow["bot_id"];
                    $temp["ava_date"] = date("Y/m",strtotime($avaRow["ava_date"]));
                    $temp["ava_amt"] = $avaRow["ava_amt"];
                    $temp["ava_num"] = $avaRow["ava_num"];
                    $temp["ava_city"] = $avaRow["ava_city"];
                    $temp["ava_rate"] = $avaRow["ava_rate"];
                    $temp["ava_note"] = $avaRow["ava_note"];
                    $temp["ava_fact_amt"] = !empty($avaRow["ava_fact_amt"])?floatval($avaRow["ava_fact_amt"]):null;
                    $temp['uflag'] = 'N';
                    $this->avaInfo[] = $temp;
                }
            }
            return true;
		}else{
		    return false;
        }
	}

	public function setModelData($index){
		$sql = "select a.* from sal_ka_bot a where a.id=".$index."";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
        $arr = array(
            "id"=>1,"apply_date"=>2,"customer_no"=>1,"customer_name"=>1,"kam_id"=>1,
            "head_city_id"=>1,"talk_city_id"=>1,"contact_user"=>1,"contact_phone"=>1,
            "contact_email"=>1,"source_text"=>1,"source_id"=>1,
            "area_id"=>1,"level_id"=>1,"class_id"=>1,"busine_id"=>4,"link_id"=>1,
            "support_user"=>3,"sign_odds"=>1,"city"=>1,"remark"=>1,
            "available_amt"=>3,"available_date"=>2,"sign_date"=>2,"sign_month"=>1,"sign_amt"=>3,"sum_amt"=>3,
            "contact_adr"=>1,
            "work_user"=>1,"work_phone"=>1,"work_email"=>1,"class_other"=>1,
        );
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
                    case 4://数组
                        if($row[$key]===null){
                            $this->$key=null;
                        }elseif (is_numeric($row[$key])){//老版只能单选，需要兼容
                            $this->$key=array($row[$key]);
                        }else{
                            $this->$key=json_decode($row[$key],true);
                        }
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

	public static function getBotHistoryRows($bot_id){
        $rows = Yii::app()->db->createCommand()->select("id,update_html,lcu,lcd")
            ->from("sal_ka_bot_history")
            ->where("bot_id=:bot_id",array(":bot_id"=>$bot_id))->order("lcd desc")->queryAll();
        return $rows;
    }
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
            $this->historySave($connection);
			$this->save($connection);
            $this->saveDetail($connection);
            $this->saveAvaInfo($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,$e->getMessage());
		}
	}

    //哪些字段修改后需要记录
    private static function historyUpdateList(){
        return array("apply_date","head_city_id","talk_city_id","contact_user",
            "contact_phone","contact_email","source_text","source_id","area_id",
            "level_id","class_id","busine_id","link_id","available_amt","available_date","support_user","sign_odds",
            "sign_date","sign_month","sign_amt","sum_amt",
            "contact_adr",
            "work_user","work_phone","work_email","class_other"
        );
    }

    private static function getNameForValue($type,$value){
        switch ($type){
            case "head_city_id":
            case "talk_city_id":
            case "area_id":
                $value = KAAreaForm::getAreaNameForId($value);
                break;
            case "source_id":
                $value = KASourceForm::getSourceNameForId($value);
                break;
            case "level_id":
                $value = KALevelForm::getLevelNameForId($value);
                break;
            case "class_id":
                $value = KAClassForm::getClassNameForId($value);
                break;
            case "busine_id":
                $value = KABusineForm::getBusineNameForArr($value);
                break;
            case "link_id":
                $value = KALinkForm::getLinkNameForId($value);
                break;
            case "sign_odds":
                $value = KABotForm::getSignOddsListForId($value,true);
                break;
            case "support_user":
                $value = KABotForm::getEmployeeNameForId($value);
                break;
        }
        return $value;
    }

	//保存历史记录
    protected function historySave(&$connection){
        switch ($this->getScenario()){
            case "delete":
                $connection->createCommand()->delete("sal_ka_bot_history", "bot_id=:id", array(":id" => $this->id));
                break;
            case "edit":
                $uid = Yii::app()->user->id;
                $model = new KABotForm();
                $model->employee_id = $this->employee_id;
                $model->retrieveData($this->id);
                $keyArr = self::historyUpdateList();
                $list=array("bot_id"=>$this->id,"lcu"=>$uid,"update_type"=>1,"update_html"=>array());
                foreach ($keyArr as $key){
                    if($model->$key!=$this->$key){
                        if(in_array($key,array("sum_amt","sign_odds"))){
                            $this->espe_type = 1;
                        }
                        $list["update_html"][]="<span>".$this->getAttributeLabel($key)."：".self::getNameForValue($key,$model->$key)." 修改为 ".self::getNameForValue($key,$this->$key)."</span>";
                    }
                }
                $this->getHistoryDetail($list["update_html"]);
                $this->getHistoryAvaInfo($list["update_html"]);
                if(!empty($list["update_html"])){
                    $list["update_html"] = implode("<br/>",$list["update_html"]);
                    $list["espe_type"] = $this->espe_type;
                    $list["sum_amt"] = empty($this->sum_amt)?0:$this->sum_amt;
                    $list["sign_odds"] = empty($this->sign_odds)?0:$this->sign_odds;
                    $list["update_json"] = $this->getUpdateJson();
                    $connection->createCommand()->insert("sal_ka_bot_history", $list);
                }
                break;
        }
    }

    private function getHistoryDetail(&$list){
        $followDate = empty($this->follow_date)?0:$this->follow_date;
        if(isset($_POST['KABotForm']['detail'])){
            foreach ($_POST['KABotForm']['detail'] as $row) {
                if(in_array($row['uflag'],array("N","Y"))&&strtotime($row['info_date'])>=strtotime($followDate)){
                    $followDate = $row["info_date"];
                    $this->follow_date = $followDate;
                }
                switch ($row['uflag']){
                    case "Y"://修改
                        if(!empty($row['id'])){
                            $list[]="<span>修改了跟进事项：".$row['info_date']."</span>";
                        }
                        break;
                    case "D"://刪除
                        $list[]="<span>删除了跟进事项：".$row['info_date']."</span>";
                        break;
                }
            }
        }
        return $list;
    }

    protected function saveDetail(&$connection)
    {
        $uid = Yii::app()->user->id;
        if(isset($_POST['KABotForm']['detail'])){
            foreach ($_POST['KABotForm']['detail'] as $row) {
                $sql = '';
                switch ($this->scenario) {
                    case 'delete':
                        $sql = "delete from sal_ka_bot_info where bot_id = :bot_id";
                        break;
                    case 'new':
                        if ($row['uflag']=='Y') {
                            $sql = "insert into sal_ka_bot_info(
									bot_id, info_date, info_text,lcu
								) values (
									:bot_id,:info_date,:info_text,:lcu
								)";
                        }
                        break;
                    case 'edit':
                        switch ($row['uflag']) {
                            case 'D':
                                $sql = "delete from sal_ka_bot_info where id = :id";
                                break;
                            case 'Y':
                                $sql = ($row['id']==0)
                                    ?
                                    "insert into sal_ka_bot_info(
									  bot_id, info_date, info_text,lcu
									) values (
									  :bot_id,:info_date,:info_text,:lcu
									)"
                                    :
                                    "update sal_ka_bot_info set
										info_date = :info_date, 
										info_text = :info_text,
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
                    if (strpos($sql,':bot_id')!==false)
                        $command->bindParam(':bot_id',$this->id,PDO::PARAM_INT);
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

    private function getHistoryAvaInfo(&$list){
        $maxDate = $this->available_date;
        if(isset($_POST['KABotForm']['avaInfo'])){
            foreach ($_POST['KABotForm']['avaInfo'] as $row) {
                if(empty($row['ava_date'])){
                    continue;
                }
                $row['ava_date']=str_replace("-","/",$row['ava_date']);
                $row['ava_date'] = explode("/",$row['ava_date']);
                if(count($row['ava_date'])==2){
                    $row['ava_date'][]="01";
                }
                $row['ava_date']=implode("/",$row['ava_date']);
                if(in_array($row['uflag'],array("N","Y"))&&strtotime($row['ava_date'])>=strtotime($maxDate)){
                    $maxDate = $row["ava_date"];
                }
                switch ($row['uflag']){
                    case "Y"://修改
                        if(!empty($row['id'])){
                            $list[]="<span>修改了可成交列表：".$row['ava_date']."</span>";
                        }
                        break;
                    case "D"://刪除
                        $list[]="<span>删除了可成交列表：".$row['ava_date']."</span>";
                        break;
                }
            }
        }
        $this->ava_show_date = $maxDate;
        return $list;
    }

    protected function saveAvaInfo(&$connection)
    {
        $uid = Yii::app()->user->id;
        if(isset($_POST['KABotForm']['avaInfo'])){
            foreach ($_POST['KABotForm']['avaInfo'] as $row) {
                if(empty($row["ava_date"])){
                    continue;
                }
                $sql = '';
                switch ($this->scenario) {
                    case 'delete':
                        $sql = "delete from sal_ka_bot_ava where bot_id = :bot_id";
                        break;
                    case 'new':
                        if ($row['uflag']=='Y') {
                            $sql = "insert into sal_ka_bot_ava(
									bot_id, ava_date, ava_amt, ava_num, ava_city, ava_rate, ava_note, ava_fact_amt,lcu
								) values (
									:bot_id,:ava_date,:ava_amt,:ava_num,:ava_city,:ava_rate,:ava_note,:ava_fact_amt,:lcu
								)";
                        }
                        break;
                    case 'edit':
                        switch ($row['uflag']) {
                            case 'D':
                                $sql = "delete from sal_ka_bot_ava where id = :id";
                                break;
                            case 'Y':
                                $sql = ($row['id']==0)
                                    ?
                                    "insert into sal_ka_bot_ava(
                                        bot_id, ava_date, ava_amt, ava_num, ava_city, ava_rate, ava_note,ava_fact_amt,lcu
                                    ) values (
                                        :bot_id,:ava_date,:ava_amt,:ava_num,:ava_city,:ava_rate,:ava_note,:ava_fact_amt,:lcu
									)"
                                    :
                                    "update sal_ka_bot_ava set
										ava_date = :ava_date, 
										ava_amt = :ava_amt,
										ava_rate = :ava_rate,
										ava_note = :ava_note,
										ava_num = :ava_num,
										ava_city = :ava_city,
										ava_fact_amt = :ava_fact_amt,
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
                    if (strpos($sql,':bot_id')!==false)
                        $command->bindParam(':bot_id',$this->id,PDO::PARAM_INT);
                    if (strpos($sql,':ava_date')!==false){
                        if(empty($row['ava_date'])){
                            $row['ava_date']=null;
                        }else{
                            $row['ava_date']=str_replace("-","/",$row['ava_date']);
                            $row['ava_date'] = explode("/",$row['ava_date']);
                            if(count($row['ava_date'])==2){
                                $row['ava_date'][]="01";
                            }
                            $row['ava_date']=implode("/",$row['ava_date']);
                        }
                        $command->bindParam(':ava_date',$row['ava_date'],PDO::PARAM_STR);
                    }
                    if (strpos($sql,':ava_amt')!==false){
                        $row['ava_amt']=empty($row['ava_amt'])?null:$row['ava_amt'];
                        $command->bindParam(':ava_amt',$row['ava_amt'],PDO::PARAM_STR);
                    }
                    if (strpos($sql,':ava_rate')!==false){
                        $row['ava_rate']=empty($row['ava_rate'])?0:$row['ava_rate'];
                        $command->bindParam(':ava_rate',$row['ava_rate'],PDO::PARAM_STR);
                    }
                    if (strpos($sql,':ava_num')!==false){
                        $row['ava_num']=empty($row['ava_num'])?null:$row['ava_num'];
                        $command->bindParam(':ava_num',$row['ava_num'],PDO::PARAM_STR);
                    }
                    if (strpos($sql,':ava_city')!==false){
                        $row['ava_city']=empty($row['ava_city'])?null:$row['ava_city'];
                        $command->bindParam(':ava_city',$row['ava_city'],PDO::PARAM_STR);
                    }
                    if (strpos($sql,':ava_fact_amt')!==false){
                        $row['ava_fact_amt']=empty($row['ava_fact_amt'])?null:$row['ava_fact_amt'];
                        $command->bindParam(':ava_fact_amt',$row['ava_fact_amt'],PDO::PARAM_STR);
                    }
                    if (strpos($sql,':ava_note')!==false)
                        $command->bindParam(':ava_note',$row['ava_note'],PDO::PARAM_STR);
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
        $busine_name = KABusineForm::getBusineNameForArr($this->busine_id);
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
	    $list=array();
        $arr = array(
            "apply_date"=>2,"follow_date"=>2,"customer_name"=>1,
            "head_city_id"=>3,"talk_city_id"=>3,"contact_user"=>1,"contact_phone"=>1,
            "contact_email"=>1,"source_text"=>1,"source_id"=>3,
            "area_id"=>3,"level_id"=>3,"class_id"=>3,"busine_id"=>4,"link_id"=>3,
            "support_user"=>3,"sign_odds"=>3,"remark"=>1,
            "available_amt"=>3,"available_date"=>2,"sign_date"=>2,"sign_month"=>3,"sign_amt"=>3,"sum_amt"=>3,

            "contact_adr"=>1,"ava_show_date"=>1,
            "work_user"=>1,"work_phone"=>1,"work_email"=>1,"class_other"=>1,
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
                case 4://数字
                    $value = $value===""?null:json_encode($value);
                    break;
            }
            $this->$key=$value;
            $list[$key] = $value;
        }
        switch ($this->scenario) {
            case 'delete':
                $connection->createCommand()->delete("sal_ka_bot", "id=:id", array(":id" => $this->id));
                break;
            case 'new':
                $list["busine_name"] = $busine_name;
                $list["kam_id"] = $this->employee_id;
                $list["city"] = $city;
                $list["lcu"] = $uid;
                $connection->createCommand()->insert("sal_ka_bot", $list);
                break;
            case 'edit':
                unset($list["apply_date"]);
                //unset($list["customer_name"]);
                unset($list["kam_id"]);
                $list["busine_name"] = $busine_name;
                $list["luu"] = $uid;
                $connection->createCommand()->update("sal_ka_bot", $list, "id=:id", array(":id" => $this->id));
                break;
        }

		if ($this->scenario=='new'){
            $this->id = Yii::app()->db->getLastInsertID();
            $this->lenStr();
            Yii::app()->db->createCommand()->update('sal_ka_bot', array(
                'customer_no'=>$this->customer_no
            ), 'id=:id', array(':id'=>$this->id));

            //新增也需要记录历史
            $list=array(
                "bot_id"=>$this->id,
                "lcu"=>$uid,
                "update_type"=>2,
                "update_html"=>"<span>新增</span>",
                "update_json"=>$this->getUpdateJson(),
                "espe_type"=>1,
                "sum_amt"=>empty($this->sum_amt)?0:$this->sum_amt,
                "sign_odds"=>empty($this->sign_odds)?null:$this->sign_odds,
                "lcd"=>$this->apply_date,
            );
            if(strtotime($this->apply_date)!=strtotime(date("Y/m/d"))){
                $list["update_html"].="<br/><span>保存日期:".date("Y/m/d H:i:s")."</span>";
            }
            $connection->createCommand()->insert("sal_ka_bot_history", $list);
        }
		return true;
	}

    private function lenStr(){
        $code = strval($this->id);
        $this->customer_no = "LBSKA";
        for($i = 0;$i < 5-strlen($code);$i++){
            $this->customer_no.="0";
        }
        $this->customer_no .= $code;
    }

	public function isOccupied(){
	    return false;
    }

	public static function getSignOddsListForId($id="",$bool=false){
	    $list = array(
	        ""=>"",
            0=>"0%"."（".Yii::t("ka","reject")."）",
            40=>"<50%",
            50=>"50%",
            60=>"51~80%",
            90=>">80%",
            100=>"100%",
        );
	    if($bool){
            $id="".$id;
	        if(key_exists($id,$list)){
	            return $list[$id];
            }else{
	            return $id;
            }
        }
	    return $list;
    }

	public static function getSignMonthListForId($id="",$bool=false){
	    $list = array(
	        ""=>"",
            1=>"1".Yii::t("ka"," year"),
            2=>"2".Yii::t("ka"," year"),
            3=>"3".Yii::t("ka"," year")
        );
	    if($bool){
	        if(key_exists($id,$list)){
	            return $list[$id];
            }else{
	            return $id;
            }
        }
	    return $list;
    }

	public static function getAvaRateListForId($id="",$bool=false){
	    $list = array(
	        ""=>"",
            49=>"<50%",
            60=>"50-80%",
            90=>"81-100%"
        );
	    if($bool){
	        if(key_exists($id,$list)){
	            return $list[$id];
            }else{
	            return $id;
            }
        }
	    return $list;
    }

	public static function validateEmployee($model){
        $uid = Yii::app()->user->id;
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
            ->from("hr{$suffix}.hr_binding a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id = b.id")
            ->where("a.user_id=:user_id",array(":user_id"=>$uid))
            ->queryRow();
        if($row){
            $model->employee_id = $row["id"];
            $model->employee_code = $row["code"];
            $model->employee_name = $row["name"];
            return true;
        }else{
            return false;
        }
    }

    public static function getEmployeeNameForId($kam_id){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
            ->from("hr{$suffix}.hr_employee b")
            ->where("b.id=:id",array(":id"=>$kam_id))
            ->queryRow();
        if($row){
            return $row["name"]." ({$row["code"]})";
        }else{
            return "";
        }
    }

    public static function getSupportUserList($ka_city,$id=0){
        $suffix = Yii::app()->params['envSuffix'];
        $list=array(""=>"");
        if(!empty($ka_city)){
            $city = Yii::app()->db->createCommand()->select("city_code")->from("sal_ka_area")
                ->where("id=:id",array(":id"=>$ka_city))
                ->queryScalar();//查询KA城市的日报表系统编号
            $city=$city?$city:0;
            $city_allow = City::model()->getDescendantList($city);
            $city_allow .= (empty($city_allow)) ? "'$city'" : ",'$city'";
            $inRows = Yii::app()->db->createCommand()->select("code,incharge")
                ->from("security{$suffix}.sec_city")
                ->where("code in ({$city_allow})",array(":code"=>$city))
                ->queryAll();//查询城市的负责人
            if($inRows){
                foreach ($inRows as $inRow){
                    $city = $inRow["code"];
                    $incharge = $inRow["incharge"];
                    $rows = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
                        ->from("hr{$suffix}.hr_binding a")
                        ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
                        ->leftJoin("hr{$suffix}.hr_dept f","b.position=f.id")
                        ->where("(b.city=:city and f.dept_class='Sales') or a.user_id=:user_id or b.id=:id",
                            array(":city"=>$city,":user_id"=>$incharge,":id"=>$id)
                        )->queryAll();//查询城市下的销售人员
                    if($rows){
                        foreach ($rows as $row){
                            $list[$row["id"]] = $row["name"]." ({$row["code"]})";
                        }
                    }
                }
            }
        }
        return $list;
    }

    //查询相似的ka项目公司及备注
    public function AjaxCustomerName($group,$id=0){
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();//swoper$suffix.swo_service
        $html = "";
        $id = is_numeric($id)?$id:0;
        if($group!==""){
            $group = str_replace("'","\'",$group);
            $records = Yii::app()->db->createCommand()->select('a.customer_name,b.name,b.code')
                ->from("sal_ka_bot a")
                ->leftJoin("hr{$suffix}.hr_employee b","a.kam_id = b.id")
                ->where("a.id!='{$id}' and (a.customer_name like '%$group%' or a.remark like '%$group%')")
                ->queryAll();
            if($records){
                foreach ($records as $row){
                    $text = $row["customer_name"]."  -  "."{$row["name"]} ({$row['code']})";
                    $html.="<li><a class='clickThis'>".$text."</a>";
                }
            }else{
                $html = "<li><a>没有结果</a></li>";
            }
        }else{
            $html = "<li><a>请输入客户名称</a></li>";
        }
        return $html;
    }
}
