<?php
$this->beginWidget('bootstrap.widgets.TbModal', array(
				'id'=>'helpdialog',
				'show'=>false,
			));
?>

	<div class="form-group">
		<div class="col-sm-11">
<?php
			echo CHtml::image(Yii::app()->request->baseUrl.'/images/help01.png','image',array('width'=>550,'height'=>400,'class'=>'responsive-image'));
?>
		</div>
	</div>
<?php
$this->endWidget(); 
?>

