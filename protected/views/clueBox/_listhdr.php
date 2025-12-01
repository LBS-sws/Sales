<tr>
    <th width="30px">  <input name="Fruit"  type="checkbox"  id="all"></th>
	<th width="30px"></th>
	<th width="100px">
		<?php echo TbHtml::link($this->getLabelName('clue_code').$this->drawOrderArrow('a.clue_code'),'#',$this->createOrderLink('code-list','a.clue_code'))
			;
		?>
	</th>
	<th width="150px">
		<?php echo TbHtml::link($this->getLabelName('cust_name').$this->drawOrderArrow('a.cust_name'),'#',$this->createOrderLink('code-list','a.cust_name'))
			;
		?>
	</th>
	<th width="100px">
		<?php echo TbHtml::link($this->getLabelName('clue_type').$this->drawOrderArrow('a.clue_type'),'#',$this->createOrderLink('code-list','a.clue_type'))
			;
		?>
	</th>
	<th width="130px">
		<?php echo TbHtml::link($this->getLabelName('cust_class').$this->drawOrderArrow('a.cust_class'),'#',$this->createOrderLink('code-list','a.cust_class'))
			;
		?>
	</th>
	<th width="130px">
		<?php echo TbHtml::link($this->getLabelName('cust_person').$this->drawOrderArrow('a.cust_person'),'#',$this->createOrderLink('code-list','a.cust_person'))
			;
		?>
	</th>
    <th width="130px">
        <?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('a.city'),'#',$this->createOrderLink('code-list','a.city'))
        ;
        ?>
    </th>
    <th width="100px">
        <?php echo TbHtml::link($this->getLabelName('rec_type').$this->drawOrderArrow('a.rec_type'),'#',$this->createOrderLink('code-list','a.rec_type'))
        ;
        ?>
    </th>
	<th width="130px">
		<?php echo TbHtml::link($this->getLabelName('clue_source').$this->drawOrderArrow('a.clue_source'),'#',$this->createOrderLink('code-list','a.clue_source'))
			;
		?>
	</th>
	<th width="130px">
		<?php echo TbHtml::link($this->getLabelName('end_date').$this->drawOrderArrow('a.end_date'),'#',$this->createOrderLink('code-list','a.end_date'))
			;
		?>
	</th>
	<th width="130px">
		<?php echo TbHtml::link($this->getLabelName('lcd').$this->drawOrderArrow('a.lcd'),'#',$this->createOrderLink('code-list','a.lcd'))
			;
		?>
	</th>
</tr>
