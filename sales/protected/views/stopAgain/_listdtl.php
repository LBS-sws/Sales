<tr class='clickable-row <?php echo $this->record['textColor'];?>' data-href='<?php echo $this->getLink('SC07', 'stopAgain/edit', 'stopAgain/view', array('index'=>$this->record['id']));?>'>
    <td><?php echo $this->drawEditButton('SC07', 'stopAgain/edit', 'stopAgain/view', array('index'=>$this->record['id'])); ?></td>

    <td><?php echo $this->record['back_date']; ?></td>
    <td><?php echo $this->record['status_dt']; ?></td>
    <td><?php echo $this->record['company_name']; ?></td>
    <?php if (!Yii::app()->user->isSingleCity()): ?>
        <td><?php echo $this->record['city']; ?></td>
    <?php endif ?>
    <td><?php echo $this->record['description']; ?></td>
    <td><?php echo $this->record['amt_paid']; ?></td>
    <td><?php echo $this->record['salesman']; ?></td>
    <td><?php echo $this->record['shiftStatus']; ?></td>
    <td><?php echo $this->record['again_end_date']; ?></td>
</tr>
