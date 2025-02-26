<?php
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('id'=>'btnDtlClose','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'dtlviewdialog',
					'header'=>Yii::t('sales','Media'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>
<div class="box box-info" style="max-height: 350px; overflow-y: auto;">
	<video width='300' controls id='video_here'>
		Your browser does not support HTML5 video.
	</video>
	<div id="mediafile"></div>
</div>
<?php
	$this->endWidget(); 
?>
