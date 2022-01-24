<tr class='clickable-row' data-href='<?php echo $this->getLink('HD05', 'clubRec/edit', 'clubRec/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HD05', 'clubRec/edit', 'clubRec/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['rec_year']; ?></td>
	<td><?php echo $this->record['month_type']; ?></td>
	<td><?php echo $this->record['code']; ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['entry_time']; ?></td>
	<td><?php echo $this->record['dept_name']; ?></td>
	<td><?php echo $this->record['city_name']; ?></td>
	<td><?php echo $this->record['rec_user']; ?></td>
</tr>
