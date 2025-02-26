<tr class='clickable-row' data-href='<?php echo $this->getLink('SC03', 'stopSite/edit', 'stopSite/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('SC03', 'stopSite/edit', 'stopSite/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['stop_month']; ?></td>
	<td><?php echo $this->record['month_money']; ?></td>
	<td><?php echo $this->record['year_money']; ?></td>
</tr>
