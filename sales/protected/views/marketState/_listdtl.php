<tr class='clickable-row' data-href='<?php echo $this->getLink('MT06', 'marketState/edit', 'marketState/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('MT06', 'marketState/edit', 'marketState/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['state_name']; ?></td>
	<td><?php echo $this->record['state_type']; ?></td>
	<td><?php echo $this->record['state_day']; ?></td>
	<td><?php echo $this->record['z_index']; ?></td>
	<td><?php echo $this->record['z_display']; ?></td>
</tr>

