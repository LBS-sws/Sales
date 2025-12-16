<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('level_code').$this->drawOrderArrow('level_code'),'#',$this->createOrderLink('clue-level-list','level_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('level_name').$this->drawOrderArrow('level_name'),'#',$this->createOrderLink('clue-level-list','level_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('level_desc').$this->drawOrderArrow('level_desc'),'#',$this->createOrderLink('clue-level-list','level_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('sort').$this->drawOrderArrow('sort'),'#',$this->createOrderLink('clue-level-list','sort'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status').$this->drawOrderArrow('status'),'#',$this->createOrderLink('clue-level-list','status'))
			;
		?>
	</th>
</tr>
