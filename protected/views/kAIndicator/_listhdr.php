<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('employee_name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('code-list','b.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('effect_date').$this->drawOrderArrow('a.effect_date'),'#',$this->createOrderLink('code-list','a.effect_date'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('indicator_money').$this->drawOrderArrow('a.indicator_money'),'#',$this->createOrderLink('code-list','a.indicator_money'))
			;
		?>
	</th>
</tr>
