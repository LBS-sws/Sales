<?php
/**
 * 商机
 */
?>
<!--商机-->
<div class="bg_clue_service">
    <div class="clue_service">
        <div class="row" id="clue_service_row">
            <?php
            echo ClueServiceForm::printClueServiceBox($this,$model);
            ?>
        </div>
    </div>
</div>
<!--商机跟进记录、关联门店-->
<div id="clueFlowAndStore">
    <?php
    echo ClueFlowForm::printClueFlowAndStoreBox($this,$model);
    ?>
</div>

