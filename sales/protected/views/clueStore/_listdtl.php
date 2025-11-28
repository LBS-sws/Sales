<tr class='clickable-row' data-href='<?php echo $this->getLink('CM02', 'clueStore/edit', 'clueStore/view', array('index'=>$this->record['id']));?>'>
    <td><?php echo $this->drawEditButton('CM02', 'clueStore/edit', 'clueStore/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['store_name']; ?></td>
	<td><?php echo $this->record['city']; ?></td>
	<td><?php echo $this->record['address']; ?></td>
	<td><?php echo $this->record['cust_person']; ?></td>
	<td><?php echo $this->record['cust_tel']; ?></td>
	<td><?php echo $this->record['invoice_header']; ?></td>
	<td><?php echo $this->record['tax_id']; ?></td>
	<td><?php echo $this->record['invoice_address']; ?></td>
</tr>

