<tr>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('pro_name'),  $this->record['pro_name'],
								array('disabled'=>$this->model->scenario=='view')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::numberField($this->getFieldName('z_index'),  $this->record['z_index'],
								array('disabled'=>$this->model->scenario=='view')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::dropDownList($this->getFieldName('z_display'),  $this->record['z_display'], array(0=>Yii::t("ka","no"),1=>Yii::t("ka","yes")),
								array('disabled'=>$this->model->scenario=='view')
		); ?>
	</td>
	<td>
		<?php 
			echo Yii::app()->user->validRWFunction('KA09')
				? TbHtml::Button('-',array('id'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('level_id'),$this->record['level_id']); ?>
	</td>
</tr>
