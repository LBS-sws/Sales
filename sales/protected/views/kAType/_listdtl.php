<tr class='clickable-row' data-href='<?php echo $this->getLink('KA06', 'kAType/edit', 'kAType/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('KA06', 'kAType/edit', 'kAType/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['ka_name']; ?></td>
	<td><?php echo $this->record['ka_type']; ?></td>
	<td><?php echo $this->record['z_index']; ?></td>
	<td><?php echo $this->record['z_display']; ?></td>
</tr>

