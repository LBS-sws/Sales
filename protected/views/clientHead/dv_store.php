<?php
/**
 * 门店
 */
?>
<div  style="padding-top: 15px;">
    <div>
        <?php
        echo TbHtml::button(Yii::t('clue','add store'), array(
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
            'data-load'=>Yii::app()->createUrl('clueStore/ajaxShow'),
            'data-submit'=>Yii::app()->createUrl('clueStore/ajaxSave'),
            'data-serialize'=>"ClueStoreForm[scenario]=new&ClueStoreForm[city]={$model->city}&ClueStoreForm[clue_id]=".$model->id,
            'data-obj'=>"#clue_dv_store",
            'class'=>'openDialogForm',
            //'submit'=>Yii::app()->createUrl('clueStore/new',array("clue_id"=>$model->id,"type"=>1))
        ));
        ?>
    </div>
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue',"store code"); ?></th>
                <th><?php echo Yii::t('clue',"store name"); ?></th>
                <th><?php echo Yii::t('clue',"trade type"); ?></th>
                <th><?php echo Yii::t('clue',"district"); ?></th>
                <th><?php echo Yii::t('clue',"address"); ?></th>
                <th><?php echo Yii::t('clue',"client person"); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $list = CGetName::getClueStoreRows($model->id);
            if($list){
                $html ="";
                $updateBool = Yii::app()->user->validRWFunction('CM10');
                foreach ($list as $row){
                    $person = $row["cust_person"];
                    $person.= !empty($row["cust_person_role"])?" ({$row["cust_person_role"]})":"";
                    $person.= !empty($row["cust_tel"])?" {$row["cust_tel"]}":"";
                    $html.="<tr>";
                    $html.="<td>".$row["store_code"]."</td>";
                    $html.="<td>".$row["store_name"]."</td>";
                    $html.="<td>".CGetName::getCustClassStrByKey($row["cust_class"])."</td>";
                    $html.="<td>".CGetName::getDistrictStrByKey($row["district"])."</td>";
                    $html.="<td>".$row["address"]."</td>";
                    $html.="<td>".$person."</td>";
                    $html.="<td>";
                    if($updateBool){
                        $html.=TbHtml::link("<span class='glyphicon glyphicon-pencil'></span>",'javascript:void(0);',array(
                            'data-load'=>Yii::app()->createUrl('clueStore/ajaxShow'),
                            'data-submit'=>Yii::app()->createUrl('clueStore/ajaxSave'),
                            'data-serialize'=>"ClueStoreForm[scenario]=edit&ClueStoreForm[id]=".$row["id"],
                            'data-obj'=>"#clue_dv_store",
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