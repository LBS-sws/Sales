<?php
class RankNoticeWidget extends CWidget
{
	public function run() {
		$content = '';
		$level = Yii::app()->user->ranklevel();
		
		if (!empty($level) && !$this->hasRead()) {
			$content .= $this->renderContent();
			$this->renderScript();
			$this->setRead();
		}
		echo $content;
	}

	protected function renderContent() {
//		$image = CHtml::image(Yii::app()->baseUrl."/images/rank/$level.png",'image',array('width'=>140,'height'=>160));
		
		$out = <<<EOF
<div class="modal fade" id="modal-ranknotice">
	<div class="modal-dialog modal-dialog-centered modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h3 class="modal-title">測試</h3>
			</div>
			<div class="modal-body">
				<h4>測試</h4>
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
