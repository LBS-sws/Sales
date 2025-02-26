<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('pro_name').$this->drawOrderArrow('pro_name'),'#',$this->createOrderLink('code-list','pro_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('ka_type').$this->drawOrderArrow('ka_type'),'#',$this->createOrderLink('code-list','ka_type'))
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
