<tr class='clickable-row' data-href='<?php echo $this->getLink('HC15', 'payType/edit', 'payType/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HC15', 'payType/edit', 'payType/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['zt_code']; ?></td>
	<td><?php echo $this->record['u_id']; ?></td>
	<td><?php echo $this->record['z_display']; ?></td>
</tr>

