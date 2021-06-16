<?php
$suffix = Yii::app()->params['envSuffix'];
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
    $ph_records['level'] = $rank_name['level'];
}
var_dump($ph_records);die();
?>
<script type="application/javascript">
    if(/Android|iPhone/i.test(navigator.userAgent)) {
        document.write('<link href="../css/rannotice_phone.css" rel="stylesheet" type="text/css"/>');
    }else{
        document.write('<link href="../css/rannotice_pc.css" rel="stylesheet" type="text/css"/>');
    }
</script>
<div class="content">
<!--	<div class="modal-dialog modal-dialog-centered modal-md">-->
<!--		<div class="modal-content">-->
<!--			<div class="modal-header">-->
<!--				<button type="button" class="close" data-dismiss="modal" aria-label="Close">-->
<!--					<span aria-hidden="true">&times;</span>-->
<!--				</button>-->
<!--				<h3 class="modal-title">段位明星榜</h3>-->
<!--			</div>-->
			<div class="modal-body" id="divtest">
                <div class="phb">
                    <div id="one" >
                        <img class="hg_01" src="../images/rank/hg_01.png">
                        <img class="level_img" src="../images/rank/珍爱1段.png">
                        <div class="city"><span style="color: #8F0808;padding-left: 10px;">成都</span><span>高效接</span></div>
                        <div class="level">珍爱1段</div>
                    </div>
                    <div id="two" >
                        <img class="hg_02" src="../images/rank/hg_02.png">
                        <img class="level_img" src="../images/rank/白银2段.png">
                        <div class="city"><span style="color: #8F0808;">成都</span><span>高效接</span></div>
                        <div class="level">白银2段</div>
                    </div>
                    <div id="three" >
                        <img class="hg_03" src="../images/rank/hg_03.png">
                        <img class="level_img" src="../images/rank/白银1段.png">
                        <div class="city"><span style="color: #8F0808;">成都</span><span>高效接</span></div>
                        <div class="level">白银1段</div>
                    </div>

                    <!--   4.5-->
                    <div id="four" >
                        <div class="city" style="color: #dab582;"><span>成都</span><span>高效接</span><span>白银1段</span></div>
                    </div>
                    <div id="five" >
                        <div class="city" style="color: #dab582;"><span>成都</span><span>高效接</span><span>白银1段</span></div>
                    </div>
                </div>


<!--			</div>-->
<!--		</div>-->
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>