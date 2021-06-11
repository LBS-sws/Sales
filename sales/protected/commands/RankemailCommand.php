<?php
class RankemailCommand extends CConsoleCommand {
	
	public function run($args) {
        $date = empty($args) ? date("Y-m-d") : $args[0];
        $suffix = Yii::app()->params['envSuffix'];
        $firstDay = date("Y-m-01");
        $endDay = date("Y-m-31");
        $month=date("m");
        $sql="select * from sal_rank 
              WHERE  month<='$endDay' and month>='$firstDay'";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($records) > 0) {
            foreach ($records as $record) {
                $sql1 = "SELECT email FROM security$suffix.sec_user WHERE username='".$record['username']."'";
                $rs = Yii::app()->db->createCommand($sql1)->queryAll();
                $sql2 = "SELECT a.name,c.name as cityname FROM hr$suffix.hr_employee a 
                        inner join hr$suffix.hr_binding b on a.id = b.employee_id
                        left outer join security$suffix.sec_city c on c.code=a.city
                        WHERE b.user_id='".$record['username']."'";
                $name = Yii::app()->db->createCommand($sql2)->queryRow();
                $from_addr = "it@lbsgroup.com.hk";
                $to_addr = "[\"" .$rs[0]['email']."\"]";
                $subject = "当前个人段位明细";
                $sql_rank_name="select * from sal_level where start_fraction <='".$record['now_score']."' and end_fraction >='".$record['now_score']."'";
                $rank_name= Yii::app()->db->createCommand($sql_rank_name)->queryRow();
                $saiji=RankForm::numToWord($record['season']);
                // $description = "五部曲提醒-" . $record['name'];
                $pip=Yii::app()->baseUrl."/images/".$rank_name['level'].".png";
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
			<td colspan="6" height="36" style="height:36px;width:663px;" x:num="44275"><span style="font-size:14px;">{$date}</span></td>
		</tr>
		<tr height="56">
			<td height="56" style="height: 56px; text-align: center;" x:str=""><span style="font-size:18px;"><span style="font-family:arial,helvetica,sans-serif;"><strong>地区</strong></span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:18px;"><span style="font-family:arial,helvetica,sans-serif;"><strong>姓名</strong></span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:18px;"><span style="font-family:arial,helvetica,sans-serif;"><strong>赛季</strong></span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:18px;"><span style="font-family:arial,helvetica,sans-serif;"><strong>月份</strong></span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:18px;"><span style="font-family:arial,helvetica,sans-serif;"><strong>当前段位</strong></span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:18px;"><span style="font-family:arial,helvetica,sans-serif;"><strong>当前战斗值&nbsp;</strong></span></span></td>
		</tr>
		<tr height="56">
			<td height="56" style="height: 56px; text-align: center;" x:str=""><span style="font-size:16px;"><span style="font-family:arial,helvetica,sans-serif;">{$name['cityname']}</span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:16px;"><span style="font-family:arial,helvetica,sans-serif;">{$name['name']}</span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:16px;"><span style="font-family:arial,helvetica,sans-serif;">第{$saiji}赛季</span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:16px;"><span style="font-family:arial,helvetica,sans-serif;">{$month}月</span></span></td>
			<td style="text-align: center;" x:str=""><span style="font-size:16px;"><span style="font-family:arial,helvetica,sans-serif;">{$rank_name['level']}</span></span></td>
			<td style="text-align: center;" x:num="2500"><span style="font-size:16px;"><span style="font-family:arial,helvetica,sans-serif;">{$record['now_score']}</span></span></td>
		</tr>
	</tbody>
</table>
EOF;
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

            }
        }
	}


}
?>