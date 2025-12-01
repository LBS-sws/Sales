<tr>
	<th width="30px"></th>
	<th width="150px">
		<?php echo TbHtml::link($this->getLabelName('store_name').$this->drawOrderArrow('a.store_name'),'#',$this->createOrderLink('code-list','a.store_name'))
			;
		?>
	</th>
	<th width="130px">
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('code-list','b.name'))
			;
		?>
	</th>
	<th width="130px">
		<?php echo TbHtml::link($this->getLabelName('address').$this->drawOrderArrow('a.address'),'#',$this->createOrderLink('code-list','a.address'))
			;
		?>
	</th>
	<th width="100px">
		<?php echo TbHtml::link($this->getLabelName('cust_person').$this->drawOrderArrow('a.cust_person'),'#',$this->createOrderLink('code-list','a.cust_person'))
			;
		?>
	</th>
	<th width="100px">
		<?php echo TbHtml::link($this->getLabelName('cust_tel').$this->drawOrderArrow('a.cust_tel'),'#',$this->createOrderLink('code-list','a.cust_tel'))
			;
		?>
	</th>
    <th width="130px">
        <?php echo TbHtml::link($this->getLabelName('invoice_header').$this->drawOrderArrow('a.invoice_header'),'#',$this->createOrderLink('code-list','a.invoice_header'))
        ;
        ?>
    </th>
	<th width="130px">
		<?php echo TbHtml::link($this->getLabelName('tax_id').$this->drawOrderArrow('a.tax_id'),'#',$this->createOrderLink('code-list','a.tax_id'))
			;
		?>
	</th>
	<th width="130px">
		<?php echo TbHtml::link($this->getLabelName('invoice_address').$this->drawOrderArrow('a.invoice_address'),'#',$this->createOrderLink('code-list','a.invoice_address'))
			;
		?>
	</th>
</tr>
