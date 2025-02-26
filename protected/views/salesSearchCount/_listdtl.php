<?php
$labels = $this->model->attributeLabels();
$withrow = count($this->record['detail'])>0;
$idX = $this->record['id'];
?>
<tr>
    <td>
        <?php
        $iconX = $withrow ? "<span id='btn_$idX' class='fa fa-plus-square'></span>" : "<span class='fa fa-square'></span>";
        $lnkX = $withrow ? "javascript:showdetail('$idX');" : '#';
        echo TbHtml::link($iconX, $lnkX);
        ?>
    </td>
    <td><?php echo $this->record['employee_code']; ?></td>
    <td><?php echo $this->record['employee_name']; ?></td>
    <td><?php echo $this->record['city']; ?></td>
    <td><?php echo $this->record['search_date']; ?></td>
    <td><?php echo $this->record['search_num']; ?></td>
</tr>

<?php

if (count($this->record['detail'])>0) {
    foreach ($this->record['detail'] as $row) {
        $lbl_status_dt = $labels['search_date'];
        $lbl_status = $labels['search_str'];

        $fld_status_dt = $row['data'];
        $fld_status = $row['name'];

        $line = <<<EOF
<tr class='detail_$idX' style='display:none;'>
	<td colspan=2></td>
	<td><strong>$lbl_status_dt:&nbsp;</strong>$fld_status_dt</td>
	<td colspan=3><strong>$lbl_status:&nbsp;</strong>$fld_status</td>
</tr>
EOF;
        echo $line;
    }
}
?>