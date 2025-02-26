<tr class='clickable-row' data-href='<?php echo $this->getLink('SC04', 'stopType/edit', 'stopType/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('SC04', 'stopType/edit', 'stopType/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['type_name']; ?></td>
	<td><?php echo $this->record['again_type']; ?></td>
	<td><?php echo $this->record['z_index']; ?></td>
	<td><?php echo $this->record['display']; ?></td>
</tr>
