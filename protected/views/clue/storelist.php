<?php
	$ftrbtn = array();
	if(get_class($model)=="ClueHeadForm"){
        $ftrbtn[] = TbHtml::button(Yii::t('clue','import clue store'), array(
                'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                'data-toggle'=>'modal','data-target'=>'#importClueDialog','data-type'=>'clueStore',"class"=>"pull-left")
        );
    }
	$ftrbtn[] = TbHtml::button(Yii::t('clue','add store'), array(
        'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
        'submit'=>Yii::app()->createUrl('clueStore/new',array("clue_id"=>$model->id,"type"=>1))
    ));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'clueStoreDialog',
					'header'=>Yii::t('clue','store list'),
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
                <th><?php echo Yii::t("clue","store name"); ?></th>
                <th><?php echo Yii::t("clue","store address"); ?></th>
                <th><?php echo Yii::t("clue","customer person"); ?></th>
                <th><?php echo Yii::t("clue","person tel"); ?></th>
                <th><?php echo Yii::t("clue","invoice header"); ?></th>
                <th><?php echo Yii::t("clue","tax id"); ?></th>
                <th><?php echo Yii::t("clue","invoice address"); ?></th>
            </tr>
            </thead>
            <tbody>

            <?php
            $list = CGetName::getClueStoreRows($model->id);
            if($list){
                $html ="";
                $updateBool = Yii::app()->user->validRWFunction('CM02');
                foreach ($list as $row){
                    $html.="<tr>";
                    $html.="<td>";
                    if($updateBool){
                        $html.=TbHtml::link("<span class='glyphicon glyphicon-pencil'></span>",Yii::app()->createUrl('clueStore/edit',array('index'=>$row["id"],'type'=>1)));
                    }else{
                        $html.=TbHtml::link("<span class='glyphicon glyphicon-eye-open'></span>",Yii::app()->createUrl('clueStore/view',array('index'=>$row["id"],'type'=>1)));
                    }
                    $html.="</td>";
                    $html.="<td>".$row["store_name"]."</td>";
                    $html.="<td>".$row["address"]."</td>";
                    $html.="<td>".$row["cust_person"]."</td>";
                    $html.="<td>".$row["cust_tel"]."</td>";
                    $html.="<td>".$row["invoice_header"]."</td>";
                    $html.="<td>".$row["tax_id"]."</td>";
                    $html.="<td>".$row["invoice_address"]."</td>";
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
