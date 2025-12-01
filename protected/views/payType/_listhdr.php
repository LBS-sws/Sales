<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('a.name'),'#',$this->createOrderLink('code-list','a.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('zt_code').$this->drawOrderArrow('a.zt_code'),'#',$this->createOrderLink('code-list','a.zt_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('u_id').$this->drawOrderArrow('a.u_id'),'#',$this->createOrderLink('code-list','a.u_id'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_display').$this->drawOrderArrow('a.z_display'),'#',$this->createOrderLink('code-list','a.z_display'))
			;
		?>
	</th>
</tr>
