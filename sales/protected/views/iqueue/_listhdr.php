<tr>
	<th>
		<?php echo TbHtml::link($this->getLabelName('id').$this->drawOrderArrow('id'),'#',$this->createOrderLink('queue-list','id'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('import_name').$this->drawOrderArrow('import_name'),'#',$this->createOrderLink('queue-list','import_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('import_type').$this->drawOrderArrow('import_type'),'#',$this->createOrderLink('queue-list','import_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('req_dt').$this->drawOrderArrow('req_dt'),'#',$this->createOrderLink('queue-list','req_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('fin_dt').$this->drawOrderArrow('fin_dt'),'#',$this->createOrderLink('queue-list','fin_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('message').$this->drawOrderArrow('message'),'#',$this->createOrderLink('queue-list','message'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('success_num').$this->drawOrderArrow('success_num'),'#',$this->createOrderLink('queue-list','success_num'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('error_num').$this->drawOrderArrow('error_num'),'#',$this->createOrderLink('queue-list','error_num'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status').$this->drawOrderArrow('status'),'#',$this->createOrderLink('queue-list','status'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('error_file').$this->drawOrderArrow('ts'),'#',$this->createOrderLink('queue-list','ts'))
			;
		?>
	</th>
</tr>
