<?php
$this->pageTitle=Yii::app()->name . ' - Visit Type Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
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
		<strong><?php echo Yii::t('app','service type setting'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('serviceType/new')));
				echo TbHtml::button('复制', array(
					'submit'=>Yii::app()->createUrl('serviceType/new',array("index"=>$model->id))));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('serviceType/index')));
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('serviceType/save')));
			?>
<?php endif ?>
<?php if ($model->scenario=='edit'): ?>
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

            <?php if ($model->scenario!='new'): ?>
                <div class="form-group">
                    <?php echo $form->labelEx($model,'id_char',array('class'=>"col-sm-2 control-label")); ?>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'id_char',
                            array('size'=>50,'maxlength'=>100,'readonly'=>true)
                        ); ?>
                    </div>
                </div>
            <?php endif ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textField($model, 'name',
						array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'service_type',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->dropDownList($model, 'service_type',CGetName::getServiceTypeList(),
						array('readonly'=>($model->scenario=='view'),'empty'=>'')
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'class_id',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->dropDownList($model, 'class_id',CGetName::getSetMenuTypeList('serviceTypeClass'),
						array('readonly'=>($model->scenario=='view'),'empty'=>'')
					); ?>
				</div>
                <div class="col-sm-8">
                    <p class="form-control-static">不选择则销售签单的数量及签单金额都不统计</p>
                </div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'type_str',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->dropDownList($model, 'type_str',CGetName::getANTypeList(),
						array('readonly'=>($model->scenario=='view'))
					); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'u_code',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textField($model, 'u_code',
						array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'z_index',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->numberField($model, 'z_index',
						array('readonly'=>($model->scenario=='view'))
					); ?>
				</div>
                <div class="col-sm-8">
                    <p class="form-control-static">数字越高，排序越靠后</p>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'z_display',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->inlineRadioButtonList($model, 'z_display',CGetName::getDisplayList(),
						array('readonly'=>($model->scenario=='view'))
					); ?>
				</div>
			</div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10 text-danger">
                    <p class="form-control-static">标靶虫害、处理方式如果需要默认选中，默认值需要设置为：Y<br/>设备、洁具如果需要默认选中，默认值需要设置为大于零的数值，例如：1</p>
                </div>
            </div>

            <div class="form-group" id="" >
                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <?php
                            $html="<thead><tr>";
                            $html.="<th width='15%'>名称</th>";
                            $html.="<th width='12%'>输入框类型</th>";
                            $html.="<th width='10%'>选择框函数</th>";
                            $html.="<th width='8%'>默认值</th>";
                            $html.="<th width='8%'>派单(id)标识</th>";
                            $html.="<th width='8%'>派单(type)标识</th>";
                            $html.="<th width='10%'>是否强制换行</th>";
                            $html.="<th width='8%'>层级</th>";
                            $html.="<th width='8%'>是否显示</th>";
                            $html.="<th width='12%'>是否最终统计金额</th>";
                            if($model->isReadonly()===false){
                                $num =count($model->infoJson);
                                $html.="<th width='1%'>";
                                $html.=TbHtml::button("+",array(
                                    "class"=>"table_add",
                                    "data-temp"=>"temp2",
                                    "data-num"=>$num,
                                ));
                                $tempHtml=$this->renderPartial('//serviceType/table_temp',array("model"=>$model,"form"=>$form,"num"=>0),true);
                                $html.=TbHtml::hiddenField("temp",$tempHtml);
                                $html.="</th>";
                            }
                            $html.="</tr></thead><tbody>";
                            if(!empty($model->infoJson)){
                                foreach ($model->infoJson as $key=>$row){
                                    $html.=$this->renderPartial('//serviceType/table_temp',array("model"=>$model,"form"=>$form,"row"=>$row,"num"=>$key),true);
                                }
                            }
                            $html.="</tbody>";
                            echo $html;
                            ?>
                        </table>
                    </div>
                </div>
            </div>

        </div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
switch(Yii::app()->language) {
    case 'zh_cn': $lang = 'zh-CN'; break;
    case 'zh_tw': $lang = 'zh-TW'; break;
    default: $lang = Yii::app()->language;
}
$disabled = $model->scenario=='view'? 'true':'false';
$js="
";
Yii::app()->clientScript->registerScript('selectBoxFunction',$js,CClientScript::POS_READY);
$js = <<<EOF
$('table').on('click','.table_del', function() {
	$(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
	$(this).closest('tr').hide();
});
EOF;
Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);
$modelClass = get_class($model);
$js = <<<EOF
$('table').on('change','[id^="{$modelClass}"]',function() {
	var n=$(this).attr('id').split('_');
	$('#{$modelClass}_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});
$('table').on('change','.inputType',function() {
    if($(this).val()=="select"){
        $(this).parents('tr:first').find('.func').removeClass('readonly').removeAttr('readonly');
    }else{
        $(this).parents('tr:first').find('.func').addClass('readonly').attr('readonly','readonly');
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
$js = <<<EOF
function formatState(state) {
	var rtn = $('<span style="color:black">'+state.text+'</span>');
	return rtn;
}
EOF;
Yii::app()->clientScript->registerScript('formatState',$js,CClientScript::POS_READY);
$js = Script::genDeleteData(Yii::app()->createUrl('serviceType/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


