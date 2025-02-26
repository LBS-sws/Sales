<?php
$labels = $this->model->attributeLabels();
$withrow = count($this->record['detail'])>0;
$idX = $this->record['id'];
?>
<tr class='clickable-row <?php echo $this->record['color']; ?>' data-href='<?php echo $this->getLink('MT03', 'marketReject/edit', 'marketReject/view', array('index'=>$this->record['id']));?>'>
    <td class="che">
        <?php if (empty($this->record["ready_bool"])): ?>
            <input value="<?php echo $this->record['id']; ?>"  type="checkbox">
        <?php endif ?>
    </td>
    <td><?php echo $this->drawEditButton('MT03', 'marketReject/edit', 'marketReject/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['number_no']; ?></td>
	<td><?php echo $this->record['company_name']; ?></td>
	<td><?php echo $this->record['person_phone']; ?></td>
	<td><?php echo $this->record['allot_city']; ?></td>
	<td><?php echo $this->record['employee_name']; ?></td>
	<td><?php echo $this->record['start_date']; ?></td>
	<td><?php echo $this->record['end_date']; ?></td>
	<td><?php echo $this->record['reject_note']; ?></td>
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
        $lcd = $row["lcd"];
        $html = "<tr class='detail_$idX' data-lcd='{$lcd}' style='display:none;'>";
        $html.= "<td colspan='4' class='text-right'><strong>".Yii::t("market","info date")."：</strong>".General::toDate($row["info_date"])."</td>";
        $html.= "<td colspan='1' class='text-left'><strong>".Yii::t("market","info user")."：</strong>".$row["lcu"]."</td>";
        $html.= "<td colspan='6' class='text-left'>";
        $html.= "<p style='margin: 0px;'><strong>".Yii::t("market","info state")."：</strong>".$row["state_name"]."</p>";
        $html.= "<p style='margin: 0px;'><strong>".Yii::t("market","info text")."：</strong>".$row["info_text"]."</p>";
        $html.= "</td>";
        $html.= "</tr>";
        echo $html;
    }
}
?>
