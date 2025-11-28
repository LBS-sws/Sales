<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('city'),'#',$this->createOrderLink('code-list','city'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('name'),'#',$this->createOrderLink('code-list','name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('nal_tree_names').$this->drawOrderArrow('nal_tree_names'),'#',$this->createOrderLink('code-list','nal_tree_names'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_index').$this->drawOrderArrow('z_index'),'#',$this->createOrderLink('code-list','z_index'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('display').$this->drawOrderArrow('display'),'#',$this->createOrderLink('code-list','display'))
			;
		?>
	</th>
</tr>
