<tr class='clickable-row' data-href='<?php echo $this->getLink('CM05', 'clueRpt/edit', 'clueRpt/edit', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('CM05', 'clueRpt/edit', 'clueRpt/edit', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['clue_id']; ?></td>
	<td><?php echo $this->record['cust_name']; ?></td>
	<td><?php echo $this->record['clue_type']; ?></td>
	<td><?php echo $this->record['city']; ?></td>
	<td><?php echo $this->record['cust_class']; ?></td>
	<td><?php echo $this->record['cust_level']; ?></td>
	<td><?php echo $this->record['clue_service_id']; ?></td>
	<td><?php echo $this->record['total_amt']; ?></td>
	<td><?php echo $this->record['rpt_status']; ?></td>
	<td><?php echo $this->record['lcd']; ?></td>
</tr>

