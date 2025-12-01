<?php

class DashboardController extends Controller
{
	public $interactive = false;

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl - checksession', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('notify','salepeople','showsalepeople','Salelist','Salelists','ranklist','showranklist','renaudlist',),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionNotify($id=-1) {
		$rtn = array();
		if ($id >= 0) {
			$model = new Notification();
			$rtn = $model->getNewMessageById($id);
		}
		echo json_encode($rtn);
	}


	public function actionSalepeople($type = 0) {
		$suffix = Yii::app()->params['envSuffix'];
		$models = array();
        $idCharSql = "SELECT GROUP_CONCAT(CONCAT(\"'svc_\",a.id_char,\"'\")) as idChars FROM sal_service_type_info a LEFT JOIN sal_service_type b ON a.type_id=b.id 
WHERE b.class_id is NOT null AND a.input_type='yearAmount' AND  b.class_id not in (6,7)";
        $idChar = Yii::app()->db->createCommand($idCharSql)->queryRow();
        $idChar =$idChar?$idChar["idChars"]:"''";
		$time= date('Y-m-d', strtotime(date('Y-m-01') ));
		$sql = "select a.city, a.username, sum(convert(b.field_value, decimal(12,2))) as money
				from sal_visit a force index (idx_visit_02), sal_visit_info b
				where a.id=b.visit_id and b.field_id in ({$idChar}) 
				and a.visit_dt >= '$time' and  a.visit_obj like '%10%'
				group by a.city, a.username
			";
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($records as $record) {
			$temp = array();
			$temp['user']=$record['username'];
			$temp['money']=$record['money'];


            $sql = "select a.name from hr$suffix.hr_employee a 
                    LEFT JOIN hr$suffix.hr_binding b ON a.id = b.employee_id
                    where b.user_id='".$record['username']."'";
			$row = Yii::app()->db->createCommand($sql)->queryRow();
			$temp['name']= $row!==false ? $row['name'] : $record['username'];
		
			$sql = "select a.name as city_name, b.name as region_name 
					from security$suffix.sec_city a
					left outer join security$suffix.sec_city b on a.region=b.code
					where a.code='".$record['city']."'
				";
			$row = Yii::app()->db->createCommand($sql)->queryRow();
			$temp['city'] = $row!==false ? $row['city_name'] : $record['city'];
			$temp['quyu'] = $row!==false ? str_replace(array('1','2','3','4','5','6','7','8','9','0'),'',$row['region_name']) : '空';
			$sql_rank="select now_score from sal_rank where month>= '$time' and username='".$record['username']."'";
            $rank = Yii::app()->db->createCommand($sql_rank)->queryRow();
            $sql = "select * from sal_level where start_fraction <='" . $rank['now_score'] . "' and end_fraction >='" . $rank['now_score'] . "'";
            $rank_name = Yii::app()->db->createCommand($sql)->queryRow();
            $temp['level'] = $rank_name['level'];
            $temp['levelImg'] = Yii::app()->baseUrl."/images/".$rank_name['level'].".png";
            $temp['rank'] = $rank['now_score'];

			$models[] = $temp;
		}
		$last_names = array_column($models,'money');
		array_multisort($last_names,SORT_DESC,$models);
		if($type==0){
            $models = array_slice($models, 0, 20);
        }
		echo json_encode($models);
	}


    public function actionRenaudlist($type="money") {
	    $orderList = array("money","amount","number");
	    $type = in_array($type,$orderList)?$type:"money";
        $suffix = Yii::app()->params['envSuffix'];
        $models = array();
        $time= date('Y/m');
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
            ->order("$type desc")
            ->limit(20)
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
            echo json_encode($result);
        }else{
            echo json_encode(array());
        }
    }


    public function actionSalelist() {
        $suffix = Yii::app()->params['envSuffix'];
        $idCharSql = "SELECT GROUP_CONCAT(CONCAT(\"'svc_\",a.id_char,\"'\")) as idChars FROM sal_service_type_info a LEFT JOIN sal_service_type b ON a.type_id=b.id 
WHERE b.class_id is NOT null AND a.input_type='yearAmount'";
        $idChar = Yii::app()->db->createCommand($idCharSql)->queryRow();
        $idChar =$idChar?$idChar["idChars"]:"''";
        $models = array();
        $cities = General::getCityListWithNoDescendant();
        $time= date('Y-m-d', strtotime(date('Y-m-01') ));
        $inCityList = self::inCityList();
        foreach ($cities as $code=>$name) {
            $sum_arr = array();
            if (in_array($code,$inCityList)) {
                $sql = "select a.name as city_name, b.name as region_name 
						from security$suffix.sec_city a
						left outer join security$suffix.sec_city b on a.region=b.code
						where a.code='$code'
					";
                $row = Yii::app()->db->createCommand($sql)->queryRow();
                $temp = $row!==false ? str_replace(array('1','2','3','4','5','6','7','8','9','0'),'',$row['region_name']) : '空';

                //人数
                $sql1="select distinct  username FROM sal_visit  WHERE city='$code' and visit_dt >='".$time."'";
                $people = Yii::app()->db->createCommand($sql1)->queryAll();
                $peoples=count($people);
                if(!empty($people)){
                    //总单数
                    $sql2="select id from sal_visit where city='$code' and  visit_obj like '%10%' and visit_dt >='".$time."'";
                    $sum = Yii::app()->db->createCommand($sql2)->queryAll();
                    foreach ($sum as $id){
                        $sqlid="select count(visit_id) as sum from  sal_visit_info where field_id in ({$idChar}) and field_value>'0' and visit_id='".$id['id']."'";
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
        echo json_encode($result);
    }

    public static function notCityList(){ //已失效，可以由日报表系统的城市自由设置
        $notCityList = General::getKAAndAreaCityList();//KA城市及區域不參與排行榜
        $notCityList = array_keys($notCityList);
	    $list = array(
            "CS","H-N","HK","TC","ZS1","TP","TY","KS","TN",
            "XM","KH","ZY","MO","RN","MY","WL","HN1","QD",
        );//排行榜需要特別排除的城市
	    return array_merge($notCityList,$list);
    }

    public static function inCityList(){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("code")
            ->from("security{$suffix}.sec_city_info")
            ->where("field_id='SARANK' and field_value=1")
            ->group("code")->queryAll();
        $list = array();//排行榜需要的城市
        if($rows){
            foreach ($rows as $row){
                $list[] = $row["code"];
            }
        }
	    return $list;
    }

    public function actionSalelists() {
        $suffix = Yii::app()->params['envSuffix'];
        $models = array();
        $idCharSql = "SELECT GROUP_CONCAT(CONCAT(\"'svc_\",a.id_char,\"'\")) as idChars FROM sal_service_type_info a LEFT JOIN sal_service_type b ON a.type_id=b.id 
WHERE b.class_id is NOT null AND a.input_type='yearAmount' AND  b.class_id not in (6,7)";
        $idChar = Yii::app()->db->createCommand($idCharSql)->queryRow();
        $idChar =$idChar?$idChar["idChars"]:"''";
        $cities = General::getCityListWithNoDescendant();
        $time= date('Y-m-d', strtotime(date('Y-m-01') ));
        $inCityList = self::inCityList();
        foreach ($cities as $code=>$name) {
            if (in_array($code,$inCityList)) {
                $sql = "select a.name as city_name, b.name as region_name 
						from security$suffix.sec_city a
						left outer join security$suffix.sec_city b on a.region=b.code
						where a.code='$code'
					";
                $row = Yii::app()->db->createCommand($sql)->queryRow();
                $temp = $row!==false ? str_replace(array('1','2','3','4','5','6','7','8','9','0'),'',$row['region_name']) : '空';

                //人数
                $sql1="select distinct  username FROM sal_visit  WHERE city='$code' and visit_dt >='".$time."'";
                $people = Yii::app()->db->createCommand($sql1)->queryAll();
                $peoples=count($people);
                //总单数
                $sql2="select id from sal_visit where city='$code' and  visit_obj like '%10%' and visit_dt >='".$time."'";
                $sum = Yii::app()->db->createCommand($sql2)->queryAll();
                $sums=count($sum);
                //人均签单数
                $sale=$sums/($peoples==0?1:$peoples);
                $sale=round($sale,2);

                //总金额
                $money=0;
                foreach ($sum as $b){
                    $sql3="select field_id, field_value from sal_visit_info where field_id in ({$idChar}) and visit_id = '".$b['id']."'";
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
        echo json_encode($result);
    }

	public function actionShowsalepeople() {
		$this->layout = "main_nm";
		$this->render('//dashboard/salepeople',array('popup'=>true));
	}

	public function actionShowranklist() {
		$this->layout = "main_nm";
		$this->render('//dashboard/ranklist',array('popup'=>true));
	}

    public function actionRanklist(){
        $suffix = Yii::app()->params['envSuffix'];
        $models = array();
        $time= date('Y-m-d', strtotime(date('Y-m-01') ));
        $suffix = Yii::app()->params['envSuffix'];
        $models = array();
        $sql = "select a.city, a.username,a.now_score,c.name,a.id
				from sal_rank  a
				left outer join  hr$suffix.hr_binding b on a.username=b.user_id
				left outer join  hr$suffix.hr_employee c on b.employee_id=c.id
				where 
				a.month >= '$time' 
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
                $temp['now_score'] = $record['now_score'];
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
                if(empty($record['now_score'])){
                    $record['now_score']=0;
                }

                $temp['rank'] = $record['now_score'];
                $models[] = $temp;
            }
        }
        $last_names = array_column($models,'now_score');
        array_multisort($last_names,SORT_DESC,$models);
        $models = array_slice($models, 0, 20);
        echo json_encode($models);
    }


}

?>