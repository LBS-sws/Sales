<?php
$this->pageTitle=Yii::app()->name . ' - Virtual List';
?>
<style>
    .table-fixed { table-layout: fixed;}
</style>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Virtual List'); ?></strong>
	</h1>
</section>

<section class="content">
    <div class="box">
        <div class="box-body">
            <div class="btn-group" role="group">
                <?php
                echo TbHtml::button(Yii::t('app','Virtual Update List'), array(
                    'submit'=>Yii::app()->createUrl('virtualBatch/index')
                ));
                ?>
            </div>
            <div class="btn-group pull-right" role="group">
                <?php
                echo TbHtml::button(Yii::t("clue","Cont Amend"), array(
                    "color"=>TbHtml::BUTTON_COLOR_PRIMARY,
                    "class"=>"btn-cont",
                    "data-url"=>Yii::app()->createUrl('virtualBatch/new',array("type"=>"A")),
                ));
                ?>
                <?php
                echo TbHtml::button(Yii::t("clue","Cont Suspend"), array(
                    "color"=>TbHtml::BUTTON_COLOR_PRIMARY,
                    "class"=>"btn-cont",
                    "data-url"=>Yii::app()->createUrl('virtualBatch/new',array("type"=>"S")),
                ));
                ?>
                <?php
                echo TbHtml::button(Yii::t("clue","Cont Terminate"), array(
                    "color"=>TbHtml::BUTTON_COLOR_PRIMARY,
                    "class"=>"btn-cont",
                    "data-url"=>Yii::app()->createUrl('virtualBatch/new',array("type"=>"T")),
                ));
                ?>
                <?php
                echo TbHtml::button(Yii::t("clue","Cont Resume"), array(
                    "color"=>TbHtml::BUTTON_COLOR_PRIMARY,
                    "class"=>"btn-cont",
                    "data-url"=>Yii::app()->createUrl('virtualBatch/new',array("type"=>"R")),
                ));
                ?>
            </div>
        </div>
    </div>
	<?php
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('clue','Virtual List'),
        'model'=>$model,
        'viewhdr'=>'//virtualHead/_listhdr',
        'viewdtl'=>'//virtualHead/_listdtl',
        'advancedSearch'=>true,
        'hasDateButton'=>true,
        'tableClass'=>"table table-hover table-fixed table-condensed",
    ));
	?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
	echo $form->hiddenField($model,'filter');
	echo $form->hiddenField($model,'flow_odds');
?>
<?php $this->endWidget(); ?>

<?php

$url = Yii::app()->createUrl('virtualHead/index',array("pageNum"=>1));
$js = "
$('.che').on('click', function(e){
	var val = $(this).children('input[type=checkbox]').eq(0).prop('checked');
	if(val){
	    $(this).children('input[type=checkbox]').eq(0).addClass('select_check');
	}else{
	    $(this).children('input[type=checkbox]').eq(0).removeClass('select_check');
	}
    e.stopPropagation();
});

$('body').on('click','#all',function() {
	var val = $(this).prop('checked');
	$('.che').children('input[type=checkbox]').prop('checked',val);
	$('.che').children('input[type=checkbox]').removeClass('select_check');
	if(val){
	    $('.che').children('input[type=checkbox]').addClass('select_check');
	}
});

    $('.btn-cont').on('click',function(){
        var url = $(this).data('url');
        var check_id = '';
        var elm=$('#dialogAssignBtnOk');
        $('.select_check').each(function(){
            check_id+=check_id==''?'':',';
            check_id+=$(this).val();
        });
        if(check_id==''){
            showFormErrorHtml('请选择虚拟合约');
        }else{
            url+='&check_id='+check_id;
            window.location.href=url;
        }
    });

    $('.btn_submit').on('click',function(){
        var key=$(this).data('key');
        $(\"#VirtualHeadList_orderField\").val(\"\");
        $(\"#VirtualHeadList_flow_odds\").val(key);
        jQuery.yii.submitForm(this,'{$url}',{});
    });
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
<?php
echo TbHtml::button("",array("submit"=>"","class"=>"hide"));
$this->renderPartial('//clue/errorDialog');
?>
