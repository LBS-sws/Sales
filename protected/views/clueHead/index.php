<?php
$this->pageTitle=Yii::app()->name . ' - Clue Head';
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
		<strong><?php echo Yii::t('app','Clue Head'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
        <?php
        if (Yii::app()->user->validRWFunction('CM02'))
            echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('clue','New Clue'), array(
                    'name'=>'btnAdd','id'=>'btnAdd','data-toggle'=>'modal','data-target'=>'#clueDialog',)
            );
        ?>
	</div>
            <?php if (Yii::app()->user->validRWFunction('CM02')): ?>
                <div class="btn-group pull-right" role="group">
                    <?php
                    echo TbHtml::button(Yii::t('clue','import clue head'), array(
                            'data-toggle'=>'modal','data-target'=>'#importClueDialog','data-type'=>'clueHead')
                    );
                    ?>
                </div>
            <?php endif ?>
	</div></div>
    <div class="box">
        <div class="box-body">
            <div class="form-group">
                <div class="btn-group" role="group">
                    <?php
                    $modelName = get_class($model);
                    $signList=CGetName::getFlowOddsList();
                    $class = $model->flow_odds===""?" btn-primary active":"";
                    echo TbHtml::button("全部",array("class"=>"btn_submit".$class,"data-key"=>""));
                    foreach ($signList as $key=>$value){
                        $class = "".$key===$model->flow_odds?" btn-primary active":"";
                        if($key==3){
                            $value.='<span class="badge">'.$model->toDayNum.'</span>';
                        }
                        echo TbHtml::button($value,array("class"=>"btn_submit".$class,"data-key"=>$key));
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
	<?php
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('clue','Clue List'),
        'model'=>$model,
        'viewhdr'=>'//clueHead/_listhdr',
        'viewdtl'=>'//clueHead/_listdtl',
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
	echo $form->hiddenField($model,'flow_odds');
?>
<?php $this->endWidget(); ?>

<?php

$url = Yii::app()->createUrl('clueHead/index',array("pageNum"=>1));
$js = "
    $('.btn_submit').on('click',function(){
        var key=$(this).data('key');
        $(\"#ClueHeadList_orderField\").val(\"\");
        $(\"#ClueHeadList_flow_odds\").val(key);
        jQuery.yii.submitForm(this,'{$url}',{});
    });
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
<?php
echo TbHtml::button("",array("submit"=>"","class"=>"hide"));
$this->renderPartial('//clue/importClueDialog',array("importType"=>"clue"));
$this->renderPartial('//clue/select_clue',array("actionUrl"=>Yii::app()->createUrl('clueHead/new')));
?>
