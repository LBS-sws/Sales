<tr class='clickable-row' data-href='<?php echo $this->getLink('KA01', 'kABot/edit', 'kABot/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('KA01', 'kABot/edit', 'kABot/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['apply_date']; ?></td>
	<td><?php echo $this->record['customer_no']; ?></td>
	<td><?php echo $this->record['customer_name']; ?></td>
	<td><?php echo $this->record['contact_user']; ?></td>
	<td><?php echo $this->record['source_id']; ?></td>
	<td><?php echo $this->record['class_id']; ?></td>
	<td><?php echo $this->record['link_id']; ?></td>
	<td><?php echo $this->record['kam_id']; ?></td>
</tr>

