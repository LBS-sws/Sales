<tr class='clickable-row' data-href='<?php echo $this->getLink('KA12', 'kADup/edit', 'kADup/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('KA12', 'kADup/edit', 'kADup/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['dup_name']; ?></td>
	<td><?php echo $this->record['dup_value']; ?></td>
	<td><?php echo $this->record['z_index']; ?></td>
</tr>

