
<?php
//2024年9月28日09:28:46
?>
<!-- -->
<tr>
	<th>
		<?php echo TbHtml::link($this->getLabelName('id').$this->drawOrderArrow('id'),'#',$this->createOrderLink('curlNotes-list','id'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('info_type').$this->drawOrderArrow('info_type'),'#',$this->createOrderLink('curlNotes-list','info_type'))
			;
		?>
	</th>
	<th width="20%">
		<?php echo TbHtml::link($this->getLabelName('info_url').$this->drawOrderArrow('info_url'),'#',$this->createOrderLink('curlNotes-list','info_url'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('data_content').$this->drawOrderArrow('data_content'),'#',$this->createOrderLink('curlNotes-list','data_content'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('out_content').$this->drawOrderArrow('out_content'),'#',$this->createOrderLink('curlNotes-list','out_content'))
			;
		?>
	</th>
	<th width="15%">
		<?php echo TbHtml::link($this->getLabelName('message').$this->drawOrderArrow('message'),'#',$this->createOrderLink('curlNotes-list','message'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('lcu').$this->drawOrderArrow('lcu'),'#',$this->createOrderLink('curlNotes-list','lcu'))
			;
		?>
	</th>
	<th width="10%">
		<?php echo TbHtml::link($this->getLabelName('lcd').$this->drawOrderArrow('lcd'),'#',$this->createOrderLink('curlNotes-list','lcd'))
			;
		?>
	</th>
	<th width="10%">
		<?php echo TbHtml::link($this->getLabelName('lud').$this->drawOrderArrow('lud'),'#',$this->createOrderLink('curlNotes-list','lud'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status_type').$this->drawOrderArrow('status_type'),'#',$this->createOrderLink('curlNotes-list','status_type'))
			;
		?>
	</th>
    <th></th>
</tr>
