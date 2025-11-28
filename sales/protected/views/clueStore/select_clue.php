
<?php
$submit=Yii::app()->createUrl('clueStore/new');
?>
<form class="form-horizontal" id="selectClueForm" method="get" action="<?php echo $submit;?>">
<?php
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'selectClueFormDialog',
    'header'=>Yii::t('clue','select clue or client'),
    'footer'=>array(
        TbHtml::submitButton(Yii::t('dialog','OK'), array(
            'name'=>'',
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY
        )),
    ),
    'show'=>false,
));
?>
    <div class="form-group">
        <div class="col-lg-12">
            <?php
            echo TbHtml::dropDownList('clue_id','',$model->clueList, array("id"=>"clue_id_select"));
            ?>
        </div>
    </div>

    <?php
    $js="
$('#clue_id_select').select2({
    dropdownParent: $('#selectClueFormDialog'),
    multiple: false,
    maximumInputLength: 10,
    language: 'zh-CN'
});
";
    Yii::app()->clientScript->registerScript('addCityRecordBtn',$js,CClientScript::POS_READY);
    ?>
<?php
$this->endWidget();
?>
</form>

