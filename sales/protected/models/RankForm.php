<?php

class RankForm extends CFormModel
{
    protected $webroot;
    public $id;
    public $season;
    public $city;
    public $date;
    public $visit = array();
    public $ia = array();
    public $pyx = array();
    public $cp = array();
    public $jq = array();
    public $xdy = array();
    public $lhcity = array();
    public $lhsum = array();
    public $lhmoney = array();
    public $sales = array();
    public $food = array();
    public $fjl;
    public $city_xs;
    public $city_jb;
    public $now;//当月获得
    public $all_score;//当月所有得分乘以倍数后
    public $now_score;//当月总分(所有加起来)
    public $last_score;//上赛季分数
    public $initial_score;//初始分数
    public $rank_name;
    public $name;
    public $score_xsj;
    public $score_xsj_day;
    public $score_mc;
    public $score_mc_day;
    public $score_3bq;
    public $score_3bq_day;
    public $score_five;
    public $ruzhi;
    public $ruzhi_day;


    public function attributeLabels()
    {
        return array(
            'season' => Yii::t('sales', 'Season'),
            'city' => Yii::t('sales', 'City'),
        );
    }

    public function rules()
    {
        return array(
            array('', 'required'),
            array('id,sale_day,', 'safe'),
        );
    }

    public function init()
    {
        $this->city = '';
        $this->season = "";
//        $this->month=date("m");
//        $this->year=date("Y");
//        $this->staffs_desc = Yii::t('misc','All');
    }


    public function retrieveData($index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select a.*,b.* from sales$suffix.sal_rank a
              left outer join  sal_rankday b on  a.id=b.rank_id
              where a.id='$index'";
        $rows = Yii::app()->db->createCommand($sql)->queryRow();
        $this->id = $index;
        $city = $rows['city'];
        $cityname = $this->cityname($city);
        $year = date("Y", strtotime($rows['month']));//当前赛季时间年
        $month = date("m", strtotime($rows['month']));//当前赛季时间月
        $this->date = $month;
        $star_time = date("Y-m-01", strtotime($rows['month']));//当前赛季開始时间
        $end_time = date("Y-m-31", strtotime($rows['month']));//当前赛季結束时间
        //上赛季分数
        if ($rows['last_score'] < 0 || empty($rows['last_score'])) {
            $rows['last_score'] = 0;
        }
        $this->last_score = $rows['last_score'];
        //赛季
        $this->season = $this->numToWord($rows['season']);
        //销售人员名称
        $sql_name = "select a.* from hr$suffix.hr_binding a
                    left outer join  hr$suffix.hr_employee b on  a.employee_id=b.id where a.user_id='" . $rows['username'] . "'";
        $name = Yii::app()->db->createCommand($sql_name)->queryRow();
        $this->name = $name['employee_name'];
        // 銷售每月签单得分
        //IAIB
        $sql_ia = "select a.* from swoper$suffix.swo_service a
                  left outer join  hr$suffix.hr_employee b on  concat(b.name, ' (', b.code, ')')=a.salesman
                  left outer join  hr$suffix.hr_binding c on  c.employee_id=b.id
                  where a.status_dt>='$star_time' and a.status_dt<='$end_time' and a.status='N' and (a.cust_type='1' or a.cust_type='2') and c.user_id='" . $rows['username'] . "' and a.city='$city'
                  ";
        $rows_ia = Yii::app()->db->createCommand($sql_ia)->queryAll();
        $ia = 0;
        foreach ($rows_ia as $row) {
            if ($row['paid_type'] == 'M') {
                $amt_paid_year_a = $row['amt_paid'] * $row['ctrt_period'];
            } elseif ($row['paid_type'] == 'Y') {
                $amt_paid_year_a = $row['amt_paid'];
            } else {
                $amt_paid_year_a = $row['amt_paid'];
            }
            $ia += $amt_paid_year_a;
        }
        $ia_A = count($rows_ia);
        $amount_ia = $this->getAmount('1', $star_time, $ia);//本单产品提成比例
        $score_ia = $ia * $amount_ia['coefficient'] * (1 + 0.01 * ($ia_A - 1));
        $this->ia['sum'] = $ia_A;
        $this->ia['money'] = $ia;
        $this->ia['score'] = round($score_ia, 2);
        //飘盈香
        $sql_pyx = "select a.* from swoper$suffix.swo_service a
                  left outer join  hr$suffix.hr_employee b on  concat(b.name, ' (', b.code, ')')=a.salesman
                 left outer join  hr$suffix.hr_binding c on  c.employee_id=b.id
                  where a.status_dt>='$star_time' and a.status_dt<='$end_time' and a.status='N' and (a.cust_type='5' or a.cust_type_name='50') and c.user_id='" . $rows['username'] . "' and a.city='$city'
                  ";
        $rows_pyx = Yii::app()->db->createCommand($sql_pyx)->queryAll();
        $pyx = 0;
        foreach ($rows_pyx as $row_pyx) {
            if ($row_pyx['paid_type'] == 'M') {
                $amt_paid_year_a = $row_pyx['amt_paid'] * $row_pyx['ctrt_period'];
            } elseif ($row_pyx['paid_type'] == 'Y') {
                $amt_paid_year_a = $row_pyx['amt_paid'];
            } else {
                $amt_paid_year_a = $row_pyx['amt_paid'];
            }
            $pyx += $amt_paid_year_a;

        }
        $pyx_A = count($rows_pyx);
        $amount_ia = $this->getAmount('2', $star_time, $pyx);//本单产品提成比例
        $score_pyx = $pyx * $amount_ia['coefficient'] * (1 + 0.02 * ($pyx_A - 1));
        $this->pyx['sum'] = $pyx_A;
        $this->pyx['money'] = $pyx;
        $this->pyx['score'] = round($score_pyx, 2);
        //产品
        $sql_cp = "select b.log_dt,b.company_name,a.money,a.qty,c.description,c.sales_products,c.id from swoper$suffix.swo_logistic_dtl a
                left outer join swoper$suffix.swo_logistic b on b.id=a.log_id		
               	left outer join swoper$suffix.swo_task c on a.task=c.	id
               	  left outer join  hr$suffix.hr_employee d on  concat(d.name, ' (', d.code, ')')=b.salesman
                  left outer join  hr$suffix.hr_binding e on  e.employee_id=d.id
                where b.log_dt<='$end_time' and  b.log_dt>='$star_time' and e.user_id='" . $rows['username'] . "' and b.city ='$city' and a.money>0  and c.sales_products!='chemical'";
        $rows_cp = Yii::app()->db->createCommand($sql_cp)->queryAll();
        $cp = 0;
        foreach ($rows_cp as $row_cp) {
            $cp += $row_cp['money'] * $row_cp['qty'];
        }
        $cp_A = count($rows_cp);
        $amount_cp = $this->getAmount('3', $star_time, $cp);//本单产品提成比例
        $score_cp = $cp * $amount_cp['coefficient'];
        // $this->cp['sum']=$cp_A;
        $this->cp['money'] = $cp;
        $this->cp['score'] = round($score_cp, 2);
        //洗涤易
        $sql_xdy = "select b.log_dt,b.company_name,a.money,a.qty,c.description,c.sales_products,c.id from swoper$suffix.swo_logistic_dtl a
                left outer join swoper$suffix.swo_logistic b on b.id=a.log_id		
               	left outer join swoper$suffix.swo_task c on a.task=c.	id
               	  left outer join  hr$suffix.hr_employee d on  concat(d.name, ' (', d.code, ')')=b.salesman
                  left outer join  hr$suffix.hr_binding e on  e.employee_id=d.id
                where b.log_dt<='$end_time' and  b.log_dt>='$star_time' and e.user_id='" . $rows['username'] . "' and b.city ='$city' and a.money>0  and c.sales_products='chemical'";
        $rows_xdy = Yii::app()->db->createCommand($sql_xdy)->queryAll();
        $xdy = 0;
        foreach ($rows_xdy as $row_xdy) {
            $xdy += $row_xdy['money'] * $row_xdy['qty'];
        }
        $xdy_A = count($rows_xdy);
        $amount_xdy = $this->getAmount('4', $star_time, $xdy);//本单产品提成比例
        $score_xdy = $xdy * $amount_xdy['coefficient'] * (1 + 0.02 * (($xdy_A) - 1));
        $this->xdy['sum'] = $xdy_A;
        $this->xdy['money'] = $xdy;
        $this->xdy['score'] = round($score_xdy, 2);
        //甲醛
        $sql_jq = "select a.* from swoper$suffix.swo_service a
                  left outer join  hr$suffix.hr_employee b on  concat(b.name, ' (', b.code, ')')=a.salesman
                 left outer join  hr$suffix.hr_binding c on  c.employee_id=b.id
                  where a.status_dt>='$star_time' and a.status_dt<='$end_time' and a.status='N' and (a.cust_type='6' or a.cust_type_name='28') and c.user_id='" . $rows['username'] . "' and a.city='$city'
                  ";
        $rows_jq = Yii::app()->db->createCommand($sql_jq)->queryAll();
        $jq = 0;
        foreach ($rows_jq as $row_jq) {
            if ($row_jq['paid_type'] == 'M') {
                $amt_paid_year_a = $row_jq['amt_paid'] * $row_jq['ctrt_period'];
            } elseif ($row_jq['paid_type'] == 'Y') {
                $amt_paid_year_a = $row_jq['amt_paid'];
            } else {
                $amt_paid_year_a = $row_jq['amt_paid'];
            }
            $jq += $amt_paid_year_a;

        }
        $jq_A = count($rows_jq);
        $amount_jq = $this->getAmount('6', $star_time, $jq);//本单产品提成比例
        $score_jq = $jq * $amount_jq['coefficient'] * (1 + 0.02 * ($jq_A - 1));
        $this->jq['sum'] = $jq_A;
        $this->jq['money'] = $jq;
        $this->jq['score'] = round($score_jq, 2);
        //每月销售龙虎榜城市人均签单量排名
        $sales = ReportRankinglistForm::salelist($star_time, $end_time);
        $sales_copy = $sales;
        foreach ($sales as $k => $v) {
            if ($v['city'] == $cityname) {
                $this->lhcity['sum'] = $k + 1;
            }
        }
        if (empty($this->lhcity['sum'])) {
            $this->lhcity['sum'] = '无';
        }
        $sales_one = array_pop($sales_copy);
        $sales_two = array_pop($sales_copy);
        $score_sales_one = $sales_one['city'] == $cityname ? -3000 : 0;
        $score_sales_two = $sales_two['city'] == $cityname ? -2000 : 0;
        $this->lhcity['score'] = $score_sales_one + $score_sales_two;
        //每月销售龙虎榜城市人均签单金额排名
        $salemoney = ReportRankinglistForm::salelists($star_time, $end_time);
        $salemoney_copy = $salemoney;
        foreach ($salemoney as $k => $v) {
            if ($v['city'] == $cityname) {
                $this->lhsum['sum'] = $k + 1;
            }
        }
        if (empty($this->lhsum['sum'])) {
            $this->lhsum['sum'] = '无';
        }
        $salemoney_one = array_pop($salemoney_copy);
        $salemoney_two = array_pop($salemoney_copy);
        $score_salemoney_one = $salemoney_one['city'] == $cityname ? -3000 : 0;
        $score_salemoney_two = $salemoney_two['city'] == $cityname ? -2000 : 0;
        $this->lhsum['score'] = $score_salemoney_one + $score_salemoney_two;
        //每月销售龙虎榜销售排名
        $salepeople = ReportRankinglistForm::salepeople($star_time, $end_time);
        foreach ($salepeople as $k => $v) {
            if ($v['user'] == $rows['username']) {
                $this->lhmoney['sum'] = $k + 1;
            }
        }
        if (empty($this->lhmoney['sum'])) {
            $this->lhmoney['sum'] = '无';
        }
        if (!empty($salepeople)) {
            for ($a = 0; $a < count($salepeople); $a++) {
                if ($salepeople[$a]['user'] == $rows['username']) {
                    if ($a == 0) {
                        $salepeople_money = 5000;
                    } elseif ($a == 1) {
                        $salepeople_money = 3000;
                    } elseif ($a == 2) {
                        $salepeople_money = 1500;
                    } elseif ($a > 2 && $a < 10) {
                        $salepeople_money = 500;
                    } elseif ($a >= 10 && $a < 15) {
                        $salepeople_money = 300;
                    } elseif ($a >= 15 && $a <= 20) {
                        $salepeople_money = 100;
                    } elseif ($a >= (count($salepeople) - 10) && $a < count($salepeople)) {
                        $salepeople_money = -500;
                    } else {
                        $salepeople_money = 0;
                    }
                }
            }
        } else {
            $salepeople_money = 0;
        }
        $salepeople_money = empty($salepeople_money) ? 0 : $salepeople_money;
        $this->lhmoney['score'] = $salepeople_money;
        //五部曲分数
        $sql_entry = "select a.* from hr$suffix.hr_employee a 
                                          left outer join hr$suffix.hr_binding b on a.id=b.employee_id                                      
                                          where b.user_id='" . $rows['username'] . "'
                                          ";
        $entry_time = Yii::app()->db->createCommand($sql_entry)->queryRow();
        $five_time1 = date("Y-m-d", strtotime($entry_time['entry_time']));//計算分數时间月
//        $five_time2 = date("m-d", strtotime("$entry_time +6 month" ));//計算分數时间月
//        $five_time1=$year."-".$five_time1;
//        $five_time2=$year."-".$five_time2;
//        if($month==date("m", strtotime($five_time1))){
        //洗手間分數
        if (empty($rows['five_rank']) || $rows['five_rank'] == 1) {
            $score_xsj = $this->getFive($five_time1, $rows['username'], 1);
            $this->score_xsj = $score_xsj['score'];
            $this->score_xsj_day = $score_xsj['score_day'];
            $sql_rank_day = "update sal_rankday set five_rank=1 where rank_id='$index'";
            $rankday = Yii::app()->db->createCommand($sql_rank_day)->execute();
        } else {
            $this->score_xsj = 0;
        }
        //滅蟲分數
        if (empty($rows['mie_rank']) || $rows['mie_rank'] == 1) {
            $score_mc = $this->getFive($five_time1, $rows['username'], 0);
            $this->score_mc = $score_mc['score'];
            $this->score_mc_day = $score_mc['score_day'];
            $sql_rank_day = "update sal_rankday set mie_rank=1 where rank_id='$index'";
            $rankday = Yii::app()->db->createCommand($sql_rank_day)->execute();
        } else {
            $this->score_mc = 0;
        }
        //第三部曲分數
        if (empty($rows['three_rank']) || $rows['three_rank'] == 1) {
            $five_time_end = date("Y-m-d", strtotime("$five_time1 +15 day"));
            $sql_five = "select * from sal_fivestep where username='" . $rows['username'] . "' and rec_dt>='$five_time1' and rec_dt<='$five_time_end' and five_type='2'";
            $retern = Yii::app()->db->createCommand($sql_five)->queryAll();
            $sql_rank_day = "update sal_rankday set three_rank=1 where rank_id='$index'";
            $rankday = Yii::app()->db->createCommand($sql_rank_day)->execute();
            if (empty($retern)) {
                $this->score_3bq = 0;
                $this->score_3bq_day = '/';
            } else {
                $this->score_3bq = 1500;
                $this->score_3bq_day = '15天';
            }
        } else {
            $this->score_3bq = 0;
        }
        $this->score_five = $this->score_xsj + $this->score_mc + $this->score_3bq;

        //入职分数
        if ($rows['rank_day'] == 3) {
            $this->ruzhi = 2500;
            $this->ruzhi_day = '3个月';
        } elseif ($rows['rank_day'] == 1) {
            $this->ruzhi = 1000;
            $this->ruzhi_day = '1个月';
        } elseif ($rows['rank_day'] == 4) {
            $this->ruzhi = 0;
            $this->ruzhi_day = '/';
        } elseif ($rows['rank_day'] == 2) {
            $this->ruzhi = 1500;
            $this->ruzhi_day = '3个月';
        }
        //初始分数
        $this->initial_score = $this->score_five + $this->ruzhi;
        //总分数
        $score_all = $score_ia + $score_pyx + $score_cp + $score_xdy + $score_jq + $score_sales_one + $score_sales_two + $score_salemoney_one + $score_salemoney_two + $salepeople_money + $this->initial_score;
        //销售每月平均每天拜访记录  比例
        $sql_visit = "select count(id) as sums from sal_visit where username='" . $rows['username'] . "' and visit_dt>='$star_time'  and visit_dt<='$end_time'";
        $visit = Yii::app()->db->createCommand($sql_visit)->queryScalar();
        $sql_day = "select sale_day from sal_integral  where username='" . $rows['username'] . "' and year='$year' and month='$month'";
        $day = Yii::app()->db->createCommand($sql_day)->queryScalar();
        if (empty($day) || $day == 0) {
            $day = 22;
        }
        //当前上班天数
        //当前第几周
        $sb_day = date("d");
        $week = $this->get_week();
        $weekarray=array("日","一","二","三","四","五","六");
        if($weekarray[date("w")]=='日'){
            $sb_day = $sb_day-$week;
        }else{
            $sb_day = $sb_day-$week+1;
        }
        if ($sb_day > $day){
            $sb_day = $day;
        }
       // var_dump($visit);die();
        //$visit = 80;
        $sales_visit = $visit / $sb_day;//$day
        $this->visit['sum'] = round($sales_visit, 0);

        $amount_visit = $this->getAmount('0', $star_time, $this->visit['sum']);//本单产品提成比例
        $score_all = $score_all + $amount_visit['bonus'];
        $score_all_fs = $score_all;//判断负数的
        $this->now = round($score_all, 2);
        $score_all = $score_all * $amount_visit['coefficient'];
       $this->visit['score'] = $amount_visit['bonus'];
        //每月拜访获得积分
//        if (0<= $sales_visit && $sales_visit < 5) {
//            $this->visit['score'] = 0;
//        } elseif ( 5<= $sales_visit && $sales_visit < 10) {
//            $this->visit['score'] = 500;
//        } elseif (10<= $sales_visit && $sales_visit < 15) {
//            $this->visit['score'] = 1000;
//        } elseif (15<= $sales_visit) {
//            $this->visit['score'] = 2000;
//        }
        $this->visit['coefficient'] = round($amount_visit['coefficient'], 2);
//        print_r($sales_visit);exit();

        //地方销售人员/整体区比例
//        $sql_sales = "select count(a.username)
//				from security$suffix.sec_user a
//				left outer join security$suffix.sec_city b on a.city=b.code
//				left outer join security$suffix.sec_user_access c on a.username=c.username
//				left outer join hr$suffix.hr_binding d  on a.username=d.user_id
//				left outer join hr$suffix.hr_employee e  on d.employee_id=e.id
//                left outer join hr$suffix.hr_dept f  on e.department=f.id
//				where a.city='$city'  and c.system_id='sal'  and c.a_read_write like '%HK01%'  and a.status='A'  and   (f.manager_type ='1' or f.manager_type ='2')
//			";
//        $sales_people= Yii::app()->db->createCommand($sql_sales)->queryScalar();
        $sql_sales = "select data_value from swoper$suffix.swo_monthly_dtl where data_field='00061' and hdr_id=(select id from swoper$suffix.swo_monthly_hdr where city='$city' and year_no='$year' and month_no='$month')";
        $sales_people = Yii::app()->db->createCommand($sql_sales)->queryScalar();
        $sql_city = "select data_value from swoper$suffix.swo_monthly_dtl where data_field='00063' and hdr_id=(select id from swoper$suffix.swo_monthly_hdr where city='$city' and year_no='$year' and month_no='$month')";
        $sales_city = Yii::app()->db->createCommand($sql_city)->queryScalar();
        if (empty($sales_city) || $sales_city == 0) {
            $sales_city = 1;
        }
        $people = $sales_people / $sales_city;
        $this->sales['sum'] = round($people, 2);
        if ($people <= 0.25) {
            $score_all = $score_all * 0.4;
            $this->sales['score'] = 0.4;
        } elseif ($people > 0.25 && $people <= 0.5) {
            $score_all = $score_all * 0.7;
            $this->sales['score'] = 0.7;
        } elseif ($people > 0.5 && $people <= 0.75) {
            $score_all = $score_all * 1;
            $this->sales['score'] = 1;
        } elseif ($people > 0.75 && $people <= 1) {
            $score_all = $score_all * 1.1;
            $this->sales['score'] = 1.1;
        }

        //销售组别类型（餐饮组/商业组）
        $sql_food = "select a.group_type from hr$suffix.hr_employee a
                   left outer join hr$suffix.hr_binding b  on a.id=b.employee_id
                   where b.user_id='" . $rows['username'] . "'  and a.city='$city'
                 ";
        $food = Yii::app()->db->createCommand($sql_food)->queryScalar();
        if ($food == 0 || $food == 2) {
            $score_all = $score_all * 1;//餐饮组（或没分）
            $this->food['name'] = '餐饮组';
            $this->food['score'] = 1;
        } else {
            $score_all = $score_all * 1.1;
            $this->food['name'] = '商业组';
            $this->food['score'] = 1.1;
        }
        //销售岗位级别
        $sql_jl = "select * from hr$suffix.hr_employee a
                 left outer join hr$suffix.hr_binding b  on a.id=b.employee_id
                 left outer join hr$suffix.hr_dept c  on a.position=c.id
                 where b.user_id='" . $rows['username'] . "'  and a.city='$city' and c.dept_class='Sales' and c.name like '%经理%'
                 ";
        $jl = Yii::app()->db->createCommand($sql_jl)->queryAll();
        $sql_fjl = "select * from hr$suffix.hr_employee a
                 left outer join hr$suffix.hr_binding b  on a.id=b.employee_id
                 left outer join hr$suffix.hr_dept c  on a.position=c.id
                 where b.user_id='" . $rows['username'] . "'  and a.city='$city' and c.dept_class='Sales' and c.name not like '%经理%'
                 ";
        $fjl = Yii::app()->db->createCommand($sql_fjl)->queryAll();
        if (!empty($jl)) {
            $score_all = $score_all * 0.8;
            $this->fjl = 0.8;
        } elseif (!empty($fjl)) {
            $score_all = $score_all * 1.2;
            $this->fjl = 1.2;
        } else {
            $score_all = $score_all * 1.2;
            $this->fjl = 1.2;
        }
        //城市规模级别
        $year_no = $year - 1;
        $sql_city = "select sum(a.data_value) as data_value 
                    from swoper$suffix.swo_monthly_dtl a
                    left outer join swoper$suffix.swo_monthly_hdr b on a.hdr_id=b.id
                  where a.data_field='00002' and  b.city='$city' and b.year_no='$year_no'";
        $sales_city = Yii::app()->db->createCommand($sql_city)->queryScalar();
        $amount_city = $this->getAmount('7', $star_time, $sales_city);//城市规模级别
        $this->city_xs = $amount_city['coefficient'];
        $this->city_jb = $amount_city['bonus'];
        $score_all = $score_all * $this->city_xs;
        //当前赛季总分
        $this->all_score = round($score_all, 2);
        if ($score_all_fs < 0) {
            $this->all_score = round($score_all_fs, 2);
        }
        //当前赛季总分（继承后）
        $this->now_score = round($this->all_score + $this->last_score, 2);
        //$this->now_score = 30001;
        //上赛季段位
        $sql_rank_name = "select * from sal_level where start_fraction <='" . $this->now_score . "' and end_fraction >='" . $this->now_score . "'";
        $rank_name = Yii::app()->db->createCommand($sql_rank_name)->queryRow();
        $this->rank_name = $rank_name['level'];
        //分数变化
        if ($this->now_score != $this->last_score) {
            //变化前赛季
            $before_rank_name_sql = "select * from sal_level where start_fraction <='" . $this->last_score . "' and end_fraction >='" . $this->last_score . "'";
            $before_rank_name_data = Yii::app()->db->createCommand($before_rank_name_sql)->queryRow();
            $before_rank_name = $before_rank_name_data['level'];
            if ($before_rank_name != $this->rank_name) {
                //邮箱
                $sql1 = "SELECT email FROM security$suffix.sec_user WHERE username='".$name['user_id']."'";
                $rs = Yii::app()->db->createCommand($sql1)->queryRow();
                $email = $rs['email'];
                $this->Rankchageemail($suffix,$email,$cityname, $this->name,  $this->season, $this->date, $before_rank_name, $this->rank_name);
            }

        }


        $sql1 = "update sal_rank set all_score='" . $this->all_score . "',last_score='" . $this->last_score . "',now_score='" . $this->now_score . "',initial_score='" . $this->initial_score . "' where id='" . $index . "'";
        $command = Yii::app()->db->createCommand($sql1)->execute();
        return true;
    }

    public function Rankchageemail($suffix,$email,$city, $name, $season, $ondate, $before_rank, $after_rank)
    {
        $this->webroot = Yii::app()->params['webroot'];
        $subject = date('Y-m-d');
        $message = <<<EOF
<table border="" cellpadding="0" cellspacing="0" height="148" style="width:663px;" width="">
	<colgroup>
		<col />
		<col />
		<col />
		<col />
		<col />
		<col />
	</colgroup>
	<tbody>
		<tr height="36">
			<td colspan="6" height="36" style="height:36px;width:663px;" x:num="44275"><span style="font-size:14px;">{$subject}</span></td>
		</tr>
		<tr height="56">
			<td height="56" style="height: 56px; text-align: center;" x:str=""><span style="font-size:18px;"><span style="font-family:arial,helvetica,sans-serif;"><strong>地区</strong></span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:18px;"><span style="font-family:arial,helvetica,sans-serif;"><strong>姓名</strong></span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:18px;"><span style="font-family:arial,helvetica,sans-serif;"><strong>赛季</strong></span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:18px;"><span style="font-family:arial,helvetica,sans-serif;"><strong>月份</strong></span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:18px;"><span style="font-family:arial,helvetica,sans-serif;"><strong>当前段位</strong></span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:18px;"><span style="font-family:arial,helvetica,sans-serif;"><strong>变动前段位&nbsp;</strong></span></span></td>
		</tr>
		<tr height="56">
			<td height="56" style="height: 56px; text-align: center;" x:str=""><span style="font-size:16px;"><span style="font-family:arial,helvetica,sans-serif;">{$city}</span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:16px;"><span style="font-family:arial,helvetica,sans-serif;">{$name}</span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:16px;"><span style="font-family:arial,helvetica,sans-serif;">第{$season}赛季</span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:16px;"><span style="font-family:arial,helvetica,sans-serif;">{$ondate}月</span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:16px;"><span style="font-family:arial,helvetica,sans-serif;">{$after_rank}</span></span></td>
			<td style="text-align: center;" x:num="2500"><span style="font-size:16px;"><span style="font-family:arial,helvetica,sans-serif;">{$before_rank}</span></span></td>
		</tr>
	</tbody>
</table>
EOF;
        $from_addr = "it@lbsgroup.com.hk";
        $to_addr = "[\"" .$email."\"]";
//var_dump($email);die();
        $mail= new YiiMailer;
        $data = array('message' => $message, 'description'=>'', 'mailer'=>$mail);
        $mail->setData($data);
       $mail->useFileTransport = false;
        $mail->setFrom($from_addr);
        $mail->setSubject($subject);
        $mail->setTo($email);
       // $mail->setTo('2767758543@qq.com');
        $mail->setCc($email);
//        var_dump($mail->send());die();
        if($mail->send()){
            $lcu = "admin";
            $aaa = Yii::app()->db->createCommand()->insert("swoper$suffix.swo_email_queue", array(
                'request_dt' => date('Y-m-d H:i:s'),
                'from_addr' => $from_addr,
                'to_addr' => $to_addr,
                'subject' => $subject,//郵件主題
                'description' => '',//郵件副題
                'message' => $message,//郵件內容（html）
                'status' => "P",
                'lcu' => $lcu,
                'lcd' => date('Y-m-d H:i:s'),
            ));
        }else{
            echo "failse";
        }




    }

    public function getFive($five_time1, $username, $five_type)
    {//獲得五部曲分數
        $five_time_end = date("Y-m-d", strtotime("$five_time1 +3 day"));
        $sql_five = "select * from sal_fivestep where username='" . $username . "' and rec_dt>='$five_time1' and rec_dt<='$five_time_end' and five_type='$five_type'";
        $retern = Yii::app()->db->createCommand($sql_five)->queryAll();
        if (!empty($retern)) {
            $arr['score'] = 1500;
            $arr['score_day'] = '3天';
        } else {
            $five_time_end = date("Y-m-d", strtotime("$five_time1 +7 day"));
            $sql_five = "select * from sal_fivestep where username='" . $username . "' and rec_dt>='$five_time1' and rec_dt<='$five_time_end' and five_type='$five_type'";
            $retern = Yii::app()->db->createCommand($sql_five)->queryAll();
            if (!empty($retern)) {
                $arr['score'] = 1000;
                $arr['score_day'] = '7天';
            } else {
                $five_time_end = date("Y-m-d", strtotime("$five_time1 +30 day"));
                $sql_five = "select * from sal_fivestep where username='" . $username . "' and rec_dt>='$five_time1' and rec_dt<='$five_time_end' and five_type='$five_type'";
                $retern = Yii::app()->db->createCommand($sql_five)->queryAll();
                if (!empty($retern)) {
                    $arr['score'] = 500;
                    $arr['score_day'] = '30天';
                } else {
                    $arr['score'] = 0;
                    $arr['score_day'] = '/';
                }
            }
        }
        return $arr;
    }

    public function getAmount($cust_type, $start_dt, $sales_amt)
    {
        //城市，类别，时间，总金额
        $rtn = array();
        if (isset($cust_type) && !empty($start_dt) && isset($sales_amt)) {
            $suffix = Yii::app()->params['envSuffix'];
            //客户类别
            //  $sql = "select rpt_cat from swoper$suffix.swo_customer_type where id=$cust_type";
            //   $row = Yii::app()->db->createCommand($sql)->queryRow();
            //   if ($row!==false) {
            //  $type = $row['rpt_cat'];
            $sdate = General::toMyDate($start_dt);
            $sql = "select id from sal_coefficient_hdr where  start_dt<'$sdate'   order by start_dt desc limit 1";
            $row = Yii::app()->db->createCommand($sql)->queryRow();
            if ($row !== false) {
                $id = $row['id'];
                $sql = "select * from sal_coefficient_dtl
                        where hdr_id='$id' and name='$cust_type' and ((criterion>=$sales_amt and operator='LE')
                        or (criterion<$sales_amt and operator='GT'))
                        order by criterion limit 1
                    ";
                $row = Yii::app()->db->createCommand($sql)->queryRow();

                if ($row !== false) {
                    $rtn['bonus'] = $row['bonus'];
                    $rtn['coefficient'] = $row['coefficient'];
                } else {
                    $rtn['bonus'] = 0;
                    $rtn['coefficient'] = 0;
                }
            }
        } else {
            $rtn['bonus'] = 0;
            $rtn['coefficient'] = 0;
        }
        // }
//        print_r('<pre>');
     //  print_r($rtn);exit();
        return $rtn;
    }

    public function saveData()
    {
        $connection = Yii::app()->db;
        $transaction = $connection->beginTransaction();
        try {
            $this->saveTrans($connection);
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
            throw new CHttpException(404, 'Cannot update.' . $e->getMessage());
        }
    }


    protected function saveTrans(&$connection)
    {
        $sql = '';
        switch ($this->scenario) {
//			case 'delete':
//				$sql = "update sal_integral set
//						sal_day = :sal_day,
//						luu = :luu
//						where id = :id and city = :city
//					";
//				break;
//			case 'new':
//				$sql = "insert into acc_trans(
//						trans_dt, trans_type_code, acct_id,	trans_desc, amount,	status, city, luu, lcu) values (
//						:trans_dt, :trans_type_code, :acct_id, :trans_desc, :amount, 'A', :city, :luu, :lcu)";
//				break;
            case 'edit':
                $sql = "update sal_integral set 
						sale_day = :sale_day	  				  
						where id = :id 
					";
                break;
        }

        $command = $connection->createCommand($sql);
        if (strpos($sql, ':id') !== false)
            $command->bindParam(':id', $this->id, PDO::PARAM_INT);
        if (strpos($sql, ':sale_day') !== false)
            $command->bindParam(':sale_day', $this->sale_day, PDO::PARAM_INT);
        $command->execute();
        return true;
    }

    public function cityName($city)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select name from security$suffix.sec_city  where code='$city'";
        $name = Yii::app()->db->createCommand($sql)->queryScalar();
        return $name;
    }


    public function season()
    {
        $sql = "select season from sal_season group by season";
        $row = Yii::app()->db->createCommand($sql)->queryAll();
        $season = array();
        $i = 5;
        foreach ($row as $a) {
            $b = $i + 1;
            if ($i == 12) {
                $b = 1;
            }
            $season[$a['season']] = '第' . $this->numToWord($a['season']) . '赛季(' . $i . '-' . $b . '月)';
            $i = $i + 2;
            if ($i == 13) {
                $i = 1;
            }
        }
        $season = array_reverse($season, true);
        return $season;
    }

    public function numToWord($num)
    {
        $chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        $chiUni = array('', '十', '百', '千', '万', '亿', '十', '百', '千');
        $chiStr = '';
        $num_str = (string)$num;
        $count = strlen($num_str);
        $last_flag = true; //上一个 是否为0
        $zero_flag = true; //是否第一个
        $temp_num = null; //临时数字
        $chiStr = '';//拼接结果
        if ($count == 2) {//两位数
            $temp_num = $num_str[0];
            $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num] . $chiUni[1];
            $temp_num = $num_str[1];
            $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num];
        } else if ($count > 2) {
            $index = 0;
            for ($i = $count - 1; $i >= 0; $i--) {
                $temp_num = $num_str[$i];
                if ($temp_num == 0) {
                    if (!$zero_flag && !$last_flag) {
                        $chiStr = $chiNum[$temp_num] . $chiStr;
                        $last_flag = true;
                    }
                } else {
                    $chiStr = $chiNum[$temp_num] . $chiUni[$index % 9] . $chiStr;
                    $zero_flag = false;
                    $last_flag = false;
                }
                $index++;
            }
        } else {
            $chiStr = $chiNum[$num_str[0]];
        }
        return $chiStr;
    }

    public function readExcel()
    {
        Yii::$enableIncludePath = false;
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel;
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $path = Yii::app()->basePath . '/commands/template/readexcel.xlsx';
        $objPHPExcel = $objReader->load($path);
//print_r("<pre>");
//        print_r(count($model->record));
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        ob_start();
        $objWriter->save('php://output');
        $output = ob_get_clean();
        spl_autoload_register(array('YiiBase', 'autoload'));
        $time = time();
        $str = "templates/销售段位得分规则.xlsx";
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="' . $str . '"');
        header("Content-Transfer-Encoding:binary");
        echo $output;
    }


    public function adjustRight()
    {
        return Yii::app()->user->validFunction('HD01');
    }

    public function voidRight()
    {
        return Yii::app()->user->validFunction('HD01');
    }

    public function isReadOnly()
    {
//		return ($this->scenario=='view'||$this->status=='V'||$this->posted||!empty($this->req_ref_no)||!empty($this->t3_doc_no));
        return ($this->scenario != 'new' || $this->status == 'V' || $this->posted || !empty($this->req_ref_no) || !empty($this->t3_doc_no));
    }

    public function isReadAll()
    {
        return Yii::app()->user->validFunction('CN10');
    }
    function get_week(){
        #  本月第一天
        $oneDay = date('Y-m-01', time());
        #  本月天数
        $tolDay = date('d', strtotime("$oneDay +1 month -1 day"));
        #  获取今天的日期
        $day = date('d',time());
        #  计算本月第一天是周几
        $week = date('w',strtotime($oneDay));
        #  获取本月第一周有多少天
        switch($week){
            case 0;
                $weekDay = 1;
                break;
            case 1;
                $weekDay = 7;
                break;
            case 2;
                $weekDay = 6;
                break;
            case 3;
                $weekDay = 5;
                break;
            case 4;
                $weekDay = 4;
                break;
            case 5;
                $weekDay = 3;
                break;
            case 6;
                $weekDay = 2;
                break;
        }
        #  本月除去第一周剩余的天数
        $days = $tolDay - $weekDay;
        #  本月除了第一周还剩余多少周
        $d = ceil($days/7);
        #  本月第二周的第一天
        $w = $weekDay +1;



        $i = '0';
        $d = '2';
        for($w;$w<=$tolDay;$w++){
            $i++;
            if($i == '8'){
                $i = '1';
                $d++;
            }
            if($day == $w){
                return $d;
            }
        }
    }
}
