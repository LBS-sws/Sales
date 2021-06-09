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
//		$image = CHtml::image(Yii::app()->baseUrl."/images/rank/$level.png",'image',array('width'=>140,'height'=>160));
		
		$out = <<<EOF
<style>
    #divtest{
        margin: 15px;
        height:750px;
        background-image: url("../images/dw.jpg");
        background-size: 100% 100%;/*按比例缩放*/
        background-repeat: no-repeat;/*还有repeat-x,y等*/
        font-size: 18px;
        font-weight: 600;
    }
    #one{
        position: absolute;
        top: 43%;
        left: 10%;
    }
    #one img{
        width: 100px;
    }
    .city span{
        padding-right: 10px;
    }
    .level{
        margin: 5px 10px 0px 0px;
        text-align: center;
    }
    #two{
        position: absolute;
        top: 35%;
        left: 40%;
    }
    #two img{
        width: 120px;
    }
    #three{
        position: absolute;
        top: 43%;
        right: 8%;
    }
    #three img{
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
</style>
<div class="modal fade" id="modal-ranknotice">
	<div class="modal-dialog modal-dialog-centered modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h3 class="modal-title">段位榜</h3>
			</div>
			<div class="modal-body" id="divtest">
                <div class="phb">
                    <div id="one" >
                        <img src="../images/rank/$records[1]['level'].png">
                        <div class="city"><span style="color: #8F0808;">$records[1]['city']</span><span>$records[1]['name']</span></div>
                        <div class="level">$records[1]['level']</div>
                    </div>
                    <div id="two" >
                        <img src="../images/rank/$records[0]['level'].png">
                        <div class="city"><span style="color: #8F0808;padding-right: 20px;">$records[0]['city']</span><span>$records[0]['name']</span></div>
                        <div class="level">$records[0]['level']</div>
                    </div>
                    <div id="three" >
                        <img src="../images/rank/$records[2]['level'].png">
                        <div class="city"><span style="color: #8F0808;">$records[2]['city']</span><span>$records[2]['name']</span></div>
                        <div class="level">$records[2]['level']</div>
                    </div>

                    <!--   4.5-->
                    <div id="four" >
                        <div class="city" style="color: #dab582;"><span>$records[3]['city']</span><span>$records[3]['name']</span><span>$records[3]['level']</span></div>
                    </div>
                    <div id="five" >
                        <div class="city" style="color: #dab582;"><span>$records[4]['city']</span><span>$records[4]['name']</span><span>$records[4]['level']</span></div>
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
	$('#modal-ranking').modal('show');
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
