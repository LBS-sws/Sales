<?php
$readyBool = $this->model->isReadOnly();
if(!empty($this->record['id'])){
    $readyBool = $this->record['lcu']!=Yii::app()->user->id;
}
//已完成、已拒绝的资料不允许修改
$readyBool = $readyBool||in_array($this->model->status_type,array(8,10));

?>
<tr>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('lcu'), empty($this->record['lcu'])?Yii::app()->user->id:$this->record['lcu'],
								array('readonly'=>true)
		); ?>
	</td>
	<td>
		<?php echo TbHtml::dropDownList($this->getFieldName('state_id'),  $this->record['state_id'],MarketStateForm::getMarketStateList($this->record['state_id']),
								array('readonly'=>$readyBool,'empty'=>'')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('info_date'),  $this->record['info_date'],
								array('readonly'=>$readyBool,'class'=>'info_date','prepend'=>'<span class="fa fa-calendar"></span>')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textArea($this->getFieldName('info_text'),  $this->record['info_text'],
								array('readonly'=>$readyBool,'rows'=>3)
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
