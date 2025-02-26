<tr class='clickable-row' data-href='<?php echo $this->getLink('HC03', 'custtype/edit', 'custtype/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HC03', 'custtype/edit', 'custtype/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['type_group']; ?></td>
	<td><?php echo $this->record['city']; ?></td>
</tr>

