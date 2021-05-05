<?php
$this->pageTitle=Yii::app()->name . ' - Apply';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'giftRequest-list',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('redeem','apply list'); ?></strong>
    </h1>
    <!--
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="#">Layout</a></li>
            <li class="active">Top Navigation</li>
        </ol>
    -->
</section>

<section class="content">
    <div class="box">
        <div class="box-body">
<!--            <div class="btn-group pull-left" role="group">-->
<!--                <span class="text-success">--><?php //echo date("Y")."年".Yii::t("integral","Sum Gift")."：".$cutIntegral["sum"];?><!--</span>-->
<!--            </div>-->
            <div class="btn-group pull-right" role="group">
                <span class="text-success">可用<?php echo Yii::t('redeem','Sum Gift')."：".$cutIntegral["cut"];?></span>
            </div>
        </div>
    </div>
    <?php
/*    $search = array(
        'gift_name',
        'bonus_point',
    );
    $search_add_html="";
    $modelName = get_class($model);
    $search_add_html .= TbHtml::textField($modelName.'[searchTimeStart]',$model->searchTimeStart,
        array('size'=>15,'placeholder'=>Yii::t('misc','Start Date'),"class"=>"form-control","id"=>"start_time"));
    $search_add_html.="<span>&nbsp;&nbsp;-&nbsp;&nbsp;</span>";
    $search_add_html .= TbHtml::textField($modelName.'[searchTimeEnd]',$model->searchTimeEnd,
        array('size'=>15,'placeholder'=>Yii::t('misc','End Date'),"class"=>"form-control","id"=>"end_time"));*/

   $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('redeem','apply list'),
        'model'=>$model,
        'viewhdr'=>'//rgapply/_listhdr',
        'viewdtl'=>'//rgapply/_listdtl',
        'gridsize'=>'24',
        'height'=>'600',
      // 'search'=>$search_add_html,
//       'search'=>$search,
        'search'=>array(
        'gift_name',
        'bonus_point',
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
<?php $this->endWidget(); ?>

<?php
TbHtml::button('', array('submit'=>''));
$js = "
$('#start_time').datepicker({autoclose: true, format: 'yyyy/mm/dd',language: 'zh_cn'});
$('#end_time').datepicker({autoclose: true, format: 'yyyy/mm/dd',language: 'zh_cn'});
";
//Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
$js = Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>

