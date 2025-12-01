<?php
$this->pageTitle=Yii::app()->name . ' - Contract Update List';
?>
<style>
    .table-fixed { table-layout: fixed;}
</style>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'code-list',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('app','Contract Update List'); ?></strong>
    </h1>
</section>

<section class="content">
    <?php
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('app','Contract Update List'),
        'model'=>$model,
        'viewhdr'=>'//contPro/_listhdr',
        'viewdtl'=>'//contPro/_listdtl',
        'advancedSearch'=>true,
        'tableClass'=>"table table-hover table-fixed table-condensed",
    ));
    ?>
</section>
<?php
echo $form->hiddenField($model,'pageNum');
echo $form->hiddenField($model,'totalRow');
echo $form->hiddenField($model,'orderField');
echo $form->hiddenField($model,'orderType');
echo $form->hiddenField($model,'filter');
echo $form->hiddenField($model,'flow_odds');
?>
<?php $this->endWidget(); ?>

<?php

$url = Yii::app()->createUrl('contPro/index',array("pageNum"=>1));
$js = "
    $('.btn_submit').on('click',function(){
        var key=$(this).data('key');
        $(\"#ContProList_orderField\").val(\"\");
        $(\"#ContProList_flow_odds\").val(key);
        jQuery.yii.submitForm(this,'{$url}',{});
    });
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

$js = Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
<?php
echo TbHtml::button("",array("submit"=>"","class"=>"hide"));
$this->renderPartial('//clue/select_clue',array("actionUrl"=>Yii::app()->createUrl('contPro/new')));
?>
