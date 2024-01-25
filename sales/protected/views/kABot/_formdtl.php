<tr>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('info_date'),  $this->record['info_date'],
								array('disabled'=>$this->model->scenario=='view','class'=>'info_date','prepend'=>'<span class="fa fa-calendar"></span>')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textArea($this->getFieldName('info_text'),  $this->record['info_text'],
								array('disabled'=>$this->model->scenario=='view','rows'=>3)
		); ?>
	</td>
	<td>
		<?php 
			echo Yii::app()->user->validRWFunction('KA01')
				? TbHtml::Button('-',array('class'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('bot_id'),$this->record['bot_id']); ?>
	</td>
</tr>
