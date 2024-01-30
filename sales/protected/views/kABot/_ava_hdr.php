<tr>
    <th>
        <?php echo TbHtml::label($this->getLabelName('ava_date'), false); ?>
    </th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('ava_amt'), false); ?>
	</th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('ava_fact_amt'), false); ?>
	</th>
	<th>
		<?php echo Yii::app()->user->validRWFunction('KA01') ?
				TbHtml::Button('+',array('class'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
