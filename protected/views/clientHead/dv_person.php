<?php
/**
 * 联系人
 */
?>
<div  style="padding-top: 15px;">
    <div>
        <?php
        echo TbHtml::button(Yii::t('clue','add person'), array(
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
            'data-load'=>Yii::app()->createUrl('clientPerson/ajaxShow'),
            'data-submit'=>Yii::app()->createUrl('clientPerson/ajaxSave'),
            'data-serialize'=>"ClientPersonForm[scenario]=new&ClientPersonForm[clue_id]=".$model->id,
            'data-obj'=>"#clue_dv_person",
            'class'=>'openDialogForm',
        ));
        ?>
    </div>
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th><?php echo Yii::t('clue',"person code"); ?></th>
                <th><?php echo Yii::t('clue',"person name"); ?></th>
                <th><?php echo Yii::t('clue',"person sex"); ?></th>
                <th><?php echo Yii::t('clue',"person role"); ?></th>
                <th><?php echo Yii::t('clue',"person tel"); ?></th>
                <th><?php echo Yii::t('clue',"person email"); ?></th>
                <th><?php echo Yii::t('clue',"person pws"); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody id="dv_person_body">
            <?php
            $list = CGetName::getClientPersonRows($model->id);
            if($list){
                $html ="";
                $updateBool = Yii::app()->user->validRWFunction('CM10');
                foreach ($list as $row){
                    $html.="<tr>";
                    $html.="<td>".$row["person_code"]."</td>";
                    $html.="<td>".$row["cust_person"]."</td>";
                    $html.="<td>".CGetName::getPersonSexStrByKey($row["sex"])."</td>";
                    $html.="<td>".$row["cust_person_role"]."</td>";
                    $html.="<td>".$row["cust_tel"]."</td>";
                    $html.="<td>".$row["cust_email"]."</td>";
                    $html.="<td>".CGetName::getClientPersonPwsStrByKey($row["person_pws"])."</td>";
                    $html.="<td>";
                    if($updateBool){
                        $html.=TbHtml::link("<span class='glyphicon glyphicon-pencil'></span>",'javascript:void(0);',array(
                            'data-load'=>Yii::app()->createUrl('clientPerson/ajaxShow'),
                            'data-submit'=>Yii::app()->createUrl('clientPerson/ajaxSave'),
                            'data-serialize'=>"ClientPersonForm[scenario]=edit&ClientPersonForm[id]=".$row["id"],
                            'data-obj'=>"#clue_dv_person",
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
