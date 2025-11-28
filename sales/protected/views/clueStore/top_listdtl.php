<tr class='clickable-row' data-href='<?php echo $this->getLink('CM02', 'clueStore/detail', 'clueStore/detail', array('index'=>$this->record['id']));?>'>
    <td><?php echo $this->drawEditButton('CM02', 'clueStore/detail', 'clueStore/detail', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['store_code']; ?></td>
	<td><?php echo $this->record['store_name']; ?></td>
	<td><?php echo $this->record['cust_name']; ?></td>
	<td><?php echo $this->record['yewudalei']; ?></td>
	<td><?php echo $this->record['cust_class']; ?></td>
    <td><?php echo $this->record['cust_person']; ?></td>
    <td><?php echo $this->record['cust_tel']; ?></td>
	<td><?php echo $this->record['city']; ?></td>
	<td><?php echo $this->record['store_status']; ?></td>
</tr>

