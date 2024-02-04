<tr>
    <th>
        <?php echo TbHtml::label($this->getLabelName('ava_date'), false); ?>
    </th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('ava_amt'), false); ?>
	</th>
	<th width="13%">
		<?php echo TbHtml::label($this->getLabelName('ava_num'), false); ?>
	</th>
	<th width="13%">
		<?php echo TbHtml::label($this->getLabelName('ava_city'), false); ?>
	</th>
	<th width="20%">
		<?php echo TbHtml::label($this->getLabelName('ava_rate'), false); ?>
	</th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('ava_fact_amt'), false); ?>
	</th>
	<th width="1%">
		<?php echo Yii::app()->user->validRWFunction('KA01') ?
				TbHtml::Button('+',array('class'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
