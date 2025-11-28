<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('name'),'#',$this->createOrderLink('code-list','name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('type_group').$this->drawOrderArrow('type_group'),'#',$this->createOrderLink('code-list','type_group'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_display').$this->drawOrderArrow('z_display'),'#',$this->createOrderLink('code-list','z_display'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('city'),'#',$this->createOrderLink('code-list','city'))
			;
		?>
	</th>
</tr>
