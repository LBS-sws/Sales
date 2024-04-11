<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('dup_name').$this->drawOrderArrow('dup_name'),'#',$this->createOrderLink('code-list','dup_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('dup_value').$this->drawOrderArrow('dup_value'),'#',$this->createOrderLink('code-list','dup_value'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_index').$this->drawOrderArrow('z_index'),'#',$this->createOrderLink('code-list','z_index'))
			;
		?>
	</th>
</tr>
