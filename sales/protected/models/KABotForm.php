<?php

class KABotForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $apply_date;
	public $follow_date;
	public $customer_no;
	public $customer_name;
	public $search_name;
	public $kam_id;
	public $kam_name;
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
	public $sign_end_date;
	public $sign_month;
	public $sign_amt;
	public $sum_amt;
	public $ava_sum;//门店总数量
	public $available_date;
	public $available_amt;
	public $remark;
	public $support_user;
	public $sign_odds;
	public $city;
	public $shift_bool=0;

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

    public $renewal_total_amt;//续约累积金额
    public $renewal_sum;//续约累积门店
    public $contract_type=1;//合约类型：1：新增合约 2：续约合约 1,2：新增，续约合约

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

    public $avaRenewal = array(
        array('id'=>0,
            'bot_id'=>0,
            'renewal_date'=>'',//续约日期
            'renewal_num'=>'',//门店数量
            'renewal_city'=>'',//城市
            'renewal_note'=>'',//備註
            'renewal_amt'=>'',//实际成交金额
            'uflag'=>'N',
        ),
    );

    protected $function_id='CN15';
    protected $table_pre='_ka_';

    public $files;
    public $file_key='kabot';

    public $docMasterId = array(
        'kabot'=>0,
        'cabot'=>0,
        'rabot'=>0,
    );
    public $removeFileId = array(
        'kabot'=>0,
        'cabot'=>0,
        'rabot'=>0,
    );
    public $no_of_attm = array(
        'kabot'=>0,
        'cabot'=>0,
        'rabot'=>0,
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
            'area_id'=>Yii::t('ka','city'),
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
            'sign_end_date'=>Yii::t('ka','sign end date'),
            'sign_month'=>Yii::t('ka','sign month'),
            'sign_amt'=>Yii::t('ka','sign amt'),
            'sum_amt'=>Yii::t('ka','sum amt'),
            'support_user'=>Yii::t('ka','support user'),
            'sign_odds'=>Yii::t('ka','sign odds'),
            'remark'=>Yii::t('ka','remark'),
            'info_date'=>Yii::t('ka','info date'),
            'info_text'=>Yii::t('ka','info text'),
            'ava_sum'=>Yii::t('ka','ava sum'),

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

            'renewal_date'=>Yii::t('ka','renewal date'),
            'renewal_num'=>Yii::t('ka','renewal num'),
            'renewal_city'=>Yii::t('ka','renewal city'),
            'renewal_note'=>Yii::t('ka','renewal note'),
            'renewal_amt'=>Yii::t('ka','renewal amt'),
            'renewal_total_amt'=>Yii::t('ka','renewal total amt'),
            'renewal_sum'=>Yii::t('ka','renewal total num'),
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
                area_id,level_id,class_id,busine_id,link_id,support_user,sign_odds,city,order_type,
                available_date,available_amt,avaInfo,avaRenewal,renewal_sum,renewal_total_amt,
                contact_adr,work_user,work_phone,work_email,class_other,
                sign_date,sign_end_date,sign_month,sign_amt,sum_amt,ava_sum,remark','safe'),
            array('apply_date,work_user,work_phone,contact_adr,available_date,customer_name,kam_id,link_id
            ,head_city_id,talk_city_id,area_id,source_id,source_text,available_amt,sign_odds
            ,class_id,level_id','required'),
            array('customer_name','validateCustomerName'),
            array('apply_date','validateDate'),
            array('link_id','validateLinkID'),
            array('sign_amt','computeSignAmt'),
            array('files, removeFileId, docMasterId, no_of_attm','safe'),
		);
	}

	public function getThisTablePre(){
	    return $this->table_pre;
    }

    public function validateShift() {
        $shift_to_tab = key_exists("shift_to_tab",$_POST)?$_POST["shift_to_tab"]:"";
        $shift_to_staff = key_exists("shift_to_staff",$_POST)?$_POST["shift_to_staff"]:"";
        $shift_remark = key_exists("shift_remark",$_POST)?$_POST["shift_remark"]:"";
        $tablePreList = self::getTablePreList();
        if(empty($shift_to_tab)){
            $this->addError("id", "转移位置不能为空！");
        }
        if(!empty($shift_to_tab)&&!key_exists($shift_to_tab,$tablePreList)){
            $this->addError("id", "转移位置格式异常！");
        }
        if(empty($shift_to_staff)){
            $this->addError("id", "转移后员工不能为空！");
        }
        if(empty($shift_remark)){
            $this->addError("id", "转移说明不能为空！");
        }
        $rowShift = Yii::app()->db->createCommand()->select('*')->from("sal_ka_shift")
            ->where("shift_from_tab=:table_pre and shift_from_id=:id",array(":table_pre"=>$this->table_pre,":id"=>$this->id))
            ->queryRow();
        if($rowShift){
            $shiftCode = Yii::app()->db->createCommand()->select('customer_no')->from("sal{$rowShift["shift_to_tab"]}bot")
                ->where("id=:id",array(":id"=>$rowShift["shift_to_id"]))
                ->queryRow();
            if($shiftCode){
                $this->addError("id", "该记录已被转移，无法继续转移：".self::getTableStrForPre($rowShift["shift_to_tab"])." (".$shiftCode["customer_no"].")");
            }
        }

        $errorMsg = $this->getError("id");
        if(empty($errorMsg)){
            return true;
        }
        return false;
    }

    public static function getShiftRowForID($table_pre,$id){
        $rowShift = Yii::app()->db->createCommand()->select('*')->from("sal_ka_shift")
            ->where("(shift_from_tab=:table and shift_from_id=:id) or (shift_to_tab=:table and shift_to_id=:id)",array(":table"=>$table_pre,":id"=>$id))
            ->order("id desc")->queryRow();
        if($rowShift){
            return $rowShift;
        }else{
            return array();
        }
    }

    public static function getTablePreList(){
	    $list = array(
            "_ka_"=>Yii::t("app","KA Bot"),//"NKA项目"
            "_ra_"=>Yii::t("app","RA Bot"),//"RKA项目"
            "_ca_"=>Yii::t("app","CA Bot"),//"地方业务项目"
        );
	    return $list;
    }

    public static function getTableStrForPre($table_pre){
        $list = self::getTablePreList();
        if(key_exists($table_pre,$list)){
            return $list[$table_pre];
        }else{
            return $table_pre;
        }
    }

    public function validateCustomerName($attribute, $params) {
        $ka_id = 0;
        $ra_id = 0;
        $ca_id = 0;
        $group = str_replace("'","\'",$this->customer_name);
        $group = KADupForm::getSearchNameForName($group);
        $this->search_name = $group;
        switch ($this->table_pre){
            case "_ka_":
                $ka_id = $this->id;
                break;
            case "_ra_":
                $ra_id = $this->id;
                break;
            case "_ca_":
                $ca_id = $this->id;
                break;
        }
        $rowKa = Yii::app()->db->createCommand()->select('a.customer_no,a.customer_name,a.kam_id')
            ->from("sal_ka_bot a")
            ->where("a.search_name=:name and a.shift_bool=0 and id!=:id",array(":name"=>$group,":id"=>$ka_id))
            ->queryRow();
        if($rowKa){
            $this->addError($attribute, "客户名称不能重复：".$rowKa["customer_name"]."({$rowKa["customer_no"]})");
        }
        /*
        $rowRa = Yii::app()->db->createCommand()->select('a.customer_no,a.customer_name,a.kam_id')
            ->from("sal_ra_bot a")
            ->where("a.search_name=:name and a.shift_bool=0 and id!=:id",array(":name"=>$group,":id"=>$ra_id))
            ->queryRow();
        if($rowRa){
            $this->addError($attribute, "客户名称不能重复：".$rowRa["customer_name"]."({$rowRa["customer_no"]})");
        }
        */
        $rowCa = Yii::app()->db->createCommand()->select('a.customer_no,a.customer_name,a.kam_id')
            ->from("sal_ca_bot a")
            ->where("a.search_name=:name and a.shift_bool=0 and id!=:id",array(":name"=>$group,":id"=>$ca_id))
            ->queryRow();
        if($rowCa){
            $this->addError($attribute, "客户名称不能重复：".$rowCa["customer_name"]."({$rowCa["customer_no"]})");
        }
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
        $emptyTwoList = array();
        $avaDateList = array();
        $avaDateBool = false;//判断月份是否重复
        $renewalList = array();
        $renewalDateList = array();
        $renewalDateBool = false;//判断月份是否重复
        if(!empty($this->avaInfo)){
            foreach ($this->avaInfo as &$row){
                if(!empty($row["ava_date"])){
                    $list[]=$row;
                    $row["uflag"] = $model->rate_num==100?$row["uflag"]:"D";//如果沟通不是100，则没有详情
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
        if(!empty($this->avaRenewal)){
            foreach ($this->avaRenewal as &$row){
                if(!empty($row["renewal_date"])){
                    $renewalList[]=$row;
                    $row["uflag"] = $model->rate_num==100?$row["uflag"]:"D";//如果沟通不是100，则没有详情
                    if($row["uflag"]!="D"){
                        $emptyTwoList[]=$row;
                        if(!$renewalDateBool&&in_array($row["renewal_date"],$renewalDateList)){
                            $renewalDateBool = true;
                        }
                        $renewalDateList[]=$row["renewal_date"];
                    }
                }
            }
        }
        //$this->avaInfo = $list;
	    if($model->rate_num==100){
	        if(empty($this->sign_date)){
                $this->addError($attribute, Yii::t('ka','sign date')."不能为空");
            }
	        if(empty($this->sign_end_date)){
                $this->addError($attribute, Yii::t('ka','sign end date')."不能为空");
            }
	        if(empty($this->sign_month)){
                $this->addError($attribute, Yii::t('ka','sign month')."不能为空");
            }
	        if(empty($emptyList)&&empty($emptyTwoList)){
                $this->addError($attribute, Yii::t('ka','sign end date')."新增列表或续约列表至少填写一项");
            }elseif(!empty($emptyList)){
	            $endAvaList = end($emptyList);
                if(!isset($endAvaList["ava_rate"])||$endAvaList["ava_rate"]<=80){
                    $this->addError($attribute, "签约详情第一条的签约概率必须大于80");
                }
            }
            if($avaDateBool){
                $this->addError($attribute, "新增详情的月份不能重复");
            }
            if($renewalDateBool){
                $this->addError($attribute, "续约详情的月份不能重复");
            }
            $this->sign_odds=100;
            if(!empty($emptyList)&&!empty($emptyList)){
                $this->contract_type='1,2';
            }elseif (!empty($emptyList)){
                $this->contract_type=1;
            }else{
                $this->contract_type=2;
            }
        }else{
	        $this->contract_type=1;
	        $this->sign_date=null;
	        $this->sign_end_date=null;
	        $this->sign_month=null;
	        if(is_numeric($this->sign_odds)&&$this->sign_odds==100){
                $this->sign_odds=null;
            }
        }
    }

	public function computeSignAmt($attribute, $params){
        $this->sum_amt = 0;
        $this->sum_amt+=empty($this->sign_amt)?0:$this->sign_amt;
        $className = get_class($this);
        if(isset($_POST[$className]['avaInfo'])) {
            foreach ($_POST[$className]['avaInfo'] as $row) {
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
        $table_pre = $this->table_pre;
        if(Yii::app()->user->validFunction($this->function_id)){//所有
            //$whereSql = " and (a.kam_id='{$this->employee_id}' or a.support_user='{$this->employee_id}' or h.city in ({$city_allow}))";
            $whereSql = "";//2023/06/16 改為可以看的所有記錄
        }elseif(Yii::app()->user->validFunction('CN19')){//本地
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql = " and (a.kam_id in ({$idSQL}) or a.support_user in ({$idSQL}) or h.city in ({$city_allow}))";
        }else{
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql = " and (a.kam_id in ({$idSQL}) or a.support_user in ({$idSQL}))";
        }
		$sql = "select a.*,docman$suffix.countdoc('{$this->file_key}',a.id) as countdoc from sal{$table_pre}bot a left join hr{$suffix}.hr_employee h ON a.kam_id=h.id where a.id=".$index." {$whereSql}";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
        //contact_adr,work_user,work_phone,work_email,class_other

        $arr = array(
            "id"=>1,"apply_date"=>2,"available_date"=>2,"customer_no"=>1,"customer_name"=>1,"kam_id"=>1,
            "head_city_id"=>1,"talk_city_id"=>4,"contact_user"=>1,"contact_phone"=>1,
            "contact_email"=>1,"source_text"=>1,"source_id"=>1,"shift_bool"=>1,
            "area_id"=>1,"level_id"=>1,"class_id"=>1,"busine_id"=>4,"link_id"=>1,
            "support_user"=>3,"sign_odds"=>1,"city"=>1,"remark"=>1,"available_amt"=>3,
            "sign_date"=>2,"sign_end_date"=>2,"sign_month"=>1,"sign_amt"=>3,"sum_amt"=>3,
            "contact_adr"=>1,"ava_sum"=>1,"contract_type"=>1,"renewal_total_amt"=>3,"renewal_sum"=>3,
            "work_user"=>1,"work_phone"=>1,"work_email"=>1,"class_other"=>1,
        );
		if ($row!==false) {
            $this->no_of_attm[$this->file_key] = $row['countdoc'];
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
            $this->kam_name = self::getEmployeeNameForId($this->kam_id);
            $sql = "select * from sal{$table_pre}bot_info where bot_id=".$index." order by info_date desc";
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
            $sql = "select * from sal{$table_pre}bot_ava where bot_id=".$index." order by ava_date desc";
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
            $sql = "select * from sal{$table_pre}bot_renewal where bot_id=".$index." order by renewal_date desc";
            $renewalRows = Yii::app()->db->createCommand($sql)->queryAll();
            if($renewalRows){
                $this->avaRenewal=array();
                foreach ($renewalRows as $renewalRow){
                    $temp = array();
                    $temp["id"] = $renewalRow["id"];
                    $temp["bot_id"] = $renewalRow["bot_id"];
                    $temp["renewal_date"] = date("Y/m",strtotime($renewalRow["renewal_date"]));
                    $temp["renewal_num"] = $renewalRow["renewal_num"];
                    $temp["renewal_city"] = $renewalRow["renewal_city"];
                    $temp["renewal_note"] = $renewalRow["renewal_note"];
                    $temp["renewal_amt"] = !empty($renewalRow["renewal_amt"])?floatval($renewalRow["renewal_amt"]):null;
                    $temp['uflag'] = 'N';
                    $this->avaRenewal[] = $temp;
                }
            }
            return true;
		}else{
		    return false;
        }
	}

	public function setModelData($index){
        $table_pre = $this->table_pre;
		$sql = "select a.* from sal{$table_pre}bot a where a.id=".$index."";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
        $arr = array(
            "id"=>1,"apply_date"=>2,"customer_no"=>1,"customer_name"=>1,"kam_id"=>1,
            "head_city_id"=>1,"talk_city_id"=>4,"contact_user"=>1,"contact_phone"=>1,
            "contact_email"=>1,"source_text"=>1,"source_id"=>1,
            "area_id"=>1,"level_id"=>1,"class_id"=>1,"busine_id"=>4,"link_id"=>1,
            "support_user"=>3,"sign_odds"=>1,"city"=>1,"remark"=>1,
            "available_amt"=>3,"available_date"=>2,"sign_date"=>2,"sign_end_date"=>2,"sign_month"=>1,"sign_amt"=>3,"sum_amt"=>3,
            "contact_adr"=>1,"ava_sum"=>1,"contract_type"=>1,"renewal_total_amt"=>3,"renewal_sum"=>3,
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
            $this->saveRenewalInfo($connection);
            $this->updateDocman($connection,$this->file_key);
			$transaction->commit();
		}
		catch(Exception $e) {
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
        return array("apply_date","customer_name","head_city_id","talk_city_id","contact_user",
            "contact_phone","contact_email","source_text","source_id","area_id",
            "level_id","class_id","busine_id","link_id","available_amt","available_date","support_user","sign_odds",
            "sign_date","sign_end_date","sign_month","sign_amt","sum_amt",
            "contact_adr","ava_sum","contract_type","renewal_sum","renewal_total_amt",
            "work_user","work_phone","work_email","class_other"
        );
    }

    protected static function getNameForValue($type,$value){
        switch ($type){
            case "talk_city_id":
                $value = KAAreaForm::getAreaNameForArr($value);
                break;
            case "head_city_id":
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
            case "contract_type":
                $value = KABotForm::getContractTypeListForId($value,true);
                break;
        }
        return $value;
    }

    public function getThisModel(){
        switch ($this->table_pre){
            case "_ca_":
                return new CABotForm();
            case "_ra_":
                return new CABotForm();
            default:
                return new KABotForm();
        }

    }

	//保存历史记录
    protected function historySave(&$connection){
        $table_pre = $this->table_pre;
        switch ($this->getScenario()){
            case "delete":
                $connection->createCommand()->delete("sal{$table_pre}bot_history", "bot_id=:id", array(":id" => $this->id));
                break;
            case "edit":
                $uid = Yii::app()->user->id;
                $model = $this->getThisModel();
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
                $this->getHistoryRenewalInfo($list["update_html"]);
                if(!empty($list["update_html"])){
                    $list["update_html"] = implode("<br/>",$list["update_html"]);
                    $list["espe_type"] = $this->espe_type;
                    $list["sum_amt"] = empty($this->sum_amt)?0:$this->sum_amt;
                    $list["sign_odds"] = empty($this->sign_odds)?0:$this->sign_odds;
                    $list["update_json"] = $this->getUpdateJson();
                    $connection->createCommand()->insert("sal{$table_pre}bot_history", $list);
                }
                break;
        }
    }

    protected function getHistoryDetail(&$list){
        $followDate = empty($this->follow_date)?0:$this->follow_date;
        $className = get_class($this);
        if(isset($_POST[$className]['detail'])){
            foreach ($_POST[$className]['detail'] as $row) {
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
        $table_pre = $this->table_pre;
        $className = get_class($this);
        if(isset($_POST[$className]['detail'])){
            foreach ($_POST[$className]['detail'] as $row) {
                $sql = '';
                switch ($this->scenario) {
                    case 'delete':
                        $sql = "delete from sal{$table_pre}bot_info where bot_id = :bot_id";
                        break;
                    case 'new':
                        if ($row['uflag']=='Y') {
                            $sql = "insert into sal{$table_pre}bot_info(
									bot_id, info_date, info_text,lcu
								) values (
									:bot_id,:info_date,:info_text,:lcu
								)";
                        }
                        break;
                    case 'edit':
                        switch ($row['uflag']) {
                            case 'D':
                                $sql = "delete from sal{$table_pre}bot_info where id = :id";
                                break;
                            case 'Y':
                                $sql = ($row['id']==0)
                                    ?
                                    "insert into sal{$table_pre}bot_info(
									  bot_id, info_date, info_text,lcu
									) values (
									  :bot_id,:info_date,:info_text,:lcu
									)"
                                    :
                                    "update sal{$table_pre}bot_info set
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

    protected function getHistoryAvaInfo(&$list){
        $maxDate = $this->available_date;
        $className = get_class($this);
        if(isset($_POST[$className]['avaInfo'])){
            foreach ($_POST[$className]['avaInfo'] as $row) {
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

    protected function getHistoryRenewalInfo(&$list){
        $maxDate = $this->available_date;
        $className = get_class($this);
        if(isset($_POST[$className]['avaRenewal'])){
            foreach ($_POST[$className]['avaRenewal'] as $row) {
                if(empty($row['renewal_date'])){
                    continue;
                }
                $row['renewal_date']=str_replace("-","/",$row['renewal_date']);
                $row['renewal_date'] = explode("/",$row['renewal_date']);
                if(count($row['renewal_date'])==2){
                    $row['renewal_date'][]="01";
                }
                $row['renewal_date']=implode("/",$row['renewal_date']);
                if(in_array($row['uflag'],array("N","Y"))&&strtotime($row['renewal_date'])>=strtotime($maxDate)){
                    $maxDate = $row["renewal_date"];
                }
                switch ($row['uflag']){
                    case "Y"://修改
                        if(!empty($row['id'])){
                            $list[]="<span>修改了续约列表：".$row['renewal_date']."</span>";
                        }
                        break;
                    case "D"://刪除
                        $list[]="<span>删除了续约列表：".$row['renewal_date']."</span>";
                        break;
                }
            }
        }
        $this->ava_show_date = $maxDate;
        return $list;
    }

    protected function saveAvaInfo(&$connection)
    {
        $table_pre = $this->table_pre;
        $uid = Yii::app()->user->id;
        $className = get_class($this);
        if(isset($this->avaInfo)){
            foreach ($this->avaInfo as $row) {
                if(empty($row["ava_date"])){
                    continue;
                }
                $sql = '';
                switch ($this->scenario) {
                    case 'delete':
                        $sql = "delete from sal{$table_pre}bot_ava where bot_id = :bot_id";
                        break;
                    case 'new':
                        if ($row['uflag']=='Y') {
                            $sql = "insert into sal{$table_pre}bot_ava(
									bot_id, ava_date, ava_amt, ava_num, ava_city, ava_rate, ava_note, ava_fact_amt,lcu
								) values (
									:bot_id,:ava_date,:ava_amt,:ava_num,:ava_city,:ava_rate,:ava_note,:ava_fact_amt,:lcu
								)";
                        }
                        break;
                    case 'edit':
                        switch ($row['uflag']) {
                            case 'D':
                                $sql = "delete from sal{$table_pre}bot_ava where id = :id";
                                break;
                            case 'Y':
                                $sql = ($row['id']==0)
                                    ?
                                    "insert into sal{$table_pre}bot_ava(
                                        bot_id, ava_date, ava_amt, ava_num, ava_city, ava_rate, ava_note,ava_fact_amt,lcu
                                    ) values (
                                        :bot_id,:ava_date,:ava_amt,:ava_num,:ava_city,:ava_rate,:ava_note,:ava_fact_amt,:lcu
									)"
                                    :
                                    "update sal{$table_pre}bot_ava set
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

    protected function saveRenewalInfo(&$connection)
    {
        $table_pre = $this->table_pre;
        $uid = Yii::app()->user->id;
        $className = get_class($this);
        if(isset($this->avaRenewal)){
            foreach ($this->avaRenewal as $row) {
                if(empty($row["renewal_date"])){
                    continue;
                }
                $sql = '';
                switch ($this->scenario) {
                    case 'delete':
                        $sql = "delete from sal{$table_pre}bot_renewal where bot_id = :bot_id";
                        break;
                    case 'new':
                        if ($row['uflag']=='Y') {
                            $sql = "insert into sal{$table_pre}bot_renewal(
									bot_id, renewal_date, renewal_num, renewal_city, renewal_note, renewal_amt,lcu
								) values (
									:bot_id,:renewal_date,:renewal_num,:renewal_city,:renewal_note,:renewal_amt,:lcu
								)";
                        }
                        break;
                    case 'edit':
                        switch ($row['uflag']) {
                            case 'D':
                                $sql = "delete from sal{$table_pre}bot_renewal where id = :id";
                                break;
                            case 'Y':
                                $sql = ($row['id']==0)
                                    ?
                                    "insert into sal{$table_pre}bot_renewal(
                                        bot_id, renewal_date, renewal_num, renewal_city, renewal_note, renewal_amt,lcu
                                    ) values (
                                        :bot_id,:renewal_date,:renewal_num,:renewal_city,:renewal_note,:renewal_amt,:lcu
									)"
                                    :
                                    "update sal{$table_pre}bot_renewal set
										renewal_date = :renewal_date, 
										renewal_num = :renewal_num,
										renewal_city = :renewal_city,
										renewal_note = :renewal_note,
										renewal_amt = :renewal_amt,
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
                    if (strpos($sql,':renewal_date')!==false){
                        if(empty($row['renewal_date'])){
                            $row['renewal_date']=null;
                        }else{
                            $row['renewal_date']=str_replace("-","/",$row['renewal_date']);
                            $row['renewal_date'] = explode("/",$row['renewal_date']);
                            if(count($row['renewal_date'])==2){
                                $row['renewal_date'][]="01";
                            }
                            $row['renewal_date']=implode("/",$row['renewal_date']);
                        }
                        $command->bindParam(':renewal_date',$row['renewal_date'],PDO::PARAM_STR);
                    }
                    if (strpos($sql,':renewal_amt')!==false){
                        $row['renewal_amt']=empty($row['renewal_amt'])?null:$row['renewal_amt'];
                        $command->bindParam(':renewal_amt',$row['renewal_amt'],PDO::PARAM_STR);
                    }
                    if (strpos($sql,':renewal_num')!==false){
                        $row['renewal_num']=empty($row['renewal_num'])?null:$row['renewal_num'];
                        $command->bindParam(':renewal_num',$row['renewal_num'],PDO::PARAM_STR);
                    }
                    if (strpos($sql,':renewal_city')!==false){
                        $row['renewal_city']=empty($row['renewal_city'])?null:$row['renewal_city'];
                        $command->bindParam(':renewal_city',$row['renewal_city'],PDO::PARAM_STR);
                    }
                    if (strpos($sql,':renewal_note')!==false)
                        $command->bindParam(':renewal_note',$row['renewal_note'],PDO::PARAM_STR);
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
        $table_pre = $this->table_pre;
        $busine_name = KABusineForm::getBusineNameForArr($this->busine_id);
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
	    $list=array();
        $arr = array(
            "apply_date"=>2,"follow_date"=>2,"customer_name"=>1,"search_name"=>1,
            "head_city_id"=>3,"talk_city_id"=>4,"contact_user"=>1,"contact_phone"=>1,
            "contact_email"=>1,"source_text"=>1,"source_id"=>3,
            "area_id"=>3,"level_id"=>3,"class_id"=>3,"busine_id"=>4,"link_id"=>3,
            "support_user"=>3,"sign_odds"=>3,"remark"=>1,
            "available_amt"=>3,"available_date"=>2,"sign_date"=>2,"sign_end_date"=>2,"sign_month"=>3,"sign_amt"=>3,"sum_amt"=>3,
            "contract_type"=>1,"renewal_total_amt"=>3,"renewal_sum"=>3,
            "contact_adr"=>1,"ava_show_date"=>1,"ava_sum"=>3,
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
                case 4://数组
                    $value = $value===""?null:json_encode($value);
                    break;
            }
            $this->$key=$value;
            $list[$key] = $value;
        }
        switch ($this->scenario) {
            case 'delete':
                $connection->createCommand()->delete("sal{$table_pre}bot", "id=:id", array(":id" => $this->id));
                //删除转移记录
                $this->deleteShiftData($connection);
                break;
            case 'new':
                $list["busine_name"] = $busine_name;
                $list["kam_id"] = $this->employee_id;
                $list["city"] = $city;
                $list["lcu"] = $uid;
                $connection->createCommand()->insert("sal{$table_pre}bot", $list);
                break;
            case 'edit':
                unset($list["apply_date"]);
                //unset($list["customer_name"]);
                unset($list["kam_id"]);
                $list["busine_name"] = $busine_name;
                $list["luu"] = $uid;
                $connection->createCommand()->update("sal{$table_pre}bot", $list, "id=:id", array(":id" => $this->id));
                break;
        }
		if ($this->scenario=='new'){
            $this->id = Yii::app()->db->getLastInsertID();
            $this->lenStr();
            Yii::app()->db->createCommand()->update("sal{$table_pre}bot", array(
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
            $connection->createCommand()->insert("sal{$table_pre}bot_history", $list);
        }
		return true;
	}

    protected function lenStr(){
        $code = strval($this->id);
        $this->customer_no = "NKA";
        for($i = 0;$i < 5-strlen($code);$i++){
            $this->customer_no.="0";
        }
        $this->customer_no .= $code;
    }

	public function isOccupied(){
        $row = Yii::app()->db->createCommand()->select('*')->from("sal_ka_shift")
            ->where("shift_from_tab='{$this->table_pre}' and shift_from_id=:id",array(":id"=>$this->id))
            ->queryRow();
        if($row){
            return true;
        }else{
            return false;
        }
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

	public static function getContractTypeListForId($ids="",$bool=false){
	    $list = array(
	        "1"=>"新增合约",
	        "2"=>"续约合约"
        );
	    if($bool){
            $ids="".$ids;
            $idList = explode(",",$ids);
            $returnList = array();
            foreach ($idList as $id){
                $id = "".$id;
                if(key_exists($id,$list)){
                    $returnList[]=$list[$id];
                }else{
                    $returnList[]=$id;
                }
            }
            return implode("、",$returnList);
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

    public static function getGroupIDStrForEmployeeID($employee_id){
        $suffix = Yii::app()->params['envSuffix'];
        $employee_id = empty($employee_id)||!is_numeric($employee_id)?0:$employee_id;
        $list = array($employee_id);
        $bossRow = Yii::app()->db->createCommand()->select("a.id")
            ->from("hr{$suffix}.hr_group_staff a")
            ->leftJoin("hr{$suffix}.hr_group b","a.group_id=b.id")
            ->where("a.employee_id=:employee_id and b.group_code='KALIST'",array(":employee_id"=>$employee_id))
            ->queryRow();
        if($bossRow){//该员工有分组
            $infoRows = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
                ->from("hr{$suffix}.hr_group_branch a")
                ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
                ->where("a.group_staff_id=:group_staff_id",array(":group_staff_id"=>$bossRow["id"]))
                ->queryAll();
            if($infoRows){//该员工有管辖员工
                foreach ($infoRows as $infoRow){
                    $list[] = $infoRow["id"];
                }
            }
        }
        return "'".implode("','",$list)."'";
    }

    public static function getSupportUserList($ka_city,$id=0){
        $suffix = Yii::app()->params['envSuffix'];
        $list=array(""=>"");
        if(!empty($ka_city)){
            $idSql = is_array($ka_city)?implode(",",$ka_city):$ka_city;
            $cityRows = Yii::app()->db->createCommand()->select("city_code")->from("sal_ka_area")
                ->where("id in ({$idSql})")
                ->queryAll();//查询KA城市的日报表系统编号
            $cityList = array();//城市列表
            $inchargeList = array();//城市负责人
            if($cityRows){
                foreach ($cityRows as $cityRow){
                    $city=$cityRow["city_code"];
                    $city_allow = City::model()->getDescendantList($city);
                    $city_allow .= (empty($city_allow)) ? "'$city'" : ",'$city'";
                    $inRows = Yii::app()->db->createCommand()->select("code,incharge")
                        ->from("security{$suffix}.sec_city")
                        ->where("code in ({$city_allow})",array(":code"=>$city))
                        ->queryAll();//查询城市的负责人
                    if($inRows){
                        foreach ($inRows as $inRow){
                            if(!in_array($inRow["code"],$cityList)){
                                $cityList[]=$inRow["code"];
                            }
                            if(!in_array($inRow["incharge"],$inchargeList)){
                                $inchargeList[]=$inRow["incharge"];
                            }
                        }
                    }
                }
            }
            $citySql = implode("','",$cityList);
            $inchargeSql = implode("','",$inchargeList);
            $rows = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
                ->from("hr{$suffix}.hr_binding a")
                ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
                ->leftJoin("hr{$suffix}.hr_dept f","b.position=f.id")
                ->where("(b.city in ('{$citySql}') and f.dept_class='Sales') or a.user_id in ('{$inchargeSql}') or b.id=:id",
                    array(":id"=>$id)
                )->queryAll();//查询城市下的销售人员
            if($rows){
                foreach ($rows as $row){
                    $list[$row["id"]] = $row["name"]." ({$row["code"]})";
                }
            }
        }
        return $list;
    }

    public static function getKABotStaffForAccess(){
        $list = array();
        $systemId = Yii::app()->params['systemId'];
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
            ->from("hr{$suffix}.hr_binding a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->leftJoin("security{$suffix}.sec_user_access f","a.user_id=f.username and f.system_id='{$systemId}'")
            ->where("f.a_read_write like '%KA01%' or f.a_read_write like '%RA01%' or f.a_read_write like '%CA01%'")
            ->queryAll();//查询拥有读写权限的员工
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = $row["name"]." ({$row["code"]})";
            }
        }
        return $list;
    }

    //查询相似的ka项目公司及备注
    public function AjaxCustomerName($group,$id=0){
        $table_pre = $this->table_pre;
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();//swoper$suffix.swo_service
        $html = "";
        $id = is_numeric($id)?$id:0;
        if($group!==""){
            $group = str_replace("'","\'",$group);
            $recordsKa = Yii::app()->db->createCommand()->select('a.customer_no,a.customer_name,a.kam_id')
                ->from("sal_ka_bot a")
                ->where("a.id!='{$id}' and a.shift_bool=0 and (a.search_name like '%$group%')")
                ->queryAll();
            $recordsKa = $recordsKa?$recordsKa:array();
            /*
            $recordsRa = Yii::app()->db->createCommand()->select('a.customer_no,a.customer_name,a.kam_id')
                ->from("sal_ra_bot a")
                ->where("a.id!='{$id}' and a.shift_bool=0 and (a.search_name like '%$group%')")
                ->queryAll();
            */
            $recordsRa = array();
            $recordsCa = Yii::app()->db->createCommand()->select('a.customer_no,a.customer_name,a.kam_id')
                ->from("sal_ca_bot a")
                ->where("a.id!='{$id}' and a.shift_bool=0 and (a.search_name like '%$group%')")
                ->queryAll();
            $recordsCa = $recordsCa?$recordsCa:array();
            $records = array_merge($recordsKa,$recordsRa,$recordsCa);
            if($records){
                foreach ($records as $row){
                    $staffRow = Yii::app()->db->createCommand()->select('code,name')
                        ->from("hr{$suffix}.hr_employee")
                        ->where("id=:id",array(":id"=>$row["kam_id"]))
                        ->queryRow();
                    if($staffRow){
                        $text = "({$row["customer_no"]})".$row["customer_name"]."  -  "."{$staffRow["name"]} ({$staffRow['code']})";
                        $html.="<li><a class='clickThis'>".$text."</a>";
                    }
                }
            }else{
                $html = "<li><a>没有结果</a></li>";
            }
        }else{
            $html = "<li><a>请输入客户名称</a></li>";
        }
        return $html;
    }

    //项目转移
    public function shiftData(){
        $botRow = Yii::app()->db->createCommand()->select('*')->from("sal{$this->table_pre}bot")
            ->where("id=:id",array(":id"=>$this->id))
            ->queryRow();
        if($botRow){
            $connection = Yii::app()->db;
            $transaction=$connection->beginTransaction();
            try {
                $this->shiftSave($connection,$botRow);
                $transaction->commit();
            }catch(Exception $e) {
                $transaction->rollback();
                throw new CHttpException(404,$e->getMessage());
            }
            //
        }
    }

    protected function shiftSave($connection,$botRow){
        $connection->createCommand()->update("sal{$this->table_pre}bot",array(
            "shift_bool"=>1
        ),'id='.$this->id);
        $shift_to_tab = key_exists("shift_to_tab",$_POST)?$_POST["shift_to_tab"]:"";
        $shift_to_staff = key_exists("shift_to_staff",$_POST)?$_POST["shift_to_staff"]:"";
        $shift_remark = key_exists("shift_remark",$_POST)?$_POST["shift_remark"]:"";
        $historyArr = array("<span>转移</span>");
        if($shift_to_tab!=$this->table_pre){
            $historyArr[] = "<span>类型：".self::getTableStrForPre($this->table_pre)." 转移 ".self::getTableStrForPre($shift_to_tab)."</span>";
        }
        $historyArr[] = "<span>销售：".KABotForm::getEmployeeNameForId($botRow["kam_id"])." 转移 ".KABotForm::getEmployeeNameForId($shift_to_staff)."</span>";
        $historyArr[] = "<span>转移说明：{$shift_remark}</span>";
        $historyArr = implode("<br/>",$historyArr);
        $copyTable = array(
            array("from_table"=>"sal{$this->table_pre}bot_ava","to_table"=>"sal{$shift_to_tab}bot_ava"),
            array("from_table"=>"sal{$this->table_pre}bot_renewal","to_table"=>"sal{$shift_to_tab}bot_renewal"),
            array("from_table"=>"sal{$this->table_pre}bot_history","to_table"=>"sal{$shift_to_tab}bot_history"),
            array("from_table"=>"sal{$this->table_pre}bot_info","to_table"=>"sal{$shift_to_tab}bot_info"),
        );
        $uid = Yii::app()->user->id;
        //$dateTime = date_format(date_create(),"Y/m/d H:i:s");
        $arr = $botRow;
        unset($arr["id"]);
        unset($arr["customer_no"]);
        $arr["kam_id"] =$shift_to_staff;
        $connection->createCommand()->insert("sal{$shift_to_tab}bot",$arr);
        $shift_id = Yii::app()->db->getLastInsertID();
        $customer_no = "SIT".(100000+$shift_id);
        $connection->createCommand()->update("sal{$shift_to_tab}bot",array(
            "customer_no"=>$customer_no
        ),'id='.$shift_id);
        $connection->createCommand()->insert("sal{$this->table_pre}bot_history",array(
            "bot_id"=>$this->id,
            "update_type"=>4,
            "update_html"=>$historyArr,
            "lcu"=>$uid,
        ));//生成转移历史记录

        //复制ava、info、history
        foreach ($copyTable as $tableRow){
            $rows = $connection->createCommand()->select('*')->from($tableRow["from_table"])
                ->where("bot_id=:id",array(":id"=>$this->id))->queryAll();
            if($rows){
                foreach ($rows as $row){
                    $infoArr = $row;
                    unset($infoArr["id"]);
                    $infoArr["bot_id"] =$shift_id;
                    $connection->createCommand()->insert($tableRow["to_table"],$infoArr);
                }
            }
        }

        //生成转移table
        $connection->createCommand()->insert("sal_ka_shift",array(
            "shift_from_tab"=>$this->table_pre,
            "shift_from_id"=>$this->id,
            "shift_from_staff"=>$botRow["kam_id"],
            "shift_to_tab"=>$shift_to_tab,
            "shift_to_id"=>$shift_id,
            "shift_to_staff"=>$shift_to_staff,
            "shift_remark"=>$shift_remark,
            "lcu"=>$uid,
        ));
    }

    //删除项目时删除转移数据
    protected function deleteShiftData($connection){
        $row = $connection->createCommand()->select('*')->from("sal_ka_shift")
            ->where("shift_to_tab='{$this->table_pre}' and shift_to_id=:id",array(":id"=>$this->id))
            ->queryRow();
        if($row){
            $connection->createCommand()->delete("sal_ka_shift", "id=:id", array(":id" =>$row["id"]));
            $connection->createCommand()->update("sal{$row["shift_from_tab"]}bot",array(
                "shift_bool"=>0
            ),'id='.$row["shift_from_id"]);
            $connection->createCommand()->insert("sal{$row["shift_from_tab"]}bot_history",array(
                "bot_id"=>$row["shift_from_id"],
                "update_type"=>5,
                "update_html"=>"<span>转移后的数据被删除</span>",
                "lcu"=>Yii::app()->user->id,
            ));//生成转移历史记录
        }
    }
}
