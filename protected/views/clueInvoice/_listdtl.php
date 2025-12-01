<tr class='clickable-row' data-href='<?php echo $this->getLink('CM02', 'clueInvoice/edit', 'clueInvoice/view', array('index'=>$this->record['id']));?>'>
    <td><?php echo $this->drawEditButton('CM02', 'clueInvoice/edit', 'clueInvoice/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['invoice_name']; ?></td>
	<td><?php echo $this->record['city']; ?></td>
	<td><?php echo $this->record['invoice_type']; ?></td>
	<td><?php echo $this->record['invoice_header']; ?></td>
	<td><?php echo $this->record['tax_id']; ?></td>
	<td><?php echo $this->record['invoice_address']; ?></td>
</tr>

