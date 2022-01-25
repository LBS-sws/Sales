<?php

class ClubSalesList extends CListPageModel
{
    public $year;
    public $month_type;//1:上半年（1-6月） 2：下半年（7-12月）
    private $no_staff=array(0);

    public $sales_elite=array();//销售精英
    public $sales_forward=array();//最佳进步表现人员
    public $sales_out=array();//新业务杰出表现人员
    public $sales_visit=array();//陌生拜访记录最多销售
    public $sales_rec=array();//总监推荐人选

    public $clubSetting=array();//俱乐部配置
    public $salesList=array();//所有销售人员(员工表)
    public $userList=array();//所有销售人员(账号表)

    private $startDate="";
    private $endDate="";
    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'year'=>Yii::t('club','year'),
			'month_type'=>Yii::t('club','month type'),
			'name'=>Yii::t('epc','ClubSales Name'),
			'z_index'=>Yii::t('epc','z_index'),
			'display'=>Yii::t('epc','display'),
		);
	}

	public function clubSalesAll($year,$month_type){
        $year = (empty($year)||!is_numeric($year))?date("Y"):$year;
        $month_type = in_array($month_type,array(1,2))?$month_type:1;
        $this->year = $year;
        $this->month_type = $month_type;
        if($this->month_type==1){
            $this->startDate=$this->year.'-01-01';
            $this->endDate=$this->year.'-06-31';
        }else{
            $this->startDate=$this->year.'-07-01';
            $this->endDate=$this->year.'-12-31';
        }

        $this->setSalesList();
        $salesCount = count($this->salesList);
        $this->clubSetting = ClubSettingForm::getClubSettingForDate($this->endDate,$salesCount);
        $this->sales_elite = $this->salesElite();
        $this->addUserList("sales_elite");
        $this->sales_forward = $this->salesForward();
        $this->addUserList("sales_forward");
        $this->sales_out = $this->salesOut();
        $this->addUserList("sales_out");
        $this->sales_visit = $this->salesVisit();
        $this->addUserList("sales_visit");
        $this->sales_rec = $this->salesRec();
        $this->addUserList("sales_rec");
    }
    //銷售俱樂部添加銷售
    private function addUserList($key){
        if(key_exists($key,$this->clubSetting)){
            $people = $this->clubSetting[$key]["people"];
            $lists = $this->$key;
            $fun = $this->clubSetting[$key]["fun"];
            $unifyStr = "判断是否和上一次的成绩一致";//如果一致，并列排名
            if(!empty($lists)&&$people>0){
                foreach ($lists as $list){
                    if($people<=0){
                        return;
                    }
                    if($this->$fun($list,$key)){
                        $this->clubSetting[$key]["userList"][]=$list;
                        $this->no_staff[] = $list["staffList"]["user_id"];
                        if($unifyStr != $list["unifyStr"]){
                            $people--;
                        }
                    }
                    $unifyStr = $list["unifyStr"];
                }
            }
        }
    }

    //设置销售所有人员列表
    private function setSalesList(){
        $date = $this->endDate;
        $noCity = ClubSettingForm::$noCity;
        $noCitySql = implode("','",$noCity);
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.code,a.name,city.name as city_name,a.entry_time,f.user_id,b.name as dept_name")
            ->from("hr{$suffix}.hr_employee a")
            ->leftJoin("hr{$suffix}.hr_dept b","a.position=b.id")
            ->leftJoin("hr{$suffix}.hr_binding f","a.id=f.employee_id")
            ->leftJoin("security{$suffix}.sec_city city","a.city=city.code")
            ->where("f.user_id is not null and replace(a.entry_time,'/', '-')<='{$date}' and b.dept_class='Sales' and a.city not in ('{$noCitySql}') and b.manager_leave=1 and a.staff_status!=-1")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $this->salesList[$row["id"]] = $row;
                $this->userList[$row["user_id"]] = $row;
            }
        }
    }

    //销售精英
    private function salesElite(){
        $noCity = ClubSettingForm::$noCity;
        $noCitySql = implode("','",$noCity);
        $sql = "select city, username,SUM(all_score) as sumScore 
				from sal_rank  
				where  city not in ('$noCitySql') and month<='{$this->endDate}' and month>='{$this->startDate}'   
			  	group by city, username
			";
        $records = Yii::app()->db->createCommand()->select("a.*")
            ->from("($sql) a")->order("a.sumScore desc")->queryAll();
        $list = array();
        if($records){
            foreach ($records as $record){
                if(key_exists($record["username"],$this->userList)){ //该销售是否参加俱乐部
                    $record["staffList"]=$this->userList[$record["username"]];
                    $record["unifyStr"]=$record["sumScore"];//统一排序的字符串名字
                    $list[]=$record;
                }
            }
        }
        return $list;
    }

    //最佳进步表现人员
    private function salesForward(){
        if($this->month_type==1){
            $startDate=($this->year-1).'-07-01';
            $endDate=($this->year-1).'-12-31';
        }else{
            $startDate=$this->year.'-01-01';
            $endDate=$this->year.'-06-31';
        }
        $noCity = ClubSettingForm::$noCity;
        $noCitySql = implode("','",$noCity);
        $list = array();
        if(!empty($this->sales_elite)){
            foreach ($this->sales_elite as $key=>$row){
                if(!in_array($row["username"],$this->no_staff,true)){//已加入俱乐部的员工不需要重复加入
                    $staff=$row;
                    $staff["lastScore"] = Yii::app()->db->createCommand()->select("SUM(all_score)")->from("sal_rank")
                        ->where("city not in ('$noCitySql') and username='{$row['username']}' and month<='{$endDate}' and month>='{$startDate}'")->queryScalar();

                    $staff['ratioScore'] = empty($staff["lastScore"])?0:($staff["sumScore"]-$staff["lastScore"])/$staff["lastScore"];
                    $staff['ratioScore'] = number_format($staff['ratioScore'],3);
                    $staff['ratioScore'] = floatval($staff['ratioScore']);
                    $staff["unifyStr"]=$staff["ratioScore"];//统一排序的字符串名字
                    $list[]=$staff;
                }
            }
        }
        return empty($list)?array():self::arraySort($list,"ratioScore");
    }

    //新业务杰出表现人员
    private function salesOut(){
        $noCity = ClubSettingForm::$noCity;
        $noCitySql = implode("','",$noCity);
        $suffix = Yii::app()->params['envSuffix'];
        //飘盈香及甲醛 （IA服务）
        $bringRows = Yii::app()->db->createCommand()->select("a.salesman_id,a.paid_type,a.amt_paid,a.ctrt_period")
            ->from("swoper$suffix.swo_service a")
            ->leftJoin("swoper$suffix.swo_customer_type_twoname f","a.cust_type_name=f.id")
            ->where("a.city not in ('$noCitySql') and a.status_dt>='{$this->startDate}' and a.status_dt<='{$this->endDate}' and a.status='N' and (a.cust_type in (5,6) or a.cust_type_name in (50,28) or f.bring=1)")->queryAll();
        $list=array();
        if($bringRows){
            foreach ($bringRows as $bringRow){
                if(key_exists($bringRow["salesman_id"],$this->salesList)){ //该销售是否参加俱乐部
                    $username = $this->salesList[$bringRow["salesman_id"]]["user_id"];
                    if(!in_array($username,$this->no_staff,true)) {//已加入俱乐部的员工不需要重复加入
                        $money = $bringRow["amt_paid"];
                        if($bringRow["paid_type"]=="M"){ //月金额
                            $money*=$bringRow["ctrt_period"];
                        }
                        if(!key_exists($bringRow["salesman_id"],$list)){ //初始化
                            $list[$bringRow["salesman_id"]]=array(
                                "unifyStr"=>0,
                                "money"=>0,
                                "staffList"=>$this->salesList[$bringRow["salesman_id"]]
                            );
                        }
                        $list[$bringRow["salesman_id"]]["unifyStr"]+=$money;//统一排序的字符串名字
                        $list[$bringRow["salesman_id"]]["money"]+=$money;
                    }
                }
            }
        }
        //ID服务
        $serviceIDRows = Yii::app()->db->createCommand()->select("a.salesman_id,sum(a.amt_money) as money")
            ->from("swoper$suffix.swo_serviceid a")
            ->where("a.city not in ('$noCitySql') and a.status_dt>='{$this->startDate}' and a.status_dt<='{$this->endDate}' and a.status='N'")
            ->group("a.salesman_id")->queryAll();
        if($serviceIDRows){
            foreach ($serviceIDRows as $serviceIDRow){
                if(key_exists($serviceIDRow["salesman_id"],$this->salesList)){ //该销售是否参加俱乐部
                    $username = $this->salesList[$serviceIDRow["salesman_id"]]["user_id"];
                    if(!in_array($username,$this->no_staff,true)) {//已加入俱乐部的员工不需要重复加入
                        if(!key_exists($serviceIDRow["salesman_id"],$list)){ //初始化
                            $list[$serviceIDRow["salesman_id"]]=array(
                                "unifyStr"=>0,
                                "money"=>0,
                                "staffList"=>$this->salesList[$serviceIDRow["salesman_id"]]
                            );
                        }
                        $list[$serviceIDRow["salesman_id"]]["unifyStr"]+=$serviceIDRow["money"];//统一排序的字符串名字
                        $list[$serviceIDRow["salesman_id"]]["money"]+=$serviceIDRow["money"];
                    }
                }
            }
        }

        return empty($list)?array():self::arraySort($list,"money");
    }

    //陌生拜访记录最多销售
    private function salesVisit(){
        $noCity = ClubSettingForm::$noCity;
        $noCitySql = implode("','",$noCity);
        $list = array();

        $visitRows = Yii::app()->db->createCommand()->select("username,count(id) as staff_sum")
            ->from("sal_visit")
            ->where("city not in ('$noCitySql') and visit_dt>='{$this->startDate}' and visit_dt<='{$this->endDate}'")
            ->group("username")->queryAll();
        if($visitRows){
            foreach ($visitRows as $visitRow){
                if(key_exists($visitRow["username"],$this->userList)){ //该销售是否参加俱乐部
                    if(!in_array($visitRow["username"],$this->no_staff,true)) {//已加入俱乐部的员工不需要重复加入
                        $visitRow["staffList"] = $this->userList[$visitRow["username"]];
                        $visitRow["unifyStr"] = $visitRow["staff_sum"];//统一排序的字符串名字
                        $list[$visitRow["username"]]=$visitRow;
                    }
                }
            }
        }
        return empty($list)?array():self::arraySort($list,"staff_sum");
    }

    //总监推荐人选
    private function salesRec(){
        $list = array();
        $recRows = Yii::app()->db->createCommand()->select("employee_id,rec_remark,rec_user,rec_name")
            ->from("sal_club_rec")
            ->where("rec_year='{$this->year}' and month_type<='{$this->month_type}'")->order("lcd desc")->queryAll();
        if($recRows){
            foreach ($recRows as $recRow){
                if(key_exists($recRow["employee_id"],$this->salesList)) { //该销售是否参加俱乐部
                    $username = $this->salesList[$recRow["employee_id"]]["user_id"];
                    if(!in_array($username,$this->no_staff,true)) {//已加入俱乐部的员工不需要重复加入
                        $recRow["staffList"] = $this->salesList[$recRow["employee_id"]];
                        $recRow["unifyStr"] = 1;//统一排序的字符串名字
                        $list[]=$recRow;
                    }
                }
            }
        }
        return $list;
    }

    public function printAllTable(){
	    $html=$this->htmlCountPage();
	    if(!empty($this->clubSetting)){
	        foreach ($this->clubSetting as $key=>$list){
	            $html.= $this->htmlMinPage($key);
            }
        }
	    return $html;
    }

    //总页html
    private function htmlCountPage(){
        $html="<div class='tab-pane fade active in'><p>&nbsp;</p>";
        if(!empty($this->clubSetting)){
            foreach ($this->clubSetting as $key=>$list){
                $html.= "<div class='col-lg-6'><div class='box box-primary'>";
                $html.="<div class='box-header with-border'>".Yii::t("club",$list["name"])."</div>";
                $html.="<div class='box-body'>";
                $html.="<div class='direct-chat-messages' style='height: 250px'>";
                $html.=self::tableHtml($key,$list["userList"],true);
                $html.="</div>";
                $html.="</div>";
                $html.="</div></div>";
            }
        }
        $html.="</div>";
        return $html;
    }

    //分页的html
    private function htmlMinPage($key){
        $rows = $this->$key;
        $html="<div id='tab_{$key}' class='tab-pane fade'><p>&nbsp;</p>";
        $html.=self::tableHtml($key,$rows);
        $html.="</div>";
        return $html;
    }

    public static function tableHtml($key,$rows,$small=false){
        if($small){
            $html="<table class='table table-bordered table-striped small'>";
        }else{
            $html="<table class='table table-bordered table-striped'>";
        }
        $html.=self::tableHead($key,$small);
        $html.=self::tableBody($key,$rows,$small);
        $html.="</table>";
        return $html;
    }

    public static function tableHead($key,$small){
        $table ="<thead><tr>";
        if($key!="aaa") {
            $table .= "<th>" . Yii::t("club", "ranking") . "</th>";
        }
        $table.="<th>".Yii::t("club","staff code")."</th>";
        $table.="<th>".Yii::t("club","staff name")."</th>";
        $table.="<th>".Yii::t("club","staff city")."</th>";
        $table.="<th>".Yii::t("club","entry date")."</th>";
        $table.="<th>".Yii::t("club","dept name")."</th>";
        switch ($key){
            case "sales_elite"://销售精英
                $table.="<th>".Yii::t("club","rank total score")."</th>";
                break;
            case "sales_forward"://最佳进步表现人员
                if(!$small){
                    $table.="<th>".Yii::t("club","last score")."</th>";
                    $table.="<th>".Yii::t("club","now score")."</th>";
                }
                $table.="<th>".Yii::t("club","ratio score")."</th>";
                break;
            case "sales_out"://新业务杰出表现人员
                $table.="<th>".Yii::t("club","money total score")."</th>";
                break;
            case "sales_visit"://陌生拜访记录最多销售
                $table.="<th>".Yii::t("club","visit total score")."</th>";
                break;
            case "sales_rec"://总监推荐人选
                $table.="<th>".Yii::t("club","referees user")."</th>";
                break;
        }
        $table.="</tr></thead>";
        return $table;
    }

    public static function tableBody($key,$rows,$small){
        $body = "<tbody>";
        $i = 1;
        if(!empty($rows)){
            foreach ($rows as $row){
                if($key=="aaa"){
                    $row["staffList"] = $row;//适配员工显示
                }
                $body.="<tr>";
                if($key!="aaa"){
                    $body.="<td>".$i."</td>";
                }
                $body.="<td>".$row["staffList"]["code"]."</td>";
                $body.="<td>".$row["staffList"]["name"]."</td>";
                $body.="<td>".$row["staffList"]["city_name"]."</td>";
                $body.="<td>".$row["staffList"]["entry_time"]."</td>";
                $body.="<td>".$row["staffList"]["dept_name"]."</td>";
                switch ($key){
                    case "sales_elite"://销售精英
                        $body.="<td>".$row["unifyStr"]."</td>";
                        break;
                    case "sales_forward"://最佳进步表现人员
                        if(!$small){
                            $body.="<td>".$row["lastScore"]."</td>";
                            $body.="<td>".$row["sumScore"]."</td>";
                        }
                        $body.="<td>".$row["unifyStr"]."</td>";
                        break;
                    case "sales_out"://新业务杰出表现人员
                        $body.="<td>".$row["unifyStr"]."</td>";
                        break;
                    case "sales_visit"://陌生拜访记录最多销售
                        $body.="<td>".$row["unifyStr"]."</td>";
                        break;
                    case "sales_rec"://总监推荐人选
                        $body.="<td>".$row["rec_user"]."</td>";
                        break;
                }
                $body.="</tr>";
                $i++;
            }
        }
        $body.="</tbody>";
        return $body;
    }

    private function validateTrue($list,$key){
        return true;
    }

    private function validateVisit($list,$key){
        if($list["unifyStr"]<1800){ //销售拜访必须大于1800条
            return false;
        }else{
            return true;
        }
    }

    private function validateOut($list,$key){
        if($list["unifyStr"]<100000){ //新业务金额最低门槛10万
            return false;
        }else{
            return true;
        }
    }
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select * 
				from epc_clubSales 
				where 1=1 
			";
		$sql2 = "select count(id)
				from epc_clubSales 
				where 1=1 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'name':
					$clause .= General::getSqlConditionClause('name',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
            $order .= " order by {$this->orderField} ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by id desc ";
        }

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();

		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
					$this->attr[] = array(
						'id'=>$record['id'],
						'name'=>$record['name'],
						'z_index'=>$record['z_index'],
                        'display'=>$record['display']==1?Yii::t("epc","show"):Yii::t("epc","none"),
					);
			}
		}
		$session = Yii::app()->session;
		$session['clubSales_c01'] = $this->getCriteria();
		return true;
	}

	public static function getYearList(){
	    $year = date("Y");
	    $arr = array();
	    for ($i=$year-4;$i<=$year;$i++){
	        $arr[$i] = $i.Yii::t("club","year");
        }
        return $arr;
    }

	public static function getMothTypeList(){
	    $arr = array(
	        1=>Yii::t("club","first half year"),
	        2=>Yii::t("club","second half year"),
        );
        return $arr;
    }

    /**
     * 二维数组根据某个字段排序
     * @param array $array 要排序的数组
     * @param string $keys   要排序的键字段
     * @param string $sort  排序类型  SORT_ASC     SORT_DESC
     * @return array 排序后的数组
     */
    public  static function arraySort($array, $keys, $sort = SORT_DESC) {
        $last_names = array_column($array,$keys);
        array_multisort($last_names,$sort,SORT_NUMERIC,$array);
        return $array;
    }
}
