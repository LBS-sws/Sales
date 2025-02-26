<tr>
    <th width="16%">
        <?php echo TbHtml::label($this->getLabelName('user_name'), false); ?>
    </th>
    <th width="16%">
        <?php echo TbHtml::label($this->getLabelName('user_dept'), false); ?>
    </th>
    <th width="16%">
        <?php echo TbHtml::label($this->getLabelName('user_phone'), false); ?>
    </th>
	<th width="16%">
		<?php echo TbHtml::label($this->getLabelName('user_email'), false); ?>
	</th>
	<th width="16%">
		<?php echo TbHtml::label($this->getLabelName('user_wechat'), false); ?>
	</th>
	<th width="16%">
		<?php echo TbHtml::label($this->getLabelName('user_text'), false); ?>
	</th>
	<th>
		<?php
        $bool = Yii::app()->user->validRWFunction('MT01')||Yii::app()->user->validRWFunction('MT02')||Yii::app()->user->validRWFunction('MT03');
        echo $bool&&!$this->model->isReadOnly() ?
				TbHtml::Button('+',array('class'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
