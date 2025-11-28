<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('id_char').$this->drawOrderArrow('a.id_char'),'#',$this->createOrderLink('code-list','a.id_char'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('a.name'),'#',$this->createOrderLink('code-list','a.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('class_id').$this->drawOrderArrow('a.class_id'),'#',$this->createOrderLink('code-list','a.class_id'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('u_code').$this->drawOrderArrow('a.u_code'),'#',$this->createOrderLink('code-list','a.u_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_index').$this->drawOrderArrow('a.z_index'),'#',$this->createOrderLink('code-list','a.z_index'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_display').$this->drawOrderArrow('a.z_display'),'#',$this->createOrderLink('code-list','a.z_display'))
			;
		?>
	</th>
</tr>
