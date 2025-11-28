<?php
$this->pageTitle=Yii::app()->name . ' - Clue Box';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>
<style>
    .select2.select2-container{ width: 100%!important;}
    .select2-container--default .select2-selection--multiple .select2-selection__choice{ padding: 0px 7px;}
    .select2-container .select2-selection--single{ height: 34px;}
</style>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Clue Box'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
        <?php
        if (Yii::app()->user->validRWFunction('CM01')){
            echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('clue','New Clue Box'), array(
                    'data-toggle'=>'modal','data-target'=>'#clueDialog',)
            );
        }
        if (Yii::app()->user->validFunction('CM01')){
            echo TbHtml::button('<span class="fa fa-level-down"></span> '.Yii::t('clue','batch assign'), array(
                    'data-toggle'=>'modal','data-target'=>'#clueAssignDialog',)
            );
        }
        if (Yii::app()->user->validRWFunction('CM01')){
            echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('clue','batch delete'), array(
                    'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
            );
        }
        ?>
	</div>
            <?php if (Yii::app()->user->validRWFunction('CM01')): ?>
            <div class="btn-group pull-right" role="group">
                <?php
                echo TbHtml::button(Yii::t('clue','import clue box'), array(
                        'data-toggle'=>'modal','data-target'=>'#importClueDialog','data-type'=>'clueBox')
                );
                ?>
            </div>
            <?php endif ?>
	</div></div>
	<?php
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('clue','Clue List'),
        'model'=>$model,
        'viewhdr'=>'//clueBox/_listhdr',
        'viewdtl'=>'//clueBox/_listdtl',
        'advancedSearch'=>true,
    ));
	?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
	echo $form->hiddenField($model,'filter');
?>
<?php $this->endWidget(); ?>

<?php
$deleteUrl = Yii::app()->createUrl('clueBox/batchDelete');
$url = Yii::app()->createUrl('clueBox/index',array("pageNum"=>1));
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

    $('#btnDeleteData').on('click',function(){
        var url = '{$deleteUrl}';
        var assign_id = '';
        var elm=$('#dialogAssignBtnOk');
        $('.select_check').each(function(){
            assign_id+=assign_id==''?'':',';
            assign_id+=$(this).val();
        });
        url+='?assign_id='+assign_id;
        window.location.href=url;
    });
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
<?php
echo TbHtml::button("",array("submit"=>"","class"=>"hide"));
$this->renderPartial('//site/removedialog');
$this->renderPartial('//clue/importClueDialog',array("importType"=>"clueBox"));
$this->renderPartial('//clue/select_clue',array("actionUrl"=>Yii::app()->createUrl('clueBox/new'),"allBool"=>true));
$this->renderPartial('//clue/clue_assign',array("actionUrl"=>Yii::app()->createUrl('clueBox/batchAssign'),"assignCity"=>Yii::app()->user->city()));
?>
