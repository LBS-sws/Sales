<?php
$this->pageTitle=Yii::app()->name . ' - performance Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'performance-form',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('sales','Sales Performance Form'); ?></strong>
    </h1>
</section>

<section class="content">
    <div class="box"><div class="box-body">
            <div class="btn-group" role="group">
                <?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('report/performance')));
                ?>
<!--                <input class="btn btn-default" type="button" name="Submit" onclick="javascript:history.back(-1);" value="返回">-->
            </div>
            <?php if(!empty($post['sale'])){?>
            <div class="btn-group pull-right" role="group">
                <?php echo TbHtml::button('<span class="fa fa-download"></span> '.Yii::t('misc','Xiazai'), array(
                    'submit'=>Yii::app()->createUrl('report/performancedown')));
                ?>
            </div>
            <?php }?>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-body" style=" overflow-x:auto; overflow-y:auto;">
            <?php
//            echo $form->hiddenField($model, 'scenario');
//            echo $form->hiddenField($model, 'id');
//            echo $form->hiddenField($model, 'city');
//            ?>
            <?php if(!empty($post)){?>
            <input type="text" name="RptFive[city]" value="<?php echo $post['city']?>" style="display:none"/>
            <input type="text" name="RptFive[start_dt]" value="<?php echo $post['start_dt']?>" style="display:none"/>
            <input type="text" name="RptFive[end_dt]" value="<?php echo $post['end_dt']?>" style="display:none"/>
            <input type="text" name="RptFive[sort]" value="<?php echo $post['sort']?>" style="display:none"/>
            <?php if(!empty($post['sale'])){ foreach ($post['sale'] as $v){?>
            <input name="RptFive[sale][]" type="checkbox" value="<?php echo $v ;?>" checked="checked" style="display:none"/>
            <?php }}}?>
            <h3><?php echo $post['start_dt']?>--<?php echo $post['end_dt']?></h3>
            <table class="table table-bordered small" style="text-align: center;">
              <tbody>
                <tr>
                    <td style="width: 9%;"><h4><b><?php echo Yii::t('report','name');?></b></h4></td>
                    <td style="width: 9%;"><h4><b><?php echo Yii::t('report','dept name');?></b></h4></td>
                    <td style="width: 9%;"><h4><b><?php echo Yii::t('report','City');?></b></h4></td>
                    <td style="width: 9%;"><h4><b><?php echo Yii::t('report','office name');?></b></h4></td>
                    <td style="width: 9%;"><h4><b><?php echo Yii::t('report','singular');?></b></h4></td>
                    <td style="width: 9%;"><h4><b><?php echo Yii::t('report','fuwumoney');?></b></h4></td>
                    <?php
                    $classList = CGetName::getSetMenuTypeList("serviceTypeClass");
                    foreach ($classList as $set_id=>$set_name){
                        echo '<td style="background-color: #9acfea;width: 9%;text-align: center;" colspan="2"><h4><b>'.$set_name.'</b></h4></td>';
                    }
                    ?>
                </tr>
                <?php if(!empty($sum)){?>
                <tr >
                    <td colspan="4" ><h4><b><?php echo Yii::t('report','money/sum');?></b></h4></td>  <td><h4><b><?php echo $sum['singular'];?></b></h4></td> <td><h4><b><?php echo $sum['money'];?></b></h4></td>

                    <?php
                    foreach ($classList as $set_id=>$set_name){
                        echo '<td style="background-color: #9acfea"><h4><b>'.$sum["amt_".$set_id].'</b></h4></td><td style="background-color: #9acfea;width: 4%;"><h4>'.$sum["sum_".$set_id].'</b></h4></td>';
                    }
                    ?>
                </tr>
                <?php }?>
                <?php
                if(!empty($array)){
                    foreach ($array as $a) {
                        echo "<tr>";
                        echo '<td>'.$a['names'].'</td> <td>'.$a['dept_name'].'</td> <td>'.$a['cityname'].'</td> <td>'.$a['office_name'].'</td> <td>'.$a['singular'].'</td> <td>'.$a['money'].'</td>';

                        foreach ($classList as $set_id=>$set_name){
                            echo '<td>'.$a["amt_".$set_id].'</td>';
                            echo '<td>'.$a["sum_".$set_id].'</td>';
                        }
                        echo "</tr>";
                    }
                }

                ?>
              </tbody>
            </table>

        </div>
    </div>


</section>



<?php
$js = Script::genDeleteData(Yii::app()->createUrl('visit/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


