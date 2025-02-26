<tr>

	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('city'),'#',$this->createOrderLink('Integral-list','city'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('name'),'#',$this->createOrderLink('Integral-list','name'))
        ;
        ?>
    </th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('year').$this->drawOrderArrow('year'),'#',$this->createOrderLink('Integral-list','year'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('month').$this->drawOrderArrow('month'),'#',$this->createOrderLink('Integral-list','month'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('all_sum').$this->drawOrderArrow('all_sum'),'#',$this->createOrderLink('Integral-list','all_sum'))
        ;
        ?>
    </th>
</tr>
