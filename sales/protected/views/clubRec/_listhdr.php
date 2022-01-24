<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('rec_year').$this->drawOrderArrow('a.rec_year'),'#',$this->createOrderLink('clubRec-list','a.rec_year'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('month_type').$this->drawOrderArrow('a.month_type'),'#',$this->createOrderLink('clubRec-list','a.month_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('code').$this->drawOrderArrow('b.code'),'#',$this->createOrderLink('clubRec-list','b.code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('clubRec-list','b.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('entry_time').$this->drawOrderArrow('b.entry_time'),'#',$this->createOrderLink('clubRec-list','b.entry_time'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('dept_name').$this->drawOrderArrow('f.name'),'#',$this->createOrderLink('clubRec-list','f.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city.name'),'#',$this->createOrderLink('clubRec-list','city.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('rec_user').$this->drawOrderArrow('a.rec_user'),'#',$this->createOrderLink('clubRec-list','a.rec_user'))
			;
		?>
	</th>
</tr>
