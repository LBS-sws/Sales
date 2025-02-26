<tr>
    <td>
        <?php echo TbHtml::dropDownList($this->getFieldName('name'),  $this->record['name'],
            array(
                '0'=>'销售每月平均每天拜访记录',
                '1'=>'销售每月IA，IB签单金额',
                '2'=>'销售每月飘盈香签单金额',
                '3'=>'销售每月产品（不包括洗地易）签单金额',
                '4'=>'销售每月洗地易签单金额',
                '6'=>'销售每月甲醛签单金额',
                '5'=>'地方销售人员/整体区比例 ',
                '7'=>'城市规模级别 ',
            ),
            array('disabled'=>$this->model->isReadOnly())
        ); ?>
    </td>
	<td>
		<?php echo TbHtml::dropDownList($this->getFieldName('operator'),  $this->record['operator'], array('LE'=>'<=','GT'=>'>'),
								array('disabled'=>$this->model->isReadOnly())
		); ?>
	</td>
	<td>
		<?php  
			echo TbHtml::numberField($this->getFieldName('criterion'), $this->record['criterion'],
							array('size'=>10,'min'=>0,
							'readonly'=>($this->model->isReadOnly()),
							)
						);
		?>
	</td>
	<td>
		<?php  
			echo TbHtml::numberField($this->getFieldName('bonus'), $this->record['bonus'],
							array('size'=>5,'min'=>0,
							'readonly'=>($this->model->isReadOnly()),
							)
						);
		?>
	</td>
	<td>
		<?php
			echo TbHtml::numberField($this->getFieldName('coefficient'), $this->record['coefficient'],
							array('size'=>5,'min'=>0,
							'readonly'=>($this->model->isReadOnly()),
							)
						);
		?>
	</td>
	<td>
		<?php 
			echo !$this->model->isReadOnly() 
				? TbHtml::Button('-',array('id'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('hdr_id'),$this->record['hdr_id']); ?>
	</td>
</tr>
