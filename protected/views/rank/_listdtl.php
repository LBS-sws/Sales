<tr class='clickable-row' data-href='<?php echo $this->getLink('HK05', 'rank/view', 'rank/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HD01', 'rank/view', 'rank/view', array('index'=>$this->record['id'])); ?></td>
    <td><?php echo $this->record['season']; ?></td>
    <td><?php echo $this->record['month']; ?></td>
	<td><?php echo $this->record['city']; ?></td>
    <td><?php echo $this->record['employee_name']; ?></td>

    <td><?php echo $this->record['new_rank']; ?></td>
</tr>

