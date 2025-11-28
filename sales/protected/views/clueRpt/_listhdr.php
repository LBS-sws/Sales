<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('clue_id').$this->drawOrderArrow('rpt.clue_id'),'#',$this->createOrderLink('code-list','rpt.clue_id'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('cust_name').$this->drawOrderArrow('a.cust_name'),'#',$this->createOrderLink('code-list','a.cust_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('clue_type').$this->drawOrderArrow('rpt.clue_type'),'#',$this->createOrderLink('code-list','rpt.clue_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('code-list','b.name'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('cust_class').$this->drawOrderArrow('g.name'),'#',$this->createOrderLink('code-list','g.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('cust_level').$this->drawOrderArrow('h.pro_name'),'#',$this->createOrderLink('code-list','h.pro_name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('clue_service_id').$this->drawOrderArrow('rpt.clue_service_id'),'#',$this->createOrderLink('code-list','rpt.clue_service_id'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('total_amt').$this->drawOrderArrow('rpt.total_amt'),'#',$this->createOrderLink('code-list','rpt.total_amt'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('rpt_status').$this->drawOrderArrow('rpt.rpt_status'),'#',$this->createOrderLink('code-list','rpt.rpt_status'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('lcd').$this->drawOrderArrow('rpt.lcd'),'#',$this->createOrderLink('code-list','rpt.lcd'))
        ;
        ?>
    </th>
</tr>
