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
		<strong><?php echo Yii::t('app','KA Bot'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if (Yii::app()->user->validRWFunction('KA01'))
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
					'submit'=>Yii::app()->createUrl('kABot/new'), 
				)); 
		?>
	</div>
	<div class="btn-group pull-right hide" role="group">
		<?php
        echo TbHtml::button('<span class="fa fa-down"></span> '.Yii::t('ka','Download'), array(
            'data-toggle'=>'modal','data-target'=>'#downDialog'
        ));
		?>
	</div>
	</div></div>
	<div class="box">
        <div class="box-body">
            <div class="form-group">
                <label><?php echo Yii::t("ka","sign odds")."：";?></label>
                <div class="btn-group" role="group">
                    <?php
                    $modelName = get_class($model);
                    $signList=KABotForm::getSignOddsListForId();
                    $signList[""]="全部";
                    $signZero = $signList[0];
                    unset($signList[0]);
                    $signList[0]=$signZero;
                    foreach ($signList as $key=>$value){
                        $class = $key===$model->sign_odds?" btn-primary active":"";
                        echo TbHtml::button($value,array("class"=>"btn_submit".$class,"data-key"=>$key));
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
	<?php
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('ka','Setting Type List'),
        'model'=>$model,
        'viewhdr'=>'//kABot/_listhdr',
        'viewdtl'=>'//kABot/_listdtl',
        'search'=>array(
            'customer_no',
            'customer_name',
            'available_date',
            'class_id',
            'source_id',
            'link_id',
            'sign_odds',
            'kam_id',
        ),
    ));
	?>
</section>
<?php
	echo $form->hiddenField($model,'sign_odds');
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
?>
<?php
$ftrbtn = array();
$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_DEFAULT,'class'=>'pull-left'));
$ftrbtn[] = TbHtml::button(Yii::t('ka','Download'), array(
    'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
    'submit'=>Yii::app()->createUrl('kABot/downExcel')
));
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'downDialog',
    'header'=>Yii::t('ka','Download'),
    'footer'=>$ftrbtn,
    'show'=>false,
));
?>
<div class="row">
    <div class="col-lg-12">
        <?php echo TbHtml::label(Yii::t("ka",'search year'),"",array('class'=>"col-lg-4 control-label text-right"));?>
        <div class="col-lg-4">
            <?php echo TbHtml::numberField("year",date("Y"));?>
        </div>
    </div>
</div>

<?php
$this->endWidget();
?>

<?php $this->endWidget(); ?>
<?php
$url = Yii::app()->createUrl('kABot/index',array("pageNum"=>1));
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
        $("#KABotList_orderField").val("");
        $("#KABotList_sign_odds").val(key);
        jQuery.yii.submitForm(this,'{$url}',{});
    });
EOF;
$js.= Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
	//Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
