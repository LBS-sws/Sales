<?php
$sec_type = $row["update_bool"]==1?"edit":"view";
$model = new ClueSSEForm($sec_type);
$model->retrieveData($row["a_id"]);
$form=$this->beginWidget('TbActiveForm', array(
    'id'=>'sse-form'.$row["a_id"],
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,

));
?>
<?php echo $form->hiddenField($model, 'id'); ?>
<?php echo $form->hiddenField($model, 'scenario'); ?>
<?php echo $form->hiddenField($model, 'clue_id'); ?>
<?php echo $form->hiddenField($model, 'clue_service_id'); ?>
<?php echo $form->hiddenField($model, 'clue_store_id'); ?>

<div class="col-lg-12">
    <?php
    $this->renderPartial('//visit/serviceDiv',array(
        "model"=>$model,
        "form"=>$form,
    ));
    ?>
</div>
<?php $this->endWidget(); ?>