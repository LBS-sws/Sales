<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('a.name'),'#',$this->createOrderLink('code-list','a.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('mh_code').$this->drawOrderArrow('a.mh_code'),'#',$this->createOrderLink('code-list','a.mh_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_display').$this->drawOrderArrow('a.z_display'),'#',$this->createOrderLink('code-list','a.z_display'))
			;
		?>
	</th>
</tr>
