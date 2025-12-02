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
				'actions'=>array('notify','salepeople','Salelist','Salelists'),
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
	
	public function actionSalepeople() {
		$suffix = Yii::app()->params['envSuffix'];
		$models = array();
		$time= date('Y-m-d', strtotime(date('Y-m-01') ));
		$sql = "select a.city, a.username, sum(convert(b.field_value, decimal(12,2))) as money
				from sal_visit a force index (idx_visit_02), sal_visit_info b
				where a.id=b.visit_id and b.field_id in ('svc_A7','svc_B6','svc_C7','svc_D6','svc_E7','svc_F4','svc_G3') 
				and a.visit_dt > '$time' and  a.visit_obj like '%10%'
				group by a.city, a.username
			";
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($records as $record) {
			$temp = array();
			$temp['user']=$record['username'];
			$temp['money']=$record['money'];

			$sql = "select employee_name from hr$suffix.hr_binding where user_id='".$record['username']."'";
			$row = Yii::app()->db->createCommand($sql)->queryRow();
			$temp['name']= $row!==false ? $row['employee_name'] : $record['username'];
		
			$sql = "select a.name as city_name, b.name as region_name 
					from security$suffix.sec_city a
					left outer join security$suffix.sec_city b on a.region=b.code
					where a.code='".$record['city']."'
				";
			$row = Yii::app()->db->createCommand($sql)->queryRow();
			$temp['city'] = $row!==false ? $row['city_name'] : $record['city'];
			$temp['quyu'] = $row!==false ? str_replace(array('1','2','3','4','5','6','7','8','9','0'),'',$row['region_name']) : '空';

			$models[] = $temp;
		}
		$last_names = array_column($models,'money');
		array_multisort($last_names,SORT_DESC,$models);
		$models = array_slice($models, 0, 20);

		echo json_encode($models);
	}

    public function actionSalelist() {
		$suffix = Yii::app()->params['envSuffix'];
		$models = array();
		$cities = General::getCityListWithNoDescendant();
		foreach ($cities as $code=>$name) {
			if (strpos('/CS/H-N/HK/TC/TP/ZS1/TY/KS/TN/XM//KH/ZY/MO/RN/MY/',$code)===false) {
				$sql = "select a.name as city_name, b.name as region_name 
						from security$suffix.sec_city a
						left outer join security$suffix.sec_city b on a.region=b.code
						where a.code='$code'
					";
				$row = Yii::app()->db->createCommand($sql)->queryRow();
				$temp = $row!==false ? str_replace(array('1','2','3','4','5','6','7','8','9','0'),'',$row['region_name']) : '空';
				$models[$code] = array('city'=>$name, 'renjun'=>0, 'quyu'=>$temp);
			}
		}

		$time= date('Y-m-d', strtotime(date('Y-m-01') ));
		$sql = "select a.city, a.username, count(a.username) as renjun
				from sal_visit a force index (idx_visit_02), sal_visit_info b
				where a.id=b.visit_id and b.field_id in ('svc_A7','svc_B6','svc_C7','svc_D6','svc_E7','svc_F4','svc_G3') 
				and a.visit_dt > '$time' and  a.visit_obj like '%10%'
				group by a.city, a.username 
			";
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		$lastcity = '';
		$num = 0;
		$cnt = 0;
		foreach ($records as $record) {
			if (strpos('/CS/H-N/HK/TC/TP/ZS1/TY/KS/TN/XM//KH/ZY/MO/RN/MY/',$record['city'])===false) {
				if ($lastcity!=$record['city']) {
					if ($lastcity!='') {
						$models[$record['city']]['renjun'] = round($cnt/($num==0?1:$num), 2);
					}
					$num = 0;
					$cnt = 0;
					$lastcity = $record['city'];
				}
				$num++;
				$cnt += $record['renjun'];
			}
		}
		
		$result = array();
		foreach ($models as $key=>$item) {
			$result[] = $item;
		}
		
        $arraycol = array_column($result,'renjun');
        array_multisort($arraycol,SORT_DESC,$result);
        echo json_encode($result);
    }

    public function actionSalelists() {
		$suffix = Yii::app()->params['envSuffix'];
		$models = array();
		$cities = General::getCityListWithNoDescendant();
		foreach ($cities as $code=>$name) {
			if (strpos('/CS/H-N/HK/TC/TP/ZS1/TY/KS/TN/XM/KH/ZY/MO/RN/MY/',$code)===false) {
				$sql = "select a.name as city_name, b.name as region_name 
						from security$suffix.sec_city a
						left outer join security$suffix.sec_city b on a.region=b.code
						where a.code='$code'
					";
				$row = Yii::app()->db->createCommand($sql)->queryRow();
				$temp = $row!==false ? str_replace(array('1','2','3','4','5','6','7','8','9','0'),'',$row['region_name']) : '空';
				$models[$code] = array('city'=>$name, 'money'=>number_format((float)0, 2, '.', ''), 'quyu'=>$temp);
			}
		}

		$time= date('Y-m-d', strtotime(date('Y-m-01') ));
		$sql = "select a.city, a.username, sum(convert(b.field_value, decimal(12,2))) as money
				from sal_visit a force index (idx_visit_02), sal_visit_info b
				where a.id=b.visit_id and b.field_id in ('svc_A7','svc_B6','svc_C7','svc_D6','svc_E7','svc_F4','svc_G3') 
				and a.visit_dt > '$time' and  a.visit_obj like '%10%'
				group by a.city, a.username 
			";
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		$lastcity = '';
		$num = 0;
		$amt = 0;
		foreach ($records as $record) {
			if (strpos('/CS/H-N/HK/TC/TP/ZS1/TY/KS/TN/XM//KH/ZY/MO/RN/MY/',$record['city'])===false) {
				if ($lastcity!=$record['city']) {
					if ($lastcity!='') {
						$tmp = $amt/($num==0?1:$num);
						$models[$record['city']]['money'] = number_format((float)$tmp, 2, '.', '');
					}
					$num = 0;
					$amt = 0;
					$lastcity = $record['city'];
				}
			}
			$num++;
			$amt += $record['money'];
		}
		
		$result = array();
		foreach ($models as $key=>$item) {
			$result[] = $item;
		}
		
        $arraycol = array_column($result,'money');
        array_multisort($arraycol,SORT_DESC,$result);
        echo json_encode($result);
    }
}

?>