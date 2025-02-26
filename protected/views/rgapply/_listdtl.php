<tr class='clickable-row'  data-href='<?php echo $this->getLink('HE02', 'rgapply/edit', 'rgapply/view', array('index'=>$this->record['id']));?>'>

	<td><?php echo $this->drawEditButton('HE02', 'rgapply/edit', 'edit', array('index'=>$this->record['id'])); ?></td>

    <td><?php echo $this->record['employee_name']; ?></td>
    <td><?php echo $this->record['gift_name']; ?></td>
    <td><?php echo $this->record['bonus_point']; ?></td>
    <td><?php echo $this->record['apply_num']; ?></td>
    <td><?php echo $this->record['apply_date']; ?></td>
<td><?php if($this->record['status']==1){echo "审核通过";}else if($this->record['status']==2){echo "审核驳回";}else{echo "待审核";} ?></td>
</tr>
