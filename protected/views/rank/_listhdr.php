<tr>
	<th></th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('season').$this->drawOrderArrow('season'),'#',$this->createOrderLink('Rank-list','season'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('month').$this->drawOrderArrow('month'),'#',$this->createOrderLink('Rank-list','month'))
        ;
        ?>
    </th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('city'),'#',$this->createOrderLink('Rank-list','city'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('employee_name').$this->drawOrderArrow('employee_name'),'#',$this->createOrderLink('Rank-list','employee_name'))
        ;
        ?>
    </th>

    <th>
        <?php echo TbHtml::link($this->getLabelName('rank'),'#')
        ;
        ?>
    </th>
</tr>
