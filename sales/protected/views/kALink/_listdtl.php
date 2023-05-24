<tr class='clickable-row' data-href='<?php echo $this->getLink('KA04', 'kALink/edit', 'kALink/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('KA04', 'kALink/edit', 'kALink/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['pro_name']; ?></td>
	<td><?php echo $this->record['rate_num']; ?></td>
	<td><?php echo $this->record['z_index']; ?></td>
	<td><?php echo $this->record['z_display']; ?></td>
</tr>

