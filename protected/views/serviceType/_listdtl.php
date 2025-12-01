<tr class='clickable-row' data-href='<?php echo $this->getLink('HC16', 'serviceType/edit', 'serviceType/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HC16', 'serviceType/edit', 'serviceType/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['id_char']; ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['class_id']; ?></td>
	<td><?php echo $this->record['u_code']; ?></td>
	<td><?php echo $this->record['z_index']; ?></td>
	<td><?php echo $this->record['z_display']; ?></td>
</tr>

