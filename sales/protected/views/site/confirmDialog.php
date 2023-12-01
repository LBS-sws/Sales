<?php
	$header = isset($header)?$header:Yii::t('dialog','Back Record');
	$content = isset($content)?$content:"<p>".Yii::t('dialog','Are you sure to back?')."</p>";
	$this->widget('bootstrap.widgets.TbModal', array(
					'id'=>'confirmDialog',
					'header'=>$header,
					'content'=>$content,
					'footer'=>array(
						TbHtml::button(Yii::t('dialog','OK'), array('submit'=>$submit,'data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
						TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
					),
					'show'=>false,
				));
?>