<tr class='clickable-row' data-href='<?php echo $this->getLink('HC23', 'clueTag/edit', 'clueTag/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HC23', 'clueTag/edit', 'clueTag/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['tag_code']; ?></td>
	<td><?php echo $this->record['tag_name']; ?></td>
	<td><?php echo sprintf('<span style="background-color:%s;color:white;padding:5px 10px;border-radius:3px;">%s</span>',$this->record['tag_color'],$this->record['tag_color']); ?></td>
	<td><?php echo $this->record['tag_desc']; ?></td>
	<td><?php echo $this->record['sort']; ?></td>
	<td><?php echo $this->record['status']==1?'启用':'禁用'; ?></td>
</tr>
