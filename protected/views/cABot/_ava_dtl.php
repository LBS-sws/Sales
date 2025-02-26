<tr>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('ava_date'),  $this->record['ava_date'],
								array('disabled'=>$this->model->scenario=='view','autocomplete'=>'off','class'=>'ava_date','autocomplete'=>'off','prepend'=>'<span class="fa fa-calendar"></span>')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::numberField($this->getFieldName('ava_amt'),  $this->record['ava_amt'],
								array('disabled'=>$this->model->scenario=='view','autocomplete'=>'off','min'=>0)
		); ?>
	</td>
	<td>
		<?php echo TbHtml::numberField($this->getFieldName('ava_num'),  $this->record['ava_num'],
								array('disabled'=>$this->model->scenario=='view','class'=>'change_ava_num','autocomplete'=>'off')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('ava_city'),  $this->record['ava_city'],
								array('disabled'=>$this->model->scenario=='view','autocomplete'=>'off')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::dropDownList($this->getFieldName('ava_rate'),  $this->record['ava_rate'],CABotForm::getAvaRateListForId(),
								array('disabled'=>$this->model->scenario=='view','autocomplete'=>'off')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::numberField($this->getFieldName('ava_fact_amt'),  $this->record['ava_fact_amt'],
								array('disabled'=>$this->model->scenario=='view','min'=>0,'autocomplete'=>'off','class'=>'changeSumAmt')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textArea($this->getFieldName('ava_note'),  $this->record['ava_note'],
								array('disabled'=>$this->model->scenario=='view','rows'=>3,'autocomplete'=>'off')
		); ?>
	</td>
	<td>
		<?php 
			echo Yii::app()->user->validRWFunction('CA01')
				? TbHtml::Button('-',array('class'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('bot_id'),$this->record['bot_id']); ?>
	</td>
</tr>
