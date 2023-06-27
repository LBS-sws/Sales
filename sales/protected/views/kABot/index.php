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
	<div class="btn-group pull-right" role="group">
		<?php
        echo TbHtml::button('<span class="fa fa-down"></span> '.Yii::t('ka','Download'), array(
            'data-toggle'=>'modal','data-target'=>'#downDialog'
        ));
		?>
	</div>
	</div></div>
	<?php
    $search_add_html="";
    $modelName = get_class($model);
    $signList=KABotForm::getSignOddsListForId();
    $signList[""]=Yii::t("ka","sign odds");
    $search_add_html .= TbHtml::dropDownList($modelName.'[sign_odds]',$model->sign_odds,$signList,
        array("class"=>"form-control btn_submit"));

    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('ka','Setting Type List'),
        'model'=>$model,
        'viewhdr'=>'//kABot/_listhdr',
        'viewdtl'=>'//kABot/_listdtl',
        'search_add_html'=>$search_add_html,
        'search'=>array(
            'customer_no',
            'customer_name',
            'contact_user',
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

    $('.btn_submit').on('change',function(){
        $('form:first').submit();
    });
EOF;
$js.= Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
	//Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
