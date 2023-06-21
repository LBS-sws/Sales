<?php
$labels = $this->model->attributeLabels();
$withrow = count($this->record['detail'])>0;
$idX = $this->record['id'];
?>
<tr class='clickable-row' data-href='<?php echo $this->getLink('KA01', 'kABot/edit', 'kABot/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('KA01', 'kABot/edit', 'kABot/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['info_date']; ?></td>
	<td><?php echo $this->record['apply_date']; ?></td>
	<td><?php echo $this->record['customer_no']; ?></td>
	<td><?php echo $this->record['customer_name']; ?></td>
	<td><?php echo $this->record['contact_user']; ?></td>
	<td><?php echo $this->record['source_id']; ?></td>
	<td><?php echo $this->record['class_id']; ?></td>
	<td><?php echo $this->record['link_id']; ?></td>
	<td><?php echo $this->record['kam_id']; ?></td>
    <td class="click-td" data-id="<?php echo $idX;?>">
        <?php
        //showdetail
        $iconX = $withrow ? "<span id='btn_$idX' class='fa fa-plus-square'></span>" : "<span class='fa fa-square'></span>";
        echo $iconX;
        ?>
    </td>
</tr>

<?php

if (count($this->record['detail'])>0) {
    foreach ($this->record['detail'] as $row) {
        $html = "<tr class='detail_$idX' style='display:none;'>";
        $html.= "<td colspan='4' class='text-right'><strong>".Yii::t("ka","info date")."：</strong>".General::toDate($row["info_date"])."</td>";
        $html.= "<td colspan='7' class='text-left'><strong>".Yii::t("ka","info text")."：</strong>".$row["info_text"]."</td>";
        $html.= "</tr>";
        echo $html;
    }
}
?>
