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
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,apply_date,customer_no,customer_name,kam_id,head_city_id,talk_city_id,
                contact_user,contact_phone,contact_email,contact_dept,source_text,source_id,
                area_id,level_id,class_id,busine_id,link_id,year_amt,support_user,sign_odds,city,
                available_date,available_amt,
                quarter_amt,month_amt,sign_date,sign_month,sign_amt,sum_amt,remark','safe'),
            array('apply_date,available_date,customer_name,kam_id,link_id','required'),
            array('apply_date','validateDate'),
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

	public function computeSignAmt($attribute, $params){
        $this->sum_amt = 0;
        if(!empty($this->sign_amt)){
            $this->sum_amt = $this->sign_amt;
        }else{
            $this->sum_amt+=empty($this->month_amt)?0:$this->month_amt;
            $this->sum_amt+=empty($this->quarter_amt)?0:$this->quarter_amt;
            $this->sum_amt+=empty($this->year_amt)?0:$this->year_amt;
            $this->sum_amt+=empty($this->available_amt)?0:$this->available_amt;
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
        $arr = array(
            "id"=>1,"apply_date"=>2,"available_date"=>2,"customer_no"=>1,"customer_name"=>1,"kam_id"=>1,
            "head_city_id"=>1,"talk_city_id"=>1,"contact_user"=>1,"contact_phone"=>1,
            "contact_email"=>1,"contact_dept"=>1,"source_text"=>1,"source_id"=>1,
            "area_id"=>1,"level_id"=>1,"class_id"=>1,"busine_id"=>1,"link_id"=>1,"year_amt"=>3,
            "support_user"=>3,"sign_odds"=>1,"city"=>1,"remark"=>1,"quarter_amt"=>3,"available_amt"=>3,
            "month_amt"=>3,"sign_date"=>2,"sign_month"=>1,"sign_amt"=>3,"sum_amt"=>3,
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
            "contact_email"=>1,"contact_dept"=>1,"source_text"=>1,"source_id"=>1,
            "area_id"=>1,"level_id"=>1,"class_id"=>1,"busine_id"=>1,"link_id"=>1,"year_amt"=>3,
            "support_user"=>3,"sign_odds"=>1,"city"=>1,"remark"=>1,"quarter_amt"=>3,
            "available_amt"=>3,"available_date"=>2,"month_amt"=>3,"sign_date"=>2,"sign_month"=>1,"sign_amt"=>3,"sum_amt"=>3,
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
            "contact_phone","contact_email","contact_dept","source_text","source_id","area_id",
            "level_id","class_id","busine_id","link_id","year_amt","available_amt","available_date","support_user","sign_odds",
            "quarter_amt","month_amt","sign_date","sign_month","sign_amt","sum_amt"
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
                $value = KALevelForm::getClassNameForId($value);
                break;
            case "busine_id":
                $value = KABusineForm::getBusineNameForId($value);
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
        $followDate = empty($this->follow_date)?0:strtotime($this->follow_date);
        if(isset($_POST['KABotForm']['detail'])){
            foreach ($_POST['KABotForm']['detail'] as $row) {
                if(in_array($row['uflag'],array("N","Y"))&&strtotime($row['info_date'])>=$followDate){
                    $this->follow_date = $row["info_date"];
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

	protected function save(&$connection)
	{
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
	    $list=array();
        $arr = array(
            "apply_date"=>2,"follow_date"=>2,"customer_name"=>1,
            "head_city_id"=>3,"talk_city_id"=>3,"contact_user"=>1,"contact_phone"=>1,
            "contact_email"=>1,"contact_dept"=>1,"source_text"=>1,"source_id"=>3,
            "area_id"=>3,"level_id"=>3,"class_id"=>3,"busine_id"=>3,"link_id"=>3,"year_amt"=>3,
            "support_user"=>3,"sign_odds"=>3,"remark"=>1,"quarter_amt"=>3,
            "available_amt"=>3,"available_date"=>2,"month_amt"=>3,"sign_date"=>2,"sign_month"=>3,"sign_amt"=>3,"sum_amt"=>3,
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
        switch ($this->scenario) {
            case 'delete':
                $connection->createCommand()->delete("sal_ka_bot", "id=:id", array(":id" => $this->id));
                break;
            case 'new':
                $list["kam_id"] = $this->employee_id;
                $list["city"] = $city;
                $list["lcu"] = $uid;
                $connection->createCommand()->insert("sal_ka_bot", $list);
                break;
            case 'edit':
                unset($list["apply_date"]);
                unset($list["customer_name"]);
                unset($list["kam_id"]);
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
            $incharge = Yii::app()->db->createCommand()->select("incharge")
                ->from("security{$suffix}.sec_city")
                ->where("code=:code",array(":code"=>$city))
                ->queryScalar();//查询城市的负责人
            $incharge=$incharge?$incharge:0;
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
        return $list;
    }
}
