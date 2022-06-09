<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('type_name').$this->drawOrderArrow('type_name'),'#',$this->createOrderLink('stopType-list','type_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('again_type').$this->drawOrderArrow('again_type'),'#',$this->createOrderLink('stopType-list','again_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_index').$this->drawOrderArrow('z_index'),'#',$this->createOrderLink('stopType-list','z_index'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('display').$this->drawOrderArrow('display'),'#',$this->createOrderLink('stopType-list','display'))
			;
		?>
	</th>
</tr>
