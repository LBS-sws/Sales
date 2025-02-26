<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('state_name').$this->drawOrderArrow('state_name'),'#',$this->createOrderLink('code-list','state_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('state_type').$this->drawOrderArrow('state_type'),'#',$this->createOrderLink('code-list','state_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('state_day').$this->drawOrderArrow('state_day'),'#',$this->createOrderLink('code-list','state_day'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_index').$this->drawOrderArrow('z_index'),'#',$this->createOrderLink('code-list','z_index'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_display').$this->drawOrderArrow('z_display'),'#',$this->createOrderLink('code-list','z_display'))
			;
		?>
	</th>
</tr>
