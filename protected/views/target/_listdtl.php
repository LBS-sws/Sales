<tr class='clickable-row' data-href='<?php echo $this->getLink('HK05', 'target/edit', 'target/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HK05', 'target/edit', 'target/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['city']; ?></td>
    <td><?php echo $this->record['employee_name']; ?></td>
	<td><?php echo $this->record['year']; ?></td>
	<td><?php echo $this->record['month']; ?></td>
    <td><?php echo $this->record['sale_day']; ?></td>
</tr>

