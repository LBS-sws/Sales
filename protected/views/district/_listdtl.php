<tr class='clickable-row' data-href='<?php echo $this->getLink('HC04', 'district/edit', 'district/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HC04', 'district/edit', 'district/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['city']; ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['nal_tree_names']; ?></td>
	<td><?php echo $this->record['z_index']; ?></td>
	<td><?php echo $this->record['display']; ?></td>
</tr>

