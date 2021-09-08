<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('rpt_type').$this->drawOrderArrow('rpt_type'),'#',$this->createOrderLink('code-list','rpt_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('name'),'#',$this->createOrderLink('code-list','name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('display_num').$this->drawOrderArrow('display_num'),'#',$this->createOrderLink('code-list','display_num'))
			;
		?>
	</th>
</tr>
