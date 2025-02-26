<tr>
    <th width="15%">
        <?php echo TbHtml::label($this->getLabelName('info_lcu'), false); ?>
    </th>
    <th width="30%">
        <?php echo TbHtml::label($this->getLabelName('state_id'), false); ?>
    </th>
    <th width="19%">
        <?php echo TbHtml::label($this->getLabelName('info_date'), false); ?>
    </th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('info_text'), false); ?>
	</th>
	<th>
		<?php
        $bool = Yii::app()->user->validRWFunction('MT01')||Yii::app()->user->validRWFunction('MT02')||Yii::app()->user->validRWFunction('MT03');
        //已完成、已拒绝的资料不允许修改
        $bool = $bool&&!in_array($this->model->status_type,array(8,10));
        echo $bool ?
				TbHtml::Button('+',array('class'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
