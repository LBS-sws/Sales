<tr>
    <th width="14%">
        <?php echo TbHtml::label($this->getLabelName('renewal_date'), false); ?>
    </th>
	<th width="10%">
		<?php echo TbHtml::label($this->getLabelName('renewal_num'), false); ?>
	</th>
	<th width="10%">
		<?php echo TbHtml::label($this->getLabelName('renewal_city'), false); ?>
	</th>
	<th width="12%">
		<?php echo TbHtml::label($this->getLabelName('renewal_amt'), false); ?>
	</th>
	<th width="23%">
		<?php echo TbHtml::label($this->getLabelName('ava_note'), false); ?>
	</th>
	<th width="1%">
		<?php echo Yii::app()->user->validRWFunction('KA01') ?
				TbHtml::Button('+',array('class'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
