
<?php
//2024年9月28日09:28:46
?>
<!-- -->
<tr>
	<th>
		<?php echo TbHtml::link($this->getLabelName('id').$this->drawOrderArrow('id'),'#',$this->createOrderLink('curlReceive-list','id'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('info_type').$this->drawOrderArrow('info_type'),'#',$this->createOrderLink('curlReceive-list','info_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('data_content').$this->drawOrderArrow('data_content'),'#',$this->createOrderLink('curlReceive-list','data_content'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('out_content').$this->drawOrderArrow('out_content'),'#',$this->createOrderLink('curlReceive-list','out_content'))
			;
		?>
	</th>
	<th width="15%">
		<?php echo TbHtml::link($this->getLabelName('message').$this->drawOrderArrow('message'),'#',$this->createOrderLink('curlReceive-list','message'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('lcu').$this->drawOrderArrow('lcu'),'#',$this->createOrderLink('curlReceive-list','lcu'))
			;
		?>
	</th>
	<th width="10%">
		<?php echo TbHtml::link($this->getLabelName('lcd').$this->drawOrderArrow('lcd'),'#',$this->createOrderLink('curlReceive-list','lcd'))
			;
		?>
	</th>
	<th width="10%">
		<?php echo TbHtml::link($this->getLabelName('lud').$this->drawOrderArrow('lud'),'#',$this->createOrderLink('curlReceive-list','lud'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status_type').$this->drawOrderArrow('status_type'),'#',$this->createOrderLink('curlReceive-list','status_type'))
			;
		?>
	</th>
    <th></th>
</tr>
