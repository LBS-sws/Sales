<div class="information-header">
    <h4>
        <strong>服务项目</strong>
    </h4>
</div>
<div class="form-group">
    <div class="col-lg-12">
        <?php
        $this->renderPartial('//visit/serviceDiv',array(
            "model"=>$virModel,
            "form"=>$form,
            'freBool'=>true,
        ));
        ?>
    </div>
</div>