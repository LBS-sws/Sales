<tr class='clickable-row' data-href='<?php echo $this->getLink('HC05', 'level/edit', 'level/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HC05', 'level/edit', 'level/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['level']; ?></td>
	<td><?php echo $this->record['start_fraction']; ?></td>
    <td><?php echo $this->record['end_fraction']; ?></td>
    <td><?php echo $this->record['new_level']; ?></td>
    <td><?php echo $this->record['new_fraction']; ?></td>
    <td><?php echo $this->record['reward']; ?></td>
</tr>

