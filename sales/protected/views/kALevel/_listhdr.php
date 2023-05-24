<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('pro_name').$this->drawOrderArrow('pro_name'),'#',$this->createOrderLink('code-list','pro_name'))
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
	<th>
		<?php echo TbHtml::link($this->getLabelName('class_name').$this->drawOrderArrow('id'),'#',$this->createOrderLink('code-list','id'))
			;
		?>
	</th>
</tr>
