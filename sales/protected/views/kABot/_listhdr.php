<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('follow_date').$this->drawOrderArrow('a.follow_date'),'#',$this->createOrderLink('code-list','a.follow_date'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('apply_date').$this->drawOrderArrow('a.apply_date'),'#',$this->createOrderLink('code-list','a.apply_date'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('customer_no').$this->drawOrderArrow('a.customer_no'),'#',$this->createOrderLink('code-list','a.customer_no'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('customer_name').$this->drawOrderArrow('a.customer_name'),'#',$this->createOrderLink('code-list','a.customer_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('available_date').$this->drawOrderArrow('a.available_date'),'#',$this->createOrderLink('code-list','a.available_date'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('source_id').$this->drawOrderArrow('f.pro_name'),'#',$this->createOrderLink('code-list','f.pro_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('class_id').$this->drawOrderArrow('b.pro_name'),'#',$this->createOrderLink('code-list','b.pro_name'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('sign_odds').$this->drawOrderArrow('a.sign_odds'),'#',$this->createOrderLink('code-list','a.sign_odds'))
        ;
        ?>
    </th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('link_id').$this->drawOrderArrow('g.rate_num'),'#',$this->createOrderLink('code-list','g.rate_num'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('kam_id').$this->drawOrderArrow('a.kam_id'),'#',$this->createOrderLink('code-list','a.kam_id'))
			;
		?>
	</th>
    <th></th>
</tr>
