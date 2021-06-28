<?php 
	$labels = $this->model->attributeLabels(); 
	$withrow = count($this->record['detail'])>0;
	$idX = $this->record['company_id'];
?>
<tr>
	<td>
		<?php
			$iconX = $withrow ? "<span id='btn_$idX' class='fa fa-plus-square'></span>" : "<span class='fa fa-square'></span>";
			$lnkX = $withrow ? "javascript:showdetail('$idX');" : '#';
			echo TbHtml::link($iconX, $lnkX);
		?>
	</td>
	<td><?php echo $this->record['city_name']; ?></td>
	<td><?php echo $this->record['company_code']; ?></td>
	<td colspan=4><?php echo $this->record['company_name']; ?></td>
	<td><?php echo $this->record['company_status']; ?></td>
</tr>

<?php

if (count($this->record['detail'])>0) {
	foreach ($this->record['detail'] as $row) {
		$lbl_status_dt = $labels['status_dt'];
		$lbl_status = $labels['status'];
		$lbl_cust_type_desc = $labels['cust_type_desc'];
		$lbl_product_desc = $labels['product_desc'];
		$lbl_first_dt = $labels['first_dt'];
		$lbl_amt_paid = $labels['amt_paid'];

		$fld_status_dt = $row['status_dt'];
		$fld_status = $row['status'];
		$fld_cust_type_desc = $row['cust_type_desc'];
		$fld_product_desc = $row['service'];
		$fld_first_dt = $row['first_dt'];
		$fld_amt_paid = $row['amt_paid'];
		$fld_paid_type = empty($row['paid_type']) ? '' : '('.$row['paid_type'].')';
		
		$line = <<<EOF
<tr class='detail_$idX' style='display:none;'>
	<td colspan=2></td>
	<td><strong>$lbl_status_dt:&nbsp;</strong>$fld_status_dt</td>
	<td><strong>$lbl_status:&nbsp;</strong>$fld_status</td>
	<td><strong>$lbl_cust_type_desc:&nbsp;</strong>$fld_cust_type_desc</td>
	<td><strong>$lbl_product_desc:&nbsp;</strong>$fld_product_desc</td>
	<td><strong>$lbl_first_dt:&nbsp;</strong>$fld_first_dt</td>
	<td><strong>$lbl_amt_paid:&nbsp;</strong>$fld_amt_paid $fld_paid_type</td>
</tr>
EOF;
		echo $line;
	}
}
?>
