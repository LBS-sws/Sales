<tr class='clickable-row' data-href='<?php echo $this->getLink('CM36', 'virtualHead/detail', 'virtualHead/detail', array('index'=>$this->record['id']));?>'>
    <td class="che">
        <?php if ($this->record['check_bool']): ?>
            <input value="<?php echo $this->record['id']; ?>"  type="checkbox" class="checkOne">
        <?php endif ?>
    </td>
	<td><?php echo $this->drawEditButton('CM36', 'virtualHead/detail', 'virtualHead/detail', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['vir_code']; ?></td>
	<td><?php echo $this->record['busine_id_text']; ?></td>
	<td><?php echo $this->record['vir_status']; ?></td>
	<td><?php echo $this->record['yewudalei']; ?></td>
	<td><?php echo $this->record['sign_type']; ?></td>
	<td><?php echo $this->record['city']; ?></td>
    <td><?php echo $this->record['store_code']; ?></td>
    <td><?php echo $this->record['store_name']; ?></td>
	<td><?php echo $this->record['year_amt']; ?></td>
	<td><?php echo $this->record['cont_code']; ?></td>
	<td><?php echo $this->record['sales_id']; ?></td>
    <td><?php echo $this->record['lbs_main']; ?></td>
	<td><?php echo $this->record['cont_start_dt']; ?></td>
	<td><?php echo $this->record['cont_end_dt']; ?></td>
	<td><?php echo $this->record['lcd']; ?></td>
	<td><?php echo $this->record['lcu']; ?></td>
	<td><?php echo $this->record['lud']; ?></td>
	<td><?php echo $this->record['luu']; ?></td>
</tr>

