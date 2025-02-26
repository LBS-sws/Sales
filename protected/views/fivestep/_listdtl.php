<?php
	$cls_str = "class='clickable-row' data-href='"
		.$this->getLink('HK03', 'fivestep/edit', 'fivestep/view', array('index'=>$this->record['id']))
		."'";
?>
<tr>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<td <?php echo $cls_str;?>><?php echo $this->record['city_name']; ?></td>
<?php endif ?>
	<td <?php echo $cls_str;?>><?php echo $this->record['rec_dt']; ?></td>
	<td <?php echo $cls_str;?>><?php echo $this->record['staff_code']; ?></td>
	<td <?php echo $cls_str;?>><?php echo $this->record['staff_name']; ?></td>
	<td <?php echo $cls_str;?>><?php echo $this->record['step']; ?></td>
	<td <?php echo $cls_str;?>><?php if($this->record['sup_score']==-1){echo '要求重做';}else{echo $this->record['sup_score'];} ?></td>
    <td <?php echo $cls_str;?>><?php if($this->record['mgr_score']==-1){echo '要求重做';}else{echo $this->record['mgr_score'];} ?></td>
    <td <?php echo $cls_str;?>><?php if($this->record['dir_score']==-1){echo '要求重做';}else{echo $this->record['dir_score'];} ?></td>

	<td>
		<?php 
			echo TbHtml::button('<span class="fa fa-paperclip"></span>', 
				array(
					'class'=>'btn-xs',
					'onclick'=>'javascript:showmedia('.$this->record['id'].');',
				)
			);
		?>
	</td>
</tr>
