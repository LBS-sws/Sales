<tr class='clickable-row' data-href='<?php echo $this->getLink('HC22', 'clueLevel/edit', 'clueLevel/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HC22', 'clueLevel/edit', 'clueLevel/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['level_code']; ?></td>
	<td><?php echo $this->record['level_name']; ?></td>
	<td><?php echo $this->record['level_desc']; ?></td>
	<td><?php echo $this->record['sort']; ?></td>
	<td><?php echo $this->record['status']==1?'启用':'禁用'; ?></td>
</tr>
