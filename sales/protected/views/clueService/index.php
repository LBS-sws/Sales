<?php
$this->pageTitle=Yii::app()->name . ' - Clue Service';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Clue Service'); ?></strong>
	</h1>
</section>

<section class="content">
    <div class="box">
        <div class="box-body">
            <div class="form-group">
                <div class="btn-group" role="group">
                    <?php
                    $modelName = get_class($model);
                    $signList=CGetName::getFlowOddsList();
                    $signList[10]="待发起合同审批";
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
        'viewhdr'=>'//clueService/_listhdr',
        'viewdtl'=>'//clueService/_listdtl',
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

$url = Yii::app()->createUrl('clueService/index',array("pageNum"=>1));
$js = "
    $('.btn_submit').on('click',function(){
        var key=$(this).data('key');
        $(\"#ClueServiceList_orderField\").val(\"\");
        $(\"#ClueServiceList_flow_odds\").val(key);
        jQuery.yii.submitForm(this,'{$url}',{});
    });
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
<?php
echo TbHtml::button("",array("submit"=>"","class"=>"hide"));
$this->renderPartial('//clue/select_clue',array("actionUrl"=>Yii::app()->createUrl('clueService/new')));
?>
