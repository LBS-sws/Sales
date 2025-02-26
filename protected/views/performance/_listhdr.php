<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('city'),'#',$this->createOrderLink('Performance-list','city'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('year').$this->drawOrderArrow('year'),'#',$this->createOrderLink('Performance-list','year'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('month').$this->drawOrderArrow('month'),'#',$this->createOrderLink('Performance-list','month'))
			;
		?>
	</th>
</tr>
