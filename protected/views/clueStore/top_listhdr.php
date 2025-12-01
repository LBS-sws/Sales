<tr>
	<th width="30px"></th>
	<th width="100px">
		<?php echo TbHtml::link($this->getLabelName('store_code').$this->drawOrderArrow('a.store_code'),'#',$this->createOrderLink('code-list','a.store_code'))
			;
		?>
	</th>
	<th width="200px">
		<?php echo TbHtml::link($this->getLabelName('store_name').$this->drawOrderArrow('a.store_name'),'#',$this->createOrderLink('code-list','a.store_name'))
			;
		?>
	</th>
	<th width="200px">
		<?php echo TbHtml::link($this->getLabelName('cust_name').$this->drawOrderArrow('g.cust_name'),'#',$this->createOrderLink('code-list','g.cust_name'))
			;
		?>
	</th>
	<th width="100px">
		<?php echo TbHtml::link($this->getLabelName('yewudalei').$this->drawOrderArrow('a.yewudalei'),'#',$this->createOrderLink('code-list','a.yewudalei'))
			;
		?>
	</th>
	<th width="130px">
		<?php echo TbHtml::link($this->getLabelName('cust_class').$this->drawOrderArrow('h.name'),'#',$this->createOrderLink('code-list','g.name'))
			;
		?>
	</th>
    <th width="100px">
        <?php echo TbHtml::link($this->getLabelName('cust_person').$this->drawOrderArrow('a.cust_person'),'#',$this->createOrderLink('code-list','a.cust_person'))
        ;
        ?>
    </th>
    <th width="100px">
        <?php echo TbHtml::link($this->getLabelName('cust_tel').$this->drawOrderArrow('a.cust_tel'),'#',$this->createOrderLink('code-list','a.cust_tel'))
        ;
        ?>
    </th>
	<th width="100px">
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('code-list','b.name'))
			;
		?>
	</th>
	<th width="70px">
		<?php echo TbHtml::link($this->getLabelName('store_status').$this->drawOrderArrow('a.store_status'),'#',$this->createOrderLink('code-list','a.store_status'))
			;
		?>
	</th>
</tr>
