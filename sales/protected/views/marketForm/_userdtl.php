<?php
$readyBool = $this->model->isReadOnly();
?>
<tr>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('user_name'), $this->record['user_name'],
								array('readonly'=>$readyBool)
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('user_dept'), $this->record['user_dept'],
								array('readonly'=>$readyBool)
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('user_phone'), $this->record['user_phone'],
								array('readonly'=>$readyBool,'class'=>'user_phone')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('user_email'), $this->record['user_email'],
								array('readonly'=>$readyBool)
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('user_wechat'), $this->record['user_wechat'],
								array('readonly'=>$readyBool,'class'=>'user_wechat')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textArea($this->getFieldName('user_text'),  $this->record['user_text'],
								array('readonly'=>$readyBool,'rows'=>2)
		); ?>
	</td>
	<td>
		<?php
        $bool = Yii::app()->user->validRWFunction('MT01')||Yii::app()->user->validRWFunction('MT02')||Yii::app()->user->validRWFunction('MT03');

        echo $bool&&!$readyBool
				? TbHtml::Button('-',array('id'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('market_id'),$this->record['market_id']); ?>
	</td>
</tr>
