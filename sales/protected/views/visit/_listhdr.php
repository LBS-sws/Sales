<tr>
	<th></th>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city_name'),'#',$this->createOrderLink('visit-list','city_name'))
			;
		?>
	</th>
<?php endif ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('visit_dt').$this->drawOrderArrow('visit_dt'),'#',$this->createOrderLink('visit-list','visit_dt'))
			;
		?>
	</th>
<!--
	<th>
		<?php
//			echo TbHtml::link($this->getLabelName('status_dt').$this->drawOrderArrow('status_dt'),'#',$this->createOrderLink('visit-list','status_dt'));
		?>
	</th>
-->
	<th>
		<?php echo TbHtml::link($this->getLabelName('cust_type').$this->drawOrderArrow('cust_type'),'#',$this->createOrderLink('visit-list','cust_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('cust_name').$this->drawOrderArrow('cust_name'),'#',$this->createOrderLink('visit-list','cust_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('visit_type').$this->drawOrderArrow('visit_type'),'#',$this->createOrderLink('visit-list','visit_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('visit_obj').$this->drawOrderArrow('visit_obj'),'#',$this->createOrderLink('visit-list','visit_obj'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('district').$this->drawOrderArrow('district'),'#',$this->createOrderLink('visit-list','district'))
			;
		?>
	</th>
	<th style="pointer-events:none;">
		<?php echo TbHtml::link($this->getLabelName('quote').$this->drawOrderArrow('quote'),'#',$this->createOrderLink('visit-list','quote'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('staff').$this->drawOrderArrow('staff'),'#',$this->createOrderLink('visit-list','staff'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('sign_odds').$this->drawOrderArrow('a.sign_odds'),'#',$this->createOrderLink('visit-list','a.sign_odds'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('visitdoc').$this->drawOrderArrow('visitdoc'),'#',$this->createOrderLink('visit-list','visitdoc'))
        ;
        ?>
    </th>
</tr>
