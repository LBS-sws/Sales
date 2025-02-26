<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('level').$this->drawOrderArrow('level'),'#',$this->createOrderLink('level-list','level'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('start_fraction').$this->drawOrderArrow('start_fraction'),'#',$this->createOrderLink('level-list','start_fraction'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('end_fraction').$this->drawOrderArrow('end_fraction'),'#',$this->createOrderLink('level-list','end_fraction'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('new_level').$this->drawOrderArrow('new_level'),'#',$this->createOrderLink('level-list','new_level'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('new_fraction').$this->drawOrderArrow('new_fraction'),'#',$this->createOrderLink('level-list','new_fraction'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('reward').$this->drawOrderArrow('reward'),'#',$this->createOrderLink('level-list','reward'))
        ;
        ?>
    </th>
</tr>
