<tr>
    <th><input type="checkbox" value="" name="chkboxAll" id="chkboxAll"></th>
	<th></th>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city_name'),'#',$this->createOrderLink('shift-list','city_name'))
			;
		?>
	</th>
<?php endif ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('visit_dt').$this->drawOrderArrow('visit_dt'),'#',$this->createOrderLink('shift-list','visit_dt'))
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
		<?php echo TbHtml::link($this->getLabelName('cust_type').$this->drawOrderArrow('cust_type'),'#',$this->createOrderLink('shift-list','cust_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('cust_name').$this->drawOrderArrow('cust_name'),'#',$this->createOrderLink('shift-list','cust_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('visit_type').$this->drawOrderArrow('visit_type'),'#',$this->createOrderLink('shift-list','visit_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('visit_obj').$this->drawOrderArrow('visit_obj'),'#',$this->createOrderLink('shift-list','visit_obj'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('district').$this->drawOrderArrow('district'),'#',$this->createOrderLink('shift-list','district'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('street').$this->drawOrderArrow('street'),'#',$this->createOrderLink('shift-list','street'))
			;
		?>
	</th>
<?php if (VisitForm::isReadAll()) : ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('staff').$this->drawOrderArrow('staff'),'#',$this->createOrderLink('shift-list','staff'))
			;
		?>
	</th>
<?php endif ?>
</tr>
