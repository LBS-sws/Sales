<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city_name'),'#',$this->createOrderLink('salesSearchCustomer','city_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('company_code').$this->drawOrderArrow('company_code'),'#',$this->createOrderLink('salesSearchCustomer','company_code'))
			;
		?>
	</th>
	<th colspan=4>
		<?php echo TbHtml::link($this->getLabelName('company_name').$this->drawOrderArrow('company_name'),'#',$this->createOrderLink('salesSearchCustomer','company_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('company_status').$this->drawOrderArrow('company_status'),'#',$this->createOrderLink('salesSearchCustomer','company_status'))
			;
		?>
	</th>
</tr>
