<?php
/* Reimbursement Form */

class ReportRankinglistForm extends CReportForm
{
    public $staffs;
    public $staffs_desc;

    protected function labelsEx() {
        return array(
            'staffs'=>Yii::t('report','Staffs'),
            'date'=>Yii::t('report','Date'),
        );
    }

    protected function rulesEx() {
        return array(
            array('staffs, staffs_desc','safe'),
        );
    }

    protected function queueItemEx() {
        return array(
            'STAFFS'=>$this->staffs,
            'STAFFSDESC'=>$this->staffs_desc,
        );
    }

    public function init() {
        $this->id = 'RptFive';
        $this->name = Yii::t('app','Five Steps');
        $this->format = 'EXCEL';
        $this->city = '';
        $this->fields = 'start_dt,end_dt,staffs,staffs_desc';
        $this->start_dt = date("Y/m/d");
        $this->end_dt = date("Y/m/d");
        $this->five = array();
        $this->date="";
        $this->staffs = '';
        $this->month="";
        $this->year="";
        $this->staffs_desc = Yii::t('misc','All');
    }

    public function retrieveDatas($model){
        $start_date = '2017-01-01'; // 自动为00:00:00 时分秒
        $end_date = date("Y-m-d");
        $start_arr = explode("-", $start_date);
        $end_arr = explode("-", $end_date);
        $start_year = intval($start_arr[0]);
        $start_month = intval($start_arr[1]);
        $end_year = intval($end_arr[0]);
        $end_month = intval($end_arr[1]);
        $diff_year = $end_year-$start_year;
        $year_arr=[];
        for($year=$end_year;$year>=$start_year;$year--){
            $year_arr[] = $year;
        }
        $this->date=$year_arr;
    }

    public function salepeople($start,$end,$onlyBool=false) {
        $suffix = Yii::app()->params['envSuffix'];
        $models = array();
       // $time= date('Y-m-d', strtotime(date('Y-m-01') ));
        $sql = "select a.city, a.username, sum(convert(b.field_value, decimal(12,2))) as money
				from sal_visit a force index (idx_visit_02), sal_visit_info b
				where a.id=b.visit_id and b.field_id in ('svc_A7','svc_B6','svc_C7','svc_D6','svc_E7') 
				and a.visit_dt >= '$start'and a.visit_dt <= '$end' and  a.visit_obj like '%10%'
				group by a.city, a.username
			";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        foreach ($records as $record) {
            $temp = array();
            $temp['user']=$record['username'];
            $temp['money']=$record['money'];

            $sql = "select a.name,f.manager_leave from hr$suffix.hr_employee a 
                    LEFT JOIN hr$suffix.hr_binding b ON a.id = b.employee_id
                    LEFT JOIN hr$suffix.hr_dept f on a.position=f.id
                    where b.user_id='".$record['username']."'";
            $row = Yii::app()->db->createCommand($sql)->queryRow();
            if($onlyBool){
                if(!$row||($row&&$row["manager_leave"]!=1)){
                    continue;
                }
            }
            $temp['name']= $row!==false ? $row['name'] : $record['username'];

            $sql = "select a.name as city_name, b.name as region_name 
					from security$suffix.sec_city a
					left outer join security$suffix.sec_city b on a.region=b.code
					where a.code='".$record['city']."'
				";
            $row = Yii::app()->db->createCommand($sql)->queryRow();
            $temp['city'] = $row!==false ? $row['city_name'] : $record['city'];
            $temp['quyu'] = $row!==false ? str_replace(array('1','2','3','4','5','6','7','8','9','0'),'',$row['region_name']) : '空';
            $sql_rank="select now_score from sal_rank where month>= '$start' and month<= '$end' and username='".$record['username']."'";
            $rank = Yii::app()->db->createCommand($sql_rank)->queryRow();
            $sql = "select * from sal_level where start_fraction <='" . $rank['now_score'] . "' and end_fraction >='" . $rank['now_score'] . "'";
            $rank_name = Yii::app()->db->createCommand($sql)->queryRow();
            $temp['level'] = $rank_name['level'];
            $temp['rank'] = $rank['now_score'];
            $models[] = $temp;
        }
        $last_names = array_column($models,'money');
        array_multisort($last_names,SORT_DESC,$models);
        //$models = array_slice($models, 0, 20);

        return $models;
    }

    public function salelist($start,$end) {
        $suffix = Yii::app()->params['envSuffix'];
        $models = array();
        $cities = General::getCityListWithNoDescendant();
       // $time= date('Y-m-d', strtotime(date('Y-m-01') ));
        foreach ($cities as $code=>$name) {
            $sum_arr = array();
            if (strpos("/'CS'/'H-N'/'HK'/'TC'/'ZS1'/'TP'/'TY'/'KS'/'TN'/'XM'/'KH'/'ZY'/'MO'/'RN'/'MY'/'WL'/'HN1'/","'".$code."'")===false) {
                $sql = "select a.name as city_name, b.name as region_name 
						from security$suffix.sec_city a
						left outer join security$suffix.sec_city b on a.region=b.code
						where a.code='$code'
					";
                $row = Yii::app()->db->createCommand($sql)->queryRow();
                $temp = $row!==false ? str_replace(array('1','2','3','4','5','6','7','8','9','0'),'',$row['region_name']) : '空';

                //人数
                $sql1="select distinct  username FROM sal_visit  WHERE city='$code' and visit_dt >='".$start."' and visit_dt <='".$end."'";
                $people = Yii::app()->db->createCommand($sql1)->queryAll();
                $peoples=count($people);
                if(!empty($people)){
                    //总单数
                    $sql2="select id from sal_visit where city='$code' and  visit_obj like '%10%' and visit_dt >='".$start."'and visit_dt <='".$end."'";
                    $sum = Yii::app()->db->createCommand($sql2)->queryAll();
                    foreach ($sum as $id){
                        $sqlid="select count(visit_id) as sum from  sal_visit_info where field_id in ('svc_A7','svc_B6','svc_C7','svc_D6','svc_E7','svc_F4','svc_G3') and field_value>'0' and visit_id='".$id['id']."'";
                        $arr = Yii::app()->db->createCommand($sqlid)->queryRow();
                        $sum_arr[]=$arr['sum'];
                    }
                    $sums=array_sum($sum_arr);
                    //人均签单数
                    $sale=$sums/($peoples==0?1:$peoples);
                }else{
                    $sale=0;
                }
                $sale=round($sale,2);
                $models[$code] = array('city'=>$name, 'renjun'=>$sale, 'quyu'=>$temp,'people'=>$peoples);

            }
        }
        foreach ($models as $key=>$item) {
            $result[] = $item;
        }

        $arraycol = array_column($result,'renjun');
        array_multisort($arraycol,SORT_DESC,$result);
//print_r('<pre>');
//print_r($models);
        return $result;
    }

    public function salelists($start,$end) {
        $suffix = Yii::app()->params['envSuffix'];
        $models = array();
        $cities = General::getCityListWithNoDescendant();
     //   $time= date('Y-m-d', strtotime(date('Y-m-01') ));
        foreach ($cities as $code=>$name) {
            if (strpos("/'CS'/'H-N'/'HK'/'TC'/'ZS1'/'TP'/'TY'/'KS'/'TN'/'XM'/'KH'/'ZY'/'MO'/'RN'/'MY'/'WL'/'HN1'/","'".$code."'")===false) {
                $sql = "select a.name as city_name, b.name as region_name 
						from security$suffix.sec_city a
						left outer join security$suffix.sec_city b on a.region=b.code
						where a.code='$code'
					";
                $row = Yii::app()->db->createCommand($sql)->queryRow();
                $temp = $row!==false ? str_replace(array('1','2','3','4','5','6','7','8','9','0'),'',$row['region_name']) : '空';

                //人数
                $sql1="select distinct  username FROM sal_visit  WHERE city='$code' and visit_dt >='".$start."'and visit_dt <='".$end."'";
                $people = Yii::app()->db->createCommand($sql1)->queryAll();
                $peoples=count($people);
                //总单数
                $sql2="select id from sal_visit where city='$code' and  visit_obj like '%10%' and visit_dt >='".$start."'and visit_dt <='".$end."'";
                $sum = Yii::app()->db->createCommand($sql2)->queryAll();
                $sums=count($sum);
                //人均签单数
                $sale=$sums/($peoples==0?1:$peoples);
                $sale=round($sale,2);

                //总金额
                $money=0;
                foreach ($sum as $b){
                    $sql3="select field_id, field_value from sal_visit_info where field_id in ('svc_A7','svc_B6','svc_C7','svc_D6','svc_E7') and visit_id = '".$b['id']."'";
                    $array = Yii::app()->db->createCommand($sql3)->queryAll();
                    $summoney = 0;
                    foreach($array as $item){
                        $summoney += $item['field_value'];
                    }
                    //总金额
                    $money+=$summoney;
                }
                $money=$money/($peoples==0?1:$peoples);
                $money=round($money,2);
                $models[$code] = array('city'=>$name, 'money'=>$money, 'quyu'=>$temp,'people'=>$peoples);
            }
        }
        foreach ($models as $key=>$item) {
            $result[] = $item;
        }

        $arraycol = array_column($result,'money');
        array_multisort($arraycol,SORT_DESC,$result);
//print_r('<pre>');
//print_r($result);
        return $result;
    }

    public function Ranklist($start,$end){
        $suffix = Yii::app()->params['envSuffix'];
        $models = array();
        $sql = "select a.city, a.username,a.now_score,c.name
				from sal_rank  a
				left outer join  hr$suffix.hr_binding b on a.username=b.user_id
				left outer join  hr$suffix.hr_employee c on b.employee_id=c.id
				where 
				a.month >= '$start' and  a.month <= '$end' 
                order by a.now_score desc
			";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        foreach ($records as $record) {
            if (strpos("/'CS'/'H-N'/'HK'/'TC'/'ZS1'/'TP'/'TY'/'KS'/'TN'/'XM'/'ZY'/'MO'/'RN'/'MY'/'WL'/'HN2'/'JMS'/'RW'/'HN1'/'HXHB'/'HD'/'HN'/'HD1'/'CN'/'HX'/'HB'/","'".$record['city']."'")===false) {
                $temp = array();
                $temp['user'] = $record['username'];
//            $sql = "select name from hr$suffix.hr_employee where id=(SELECT employee_id from hr$suffix.hr_binding WHERE user_id='".$record['username']."')";
//            $row = Yii::app()->db->createCommand($sql)->queryRow();
//            $temp['name']= $row!==false ? $row['name'] : $record['username'];
                $temp['name'] = $record['name'];
                $temp['rank'] = $record['now_score'];
                $sql = "select a.name as city_name, b.name as region_name 
					from security$suffix.sec_city a
					left outer join security$suffix.sec_city b on a.region=b.code
					where a.code='" . $record['city'] . "'
				";
                $row = Yii::app()->db->createCommand($sql)->queryRow();
                $temp['city'] = $row !== false ? $row['city_name'] : $record['city'];
                $temp['quyu'] = $row !== false ? str_replace(array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0'), '', $row['region_name']) : '空';

                $sql = "select * from sal_level where start_fraction <='" . $record['now_score'] . "' and end_fraction >='" . $record['now_score'] . "'";
                $rank_name = Yii::app()->db->createCommand($sql)->queryRow();
                $temp['level'] = $rank_name['level'];
                $models[] = $temp;
            }
        }
        //print_r();exit()
        $last_names = array_column($models,'rank');
        array_multisort($last_names,SORT_DESC,$models);
        $models = array_slice($models, 0, 20);
        return $models;
    }

    public function renaudlist($start,$end) {
        $suffix = Yii::app()->params['envSuffix'];
        $time= date('Y/m',strtotime($start));
        //生成查詢sql
        $tableSql = Yii::app()->db->createCommand()
            ->select("a.visit_id,b.username,
            MAX(CASE a.field_id WHEN 'svc_H2' THEN a.field_value+0 ELSE 0 END) as 'svc_H2',
            MAX(CASE a.field_id WHEN 'svc_H3' THEN a.field_value+0 ELSE 0 END) as 'svc_H3',
            MAX(CASE a.field_id WHEN 'svc_H6' THEN a.field_value+0 ELSE 0 END) as 'svc_H6'
            ")
            ->from("sal_visit_info a")
            ->leftJoin("sal_visit b","a.visit_id = b.id")
            ->where("date_format(b.visit_dt,'%Y/%m')='$time' and b.visit_obj like '%10%'")
            ->group("a.visit_id,b.username")
            ->getText();
        //排序及統計
        $result = Yii::app()->db->createCommand()
            ->select("f.username,
            COUNT(f.username) as amount,SUM(f.svc_H2)+SUM(f.svc_H3) as number,
            SUM(f.svc_H6) as money
            ")
            ->from("($tableSql) f")
            ->where("f.svc_H6 != 0")
            ->group("f.username")
            ->order("money desc")
            ->queryAll();
        if($result){
            //登錄賬戶補充員工名字、城市、區域
            foreach ($result as &$row){
                $list = Yii::app()->db->createCommand()
                    ->select("f.name as city_name,g.name as region_name,b.name")
                    ->from("hr$suffix.hr_binding a")
                    ->leftJoin("hr$suffix.hr_employee b","a.employee_id=b.id")
                    ->leftJoin("security$suffix.sec_city f","b.city=f.code")
                    ->leftJoin("security$suffix.sec_city g","f.region=g.code")
                    ->where("a.user_id=:user_id",array(":user_id"=>$row["username"]))
                    ->queryRow();
                $row["city_name"] = $list?$list["city_name"]:"";
                $row["region_name"] = $list?($list["region_name"]==null?"":$list["region_name"]):"";
                $row["name"] = $list?$list["name"]:"";
            }
            //var_dump($result);die();
            return $result;
        }else{
            return array();
        }
    }

	public function export($date, $data_1, $data_2, $data_3, $data_4, $renaudlist) {
		$excel = new ExcelToolEx;
		$excel->start();
		$excel->newFile();
		
		$excel->setActiveSheet(0);
		$excel->setColWidth(0,10);
		$excel->setColWidth(1,10);
		$excel->setColWidth(2,10);
		$excel->setColWidth(3,15);
		$excel->setColWidth(4,15);
		$excel->setColWidth(5,25);
		$excel->setCellFont(0,3,array('bold'=>true));
		$excel->setCellFont(1,3,array('bold'=>true));
		$excel->setCellFont(2,3,array('bold'=>true));
		$excel->setCellFont(3,3,array('bold'=>true));
		$excel->setCellFont(4,3,array('bold'=>true));
		$excel->setCellFont(5,3,array('bold'=>true));
		$title = Yii::t('report','List of total amount of individual sales signing').' '.date('Y/m',strtotime($date));
		$excel->writeReportTitle($title);
		$excel->writeCell(0,3,Yii::t('report','ranking'));
		$excel->writeCell(1,3,Yii::t('report','city'));
		$excel->writeCell(2,3,Yii::t('report','quyu'));
		$excel->writeCell(3,3,Yii::t('report','name'));
		$excel->writeCell(4,3,Yii::t('report','level'));
		$excel->writeCell(5,3,Yii::t('report','fuwumoney'));
		$row = 4;
		foreach ($data_1 as $record) {
			$excel->writeCell(0, $row, $row-3);
			$excel->writeCell(1, $row, $record['city']);
			$excel->writeCell(2, $row, $record['quyu']);
			$excel->writeCell(3, $row, $record['name']);
			$excel->writeCell(4, $row, $record['level']);
			$excel->writeCell(5, $row, number_format($record['money'],2));
			$row += 1;
		}

		$excel->createSheet();
		$excel->setActiveSheet(1);
		$excel->setColWidth(0,10);
		$excel->setColWidth(1,10);
		$excel->setColWidth(2,10);
		$excel->setColWidth(3,20);
		$excel->setColWidth(4,20);
		$excel->setCellFont(0,3,array('bold'=>true));
		$excel->setCellFont(1,3,array('bold'=>true));
		$excel->setCellFont(2,3,array('bold'=>true));
		$excel->setCellFont(3,3,array('bold'=>true));
		$excel->setCellFont(4,3,array('bold'=>true));
		$title = Yii::t('report','List of regional sales per capita order signing volume').' '.date('Y/m',strtotime($date));
		$excel->writeReportTitle($title);
		$excel->writeCell(0,3,Yii::t('report','ranking'));
		$excel->writeCell(1,3,Yii::t('report','city'));
		$excel->writeCell(2,3,Yii::t('report','quyu'));
		$excel->writeCell(3,3,Yii::t('report','sum'));
		$excel->writeCell(4,3,Yii::t('report','renjun'));
		$row = 4;
		foreach ($data_2 as $record) {
			$excel->writeCell(0, $row, $row-3);
			$excel->writeCell(1, $row, $record['city']);
			$excel->writeCell(2, $row, $record['quyu']);
			$excel->writeCell(3, $row, $record['people']);
			$excel->writeCell(4, $row, number_format($record['renjun'],2));
			$row += 1;
		}
		
		$excel->createSheet();
		$excel->setActiveSheet(2);
		$excel->setColWidth(0,10);
		$excel->setColWidth(1,10);
		$excel->setColWidth(2,10);
		$excel->setColWidth(3,20);
		$excel->setColWidth(4,20);
		$excel->setCellFont(0,3,array('bold'=>true));
		$excel->setCellFont(1,3,array('bold'=>true));
		$excel->setCellFont(2,3,array('bold'=>true));
		$excel->setCellFont(3,3,array('bold'=>true));
		$excel->setCellFont(4,3,array('bold'=>true));
		$title = Yii::t('report','List of regional sales per capita signed amount').' '.date('Y/m',strtotime($date));
		$excel->writeReportTitle($title);
		$excel->writeCell(0,3,Yii::t('report','ranking'));
		$excel->writeCell(1,3,Yii::t('report','city'));
		$excel->writeCell(2,3,Yii::t('report','quyu'));
		$excel->writeCell(3,3,Yii::t('report','sum'));
		$excel->writeCell(4,3,Yii::t('report','money'));
		$row = 4;
		foreach ($data_3 as $record) {
			$excel->writeCell(0, $row, $row-3);
			$excel->writeCell(1, $row, $record['city']);
			$excel->writeCell(2, $row, $record['quyu']);
			$excel->writeCell(3, $row, $record['people']);
			$excel->writeCell(4, $row, number_format($record['money'],2));
			$row += 1;
		}

		$excel->createSheet();
		$excel->setActiveSheet(3);
		$excel->setColWidth(0,10);
		$excel->setColWidth(1,15);
		$excel->setColWidth(2,15);
		$excel->setColWidth(3,25);
		$excel->setColWidth(4,10);
		$excel->setColWidth(5,10);
		$excel->setCellFont(0,3,array('bold'=>true));
		$excel->setCellFont(1,3,array('bold'=>true));
		$excel->setCellFont(2,3,array('bold'=>true));
		$excel->setCellFont(3,3,array('bold'=>true));
		$excel->setCellFont(4,3,array('bold'=>true));
		$excel->setCellFont(5,3,array('bold'=>true));
		$title = Yii::t('report','Sales ranking').' '.date('Y/m',strtotime($date));
		$excel->writeReportTitle($title);
		$excel->writeCell(0,3,Yii::t('report','ranking'));
		$excel->writeCell(1,3,Yii::t('report','level'));
		$excel->writeCell(2,3,Yii::t('report','name'));
		$excel->writeCell(3,3,Yii::t('report','rank'));
		$excel->writeCell(4,3,Yii::t('report','city'));
		$excel->writeCell(5,3,Yii::t('report','quyu'));
		$row = 4;
		foreach ($data_4 as $record) {
			$excel->writeCell(0, $row, $row-3);
			$excel->writeCell(1, $row, $record['level']);
			$excel->writeCell(2, $row, $record['name']);
			$excel->writeCell(3, $row, $record['rank']);
			$excel->writeCell(4, $row, $record['city']);
			$excel->writeCell(5, $row, $record['quyu']);
			$row += 1;
		}

		$excel->createSheet();
		$excel->setActiveSheet(4);
		$excel->setColWidth(0,10);
		$excel->setColWidth(1,15);
		$excel->setColWidth(2,15);
		$excel->setColWidth(3,25);
		$excel->setColWidth(4,10);
		$excel->setColWidth(5,25);
		$excel->setColWidth(6,25);
		$excel->setCellFont(0,3,array('bold'=>true));
		$excel->setCellFont(1,3,array('bold'=>true));
		$excel->setCellFont(2,3,array('bold'=>true));
		$excel->setCellFont(3,3,array('bold'=>true));
		$excel->setCellFont(4,3,array('bold'=>true));
		$excel->setCellFont(5,3,array('bold'=>true));
		$excel->setCellFont(6,3,array('bold'=>true));
		$title = Yii::t('app','Renaud Air List').' '.date('Y/m',strtotime($date));
		$excel->writeReportTitle($title);
		$excel->writeCell(0,3,Yii::t('report','ranking'));
		$excel->writeCell(1,3,Yii::t('report','name'));
		$excel->writeCell(2,3,Yii::t('report','city'));
		$excel->writeCell(3,3,Yii::t('report','quyu'));
		$excel->writeCell(4,3,Yii::t('sales','singular'));
		$excel->writeCell(5,3,Yii::t('sales','Score Ari'));
		$excel->writeCell(6,3,Yii::t('report','fuwumoney'));
		$row = 4;
		foreach ($renaudlist as $record) {
			$excel->writeCell(0, $row, $row-3);
			$excel->writeCell(1, $row, $record['name']);
			$excel->writeCell(2, $row, $record['city_name']);
			$excel->writeCell(3, $row, $record['region_name']);
			$excel->writeCell(4, $row, $record['amount']);
			$excel->writeCell(5, $row, $record['number']);
			$excel->writeCell(6, $row, $record['money']);
			$row += 1;
		}

		$rtn = $excel->getOutput();
		$excel->end();
		
		return $rtn;
	}
}
