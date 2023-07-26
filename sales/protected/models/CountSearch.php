<?php

/**
 * 合同同比分析、查詢
 */
class CountSearch{

    private static $whereSQL=" and not(f.rpt_cat='INV' and f.single=1)";
    private static $IDBool=true;//是否需要ID服務的查詢

    private static $system=0;//0:大陸 1:台灣 2:國際

    //獲取暫停、終止的最後一條記錄(一条服务在一个月内只能存在一条暂停和终止)，特例：暫停→恢復→終止（三個都需要計算）
    public static function getServiceForST($start_dt,$end_dt,$city_allow){
        $suffix = Yii::app()->params['envSuffix'];
        $list = array();
        $sum_money = "case b.paid_type when 'M' then b.amt_paid * b.ctrt_period else b.amt_paid end";

        $whereSql = "b.status in ('S','T') and b.status_dt BETWEEN '{$start_dt}' and '{$end_dt}'";
        if(!empty($city_allow)&&$city_allow!="all"){
            $whereSql.= " and b.city in ({$city_allow})";
        }
        $whereSql.=self::$whereSQL;
        $rows= Yii::app()->db->createCommand()
            ->select("a.id,a.status,a.status_dt,a.contract_no,a.service_id,
            b.city,({$sum_money}) as sum_money,
            (case b.paid_type
                    when 'M' then b.amt_paid
                    else if(b.ctrt_period='' or b.ctrt_period is null,0,b.amt_paid/b.ctrt_period)
                end
            ) as num_month,
            DATE_FORMAT(a.status_dt,'%Y/%m') as month_date")
            ->from("swoper{$suffix}.swo_service_contract_no a")
            ->leftJoin("swoper{$suffix}.swo_service b","b.id=a.service_id")
            ->leftJoin("swoper{$suffix}.swo_customer_type f","b.cust_type=f.id")
            ->where($whereSql)
            ->queryAll();
        if($rows){//
            foreach ($rows as $row){
                $city = $row["city"];
                if(!key_exists($city,$list)){
                    $list[$city]=array(
                        "num_pause"=>0,//暫停金額（年金額）
                        "num_stop"=>0,//停單金額（年金額）
                        "num_month"=>0,//停單金額（月金額）
                    );
                }
                $nextRow= Yii::app()->db->createCommand()
                    ->select("status")->from("swoper{$suffix}.swo_service_contract_no")
                    ->where("contract_no='{$row["contract_no"]}' and 
                        id!='{$row["id"]}' and 
                        status_dt>'{$row['status_dt']}' and 
                        DATE_FORMAT(status_dt,'%Y/%m')='{$row['month_date']}'")
                    ->order("status_dt asc")
                    ->queryRow();//查詢本月的後面一條數據
                if($nextRow&&in_array($nextRow["status"],array("S","T"))){
                    continue;//如果下一條數據是暫停或者終止，則不統計本條數據
                }else{
                    $money = round($row["sum_money"],2);
                    if($row["status"]=="T"){
                        $list[$city]["num_stop"]+=$money;
                        $list[$city]["num_month"]+= empty($row["num_month"])?0:round($row["num_month"],2);
                    }else{
                        $list[$city]["num_pause"]+=$money;
                    }
                }
            }
        }

        if(self::$IDBool){ //ID服務的暫停、終止
            $rows = Yii::app()->db->createCommand()
                ->select("sum(b.amt_paid*b.ctrt_period) as sum_amount,sum(b.amt_paid) as num_month,b.city,b.status")
                ->from("swoper{$suffix}.swo_serviceid b")
                ->leftJoin("swoper{$suffix}.swo_customer_type_id f","b.cust_type=f.id")
                ->where($whereSql)->group("b.city,b.status")->queryAll();//
            if($rows){
                foreach ($rows as $row){
                    if(!key_exists($row["city"],$list)){
                        $list[$row["city"]]=array(
                            "num_pause"=>0,//暫停金額（年金額）
                            "num_stop"=>0,//停單金額（年金額）
                            "num_month"=>0,//停單金額（月金額）
                        );
                    }
                    $money = empty($row["sum_amount"])?0:round($row["sum_amount"],2);
                    if($row["status"]=="S"){ //暫停
                        $list[$row["city"]]["num_pause"]+= $money;
                    }else{
                        $list[$row["city"]]["num_stop"]+= $money;
                        $list[$row["city"]]["num_month"]+= empty($row["num_month"])?0:round($row["num_month"],2);
                    }
                }
            }
        }
        return $list;
    }

    //客户服务查询(根據服務類型)
    public static function getServiceForType($startDate,$endDate,$city_allow="",$type="N"){
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = "a.status='{$type}' and a.status_dt BETWEEN '{$startDate}' and '{$endDate}'";
        if(!empty($city_allow)&&$city_allow!="all"){
            $whereSql.= " and a.city in ({$city_allow})";
        }
        $whereSql .= self::$whereSQL;
        $list=array();
        $rows = Yii::app()->db->createCommand()
            ->select("sum(case a.paid_type
							when 'M' then a.amt_paid * a.ctrt_period
							else a.amt_paid
						end
					) as sum_amount,a.city")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("swoper{$suffix}.swo_customer_type f","a.cust_type=f.id")
            ->where($whereSql)->group("a.city")->queryAll();
        $rows = $rows?$rows:array();

        if(self::$IDBool){
            $IDRows = Yii::app()->db->createCommand()
                ->select("sum(a.amt_paid*a.ctrt_period) as sum_amount,a.city")
                ->from("swoper{$suffix}.swo_serviceid a")
                ->leftJoin("swoper{$suffix}.swo_customer_type_id f","a.cust_type=f.id")
                ->where($whereSql)->group("a.city")->queryAll();//
            $IDRows = $IDRows?$IDRows:array();
            $rows = array_merge($rows,$IDRows);
        }
        foreach ($rows as $row){
            if(!key_exists($row["city"],$list)){
                $list[$row["city"]]=0;
            }
            $list[$row["city"]]+=$row["sum_amount"];
        }
        return $list;
    }

    //客户服务查询(根據服務類型)
    public static function getServiceForT($startDate,$endDate,$city_allow=""){
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = "a.status='T' and a.status_dt BETWEEN '{$startDate}' and '{$endDate}'";
        if(!empty($city_allow)&&$city_allow!="all"){
            $whereSql.= " and a.city in ({$city_allow})";
        }
        $whereSql .= self::$whereSQL;
        $list=array();
        $rows = Yii::app()->db->createCommand()
            ->select("sum(case a.paid_type
							when 'M' then a.amt_paid * a.ctrt_period
							else a.amt_paid
						end
					) as sum_amount,
					sum(case a.paid_type
                    when 'M' then a.amt_paid
                    else if(a.ctrt_period='' or a.ctrt_period is null,0,a.amt_paid/a.ctrt_period)
                    end
           		) as sum_month,a.city")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("swoper{$suffix}.swo_customer_type f","a.cust_type=f.id")
            ->where($whereSql)->group("a.city")->queryAll();
        $rows = $rows?$rows:array();

        if(self::$IDBool){
            $IDRows = Yii::app()->db->createCommand()
                ->select("sum(a.amt_paid*a.ctrt_period) as sum_amount,sum(a.amt_paid) as sum_month,a.city")
                ->from("swoper{$suffix}.swo_serviceid a")
                ->leftJoin("swoper{$suffix}.swo_customer_type_id f","a.cust_type=f.id")
                ->where($whereSql)->group("a.city")->queryAll();//
            $IDRows = $IDRows?$IDRows:array();
            $rows = array_merge($rows,$IDRows);
        }
        foreach ($rows as $row){
            if(!key_exists($row["city"],$list)){
                $list[$row["city"]]=array(
                    "sum_amount"=>0,//本周年金额
                    "sum_month"=>0//本周月金额
                );
            }
            $list[$row["city"]]["sum_amount"]+=$row["sum_amount"];
            $list[$row["city"]]["sum_month"]+=$row["sum_month"];
        }
        return $list;
    }

    //月金额超过1000元/月的停单客户
    public static function getServiceRowsForT($startDate,$endDate,$city_allow=""){
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = "a.status='T' and a.status_dt BETWEEN '{$startDate}' and '{$endDate}'";
        if(!empty($city_allow)&&$city_allow!="all"){
            $whereSql.= " and a.city in ({$city_allow})";
        }
        $whereSql .= self::$whereSQL;
        $monthSql = "(case a.paid_type
                when 'M' then a.amt_paid
                else if(a.ctrt_period='' or a.ctrt_period is null,0,a.amt_paid/a.ctrt_period)
                end
            )";
        $gtSqlIA="";
        $gtSqlID="";
        switch (self::$system){
            case 0://大陸
                $gtSqlIA= " and {$monthSql}>=1000";
                $gtSqlID= " and a.amt_paid>=1000";
                break;
            case 1://台灣
                $gtSqlIA= " and {$monthSql}>=4000";
                $gtSqlID= " and a.amt_paid>=4000";
                break;
            case 2://國際
                //$city=="MY"?600:200;
                $gtSqlIA= " and ((a.city='MY' and {$monthSql}>=600)or(a.city!='MY' and {$monthSql}>=200))";
                $gtSqlID= " and ((a.city='MY' and a.amt_paid>=600)or(a.city!='MY' and a.amt_paid>=200))";
                break;
        }
        $list=array();
        $rows = Yii::app()->db->createCommand()
            ->select("a.city,a.status_dt,a.service,a.reason,a.amt_paid,a.ctrt_period,a.paid_type,
            com.code,com.name,f.description as type_name,g.description as nature_name,
            (case a.paid_type
                when 'M' then a.amt_paid * a.ctrt_period
                else a.amt_paid
                end
            ) as stopMoneyForYear,
            {$monthSql} as stopMoneyForMonth
            ")
            ->from("swoper{$suffix}.swo_service a")
            //->leftJoin("swoper{$suffix}.swo_service_contract_no n","a.id=n.service_id")
            ->leftJoin("swoper{$suffix}.swo_company com","com.id=a.company_id")
            ->leftJoin("swoper{$suffix}.swo_customer_type f","a.cust_type=f.id")
            ->leftJoin("swoper{$suffix}.swo_nature g","a.nature_type=g.id")
            ->where($whereSql.$gtSqlIA)
            ->order("a.city")->queryAll();
        $rows = $rows?$rows:array();

        if(self::$IDBool){
            $IDRows = Yii::app()->db->createCommand()
                ->select("a.city,a.status_dt,a.service,a.reason,a.amt_paid,a.ctrt_period,CONCAT('M') as paid_type,
            com.code,com.name,f.description as type_name,g.description as nature_name,
            (a.amt_paid*a.ctrt_period) as stopMoneyForYear,(a.amt_paid) as stopMoneyForMonth")
                ->from("swoper{$suffix}.swo_serviceid a")
                ->leftJoin("swoper{$suffix}.swo_company com","com.id=a.company_id")
                ->leftJoin("swoper{$suffix}.swo_customer_type_id f","a.cust_type=f.id")
                ->leftJoin("swoper{$suffix}.swo_nature g","a.nature_type=g.id")
                ->where($whereSql.$gtSqlID)->queryAll();//
            $IDRows = $IDRows?$IDRows:array();
            $rows = array_merge($rows,$IDRows);
        }
        foreach ($rows as $row){
            if(!key_exists($row["city"],$list)){
                $list[$row["city"]]=array();
            }
            $row["stopMoneyForYear"]=floatval($row["stopMoneyForYear"]);
            $row["stopMoneyForMonth"]=floatval($row["stopMoneyForMonth"]);
            $list[$row["city"]][]=$row;
        }
        return $list;
    }

    //客户服务的匯總（新增+恢復+更改-暫停-終止)
    public static function getServiceForAll($startDate,$endDate,$city_allow=""){
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = "a.status in ('N','S','A','R','T') and a.status_dt BETWEEN '{$startDate}' and '{$endDate}'";
        if(!empty($city_allow)&&$city_allow!="all"){
            $whereSql.= " and a.city in ({$city_allow})";
        }
        $whereSql .= self::$whereSQL;
        $list=array();
        $sumAmtSql = "case a.paid_type when 'M' then a.amt_paid * a.ctrt_period else a.amt_paid end";
        $b4_sumAmtSql = "case a.b4_paid_type when 'M' then a.b4_amt_paid * a.ctrt_period else a.b4_amt_paid end";
        $rows = Yii::app()->db->createCommand()
            ->select("
            sum(
                if(a.status in ('N','R'),($sumAmtSql),
                    if(a.status='A',($sumAmtSql)-($b4_sumAmtSql),-1*($sumAmtSql))
                )
            ) as sum_amount,a.city")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("swoper{$suffix}.swo_customer_type f","a.cust_type=f.id")
            ->where($whereSql)->group("a.city")->queryAll();
        $rows = $rows?$rows:array();

        if(self::$IDBool){
            $IDRows = Yii::app()->db->createCommand()
                ->select("sum(
                        if(a.status in ('N','R'),(a.amt_paid*a.ctrt_period),
                            if(a.status='A',(a.amt_paid*a.ctrt_period)-(a.b4_amt_paid*a.ctrt_period),-1*(a.amt_paid*a.ctrt_period))
                        )
                    ) as sum_amount,a.city")
                ->from("swoper{$suffix}.swo_serviceid a")
                ->leftJoin("swoper{$suffix}.swo_customer_type_id f","a.cust_type=f.id")
                ->where($whereSql)->group("a.city")->queryAll();//
            $IDRows = $IDRows?$IDRows:array();
            $rows = array_merge($rows,$IDRows);
        }
        foreach ($rows as $row){
            if(!key_exists($row["city"],$list)){
                $list[$row["city"]]=0;
            }
            $list[$row["city"]]+=$row["sum_amount"];
        }
        return $list;
    }

    //客户服务查询(更改)
    public static function getServiceForA($startDate,$endDate,$city_allow=""){
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = "a.status='A' and a.status_dt BETWEEN '{$startDate}' and '{$endDate}'";
        if(!empty($city_allow)&&$city_allow!="all"){
            $whereSql.= " and a.city in ({$city_allow})";
        }
        $whereSql .= self::$whereSQL;
        $list=array();
        $rows = Yii::app()->db->createCommand()
            ->select("sum(case a.paid_type
							when 'M' then a.amt_paid * a.ctrt_period
							else a.amt_paid
						end
					) as sum_amount,sum(case a.b4_paid_type
							when 'M' then a.b4_amt_paid * a.ctrt_period
							else a.b4_amt_paid
						end
					) as b4_sum_amount,a.city")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("swoper{$suffix}.swo_customer_type f","a.cust_type=f.id")
            ->where($whereSql)->group("a.city")->queryAll();
        $rows = $rows?$rows:array();

        if(self::$IDBool){
            $IDRows = Yii::app()->db->createCommand()
                ->select("sum(a.amt_paid*a.ctrt_period) as sum_amount,sum(a.b4_amt_money) as b4_sum_amount,a.city")
                ->from("swoper{$suffix}.swo_serviceid a")
                ->leftJoin("swoper{$suffix}.swo_customer_type_id f","a.cust_type=f.id")
                ->where($whereSql)->group("a.city")->queryAll();
            $IDRows = $IDRows?$IDRows:array();
            $rows = array_merge($rows,$IDRows);
        }
        foreach ($rows as $row){
            if(!key_exists($row["city"],$list)){
                $list[$row["city"]]=0;
            }
            $list[$row["city"]]+=$row["sum_amount"]-$row["b4_sum_amount"];
        }
        return $list;
    }

    //服务新增詳情(長約、短約、一次性服務、餐飲、非餐飲)
    public static function getServiceDetailForAdd($startDay,$endDay,$city_allow=""){
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = "a.status='N' and a.status_dt BETWEEN '{$startDay}' and '{$endDay}'";
        if(!empty($city_allow)&&$city_allow!="all"){
            $whereSql.= " and a.city in ({$city_allow})";
        }
        $whereSql .= self::$whereSQL;
        $list = array();
        $sum_money = "case a.paid_type when 'M' then a.amt_paid * a.ctrt_period else a.amt_paid end";
        $rows = Yii::app()->db->createCommand()
            ->select("sum($sum_money) as sum_amount,a.city,
            sum(if(a.ctrt_period>=12,({$sum_money}),0)) as num_long,
            sum(if(a.ctrt_period<12 and a.paid_type!=1,({$sum_money}),0)) as num_short,
            sum(if(a.ctrt_period<12 and a.paid_type=1,({$sum_money}),0)) as one_service,
            sum(if(g.rpt_cat='A01',({$sum_money}),0)) as num_cate,
            sum(if(g.rpt_cat!='A01',({$sum_money}),0)) as num_not_cate
            ")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("swoper{$suffix}.swo_customer_type f","a.cust_type=f.id")
            ->leftJoin("swoper{$suffix}.swo_nature g","a.nature_type=g.id")
            ->where($whereSql)
            ->group("a.city")->queryAll();
        $rows = $rows?$rows:array();

        if(self::$IDBool){
            $IDRows = Yii::app()->db->createCommand()
                ->select("sum(a.amt_paid*a.ctrt_period) as sum_amount,a.city,
            sum(if(a.ctrt_period>=12,a.amt_paid*a.ctrt_period,0)) as num_long,
            sum(if(a.ctrt_period<12,a.amt_paid*a.ctrt_period,0)) as num_short,
            CONCAT(0) as one_service,
            sum(if(g.rpt_cat='A01',a.amt_paid*a.ctrt_period,0)) as num_cate,
            sum(if(g.rpt_cat!='A01',a.amt_paid*a.ctrt_period,0)) as num_not_cate
            ")
                ->from("swoper{$suffix}.swo_serviceid a")
                ->leftJoin("swoper{$suffix}.swo_customer_type_id f","a.cust_type=f.id")
                ->leftJoin("swoper{$suffix}.swo_nature g","a.nature_type=g.id")
                ->where($whereSql)->group("a.city")->queryAll();//ID服務暫時全部為非一次性服務
            $IDRows = $IDRows?$IDRows:array();
            $rows = array_merge($rows,$IDRows);
        }
        foreach ($rows as $row){
            if(!key_exists($row["city"],$list)){
                $list[$row["city"]]=array(
                    "sum_amount"=>0,//
                    "num_long"=>0,//长约（>=12月）
                    "num_short"=>0,//短约
                    "one_service"=>0,//一次性服務
                    "num_cate"=>0,//餐饮客户
                    "num_not_cate"=>0,//非餐饮客户
                );
            }
            $list[$row["city"]]["sum_amount"]+=$row["sum_amount"];
            $list[$row["city"]]["num_long"]+=$row["num_long"];
            $list[$row["city"]]["num_short"]+=$row["num_short"];
            $list[$row["city"]]["one_service"]+=$row["one_service"];
            $list[$row["city"]]["num_cate"]+=$row["num_cate"];
            $list[$row["city"]]["num_not_cate"]+=$row["num_not_cate"];
        }
        return $list;
    }

    //服务新增INV(餐飲、非餐飲)(台灣版專用)
    public static function getServiceTWForAdd($startDay,$endDay,$city_allow=""){
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = "a.status='N' and f.rpt_cat='INV' and a.status_dt BETWEEN '{$startDay}' and '{$endDay}'";
        if(!empty($city_allow)&&$city_allow!="all"){
            $whereSql.= " and a.city in ({$city_allow})";
        }
        $list = array();
        $sum_money = "case a.paid_type when 'M' then a.amt_paid * a.ctrt_period else a.amt_paid end";
        $rows = Yii::app()->db->createCommand()
            ->select("sum($sum_money) as sum_amount,a.city,
            sum(if(g.rpt_cat='A01',({$sum_money}),0)) as num_cate,
            sum(if(g.rpt_cat!='A01',({$sum_money}),0)) as num_not_cate
            ")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("swoper{$suffix}.swo_customer_type f","a.cust_type=f.id")
            ->leftJoin("swoper{$suffix}.swo_nature g","a.nature_type=g.id")
            ->where($whereSql)
            ->group("a.city")->queryAll();
        $rows = $rows?$rows:array();

        foreach ($rows as $row){
            if(!key_exists($row["city"],$list)){
                $list[$row["city"]]=array(
                    "sum_money"=>0,
                    "u_num_cate"=>0,//餐饮客户
                    "u_num_not_cate"=>0//非餐饮客户
                );
            }
            $list[$row["city"]]["sum_money"]+=$row["sum_amount"];
            $list[$row["city"]]["u_num_cate"]+=$row["num_cate"];
            $list[$row["city"]]["u_num_not_cate"]+=$row["num_not_cate"];
        }
        return $list;
    }

    //服务新增（非一次性 和 一次性)
    public static function getServiceAddForNY($startDay,$endDay,$city_allow=""){
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = "a.status='N' and a.status_dt BETWEEN '{$startDay}' and '{$endDay}'";
        if(!empty($city_allow)&&$city_allow!="all"){
            $whereSql.= " and a.city in ({$city_allow})";
        }
        $whereSql .= self::$whereSQL;
        $sum_money = "case a.paid_type when 'M' then a.amt_paid * a.ctrt_period else a.amt_paid end";
        $list = array();
        $rows = Yii::app()->db->createCommand()
            ->select("sum({$sum_money}) as sum_amount,a.city,
            sum(if(a.paid_type=1 and a.ctrt_period<12,({$sum_money}),0)) as num_new_n,
            sum(if(a.paid_type=1 and a.ctrt_period<12,0,({$sum_money}))) as num_new
            ")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("swoper{$suffix}.swo_customer_type f","a.cust_type=f.id")
            ->where($whereSql)->group("a.city")->queryAll();
        $rows = $rows?$rows:array();

        if(self::$IDBool){
            $IDRows = Yii::app()->db->createCommand()
                ->select("sum(a.amt_paid*a.ctrt_period) as sum_amount,a.city,
                sum(a.amt_paid*a.ctrt_period) as num_new,
                CONCAT(0) as num_new_n")
                ->from("swoper{$suffix}.swo_serviceid a")
                ->leftJoin("swoper{$suffix}.swo_customer_type_id f","a.cust_type=f.id")
                //->leftJoin("swoper{$suffix}.swo_customer_type_id g","a.cust_type_name=g.id")
                ->where($whereSql)->group("a.city")->queryAll();//ID服務暫時全部為非一次性服務
            $IDRows = $IDRows?$IDRows:array();
            $rows = array_merge($rows,$IDRows);
        }
        foreach ($rows as $row){
            if(!key_exists($row["city"],$list)){
                $list[$row["city"]]=array(
                    "num_new"=>0,
                    "num_new_n"=>0,
                );
            }
            $list[$row["city"]]["num_new"]+=$row["num_new"];
            $list[$row["city"]]["num_new_n"]+=$row["num_new_n"];
        }
        return $list;
    }

    //服务新增（非一次性)
    public static function getServiceAddForN($startDay,$endDay,$city_allow=""){
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = "a.status='N' and a.status_dt BETWEEN '{$startDay}' and '{$endDay}'";
        if(!empty($city_allow)&&$city_allow!="all"){
            $whereSql.= " and a.city in ({$city_allow})";
        }
        $whereSql .= self::$whereSQL;
        $list = array();
        $rows = Yii::app()->db->createCommand()
            ->select("sum(case a.paid_type
							when 'M' then a.amt_paid * a.ctrt_period
							else a.amt_paid
						end
					) as sum_amount,a.city")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("swoper{$suffix}.swo_customer_type f","a.cust_type=f.id")
            ->where($whereSql." and not (a.paid_type=1 and a.ctrt_period<12)")->group("a.city")->queryAll();
        $rows = $rows?$rows:array();

        if(self::$IDBool){
            $IDRows = Yii::app()->db->createCommand()
                ->select("sum(a.amt_paid*a.ctrt_period) as sum_amount,a.city")
                ->from("swoper{$suffix}.swo_serviceid a")
                ->leftJoin("swoper{$suffix}.swo_customer_type_id f","a.cust_type=f.id")
                //->leftJoin("swoper{$suffix}.swo_customer_type_id g","a.cust_type_name=g.id")
                ->where($whereSql)->group("a.city")->queryAll();//ID服務暫時全部為非一次性服務
            $IDRows = $IDRows?$IDRows:array();
            $rows = array_merge($rows,$IDRows);
        }
        foreach ($rows as $row){
            if(!key_exists($row["city"],$list)){
                $list[$row["city"]]=0;
            }
            $list[$row["city"]]+=$row["sum_amount"];
        }
        return $list;
    }

    //服务新增（一次性)
    public static function getServiceAddForY($startDay,$endDay,$city_allow=""){
        $suffix = Yii::app()->params['envSuffix'];
        $whereSql = "a.status='N' and a.status_dt BETWEEN '{$startDay}' and '{$endDay}'";
        if(!empty($city_allow)&&$city_allow!="all"){
            $whereSql.= " and a.city in ({$city_allow})";
        }
        $whereSql .= self::$whereSQL;
        $list = array();
        $rows = Yii::app()->db->createCommand()
            ->select("sum(case a.paid_type
							when 'M' then a.amt_paid * a.ctrt_period
							else a.amt_paid
						end
					) as sum_amount,a.city")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("swoper{$suffix}.swo_customer_type f","a.cust_type=f.id")
            ->where($whereSql." and a.paid_type=1 and a.ctrt_period<12")->group("a.city")->queryAll();
        $rows = $rows?$rows:array();

        if(self::$IDBool){
            /* ID服務暫時全部為非一次性服務
            $IDRows = Yii::app()->db->createCommand()
                ->select("sum(a.amt_paid*a.ctrt_period) as sum_amount,a.city")
                ->from("swoper{$suffix}.swo_serviceid a")
                ->leftJoin("swoper{$suffix}.swo_customer_type_id f","a.cust_type=f.id")
                ->leftJoin("swoper{$suffix}.swo_customer_type_id g","a.cust_type_name=g.id")
                ->where($whereSql." and g.single=1")->group("a.city")->queryAll();
            $IDRows = $IDRows?$IDRows:array();
            $rows = array_merge($rows,$IDRows);
            */
        }
        foreach ($rows as $row){
            if(!key_exists($row["city"],$list)){
                $list[$row["city"]]=0;
            }
            $list[$row["city"]]+=$row["sum_amount"];
        }
        return $list;
    }

    //获取生意額的数据(U系統服務生意額 + U系統產品金額)
    public static function getUActualMoney($startDay,$endDay,$city_allow=""){
        $uServiceList = self::getUServiceMoney($startDay,$endDay,$city_allow);
        $uData = self::getUInvMoney($startDay,$endDay);
        foreach ($uData as $city=>$row){
            if(!key_exists($city,$uServiceList)){
                $uServiceList[$city]=0;
            }
            $uServiceList[$city]+=$row["sum_money"];
        }
        return $uServiceList;
    }

    //获取U系统的服务单数据
    public static function getUServiceMoney($startDay,$endDay,$city_allow=""){
        $list = array();
        $citySql = "";
        if(!empty($city_allow)&&$city_allow!="all"){
            $citySql = " and b.Text in ({$city_allow})";
        }
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
            ->select("b.Text,sum(
                    if(a.TermCount=0,0,a.Fee/a.TermCount)
					) as sum_amount")
            ->from("service{$suffix}.joborder a")
            ->leftJoin("service{$suffix}.officecity f","a.City = f.City")
            ->leftJoin("service{$suffix}.enums b","f.Office = b.EnumID and b.EnumType=8")
            ->where("a.Status=3 and a.JobDate BETWEEN '{$startDay}' AND '{$endDay}' {$citySql}")
            ->group("b.Text")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $city = self::resetCity($row["Text"]);
                $money = empty($row["sum_amount"])?0:round($row["sum_amount"],2);
                if(!key_exists($city,$list)){
                    $list[$city]=0;
                }
                $list[$city]+=$money;
            }
        }
        return $list;
    }

    //获取U系统的產品数据
    public static function getUInvMoney($startDay,$endDay,$city_allow=""){
        if(self::$system===1){//台灣版的產品為lbs的inv新增
            return self::getServiceTWForAdd($startDay,$endDay,$city_allow);
        }
        $city = "";
        if(!empty($city_allow)&&$city_allow!="all"){
            $city = $city_allow;
        }
        $json = Invoice::getInvData($startDay,$endDay,$city);
        $list = array();
        if($json["message"]==="Success"){
            $jsonData = $json["data"];
            foreach ($jsonData as $row){
                $city = self::resetCity($row["city"]);
                $money = is_numeric($row["invoice_amt"])?floatval($row["invoice_amt"]):0;
                if(!key_exists($city,$list)){
                    $list[$city]=array(
                        "sum_money"=>0,
                        "u_num_cate"=>0,
                        "u_num_not_cate"=>0
                    );
                }
                $list[$city]["sum_money"]+=$money;
                if($row["customer_type"]==="餐饮类"){
                    $list[$city]["u_num_cate"]+=$money;
                }else{
                    $list[$city]["u_num_not_cate"]+=$money;
                }
            }
        }
        return $list;
    }

    public static function computeLastMonth($date,$diffMonth=1){
        $lastDate = date("Y/m/d",strtotime($date." - {$diffMonth} month"));
        $maxDay = date("t",strtotime($date));
        $thisDay = date("d",strtotime($date));
        $lastDay = date("d",strtotime($lastDate));
        if($maxDay==$thisDay){
            if($lastDay<$thisDay){ //大月份转小月份
                $lastDate = date("Y/m/01",strtotime($lastDate));
                $lastDate = date("Y/m/d",strtotime($lastDate." - 1 day"));
            }elseif($lastDay==$thisDay){ //小月份转大月份
                $lastDate = date("Y/m/t",strtotime($lastDate));
            }elseif($lastDay>$thisDay){ //本情况不可能发生
                //$lastDate = date("Y/m/t",strtotime($lastDate));
            }
        }else{
            if($thisDay!=$lastDay){
                $lastDate = date("Y/m/01",strtotime($lastDate));
                $lastDate = date("Y/m/d",strtotime($lastDate." - 1 day"));
            }
        }

        return $lastDate;
    }

    //轉換U系統的城市（國際版專用）
    public static function resetCity($city){
        if(self::$system===2){
            switch($city){
                case "KL":
                    return "MY";
                case "SL":
                    return "MY";
            }
        }
        return $city;
    }

    //獲取簽單類型的拜訪類型
    public static function getDealString($field) {
        $rtn = '';
        $sql = "select id from sal_visit_obj where rpt_type='DEAL'";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        foreach ($rows as $row) {
            $rtn .= ($rtn=='' ? '' : ' or ').$field." like '%\"".$row['id']."\"%'";
        }
        return ($rtn=='' ? "$field='0'" : $rtn);
    }

    //獲取5個月以內簽單類型的拜訪總金額（根據賬號、月份分類）
    public static function getStaffOldMonthData($arr){
        $date = date("Y/m/01",strtotime($arr["end_dt"]));
        $startDate = date("Y/m/01",strtotime($date." -4 month"));
        $endDate = date("Y/m/t",strtotime($date));
        $dateTemp = array();
        for($i=0;$i<=4;$i++){
            $dateKey = $i===0?date("Y/m",strtotime($date)):date("Y/m",strtotime($date." -{$i} month"));
            $dateTemp[$dateKey]=0;
        }
        $list = array();
        $obj_where = self::getDealString("b.visit_obj");
        $rows = Yii::app()->db->createCommand()->select("b.username,DATE_FORMAT(b.visit_dt,'%Y/%m') as yearMonth,sum(convert(a.field_value, decimal(12,2))) as money")
            ->from("sal_visit_info a")
            ->leftJoin("sal_visit b","a.visit_id=b.id")
            ->where("a.field_id in ('svc_A7','svc_B6','svc_C7','svc_D6','svc_E7','svc_F4','svc_G3') and b.visit_dt BETWEEN '{$startDate}' AND '{$endDate}' and ({$obj_where})")
            ->group("b.username,yearMonth")->queryAll();
        if($rows){
            foreach ($rows as $row){
                if(!key_exists($row['username'],$list)){
                    $list[$row['username']]=$dateTemp;
                }
                $list[$row['username']][$row['yearMonth']]=floatval($row['money']);
            }
        }
        return $list;
    }
}