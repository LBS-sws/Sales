<?php
	$idNum = isset($idNum)?$idNum:"";
	$header = isset($header)?$header:Yii::t('dialog','Back Record');
	$content = isset($content)?$content:"<p>".Yii::t('dialog','Are you sure to back?')."</p>";
	$this->widget('bootstrap.widgets.TbModal', array(
					'id'=>'confirmDialog'.$idNum,
					'header'=>$header,
					'content'=>$content,
					'footer'=>array(
                        TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal')),
						TbHtml::button(Yii::t('dialog','OK'), array('submit'=>$submit,'id'=>'okBtnConfirmDialog'.$idNum,'data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
					),
					'show'=>false,
				));
?>