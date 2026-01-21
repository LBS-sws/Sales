
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
	<td><?php echo !empty($this->record['username']) ? $this->record['username'] : '-'; ?></td>
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
	<td>
		<?php
			// 添加删除按钮（有XF01权限即可）
			// 调试：显示权限检查结果
			$hasPermission = Yii::app()->user->validFunction('XF01');
			// echo "<!-- 权限检查结果: " . ($hasPermission ? '有权限' : '无权限') . " -->";
			
			if ($hasPermission) {
				echo TbHtml::button('<i class="fa fa-trash"></i> 删除', array(
					'class' => 'btn btn-danger btn-xs btn-remove',
					'data-toggle' => 'modal',
					'data-target' => '#removeIqueueDialog',
					'data-id' => $this->record['id'],
					'data-name' => $this->record['import_name'],
					'data-type' => $this->record['import_type'],
					'data-username' => $this->record['username'],
					'data-req-dt' => $this->record['req_dt'],
					'data-fin-dt' => $this->record['fin_dt'],
					'data-success-num' => $this->record['success_num'],
					'data-error-num' => $this->record['error_num'],
					'data-status' => $this->record['status'],
					'data-message' => isset($this->record['message']) ? $this->record['message'] : '',
				));
			} else {
				// 如果没有权限，显示一个提示
				echo '<span class="text-muted" style="font-size: 12px;">无删除权限</span>';
			}
		?>
	</td>
</tr>
