<?php
class RptRenewalReminder extends CReport {
	protected $result;

	public function genReport() {
		if ($this->retrieveData()) {
			$output = $this->printReport();
			$this->submitEmail($output);
		} else {
			$output = '';
		}
		return $output;
	}
	
	public function retrieveData() {
		$target_dt = $this->criteria['TARGET_DT'].' 00:00:00';
		$city = $this->criteria['CITY'];
		$days = $this->criteria['DURATION'];
		
		$suffix = Yii::app()->params['envSuffix'];
		
		$sql = "select
					a.*, d.description as nature, c.description as customer_type
				from 
					swoper$suffix.swo_service a
					left outer join swoper$suffix.swo_service b 
						on (a.company_id=b.company_id or a.company_name=b.company_name) and 
						(a.product_id=b.product_id or a.service=b.service or 
						a.product_id=b.b4_product_id or a.service=b.b4_service) and
						(a.status_dt < b.status_dt or 
						(a.status_dt = b.status_dt and a.id < b.id))
					left outer join swoper$suffix.swo_customer_type c
						on a.cust_type=c.id
					left outer join swoper$suffix.swo_nature d 
						on a.nature_type=d.id 
				where 
					b.id is null and 
					a.paid_type <> '1' and
					a.ctrt_end_dt is not null and 
					a.city='$city' and 
					datediff(a.ctrt_end_dt,'$target_dt') = $days and
					a.status not in ('S', 'T')
				order by a.ctrt_end_dt
		";
		$this->result = Yii::app()->db->createCommand($sql)->queryAll();
		return !empty($this->result);
	}

	public function submitEmail($msg) {
		$city = $this->criteria['CITY'];
		$date = $this->criteria['TARGET_DT'];
		$days = $this->criteria['DURATION'];
		$recipient = array();
		
		$director = $this->getArrayDirector($city);
		$mgr = City::model()->findByPk($city)->incharge;
		$staff = $this->getArrayStaff($city, array_merge((array)$mgr, $director));
		$sales = $this->getArraySales();
		
		if ($days <= 10) {
			$recipient = array_merge($director, (array)$mgr);
		} elseif ($days <= 30) {
			$recipient = array_merge((array)$mgr, $sales, $staff);
		} else {
			$recipient = array_merge($sales, $staff);
		}
		
		$to = General::getEmailByUserIdArray($recipient);
		$to = General::dedupToEmailList($to);
// Remove Joe Yiu from to address
//		$tmp = $to;
//		$to = array();
//		foreach($tmp as $itm) {
//			if ($itm != 'joeyiu@lbsgroup.com.cn') $to[] = $itm;
//		}

		$cc = array();
		
		$subject = Yii::t('report','Renewal Reminder Report').' ('.Yii::t('report','Days Before Expiry').':'.$days.' '.Yii::t('report','days').') - '.General::getCityName($city);
		$desc = Yii::t('report','Renewal Reminder Report').' ('.Yii::t('report','Days Before Expiry').':'.$days.' '.Yii::t('report','days').') - '.General::getCityName($city);
		
		$param = array(
				'from_addr'=>Yii::app()->params['systemEmail'],
				'to_addr'=>json_encode($to),
				'cc_addr'=>json_encode($cc),
				'subject'=>$subject,
				'description'=>$desc,
				'message'=>$msg,
				'test'=>false,
			);
		$connection = Yii::app()->db;
		$this->sendEmail($connection, $param);
	}
	
	protected function getArraySales() {
		$rtn = array();
		$suffix = Yii::app()->params['envSuffix'];
		foreach ($this->result as $record) {
			$salesman = $record['salesman'];
			if (!empty($salesman)) {
				$sql = "select b.user_id from hr$suffix.hr_employee a, hr$suffix.hr_binding b
						where a.id = b.employee_id and
							(instr('$salesman',a.code) > 0 or instr('$salesman',a.name) > 0)
						limit 1
				";
				$row = Yii::app()->db->createCommand($sql)->queryRow();
				if ($row!==false) $rtn[] = $row['user_id'];
			}
		}
		return $rtn;
	}

	protected function getArrayStaff($city, $exclude=array()) {
		$rtn = array();
		$staff = $this->getUserWithRights($city, 'A02', true);
		foreach ($staff as $item) {
			if (!in_array($item, $exclude)) $rtn[] = $item;
		}
		return $rtn;
	}
	
	protected function getArrayDirector($city) {
		$rtn = array();
		$incharge = City::model()->getAncestorInChargeList($city);
		$head = City::model()->findByPk('CN')->incharge;
		$flag = true;
		foreach ($incharge as $item) {
			if ($item==$head && $flag)
				$flag = false;
			else 
				$rtn[] = $item;
		}
		return $rtn;
	}
	
	protected function getUserWithRights($city, $right, $rw=false) {
		$rtn = array();
		
		$citylist = City::model()->getAncestorList($city);
		$citylist = ($citylist=='' ? $citylist : $citylist.',')."'$city'";
		
		$suffix = Yii::app()->params['envSuffix'];
		$sql = $rw ?
			"select a.username from security$suffix.sec_user_access a, security$suffix.sec_user b
				where a.a_read_write like '%$right%'
				and a.username=b.username and b.city in ($citylist) and b.status='A'
				and a.system_id='drs'
			"
			:
			"select a.username from security$suffix.sec_user_access a, security$suffix.sec_user b
				where (a.a_read_only like '%$right%' or a.a_read_write like '%$right%'
				or a.a_control like '%$right%')
				and a.username=b.username and b.city in ($citylist) and b.status='A'
				and a.system_id='drs'
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$rtn[] = $row['username'];
			}
		}
		return $rtn;
	}
	
	public function printReport() {
		$output = "<table border=1>";
		$output .= "<tr><th>".Yii::t('service','Expiry Date')
				."</th><th>".Yii::t('service','Customer')
				."</th><th>".Yii::t('customer','Nature')
				."</th><th>".Yii::t('service','Service')
				."</th><th>".Yii::t('service','Monthly')
				."</th><th>".Yii::t('service','Yearly')
				."</th><th>".Yii::t('service','Install Amt')
				."</th><th>".Yii::t('service','Salesman')
				."</th><th>".Yii::t('service','New Date')
				."</th><th>".Yii::t('service','Sign Date')
				."</th><th>".Yii::t('service','Contract Period')
				."</th><th>".Yii::t('service','Contact')
				."</th></tr>\n";
		foreach ($this->result as $row) {
			$output .= "<tr><td>".General::toDate($row['ctrt_end_dt'])
					."</td><td>".$row['company_name']
					."</td><td>".$row['nature']
					."</td><td>".$row['service']
					."</td><td align='right'>".number_format(($row['paid_type']=='1'?$row['amt_paid']:($row['paid_type']=='M'?$row['amt_paid']:round($row['amt_paid']/12,2))),2,'.','')
					."</td><td align='right'>".number_format(($row['paid_type']=='1'?0:($row['paid_type']=='M'?$row['amt_paid']*12:$row['amt_paid'])),2,'.','')
					."</td><td align='right'>".number_format($row['amt_install'],2,'.','')
					."</td><td>".$row['salesman']
					."</td><td>".General::toDate($row['status_dt'])
					."</td><td>".General::toDate($row['sign_dt'])
					."</td><td>".$row['ctrt_period']
					."</td><td>".$row['cont_info']
					."</td></tr>\n";
		}
		$output .= "</table>";
		
		return $output;
	}
}
?>
