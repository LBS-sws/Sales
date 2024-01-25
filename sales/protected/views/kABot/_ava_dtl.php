<tr>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('ava_date'),  $this->record['ava_date'],
								array('disabled'=>$this->model->scenario=='view','class'=>'ava_date','prepend'=>'<span class="fa fa-calendar"></span>')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::numberField($this->getFieldName('ava_amt'),  $this->record['ava_amt'],
								array('disabled'=>$this->model->scenario=='view','min'=>0)
		); ?>
	</td>
	<td>
		<?php echo TbHtml::numberField($this->getFieldName('ava_rate'),  $this->record['ava_rate'],
								array('disabled'=>$this->model->scenario=='view','min'=>0,'append'=>"%")
		); ?>
	</td>
	<td>
		<?php echo TbHtml::numberField($this->getFieldName('ava_fact_amt'),  $this->record['ava_fact_amt'],
								array('disabled'=>$this->model->scenario=='view','min'=>0)
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
