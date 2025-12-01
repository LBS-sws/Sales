<?php
/**
 * 开票信息
 */
?>

<div  style="padding-top: 15px;">
    <div>
        <?php
        echo TbHtml::button(Yii::t('clue','add invoice'), array(
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
            'data-load'=>Yii::app()->createUrl('clueInvoice/ajaxShow'),
            'data-submit'=>Yii::app()->createUrl('clueInvoice/ajaxSave'),
            'data-serialize'=>"ClueInvoiceForm[scenario]=new&ClueInvoiceForm[clue_id]=".$model->id,
            'data-obj'=>"#clue_dv_invoice",
            'class'=>'openDialogForm',
        ));
        ?>
    </div>
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue',"invoice name"); ?></th>
                <th><?php echo Yii::t('clue',"invoice type"); ?></th>
                <th><?php echo Yii::t('clue',"invoice header"); ?></th>
                <th><?php echo Yii::t('clue',"tax id"); ?></th>
                <th><?php echo Yii::t('clue',"invoice address"); ?></th>
                <th><?php echo Yii::t('clue',"invoice number"); ?></th>
                <th><?php echo Yii::t('clue',"invoice user"); ?></th>
                <th><?php echo Yii::t('clue',"z display"); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $list = CGetName::getClueInvoiceRows($model->id);
            if($list){
                $html ="";
                $updateBool = Yii::app()->user->validRWFunction('CM10');
                foreach ($list as $row){
                    $html.="<tr>";
                    $html.="<td>".$row["invoice_name"]."</td>";
                    $html.="<td>".CGetName::getInvoiceTypeStrByKey($row["invoice_type"])."</td>";
                    $html.="<td>".$row["invoice_header"]."</td>";
                    $html.="<td>".$row["tax_id"]."</td>";
                    $html.="<td>".$row["invoice_address"]."</td>";
                    $html.="<td>".$row["invoice_number"]."</td>";
                    $html.="<td>".$row["invoice_user"]."</td>";
                    $html.="<td>".CGetName::getDisplayStrByKey($row["z_display"])."</td>";
                    $html.="<td>";
                    if($updateBool){
                        $html.=TbHtml::link("<span class='glyphicon glyphicon-pencil'></span>",'javascript:void(0);',array(
                            'data-load'=>Yii::app()->createUrl('clueInvoice/ajaxShow'),
                            'data-submit'=>Yii::app()->createUrl('clueInvoice/ajaxSave'),
                            'data-serialize'=>"ClueInvoiceForm[scenario]=edit&ClueInvoiceForm[id]=".$row["id"],
                            'data-obj'=>"#clue_dv_invoice",
                            'class'=>'openDialogForm',
                        ));
                    }
                    $html.="</td>";
                    $html.="</tr>";
                }
                echo $html;
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
