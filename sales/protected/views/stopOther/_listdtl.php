<tr class='clickable-row' data-href='<?php echo $this->getLink('SC02', 'stopOther/edit', 'stopOther/view', array('index'=>$this->record['service_id']));?>'>
    <td>
        <?php
        echo TbHtml::checkBox("StopOtherForm[shiftId][{$this->record['service_id']}]",false,array('class'=>'checkOne'))
        ?>
    </td>
    <td><?php echo $this->record['company_name']; ?></td>
	<td><?php echo $this->record['description']; ?></td>
	<td><?php echo $this->record['service']; ?></td>
	<td><?php echo $this->record['cont_info']; ?></td>
	<td><?php echo $this->record['salesman']; ?></td>
	<td><?php echo $this->record['status_dt']; ?></td>
	<td><?php echo $this->record['status']; ?></td>
</tr>
