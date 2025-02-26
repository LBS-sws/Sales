<tr>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('renewal_date'),  $this->record['renewal_date'],
								array('disabled'=>$this->model->scenario=='view','autocomplete'=>'off','class'=>'renewal_date','autocomplete'=>'off','prepend'=>'<span class="fa fa-calendar"></span>')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::numberField($this->getFieldName('renewal_num'),  $this->record['renewal_num'],
								array('disabled'=>$this->model->scenario=='view','class'=>'changeRenewalNum','autocomplete'=>'off')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('renewal_city'),  $this->record['renewal_city'],
								array('disabled'=>$this->model->scenario=='view','autocomplete'=>'off')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::numberField($this->getFieldName('renewal_amt'),  $this->record['renewal_amt'],
								array('disabled'=>$this->model->scenario=='view','min'=>0,'autocomplete'=>'off','class'=>'changeRenewalAmt')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textArea($this->getFieldName('renewal_note'),  $this->record['renewal_note'],
								array('disabled'=>$this->model->scenario=='view','rows'=>3,'autocomplete'=>'off')
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
