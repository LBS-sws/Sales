<?php
    $type=isset($type)?$type:1;
    switch ($type){
        case 1://线索记录
            $header=Yii::t('clue','Clue History');
            break;
        case 2://门店记录
            $header=Yii::t('clue','Clue Store History');
            break;
        case 3://报价记录
            $header=Yii::t('clue','Clue Rpt History');
            break;
        case 4://税号记录
            $header=Yii::t('clue','Clue Invoice History');
            break;
        case 5://合约记录
            $header=Yii::t('clue','Clue Con History');
            break;
        default:
            $header=Yii::t('clue','Clue History');
    }
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'clueHistoryDialog',
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
            </tr>
            </thead>
            <tbody>

            <?php
            $list = CGetName::getClueHistoryRows($model->id,$type);
            if($list){
                foreach ($list as $row){
                    echo "<tr><td>".$row['lcu']."</td><td>".$row['lcd']."</td><td>".$row['history_html']."</td></tr>";
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<?php
	$this->endWidget();
?>
