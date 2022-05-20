<tr class='clickable-row <?php echo $this->record['textColor'];?>' data-href='<?php echo $this->getLink('SC01', 'stopBack/edit', 'stopBack/view', array('index'=>$this->record['service_id']));?>'>
    <td><?php echo $this->drawEditButton('SC01', 'stopBack/edit', 'stopBack/view', array('index'=>$this->record['service_id'])); ?></td>
    <td>
        <?php
        if(empty($this->record['bold_service'])){
            echo "<a class='updateVip' data-id='{$this->record['service_id']}' href='javascript:void(0);'><span class='fa fa-star-o'></span></a>";
        }else{
            echo "<a class='updateVip' data-id='{$this->record['service_id']}' href='javascript:void(0);'><span class='fa fa-star'></span></a>";
        }
        ?>
    </td>
    <td><?php echo $this->record['back_date']; ?></td>
    <td><?php echo $this->record['status_dt']; ?></td>
    <td><?php echo $this->record['company_name']; ?></td>
    <td><?php echo $this->record['description']; ?></td>
    <td><?php echo $this->record['amt_paid']; ?></td>
    <td><?php echo $this->record['salesman']; ?></td>
    <td><?php echo $this->record['shiftStatus']; ?></td>
</tr>
