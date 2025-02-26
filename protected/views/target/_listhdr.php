<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('city'),'#',$this->createOrderLink('Target-list','city'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('employee_name').$this->drawOrderArrow('employee_name'),'#',$this->createOrderLink('Target-list','employee_name'))
        ;
        ?>
    </th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('year').$this->drawOrderArrow('year'),'#',$this->createOrderLink('Target-list','year'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('month').$this->drawOrderArrow('month'),'#',$this->createOrderLink('Target-list','month'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('sale_day'),'#')
        ;
        ?>
    </th>
</tr>
