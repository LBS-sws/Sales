<?php
/**
 * 门店
 */
?>
<div  style="padding-top: 15px;">
    <div>
        <?php
        echo TbHtml::button(Yii::t('clue','add u area'), array(
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
            'data-load'=>Yii::app()->createUrl('clueUArea/ajaxShow'),
            'data-submit'=>Yii::app()->createUrl('clueUArea/ajaxSave'),
            'data-serialize'=>"ClueUAreaForm[scenario]=new&ClueUAreaForm[city]={$model->city}&ClueUAreaForm[clue_id]=".$model->id,
            'data-obj'=>"#clue_dv_u_area",
            'class'=>'openDialogForm',
            //'submit'=>Yii::app()->createUrl('clueUArea/new',array("clue_id"=>$model->id,"type"=>1))
        ));
        ?>
    </div>
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue',"city"); ?></th>
                <th><?php echo Yii::t('clue',"client u area"); ?></th>
                <th><?php echo Yii::t('clue',"u id"); ?></th>
                <th><?php echo Yii::t('clue',"lcu"); ?></th>
                <th><?php echo Yii::t('clue',"luu"); ?></th>
                <th><?php echo Yii::t('clue',"lcd"); ?></th>
                <th><?php echo Yii::t('clue',"lud"); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $list = CGetName::getClueUAreaRows($model->id);
            if($list){
                $html ="";
                $updateBool = Yii::app()->user->validRWFunction('CM10');
                foreach ($list as $row){
                    $city_type = empty($row["city_type"])?Yii::t("clue","other u area"):Yii::t("clue","local u area");
                    $html.="<tr>";
                    $html.="<td>".General::getCityName($row["city_code"])."</td>";
                    $html.="<td>".$city_type."</td>";
                    $html.="<td>".$row["u_id"]."</td>";
                    $html.="<td>".$row["lcu"]."</td>";
                    $html.="<td>".$row["luu"]."</td>";
                    $html.="<td>".$row["lcd"]."</td>";
                    $html.="<td>".$row["lud"]."</td>";
                    $html.="<td>";
                    if($updateBool){
                        $html.=TbHtml::link("<span class='glyphicon glyphicon-pencil'></span>",'javascript:void(0);',array(
                            'data-load'=>Yii::app()->createUrl('clueUArea/ajaxShow'),
                            'data-submit'=>Yii::app()->createUrl('clueUArea/ajaxSave'),
                            'data-serialize'=>"ClueUAreaForm[scenario]=edit&ClueUAreaForm[id]=".$row["id"],
                            'data-obj'=>"#clue_dv_u_area",
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