<?php
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('clue','add invoice'), array(
        'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
        'submit'=>Yii::app()->createUrl('clueInvoice/new',array("clue_id"=>$model->id,"type"=>1))
    ));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'clueInvoiceDialog',
					'header'=>Yii::t('clue','invoice list'),
					'footer'=>$ftrbtn,
					'show'=>false,
					'size'=>" modal-lg",
				));
?>

<div class="box" style="max-height: 300px; overflow-y: auto;">
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th></th>
                <th><?php echo Yii::t("clue","invoice name"); ?></th>
                <th><?php echo Yii::t("clue","invoice type"); ?></th>
                <th><?php echo Yii::t("clue","invoice header"); ?></th>
                <th><?php echo Yii::t("clue","tax id"); ?></th>
                <th><?php echo Yii::t("clue","invoice address"); ?></th>
                <th><?php echo Yii::t("clue","z display"); ?></th>
            </tr>
            </thead>
            <tbody>

            <?php
            $list = CGetName::getClueInvoiceRows($model->id);
            if($list){
                $html ="";
                $updateBool = Yii::app()->user->validRWFunction('CM02');
                foreach ($list as $row){
                    $html.="<tr>";
                    $html.="<td>";
                    if($updateBool){
                        $html.=TbHtml::link("<span class='glyphicon glyphicon-pencil'></span>",Yii::app()->createUrl('clueInvoice/edit',array('index'=>$row["id"],'type'=>1)));
                    }else{
                        $html.=TbHtml::link("<span class='glyphicon glyphicon-eye-open'></span>",Yii::app()->createUrl('clueInvoice/view',array('index'=>$row["id"],'type'=>1)));
                    }
                    $html.="</td>";
                    $html.="<td>".$row["invoice_name"]."</td>";
                    $html.="<td>".CGetName::getInvoiceTypeStrByKey($row["invoice_type"])."</td>";
                    $html.="<td>".$row["invoice_header"]."</td>";
                    $html.="<td>".$row["tax_id"]."</td>";
                    $html.="<td>".$row["invoice_address"]."</td>";
                    $html.="<td>".CGetName::getDisplayStrByKey($row["z_display"])."</td>";
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
