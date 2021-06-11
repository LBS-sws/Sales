<?php
class RankNoticeWidget extends CWidget
{
	public function run() {
		$content = '';
		//获取前5位排行榜
        $time= date('Y-m-d', strtotime(date('Y-m-01') ));
        $suffix = Yii::app()->params['envSuffix'];
        $models = array();
        $sql = "select d.name as city, a.username,a.now_score,c.name,a.id
				from sal_rank  a
				left outer join  hr$suffix.hr_binding b on a.username=b.user_id
				left outer join  hr$suffix.hr_employee c on b.employee_id=c.id
				left outer join  security$suffix.sec_city d on d.code=c.city
				where
				a.month >= '$time'
                order by a.now_score desc limit 5
			";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        foreach ($records as &$record) {
            $sql = "select * from sal_level where start_fraction <='" . $record['now_score'] . "' and end_fraction >'" . $record['now_score'] . "'";
            $rank_name = Yii::app()->db->createCommand($sql)->queryRow();
            $record['level'] = $rank_name['level'];
        }
		if (!empty($records) && !$this->hasRead()) {
			$content .= $this->renderContent($records);
			$this->renderScript();
			$this->setRead();
		}
		echo $content;
	}

	protected function renderContent($records) {
	    $one_level = $records[0]['level'];
        $two_level = $records[1]['level'];
        $three_level = $records[2]['level'];
        $four_level = $records[3]['level'];
        $five_level = $records[4]['level'];

        $one_city = $records[0]['city'];
        $two_city = $records[1]['city'];
        $three_city = $records[2]['city'];
        $four_city = $records[3]['city'];
        $five_city = $records[4]['city'];

        $one_name = $records[0]['name'];
        $two_name = $records[1]['name'];
        $three_name = $records[2]['name'];
        $four_name = $records[3]['name'];
        $five_name = $records[4]['name'];
//		$image = CHtml::image(Yii::app()->baseUrl."/images/rank/$level.png",'image',array('width'=>140,'height'=>160));
		$out = <<<EOF
<script type="application/javascript">
    if(/Android|iPhone/i.test(navigator.userAgent)) {
        document.write('<link href="../sa-uat/css/rannotice_phone.css" rel="stylesheet" type="text/css"/>');
    }else{
        document.write('<link href="../sa-uat/css/rannotice_pc.css" rel="stylesheet" type="text/css"/>');
    }
</script>
<div class="modal fade" id="modal-ranknotice">
	<div class="modal-dialog modal-dialog-centered modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h3 class="modal-title">段位明星榜</h3>
			</div>
			<div class="modal-body" id="divtest">
                <div class="phb">
                    <div id="two" >
                        <img class="hg_02" src="../sa-uat/images/rank/hg_02.png">  
                        <img class="level_img" src="../sa-uat/images/rank/$two_level.png">
                        <div class="city"><span style="color: #8F0808;">$two_city</span><span>$two_name</span></div>
                        <div class="level">$two_level</div>
                    </div>
                    <div id="one" >
                        <img class="hg_01" src="../sa-uat/images/rank/hg_01.png">
                        <img class="level_img" src="../sa-uat/images/rank/$one_level.png">
                        <div class="city"><span style="color: #8F0808;padding-left: 10px;">$one_city</span><span>$one_name</span></div>
                        <div class="level">$one_level</div>
                    </div>
                    <div id="three" >
                        <img class="hg_03" src="../sa-uat/images/rank/hg_03.png">
                        <img class="level_img" src="../sa-uat/images/rank/$three_level.png">
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


			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->
EOF;
		return $out;
	}
	
	protected function renderScript() {
		$js = <<<EOF
$('#modal-ranknotice').modal('show');
$('#modal-ranknotice').on("hidden.bs.modal", function() {
    $( '#modal-ranking' ).off().on( 'hidden', 'hidden.bs.modal');
});
EOF;
		Yii::app()->clientScript->registerScript('ranknotice',$js,CClientScript::POS_READY);
	}

	protected function hasRead() {
		$session = Yii::app()->session;
		return (isset($session['ranknotice']) && !empty($session['ranknotice'])) ?  $session['ranknotice'] : false;
	}
	
	protected function setRead() {
		$session = Yii::app()->session;
		$session['ranknotice'] = true;
	}

	public function render($view,$data=null,$return=false) {
		$ctrl = $this->getController();
		if(($viewFile=$ctrl->getViewFile($view))!==false)
			return $this->renderFile($viewFile,$data,$return);
		else
			throw new CException(Yii::t('yii','{widget} cannot find the view "{view}".',
				array('{widget}'=>get_class($this), '{view}'=>$view)));
	}
}
