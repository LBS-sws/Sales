<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('a.name'),'#',$this->createOrderLink('code-list','a.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('sh_code').$this->drawOrderArrow('a.sh_code'),'#',$this->createOrderLink('code-list','a.sh_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('mh_code').$this->drawOrderArrow('a.mh_code'),'#',$this->createOrderLink('code-list','a.mh_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('code-list','b.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('show_type').$this->drawOrderArrow('a.show_type'),'#',$this->createOrderLink('code-list','a.show_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_display').$this->drawOrderArrow('a.z_display'),'#',$this->createOrderLink('code-list','a.z_display'))
			;
		?>
	</th>
</tr>
