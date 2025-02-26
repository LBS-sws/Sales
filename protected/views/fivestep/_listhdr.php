<tr>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city_name'),'#',$this->createOrderLink('fivestep-list','city_name'))
			;
		?>
	</th>
<?php endif ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('rec_dt').$this->drawOrderArrow('rec_dt'),'#',$this->createOrderLink('fivestep-list','rec_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('staff_code').$this->drawOrderArrow('staff_code'),'#',$this->createOrderLink('fivestep-list','staff_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('staff_name').$this->drawOrderArrow('staff_name'),'#',$this->createOrderLink('fivestep-list','staff_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('step').$this->drawOrderArrow('step'),'#',$this->createOrderLink('fivestep-list','step'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('sup_score').$this->drawOrderArrow('sup_score'),'#',$this->createOrderLink('fivestep-list','sup_score'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('mgr_score').$this->drawOrderArrow('mgr_score'),'#',$this->createOrderLink('fivestep-list','mgr_score'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('dir_score').$this->drawOrderArrow('dir_score'),'#',$this->createOrderLink('fivestep-list','dir_score'))
			;
		?>
	</th>
	<th>
		&nbsp;
	</th>
</tr>
