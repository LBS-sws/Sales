<?php
$this->pageTitle=Yii::app()->name . ' - Clue Head Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
    'htmlOptions'=>array('enctype'=>'multipart/form-data'),
)); ?>
<style>
    .select2-container.select2-container-disabled .select2-choice {
        background-color: #ddd;
        border-color: #a8a8a8;
    }
    select.readonly{ pointer-events: none;}
    select[readonly]{pointer-events: none;}
    .select2.select2-container{ width: 100%!important;}
    .select2-container--default .select2-selection--multiple .select2-selection__choice{ padding: 0px 7px;}
    .select2-container .select2-selection--single{ height: 34px;}

</style>
<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('clue','Clue Rpt Form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php
        $session = Yii::app()->session;
        if(isset($session["clueDetail"])&&$session["clueDetail"]=="rpt"){
            $backUrl = Yii::app()->createUrl('clueRpt/index');
        }elseif($model->clueHeadRow["table_type"]==1){
            $backUrl = Yii::app()->createUrl('clueHead/view',array('index'=>$model->clue_id,'service_id'=>$model->clue_service_id));
        }else{
            $backUrl = Yii::app()->createUrl('clientHead/view',array('index'=>$model->clue_id,'service_id'=>$model->clue_service_id));
        }
        echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>$backUrl));
		?>
        <?php if ($model->scenario!='view'&&in_array($model->rpt_status,array(0,9))): ?>
            <?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('clue','draft'), array(
                'submit'=>Yii::app()->createUrl('clueRpt/save',array('type'=>'draft'))));
            ?>
            <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('clue','need audit'), array(
                'submit'=>Yii::app()->createUrl('clueRpt/save',array('type'=>'audit'))));
            ?>
            <?php if ($model->scenario!='new'): ?>
                <?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
                    'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog'));
                ?>
            <?php endif ?>
        <?php endif ?>
	</div>
            <div class="btn-group pull-right" role="group">
                <?php if (!empty($model->id)): ?>
                    <?php
                    echo TbHtml::link(Yii::t("clue","link mh"),CGetName::getMHUrlByClueRptMHID($model->mh_id),array(
                        "class"=>"btn btn-default",
                        "target"=>"_blank",
                    ));
                    ?>
                    <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('clue','Clue Rpt History'), array(
                            'data-toggle'=>'modal','data-target'=>'#clueHistoryDialog',)
                    );
                    ?>
                <?php endif ?>
            </div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
            <?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo $form->hiddenField($model, 'clue_type'); ?>
            <?php echo $form->hiddenField($model, 'city'); ?>
            <?php echo $form->hiddenField($model, 'mh_id'); ?>
            <?php echo $form->hiddenField($model, 'rpt_status'); ?>
            <div class="form-group">
                <?php echo $form->labelEx($model,'sales_id',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->hiddenField($model, 'sales_id'); ?>
                    <?php
                    echo TbHtml::textField("sales_id",CGetName::getEmployeeNameByKey($model->sales_id),array(
                            'readonly'=>true
                    ));
                    ?>
                </div>
                <?php echo $form->labelEx($model,'rpt_status',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->hiddenField($model, 'rpt_status'); ?>
                    <?php
                    echo TbHtml::textField("rpt_status",CGetName::getRptStatusStrByKey($model->rpt_status),array(
                        'readonly'=>true
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'clue_id',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->textField($model, 'clue_id',
                        array('readonly'=>true)
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'clue_service_id',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'clue_service_id',
                        array('readonly'=>true)
                    ); ?>
                </div>
                <div class="col-lg-4">
                    <p class="form-control-static">
                        <?php
                        if($model->clueHeadRow["table_type"]==1){
                            $goUrl = Yii::app()->createUrl('clueHead/view',array('index'=>$model->clue_id,'service_id'=>$model->clue_service_id));
                        }else{
                            $goUrl = Yii::app()->createUrl('clientHead/view',array('index'=>$model->clue_id,'service_id'=>$model->clue_service_id));
                        }
                        echo TbHtml::link("查看商机",$goUrl,array(
                            "target"=>'_blank'
                        ));
                        ?>
                    </p>
                </div>
            </div>
            <?php
            $rptHistoryRows = $model->getRptHistoryRows();
            if(!empty($rptHistoryRows)){
                ?>
                <div class="form-group">
                    <div class="col-lg-12">
                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th width="90">ID</th>
                                <th width="140">创建时间</th>
                                <th width="140">报价人</th>
                                <th width="140">状态</th>
                                <th width="140">报价金额</th>
                                <th width="160">门户</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($rptHistoryRows as $rptRow): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $rptEditUrl = Yii::app()->createUrl('clueRpt/edit',array('index'=>$rptRow['id']));
                                        echo TbHtml::link($rptRow['id'],$rptEditUrl,array('target'=>'_blank'));
                                        ?>
                                    </td>
                                    <td><?php echo $rptRow['lcd']; ?></td>
                                    <td><?php echo CGetName::getEmployeeNameByKey($rptRow['sales_id']); ?></td>
                                    <td><?php echo CGetName::getRptStatusStrByKey($rptRow['rpt_status']); ?></td>
                                    <td><?php echo $rptRow['total_amt']; ?></td>
                                    <td>
                                        <?php
                                        if(!empty($rptRow['mh_id'])){
                                            echo TbHtml::link(Yii::t("clue","link mh"),CGetName::getMHUrlByClueRptMHID($rptRow['mh_id']),array(
                                                "target"=>"_blank",
                                            ));
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            }
            ?>
            <div class="form-group">
                <?php echo $form->labelEx($model,'cust_name',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-6">
                    <?php echo $form->textField($model, 'cust_name',
                        array('readonly'=>true)
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'cust_class',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->hiddenField($model, 'cust_class'); ?>
                    <?php

                    echo TbHtml::textField("cust_class",CGetName::getCustClassStrByKey($model->cust_class),array(
                        'readonly'=>true
                    ));
                    ?>
                </div>
                <?php echo $form->labelEx($model,'cust_level',array('class'=>"col-lg-1 control-label")); ?>
                <div class="col-lg-3">
                    <?php echo $form->hiddenField($model, 'cust_level'); ?>
                    <?php
                    echo TbHtml::textField("cust_level",CGetName::getCustLevelStrByKey($model->cust_level),array(
                        'readonly'=>true
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'yewudalei',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-3">
                    <?php
                    echo TbHtml::textField("yewudalei",CGetName::getYewudaleiStrByKey($model->clueHeadRow["yewudalei"]),array(
                        'readonly'=>true
                    ));
                    ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel("lbs_main"),'lbs_main',array('class'=>"col-lg-1 control-label",'required'=>true)); ?>
                <div class="col-lg-6">

                    <?php
                    echo $form->dropDownList($model, 'lbs_main',CGetName::getLbsMainList($model->city),array(
                        'readonly'=>$model->isReadonly(),'empty'=>''
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel("total_amt"),'total_amt',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php echo $form->numberField($model, 'total_amt',
                        array('readonly'=>$model->isReadonly())
                    ); ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel("file_type"),'file_type',array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model, 'file_type',CGetName::getFileTypeList(),array(
                        'readonly'=>$model->isReadonly(),'empty'=>''
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel("cont_type_id"),'cont_type_id',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php echo $form->dropDownList($model, 'cont_type_id',CGetName::getContTypeList(),
                        array('readonly'=>$model->isReadonly(),'empty'=>'')
                    ); ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel("fee_add"),'fee_add',array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model, 'fee_add',CGetName::getHasAndNotList(),array(
                        'readonly'=>$model->isReadonly(),'empty'=>''
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel("service_type_id"),'service_type_id',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php echo $form->dropDownList($model, 'service_type_id',CGetName::getServiceFreeList(),
                        array('readonly'=>$model->isReadonly(),'empty'=>'')
                    ); ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel("bill_week"),'bill_week',array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model, 'bill_week',CGetName::getBillWeekList(),array(
                        'readonly'=>$model->isReadonly(),'empty'=>''
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel("audit_type"),'audit_type',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php echo $form->dropDownList($model, 'audit_type',CGetName::getAuditTypeList(),
                        array('readonly'=>$model->isReadonly(),'empty'=>'')
                    ); ?>
                </div>
                <?php echo TbHtml::label($model->getAttributeLabel("cut_type"),'cut_type',array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->dropDownList($model, 'cut_type',CGetName::getHasAndNotList(),array(
                        'readonly'=>$model->isReadonly(),'empty'=>''
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel("is_seal"),'is_seal',array('class'=>"col-lg-2 control-label",'required'=>true)); ?>

                <div class="col-lg-3">
                    <?php
                    echo $form->inlineRadioButtonList($model, 'is_seal',CGetName::getCustVipList(),array(
                        'disabled'=>$model->isReadonly()
                    ));
                    ?>
                </div>
                <div id="seal_type_div" class="<?php echo $model->is_seal=="N"?"hide":"";?>">
                    <?php echo TbHtml::label($model->getAttributeLabel("seal_type_id"),'seal_type_id',array('class'=>"col-lg-1 control-label",'required'=>true)); ?>

                    <div class="col-lg-3">
                        <?php
                        echo $form->dropDownList($model, 'seal_type_id',CGetName::getSealTypeList(),array(
                            'readonly'=>$model->isReadonly(),'empty'=>'','id'=>'seal_type_id'
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel('rptFileJson'),'',array('class'=>"col-lg-2 control-label","required"=>true));?>

                <div class="col-lg-8">
                    <?php
                    $this->renderPartial('//clueRpt/from_table',array(
                        "model"=>$model,
                        "form"=>$form,
                        "fileJson"=>$model->rptFileJson,
                        "valueStr"=>"rptFileJson"
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label($model->getAttributeLabel('contFileJson'),'',array('class'=>"col-lg-2 control-label","required"=>true));?>
                <div class="col-lg-8">
                    <?php
                    $this->renderPartial('//clueRpt/from_table',array(
                        "model"=>$model,
                        "form"=>$form,
                        "fileJson"=>$model->contFileJson,
                        "valueStr"=>"contFileJson"
                    ));
                    ?>
                </div>
            </div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//clue/historylist',array("model"=>$model,"type"=>3)); ?>
<?php
$js = Script::genDeleteData(Yii::app()->createUrl('clueRpt/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = <<<EOF
function formatState(state) {
	var rtn = $('<span style="color:black">'+state.text+'</span>');
	return rtn;
}
EOF;
Yii::app()->clientScript->registerScript('formatState',$js,CClientScript::POS_READY);
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);

$modelClass = get_class($model);
$js = <<<EOF
$('table').on('change','[id^="{$modelClass}"]',function() {
	var n=$(this).attr('id').split('_');
	$('#{$modelClass}_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});
EOF;
Yii::app()->clientScript->registerScript('changeTable',$js,CClientScript::POS_READY);
$js = <<<EOF
$('table').on('click','.table_del', function() {
	$(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
	$(this).closest('tr').hide();
});
EOF;
Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);
$js = <<<EOF
$('#ClueRptForm_is_seal input').change(function(){
    if($(this).val()=="Y"){
        $('#seal_type_div').removeClass('hide');
    }else{
        $('#seal_type_div').addClass('hide');
        $('#seal_type_id').val('');
    }
});
$('table').on('change','.fileVal',function() {
    var fileInput = $(this);
    var filename = fileInput.val();
    var pos = filename.lastIndexOf("\\\\")+1;
    filename = filename.substring(pos, filename.length);
    //验证文件
    if(this.files[0].size>{$model->docMaxSize}){
        showFormErrorHtml("文件大小不能超过10M");
        $(this).val('');
        return false;
    }
    
    var pos = filename.lastIndexOf(".");
    var str = filename.substring(pos, filename.length);
    var str1 = str.toLowerCase();
    var fileType = "jpg|jpeg|png|xlsx|pdf|docx|txt|doc|wps";
    var re = new RegExp("\.(" + fileType + ")$");
    if (!re.test(str1)) {
        showFormErrorHtml("文件格式不正确，只能上传格式为：" + fileType + "的文件。");
        $(this).val('');
        return false;
    }else{
        $(this).parents('tr:first').find('.fileName').val(filename);
    }
});
$('table').on('click','.table_add',function() {
	var r = $(this).data('num');
	if (r>=0) {
	    r++;
	    $(this).data('num',r);
		var nid = '';
		var ct = $(this).next('input').val();
		$(this).parents('thead').eq(0).next('tbody').append(ct);
		$(this).parents('table').eq(0).find('tbody>tr').eq(-1).find('[id*=\"{$modelClass}_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);
		});
	}
});
EOF;
Yii::app()->clientScript->registerScript('addRow',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

<?php
$this->renderPartial("//lookFile/lookFileDialog",array("lookUrl"=>Yii::app()->createUrl('lookFile/rpt')));
?>
<?php $this->renderPartial("//clue/errorDialog");?>
