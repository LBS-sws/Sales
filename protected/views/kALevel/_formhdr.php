<tr>
    <th>
        <?php echo TbHtml::label($this->getLabelName('class_name'), false); ?>
    </th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('z_index'), false); ?>
	</th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('z_display'), false); ?>
	</th>
	<th>
		<?php echo Yii::app()->user->validRWFunction('KA09') ?
				TbHtml::Button('+',array('id'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
