<?php
$sec_type = $model->getScenario();
$sec_type = $model->isReadonly()?"view":$sec_type;
if(empty($model->id)){
    $clueSSEModel = new ClueSSEForm($sec_type);
}else{
    $clueSSEModel = new ContSSEForm($sec_type);
}
$clueSSEModel->retrieveServiceData($row["a_id"]);
?>

<div class="col-lg-12">
    <?php
    $this->renderPartial('//visit/serviceDiv',array(
        "model"=>$clueSSEModel,
        "form"=>$form,
        'freBool'=>true,
    ));
    ?>
</div>