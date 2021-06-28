<tr>
	<th>
		<?php echo TbHtml::link($this->getLabelName('employee_code').$this->drawOrderArrow('a.employee_code'),'#',$this->createOrderLink('salesSearchCount-list','employee_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('employee_name').$this->drawOrderArrow('a.employee_name'),'#',$this->createOrderLink('salesSearchCount-list','employee_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('salesSearchCount-list','city'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('search_date').$this->drawOrderArrow('a.search_date'),'#',$this->createOrderLink('salesSearchCount-list','search_date'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('search_num').$this->drawOrderArrow('a.search_num'),'#',$this->createOrderLink('salesSearchCount-list','search_num'))
			;
		?>
	</th>
</tr>
