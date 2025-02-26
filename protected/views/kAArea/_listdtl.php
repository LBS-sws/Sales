<tr class='clickable-row' data-href='<?php echo $this->getLink('KA05', 'kAArea/edit', 'kAArea/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('KA05', 'kAArea/edit', 'kAArea/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['pro_name']; ?></td>
	<td><?php echo $this->record['city_code']; ?></td>
	<td><?php echo $this->record['z_index']; ?></td>
	<td><?php echo $this->record['z_display']; ?></td>
</tr>

