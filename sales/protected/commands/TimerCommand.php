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
	}


}
?>