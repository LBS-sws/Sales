<tr class='clickable-row contract-row' data-href='<?php echo $this->getLink('CM36', 'contHead/detail', 'contHead/detail', array('index'=>$this->record['id']));?>'>
	<td onclick="event.stopPropagation();">
        <?php 
        // 获取原始状态值（从数据库的 cont_status 字段）
        $statusValue = isset($this->record['cont_status_value']) ? $this->record['cont_status_value'] : 0;
        if ($statusValue < 10): 
        ?>
        <input type="checkbox" class="select-contract-item" value="<?php echo $this->record['id']; ?>" data-clue-id="<?php echo isset($this->record['clue_id']) ? $this->record['clue_id'] : ''; ?>" />
        <?php else: ?>
        <span class="text-muted" title="已生效的合同不可操作">-</span>
        <?php endif; ?>
    </td>
	<td><?php echo $this->record['cont_code']; ?></td>
	<td><?php echo $this->record['cont_type']; ?></td>
	<td><?php echo $this->record['busine_id_text']; ?></td>
	<td><?php echo $this->record['cont_status']; ?></td>
	<td><?php echo $this->record['yewudalei']; ?></td>
	<td><?php echo $this->record['sign_type']; ?></td>
    <td><?php echo $this->record['clue_code']; ?></td>
    <td><?php echo $this->record['cust_name']; ?></td>
	<td><?php echo $this->record['store_sum']; ?></td>
	<td><?php echo $this->record['total_amt']; ?></td>
	<td><?php echo $this->record['sales_id']; ?></td>
    <td><?php echo $this->record['lbs_main']; ?></td>
    <td><?php echo $this->record['city']; ?></td>
	<td><?php echo $this->record['cont_start_dt']; ?></td>
	<td><?php echo $this->record['cont_end_dt']; ?></td>
	<td><?php echo $this->record['lcd']; ?></td>
	<td><?php echo $this->record['lcu']; ?></td>
	<td><?php echo $this->record['lud']; ?></td>
	<td><?php echo $this->record['luu']; ?></td>
</tr>

