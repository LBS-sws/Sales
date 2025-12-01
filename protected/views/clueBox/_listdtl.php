<tr class='clickable-row' data-href='<?php echo $this->getLink('CM01', 'clueBox/edit', 'clueBox/view', array('index'=>$this->record['id']));?>'>
    <td class="che">
        <?php if ($this->record['assign_bool']): ?>
            <input value="<?php echo $this->record['id']; ?>"  type="checkbox" class="checkOne">
        <?php endif ?>
    </td>
    <td><?php echo $this->drawEditButton('CM01', 'clueBox/edit', 'clueBox/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['clue_code']; ?></td>
	<td><?php echo $this->record['cust_name']; ?></td>
	<td><?php echo $this->record['clue_type']; ?></td>
	<td><?php echo $this->record['cust_class']; ?></td>
	<td><?php echo $this->record['cust_person']; ?></td>
	<td><?php echo $this->record['city']; ?></td>
	<td><?php echo $this->record['rec_type']; ?></td>
	<td><?php echo $this->record['clue_source']; ?></td>
	<td><?php echo $this->record['end_date']; ?></td>
	<td><?php echo $this->record['lcd']; ?></td>
</tr>

