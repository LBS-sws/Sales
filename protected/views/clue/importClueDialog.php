<?php
$title="";
$code=isset($code)?$code:"";
$importType=empty($importType)?"clueBox":$importType;
switch ($importType){
    case "clueBox":
        $title = Yii::t("clue","import clue box");
        $actionUrl = Yii::app()->createUrl('import/clueSubmit',array("type"=>"clueBox"));
        break;
    case "clue":
        $title = Yii::t("clue","import clue head");
        $actionUrl = Yii::app()->createUrl('import/clueSubmit',array("type"=>"clueHead"));
        break;
    case "clueStore":
        $title = Yii::t("clue","import clue store");
        $actionUrl = Yii::app()->createUrl('import/clueSubmit',array("type"=>"clueStore"));
        break;
    default:
        $importType="";
        $title="";
        $actionUrl="";
}
$model = new ImportForm('new');
$model->import_type=$importType;
$form=$this->beginWidget('TbActiveForm', array(
    'id'=>'import-form',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
    'htmlOptions'=>array('enctype'=>'multipart/form-data'),
));
?>
<?php
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'importClueDialog',
    'header'=>$title,
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal')),
        TbHtml::button(Yii::t('dialog','OK'), array(
            'id'=>"import-form-btn-ok",
            'name'=>"import-form-btn-ok",
            'submit'=>$actionUrl,
            'color'=>TbHtml::BUTTON_COLOR_PRIMARY
        )),
    ),
    'show'=>false,
    'size'=>" modal-lg",
));
?>
<div>
    <div class="form-group">
        <?php echo $form->labelEx($model,'import_type',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-3">
            <?php
            echo $form->hiddenField($model,'import_type',array("id"=>"import_type"));
            echo TbHtml::textField("import_type",$title,array("readonly"=>true,"id"=>"import_type_name"));
            ?>
        </div>
        <div class="col-lg-7">
            <p class="form-control-static">
                <?php
                echo TbHtml::link("下载导入模板","#",array("id"=>"downExcelLink"));
                ?>
            </p>
        </div>
    </div>

    <div class="form-group">
        <?php echo $form->labelEx($model,'import_file',array('class'=>"col-lg-2 control-label")); ?>
        <div class="col-lg-4">
            <?php echo $form->fileField($model, 'import_file',array("class"=>"form-control")); ?>
        </div>
        <div class="col-lg-6">
            <p class="form-control-static text-danger">* 必须是xlsx格式的文件，且文件大小不能大于5M</p>
        </div>
    </div>
</div>
<?php $this->endWidget(); ?>
<?php $this->endWidget(); ?>

<?php
$link = Yii::app()->createUrl('import/downExcel');
$js = <<<EOF
$('#downExcelLink').on('click',function(){
    var url = '{$link}';
    url+='?type='+$('#import_type').val()+'&code={$code}';
    window.open(url);
});
EOF;
Yii::app()->clientScript->registerScript('import_form',$js,CClientScript::POS_READY);
?>
