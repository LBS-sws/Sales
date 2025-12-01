<tr class='clickable-row' data-href='<?php echo $this->getLink('CMT02', 'clueHead/edit', 'clueHead/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('CMT02', 'clueHead/edit', 'clueHead/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['clue_code']; ?></td>
	<td><?php echo $this->record['cust_name']; ?></td>
	<td><?php echo $this->record['clue_type']; ?></td>
	<td><?php echo $this->record['cust_class']; ?></td>
	<td><?php echo $this->record['cust_person']; ?></td>
	<td><?php echo $this->record['city']; ?></td>
	<td><?php echo $this->record['clue_source']; ?></td>
	<td><?php echo $this->record['last_date']; ?></td>
	<td><?php echo $this->record['clue_status']; ?></td>
	<td><?php echo $this->record['rec_employee_id']; ?></td>
	<td><?php echo $this->record['end_date']; ?></td>
    <td>
        <?php
        echo TbHtml::link("<span class='fa fa-ellipsis-h'></span>",Yii::app()->createUrl('clueStore/storeList',array(
            "clue_id"=>$this->record['id'])),array("style"=>"padding:10px;"
        )); ?>
    </td>
</tr>

