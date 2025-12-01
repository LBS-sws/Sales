<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('a.name'),'#',$this->createOrderLink('code-list','a.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('str_type').$this->drawOrderArrow('a.str_type'),'#',$this->createOrderLink('code-list','a.str_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_display').$this->drawOrderArrow('a.z_display'),'#',$this->createOrderLink('code-list','a.z_display'))
			;
		?>
	</th>
</tr>
