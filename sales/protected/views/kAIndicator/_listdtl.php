<tr class='clickable-row' data-href='<?php echo $this->getLink('KA13', 'kAIndicator/edit', 'kAIndicator/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('KA13', 'kAIndicator/edit', 'kAIndicator/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['employee_name']; ?></td>
	<td><?php echo $this->record['effect_date']; ?></td>
	<td><?php echo $this->record['indicator_money']; ?></td>
</tr>

