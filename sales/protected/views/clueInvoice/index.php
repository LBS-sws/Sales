<?php
$this->pageTitle=Yii::app()->name . ' - Clue Invoice';
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
		<strong>
            <?php
            if(!empty($clueHeadModel->id)){
                echo "线索：".$clueHeadModel->cust_name;
            }else{
                echo Yii::t('app','Clue Invoice');
            }
            ?>
        </strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
        <?php
        if (Yii::app()->user->validRWFunction('CM02')&&!empty($clueHeadModel->id)){
            echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('clue','add invoice'), array(
                    'submit'=>Yii::app()->createUrl('clueInvoice/new',array("clue_id"=>$clueHeadModel->id)))
            );
        }
        ?>
	</div>
	</div></div>
	<?php
    $searchlinkparam=array();
    if(!empty($clueHeadModel->id)){
        $searchlinkparam=array(
            "clue_id"=>$clueHeadModel->id
        );
    }
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('clue','invoice list'),
        'model'=>$model,
        'viewhdr'=>'//clueInvoice/_listhdr',
        'viewdtl'=>'//clueInvoice/_listdtl',
        'searchlinkparam'=>$searchlinkparam,
        'search'=>array(
                "invoice_name",
                "city",
                "invoice_header",
                "tax_id",
                "invoice_address",
        )
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
$js = "
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
