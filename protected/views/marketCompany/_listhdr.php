<tr>
    <th>  <input name="Fruit"  type="checkbox"  id="all"></th>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('number_no').$this->drawOrderArrow('a.number_no'),'#',$this->createOrderLink('code-list','a.number_no'))
			;
		?>
	</th>
	<th width="18%">
		<?php echo TbHtml::link($this->getLabelName('company_name').$this->drawOrderArrow('a.company_name'),'#',$this->createOrderLink('code-list','a.company_name'))
			;
		?>
	</th>
	<th width="18%">
		<?php echo TbHtml::link($this->getLabelName('person_phone').$this->drawOrderArrow('a.person_phone'),'#',$this->createOrderLink('code-list','a.person_phone'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('allot_city').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('code-list','b.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('employee_name').$this->drawOrderArrow('h.name'),'#',$this->createOrderLink('code-list','h.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('start_date').$this->drawOrderArrow('a.start_date'),'#',$this->createOrderLink('code-list','a.start_date'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('end_date').$this->drawOrderArrow('a.end_date'),'#',$this->createOrderLink('code-list','a.end_date'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status_type').$this->drawOrderArrow('a.status_type'),'#',$this->createOrderLink('code-list','a.status_type'))
			;
		?>
	</th>
    <th></th>
</tr>
