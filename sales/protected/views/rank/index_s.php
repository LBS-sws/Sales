<?php
$this->pageTitle=Yii::app()->name . ' - commission Report';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'commission-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('report','Sales Rank'); ?></strong>
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
    <div class="box"><div class="box-body">
            <div class="btn-group" role="group">
                <?php echo TbHtml::button(Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('rank/index')));
                ?>
                <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Xiazai'), array(
                    'submit'=>Yii::app()->createUrl('rank/excel')));
                ?>
            </div>
            <div class="btn-group" role="group">
    <span class="text-red">
        <?php
        echo Yii::t('misc','Click to download and view the segment calculation rule report.');
        ?>
    </span>
            </div>
            <div class="btn-group pull-right" role="group">
                <?php
                echo TbHtml::link(Yii::t("rank","go back"),Yii::app()->createUrl(''),array("class"=>"btn btn-default"));
                ?>
            </div>
        </div></div>
    <input type="hidden" name="city" value="<?php echo $a['city'];?>">
    <input type="hidden" name="season" value="<?php echo $a['season'];?>">
	<?php
    $search = array(
        'employee_code',
        'employee_name',
        'city',
        'user_name',
    );
    $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('report','Sales Rank man'),
			'model'=>$model,
				'viewhdr'=>'//rank/_listhdr',
				'viewdtl'=>'//rank/_listdtl',
				'gridsize'=>'24',
				'height'=>'600',
                'hasNavBar'=>false,
                'hasPageBar'=>false,
                'hasSearchBar'=>false,

		));
    echo TBhtml::button('dummyButtin',array('style'=>'display:none','disabled'=>true,'submit'=>'#',))
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
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>

