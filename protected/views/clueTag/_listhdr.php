<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('tag_code').$this->drawOrderArrow('tag_code'),'#',$this->createOrderLink('clue-tag-list','tag_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('tag_name').$this->drawOrderArrow('tag_name'),'#',$this->createOrderLink('clue-tag-list','tag_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('tag_color').$this->drawOrderArrow('tag_color'),'#',$this->createOrderLink('clue-tag-list','tag_color'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('tag_desc').$this->drawOrderArrow('tag_desc'),'#',$this->createOrderLink('clue-tag-list','tag_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('sort').$this->drawOrderArrow('sort'),'#',$this->createOrderLink('clue-tag-list','sort'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status').$this->drawOrderArrow('status'),'#',$this->createOrderLink('clue-tag-list','status'))
			;
		?>
	</th>
</tr>
