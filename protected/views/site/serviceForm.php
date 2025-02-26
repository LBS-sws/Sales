<?php
	$serviceList = StopOtherForm::getServiceList($model->service_id);
?>

<?php if (!empty($serviceList)): ?>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Record Type"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-3">
            <?php
            echo TbHtml::textField("status_desc",$serviceList["status_desc"],array('readonly'=>true));
            ?>
		</div>
        <?php echo TbHtml::label(Yii::t("service","Contract No"),'',array('class'=>"col-lg-1 control-label")); ?>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("contract_no",$serviceList["contract_no"],array('readonly'=>true));
            ?>
		</div>
        <?php echo TbHtml::label(Yii::t("service","Contact"),'',array('class'=>"col-lg-1 control-label",'style'=>"white-space:nowrap")); ?>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("contract_no",$serviceList["company_cont"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Terminate Date"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-3">
            <?php
            echo TbHtml::textField("status_dt",$serviceList["status_dt"],array('readonly'=>true));
            ?>
		</div>
        <?php echo TbHtml::label(Yii::t("service","Prepay Month"),'',array('class'=>"col-lg-1 control-label")); ?>
		<div class="col-lg-1">
            <?php
            echo TbHtml::textField("prepay_month",$serviceList["prepay_month"],array('readonly'=>true));
            ?>
		</div>
        <?php echo TbHtml::label(Yii::t("service","Prepay Start"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-1">
            <?php
            echo TbHtml::textField("prepay_start",$serviceList["prepay_start"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Customer"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-7">
            <?php
            echo TbHtml::textField("company_name",$serviceList["company_name"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Customer Type"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-3">
            <?php
            echo TbHtml::textField("cust_type",$serviceList["cust_type"],array('readonly'=>true));
            ?>
		</div>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("cust_type_name",$serviceList["cust_type_name"],array('readonly'=>true));
            ?>
		</div>
        <?php echo TbHtml::label(Yii::t("service","Pieces"),'',array('class'=>"col-lg-1 control-label")); ?>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("pieces",$serviceList["pieces"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Nature"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-3">
            <?php
            echo TbHtml::textField("nature_type",$serviceList["nature_type"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Service"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-7">
            <?php
            echo TbHtml::textField("service",$serviceList["service"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Paid Amt"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-3">
            <?php
            echo TbHtml::textField("paid_type",$serviceList["paid_type"],array('readonly'=>true));
            ?>
		</div>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("amt_paid",$serviceList["amt_paid"],array('readonly'=>true,'prepend'=>"<span class='fa fa-cny'></span>"));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Number"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("all_number",$serviceList["all_number"],array('readonly'=>true));
            ?>
		</div>
        <?php echo TbHtml::label(Yii::t("service","Surplus"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("surplus",$serviceList["surplus"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Number edit0"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("all_number_edit0",$serviceList["all_number_edit0"],array('readonly'=>true));
            ?>
		</div>
        <?php echo TbHtml::label(Yii::t("service","Surplus edit0"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("surplus_edit0",$serviceList["surplus_edit0"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Number edit1"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("all_number_edit1",$serviceList["all_number_edit1"],array('readonly'=>true));
            ?>
		</div>
        <?php echo TbHtml::label(Yii::t("service","Surplus edit1"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("surplus_edit1",$serviceList["surplus_edit1"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Number edit2"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("all_number_edit2",$serviceList["all_number_edit2"],array('readonly'=>true));
            ?>
		</div>
        <?php echo TbHtml::label(Yii::t("service","Surplus edit2"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("surplus_edit2",$serviceList["surplus_edit2"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Number edit3"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("all_number_edit3",$serviceList["all_number_edit3"],array('readonly'=>true));
            ?>
		</div>
        <?php echo TbHtml::label(Yii::t("service","Surplus edit3"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("surplus_edit3",$serviceList["surplus_edit3"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Resp. Sales"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-7">
            <?php
            echo TbHtml::textField("salesman_name",$serviceList["salesman_name"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","OtherSalesman"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-7">
            <?php
            echo TbHtml::textField("othersalesman_name",$serviceList["othersalesman_name"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Resp. Tech."),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-7">
            <?php
            echo TbHtml::textField("sign_dt",$serviceList["sign_dt"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Sign Date"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-3">
            <?php
            echo TbHtml::textField("technician_name",$serviceList["technician_name"],array('readonly'=>true,'prepend'=>"<span class='fa fa-calendar'></span>"));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Contract Period"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-2">
            <?php
            echo TbHtml::textField("ctrt_period",$serviceList["ctrt_period"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Contract End Date"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-3">
            <?php
            echo TbHtml::textField("ctrt_end_dt",$serviceList["ctrt_end_dt"],array('readonly'=>true,'prepend'=>"<span class='fa fa-calendar'></span>"));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Reason"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-7">
            <?php
            echo TbHtml::textArea("reason",$serviceList["reason"],array('readonly'=>true,'rows'=>3));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Org. Equip. Qty"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-7">
            <?php
            echo TbHtml::textField("org_equip_qty",$serviceList["org_equip_qty"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Return Equip. Qty"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-7">
            <?php
            echo TbHtml::textField("rtn_equip_qty",$serviceList["rtn_equip_qty"],array('readonly'=>true));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Remarks"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-7">
            <?php
            echo TbHtml::textArea("remarks2",$serviceList["remarks2"],array('readonly'=>true,'rows'=>3));
            ?>
		</div>
	</div>

	<div class="form-group">
        <?php echo TbHtml::label(Yii::t("service","Cross Area Remarks"),'',array('class'=>"col-lg-2 control-label")); ?>
		<div class="col-lg-7">
            <?php
            echo TbHtml::textArea("remarks",$serviceList["remarks"],array('readonly'=>true,'rows'=>3));
            ?>
		</div>
	</div>

<?php endif ?>