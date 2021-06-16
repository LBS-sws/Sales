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

        //明星排行榜数据
        //获取前5位排行榜
        $time= date('Y-m-d', strtotime(date('Y-m-01') ));
        $ph_sql = "select d.name as city, a.username,a.now_score,c.name,a.id
				from sal_rank  a
				left outer join  hr$suffix.hr_binding b on a.username=b.user_id
				left outer join  hr$suffix.hr_employee c on b.employee_id=c.id
				left outer join  security$suffix.sec_city d on d.code=c.city
				where
				a.month >= '$time'
                order by a.now_score desc limit 5
			";
        $ph_records = Yii::app()->db->createCommand($ph_sql)->queryAll();
        foreach ($ph_records as &$ph_record) {
            $sql = "select * from sal_level where start_fraction <='" . $ph_record['now_score'] . "' and end_fraction >'" . $ph_record['now_score'] . "'";
            $rank_name = Yii::app()->db->createCommand($sql)->queryRow();
            $ph_record['level'] = $rank_name['level'];
        }

        $one_level = $ph_records[0]['level'];
        $two_level = $ph_records[1]['level'];
        $three_level = $ph_records[2]['level'];
        $four_level = $ph_records[3]['level'];
        $five_level = $ph_records[4]['level'];

        $one_city = $ph_records[0]['city'];
        $two_city = $ph_records[1]['city'];
        $three_city = $ph_records[2]['city'];
        $four_city = $ph_records[3]['city'];
        $five_city = $ph_records[4]['city'];

        $one_name = $ph_records[0]['name'];
        $two_name = $ph_records[1]['name'];
        $three_name = $ph_records[2]['name'];
        $four_name = $ph_records[3]['name'];
        $five_name = $ph_records[4]['name'];

        $url_t = "https://".$_SERVER['SERVER_NAME']."/sa-uat";
        $back_img = $url_t."/images/rank/ph_b.jpg";
        $one_img = $url_t."/images/rank/".$one_level.".png";
        $two_img=$url_t."/images/rank/".$two_level.".png";
        $three_img= $url_t."/images/rank/".$three_level.".png";
        $one_img_hg = $url_t."/images/rank/hg_01.png";
        $two_img_hg = $url_t."/images/rank/hg_02.png";
        $three_img_hg = $url_t."/images/rank/hg_03.png";
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
<style>
#divtest{
    height:750px;
    width: 600px;
    background-image: url({$back_img});
    background-size: 100% 100%;/*按比例缩放*/
    background-repeat: no-repeat;/*还有repeat-x,y等*/
    font-size: 18px;
    font-weight: 600;
}
#one{
    position: absolute;
    top: 35%;
    left: 40%;
}
#one .level_img{
    width: 120px;
}
#two{
    position: absolute;
    top: 43%;
    left: 10%;
}
#two .level_img{
    width: 100px;
}
.city span{
    padding-right: 10px;
}
.level{
    margin: 5px 10px 0px 0px;
    text-align: center;
}

#three{
    position: absolute;
    top: 43%;
    right: 8%;
}
#three .level_img{
    width: 100px;
}
#four{
    position: absolute;
    top: 77%;
    left: 40%;
}
#five{
    position: absolute;
    top: 87%;
    left: 40%;
}
.hg_01{
    position: absolute;
    width: 80px;
    top: -33px;
    left: 20px;
}
.hg_02{
    position: absolute;
    width: 66px;
    top: -30px;
    left: 17px;
}
.hg_03{
    position: absolute;
    width: 67px;
    top: -30px;
    left: 17px;
}
</style>
<div class="content" style="margin-top: 10px">
			<div class="modal-body" id="divtest">
                <div class="phb">
                    <div id="two" >
                        <img class="hg_02" src="{$two_img_hg}">  
                        <img class="level_img" src="{$two_img}">
                        <div class="city"><span style="color: #8F0808;">$two_city</span><span>$two_name</span></div>
                        <div class="level">$two_level</div>
                    </div>
                    <div id="one" >
                        <img class="hg_01" src="{$one_img_hg}">
                        <img class="level_img" src="{$one_img}">
                        <div class="city"><span style="color: #8F0808;padding-left: 10px;">$one_city</span><span>$one_name</span></div>
                        <div class="level">$one_level</div>
                    </div>
                    <div id="three" >
                        <img class="hg_03" src="{$three_img_hg}">
                        <img class="level_img" src="{$three_img}">
                        <div class="city"><span style="color: #8F0808;">$three_city</span><span>$three_name</span></div>
                        <div class="level">$three_level</div>
                    </div>

                    <!--   4.5-->
                    <div id="four" >
                        <div class="city" style="color: #dab582;"><span>$four_city</span><span>$four_name</span><span>$four_level</span></div>
                    </div>
                    <div id="five" >
                        <div class="city" style="color: #dab582;"><span>$five_city</span><span>$five_name</span><span>$five_level</span></div>
                    </div>
                </div>

		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
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