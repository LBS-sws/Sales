<?php
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'flowinfodialog',
					'header'=>Yii::t('ka','Flow Info'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>

<div class="box" id="flow-list" style="max-height: 300px; overflow-y: auto;">
	<table id="tblFlow" class="table table-bordered table-striped table-hover">
		<thead>
			<tr>
                <th><?php echo Yii::t("ka","Operator User"); ?></th>
                <th><?php echo Yii::t("ka","Operator Time"); ?></th>
                <th><?php echo Yii::t("ka","Operator Text"); ?></th>
			</tr>
		</thead>
		<tbody>

        <?php
        $list = CABotForm::getBotHistoryRows($model->id);
        if($list){
            foreach ($list as $row){
                echo "<tr data-id='{$row['id']}'><td>".$row['lcu']."</td><td>".$row['lcd']."</td><td>".$row['update_html']."</td></tr>";

            }
        }
        ?>
		</tbody>
	</table>
</div>

<?php
	$this->endWidget();
?>
