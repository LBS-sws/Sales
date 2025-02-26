<?php
$this->pageTitle=Yii::app()->name . ' - Redeem';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'Redeem-list',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('redeem','Redeem List'); ?></strong>
    </h1>
</section>
    <!--
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="#">Layout</a></li>
            <li class="active">Top Navigation</li>
        </ol>
    -->


<section class="content">
    <div class="box">
        <div class="box-body">
            <?php
            if (Yii::app()->user->validRWFunction('HC08'))
                echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
                    'submit'=>Yii::app()->createUrl('redeemsetting/new'),
                ));
            ?>
            <?php
            $listArrIntegral = RedeemGifts::getNowIntegral();
//            echo  '<div class="btn-group pull-left text-right"><span class="text-success">'.date("Y")."年".Yii::t('redeem','Sum Gift')."：".$listArrIntegral["sum"]."</span></div>";
            echo  '<div class="btn-group pull-right text-right"><span class="text-success">'."可用".Yii::t('redeem','Sum Gift')."：".$listArrIntegral["cut"]."</span></div>";

            ?>
        </div>
    </div>
    <?php $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('redeem','Gift List'),
        'model'=>$model,
        'viewhdr'=>'//redeemsetting/_listhdr',
        'viewdtl'=>'//redeemsetting/_listdtl',
        'search'=>array(
            'gift_name',
            'bonus_point',
            'city_name',
        ),
    ));
    ?>
</section>
<?php
echo $form->hiddenField($model,'pageNum');
echo $form->hiddenField($model,'totalRow');
echo $form->hiddenField($model,'orderField');
echo $form->hiddenField($model,'orderType');
?>
<?php $this->endWidget(); ?>


<!--<form class="form-horizontal MultiFile-intercepted" action="" method="post">-->
<!--    --><?php //$this->renderPartial('//site/redeemApply',array(
//        'submit'=> Yii::app()->createUrl('redeemsetting/apply'),
//    ));
//    ?>
<!--</form>-->
<?php
$js = Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>

