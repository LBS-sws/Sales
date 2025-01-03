<?php

class IntegralForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $name;
	public $cust_type_name=array();
	public $type_group;
	public $username;
	public $sale_day;
	public $employee_id;
	public $employee_code;
	public $employee_name;
	public $city;
    public $sum=0;
    public $sums;
    public $year;
    public $month;

    protected $boolNew="";
    protected $oneAndTwo=0;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'name'=>Yii::t('code','Description'),
			'rpt_type'=>Yii::t('code','Report Category'),
			'city'=>Yii::t('sales','City'),
			'type_group'=>Yii::t('code','Type'),
            'sum'=>Yii::t('code','Sum'),
            'sums'=>Yii::t('code','Sums'),
            'year'=>Yii::t('code','Year'),
            'month'=>Yii::t('code','Month'),

		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('','required'),
			array('id,rpt_type,sums,','safe'),
		);
	}

	public static function getCustTypeList($key=0,$bool=false){
        $list = array(
            '1'=>'每部',
            '2'=>'每个新客户',
            '3'=>'每个新客户订购一包',
            '4'=>'每个新客户每桶',
            '5'=>'每个新客户每箱',
            '6'=>'每月'
        );
        if($bool){
            if(key_exists($key,$list)){
                return $list[$key];
            }else{
                return $key;
            }
        }else{
            return $list;
        }
    }

    //改版后的详情（正在使用）
	public function deleteSave($index)
    {
        $citylist = Yii::app()->user->city_allow();
        $row = Yii::app()->db->createCommand()
            ->select("a.id")->from("sal_integral a")
            ->where("a.id=:id and a.city in ({$citylist})", array(":id" => $index))->queryRow();
        if($row){
            Yii::app()->db->createCommand()->delete('sal_integral', 'id=:id', array(':id'=>$index));
            echo "delete success";
        }else{
            echo "not find";
        }
    }

    //改版后的详情（正在使用）
	public function retrieveDataNew($index){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("a.id,a.year,a.month,a.sale_day,a.username,f.id as employee_id,f.code as employee_code,f.name as employee_name")
            ->from("sal_integral a")
            ->leftJoin("hr$suffix.hr_binding b","a.username=b.user_id")
            ->leftJoin("hr$suffix.hr_employee f","b.employee_id=f.id")
            ->where("a.id=:id",array(":id"=>$index))->queryRow();
        if($row){
            $this->id = $row["id"];
            $this->year = $row["year"];
            $this->month = $row["month"];
            $this->username = $row["username"];
            $this->sale_day = $row["sale_day"];
            $this->employee_id = $row["employee_id"];
            $this->employee_code = $row["employee_code"];
            $this->employee_name = $row["employee_name"];
            $this->name = $row["employee_name"];
            $startDate = $this->year."-".$this->month."-01";
            $endDate = $this->year."-".$this->month."-31";
            //产品（INV）只统计新增，服务（非INV）统计新增、续约、更改
            $exprSql = " and ((a.status in('A','C') and f.rpt_cat<>'INV') or (a.status='N'))";
            $IDExprSql = " and (((a.status='A' or (a.status = 'C' and a.ctrt_period>=12)) and f.rpt_cat<>'INV') or (a.status='N'))";
            $selectSql = "a.*,g.score_bool,
            CONCAT(h.code,h.name) as company_name_str,
            f.rpt_cat,f.description,b.cust_type_name as cust_type_name_name,b.conditions,b.fraction,b.toplimit";
            //所有需要計算的客戶服務(客戶服務)
            $serviceRows = Yii::app()->db->createCommand()
                ->select("$selectSql,b.bring,CONCAT('A') as sql_type_name")
                ->from("swoper$suffix.swo_service a")
                ->leftJoin("swoper$suffix.swo_customer_type_twoname b","a.cust_type_name=b.id")
                ->leftJoin("swoper$suffix.swo_customer_type f","a.cust_type=f.id")
                ->leftJoin("swoper$suffix.swo_nature_type g","a.nature_type_two=g.id")
                ->leftJoin("swoper$suffix.swo_company h","a.company_id=h.id")
                ->where("a.status_dt BETWEEN '$startDate' and '$endDate' and a.salesman_id='$this->employee_id' $exprSql")->queryAll();
            $serviceRows=$serviceRows?$serviceRows:array();
            //所有需要計算的客戶服務(ID客戶服務)
            $serviceRowsID = Yii::app()->db->createCommand()
                ->select("$selectSql,CONCAT('D') as sql_type_name")
                ->from("swoper$suffix.swo_serviceid a")
                ->leftJoin("swoper$suffix.swo_customer_type_info b","a.cust_type_name=b.id")
                ->leftJoin("swoper$suffix.swo_customer_type_id f","a.cust_type=f.id")
                ->leftJoin("swoper$suffix.swo_nature_type g","a.nature_type_two=g.id")
                ->leftJoin("swoper$suffix.swo_company h","a.company_id=h.id")
                ->where("a.status_dt BETWEEN '$startDate' and '$endDate' and a.salesman_id='$this->employee_id' $IDExprSql")->queryAll();
            $serviceRowsID=$serviceRowsID?$serviceRowsID:array();
            $serviceRows = array_merge($serviceRows,$serviceRowsID);
            $typeForINV = $this->getServiceTypeList("INV");//產品類型
            $typeAuto = $this->getServiceTypeList();//服務類型
            $otherList = $this->getOtherTypeList();//其它類型
            $this->cust_type_name = array(
                "INV"=>array(//產品
                    "name"=>"产品",
                    "list"=>$typeForINV,//統計（頁面顯示）
                    "service"=>array(),//包含的服務（下載需要）
                    "count"=>0//小計（頁面顯示）
                ),
                "AUTO"=>array(//服務
                    "name"=>"服务",
                    "list"=>$typeAuto,//統計（頁面顯示）
                    "service"=>array(),//包含的服務（下載需要）
                    "count"=>0//小計（頁面顯示）
                ),
                "OTHER"=>array(//其它
                    "name"=>"其它",
                    "list"=>$otherList,//統計（頁面顯示）
                    "insertService"=>array(),//安装维护包含的服務（下載需要）
                    "payMonthService"=>array(),//预收月数包含的服務（下載需要）
                    "groupService"=>array(),//商业组重点开发客户（下載需要）
                    "count"=>0//小計（頁面顯示）
                ),
                "all_sum"=>0,//總計
                "point"=>0,//積分
                "sale_day"=>$this->sale_day,//工作天數
            );
            if(!empty($serviceRows)){
                foreach ($serviceRows as $serviceRow){
                    $this->boolNew="";
                    //計算日報表系統的所有設置
                    $this->computeServiceForSwo($serviceRow);
                    if($serviceRow["status"]=="N"){ //只有新增類的服務才計算
                        //安装维护费计算
                        $this->computeInstallForSwo($serviceRow);
                        //预收月数计算
                        $this->computePayMonthForSwo($serviceRow);
                        //当月销售净化机器+隔油池金额 >= 1500
                        $this->computeOneAndTwo($serviceRow);
                        //商业组重点开发客户
                        $this->commercialGroup($serviceRow);
                    }
                }
            }
            //销售拜访计算
            $this->computeSales();
            //总计
            $this->computeSum();
            return true;
        }else{
            return false;
        }
    }

    //判断是不是新客户
    protected function selectNewService($serviceRow){
	    if($serviceRow["status"]!="N"){
	        return false;
        }
        if($this->boolNew!=""){//每個循環只需要執行一次
            return $this->boolNew;
        }
        $bool = true;
	    switch ($serviceRow["sql_type_name"]){
            case "A"://非ID服務
                $startDate = strtotime("{$serviceRow["status_dt"]} - 3 month");
                $date = General::toMyDate($serviceRow["status_dt"]);
                $suffix = Yii::app()->params['envSuffix'];
                $row = Yii::app()->db->createCommand()->select("a.id,a.status,a.status_dt")->from("swoper$suffix.swo_service a")
                    ->where("a.status_dt<='$date' 
            and a.salesman_id='$this->employee_id' 
            and a.company_id='{$serviceRow['company_id']}' 
            and a.cust_type='{$serviceRow['cust_type']}' 
            and a.cust_type_name='{$serviceRow['cust_type_name']}' 
            and a.id!='{$serviceRow['id']}'")->order("a.status_dt desc")->queryRow();

                if($row){
                    if(in_array($row["status"],array("T","S"))&&$startDate>=strtotime($row["status_dt"])){
                        //終止時間或者暫停時間大於3個月
                        $bool = true;//是最新的客户
                    }else{
                        $bool = false;//不是最新的客户
                    }
                }
                break;
            case "D"://ID服務
                break;
        }
        $this->boolNew = $bool;
        return $bool;
    }

    //查詢該客戶歷史的產品總數量
    protected function selectHistorySumService($serviceRow){
        $date = General::toMyDate($serviceRow["status_dt"]);
        $suffix = Yii::app()->params['envSuffix'];
        if($serviceRow["rpt_cat"]=="INV"){
            $mysql = "and a.status='N'";
        }else{
            $mysql = "and a.status in ('N','A','C')";
        }
        $historySum=Yii::app()->db->createCommand()->select("sum(a.pieces)")->from("swoper$suffix.swo_service a")
            ->where("a.status_dt<='$date' $mysql 
            and a.salesman_id='$this->employee_id' 
            and a.company_id='{$serviceRow['company_id']}' 
            and a.cust_type='{$serviceRow['cust_type']}' 
            and a.cust_type_name='{$serviceRow['cust_type_name']}' 
            and a.id!='{$serviceRow['id']}'")->queryScalar();
        return $historySum?$historySum:0;
    }

    //安装维护费计算
    protected function computeInstallForSwo($serviceRow){
        if($serviceRow["sql_type_name"]=="D"){
            return false;//ID服務不計算安裝費
        }
        if(!key_exists("other",$this->cust_type_name["OTHER"]["list"])){
            return false;//如果没有配置安装服务费则不计算
        }
        $typeList = &$this->cust_type_name["OTHER"]["list"]["other"];//装机的列表
        //由于安装维护费没有“个数”的概念，所以只算新客户或者每个客户
        $maxNum = empty($typeList["toplimit"])?0:intval($typeList["toplimit"]);
        $fraction = empty($typeList["fraction"])?0:intval($typeList["fraction"]);
        $serviceRow["amt_install"]=floatval($serviceRow["amt_install"]);
        if($serviceRow["need_install"]=="Y"&&$serviceRow["amt_install"]!=0){//该服务有装机
            $num = 1;
            if($maxNum!=0&&$typeList["num"]>=$maxNum){
                $num = 0;//设置了上限后，不允许超出上限
            }
            //判断是不是新客户或者设置了每个
            if($typeList["conditions"]==1||$this->selectNewService($serviceRow)){
                $typeList["num"]+=1;
                $typeList["sum"]+=$num*$fraction;
                if($num>0){
                    $serviceRow["integralNum"]=1;//积分实际计算的数量
                    $this->cust_type_name["OTHER"]["insertService"][]=$serviceRow;//下载需要
                }
                //小计
                $this->cust_type_name["OTHER"]["count"]+=$num*$fraction;
            }
        }
    }

    //商业组重点开发客户(仅适用IA、IB)
    protected function commercialGroup($serviceRow){
        //IA、IB客户
        $bool = strpos($serviceRow["description"],'IA')!==false||strpos($serviceRow["description"],'IB')!==false;
        if ($bool&&$serviceRow["score_bool"]==1){//工厂、酒店、物业、学校、医院
            $this->cust_type_name["OTHER"]["groupService"][]=$serviceRow;//下载需要

            $this->cust_type_name["OTHER"]["list"]["commercialGroup"]["num"]+=1;
            $this->cust_type_name["OTHER"]["list"]["commercialGroup"]["sum"]+=2;
        }
    }

    //当月销售净化机器+隔油池金额 >= 1500
    protected function computeOneAndTwo($serviceRow){
        if($this->oneAndTwo===1){//1:需要執行 2:執行中
            if($serviceRow["sql_type_name"]=="D"){ //ID服務
                $this->cust_type_name["OTHER"]["list"]["oneAndTwo"]["money"]+=$serviceRow["amt_money"];
            }
            //IA服務的非一次性服務
            if($serviceRow["sql_type_name"]=="A"&&$serviceRow["cust_type_name"]>0&&$serviceRow["bring"]==1){
                $money=$serviceRow["amt_paid"];
                if($serviceRow["paid_type"]=="M"){
                    $money*=$serviceRow["ctrt_period"];
                }
                $this->cust_type_name["OTHER"]["list"]["oneAndTwo"]["money"]+=$money;
            }
            if($this->cust_type_name["OTHER"]["list"]["oneAndTwo"]["money"]>=1500){
                $this->cust_type_name["OTHER"]["list"]["oneAndTwo"]["num"]=0;
                $this->cust_type_name["OTHER"]["list"]["oneAndTwo"]["sum"]=0;
            }
        }
    }

    //预收月数计算
    protected function computePayMonthForSwo($serviceRow){
        $month = empty($serviceRow["prepay_month"])?0:floatval($serviceRow["prepay_month"]);
        if($month==0){
            return false;
        }
        if($serviceRow["sql_type_name"]=="D"&&$month<12){
            return false;//ID服務必須預收12月以上
        }
        $typeList = &$this->cust_type_name["OTHER"]["list"];//装机的列表
        $rows = array_reverse($typeList,true);//反转数组
        foreach ($rows as $key=>$row){
            if($row["rpt_cat"]==2){//其它类型里的预收
                if($row["toplimit"]<=$month){
                    //判断是不是新客户或者设置了每个
                    if($typeList[$key]["conditions"]==1||$this->selectNewService($serviceRow)){
                        $typeList[$key]["num"]+=1;
                        $typeList[$key]["sum"]+=$typeList[$key]["fraction"];

                        $serviceRow["integralNum"]=1;//积分实际计算的数量
                        $this->cust_type_name["OTHER"]["payMonthService"][]=$serviceRow;//下载需要
                        //小计
                        $this->cust_type_name["OTHER"]["count"]+=$typeList[$key]["fraction"];
                    }
                    return true;
                }
            }
        }
    }

    //销售拜访计算
    protected function computeSales(){
        $startDate = $this->year."-".$this->month."-01";
        $endDate = $this->year."-".$this->month."-31";
        $sum = Yii::app()->db->createCommand()->select("count(id)")
            ->from("sal_visit")
            ->where("username='{$this->username}' 
            and visit_dt BETWEEN '$startDate' and '$endDate'")
            ->queryScalar();
        $sum = $sum?$sum:0;
        $this->sum = $sum;
        //计算销售拜访的平均数
        $average = empty($this->sale_day)?0:$sum/$this->sale_day;

        $typeList = &$this->cust_type_name["OTHER"]["list"];//装机的列表
        $rows = array_reverse($typeList,true);//反转数组
        foreach ($rows as $key=>$row){
            if($row["rpt_cat"]==3){//其它类型里的预收
                if($row["toplimit"]<=$average){
                    $typeList[$key]["num"]=1;
                    $typeList[$key]["sum"]=$typeList[$key]["fraction"];
                    return true;
                }
            }
        }
    }

    //统计汇总
    protected function computeSum(){
        //其它類別的小計（由於後續擴充，需要單獨統計）
        $this->cust_type_name["OTHER"]["count"] = 0;
        foreach ($this->cust_type_name["OTHER"]["list"] as $row){
            $this->cust_type_name["OTHER"]["count"] += $row["sum"];
        }
        $this->cust_type_name["all_sum"]+=$this->cust_type_name["INV"]["count"];
        $this->cust_type_name["all_sum"]+=$this->cust_type_name["AUTO"]["count"];
        $this->cust_type_name["all_sum"]+=$this->cust_type_name["OTHER"]["count"];
        $list = array(
            array("sum"=>10,"point"=>-0.01),
            array("sum"=>20,"point"=>-0.005),
            array("sum"=>30,"point"=>0),
            array("sum"=>80,"point"=>0.01)
        );
        $point=0.02;//总分大于80
        if($this->sum<200){//销售拜访不足200，不需要计算分数
            $point = $list[0]["point"];
        }else{
            foreach ($list as $row){
                if($this->cust_type_name["all_sum"]<=$row["sum"]){
                    $point = $row["point"];
                    break;
                }
            }
        }
        $this->cust_type_name["point"]=$point;

        //列表更新
        $sql1="update sal_integral set point='".$this->cust_type_name['point']."',all_sum='".$this->cust_type_name['all_sum']."' where id='{$this->id}'";
        $command=Yii::app()->db->createCommand($sql1)->execute();
    }

    //产品、服务的数量计算
    protected function computeServiceForSwo($serviceRow){
	    //产品数量
        $pieces = empty($serviceRow["pieces"])?0:intval($serviceRow["pieces"]);

        //ID產品（更改）必須先算出更改的數量
        if($serviceRow["sql_type_name"]=="D"&&$serviceRow["status"]=="A"){
            $b4_pieces=empty($serviceRow["b4_pieces"])?0:intval($serviceRow["b4_pieces"]);
            $pieces-=$b4_pieces;
            $pieces=$pieces>0?$pieces:0;
        }
        //单个积分
        $fraction = empty($serviceRow["fraction"])?0:intval($serviceRow["fraction"]);
        $id = $serviceRow["sql_type_name"].$serviceRow["cust_type_name"];
        $historySum=$this->selectHistorySumService($serviceRow);
        switch ($serviceRow["conditions"]){
            case 1://每个
                $this->addServiceSum($id,$pieces,$fraction,$serviceRow,$historySum);
                break;
            case 2://每个新客户
                if($this->selectNewService($serviceRow)){
                    $this->addServiceSum($id,1,$fraction,$serviceRow,$historySum);
                }
                break;
            case 3://每个新客户订购一包
            case 4://每个新客户每桶
            case 5://每个新客户每箱
                $this->addServiceSum($id,$pieces,$fraction,$serviceRow,$historySum);
                break;
            case 6://每月
                break;
        }
    }

    //数量计算
    protected function addServiceSum($id,$pieces,$fraction,$serviceRow,$historySum){
        $type = $serviceRow["rpt_cat"]=="INV"?"INV":"AUTO";
        $maxNum = $serviceRow["toplimit"];//產品上限（歷史記錄的總上限）不會每月重置
        $exprNum = $maxNum-$historySum;//允許計算積分的產品數量
        $exprNum = $exprNum<0?0:$exprNum;
        $integralNum = $pieces;
        if(key_exists($id,$this->cust_type_name[$type]["list"])){
            if($maxNum!=0&&$pieces>$exprNum){
                $integralNum = $exprNum;
                $fraction = $exprNum*$fraction;
            }else{
                $fraction = $pieces*$fraction;
            }
            if($fraction>0){//下载只显示已计算的服务
                if($maxNum!=0){
                    $serviceRow["expr_num"] = $exprNum;//剩余可算积分的数量
                }
                $serviceRow["integralNum"]=$integralNum;//积分实际计算的数量
                $this->cust_type_name[$type]["service"][]=$serviceRow;
                //奇葩要求，所以放到if之內
                $this->cust_type_name[$type]["list"][$id]['num']+=$pieces;
            }
            $this->cust_type_name[$type]["list"][$id]['sum']+=$fraction;
            //小计
            $this->cust_type_name[$type]["count"]+=$fraction;
        }
    }

    //獲取服務類別（包含id客戶類別表）
    protected function getServiceTypeList($type=""){
        $suffix = Yii::app()->params['envSuffix'];
        if($type=="INV"){
            $mysql = "b.rpt_cat='INV'";
        }else{
            $mysql = "b.rpt_cat<>'INV'";
        }
        $unionTextID = Yii::app()->db->createCommand()
            ->select("a.id,b.lcd,a.conditions,a.cust_type_name,a.fraction,a.toplimit,b.description,CONCAT('D') as sql_type_name")
            ->from("swoper$suffix.swo_customer_type_info a")
            ->leftJoin("swoper$suffix.swo_customer_type_id b","a.cust_type_id=b.id")
            ->where("$mysql and a.index_num=2")->getText();
        $unionText = Yii::app()->db->createCommand()
            ->select("a.id,b.lcd,a.conditions,a.cust_type_name,a.fraction,a.toplimit,b.description,CONCAT('A') as sql_type_name")
            ->from("swoper$suffix.swo_customer_type_twoname a")
            ->leftJoin("swoper$suffix.swo_customer_type b","a.cust_type_id=b.id")
            ->where($mysql)->union($unionTextID)->getText();
        $rows = Yii::app()->db->createCommand()
            ->select("f.*")
            ->from("($unionText) f")
            ->order("f.sql_type_name asc,f.lcd asc,f.id asc")->queryAll();
        $list = array();
        if($rows){
            foreach ($rows as $row){
                $row["con_name"]=$this->getCustTypeList($row["conditions"],true);
                $row["num"]=0;
                $row["historySum"]=0;//暂时无用
                $row["sum"]=0;
                $row["remark"]=empty($row["toplimit"])?"":"上限为".$row["toplimit"];
                $list[$row["sql_type_name"].$row["id"]] = $row;//由於後期添加了ID服務表所以添加字符串"A"
            }
        }
        return $list;
    }

    //其它類型
    protected function getOtherTypeList(){
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.conditions,a.cust_type_name,a.fraction,a.toplimit,b.description,b.rpt_cat")
            ->from("sal_points_type_twoname a")
            ->leftJoin("sal_points_type b","a.cust_type_id=b.id")
            ->order("b.rpt_cat asc,a.toplimit asc,a.id asc")->queryAll();
        $list = array();
        if($rows){
            foreach ($rows as $row){
                $id ="other";
                $row["con_name"]=$this->getCustTypeList($row["conditions"],true);
                $row["description"]="其它";
                $row["num"]=0;
                $row["historySum"]=0;//暂时无用
                $row["sum"]=0;
                switch ($row["rpt_cat"]){
                    case 1://裝機
                        $row["remark"]=empty($row["toplimit"])?"":"上限为".$row["toplimit"];
                        break;
                    case 2://預收
                        $row["remark"]=empty($row["toplimit"])?"":$row["toplimit"]."个月";
                        $id.=$row["id"];
                        break;
                    case 3://銷售拜訪
                        $row["remark"]=empty($row["toplimit"])?"":$row["toplimit"]."条";
                        $id.=$row["id"];
                        break;
                    default:
                        $id.=$row["id"];
                }
                $list[$id] = $row;
            }
        }
        $date = date("Y-m-d",strtotime("{$this->year}-{$this->month}-01"));
        if($date>="2022-01-01" && $date<="2023-04-01"){
            $this->oneAndTwo = 1;
            $list["oneAndTwo"] = array(
                "cust_type_name"=>"当月销售净化机器+隔油池金额小于1500",
                "con_name"=>"每月",
                "description"=>"其它",
                "fraction"=>-5,
                "toplimit"=>0,
                "rpt_cat"=>"oneAndTwo",
                "num"=>1,
                "historySum"=>0,
                "sum"=>-5,
                "money"=>0,
                "remark"=>"当月数量显示1时扣5分，显示0时分数为0分",
            );
        }elseif($date>="2023-05-01"){
            $this->oneAndTwo = 2;
            $list["commercialGroup"] = array(
                "cust_type_name"=>"商业组重点开发客户",
                "con_name"=>"每个新客户",
                "description"=>"其它",
                "fraction"=>2,
                "toplimit"=>0,
                "rpt_cat"=>"commercialGroup",
                "num"=>0,
                "historySum"=>0,
                "sum"=>0,
                "money"=>0,
                "remark"=>"工厂、酒店、物业、学校、医院，适用于IA/IB",
            );
        }else{
            $this->oneAndTwo = 0;//防止重复调用
        }
        return $list;
    }

    //改版前的详情（已放弃使用）- 可以删除
	public function retrieveData($index)
	{
        $suffix = Yii::app()->params['envSuffix'];
        $this->cust_type_name['canpin']=$this->custTypeNameA(9);//产品买卖
        $this->cust_type_name['fuwu']=$this->custTypeNameB(9);//产品买卖之外的全部
        $sql="select * from sal_integral where id='$index'";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        $startime=$row['year']."-".$row['month']."-01";
        $endtime=$row['year']."-".$row['month']."-31";
        $this->id=$index;
        $this->year=$row['year'];
        $this->month=$row['month'];
        $i=0;
        foreach ($this->cust_type_name['canpin'] as &$value){//产品的(INV服務)
            $sum_c=array();
            $sum_s=array();
            $sql1="select a.* from swoper$suffix.swo_service a
               inner join hr$suffix.hr_employee b on a.salesman=concat(b.name, ' (', b.code, ')')
               inner join hr$suffix.hr_binding c on b.id=c.employee_id 
               where c.user_id='".$row['username']."' and a.cust_type_name='".$value['id']."' and a.status_dt>='$startime' and status_dt<='$endtime' and a.status='N'";
            $service = Yii::app()->db->createCommand($sql1)->queryAll();
            if(!empty($service)){
                $two=0;//判断本月同一家公司有几条
                foreach ($service as $arr){
                     $arr['company_name']=str_replace("'","''",$arr['company_name']);
                    if($value['conditions']==3||$value['conditions']==4||$value['conditions']==5){
                        $sql="select sum(pieces) as sumpieces from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and status='N' and status_dt<'$startime'";
                        $m = Yii::app()->db->createCommand($sql)->queryScalar();
                        $sql="select sum(pieces) as sumpieces from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and status='N' and status_dt>='$startime' and status_dt<='$endtime'";
                        $s = Yii::app()->db->createCommand($sql)->queryScalar();
                        $sql_calculation="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and status='N' and status_dt>='$startime' and status_dt<='$endtime'";
                        $list = Yii::app()->db->createCommand($sql_calculation)->queryAll();
                        if(empty($m)){
                            $m=0;
                        }

                        if($value['toplimit']>0){//有上限
                            if((($m<$value['toplimit'])&&$two==0)||(($m<$value['toplimit'])&&count($list)==1)){
                                if($s>$value['toplimit']){
                                    $sum_c[]=$value['toplimit'];//$s
                                    $sum_s[]=$value['toplimit']-$m;
                                    $value['list'][]=$list;
                                }else{
                                    if(($m+$s)<=$value['toplimit']){
                                        $sum_c[]=$s;
                                        $sum_s[]=$s;
                                        $value['list'][]=$list;
                                    }else{
                                        $sum_c[]=$s;
                                        $sum_s[]=$value['toplimit']-$m;
                                        $value['list'][]=$list;
                                    }
                                }
                            }else{
                                $sum_c[]=0;
                                $sum_s[]=0;
                                if(!empty($list[$two])){
                                    $value['list'][][0]=$list[$two];
                                }
                            }
                        }else{
                            $sum_c[]=$arr['pieces'];
                            $sum_s[]=$arr['pieces'];
                            $value['list'][]=$list;
                        }
                        if(count($list)>1){
                            $two=$two+1;
                        }
                    // print_r('<pre>');   print_r($sum_s);
                    }elseif($value['conditions']==2){
                        $sql_calculation="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and status='N' and status_dt<'$startime'";
                        $m = Yii::app()->db->createCommand($sql_calculation)->queryAll();
                        $sql="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and status='N' and status_dt>='$startime' and status_dt<='$endtime'";
                        $s = Yii::app()->db->createCommand($sql)->queryAll();
                        if(empty($m)&&!empty($s)){
                            $sum_c[]= 1;
                            $sum_s[]=1;
                            $value['list'][]=$s;
                        }else{
                            $sum_c[]=0;
                            $sum_s[]=0;
                        }
                    }elseif($value['conditions']==1){
                        $sum_c[]= $arr['pieces'];
                        if(($arr['pieces']>$value['toplimit'])&&$value['toplimit']!=0){
                            $sum_s[]=$value['toplimit'];
                        }else{
                            $sum_s[]=$arr['pieces'];
                        }
                        $value['list'][$i][]=$arr;
                        $i=$i+1;
                    }
                }
                $value['number']=array_sum($sum_c);//数量
//                print_r('<pre>');
//                print_r( $sum_c);
                $value['sum']=array_sum($sum_s)*$value['fraction'];
            }else{
                $value['number']=0;
                $value['sum']=0;
            }

        }
        $f=0;
        foreach ($this->cust_type_name['fuwu'] as &$value){//服务的
            $sum_f=array();
            $sum_ff=array();
            $sql1="select a.* from swoper$suffix.swo_service a
               inner join hr$suffix.hr_employee b on a.salesman=concat(b.name, ' (', b.code, ')')
               inner join hr$suffix.hr_binding c on b.id=c.employee_id 
               where c.user_id='".$row['username']."' and a.cust_type_name='".$value['id']."' and a.status_dt>='$startime' and a.status_dt<='$endtime' and (a.status='N' or a.status='A' or a.status='C')";
            $service = Yii::app()->db->createCommand($sql1)->queryAll();
            if(!empty($service)){
                $two=0;//判断本月同一家公司有几条
                foreach ($service as $arr){
                    $arr['company_name']=str_replace("'","''",$arr['company_name']);
                    if($value['conditions']==3||$value['conditions']==4||$value['conditions']==5){
                        $sql="select sum(pieces) as sumpieces from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and status='N' and status_dt<'$startime'";
                        $m = Yii::app()->db->createCommand($sql)->queryScalar();
                        $sql="select sum(pieces) as sumpieces from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and status='N' and status_dt>='$startime'";
                        $s = Yii::app()->db->createCommand($sql)->queryScalar();
                        $sql_calculation="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and status='N' and status_dt>='$startime' and status_dt<='$endtime'";
                        $list = Yii::app()->db->createCommand($sql_calculation)->queryAll();
                        if(empty($m)){
                            $m=0;
                        }
                        if($value['toplimit']>0){
                            if(($m<$value['toplimit'])&&$two==0){
                                if($s>$value['toplimit']){
                                    $sum_f[]=$value['toplimit'];//$s
                                    $sum_ff[]=$value['toplimit']-$m;
                                    $value['list'][]=$list;;
                                }else{
                                    if(($m+$s)<=$value['toplimit']){
                                        $sum_f[]=$s;
                                        $sum_ff[]=$s;
                                        $value['list'][]=$list;;
                                    }else{
                                        $sum_f[]=$s;
                                        $sum_ff[]=$value['toplimit']-$m;
                                        $value['list'][]=$list;;
                                    }
                                }
                            }else{
                                $sum_f[]=0;
                                $sum_ff[]=0;
                                if(!empty($list[$two])){
                                    $value['list'][][0]=$list[$two];
                                }
                            }
                        }else{
                            $sum_c[]=$arr['pieces'];
                            $sum_s[]=$arr['pieces'];
                            $value['list'][]=$list;;
                        }
                        if(count($list)>1){
                            $two=$two+1;
                        }
                    }elseif($value['conditions']==2){
                        if($arr['status']=='N'){
//                            $sql_calculation="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and status='N' and status_dt<'$startime'";
//                            $m = Yii::app()->db->createCommand($sql_calculation)->queryAll();
                            $sql="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and status='N' and status_dt>='$startime' and status_dt<='$endtime'";
                            $s = Yii::app()->db->createCommand($sql)->queryAll();
                            if(!empty($s)){
                                $sum_f[]= 1;
                                $value['list'][]=$s;
                                $sum_ff[]=1;
                            }else{
                                $sum_f[]=0;
                                $sum_ff[]=0;
                            }
                        }
                    }elseif($value['conditions']==1){
                        if($arr['status']=='N'){
                            $sum_f[]= $arr['pieces'];
                            if(($arr['pieces']>$value['toplimit'])&&$value['toplimit']!=0){
                                $sum_ff[]=$value['toplimit'];
                            }else{
                                $sum_ff[]=$arr['pieces'];
                            }
                            $value['list'][$f][]=$arr;
                        }
                        if($arr['status']=='A'){
                            $sql_calculation="select sum(pieces) as sumpieces from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and (status='N' or status='C') and status_dt<='".$arr['status_dt']."'";
                            $sum = Yii::app()->db->createCommand($sql_calculation)->queryScalar();
                            $sql="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and (status='N' or status='C') and status_dt<='".$arr['status_dt']."' order by  id desc";
                            $m = Yii::app()->db->createCommand($sql)->queryRow();
                            if(!empty($m)){
                                if($m['paid_type']=='M'){
                                    $n_money=$m['ctrt_period']*$m['amt_paid'];
                                }else{
                                    $n_money=$m['amt_paid'];
                                }
                                if($arr['paid_type']=='M'){
                                    $a_money=$arr['ctrt_period']*$arr['amt_paid'];
                                }else{
                                    $a_money=$arr['amt_paid'];
                                }
                                if($a_money>$n_money){
                                    if(($sum<$value['toplimit'])&&$value['toplimit']!=0){
                                        $cha=$value['toplimit']-$sum;
                                        if($arr['pieces']>=$cha){
                                            $sum_f[]=$cha;
                                            $sum_ff[]=$cha;
                                            $value['list'][$f][]=$arr;
                                        }else{
                                            $sum_f[]=$arr['pieces'];
                                            $sum_ff[]=$arr['pieces'];
                                            $value['list'][$f][]=$arr;
                                        }
                                    }
                                   // print_r('<pre>');print_r($sum);
                                }
                            }
                        }
                        if($arr['status']=='C'){
                            $sql_calculation="select sum(pieces) as sumpieces from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and (status='N' or status='A') and status_dt<='".$arr['status_dt']."'";
                            $sum = Yii::app()->db->createCommand($sql_calculation)->queryScalar();
                            $sql="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and (status='N' or status='A') and status_dt<='".$arr['status_dt']."' order by  id desc";
                            $m = Yii::app()->db->createCommand($sql)->queryRow();

                            if(!empty($m)){
                                if(($sum<$value['toplimit'])&&$value['toplimit']!=0){
                                    $cha=$value['toplimit']-$sum;
                                    if($arr['pieces']>=$cha){
                                        $sum_f[]=$cha;
                                        $sum_ff[]=$cha;
                                        $value['list'][$f][]=$arr;
                                    }else{
                                        $sum_f[]=$arr['pieces'];
                                        $sum_ff[]=$arr['pieces'];
                                        $value['list'][$f][]=$arr;
                                    }
                                }
                            //    print_r('<pre>');print_r($arr['pieces']);
                            }
                        }

                        $f=$f+1;
                    }
                }

                $value['number']=array_sum($sum_f);//数量
                $value['sum']=array_sum($sum_ff)*$value['fraction'];
            }else{
                $value['number']=0;
                $value['sum']=0;
            }
//            print_r('<pre>');
//            print_r($arr);
        }
//        exit();exit
        //装机
        $zhuangji=$this->points(1);//产品买卖
        $sql_zj="select * from swoper$suffix.swo_service a
               inner join hr$suffix.hr_employee b on a.salesman=concat(b.name, ' (', b.code, ')')
               inner join hr$suffix.hr_binding c on b.id=c.employee_id 
               where c.user_id='".$row['username']."'  and a.status_dt>='$startime' and a.status_dt<='$endtime' and a.amt_install<>0  and a.status='N'";
        $service = Yii::app()->db->createCommand($sql_zj)->queryAll();
        if(!empty($service)){
            foreach ($service as $arr){
                $arr['company_name']=str_replace("'","''",$arr['company_name']);
                $sql_calculation="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."'  and salesman='".$arr['salesman']."' and amt_install<>0  and cust_type='".$arr['cust_type']."'  and status='N'";
                $m = Yii::app()->db->createCommand($sql_calculation)->queryAll();
                if(!empty($m)){
                    $sum_z[]=1;
                    $this->cust_type_name['zhuangji']['list'][]=$m;
                }else{
                    $sum_z[]=0;
                }
            }
            $v=array_sum($sum_z);//数量
            $this->cust_type_name['zhuangji']['sum']=$v*$zhuangji[0]['fraction'];
            $this->cust_type_name['zhuangji']['number']=$v;
            $this->cust_type_name['zhuangji']['fraction']=$zhuangji[0]['fraction'];//分数
        }else{
            $this->cust_type_name['zhuangji']['sum']=0;
            $this->cust_type_name['zhuangji']['number']=0;
            $this->cust_type_name['zhuangji']['fraction']=$zhuangji[0]['fraction'];
        }


        //预收3
        $this->cust_type_name['yushou']=$this->points(2);//预收3
        foreach ($this->cust_type_name['yushou'] as &$value){
            $sum_y3=array();
            if($value['toplimit']==3){
                $sql_ys="select a.* from swoper$suffix.swo_service a
               inner join hr$suffix.hr_employee b on a.salesman=concat(b.name, ' (', b.code, ')')
               inner join hr$suffix.hr_binding c on b.id=c.employee_id 
               where c.user_id='".$row['username']."'  and a.status_dt>='$startime' and a.status_dt<='$endtime' and a.status='N' and  prepay_month>=3 and prepay_month <6 ";
            }elseif ($value['toplimit']==6){
                $sql_ys="select a.* from swoper$suffix.swo_service a
               inner join hr$suffix.hr_employee b on a.salesman=concat(b.name, ' (', b.code, ')')
               inner join hr$suffix.hr_binding c on b.id=c.employee_id 
               where c.user_id='".$row['username']."'  and a.status_dt>='$startime' and a.status_dt<='$endtime' and a.status='N' and  prepay_month>=6 and prepay_month <12 ";
            }elseif ($value['toplimit']==12){
                $sql_ys="select a.* from swoper$suffix.swo_service a
               inner join hr$suffix.hr_employee b on a.salesman=concat(b.name, ' (', b.code, ')')
               inner join hr$suffix.hr_binding c on b.id=c.employee_id 
               where c.user_id='".$row['username']."'  and a.status_dt>='$startime' and a.status_dt<='$endtime' and a.status='N' and  prepay_month >=12  ";
            }
            $service = Yii::app()->db->createCommand($sql_ys)->queryAll();
            if(!empty($service)){
                foreach ($service as $arr){
                    $arr['company_name']=str_replace("'","''",$arr['company_name']);
                    if(empty($arr['cust_type_name'])){
                        $sql_calculation="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type='".$arr['cust_type']."' and cust_type_name=' ' and salesman='".$arr['salesman']."'  and status='N' and status_dt<='$endtime'";

                    }else{
                        $sql_calculation="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and status='N' and status_dt<='$endtime'";
                    }
                    $m = Yii::app()->db->createCommand($sql_calculation)->queryAll();
                    $sql_list="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type='".$arr['cust_type']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and status='N' and status_dt>='$startime' and status_dt<='$endtime'";
                    $list = Yii::app()->db->createCommand($sql_list)->queryAll();
                    if(!empty($m)){
                        $sum_y3[]=1;
                        $value['list'][]=$list;
                    }else{
                        $sum_y3[]=0;
                    }
                }
                $v=array_sum($sum_y3);//数量
                $value['sum']=$v*$value['fraction'];
                $value['number']=$v;
            }else{
                $value['sum']=0;
                $value['number']=0;
            }
        }

        //预收6
//        $sql_ys="select * from swoper$suffix.swo_service a
//               inner join hr$suffix.hr_employee b on a.salesman=concat(b.name, ' (', b.code, ')')
//               inner join hr$suffix.hr_binding c on b.id=c.employee_id
//               where c.user_id='".$row['username']."'  and a.status_dt>='$startime' and a.status_dt<='$endtime' and a.status='N' and  prepay_month>=6 and prepay_month <12 ";
//        $service = Yii::app()->db->createCommand($sql_ys)->queryAll();
//        if(!empty($service)){
//            foreach ($service as $arr){
//          $arr['company_name']=str_replace("'","''",$arr['company_name']);
//                if(empty($arr['cust_type_name'])){
//                    $sql_calculation="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type='".$arr['cust_type']."' and cust_type_name=' ' and salesman='".$arr['salesman']."'   and status='N'";
//
//                }else{
//                    $sql_calculation="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'  and status='N'";
//                }
//                $m = Yii::app()->db->createCommand($sql_calculation)->queryAll();
//                if(!empty($m)&&count($m)==1){
//                    $sum_y6[]=1;
//                    $this->cust_type_name['yushou6']['list'][]=$m;
//                }else{
//                    $sum_y6[]=0;
//                }
//            }
//            $v=array_sum($sum_y6);//数量
//            $this->cust_type_name['yushou6']['sum']=$v*3;
//            $this->cust_type_name['yushou6']['number']=$v;
//            $this->cust_type_name['yushou6']['fraction']=3;
//        }else{
//            $this->cust_type_name['yushou6']['sum']=0;
//            $this->cust_type_name['yushou6']['number']=0;
//            $this->cust_type_name['yushou6']['fraction']=3;
//        }
        //预收12
//        $sql_ys="select * from swoper$suffix.swo_service a
//               inner join hr$suffix.hr_employee b on a.salesman=concat(b.name, ' (', b.code, ')')
//               inner join hr$suffix.hr_binding c on b.id=c.employee_id
//               where c.user_id='".$row['username']."'  and a.status_dt>='$startime' and a.status_dt<='$endtime' and a.status='N' and  prepay_month >=12  ";
//        $service = Yii::app()->db->createCommand($sql_ys)->queryAll();
//        if(!empty($service)){
//            foreach ($service as $arr){
//          $arr['company_name']=str_replace("'","''",$arr['company_name']);
//                if(empty($arr['cust_type_name'])){
//                    $sql_calculation="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type='".$arr['cust_type']."' and cust_type_name=' ' and salesman='".$arr['salesman']."'  and status='N'";
//
//                }else{
//                    $sql_calculation="select * from swoper$suffix.swo_service where company_name='".$arr['company_name']."' and cust_type_name='".$arr['cust_type_name']."' and salesman='".$arr['salesman']."'   and status='N'";
//                }
//                $m = Yii::app()->db->createCommand($sql_calculation)->queryAll();
//                if(!empty($m)&&count($m)==1){
//                    $sum_y12[]=1;
//                    $this->cust_type_name['yushou12']['list'][]=$m;
//                }else{
//                    $sum_y12[]=0;
//                }
//            }
//            $v=array_sum($sum_y12);//数量
//            $this->cust_type_name['yushou12']['sum']=$v*5;
//            $this->cust_type_name['yushou12']['number']=$v;
//            $this->cust_type_name['yushou12']['fraction']=5;
//        }else{
//            $this->cust_type_name['yushou12']['sum']=0;
//            $this->cust_type_name['yushou12']['number']=0;
//            $this->cust_type_name['yushou12']['fraction']=5;
//        }
        //拜访15
//        $sql_bf="select * from sal_visit
//               where username='".$row['username']."'  and visit_dt>='$startime' and visit_dt<='$endtime' and  shift is null ";
//        $bf = Yii::app()->db->createCommand($sql_bf)->queryAll();
//        if(!empty($bf)&&(count($bf)/$row['sale_day'])>15){
//            $this->cust_type_name['baifang15']['sum']=3;
//            $this->cust_type_name['baifang15']['number']=1;
//            $this->cust_type_name['baifang15']['fraction']=3;
//        }else{
//            $this->cust_type_name['baifang15']['sum']=0;
//            $this->cust_type_name['baifang15']['number']=0;
//            $this->cust_type_name['baifang15']['fraction']=3;
//        }
///
        $this->cust_type_name['baifang']=$this->points(3);//拜访
        $o=0;
        foreach ($this->cust_type_name['baifang'] as &$value){
            $sql_bf="select * from sal_visit       
               where username='".$row['username']."'  and visit_dt>='$startime' and visit_dt<='$endtime'";
            $bf = Yii::app()->db->createCommand($sql_bf)->queryAll();
            if(!empty($bf)&&(count($bf)/$row['sale_day'])>=$value['toplimit']){
                $value['sum']=$value['fraction'];
                $value['number']=1;
                if($o==0&&(count($bf)/$row['sale_day'])>20){
                    $value['sum']=0;
                    $value['number']=0;
                }
            }else{
                $value['sum']=0;
                $value['number']=0;
            }
            $o=$o+1;
        }

        //拜访20
//        if(!empty($bf)&&(count($bf)/$row['sale_day'])>20){
//            $this->cust_type_name['baifang20']['sum']=6;
//            $this->cust_type_name['baifang20']['number']=1;
//            $this->cust_type_name['baifang20']['fraction']=6;
//        }else{
//            $this->cust_type_name['baifang20']['sum']=0;
//            $this->cust_type_name['baifang20']['number']=0;
//            $this->cust_type_name['baifang20']['fraction']=6;
//        }
//        if($this->cust_type_name['baifang20']['sum']==0){
//            $this->cust_type_name['baifang20']['sum']=0;
//            $baifang= $this->cust_type_name['baifang20']['sum']+$this->cust_type_name['baifang15']['sum'];
//        }else{
//            $this->cust_type_name['baifang20']['sum']=0;
//            $baifang= $this->cust_type_name['baifang20']['sum']+$this->cust_type_name['baifang15']['sum'];
//        }

        $this->cust_type_name['canpin_sum']=array_sum(array_map(create_function('$val', 'return $val["sum"];'), $this->cust_type_name['canpin']));
        $this->cust_type_name['fuwu_sum']=array_sum(array_map(create_function('$val', 'return $val["sum"];'), $this->cust_type_name['fuwu']));
        $this->cust_type_name['yushou_sum']=array_sum(array_map(create_function('$val', 'return $val["sum"];'), $this->cust_type_name['yushou']));
        $this->cust_type_name['baifang_sum']=array_sum(array_map(create_function('$val', 'return $val["sum"];'), $this->cust_type_name['baifang']));
        $this->cust_type_name['qita_sum']=$this->cust_type_name['zhuangji']['sum']+ $this->cust_type_name['yushou_sum']+$this->cust_type_name['baifang_sum'];
        $this->cust_type_name['all_sum']= $this->cust_type_name['canpin_sum']+ $this->cust_type_name['fuwu_sum']+ $this->cust_type_name['qita_sum'];
        if(empty($this->cust_type_name['all_sum'])){
            $this->cust_type_name['all_sum']=0;
        }
        if(count($bf)<200&&(count($bf)/10)<$row['sale_day']){
            $this->cust_type_name['sale_day']=0;
        }else{
            $this->cust_type_name['sale_day']=1;
        }
        if($this->cust_type_name['all_sum']<= 10|| $this->cust_type_name['sale_day']==0){
            $this->cust_type_name['point']=-0.01;
        }elseif ($this->cust_type_name['all_sum']<= 20&& $this->cust_type_name['sale_day']==1){
            $this->cust_type_name['point']=-0.005;
        }elseif ($this->cust_type_name['all_sum']<= 30&& $this->cust_type_name['sale_day']==1){
            $this->cust_type_name['point']=0;
        }elseif ($this->cust_type_name['all_sum']<= 80&& $this->cust_type_name['sale_day']==1){
            $this->cust_type_name['point']=0.01;
        }elseif ($this->cust_type_name['all_sum']> 80&& $this->cust_type_name['sale_day']==1){
            $this->cust_type_name['point']=0.02;
        }
        $sql="select * from hr$suffix.hr_binding  where user_id='".$row['username']."' ";
        $name = Yii::app()->db->createCommand($sql)->queryRow();
        //列表更新
        $sql1="update sal_integral set point='".$this->cust_type_name['point']."',all_sum='".$this->cust_type_name['all_sum']."' where id='".$index."'";
        $command=Yii::app()->db->createCommand($sql1)->execute();
        $this->name=$name['employee_name'];
        $this->sum=count($bf);
		return true;
	}

    //改版前的下載（已放弃使用）- 可以删除
	public function downEx($model){
        Yii::$enableIncludePath = false;
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        spl_autoload_unregister(array('YiiBase','autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel;
        $objReader  = PHPExcel_IOFactory::createReader('Excel2007');
        $path = Yii::app()->basePath.'/commands/template/integral.xlsx';

        $objPHPExcel = $objReader->load($path);
        $i=16;
        $objActSheet=$objPHPExcel->setActiveSheetIndex(0);
        foreach ($model['cust_type_name']['canpin'] as $arr ){
            $i=$i+1;
            $objWorksheet = $objActSheet;
            $objWorksheet->insertNewRowBefore($i + 1, 1);
            $objActSheet->setCellValue('A'.$i, $arr['cust_type_name']) ;
            $objActSheet->setCellValue('B'.$i, $this->getCustTypeName($arr['cust_type_id'])) ;
            $objActSheet->setCellValue('D'.$i, $this->getConditionsName($arr['conditions'])) ;
            $objActSheet->setCellValue('E'.$i, $arr['fraction']) ;
            $objActSheet->setCellValue('F'.$i, $arr['number']) ;
            $objActSheet->setCellValue('G'.$i, $arr['sum']) ;
            if($arr['toplimit']!=0){
                $toplimit= "上限为".$arr['toplimit'] ;
            }else{
                $toplimit="";
            }
            $objActSheet->setCellValue('H'.$i, $toplimit) ;
        }
        $i=$i+2;
        $objActSheet->setCellValue('G'.$i, $model['cust_type_name']['canpin_sum']) ;
        $i=$i+2;
        foreach ($model['cust_type_name']['fuwu'] as $arr ){
            $i=$i+1;
            $objWorksheet = $objActSheet;
            $objWorksheet->insertNewRowBefore($i + 1, 1);
            $objActSheet->setCellValue('A'.$i, $arr['cust_type_name']) ;
            $objActSheet->setCellValue('B'.$i, $this->getCustTypeName($arr['cust_type_id'])) ;
            $objActSheet->setCellValue('D'.$i, $this->getConditionsName($arr['conditions'])) ;
            $objActSheet->setCellValue('E'.$i, $arr['fraction']) ;
            $objActSheet->setCellValue('F'.$i, $arr['number']) ;
            $objActSheet->setCellValue('G'.$i, $arr['sum']) ;
            if($arr['toplimit']!=0){
                $toplimit= "上限为".$arr['toplimit'] ;
            }else{
                $toplimit="";
            }
            $objActSheet->setCellValue('H'.$i, $toplimit) ;
        }
        $i=$i+2;
        $objActSheet->setCellValue('G'.$i, $model['cust_type_name']['fuwu_sum']) ;
        $i=$i+3;
        $objActSheet->setCellValue('F'.$i, $model['cust_type_name']['zhuangji']['number']) ;
        $objActSheet->setCellValue('G'.$i, $model['cust_type_name']['zhuangji']['sum']) ;
        foreach ($model['cust_type_name']['yushou'] as $arr ){
            $i=$i+1;
            $objWorksheet = $objActSheet;
            $objWorksheet->insertNewRowBefore($i + 1, 1);
            $objActSheet->setCellValue('A'.$i, $arr['cust_type_name']) ;
            $objActSheet->setCellValue('B'.$i, '其他') ;
            $objActSheet->setCellValue('D'.$i, $this->getConditionsName($arr['conditions'])) ;
            $objActSheet->setCellValue('E'.$i, $arr['fraction']) ;
            $objActSheet->setCellValue('F'.$i, $arr['number']) ;
            $objActSheet->setCellValue('G'.$i, $arr['sum']) ;
            if($arr['toplimit']!=0){
                $toplimit= $arr['toplimit'].'个月' ;
            }else{
                $toplimit="";
            }
            $objActSheet->setCellValue('H'.$i, $toplimit) ;
        }
        foreach ($model['cust_type_name']['baifang'] as $arr ){
            $i=$i+1;
            $objWorksheet = $objActSheet;
            $objWorksheet->insertNewRowBefore($i + 1, 1);
            $objActSheet->setCellValue('A'.$i, $arr['cust_type_name']) ;
            $objActSheet->setCellValue('B'.$i, '其他') ;
            $objActSheet->setCellValue('D'.$i, $this->getConditionsName($arr['conditions'])) ;
            $objActSheet->setCellValue('E'.$i, $arr['fraction']) ;
            $objActSheet->setCellValue('F'.$i, $arr['number']) ;
            $objActSheet->setCellValue('G'.$i, $arr['sum']) ;
            if($arr['toplimit']!=0){
                $toplimit= $arr['toplimit'].'条' ;
            }else{
                $toplimit="";
            }
            $objActSheet->setCellValue('H'.$i, $toplimit) ;
        }

        $i=$i+2;
        $objActSheet->setCellValue('G'.$i, $model['cust_type_name']['qita_sum']) ;
        $i=$i+3;
        $objActSheet->setCellValue('G'.$i, $model['cust_type_name']['all_sum']) ;
        $i=$i+1;
        $point=$model['cust_type_name']['point']*100;
        $objActSheet->setCellValue('G'.$i, $point."%") ;
        //$objPHPExcel->createSheet();//新增页
        $objPHPExcel->getSheet(1)->setTitle('详情列表');
        $objPHPExcel->getSheet(0)->setTitle('积分表单');
        $objActSheet=$objPHPExcel->setActiveSheetIndex(1);
        //$objActSheet->setCellValue('A2', '啊沙雕哈市的你看的') ;
        $o=2;
        $objActSheet->setCellValue('A'.$o,'产品') ;
        foreach ($model['cust_type_name']['canpin'] as $arr ){
            if(!empty($arr['list'])){
                foreach ($arr['list'] as $list){
                    $o=$o+1;
              //      print_r('<pre>');print_r($list);
                    $objActSheet->setCellValue('B'.$o,$this->getStatusName($list[0]['status'])) ;
                    $objActSheet->setCellValue('C'.$o,	date_format(date_create($list[0]['status_dt']),"Y/m/d")) ;
                    $objActSheet->setCellValue('D'.$o,date_format(date_create($list[0]['first_dt']),"Y/m/d")) ;
                    $objActSheet->setCellValue('E'.$o,$list[0]['company_name']) ;
                    $objActSheet->setCellValue('F'.$o,$this->getCustTypeName($list[0]['cust_type'])) ;
                    $objActSheet->setCellValue('G'.$o,$this->getCustTypeNamec($list[0]['cust_type_name'])) ;
                    $objActSheet->setCellValue('H'.$o,$list[0]['pieces']) ;
                    $objActSheet->setCellValue('I'.$o,$list[0]['amt_install']) ;
                    $objActSheet->setCellValue('J'.$o,$list[0]['salesman']) ;
                    $objActSheet->setCellValue('K'.$o,$list[0]['prepay_month']) ;
                }
            }

        }
        $o=$o+1;
        $objActSheet->setCellValue('A'.$o,'服务') ;
        foreach ($model['cust_type_name']['fuwu'] as $arr ){
            if(!empty($arr['list'])){
                foreach ($arr['list'] as $list){
                    $o=$o+1;
                    $objActSheet->setCellValue('B'.$o,$this->getStatusName($list[0]['status'])) ;
                    $objActSheet->setCellValue('C'.$o,	date_format(date_create($list[0]['status_dt']),"Y/m/d")) ;
                    $objActSheet->setCellValue('D'.$o,date_format(date_create($list[0]['first_dt']),"Y/m/d")) ;
                    $objActSheet->setCellValue('E'.$o,$list[0]['company_name']) ;
                    $objActSheet->setCellValue('F'.$o,$this->getCustTypeName($list[0]['cust_type'])) ;
                    $objActSheet->setCellValue('G'.$o,$this->getCustTypeNamec($list[0]['cust_type_name'])) ;
                    $objActSheet->setCellValue('H'.$o,$list[0]['pieces']) ;
                    $objActSheet->setCellValue('I'.$o,$list[0]['amt_install']) ;
                    $objActSheet->setCellValue('J'.$o,$list[0]['salesman']) ;
                    $objActSheet->setCellValue('K'.$o,$list[0]['prepay_month']) ;
                }
            }

        }
        $o=$o+1;
        $objActSheet->setCellValue('A'.$o,'装机') ;
        if(!empty($this->cust_type_name['zhuangji']['list'])){
            foreach ($this->cust_type_name['zhuangji']['list'] as $list){
                $o=$o+1;
                $objActSheet->setCellValue('B'.$o,$this->getStatusName($list[0]['status'])) ;
                $objActSheet->setCellValue('C'.$o,	date_format(date_create($list[0]['status_dt']),"Y/m/d")) ;
                $objActSheet->setCellValue('D'.$o,date_format(date_create($list[0]['first_dt']),"Y/m/d")) ;
                $objActSheet->setCellValue('E'.$o,$list[0]['company_name']) ;
                $objActSheet->setCellValue('F'.$o,$this->getCustTypeName($list[0]['cust_type'])) ;
                $objActSheet->setCellValue('G'.$o,$this->getCustTypeNamec($list[0]['cust_type_name'])) ;
                $objActSheet->setCellValue('H'.$o,$list[0]['pieces']) ;
                $objActSheet->setCellValue('I'.$o,$list[0]['amt_install']) ;
                $objActSheet->setCellValue('J'.$o,$list[0]['salesman']) ;
                $objActSheet->setCellValue('K'.$o,$list[0]['prepay_month']) ;
            }
        }
        $o=$o+1;
        $objActSheet->setCellValue('A'.$o,'预收') ;
        //print_r('<pre>');print_r($this->cust_type_name['yushou']);exit();
        foreach ($model['cust_type_name']['yushou'] as $arr ){
            if(!empty($arr['list'])){
                foreach ($arr['list'] as $list){
                    $o=$o+1;
                    $objActSheet->setCellValue('B'.$o,$this->getStatusName($list[0]['status'])) ;
                    $objActSheet->setCellValue('C'.$o,	date_format(date_create($list[0]['status_dt']),"Y/m/d")) ;
                    $objActSheet->setCellValue('D'.$o,date_format(date_create($list[0]['first_dt']),"Y/m/d")) ;
                    $objActSheet->setCellValue('E'.$o,$list[0]['company_name']) ;
                    $objActSheet->setCellValue('F'.$o,$this->getCustTypeName($list[0]['cust_type'])) ;
                    $objActSheet->setCellValue('G'.$o,$this->getCustTypeNamec($list[0]['cust_type_name'])) ;
                    $objActSheet->setCellValue('H'.$o,$list[0]['pieces']) ;
                    $objActSheet->setCellValue('I'.$o,$list[0]['amt_install']) ;
                    $objActSheet->setCellValue('J'.$o,$list[0]['salesman']) ;
                    $objActSheet->setCellValue('K'.$o,$list[0]['prepay_month']) ;
                }
            }

        }

        $objPHPExcel->setActiveSheetIndex(0);
//        print_r("<pre>");
//        print_r($model);
//        exit();
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        ob_start();
        $objWriter->save('php://output');
        $output = ob_get_clean();
        spl_autoload_register(array('YiiBase','autoload'));
        $time=time();
        $str="templates/month_".$time.".xlsx";
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="'.$str.'"');
        header("Content-Transfer-Encoding:binary");
        echo $output;
    }

    //改版後的下載（正在使用）
	public function downExNew(){
        Yii::$enableIncludePath = false;
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        spl_autoload_unregister(array('YiiBase','autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel;
        $objReader  = PHPExcel_IOFactory::createReader('Excel2007');
        $path = Yii::app()->basePath.'/commands/template/integral.xlsx';

        $objPHPExcel = $objReader->load($path);
        $i=16;//第一页的行数（进行中）
        $o=2;//第二页的行数（进行中）
        $objPHPExcel->getSheet(1)->setTitle('详情列表');
        $objPHPExcel->getSheet(0)->setTitle('积分表单');
        $objActSheetOne=$objPHPExcel->setActiveSheetIndex(0);
        $objActSheetTwo=$objPHPExcel->setActiveSheetIndex(1);
        foreach ($this->cust_type_name as $key=>$rows) {
            if (is_array($rows)) {
                //写入第一页内容
                $this->setExcelRowForOne($i,$rows["list"],$objActSheetOne);
                if($key=="OTHER"){ //由于其它服务的小计有三行，所以分开处理
                    $i+=3;
                }else{
                    $i+=2;
                }
                $objActSheetOne->setCellValue('G'.$i, $rows['count']);
                $i+=2;
                //写入第二页内容
                if($key!="OTHER"){
                    $objActSheetTwo->setCellValue('A'.$o,$rows["name"]);
                    $this->setExcelRowForTwo($o,$rows['service'],$objActSheetTwo);
                }else{
                    $objActSheetTwo->getRowDimension('1')->setRowHeight(41);
                    $objActSheetTwo->getColumnDimension('L')->setWidth(25);
                    $objActSheetTwo->getColumnDimension('M')->setWidth(25);
                    $objActSheetTwo->setCellValue('L1',"剩余可算积分的数量\n(不包含本条服务的数量)");
                    $objActSheetTwo->setCellValue('M1',"积分实际计算数量");
                    $objActSheetTwo->setCellValue('N1',"录入日期");
                    $objActSheetTwo->setCellValue('O1',"修改日期");
                    $objActSheetTwo->setCellValue('P1',"修改人");
                    $objActSheetTwo->setCellValue('A'.$o,"装机");
                    $this->setExcelRowForTwo($o,$rows['insertService'],$objActSheetTwo);
                    $o++;
                    $objActSheetTwo->setCellValue('A'.$o,"预收");
                    $this->setExcelRowForTwo($o,$rows['payMonthService'],$objActSheetTwo);
                    if(strtotime($this->year."/".$this->month."/01")>=strtotime('2023/05/01')){
                        $o++;
                        $objActSheetTwo->setCellValue('A'.$o,"商业组重点开发客户");
                        $this->setExcelRowForTwo($o,$rows['groupService'],$objActSheetTwo);
                    }
                }
                $o++;
            }
        }
        $i++;
        $objActSheetOne->setCellValue('G'.$i,$this->cust_type_name["all_sum"]) ;
        $i++;
        $point=$this->cust_type_name['point']*100;
        $objActSheetOne->setCellValue('G'.$i, $point."%") ;
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        ob_start();
        $objWriter->save('php://output');
        $output = ob_get_clean();
        spl_autoload_register(array('YiiBase','autoload'));
        $time=time();
        $str="templates/month_".$time.".xlsx";
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="'.$str.'"');
        header("Content-Transfer-Encoding:binary");
        echo $output;
    }

    //添加excel第一页的内容
    protected function setExcelRowForOne(&$i,$list,$objActSheet){
	    if(!empty($list)){
            foreach ($list as $arr){
                $i++;
                $objActSheet->insertNewRowBefore($i + 1, 1);
                $objActSheet->setCellValue('A'.$i, $arr['cust_type_name']);
                $objActSheet->setCellValue('B'.$i, $arr['description']);
                $objActSheet->setCellValue('D'.$i, $arr['con_name']);
                $objActSheet->setCellValue('E'.$i, $arr['fraction']);
                $objActSheet->setCellValue('F'.$i, $arr['num']);
                $objActSheet->setCellValue('G'.$i, $arr['sum']);
                $objActSheet->setCellValue('H'.$i, $arr['remark']);
            }
        }
    }

    //添加excel第二页的内容
    protected function setExcelRowForTwo(&$o,$rows,$objActSheet){
        if(!empty($rows)){
            foreach ($rows as $list){
                if(isset($list['company_name_str'])){
                    $list['company_name'] = $list['company_name_str'];
                }
                $expr_num = key_exists("expr_num",$list)?$list["expr_num"]:"";
                $integral_num = key_exists("integralNum",$list)?$list["integralNum"]:"";
                $o++;
                $objActSheet->setCellValue('B'.$o,$this->getStatusName($list['status'])) ;
                $objActSheet->setCellValue('C'.$o,	date_format(date_create($list['status_dt']),"Y/m/d")) ;
                $objActSheet->setCellValue('D'.$o,date_format(date_create($list['first_dt']),"Y/m/d")) ;
                $objActSheet->setCellValue('E'.$o,$list['company_name']) ;
                $objActSheet->setCellValue('F'.$o,$list['description']) ;
                $objActSheet->setCellValue('G'.$o,$list['cust_type_name_name']) ;
                $objActSheet->setCellValue('H'.$o,$list['pieces']) ;
                $objActSheet->setCellValue('I'.$o,$list['amt_install']) ;
                $objActSheet->setCellValue('J'.$o,$this->employee_name." ({$this->employee_code})") ;
                $objActSheet->setCellValue('K'.$o,$list['prepay_month']) ;
                $objActSheet->setCellValue('L'.$o,$expr_num) ;
                $objActSheet->setCellValue('M'.$o,$integral_num) ;
                $objActSheet->setCellValue('N'.$o,$list['lcd']) ;
                $objActSheet->setCellValue('O'.$o,$list['lud']) ;
                $objActSheet->setCellValue('P'.$o,$list['luu']) ;
            }
        }
    }


	public function custTypeNameA($id){
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select * from swoper$suffix.swo_customer_type_twoname where cust_type_id='$id'";
        $row = Yii::app()->db->createCommand($sql)->queryAll();
        return $row;
    }

    public function points($id){
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select * from sal_points_type_twoname where cust_type_id=(select id from sal_points_type where rpt_cat='$id')";
        $row = Yii::app()->db->createCommand($sql)->queryAll();
        return $row;
    }

    public function custTypeNameB($id){
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select * from swoper$suffix.swo_customer_type_twoname where cust_type_id!='$id'";
        $row = Yii::app()->db->createCommand($sql)->queryAll();
        return $row;
    }
	
     public function  getCustTypeName($a){
         $suffix = Yii::app()->params['envSuffix'];
         $sql = "select description from swoper$suffix.swo_customer_type where id='$a'";
         $row = Yii::app()->db->createCommand($sql)->queryScalar();
         return $row;
     }

    public function  getCustTypeNamec($a){
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select cust_type_name from swoper$suffix.swo_customer_type_twoname where id='$a'";
        $row = Yii::app()->db->createCommand($sql)->queryScalar();
        return $row;
    }

    public function  getConditionsName($a){
	   if($a==1){
           $row='每部';
       }elseif ($a==2){
	       $row='每个新客户';
       }elseif ($a==3){
           $row='每个新客户订购一包';
       }elseif ($a==4){
           $row='每个新客户每桶';
       }elseif ($a==5){
           $row='每个新客户每箱';
       }elseif ($a==6){
           $row='每月';
       }
       if(empty($a)){
           $row='每部';
       }
        return $row;
    }

    //翻译服务状态
    public function  getStatusName($a){
        if($a=='N'){
            $row='新增';
        }elseif ($a=='C'){
            $row='续约';
        }elseif ($a=='A'){
            $row='更改';
        }elseif ($a=='S'){
            $row='暂停';
        }elseif ($a=='R'){
            $row='恢复';
        }elseif ($a=='T'){
            $row='终止';
        }else{
            $row='';
        }

        return $row;
    }

    //显示表格
    public function getTableHtml(){
        $html = "";
        foreach ($this->cust_type_name as $key=>$rows){
            if(is_array($rows)){
                //表格头部
                $html.="<tr>";
                $html.="<th>{$rows['name']}</th>";
                $html.="<th>类别</th>";
                $html.="<th>单位</th>";
                $html.="<th>条件</th>";
                $html.="<th>分数</th>";
                $html.='<th style="width: 70px;">当月数量</th>';
                $html.="<th>当月分数</th>";
                $html.="<th>备注</th>";
                $html.="</tr>";
                foreach ($rows["list"] as $row){
                    //表格数据
                    $html.= "<tr>";
                    $html.= "<td>{$row['cust_type_name']}</td>";
                    $html.= "<td>{$row['description']}</td>";
                    $html.= "<td>&nbsp;</td>";
                    $html.= "<td>{$row['con_name']}</td>";
                    $html.= "<td>{$row['fraction']}</td>";
                    $html.= "<td>{$row['num']}</td>";
                    $html.= "<td>{$row['sum']}</td>";
                    $html.= "<td>{$row['remark']}</td>";
                    $html.= "</tr>";
                }
                //小计
                $html.=  "<tr>";
                $html.=  "<td colspan='5'> </td>";
                $html.=  "<td style='background-color: #9acfea;'> 小计</td>";
                $html.=  "<td style='background-color: #9acfea'>{$rows['count']}</td>";
                $html.=  "<td> </td>";
                $html.=  " </tr>";
            }
        }
        return $html;
    }

    public function isReadAll() {
        return Yii::app()->user->validFunction('CN09');
    }
}
