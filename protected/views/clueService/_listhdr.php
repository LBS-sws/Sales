<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('id').$this->drawOrderArrow('a.id'),'#',$this->createOrderLink('code-list','a.id'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('clue_code').$this->drawOrderArrow('a.clue_code'),'#',$this->createOrderLink('code-list','a.clue_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('cust_name').$this->drawOrderArrow('a.cust_name'),'#',$this->createOrderLink('code-list','a.cust_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('code-list','b.name'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('visit_obj_text').$this->drawOrderArrow('service.visit_obj_text'),'#',$this->createOrderLink('code-list','service.visit_obj_text'))
        ;
        ?>
    </th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('clue_type').$this->drawOrderArrow('a.clue_type'),'#',$this->createOrderLink('code-list','a.clue_type'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('predict_amt').$this->drawOrderArrow('service.predict_amt'),'#',$this->createOrderLink('code-list','service.predict_amt'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('predict_date').$this->drawOrderArrow('service.predict_date'),'#',$this->createOrderLink('code-list','service.predict_date'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('sign_odds').$this->drawOrderArrow('service.sign_odds'),'#',$this->createOrderLink('code-list','service.sign_odds'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('busine_id_text').$this->drawOrderArrow('service.busine_id_text'),'#',$this->createOrderLink('code-list','service.busine_id_text'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('service_status').$this->drawOrderArrow('service.service_status'),'#',$this->createOrderLink('code-list','service.service_status'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('create_staff').$this->drawOrderArrow('service.create_staff'),'#',$this->createOrderLink('code-list','service.create_staff'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('lcd').$this->drawOrderArrow('service.lcd'),'#',$this->createOrderLink('code-list','service.lcd'))
        ;
        ?>
    </th>
</tr>
