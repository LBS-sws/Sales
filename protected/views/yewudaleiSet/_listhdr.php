<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('a.name'),'#',$this->createOrderLink('code-list','a.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('bs_id').$this->drawOrderArrow('a.bs_id'),'#',$this->createOrderLink('code-list','a.bs_id'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('u_id').$this->drawOrderArrow('a.u_id'),'#',$this->createOrderLink('code-list','a.u_id'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('mh_code').$this->drawOrderArrow('a.mh_code'),'#',$this->createOrderLink('code-list','a.mh_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status').$this->drawOrderArrow('a.status'),'#',$this->createOrderLink('code-list','a.status'))
			;
		?>
	</th>
</tr>
