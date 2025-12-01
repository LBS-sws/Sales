<tr class='clickable-row' data-href='<?php echo $this->getLink('HC14', 'seal/edit', 'seal/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HC14', 'seal/edit', 'seal/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['mh_code']; ?></td>
	<td><?php echo $this->record['z_display']; ?></td>
</tr>

