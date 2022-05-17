<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('company_name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('stopOther-list','b.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('description').$this->drawOrderArrow('f.description'),'#',$this->createOrderLink('stopOther-list','f.description'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('service').$this->drawOrderArrow('a.service'),'#',$this->createOrderLink('stopOther-list','a.service'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('cont_info').$this->drawOrderArrow('a.cont_info'),'#',$this->createOrderLink('stopOther-list','a.cont_info'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('salesman').$this->drawOrderArrow('h.name'),'#',$this->createOrderLink('stopOther-list','h.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status_dt').$this->drawOrderArrow('a.status_dt'),'#',$this->createOrderLink('stopOther-list','a.status_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status').$this->drawOrderArrow('d.id'),'#',$this->createOrderLink('stopOther-list','d.id'))
			;
		?>
	</th>
</tr>
