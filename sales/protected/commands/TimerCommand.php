<?php
class TimerCommand extends CConsoleCommand {
	
	public function run() {
		$obj = new FivestepForm();
		$typelist = $obj->getFiveTypeList();
		$steplist = $obj->getStepList();

        $suffix = Yii::app()->params['envSuffix'];
        $firstDay = date("Y/m/d");
        $firstDay = date("Y/m/d", strtotime("$firstDay - 30 day"));
        $secondDay = date("Y/m/d", strtotime("$firstDay - 60 day"));
        $sql="select * from hr$suffix.hr_employee WHERE  position in (SELECT id FROM hr$suffix.hr_dept where dept_class='sales') AND staff_status = 0";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                $record['entry_time'] = date_format(date_create($record['entry_time']), "Y/m/d");
                $sql1="select a.*, b.name as city_name, f.name as staff_name, f.code as staff_code, h.name as post_name,
				d.field_value as mgr_score, e.field_value as dir_score, g.field_value as sup_score
				from sales$suffix.sal_fivestep a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sales$suffix.sal_fivestep_info d on a.id=d.five_id and d.field_id='mgr_score'
				left outer join sales$suffix.sal_fivestep_info e on a.id=e.five_id and e.field_id='dir_score'
				left outer join sales$suffix.sal_fivestep_info g on a.id=g.five_id and g.field_id='sup_score'
				left outer join hr$suffix.hr_dept h on f.position=h.id
				where f.name= '".$record['name']."'";
                $arr = Yii::app()->db->createCommand($sql1)->queryAll();
                if($record['entry_time'] == "$firstDay"){
                    $sql = "select approver_type,username from account$suffix.acc_approver where city='".$record['city']."' and approver_type='regionMgr'";
                    $rows = Yii::app()->db->createCommand($sql)->queryAll();
                    $zjl = $rows[0]['username'];
                    $sql1 = "SELECT email FROM security$suffix.sec_user WHERE username='$zjl'";
                    $rs = Yii::app()->db->createCommand($sql1)->queryAll();

                    $from_addr = "it@lbsgroup.com.hk";
                    $to_addr = "[\"" .$rs[0]['email']."\"]";
                    $subject = "五部曲提醒-" . $record['name'];
                    $description = "五部曲提醒-" . $record['name'];
                    $message = "姓名：" . $record['name'] . ",入职日期为：" . $record['entry_time'] . ",已经到上传五部曲的时间了<br>";
					$message .= Yii::t('sales','Position')."：".$record['post_name']."<br>";
					$message .= Yii::t('sales','5 Steps')."：".$steplist[$record['step']]."<br>";
					$message .= Yii::t('misc','Five Type')."：".$typelist[$record['five_type']]."<br>";
					$lcu = "admin";
                    $aaa = Yii::app()->db->createCommand()->insert("swoper$suffix.swo_email_queue", array(
                        'request_dt' => date('Y-m-d H:i:s'),
                        'from_addr' => $from_addr,
                        'to_addr' => $to_addr,
                        'subject' => $subject,//郵件主題
                        'description' => $description,//郵件副題
                        'message' => $message,//郵件內容（html）
                        'status' => "P",
                        'lcu' => $lcu,
                        'lcd' => date('Y-m-d H:i:s'),
                    ));
                }elseif (empty($arr)&&$record['entry_time'] == "$secondDay"){
                    $sql = "select approver_type,username from account$suffix.acc_approver where city='".$record['city']."' and approver_type='regionMgr'";
                    $rows = Yii::app()->db->createCommand($sql)->queryAll();
                    $zjl = $rows[0]['username'];
                    $sql1 = "SELECT email FROM security$suffix.sec_user WHERE username='$zjl'";
                    $rs = Yii::app()->db->createCommand($sql1)->queryAll();

                    $from_addr = "it@lbsgroup.com.hk";
                    $to_addr = "[\"" .$rs[0]['email']."\"]";
                    $subject = "五部曲提醒-" . $record['name'];
                    $description = "五部曲提醒-" . $record['name'];
                    $message = "姓名：" . $record['name'] . ",入职日期为：" . $record['entry_time'] . ",賬戶五部曲還是空白请提醒上传";
					$message .= Yii::t('sales','Position')."：".$record['post_name']."<br>";
					$message .= Yii::t('sales','5 Steps')."：".$steplist[$record['step']]."<br>";
					$message .= Yii::t('misc','Five Type')."：".$typelist[$record['five_type']]."<br>";
                    $lcu = "admin";
                    $aaa = Yii::app()->db->createCommand()->insert("swoper$suffix.swo_email_queue", array(
                        'request_dt' => date('Y-m-d H:i:s'),
                        'from_addr' => $from_addr,
                        'to_addr' => $to_addr,
                        'subject' => $subject,//郵件主題
                        'description' => $description,//郵件副題
                        'message' => $message,//郵件內容（html）
                        'status' => "P",
                        'lcu' => $lcu,
                        'lcd' => date('Y-m-d H:i:s'),
                    ));
                }

            }
        }

        //终止客户邮件提醒
        $this->shiftEmailHint();
	}

    //终止客户邮件提醒
	private function shiftEmailHint(){
	    $shiftList = array();
	    $this->setShiftList($shiftList);
	    $this->sendForShiftList($shiftList);
    }

    //发送终止客户邮件
    private function sendForShiftList($shiftList){
        $systemId = Yii::app()->params['systemId'];
	    if(!empty($shiftList)){
            $subject = "超6个月终止客户回访提醒";
            $messageEpr="<p>以下客户停单已超6个月，请及时电联或上门回访</p>";
            $messageEpr.="<p>回访内容请在LBS系统-销售系统-终止客户-终止客户回访中完成</p>";
            $messageEpr.="<p>客户服务的基本信息：</p>";
            $tableHead="<table border='1' width='800px'><thead><tr>";
            $tableHead.="<th width='30%'>客户名称</th>";
            $tableHead.="<th width='13%'>停单日期</th>";
            $tableHead.="<th width='13%'>客户类别</th>";
            $tableHead.="<th width='13%'>性质</th>";
            $tableHead.="<th width='30%'>业务员</th>";
            $tableHead.="</tr></thead><tbody>";
            $tableEnd="</tbody></table>";
            $email = new Email($subject,"",$subject);
	        foreach ($shiftList as $city=>$staffRows){
	            $cityMessage = "";
	            foreach ($staffRows as $staffId => $rows){
	                $staffMessage="";
	                foreach ($rows as $row){
	                    $message="<tr>";
	                    $message.="<td>{$row['company_name']}</td>";
	                    $message.="<td>{$row['status_dt']}</td>";
	                    $message.="<td>{$row['cust_type']}</td>";
	                    $message.="<td>{$row['nature_name']}</td>";
	                    $message.="<td>{$row['salesman']}</td>";
	                    $message.="</tr>";
	                    $staffMessage.=$message;//添加到员工邮件
                        $cityMessage.=$message;//添加到城市邮件
                    }
                    $email->resetToAddr();
                    if(!empty($staffMessage)){
                        $message = $messageEpr.$tableHead.$staffMessage.$tableEnd;
                        $email->setMessage($message);
                        $email->addEmailToStaffId($staffId);
                        $email->sent("销售系统",$systemId);
                    }
                }
                $email->resetToAddr();
                if(!empty($cityMessage)){
                    $message = $messageEpr.$tableHead.$cityMessage.$tableEnd;
                    $email->setMessage($message);
                    $email->addEmailToCity($city);
                    $email->sent("销售系统",$systemId);
                }
            }
        }

    }

    //获取终止客户邮件
    private function setShiftList(&$shiftList){
        $suffix = Yii::app()->params['envSuffix'];
        $expr_sql = StopOtherList::getExprSql();
        $rows = Yii::app()->db->createCommand()
            ->select("a.id as service_id,a.city,a.status_dt,a.cust_type,a.nature_type,a.company_name,a.salesman,a.salesman_id,b.id,b.staff_id")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("sal_stop_back b","a.id=b.service_id ")
            ->where("a.status = 'T' and a.salesman_id !=0 and b.back_date is null {$expr_sql}")
            ->order("a.city asc,a.salesman_id asc")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $row["status_dt"]=General::toDate($row["status_dt"]);
                $row["cust_type"]=StopOtherForm::getCustTypeStr($row["cust_type"]);
                $row["nature_name"]=StopOtherForm::getNatureTypeStr($row["nature_type"]);
                $staff_id = empty($row["staff_id"])?$row["salesman_id"]:$row["staff_id"];//判断是否转移数据
                if(!key_exists($row["city"],$shiftList)){
                    $shiftList[$row["city"]] = array();
                }
                if(!key_exists($staff_id,$shiftList[$row["city"]])){
                    $shiftList[$row["city"]][$staff_id] = array();
                }
                $shiftList[$row["city"]][$staff_id][]=$row;
            }
        }
    }
}
?>