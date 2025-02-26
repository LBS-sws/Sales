<?php
$this->pageTitle=Yii::app()->name . ' - Visit Type';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Market Company'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if (Yii::app()->user->validRWFunction('MT01')){
                echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
                    'submit'=>Yii::app()->createUrl('marketCompany/new'),
                ));
                echo TbHtml::button('<span class="fa fa-envelope"></span> '.Yii::t('market','Batch Assign'), array(
                        'data-toggle'=>'modal','data-target'=>'#assigndialog',)
                );
                echo TbHtml::button('<span class="fa fa-trash"></span> '.Yii::t('market','Batch Reject'), array(
                        'data-toggle'=>'modal','data-target'=>'#rejectDialog',)
                );
                echo TbHtml::button('<span class="fa fa-glass"></span> '.Yii::t('market','Batch Success'), array(
                        'data-toggle'=>'modal','data-target'=>'#successDialog',)
                );
            }
		?>
	</div>
            <?php
            if (Yii::app()->user->validRWFunction('MT01')){ //导入
                echo '<div class="btn-group pull-right" role="group">';
                echo TbHtml::button('<span class="fa fa-file-excel-o"></span> '.Yii::t('market','import'), array(
                        'data-toggle'=>'modal','data-target'=>'#importdialog',)
                );
                echo '</div>';
            }
            ?>
	</div></div>
	<div class="box">
        <div class="box-body">
            <div class="form-group">
                <label><?php echo Yii::t("market","status type")."：";?></label>
                <div class="btn-group" role="group">
                    <?php
                    $modelName = get_class($model);
                    $signList=MarketFun::getSearchStatusList();
                    foreach ($signList as $key=>$value){
                        $class = $key===$model->status_type?" btn-primary active":"";
                        echo TbHtml::button($value,array("class"=>"btn_submit".$class,"data-key"=>$key));
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
	<?php
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('market','Company List'),
        'model'=>$model,
        'viewhdr'=>'//marketCompany/_listhdr',
        'viewdtl'=>'//marketCompany/_listdtl',
        'search'=>array(
            'number_no',
            'company_name',
            'person_phone',
            'allot_city',
            'employee_name',
        ),
    ));
	?>
</section>
<?php
	echo $form->hiddenField($model,'status_type');
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');

	echo CHtml::hiddenField('assign_id');
?>

<?php $this->endWidget(); ?>
<?php
$url = Yii::app()->createUrl('marketCompany/index',array("pageNum"=>1));
$js = <<<EOF
function showdetail(id) {
	var icon = $('#btn_'+id).attr('class');
	if (icon.indexOf('plus') >= 0) {
		$('.detail_'+id).show();
		$('#btn_'+id).attr('class', 'fa fa-minus-square');
	} else {
		$('.detail_'+id).hide();
		$('#btn_'+id).attr('class', 'fa fa-plus-square');
	}
}

$('.click-td').on('click',function(e){
    var id = $(this).data('id');
    if($('#btn_'+id).length>=1){
        showdetail(id);
    }
    e.stopPropagation();
});

    $('.btn_submit').on('click',function(){
        var key=$(this).data('key');
        $("#MarketCompanyList_orderField").val("");
        $("#MarketCompanyList_status_type").val(key);
        jQuery.yii.submitForm(this,'{$url}',{});
    });
    
$('.che').on('click', function(e){
e.stopPropagation();
});

$('body').on('click','#all',function() {
	var val = $(this).prop('checked');
	$('.che').children('input[type=checkbox]').prop('checked',val);
});

function totalCheckBoxList(){
    var list = [];
    $('input[type=checkbox]:checked').each(function(){
        var id = $(this).val();
        if(id!=''&&list.indexOf(id)==-1&&$(this).parent('td.che').length==1){
            list.push(id);
        }
    });
    list = list.join(',');
    $('#assign_id').val(list);
};
EOF;
$js.= Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
	//Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>

<?php
$this->renderPartial('//marketForm/assigndialog',array(
    'assignType'=>'list',
    'submit'=>Yii::app()->createUrl('marketCompany/assign'),
    'jsHtml'=>"totalCheckBoxList();",
));
?>

<?php
$this->renderPartial('//marketForm/backDialog',array(
    'submit'=>Yii::app()->createUrl('marketCompany/back'),
    'type_num'=>0,
    'jsHtml'=>"totalCheckBoxList();"
));
?>

<?php
$this->renderPartial('//marketForm/rejectDialog',array(
    'submit'=>Yii::app()->createUrl('marketCompany/reject'),
    'type_num'=>0,
    'jsHtml'=>"totalCheckBoxList();"
));
?>

<?php
$this->renderPartial('//marketForm/successDialog',array(
    'submit'=>Yii::app()->createUrl('marketCompany/success'),
    'type_num'=>0,
    'jsHtml'=>"totalCheckBoxList();"
));
?>

<?php $this->renderPartial('//marketCompany/importdialog'); ?>
