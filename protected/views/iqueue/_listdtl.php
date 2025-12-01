
<tr>
	<td><?php echo $this->record['id']; ?></td>
	<td>
        <?php
        echo TbHtml::link($this->record['import_name'],Yii::app()->createUrl('iqueue/downExcel',array(
            "index"=>$this->record['id'],
            "type"=>"file"
        )),array("target"=>"_blank"));
        ?>
    </td>
	<td><?php echo $this->record['import_type']; ?></td>
	<td><?php echo $this->record['req_dt']; ?></td>
	<td><?php echo $this->record['fin_dt']; ?></td>
	<td><?php echo $this->record['message']; ?></td>
	<td><?php echo $this->record['success_num']; ?></td>
	<td><?php echo $this->record['error_num']; ?></td>
    <td><?php echo $this->record['status']; ?></td>
	<td>
		<?php
			if (!empty($this->record['error_num'])){
                echo TbHtml::link("下载",Yii::app()->createUrl('iqueue/downExcel',array(
                    "index"=>$this->record['id'],
                    "type"=>"error"
                )),array("class"=>"btn btn-primary","target"=>"_blank"));
            }
		?>
	</td>
</tr>
