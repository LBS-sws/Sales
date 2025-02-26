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
		<strong><?php echo Yii::t('app','Market Sales'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box">
        <div class="box-body">
            <div class="form-group">
                <label><?php echo Yii::t("market","status type")."：";?></label>
                <div class="btn-group" role="group">
                    <?php
                    $modelName = get_class($model);
                    $signList=MarketFun::getSearchSalesStatusList();
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
        'viewhdr'=>'//marketSales/_listhdr',
        'viewdtl'=>'//marketSales/_listdtl',
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
	echo CHtml::button('test_btn',array('submit'=>'','class'=>'hide'));
?>

<?php $this->endWidget(); ?>
<?php
$url = Yii::app()->createUrl('marketSales/index',array("pageNum"=>1));
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
        $("#MarketSalesList_orderField").val("");
        $("#MarketSalesList_status_type").val(key);
        jQuery.yii.submitForm(this,'{$url}',{});
    });
    
EOF;
$js.= Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
	//Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>

