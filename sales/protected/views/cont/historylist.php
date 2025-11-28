<?php
    $type=isset($type)?$type:1;
    switch ($type){
        case 5://合约记录
            $header=Yii::t('clue','Contract History');
            break;
        default:
            $header=Yii::t('clue','Contract History');
    }
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'contHistoryDialog',
					'header'=>$header,
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>

<div class="box" style="max-height: 300px; overflow-y: auto;">
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t("clue","Operator User"); ?></th>
                <th><?php echo Yii::t("clue","Operator Time"); ?></th>
                <th><?php echo Yii::t("clue","Operator Text"); ?></th>
                <th width="1%">&nbsp;</th>
            </tr>
            </thead>
            <tbody>

            <?php
            $list = CGetName::getContractHistoryRows($model->id,$type);
            if($list){
                $html="";
                foreach ($list as $row){
                    $html.="<tr>";
                    $html.="<td>".$row['lcu']."</td>";
                    $html.="<td>".$row['lcd']."</td>";
                    $html.="<td>".$row['history_html']."</td>";
                    $html.="<td>&nbsp;</td>";
                    $html.="</tr>";
                }
                echo $html;
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<?php
	$this->endWidget();
?>
