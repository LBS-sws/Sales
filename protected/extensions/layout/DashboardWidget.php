<?php
class DashboardWidget extends CWidget
{
	public $config;
	
	public function run() {
		$items = require($this->config);
		$param = json_encode($items);

		$content = '';
		if (count($items) > 0) {
			$content .= "<section class='content'>\n";
			$content .= "<div class='row'></div>\n";

			$lastrowno = -99;
			$panel = '';
			foreach($items as $item) {
				if (!isset($item['access']) || empty($item['access']) || Yii::app()->user->validFunction($item['access'])) {
					if ($lastrowno != $item['row']) {
						if (!empty($panel)) $panel .= "</div>\n";
						$panel .= "<div class='row'>\n";
						$lastrowno = $item['row'];
					}
					$filename = '//dashboard/'.$item['view'];
					$result = $this->render($filename,null,true);
					$width = $item['width'];
					$panel .= "<div class='$width'>\n";
					$panel .= $result;
					$panel .= "</div>\n";
				}
			}
			if (!empty($panel)) $panel .= "</div>\n";
			
			$content .= $panel.'</section>';
		}
		echo $content;
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
