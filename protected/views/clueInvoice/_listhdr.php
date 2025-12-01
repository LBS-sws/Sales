<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('invoice_name').$this->drawOrderArrow('a.invoice_name'),'#',$this->createOrderLink('code-list','a.invoice_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('code-list','b.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('invoice_type').$this->drawOrderArrow('a.invoice_type'),'#',$this->createOrderLink('code-list','a.invoice_type'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('invoice_header').$this->drawOrderArrow('a.invoice_header'),'#',$this->createOrderLink('code-list','a.invoice_header'))
        ;
        ?>
    </th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('tax_id').$this->drawOrderArrow('a.tax_id'),'#',$this->createOrderLink('code-list','a.tax_id'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('invoice_address').$this->drawOrderArrow('a.invoice_address'),'#',$this->createOrderLink('code-list','a.invoice_address'))
			;
		?>
	</th>
</tr>
