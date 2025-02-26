<?php

class KAStatisticForm extends CFormModel
{
	/* User Fields */
	public $search_year;
	public $search_month;
	public $start_date;
	public $end_date;
	public $ka_month;
	public $ka_year;

	public $data=array();

	public $th_sum=0;//所有th的个数

    public $employee_id;
    public $employee_code;
    public $employee_name;

    public $downJsonText='';

    protected $function_id='CN15';
    protected $table_pre='_ka_';
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'start_date'=>Yii::t('ka','start date'),
            'end_date'=>Yii::t('ka','end date'),
            'search_year'=>Yii::t('ka','search year'),
            'search_month'=>Yii::t('ka','search month'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('search_year,search_month','safe'),
			array('search_year,search_month','required'),
            array('search_month','validateDate'),
		);
	}

    public function validateDate($attribute, $params) {
	    $this->ka_year = $this->search_year;
	    $this->ka_month = $this->search_month;
	    $timer = strtotime($this->search_year."/".$this->search_month."/01");
	    $this->start_date = date("Y/m/d",$timer);
	    $this->end_date = date("Y/m/t",$timer);
	    if($this->start_date<'2023/06/01'){
            $this->addError($attribute, "查询时间不能小于2023年6月");
        }elseif($this->start_date>date("Y/m/d")){
            $this->addError($attribute, "查询时间不能大于".date("Y年n月"));
        }
    }

    public function setCriteria($criteria)
    {
        if (count($criteria) > 0) {
            foreach ($criteria as $k=>$v) {
                $this->$k = $v;
            }
        }
    }

    public function getCriteria() {
        return array(
            'search_year'=>$this->search_year,
            'search_month'=>$this->search_month
        );
    }

    //获取KA所有员工
    protected function getKaManForKaBot(){
	    $maxYear = $this->search_year;
        $suffix = Yii::app()->params['envSuffix'];
        $table_pre = $this->table_pre;
        $city_allow = Yii::app()->user->city_allow();
        $whereSql = "a.id>0 ";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";
        if(Yii::app()->user->validFunction($this->function_id)){
            $whereSql.= " and (h.staff_status!=-1 or (h.staff_status=-1 and DATE_FORMAT(h.leave_time,'%Y')>={$maxYear}))";//2023/06/16 改為可以看的所有記錄
        }elseif(Yii::app()->user->validFunction('CN19')){
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and (a.kam_id in ({$idSQL}) or a.support_user in ({$idSQL}) or h.city in ({$city_allow}))";
        }else{
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and a.kam_id in ({$idSQL})";
        }
        $rows = Yii::app()->db->createCommand()
            ->select("h.id,h.code,h.name,h.city,h.entry_time,h.table_type")
            ->from("sal{$table_pre}bot a")
            ->leftJoin("hr{$suffix}.hr_employee h","a.kam_id=h.id")
            ->where($whereSql)
            ->group("h.id,h.code,h.name,h.city,h.entry_time,h.table_type")
            ->order("h.table_type asc,h.city,h.id")
            ->queryAll();
        return $rows?$rows:array();
    }

    //获取未来90天的金额及数量
    protected function getAmtNumFor90(){
        $suffix = Yii::app()->params['envSuffix'];
        $table_pre = $this->table_pre;
        $list = array();
        $conList = array(
            "sign_90_num"=>0,//未来90天数量
            "sign_90_amt"=>0,//未来90天金额
            "sign_this_num"=>0,//本月数量
            "sign_this_amt"=>0,//本月金額
        );
        $startDate = date("Y-m-d",strtotime($this->start_date));
        $endDate = date("Y-m-d",strtotime($this->start_date." + 3 months - 1 days"));
        $whereSql = "a.available_date BETWEEN '{$startDate}' and '{$endDate}' ";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";
        $city_allow = Yii::app()->user->city_allow();
        if(Yii::app()->user->validFunction($this->function_id)){
            $whereSql.= "";//2023/06/16 改為可以看的所有記錄
        }elseif(Yii::app()->user->validFunction('CN19')){
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and (a.kam_id in ({$idSQL}) or a.support_user in ({$idSQL}) or h.city in ({$city_allow}))";
        }else{
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and a.kam_id in ({$idSQL})";
        }
        $amtSql = "IFNULL(a.available_amt,0)";
        $rows = Yii::app()->db->createCommand()
            ->select("a.kam_id,
                count(a.id) as sign_90_num,
                sum(if(a.sign_odds<=80,{$amtSql}*0.5,0)) as sign_amt_one,
                sum(if(a.sign_odds>80,{$amtSql},0)) as sign_amt_two
            ")->from("sal{$table_pre}bot a")
            ->leftJoin("sal_ka_link b","a.link_id=b.id")
            ->leftJoin("hr{$suffix}.hr_employee h","a.kam_id=h.id")
            ->where($whereSql." and b.rate_num<100 and a.sign_odds>50 and a.sign_odds<100")
            ->group("a.kam_id")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $employee_id = $row["kam_id"];
                if(!key_exists($employee_id,$list)){
                    $list[$employee_id]=$conList;
                }
                $list[$employee_id]["sign_90_num"]+= $row["sign_90_num"];
                $list[$employee_id]["sign_90_amt"]+= $row["sign_amt_one"];
                $list[$employee_id]["sign_90_amt"]+= $row["sign_amt_two"];
            }
        }
        //本月、未來90天需要添加溝通100%的金額
        $searchDate = date("Y/m",strtotime($this->start_date));
        $whereSql = "DATE_FORMAT(f.ava_date,'%Y/%m')='{$searchDate}' and b.rate_num=100 and f.ava_rate>50";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";
        if(Yii::app()->user->validFunction($this->function_id)){
            $whereSql.= "";//2023/06/16 改為可以看的所有記錄
        }elseif(Yii::app()->user->validFunction('CN19')){
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and (a.kam_id in ({$idSQL}) or a.support_user in ({$idSQL}) or h.city in ({$city_allow}))";
        }else{
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and a.kam_id in ({$idSQL})";
        }
        $amtSql = "IFNULL(f.ava_amt,0)";
        $rows = Yii::app()->db->createCommand()
            ->select("a.kam_id,
                count(f.id) as amt_all,
                sum(if(f.ava_rate<=80,{$amtSql}*0.5,0)) as amt_one,
                sum(if(f.ava_rate>80,{$amtSql},0)) as amt_two,
                sum(if(f.ava_rate>80,1,0)) as amt_80,
                sum(if(f.ava_rate>50,{$amtSql},0)) as sign_this_amt,
                sum(if(f.ava_rate>50,1,0)) as sign_this_num
            ")->from("sal{$table_pre}bot_ava f")
            ->leftJoin("sal{$table_pre}bot a","f.bot_id=a.id")
            ->leftJoin("sal_ka_link b","a.link_id=b.id")
            ->leftJoin("hr{$suffix}.hr_employee h","a.kam_id=h.id")
            ->where($whereSql)
            ->group("a.kam_id")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $employee_id = $row["kam_id"];
                if(!key_exists($employee_id,$list)){
                    $list[$employee_id]=$conList;
                }
                //未來90天金額
                $list[$employee_id]["sign_90_num"]+= $row["amt_all"];
                $list[$employee_id]["sign_90_amt"]+= $row["amt_one"];
                $list[$employee_id]["sign_90_amt"]+= $row["amt_two"];
                //本月金額
                $list[$employee_id]["sign_this_num"]+= $row["sign_this_num"];
                $list[$employee_id]["sign_this_amt"]+= $row["sign_this_amt"];
            }
        }
        return $list;
    }

    //获取YTD、MTD的金额及数量
    protected function getAmtNumForYM(){
        $suffix = Yii::app()->params['envSuffix'];
        $table_pre = $this->table_pre;
        $list = array();
        $conList = array(
            "ytd_num"=>0,
            "ytd_amt"=>0,
            "mtd_num"=>0,
            "mtd_amt"=>0,
        );
        $whereSql = "DATE_FORMAT(f.ava_date,'%Y')='{$this->ka_year}' and b.rate_num=100";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";
        $city_allow = Yii::app()->user->city_allow();
        if(Yii::app()->user->validFunction($this->function_id)){
            $whereSql.= "";//2023/06/16 改為可以看的所有記錄
        }elseif(Yii::app()->user->validFunction('CN19')){
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and (a.kam_id in ({$idSQL}) or a.support_user in ({$idSQL}) or h.city in ({$city_allow}))";
        }else{
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and a.kam_id in ({$idSQL})";
        }
        $searchDate = date("Y/m",strtotime($this->start_date));
        $amtSql = "IFNULL(f.ava_fact_amt,0)";
        $sqlText = Yii::app()->db->createCommand()
            ->select("a.id,a.kam_id,
                sum({$amtSql}) as ytd_amt,
            
                sum(if(DATE_FORMAT(f.ava_date,'%Y/%m')='{$searchDate}',1,0)) as mtd_num,
                sum(if(DATE_FORMAT(f.ava_date,'%Y/%m')='{$searchDate}',{$amtSql},0)) as mtd_amt
            ")->from("sal{$table_pre}bot_ava f")
            ->leftJoin("sal{$table_pre}bot a","f.bot_id=a.id")
            ->leftJoin("sal_ka_link b","a.link_id=b.id")
            ->leftJoin("hr{$suffix}.hr_employee h","a.kam_id=h.id")
            ->where($whereSql)
            ->group("a.id,a.kam_id")
            ->getText();
        $countRows = Yii::app()->db->createCommand()
            ->select("bot.kam_id,
                count(bot.id) as ytd_num,
                sum(bot.ytd_amt) as ytd_amt,
                
                sum(if(bot.mtd_num>0,1,0)) as mtd_num,
                sum(bot.mtd_amt) as mtd_amt
            ")->from("({$sqlText}) bot")
            ->group("bot.kam_id")
            ->queryAll();
        if($countRows){
            foreach ($countRows as $countRow){
                $employee_id = $countRow["kam_id"];
                if(!key_exists($employee_id,$list)){
                    $list[$employee_id]=$conList;
                }
                $list[$employee_id]["ytd_num"]+= $countRow["ytd_num"];
                $list[$employee_id]["ytd_amt"]+= $countRow["ytd_amt"];
                $list[$employee_id]["mtd_num"]+= $countRow["mtd_num"];
                $list[$employee_id]["mtd_amt"]+= $countRow["mtd_amt"];
            }
        }
        return $list;
    }

    //获取拜访、报价、本月的金额及数量
    protected function getAmtNumForVQS(){
        $suffix = Yii::app()->params['envSuffix'];
        $table_pre = $this->table_pre;
	    $list = array();
	    $conList = array(
            "visit_num"=>0,//拜访数量
            "visit_amt"=>0,//拜访金额
            "quota_num"=>0,//报价数量
            "quota_amt"=>0,//报价金额
            "sign_this_num"=>0,//本月数量
            "sign_this_amt"=>0,//本月金额
        );
        $whereSql = "DATE_FORMAT(a.available_date,'%Y')='{$this->ka_year}'";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";
        if(Yii::app()->user->validFunction($this->function_id)){
            $whereSql.= "";//2023/06/16 改為可以看的所有記錄
        }elseif(Yii::app()->user->validFunction('CN19')){
            $city_allow = Yii::app()->user->city_allow();
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and (a.kam_id in ({$idSQL}) or a.support_user in ({$idSQL}) or h.city in ({$city_allow}))";
        }else{
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and a.kam_id in ({$idSQL})";
        }
        $searchDate = $this->start_date;

        $amtSql = "IFNULL(a.available_amt,0)";
        //$dateIFSql = "a.available_date<='{$this->end_date}' and IFNULL(a.available_date,a.apply_date)>='{$this->start_date}'";
        $rows = Yii::app()->db->createCommand()
            ->select("a.kam_id,
                count(a.id) as visit_num,
                sum({$amtSql}) as visit_amt,
                
                sum(if(b.rate_num>=30,1,0)) as quota_num,
                sum(if(b.rate_num>=30,{$amtSql},0)) as quota_amt,
                
                sum(if(b.rate_num<100 and a.sign_odds>80 and a.sign_odds<100 and DATE_FORMAT(a.available_date,'%Y/%m/01')='{$searchDate}',1,0)) as sign_this_num,
                sum(if(b.rate_num<100 and a.sign_odds>80 and a.sign_odds<100 and DATE_FORMAT(a.available_date,'%Y/%m/01')='{$searchDate}',{$amtSql},0)) as sign_this_amt
                
            ")->from("sal{$table_pre}bot a")
            ->leftJoin("sal_ka_link b","a.link_id=b.id")
            ->leftJoin("hr{$suffix}.hr_employee h","a.kam_id=h.id")
            ->where($whereSql)
            ->group("a.kam_id")
            ->queryAll();

        if($rows){
            foreach ($rows as $row){
                $employee_id = $row["kam_id"];
                if(!key_exists($employee_id,$list)){
                    $list[$employee_id]=$conList;
                }
                $list[$employee_id]["visit_num"]+= $row["visit_num"];
                $list[$employee_id]["visit_amt"]+= $row["visit_amt"];
                $list[$employee_id]["quota_num"]+= $row["quota_num"];
                $list[$employee_id]["quota_amt"]+= $row["quota_amt"];
                $list[$employee_id]["sign_this_num"]+= $row["sign_this_num"];
                $list[$employee_id]["sign_this_amt"]+= $row["sign_this_amt"];
            }
        }
        return $list;
    }

    public function getKASalesGroup(){
        $suffix = Yii::app()->params['envSuffix'];
        $groupList = array();
        $rows = Yii::app()->db->createCommand()->select("a.employee_id,a.group_id,b.group_name")
            ->from("hr{$suffix}.hr_group_staff a")
            ->leftJoin("hr{$suffix}.hr_group b","a.group_id=b.id")
            ->where("b.group_code='KAGROUP'")
            ->order("a.group_id asc")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $groupList[$row["employee_id"]] = $row;
            }
        }
        return $groupList;
    }

    public function getKAIndicatorList($date='')
    {
        $date = empty($date)?date_format(date_create(),"Y-m-01"):date_format(date_create($date),"Y-m-01");
        $list = array();
        $sql = "select employee_id,indicator_money from sal_ka_idx where DATE_FORMAT(effect_date,'%Y-%m-01')<='{$date}' order by effect_date asc";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if ($rows) {
            foreach ($rows as $row){
                $list[$row["employee_id"]] = array("idx_sales_money"=>floatval($row['indicator_money']));
            }
        }
        return $list;
    }

    public function getAmtNumForRenewal()
    {
        $table_pre = $this->table_pre;
        $list = array();
        $rows = Yii::app()->db->createCommand()
            ->select("a.bot_id,b.kam_id,sum(a.renewal_amt) as renewal_total_amt")
            ->from("sal{$table_pre}bot_renewal a")
            ->leftJoin("sal{$table_pre}bot b","a.bot_id=b.id")
            ->where("DATE_FORMAT(a.renewal_date,'%Y')='{$this->search_year}'")
            ->group("a.bot_id,b.kam_id")
            ->queryAll();
        if ($rows) {
            foreach ($rows as $row){
                $kamID = "".$row["kam_id"];
                if(!key_exists($kamID,$list)){
                    $list[$kamID]=array("renewal_total_amt"=>0,"renewal_total_sum"=>0);
                }
                $list[$kamID]["renewal_total_sum"]++;
                $list[$kamID]["renewal_total_amt"]+= $row["renewal_total_amt"];
            }
        }
        return $list;
    }

    //次月预估
    protected function getSignNextNumAmt(){
        $suffix = Yii::app()->params['envSuffix'];
        $table_pre = $this->table_pre;
        $list = array();
        $conList = array(
            "sign_next_num"=>0,//次月数量
            "sign_next_amt"=>0,//次月金額
        );
        $searchDate = date("Y/m",strtotime("{$this->start_date} + 1 months"));
        $whereSql = "DATE_FORMAT(a.available_date,'%Y/%m')='{$searchDate}' and a.sign_odds>50 and a.sign_odds<100 ";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";
        $city_allow = Yii::app()->user->city_allow();
        if(Yii::app()->user->validFunction($this->function_id)){
            $whereSql.= "";//2023/06/16 改為可以看的所有記錄
        }elseif(Yii::app()->user->validFunction('CN19')){
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and (a.kam_id in ({$idSQL}) or a.support_user in ({$idSQL}) or h.city in ({$city_allow}))";
        }else{
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and a.kam_id in ({$idSQL})";
        }
        $amtSql = "IFNULL(a.available_amt,0)";
        $rows = Yii::app()->db->createCommand()
            ->select("a.kam_id,
                count(a.id) as sign_next_num,
                sum({$amtSql}) as sign_next_amt
            ")->from("sal{$table_pre}bot a")
            ->leftJoin("sal_ka_link b","a.link_id=b.id")
            ->leftJoin("hr{$suffix}.hr_employee h","a.kam_id=h.id")
            ->where($whereSql)
            ->group("a.kam_id")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $employee_id = $row["kam_id"];
                if(!key_exists($employee_id,$list)){
                    $list[$employee_id]=$conList;
                }
                $list[$employee_id]["sign_next_num"]+= $row["sign_next_num"];
                $list[$employee_id]["sign_next_amt"]+= $row["sign_next_amt"];
            }
        }
        //计算详情表内的预估金额
        $whereSql = "DATE_FORMAT(f.ava_date,'%Y/%m')='{$searchDate}' and b.rate_num=100 and f.ava_rate>50";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";
        if(Yii::app()->user->validFunction($this->function_id)){
            $whereSql.= "";//2023/06/16 改為可以看的所有記錄
        }elseif(Yii::app()->user->validFunction('CN19')){
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and (a.kam_id in ({$idSQL}) or a.support_user in ({$idSQL}) or h.city in ({$city_allow}))";
        }else{
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and a.kam_id in ({$idSQL})";
        }
        $amtSql = "IFNULL(f.ava_amt,0)";
        $rows = Yii::app()->db->createCommand()
            ->select("a.kam_id,
                sum({$amtSql}) as sign_next_amt,
                count(f.id) as sign_next_num
            ")->from("sal{$table_pre}bot_ava f")
            ->leftJoin("sal{$table_pre}bot a","f.bot_id=a.id")
            ->leftJoin("sal_ka_link b","a.link_id=b.id")
            ->leftJoin("hr{$suffix}.hr_employee h","a.kam_id=h.id")
            ->where($whereSql)
            ->group("a.kam_id")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $employee_id = $row["kam_id"];
                if(!key_exists($employee_id,$list)){
                    $list[$employee_id]=$conList;
                }
                //次月金額
                $list[$employee_id]["sign_next_num"]+= $row["sign_next_num"];
                $list[$employee_id]["sign_next_amt"]+= $row["sign_next_amt"];
            }
        }
        return $list;
    }

    public function retrieveData() {
        $this->data=array();
        $listVQS = $this->getAmtNumForVQS();//获取拜访、报价、本月的金额及数量
        $list90 = $this->getAmtNumFor90();//获取未来90天的金额及数量
        $listNext = $this->getSignNextNumAmt();//获取次月的金额及数量
        $listYM = $this->getAmtNumForYM();//获取YTD、MTD的金额及数量
        $kaManList = $this->getKaManForKaBot();//KA所有员工
        $kaGroupList = $this->getKASalesGroup();//KA分组
        $kaIDXList = $this->getKAIndicatorList($this->start_date);//KA个人指标金额
        $renewalList = $this->getAmtNumForRenewal();//获取续约金额及数量
        $data=array("group"=>array(),"staff"=>array());//排序，分组的员工置顶
        foreach ($kaManList as $row){
            $temp = $this->getTemp();
            $ka_id = $row["id"];
            $city = $row["city"];
            if(key_exists($ka_id,$kaGroupList)){
                $keyStr = "group";
                $group_id = $kaGroupList[$ka_id]["group_id"];
                $temp["group_name"] = $kaGroupList[$ka_id]["group_name"];
            }else{
                $keyStr = "staff";
                $group_id = $city."_".$ka_id;
            }
            $temp["employee_id"] = $ka_id;
            $temp["entry_date"] = General::toDate($row["entry_time"]);
            $temp["kam_name"] = $row["name"]." ({$row["code"]})";
            $this->addTempForList($temp,$listVQS,$ka_id);
            $this->addTempForList($temp,$list90,$ka_id);
            $this->addTempForList($temp,$listNext,$ka_id);
            $this->addTempForList($temp,$listYM,$ka_id);
            $this->addTempForList($temp,$kaIDXList,$ka_id);
            $this->addTempForList($temp,$renewalList,$ka_id);

            $data[$keyStr][$group_id][$ka_id] = $temp;
        }
        $this->data = $data["group"];
        if(!empty($data["staff"])){
            foreach ($data["staff"] as $key=>$row){
                $this->data[$key]=$row;
            }
        }

        $session = Yii::app()->session;
        $session['kAStatistic_c01'] = $this->getCriteria();
        return true;
    }

    protected function addTempForList(&$temp,$list,$ka_id){
        if(key_exists($ka_id,$list)){
            foreach ($list[$ka_id] as $key=>$item){
                if(key_exists($key,$temp)){
                    if(is_numeric($temp[$key])){
                        $temp[$key]+= $item;
                    }else{
                        $temp[$key] = $item;
                    }
                }
            }
        }
    }

    protected function getTemp(){
        return array(
            "group_name"=>"独立组",//员工分组
            "employee_id"=>"",//KA_id
            "kam_name"=>"",//KA名称
            "entry_date"=>"",//入职日期
            "visit_num"=>0,//拜访数量
            "visit_amt"=>0,//拜访金额
            "quota_num"=>0,//报价数量
            "quota_amt"=>0,//报价金额
            "qv_num_rate"=>"",//转化率（报价数量/拜访数量）
            "qv_amt_rate"=>"",//转化率（报价金额/拜访金额）
            "sq_num_rate"=>"",//转化率（签约数量/报价数量）
            "sq_amt_rate"=>"",//转化率（签约金额/报价金额）
            "sv_num_rate"=>"",//转化率（签约数量/拜访数量）
            "sv_amt_rate"=>"",//转化率（签约金额/拜访金额）

            "sign_this_num"=>0,//本月数量
            "sign_this_amt"=>0,//本月金额
            "sign_next_num"=>0,//次月预估(数量)
            "sign_next_amt"=>0,//次月预估(金额)
            "sign_90_num"=>0,//未来90天数量
            "sign_90_amt"=>0,//未来90天金额
            "this_rate"=>"",//90天转化率（本月金额/90天金额）

            "mtd_num"=>0,//mtd数量
            "mtd_amt"=>0,//mtd金额
            "mtd_idx"=>0,//MTD指标
            "mtd_idx_rate"=>0,//MTD达成率（签约/指标）

            "ytd_num"=>0,//ytd数量
            "ytd_amt"=>0,//ytd金额
            "idx_sales_money"=>0,//个人指标金额
            "idx_sales_rate"=>"",//个人指标比例

            "group_amt"=>0,//团队金额
            "idx_group_money"=>0,//团队指标金额
            "idx_group_rate"=>"",//团队指标比例
            "renewal_total_sum"=>0,//续约数量
            "renewal_total_amt"=>0,//续约金额
        );
    }

    public static function getRateForNumber($number){
        $rate = "";
        if(is_numeric($number)){
            $rate = $number*100;
            $rate = round($rate);
            $rate = "".$rate."%";
        }
        return $rate;
    }

    public static function getRateForCompute($num,$sum){
        $rate = empty($sum)?0:$num/$sum;
        return self::getRateForNumber($rate);
    }

    protected function resetTdRow(&$list,$bool=false){
        $list["qv_num_rate"] = self::getRateForCompute($list["quota_num"],$list["visit_num"]);
        $list["qv_amt_rate"] = self::getRateForCompute($list["quota_amt"],$list["visit_amt"]);
        $list["sq_num_rate"] = self::getRateForCompute($list["ytd_num"],$list["quota_num"]);
        $list["sq_amt_rate"] = self::getRateForCompute($list["ytd_amt"],$list["quota_amt"]);
        $list["sv_num_rate"] = self::getRateForCompute($list["ytd_num"],$list["visit_num"]);
        $list["sv_amt_rate"] = self::getRateForCompute($list["ytd_amt"],$list["visit_amt"]);
        $list["this_rate"] = self::getRateForCompute($list["sign_this_amt"],$list["sign_90_amt"]);

        $list["mtd_idx"]=empty($list["idx_sales_money"])?0:round($list["idx_sales_money"]/12,2);
        $list["mtd_idx_rate"]=self::getRateForCompute($list["mtd_amt"],$list["mtd_idx"]);
        $list["idx_sales_rate"]=self::getRateForCompute($list["ytd_amt"],$list["idx_sales_money"]);
        //$list["idx_group_rate"]=self::getRateForCompute($list["group_amt"],$list["idx_group_money"]);
    }

    //顯示提成表的表格內容
    public function kAStatisticHtml(){
        $html= '<table id="kAStatistic" class="table table-fixed table-condensed table-bordered table-hover">';
        $html.=$this->tableTopHtml();
        $html.=$this->tableBodyHtml();
        $html.=$this->tableFooterHtml();
        $html.="</table>";
        return $html;
    }

    protected function getTopArr(){
        $topList=array(
            array("name"=>Yii::t("ka","KAM"),"background"=>"#305496","color"=>"#ffffff",
                "colspan"=>array(
                    array(
                        "name"=>Yii::t("ka","group"),//分组
                        "rowspan"=>2
                    ),
                    array(
                        "name"=>Yii::t("ka","KAM sale"),//KAM
                        "rowspan"=>2
                    ),
                    array(
                        "name"=>Yii::t("ka","entry date"),//入职日期
                        "rowspan"=>2
                    )
                )
            ),//ka销售
            array("name"=>Yii::t("ka","YTD Potential Data"),"background"=>"#305496","color"=>"#ffffff",//YTD潜客转化数据
                "colspan"=>array(
                    array(
                        "name"=>Yii::t("ka","Visiting stage"),//拜访阶段
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity")),//数量
                            array("name"=>Yii::t("ka","Contract amt")),//合同金额
                        )
                    ),
                    array(
                        "name"=>Yii::t("ka","Quotation stage"),//报价阶段
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity")),//数量
                            array("name"=>Yii::t("ka","Contract amt")),//合同金额
                        )
                    ),
                    array(
                        "name"=>Yii::t("ka","YTD QV rate"),//YTD拜访-报价转化
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity").":".Yii::t("ka","Quotation/Visit")),//数量：报价/拜访
                            array("name"=>Yii::t("ka","amt").":".Yii::t("ka","Quotation/Visit")),//金额：报价/拜访
                        )
                    ),
                    array(
                        "name"=>Yii::t("ka","YTD SQ rate"),//YTD报价-签约转化
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity").":".Yii::t("ka","Sign/Quotation")),//数量：签约/报价
                            array("name"=>Yii::t("ka","amt").":".Yii::t("ka","Sign/Quotation")),//金额：签约/报价
                        )
                    ),
                    array(
                        "name"=>Yii::t("ka","YTD SV rate"),//YTD拜访-签约转化
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity").":".Yii::t("ka","Sign/Visit")),//数量：签约/拜访
                            array("name"=>Yii::t("ka","amt").":".Yii::t("ka","Sign/Visit")),//金额：签约/拜访
                        )
                    ),
                )
            ),//YTD潜客转化数据
            array("name"=>Yii::t("ka","YTD Potential predict"),"background"=>"#2A6BA4","color"=>"#ffffff",//未来90天加权报价金额
                "colspan"=>array(
                    array(
                        "name"=>$this->search_month.Yii::t("ka"," month predict"),//月份预估
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity")),//数量
                            array("name"=>Yii::t("ka","Contract amt")),//合同金额
                        )
                    ),
                    array(
                        "name"=>Yii::t("ka","QTD Next month predict"),//次月预估
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity")),//数量
                            array("name"=>Yii::t("ka","Contract amt")),//合同金额
                        )
                    ),
                    array(
                        "name"=>Yii::t("ka","predict for next 90 days"),//未来90天预估
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity")),//数量
                            array("name"=>Yii::t("ka","Contract amt")),//合同金额
                        )
                    ),
                    array(
                        "name"=>Yii::t("ka","success for next 90 days"),//未来90天达成
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","(Actual Sign this month/90 day weighted)")),//（本月签约/90天加权）
                        )
                    )
                )
            ),//QTD潜客预估
            array("name"=>Yii::t("ka","MTD personal data"),"background"=>"#4472C4","color"=>"#ffffff",//每月KA销售业绩
                "colspan"=>array(
                    array(
                        "name"=>$this->ka_month.Yii::t("ka"," month success"),//月份达成
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","MTD Sign")),//MTD签约
                            array("name"=>Yii::t("ka","MTD Indicator")),//MTD指标
                            array("name"=>Yii::t("ka","MTD rate(sign/indicator)")),//MTD达成率（签约/指标）
                            array("name"=>Yii::t("ka","Sign total")),//签约数量
                        )
                    ),
                )
            ),//MTD个人达成数据
            array("name"=>Yii::t("ka","YTD personal data"),"background"=>"#4472C4","color"=>"#ffffff",//每月KA销售业绩
                "colspan"=>array(
                    array(
                        "name"=>$this->search_year.Yii::t("ka"," success"),//年份达成
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","YTD Sign total")),//YTD签约数量
                            array("name"=>Yii::t("ka","YTD Sign amt")),//YTD签约金额
                            array("name"=>Yii::t("ka","YTD Indicator")),//YTD指标
                            array("name"=>Yii::t("ka","YTD rate(sign/indicator)")),//YTD达成率（签约/指标）
                        )
                    ),
                )
            ),//YTD个人达成数据
        );
        $topList[]=array("name"=>Yii::t("ka","YTD group data"),"background"=>"#4472C4","color"=>"#ffffff",
            "colspan"=>array(
                array(
                    "name"=>$this->search_year.Yii::t("ka"," success"),//2024达成
                    "colspan"=>array(
                        array("name"=>Yii::t("ka","YTD Indicator")),//YTD指标
                        array("name"=>Yii::t("ka","YTD rate")),//YTD达成率
                    )
                )
            )
        );//YTD团队数据
        $topList[]=array("name"=>Yii::t("ka","YTD for renewal"),"background"=>"#2A6BA4","color"=>"#ffffff",//本月可实现销售金额
                "colspan"=>array(
                    array(
                        "name"=>$this->search_year.Yii::t("ka"," renewal"),//年份
                        "colspan"=>array(
                            array("name"=>Yii::t("ka","quantity")),//数量
                            array("name"=>Yii::t("ka","amt")),//金额
                        )
                    )
                )
            );//YTD续约数据
        return $topList;
    }

    //顯示提成表的表格內容（表頭）
    protected function tableTopHtml(){
        $topList = self::getTopArr();
        $trOne="";
        $trTwo="";
        $trThree="";
        $html="<thead>";
        foreach ($topList as $list){
            $clickName=$list["name"];
            $colList=key_exists("colspan",$list)?$list['colspan']:array();
            $style = "";
            $colNum=0;
            if(key_exists("background",$list)){
                $style.="background:{$list["background"]};";
            }
            if(key_exists("color",$list)){
                $style.="color:{$list["color"]};";
            }
            if(!empty($colList)){
                foreach ($colList as $col){
                    $threeCol=key_exists("colspan",$col)?$col['colspan']:array();
                    if(!empty($threeCol)){
                        foreach ($threeCol as $three){
                            $this->th_sum++;
                            $trThree.="<th style='{$style}'><span>".$three["name"]."</span></th>";

                        }
                    }else{
                        $this->th_sum++;
                    }
                    $threeColNum=count($threeCol);
                    $colNum+=$threeColNum;
                    $threeColNum = empty($threeColNum)?1:$threeColNum;

                    if(key_exists("rowspan",$col)){
                        $trTwo.="<th colspan='{$threeColNum}' rowspan='{$col["rowspan"]}' style='{$style}'><span>".$col["name"]."</span></th>";
                    }else{
                        $trTwo.="<th colspan='{$threeColNum}' style='{$style}'><span>".$col["name"]."</span></th>";
                    }
                }
            }else{
                $this->th_sum++;
            }
            $colNum = empty($colNum)?count($colList):$colNum;
            $trOne.="<th style='{$style}' colspan='{$colNum}'";
            if(key_exists("rowspan",$list)){
                $trOne.=" rowspan='{$list["rowspan"]}'";
            }
            if(key_exists("startKey",$list)){
                $trOne.=" data-key='{$list['startKey']}'";
            }
            $trOne.=" ><span>".$clickName."</span></th>";
        }
        $html.=$this->tableHeaderWidth();//設置表格的單元格寬度
        $html.="<tr>{$trOne}</tr><tr>{$trTwo}</tr><tr>{$trThree}</tr>";
        $html.="</thead>";
        return $html;
    }

    //設置表格的單元格寬度
    protected function tableHeaderWidth(){
        $html="<tr>";
        for($i=0;$i<$this->th_sum;$i++){
            if($i==0){
                $width = 110;
            }elseif($i>=7&&$i<=12){
                $width=110;
            }else{
                $width=90;
            }
            $html.="<th class='header-width' data-width='{$width}' width='{$width}px'>{$i}</th>";
        }
        return $html."</tr>";
    }

    public function tableBodyHtml(){
        $html="";
        if(!empty($this->data)){
            $this->downJsonText=array();
            $html.="<tbody>";
            $html.=$this->showServiceHtml($this->data);
            $html.="</tbody>";
            $this->downJsonText=json_encode($this->downJsonText);
        }
        return $html;
    }
    //获取td对应的键名
    protected function getDataAllKeyStr(){
        $bodyKey = array(
            "group_name","kam_name","entry_date","visit_num","visit_amt","quota_num","quota_amt",
            "qv_num_rate","qv_amt_rate","sq_num_rate","sq_amt_rate","sv_num_rate","sv_amt_rate",
            "sign_this_num","sign_this_amt","sign_next_num","sign_next_amt","sign_90_num","sign_90_amt","this_rate",
            "mtd_amt","mtd_idx","mtd_idx_rate","mtd_num",
            "ytd_num","ytd_amt","idx_sales_money","idx_sales_rate",
            "idx_group_money","idx_group_rate",
            "renewal_total_sum","renewal_total_amt"
        );
        return $bodyKey;
    }

    public static function showNum($num){
        $pre="";
        if (strpos($num," +")!==false){
            $pre=" +";
            $num = end(explode(" +",$num));
        }
        if (is_numeric($num)){
            $number = floatval($num);
            //$number=sprintf("%.2f",$number);
        }else{
            $number = $num;
        }
        return $pre.$number;
    }

    //將城市数据寫入表格
    protected function showServiceHtml($data){
        $bodyKey = $this->getDataAllKeyStr();
        $clickTdList = $this->getClickTdList();
        $html="";
        if(!empty($data)){
            $allRow = [];//总计(所有地区)
            foreach ($data as $city=>$row){
                $currentRow = $row;
                $staff_id=array_shift($currentRow)["employee_id"];
                $rowspan = count($row);
                $regionRow = ["idx_group_money"=>0,"group_amt"=>0];//分组汇总
                foreach ($row as $list){
                    $id = $list["employee_id"];
                    $this->resetTdRow($list);
                    $html.="<tr>";
                    foreach ($bodyKey as $keyStr){
                        if(in_array($keyStr,array("idx_group_money","idx_group_rate"))){
                            $this->downJsonText["excel"][$city][$staff_id][$keyStr]=0;
                            $html.=($keyStr=="idx_group_money"&&$staff_id==$id)?":groupMoneyHtml:":"";
                            continue;
                        }
                        $text = key_exists($keyStr,$list)?$list[$keyStr]:"0";
                        if($keyStr=="ytd_amt"){
                            $regionRow["group_amt"]+=is_numeric($text)?floatval($text):0;
                        }
                        if($keyStr=="idx_sales_money"){
                            $regionRow["idx_group_money"]+=is_numeric($text)?floatval($text):0;
                        }
                        if(!key_exists($keyStr,$allRow)){
                            $allRow[$keyStr]=0;
                        }
                        $allRow[$keyStr]+=is_numeric($text)?floatval($text):0;
                        $text = self::showNum($text);
                        $this->downJsonText["excel"][$city][$id][]=$text;
                        $class="";
                        $title="";
                        if(key_exists($keyStr,$clickTdList)){
                            $class.=" td_detail";
                            $title=$clickTdList[$keyStr];
                        }
                        $html.="<td class='{$class}' data-title='{$title}' data-type='{$keyStr}' data-employee_id='{$list['employee_id']}'>";
                        $html.="<span>{$text}</span></td>";
                        $html.="</td>";
                    }
                    $html.="</tr>";
                }

                if(in_array("idx_group_money",$bodyKey)){
                    $regionRow["idx_group_rate"] = empty($regionRow["idx_group_money"])?0:($regionRow["group_amt"]/$regionRow["idx_group_money"]);
                    $regionRow["idx_group_rate"] = self::getRateForNumber($regionRow["idx_group_rate"]);
                    $groupHtml="<td rowspan='{$rowspan}'>".$regionRow["idx_group_money"]."</td>";
                    $groupHtml.="<td rowspan='{$rowspan}'>".$regionRow["idx_group_rate"]."</td>";
                    $html=str_replace(":groupMoneyHtml:", $groupHtml, $html);
                    $this->downJsonText["excel"][$city][$staff_id]['idx_group_money']=array("groupLen"=>$rowspan,"text"=>$regionRow["idx_group_money"]);
                    $this->downJsonText["excel"][$city][$staff_id]['idx_group_rate']=array("groupLen"=>$rowspan,"text"=>$regionRow["idx_group_rate"]);
                }
            }
            //所有汇总
            $allRow["city"]="_ALL";
            $allRow["group_name"]="";
            $allRow["entry_date"]="";
            $allRow["idx_group_money"]="";
            $allRow["idx_group_rate"]="";
            $allRow["kam_name"]=Yii::t("ka","all total");
            $html.=$this->printTableTr($allRow,$bodyKey);
            $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
            $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
        }
        return $html;
    }

    protected function printTableTr($data,$bodyKey){
        $this->resetTdRow($data,true);
        $html="<tr class='tr-end click-tr'>";
        foreach ($bodyKey as $keyStr){
            $text = key_exists($keyStr,$data)?$data[$keyStr]:"0";
            $tdClass = ComparisonForm::getTextColorForKeyStr($text,$keyStr);
            $text = self::showNum($text);
            $this->downJsonText["excel"][$data['city']]["count"][]=$text;
            $html.="<td class='{$tdClass}' style='font-weight: bold'><span>{$text}</span></td>";
        }
        $html.="</tr>";
        return $html;
    }

    public function tableFooterHtml(){
        $html="<tfoot>";
        $html.="<tr class='tr-end'><td colspan='{$this->th_sum}'>&nbsp;</td></tr>";
        $html.="</tfoot>";
        return $html;
    }

    //下載YTD
    public function downYTD(){
        $excelData = $this->getYTDDownData();
        $headList = $this->getYTDDownHeader();
        $excel = new DownKAExcel();
        $excel->colTwo=1;
        $title = $this->ka_year."年".Yii::t("ka","YTD Rpt");
        $excel->SetHeaderTitle($title);
        $excel->SetHeaderString($title);
        $excel->init();
        $excel->setYTDHeader($headList);
        $excel->setKAData($excelData);
        $excel->outExcel($title);
    }

    protected function getYTDDownHeader(){
        $list = array(
            array("width"=>30,"background"=>"#305496","color"=>"#ffffff","name"=>"KA销售"),
            array("width"=>30,"background"=>"#305496","color"=>"#ffffff","name"=>"客户编号"),
            array("width"=>30,"background"=>"#305496","color"=>"#ffffff","name"=>"客户名称"),
        );
        for($i=1;$i<=12;$i++){
            $list[]=array("width"=>15,"background"=>"#2A6BA4","color"=>"#ffffff","name"=>$this->ka_year."年{$i}月");
        }
        $list[]=array("width"=>15,"background"=>"#4472C4","color"=>"#ffffff","name"=>"统计");
        return $list;
    }

    protected function getYTDDownData(){
        $table_pre = $this->table_pre;
        $this->ka_year = $this->search_year;
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = "DATE_FORMAT(f.ava_date,'%Y')='{$this->ka_year}'";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";
        if(Yii::app()->user->validFunction($this->function_id)){
            $whereSql.= "";//2023/06/16 改為可以看的所有記錄
        }elseif(Yii::app()->user->validFunction('CN19')){
            $city_allow = Yii::app()->user->city_allow();
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and (a.kam_id in ({$idSQL}) or a.support_user in ({$idSQL}) or h.city in ({$city_allow}))";
        }else{
            KABotForm::validateEmployee($this);
            $idSQL = KABotForm::getGroupIDStrForEmployeeID($this->employee_id);
            $whereSql.= " and a.kam_id in ({$idSQL})";
        }
        $whereSql.= " and g.rate_num=100";

        $selectText="a.id,a.customer_no,a.customer_name,b.name,b.code
        ,f.ava_fact_amt,f.ava_date";
        $rows = Yii::app()->db->createCommand()
            ->select($selectText)
            ->from("sal{$table_pre}bot_ava f")
            ->leftJoin("sal{$table_pre}bot a","f.bot_id=a.id")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->leftJoin("hr{$suffix}.hr_employee b","a.kam_id = b.id")
            ->where($whereSql)
            ->queryAll();
        $data=array();
        $dateTemp = array();
        $countList = array("ka_man"=>"","customer_no"=>"","customer_name"=>"汇总：");
        for ($i=1;$i<=12;$i++){
            $dateTemp["{$this->ka_year}/{$i}"]="";
            $countList["{$this->ka_year}/{$i}"]=0;
        }
        $countList["count"] = 0;
        if($rows){
            $data["city"]=array();
            foreach ($rows as $row){
                $id = $row["id"];
                $dateKey = date("Y/n",strtotime($row["ava_date"]));
                $money = empty($row["ava_fact_amt"])?0:floatval($row["ava_fact_amt"]);
                if(!key_exists($id,$data["city"])){
                    $temp=array(
                        "ka_man"=>$row["name"]." ({$row['code']})",
                        "customer_no"=>$row["customer_no"],
                        "customer_name"=>$row["customer_name"],
                    );
                    $temp = array_merge($temp,$dateTemp);
                    $temp["count"]=0;
                    $data["city"][$id]=$temp;
                }
                if(is_numeric($data["city"][$id][$dateKey])){
                    $data["city"][$id][$dateKey]+=$money;
                }else{
                    $data["city"][$id][$dateKey]=$money;
                }
                $data["city"][$id]["count"]+=$money;
                $countList[$dateKey]+=$money;
                $countList["count"]+=$money;
            }
            $data["city"]["count"]=$countList;
        }
        return $data;
    }

    //下載
    public function downExcel($excelData){
        if(!is_array($excelData)){
            $excelData = json_decode($excelData,true);
            $excelData = empty($excelData)?array():$excelData;
            $excelData = key_exists("excel",$excelData)?$excelData["excel"]:array();
        }
        $this->validateDate("","");
        $headList = $this->getTopArr();
        $excel = new DownKAExcel();
        $excel->colTwo=0;
        $excel->SetHeaderTitle(Yii::t("app","KA Statistic"));
        $excel->SetHeaderString($this->start_date." ~ ".$this->end_date);
        $excel->init();
        $excel->setKAHeader($headList);
        $excel->setKAData($excelData);
        $excel->outExcel(Yii::t("app","KA Statistic"));
    }

    //获取年份
    public static function getYearList(){
        $year = date("Y");
        $list = array();
        for ($i=$year-4;$i<=$year+1;$i++){
            if($i>2022){
                $list[$i] = $i.Yii::t("ka"," year");
            }
        }
        return $list;
    }
    //获取月份
    public static function getMonthList(){
        $list = array();
        for ($i=1;$i<=12;$i++){
            $list[$i] = $i.Yii::t("ka"," month");
        }
        return $list;
    }

    //顯示表格內的數據來源
    public function ajaxDetailForHtml(){
        $suffix = Yii::app()->params['envSuffix'];
        $employee_id = key_exists("employee_id",$_GET)?$_GET["employee_id"]:0;
        $this->search_year = key_exists("year",$_GET)?$_GET["year"]:0;
        $this->search_month = key_exists("month",$_GET)?$_GET["month"]:0;
        $this->ka_year = $this->search_year;
        $this->ka_month = $this->search_month;
        $type = key_exists("type",$_GET)?$_GET["type"]:0;
        $this->validateDate("","");

        $row = Yii::app()->db->createCommand()->select("id,name,code")->from("hr$suffix.hr_employee")
            ->where("id=:id",array(":id"=>$employee_id))->queryRow();
        $this->employee_id = $row["id"];
        $this->employee_code = $row["code"];
        $this->employee_name = $row["name"];

        if(!$row||!key_exists($type,$this->getClickTdList())){
            return "<p>数据异常，请刷新重试</p>";
        }
        $value = $type."_table";
        $html = "<table class='table table-bordered table-striped table-hover'>";
        $html.=$this->$value();
        $html.="</table>";
        return $html;
    }

    //需要顯示表格詳情的欄位
    protected function getClickTdList(){
        return array(
            "visit_num"=>Yii::t("ka","Visiting stage"),//拜访阶段
            "quota_num"=>Yii::t("ka","Quotation stage"),//报价阶段
            "sign_90_num"=>Yii::t("ka","predict for next 90 days"),//未来90天加权报价金额
            "sign_this_num"=>$this->search_month.Yii::t("ka"," month predict"),//本月可实现销售金额
            "sign_next_num"=>Yii::t("ka","QTD Next month predict"),//次月预估
            "ytd_num"=>Yii::t("ka","YTD"),//YTD
            "mtd_num"=>$this->search_month.Yii::t("ka","Month MTD"),//月MTD
            "renewal_total_sum"=>$this->search_year.Yii::t("ka"," renewal"),//续约
        );
    }

    //未来90天加权报价金额(签约概率>=51)
    protected function sign_90_num_table(){
        $table_pre = $this->table_pre;
        $searchDate = date("Y/m",strtotime($this->start_date));
        $startDate = date("Y-m-d",strtotime($this->start_date));
        $endDate = date("Y-m-d",strtotime($this->start_date." + 3 months - 1 days"));
        $whereSql = "a.available_date BETWEEN '{$startDate}' and '{$endDate}' ";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";
        $whereSql.= " and a.kam_id='{$this->employee_id}' and g.rate_num<100 and a.sign_odds>50 and a.sign_odds<100 ";

        $amtSql = "IFNULL(a.available_amt,0)";
        //$dateIFSql = "a.available_date<='{$this->end_date}' and IFNULL(a.available_date,a.apply_date)>='{$this->start_date}'";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,a.available_amt,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,g.rate_num
                ")->from("sal{$table_pre}bot a")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->order("if(g.rate_num>0,a.available_date,-1) desc,a.available_date desc")
            ->queryAll();
        $rows =$rows?$rows:array();

        //需要加上沟通100%的数据
        $whereSql = "DATE_FORMAT(f.ava_date,'%Y/%m')='{$searchDate}'";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";
        $whereSql.= " and a.kam_id='{$this->employee_id}' and g.rate_num=100 and f.ava_rate>50";

        $amtSql = "IFNULL(f.ava_amt,0)";
        $selectText="a.id,a.kam_id,f.ava_rate as sign_odds,f.ava_date as available_date,a.apply_date,a.customer_no,
        a.customer_name,a.contact_user,g.pro_name,g.rate_num";
        $rowsTwo = Yii::app()->db->createCommand()
            ->select("{$selectText},
            CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,
            {$amtSql} as available_amt")
            ->from("sal{$table_pre}bot_ava f")
            ->leftJoin("sal{$table_pre}bot a","f.bot_id=a.id")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->queryAll();
        $rowsTwo =$rowsTwo?$rowsTwo:array();
        $rows = array_merge($rows,$rowsTwo);
        return $this->staticTableBodyTwo($rows);
    }

    //本月可实现销售金额(签约概率>=81)
    protected function sign_this_num_table(){
        $table_pre = $this->table_pre;
        $searchDate = date("Y/m",strtotime($this->start_date));
        $whereSql = "DATE_FORMAT(a.available_date,'%Y/%m')='{$searchDate}' and g.rate_num<100 and a.sign_odds>80 and a.sign_odds<100 ";
        $whereSql.= " and a.kam_id='{$this->employee_id}'";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";

        $amtSql = "IFNULL(a.available_amt,0)";
        //$dateIFSql = "a.available_date<='{$this->end_date}' and IFNULL(a.available_date,a.apply_date)>='{$this->start_date}'";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,a.available_amt,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,g.rate_num
                ")->from("sal{$table_pre}bot a")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->order("if(g.rate_num>0,a.available_date,-1) desc,a.available_date desc")
            ->queryAll();
        $rows =$rows?$rows:array();

        $whereSql = "DATE_FORMAT(f.ava_date,'%Y/%m')='{$searchDate}'";
        $whereSql.= " and a.kam_id='{$this->employee_id}' and g.rate_num=100 and f.ava_rate>50";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";

        $amtSql = "IFNULL(f.ava_amt,0)";
        $selectText="a.id,a.kam_id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,
        a.customer_name,a.contact_user,g.pro_name,g.rate_num";
        $rowsTwo = Yii::app()->db->createCommand()
            ->select("{$selectText},
            CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,
            {$amtSql} as available_amt")
            ->from("sal{$table_pre}bot_ava f")
            ->leftJoin("sal{$table_pre}bot a","f.bot_id=a.id")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->queryAll();
        $rowsTwo =$rowsTwo?$rowsTwo:array();
        $rows = array_merge($rows,$rowsTwo);
        return $this->staticTableBody($rows);
    }

    //次月预估
    protected function sign_next_num_table(){
        $table_pre = $this->table_pre;
        $searchDate = date("Y/m",strtotime("{$this->start_date} + 1 months"));
        $whereSql = "DATE_FORMAT(a.available_date,'%Y/%m')='{$searchDate}' and g.rate_num<100 and a.sign_odds>50 and a.sign_odds<100 ";
        $whereSql.= " and a.kam_id='{$this->employee_id}'";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";

        $amtSql = "IFNULL(a.available_amt,0)";
        //$dateIFSql = "a.available_date<='{$this->end_date}' and IFNULL(a.available_date,a.apply_date)>='{$this->start_date}'";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,a.available_amt,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,g.rate_num
                ")->from("sal{$table_pre}bot a")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->order("if(g.rate_num>0,a.available_date,-1) desc,a.available_date desc")
            ->queryAll();
        $rows =$rows?$rows:array();

        $whereSql = "DATE_FORMAT(f.ava_date,'%Y/%m')='{$searchDate}'";
        $whereSql.= " and a.kam_id='{$this->employee_id}' and g.rate_num=100 and f.ava_rate>50";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";

        $amtSql = "IFNULL(f.ava_amt,0)";
        $selectText="a.id,a.kam_id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,
        a.customer_name,a.contact_user,g.pro_name,g.rate_num";
        $rowsTwo = Yii::app()->db->createCommand()
            ->select("{$selectText},
            CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,
            {$amtSql} as available_amt")
            ->from("sal{$table_pre}bot_ava f")
            ->leftJoin("sal{$table_pre}bot a","f.bot_id=a.id")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->queryAll();
        $rowsTwo =$rowsTwo?$rowsTwo:array();
        $rows = array_merge($rows,$rowsTwo);
        return $this->staticTableBody($rows);
    }

    //拜访阶段詳情
    protected function visit_num_table(){
        $table_pre = $this->table_pre;
        $whereSql = "DATE_FORMAT(a.available_date,'%Y')='{$this->ka_year}'";
        $whereSql.= " and a.kam_id='{$this->employee_id}'";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";
        $searchDate = $this->start_date;

        $amtSql = "IFNULL(a.available_amt,0)";
        //$dateIFSql = "a.available_date<='{$this->end_date}' and IFNULL(a.available_date,a.apply_date)>='{$this->start_date}'";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,a.available_amt,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,g.rate_num
                ")->from("sal{$table_pre}bot a")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->order("if(g.rate_num>0,a.available_date,-1) desc,a.available_date desc")
            ->queryAll();
        return $this->staticTableBody($rows);
    }

    //报价阶段詳情
    protected function quota_num_table(){
        $table_pre = $this->table_pre;
        $whereSql = "DATE_FORMAT(a.available_date,'%Y')='{$this->ka_year}'";
        $whereSql.= " and a.kam_id='{$this->employee_id}' and g.rate_num>=30";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";

        $amtSql = "IFNULL(a.available_amt,0)";
        //$dateIFSql = "a.available_date<='{$this->end_date}' and IFNULL(a.available_date,a.apply_date)>='{$this->start_date}'";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,a.available_amt,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,g.rate_num
                ")->from("sal{$table_pre}bot a")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->order("if(g.rate_num>0,a.available_date,-1) desc,a.available_date desc")
            ->queryAll();
        return $this->staticTableBody($rows);
    }

    //YTD
    protected function ytd_num_table(){
        $table_pre = $this->table_pre;
        $whereSql = "DATE_FORMAT(f.ava_date,'%Y')='{$this->ka_year}'";
        $whereSql.= " and a.kam_id='{$this->employee_id}' and g.rate_num=100";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";

        $amtSql = "IFNULL(f.ava_fact_amt,0)";
        $selectText="a.id,a.kam_id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,
        a.customer_name,a.contact_user,g.pro_name,g.rate_num";
        $rows = Yii::app()->db->createCommand()
            ->select("{$selectText},
            CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,
            sum({$amtSql}) as available_amt")
            ->from("sal{$table_pre}bot_ava f")
            ->leftJoin("sal{$table_pre}bot a","f.bot_id=a.id")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->group($selectText)
            ->queryAll();
        return $this->staticTableBodyThree($rows);
    }

    public function renewal_total_sum_table(){
        $table_pre = $this->table_pre;
        $rows = Yii::app()->db->createCommand()
            ->select("a.*,b.customer_no,b.customer_name")
            ->from("sal{$table_pre}bot_renewal a")
            ->leftJoin("sal{$table_pre}bot b","a.bot_id=b.id")
            ->where("DATE_FORMAT(a.renewal_date,'%Y')='{$this->search_year}' and b.kam_id='{$this->employee_id}'")
            ->order("a.bot_id,a.renewal_date desc")
            ->queryAll();
        return $this->staticTableBodyRenewal($rows);
    }

    protected function getBotAvaAmt($row,$type="year"){
        $table_pre = $this->table_pre;
        $amt = 0;
        if (isset($row["id"])){
            $whereSql = "a.bot_id='{$row['id']}'";
            $whereSql.= " and FIND_IN_SET('1',a.contract_type)";
            if($type=="month"){
                $searchDate = date("Y/m",strtotime($this->start_date));
                $whereSql.=" and DATE_FORMAT(a.ava_date,'%Y/%m')='{$searchDate}'";
            }else{
                $whereSql.=" and DATE_FORMAT(a.ava_date,'%Y')='{$this->ka_year}'";
            }
            $amt = Yii::app()->db->createCommand()
                ->select("sum(IFNULL(a.ava_fact_amt,0)) as amt_money")
                ->from("sal{$table_pre}bot_ava a")
                ->where($whereSql)
                ->queryScalar();
        }
        return $amt;
    }

    //mtd_num
    protected function mtd_num_table(){
        $table_pre = $this->table_pre;
        $searchDate = date("Y/m",strtotime($this->start_date));
        $whereSql = "DATE_FORMAT(f.ava_date,'%Y/%m')='$searchDate'";
        $whereSql.= " and a.kam_id='{$this->employee_id}' and g.rate_num=100";
        $whereSql.= " and FIND_IN_SET('1',a.contract_type)";

        $amtSql = "IFNULL(f.ava_fact_amt,0)";
        $selectText="a.id,a.kam_id,a.sign_odds,a.available_date,a.apply_date,a.customer_no,
        a.customer_name,a.contact_user,g.pro_name,g.rate_num";
        $rows = Yii::app()->db->createCommand()
            ->select("{$selectText},
            CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name,
            sum({$amtSql}) as available_amt")
            ->from("sal{$table_pre}bot_ava f")
            ->leftJoin("sal{$table_pre}bot a","f.bot_id=a.id")
            ->leftJoin("sal_ka_link g","a.link_id=g.id")
            ->where($whereSql)
            ->group($selectText)
            ->queryAll();
        return $this->staticTableBodyThree($rows);
    }

    protected function staticTableBody($rows){
        $urlName = ":".get_class($this);
        $urlName = (strpos($urlName,':KA')!==false)?"kABot":"cABot";
        $html = "<thead>";
        $html.="<tr>";
        $html.="<th width='120px'>预估可成交日期</th>";
        $html.="<th width='95px'>录入日期</th>";
        $html.="<th width='110px'>客户编号</th>";
        $html.="<th>客户公司</th>";
        $html.="<th width='80px'>签约概率</th>";
        $html.="<th>沟通阶段</th>";
        $html.="<th width='120px'>预估可成交金额</th>";
        $html.="<th width='1px'></th>";
        $html.="</tr>";
        $html.= "</thead><tbody>";
        if($rows){
            $sumAmt = 0;
            $sumNum = 0;
            foreach ($rows as $row){
                $row['available_amt'] = empty($row['available_amt'])?0:floatval($row['available_amt']);
                $row['rate_num'] = floatval($row['rate_num']);
                $sumAmt+=$row['available_amt'];
                $sumNum++;
                $link = self::drawEditButton('KA01',"{$urlName}/view", "{$urlName}/view", array('index'=>$row['id']));
                $html.="<tr data-id='{$row["id"]}'>";
                $html.="<td>".General::toDate($row['available_date'])."</td>";
                $html.="<td>".General::toDate($row['apply_date'])."</td>";
                $html.="<td>".$row['customer_no']."</td>";
                $html.="<td>".$row['customer_name']."</td>";
                $html.="<td>".KABotForm::getSignOddsListForId($row['sign_odds'],true)."</td>";
                $html.="<td>".$row['link_name']."</td>";
                $html.="<td class='text-right'>".$row['available_amt']."</td>";
                $html.="<td>".$link."</td>";
                $html.="</tr>";
            }
            $html.="<tr>";
            $html.="<td colspan='2' class='text-right'>总数量:</td><td colspan='2'>{$sumNum}</td>";
            $html.="<td colspan='2' class='text-right'>总金额:</td><td colspan='2'>{$sumAmt}</td>";
            $html.="</tr>";
            $html.="<tr><td colspan='8' class='text-right text-danger'>&nbsp;</td></tr>";
        }else{
            $html.="<tr><td colspan='8'>无</td></tr>";
        }
        $html.= "</tbody>";
        return $html;
    }

    //未来90天加权报价金额
    protected function staticTableBodyTwo($rows){
        $urlName = ":".get_class($this);
        $urlName = (strpos($urlName,':KA')!==false)?"kABot":"cABot";
        $html = "<thead>";
        $html.="<tr>";
        $html.="<th width='120px'>预估可成交日期</th>";
        $html.="<th width='95px'>录入日期</th>";
        $html.="<th width='110px'>客户编号</th>";
        $html.="<th>客户公司</th>";
        $html.="<th width='80px'>签约概率</th>";
        $html.="<th>沟通阶段</th>";
        $html.="<th width='120px'>预估可成交金额</th>";
        $html.="<th width='95px'>统计金额</th>";
        $html.="<th width='1px'></th>";
        $html.="</tr>";
        $html.= "</thead><tbody>";
        if($rows){
            $sumAmt = 0;
            $sumNum = 0;
            foreach ($rows as $row){
                $row['sign_odds'] = empty($row['sign_odds'])?0:floatval($row['sign_odds']);
                $row['available_amt'] = empty($row['available_amt'])?0:floatval($row['available_amt']);
                $row['rate_num'] = floatval($row['rate_num']);
                $amt_me = $row['available_amt'];
                $amt_me*= $row['sign_odds']>80?1:0.5;
                $sumAmt+=$amt_me;
                $sumNum++;
                $link = self::drawEditButton('KA01',"{$urlName}/view", "{$urlName}/view", array('index'=>$row['id']));
                $html.="<tr data-id='{$row["id"]}'>";
                $html.="<td>".General::toDate($row['available_date'])."</td>";
                $html.="<td>".General::toDate($row['apply_date'])."</td>";
                $html.="<td>".$row['customer_no']."</td>";
                $html.="<td>".$row['customer_name']."</td>";
                $html.="<td>".KABotForm::getSignOddsListForId($row['sign_odds'],true)."</td>";
                $html.="<td>".$row['link_name']."</td>";
                $html.="<td class='text-right'>".$row['available_amt']."</td>";
                $html.="<td class='text-right'>".$amt_me."</td>";
                $html.="<td>".$link."</td>";
                $html.="</tr>";
            }
            $html.="<tr>";
            $html.="<td colspan='2' class='text-right'>总数量:</td><td colspan='2'>{$sumNum}</td>";
            $html.="<td colspan='3' class='text-right'>总金额:</td><td colspan='2'>{$sumAmt}</td>";
            $html.="</tr>";
            $html.="<tr><td colspan='9' class='text-right text-danger'>&nbsp;</td></tr>";
        }else{
            $html.="<tr><td colspan='9'>无</td></tr>";
        }
        $html.= "</tbody>";
        return $html;
    }

    //YTD、MTD
    protected function staticTableBodyThree($rows){
        $urlName = ":".get_class($this);
        $urlName = (strpos($urlName,':KA')!==false)?"kABot":"cABot";
        $html = "<thead>";
        $html.="<tr>";
        $html.="<th width='120px'>预估可成交日期</th>";
        $html.="<th width='95px'>录入日期</th>";
        $html.="<th width='110px'>客户编号</th>";
        $html.="<th>客户公司</th>";
        $html.="<th width='80px'>签约概率</th>";
        $html.="<th>沟通阶段</th>";
        $html.="<th width='120px'>计算金额</th>";
        $html.="<th width='1px'></th>";
        $html.="</tr>";
        $html.= "</thead><tbody>";
        if($rows){
            $sumAmt = 0;
            $sumNum = 0;
            foreach ($rows as $row){
                $row['available_amt'] = empty($row['available_amt'])?0:floatval($row['available_amt']);
                $row['rate_num'] = floatval($row['rate_num']);
                $sumAmt+=$row['available_amt'];
                $sumNum++;
                $link = self::drawEditButton('KA01',"{$urlName}/view", "{$urlName}/view", array('index'=>$row['id']));
                $html.="<tr data-id='{$row["id"]}'>";
                $html.="<td>".General::toDate($row['available_date'])."</td>";
                $html.="<td>".General::toDate($row['apply_date'])."</td>";
                $html.="<td>".$row['customer_no']."</td>";
                $html.="<td>".$row['customer_name']."</td>";
                $html.="<td>".KABotForm::getSignOddsListForId($row['sign_odds'],true)."</td>";
                $html.="<td>".$row['link_name']."</td>";
                $html.="<td class='text-right'>".$row['available_amt']."</td>";
                $html.="<td>".$link."</td>";
                $html.="</tr>";
            }
            $html.="<tr>";
            $html.="<td colspan='2' class='text-right'>总数量:</td><td colspan='2'>{$sumNum}</td>";
            $html.="<td colspan='2' class='text-right'>总金额:</td><td colspan='2'>{$sumAmt}</td>";
            $html.="</tr>";
            $html.="<tr><td colspan='8' class='text-right text-danger'>&nbsp;</td></tr>";
        }else{
            $html.="<tr><td colspan='8'>无</td></tr>";
        }
        $html.= "</tbody>";
        return $html;
    }

    //续约
    protected function staticTableBodyRenewal($rows){
        $urlName = ":".get_class($this);
        $urlName = (strpos($urlName,':KA')!==false)?"kABot":"cABot";
        $html = "<thead>";
        $html.="<tr>";
        $html.="<th width='110px'>客户编号</th>";
        $html.="<th>客户公司</th>";
        $html.="<th width='95px'>续约日期</th>";
        $html.="<th width='110px'>续约门店数量</th>";
        $html.="<th width='95px'>续约城市</th>";
        $html.="<th width='95px'>续约金额</th>";
        $html.="<th width='1px'></th>";
        $html.="</tr>";
        $html.= "</thead><tbody>";
        if($rows){
            $botList=array();
            $sumNum=0;
            $sumAmt=0;
            foreach ($rows as $row){
                if(!in_array($row['bot_id'],$botList)){
                    $botList[]=$row['bot_id'];
                    $sumNum++;
                }
                $row['renewal_amt'] = floatval($row['renewal_amt']);
                $sumAmt+=$row['renewal_amt'];
                $link = self::drawEditButton('KA01',"{$urlName}/view", "{$urlName}/view", array('index'=>$row['bot_id']));
                $html.="<tr data-id='{$row["id"]}'>";
                $html.="<td>".$row['customer_no']."</td>";
                $html.="<td>".$row['customer_name']."</td>";
                $html.="<td>".General::toDate($row['renewal_date'])."</td>";
                $html.="<td>".$row['renewal_num']."</td>";
                $html.="<td>".$row['renewal_city']."</td>";
                $html.="<td class='text-right'>".round($row['renewal_amt'],2)."</td>";
                $html.="<td>".$link."</td>";
                $html.="</tr>";
            }
            $html.="<tr>";
            $html.="<td colspan='2' class='text-right'>总数量:</td><td colspan='1'>{$sumNum}</td>";
            $html.="<td colspan='2' class='text-right'>总金额:</td><td colspan='2'>{$sumAmt}</td>";
            $html.="</tr>";
        }else{
            $html.="<tr><td colspan='7'>无</td></tr>";
        }
        $html.= "</tbody>";
        return $html;
    }


    public static function drawEditButton($access, $writeurl, $readurl, $param) {
        $rw = Yii::app()->user->validRWFunction($access);
        $url = $rw ? $writeurl : $readurl;
        $icon = $rw ? "glyphicon glyphicon-pencil" : "glyphicon glyphicon-eye-open";
        $lnk=Yii::app()->createUrl($url,$param);

        return "<a href=\"$lnk\" target='_blank'><span class=\"$icon\"></span></a>";
    }
}