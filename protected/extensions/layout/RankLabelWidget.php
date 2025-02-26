<?php
class RankLabelWidget extends CWidget {
	public function run() {
		$content = '';
		
		$level = Yii::app()->user->ranklevel();
		if (!empty($level)) {
//			$img = CHtml::image(Yii::app()->baseUrl."/images/rank/$level.png",'image',array('width'=>167*0.11,'height'=>214*0.11,'class'=>'user-image'));
			$img = CHtml::image(Yii::app()->baseUrl."/images/rank/$level.png",'image',
				array('style'=>'width:20px; height:25px;margin-top:-8px;margin-right:10px'));
			$content = <<<EOF
			<li class="dropdown user user-menu">
<a href="#" class="dropdown-toggle">
				$img
				<span class="hidden-xs">$level</span>
</a>
			</li>
EOF;
		}
		
		echo $content;
	}
}
