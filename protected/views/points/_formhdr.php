<tr>
    <th>
        <?php echo TbHtml::label($this->getLabelName('cust_type_name'), false); ?>
    </th>
    <th>
        <?php echo TbHtml::label($this->getLabelName('conditions'), false); ?>
    </th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('fraction'), false); ?>
	</th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('toplimit'), false); ?>
	</th>

	<th>
<!--		--><?php echo // Yii::app()->user->validRWFunction('XS03') ?
				TbHtml::Button('+',array('id'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL));
//				: '&nbsp;';
		?>
	</th>
</tr>
