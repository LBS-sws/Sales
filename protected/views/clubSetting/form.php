<?php
$this->pageTitle=Yii::app()->name . ' - ClubSetting Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'ClubSetting-form',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    .input-group-btn>.changeSelect{ min-width: 150px;}
</style>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('club','club setting form'); ?></strong>
    </h1>
    <!--
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="#">Layout</a></li>
            <li class="active">Top Navigation</li>
        </ol>
    -->
</section>

<section class="content">
    <div class="box"><div class="box-body">
            <div class="btn-group" role="group">
                <?php
                if ($model->scenario!='new' && $model->scenario!='view') {
                    echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
                        'submit'=>Yii::app()->createUrl('clubSetting/new')));
                }
                ?>
                <?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('clubSetting/index')));
                ?>
                <?php if ($model->scenario!='view'): ?>
                    <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
                        'submit'=>Yii::app()->createUrl('clubSetting/save')));
                    ?>
                <?php endif ?>
                <?php if ($model->scenario!='new' && $model->scenario!='view'): ?>
                    <?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
                            'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
                    );
                    ?>
                <?php endif ?>
            </div>
        </div></div>

    <div class="box box-info">
        <div class="box-body">
            <?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo $form->hiddenField($model, 'id'); ?>

            <div class="form-group">
                <?php echo Tbhtml::label(Yii::t("club","count sales"),'',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-5">
                    <p class="form-control-static">
                        <?php
                        $countSales = ClubSettingForm::getSalesCount();
                        echo "<b>{$countSales}人</b>";
                        echo TbHtml::hiddenField("countSales",$countSales,array("id"=>"countSales"));
                        ?>
                    </p>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'explain_text',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-5">
                    <?php echo $form->textField($model, 'explain_text',
                        array('maxlength'=>100,'readonly'=>($model->scenario=='view'))
                    ); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'effect_date',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php
                    echo $form->textField($model, 'effect_date',
                        array('readonly'=>($model->scenario=='view'),'id'=>'effect_date','prepend'=>"<span class='fa fa-calendar'></span>")
                    ); ?>
                </div>
            </div>

            <?php
            echo $model->getSettingHtml();
            ?>
        </div>
    </div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php

$js = "
function ratioPeople(){
console.log(1);
    var number = 0;
    var type = 1;
    var countSales = $('#countSales').val();
    var num='';
    $('.ratioPeople').each(function(){
        number = $(this).find('.forNumber').eq(0).val();
        type = $(this).find('.changeSelect').eq(0).val();
        if(type == 1){
            num = Math.round(countSales*number*0.01);
            $(this).find('.salesNum').eq(0).text(num+'人');
        }else{
            $(this).find('.salesNum').eq(0).text('');
        }
    });
}

$('.changeSelect').on('change',function(){
    if($(this).val()==1){
        $(this).parents('.input-group:first').find('span.input-group-addon').text('%');
    }else{
        $(this).parents('.input-group:first').find('span.input-group-addon').text('人');
    }
    ratioPeople();
});
$('.forNumber').on('change keyup',ratioPeople);
$('.forNumber').eq(0).trigger('change');
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
$js = Script::genDeleteData(Yii::app()->createUrl('clubSetting/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

if ($model->scenario!='view') {
    $js = Script::genDatePicker(array(
        'effect_date',
    ));
    Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


