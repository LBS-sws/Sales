<tr class='clickable-row' data-href='<?php echo $this->getLink('HC12', 'mainLbs/edit', 'mainLbs/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HC12', 'mainLbs/edit', 'mainLbs/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['sh_code']; ?></td>
	<td><?php echo $this->record['mh_code']; ?></td>
	<td><?php echo $this->record['city']; ?></td>
	<td><?php echo $this->record['show_type']; ?></td>
	<td><?php echo $this->record['z_display']; ?></td>
</tr>

