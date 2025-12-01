<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('set_id').$this->drawOrderArrow('a.set_id'),'#',$this->createOrderLink('code-list','a.set_id'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('set_name').$this->drawOrderArrow('a.set_name'),'#',$this->createOrderLink('code-list','a.set_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('set_type').$this->drawOrderArrow('a.set_type'),'#',$this->createOrderLink('code-list','a.set_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('u_code').$this->drawOrderArrow('a.u_code'),'#',$this->createOrderLink('code-list','a.u_code'))
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
