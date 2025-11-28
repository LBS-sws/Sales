<?php
/**
 * 门店
 */
?>
<div  style="padding-top: 15px;">
    <div>
        <?php
        echo TbHtml::button(Yii::t('clue','add u staff'), array(
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
            'data-load'=>Yii::app()->createUrl('clueUStaff/ajaxShow'),
            'data-submit'=>Yii::app()->createUrl('clueUStaff/ajaxSave'),
            'data-serialize'=>"ClueUStaffForm[scenario]=new&ClueUStaffForm[city]={$model->city}&ClueUStaffForm[clue_id]=".$model->id,
            'data-obj'=>"#clue_dv_u_staff",
            'class'=>'openDialogForm',
            //'submit'=>Yii::app()->createUrl('clueUStaff/new',array("clue_id"=>$model->id,"type"=>1))
        ));
        ?>
    </div>
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue',"staff"); ?></th>
                <th><?php echo Yii::t('clue',"client u staff"); ?></th>
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
            $list = CGetName::getClueUStaffRows($model->id);
            if($list){
                $html ="";
                $updateBool = Yii::app()->user->validRWFunction('CM10');
                foreach ($list as $row){
                    $employee_type = empty($row["employee_type"])?Yii::t("clue","other u staff"):Yii::t("clue","local u staff");
                    $html.="<tr>";
                    $html.="<td>".CGetName::getEmployeeNameByKey($row["employee_id"])."</td>";
                    $html.="<td>".$employee_type."</td>";
                    $html.="<td>".$row["u_id"]."</td>";
                    $html.="<td>".$row["lcu"]."</td>";
                    $html.="<td>".$row["luu"]."</td>";
                    $html.="<td>".$row["lcd"]."</td>";
                    $html.="<td>".$row["lud"]."</td>";
                    $html.="<td>";
                    if($updateBool){
                        $html.=TbHtml::link("<span class='glyphicon glyphicon-pencil'></span>",'javascript:void(0);',array(
                            'data-load'=>Yii::app()->createUrl('clueUStaff/ajaxShow'),
                            'data-submit'=>Yii::app()->createUrl('clueUStaff/ajaxSave'),
                            'data-serialize'=>"ClueUStaffForm[scenario]=edit&ClueUStaffForm[id]=".$row["id"],
                            'data-obj'=>"#clue_dv_u_staff",
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