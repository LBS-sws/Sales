<?php
$urlStr = $this->record['table_type']==1?"clueHead/view":"clientHead/view";
?>

<tr class='clickable-row' data-href='<?php echo $this->getLink('CMT02', $urlStr, $urlStr, array('index'=>$this->record['clue_id'],'service_id'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('CMT02', $urlStr, $urlStr, array('index'=>$this->record['clue_id'],'service_id'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['id']; ?></td>
	<td><?php echo $this->record['clue_code']; ?></td>
	<td><?php echo $this->record['cust_name']; ?></td>
	<td><?php echo $this->record['city']; ?></td>
	<td><?php echo $this->record['visit_obj_text']; ?></td>
	<td><?php echo $this->record['clue_type']; ?></td>
	<td><?php echo $this->record['predict_amt']; ?></td>
	<td><?php echo $this->record['predict_date']; ?></td>
	<td><?php echo $this->record['sign_odds']; ?></td>
	<td><?php echo $this->record['busine_id_text']; ?></td>
	<td><?php echo $this->record['service_status']; ?></td>
	<td><?php echo $this->record['create_staff']; ?></td>
	<td><?php echo $this->record['lcd']; ?></td>
</tr>

