<?php
class RptRenewalReminder extends CReport {
	protected $result;

	public function genReport() {
		$serviceBool = $this->retrieveData();
		$serviceKABool = $this->retrieveKAData();
		if ($serviceBool||$serviceKABool) {
			$output = $this->printReportForRows($this->result);
			$this->submitEmailForCity($output);
			$this->submitEmailForSale();
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
					a.*,CONCAT('客户服务') as table_class, d.description as nature, c.description as customer_type
				from 
					(select x1.*, x2.contract_no from swoper$suffix.swo_service x1 left outer join swoper$suffix.swo_service_contract_no x2 
					on x1.id=x2.service_id) a
					left outer join 
					(select y1.*, y2.contract_no from swoper$suffix.swo_service y1 left outer join swoper$suffix.swo_service_contract_no y2 
					on y1.id=y2.service_id) b 
						on (a.company_id=b.company_id or a.company_name=b.company_name) and 
						(a.product_id=b.product_id or a.service=b.service or 
						a.product_id=b.b4_product_id or a.service=b.b4_service or
						a.contract_no=b.contract_no) and
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
        $result = Yii::app()->db->createCommand($sql)->queryAll();
        if(empty($result)){
            $this->result = array();
        	return false;
		}else{
            $this->result=!empty($this->result)?$this->result:array();
            $this->result = array_merge($this->result,$result);
            return true;
		}
	}

	public function retrieveKAData() {
		$target_dt = $this->criteria['TARGET_DT'].' 00:00:00';
		$city = $this->criteria['CITY'];
		$days = $this->criteria['DURATION'];

		$suffix = Yii::app()->params['envSuffix'];

		$sql = "select
					a.*,CONCAT('KA客户服务') as table_class, d.description as nature, c.description as customer_type
				from 
					(select x1.*, x2.contract_no from swoper$suffix.swo_service_ka x1 left outer join swoper$suffix.swo_service_ka_no x2 
					on x1.id=x2.service_id) a
					left outer join 
					(select y1.*, y2.contract_no from swoper$suffix.swo_service_ka y1 left outer join swoper$suffix.swo_service_ka_no y2 
					on y1.id=y2.service_id) b 
						on (a.company_id=b.company_id or a.company_name=b.company_name) and 
						(a.product_id=b.product_id or a.service=b.service or 
						a.product_id=b.b4_product_id or a.service=b.b4_service or
						a.contract_no=b.contract_no) and
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
		$result = Yii::app()->db->createCommand($sql)->queryAll();
		if(empty($result)){
			return false;
		}else{
            $this->result=!empty($this->result)?$this->result:array();
            $this->result = array_merge($this->result,$result);
            return true;
		}
	}

    //发送邮件给客户服务的销售
	public function submitEmailForSale() {
        $days = $this->criteria['DURATION'];
        $subject = Yii::t('report','Renewal Reminder Report').' ('.Yii::t('report','Days Before Expiry').':'.$days.' '.Yii::t('report','days').')';

        $staffRows=array();
        if(!empty($this->result)){
        	foreach ($this->result as $row){
        		if(!empty($row["salesman_id"])){
                    $salesman_id = "".$row["salesman_id"];
                    if(!key_exists($salesman_id,$staffRows)){
                    	$email = $this->getEmailForStaff($salesman_id);
                    	$staffRows[$salesman_id]=array("id"=>$salesman_id,"email"=>$email,"name"=>$row["salesman"],"list"=>array());
					}
                    $staffRows[$salesman_id]["list"][]=$row;
				}
			}
		}
		if(!empty($staffRows)){
        	foreach ($staffRows as $staffRow){
        		if(!empty($staffRow["email"])){
                    $to = array($staffRow["email"]);
                    $msg = $this->printReportForRows($staffRow["list"]);
                    $this->submitEmail($subject,$to,$msg);
				}
			}
		}
	}

    //发送邮件
	protected function submitEmail($subject,$to,$msg) {
		$param = array(
				'from_addr'=>Yii::app()->params['systemEmail'],
				'to_addr'=>json_encode($to),
				'cc_addr'=>json_encode(array()),
				'subject'=>$subject,
				'description'=>$subject,
				'message'=>$msg,
				'test'=>false,
			);
		$connection = Yii::app()->db;
		$this->sendEmail($connection, $param);
	}

	//发送邮件给地区负责人及权限人员
	public function submitEmailForCity($msg) {
		$city = $this->criteria['CITY'];
		$days = $this->criteria['DURATION'];

		$director = $this->getArrayDirector($city);
		$mgr = City::model()->findByPk($city)->incharge;
		$staff = $this->getArrayStaff($city, array_merge((array)$mgr, $director));

		if ($days <= 10) {
			$recipient = array_merge($director, (array)$mgr);
		} elseif ($days <= 30) {
			$recipient = array_merge((array)$mgr,  $staff);
		} else {
			$recipient = $staff;
		}

		$to = General::getEmailByUserIdArray($recipient);
		$to = General::dedupToEmailList($to);

		$subject = Yii::t('report','Renewal Reminder Report').' ('.Yii::t('report','Days Before Expiry').':'.$days.' '.Yii::t('report','days').') - '.General::getCityName($city);

		$this->submitEmail($subject,$to,$msg);
	}
	
	protected function getEmailForStaff($staff_id) {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("f.email")
			->from("hr{$suffix}.hr_binding a")
			->leftJoin("security{$suffix}.sec_user f","a.user_id = f.username")
			->where("a.employee_id=:id",array(":id"=>$staff_id))
			->queryRow();
        return $row?$row["email"]:"";
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

	public function printReportForRows($rows) {
		$output = "<table border=1>";
		$output .= "<tr><th>".Yii::t('service','Expiry Date')
				."</th><th>"."菜单名称"
				."</th><th>".Yii::t('service','Customer')
				."</th><th>".Yii::t('customer','Nature')
				."</th><th>".Yii::t('service','Service')
				."</th><th>".Yii::t('service','Monthly')
				."</th><th>".Yii::t('service','Yearly')
				."</th><th>"."装机金额"
				."</th><th>"."业务员"
				."</th><th>".Yii::t('service','New Date')
				."</th><th>".Yii::t('service','Sign Date')
				."</th><th>".Yii::t('service','Contract Period')
				."</th><th>".Yii::t('service','Contact')
				."</th></tr>\n";
		foreach ($rows as $row) {
			$output .= "<tr><td>".General::toDate($row['ctrt_end_dt'])
					."</td><td>".$row['table_class']
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
