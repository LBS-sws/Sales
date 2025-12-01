<tr class='clickable-row' data-href='<?php echo $this->getLink('HC21', 'setMenu/edit', 'setMenu/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HC21', 'setMenu/edit', 'setMenu/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['set_id']; ?></td>
	<td><?php echo $this->record['set_name']; ?></td>
	<td><?php echo $this->record['set_type']; ?></td>
	<td><?php echo $this->record['u_code']; ?></td>
	<td><?php echo $this->record['mh_code']; ?></td>
	<td><?php echo $this->record['z_display']; ?></td>
</tr>

