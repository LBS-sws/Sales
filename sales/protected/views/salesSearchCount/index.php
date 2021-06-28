<?php
$this->pageTitle=Yii::app()->name . ' - Sales Count';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'salesSearchCount-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Sales Search Count'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box">
        <div class="box-body">
            <ul class="nav nav-tabs" role="menu">
                <li class="active">
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('SalesSearchCount/index');?>" ><?php echo Yii::t('sales','sales search list'); ?></a>
                </li>
                <li class="">
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('SalesSearchCount/onlySales');?>" ><?php echo Yii::t('sales','only sales statistics'); ?></a>
                </li>
                <li class="">
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('SalesSearchCount/allSales');?>" ><?php echo Yii::t('sales','all sales statistics'); ?></a>
                </li>
                <li class="">
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('SalesSearchCount/allCity');?>" ><?php echo Yii::t('sales','city statistics'); ?></a>
                </li>
            </ul>
            <?php
            $this->widget('ext.layout.ListPageWidget', array(
                'title'=>Yii::t('sales','sales search list'),
                'model'=>$model,
                'viewhdr'=>'//salesSearchCount/_listhdr',
                'viewdtl'=>'//salesSearchCount/_listdtl',
                'search'=>array(
                    'employee_code',
                    'employee_name',
                    'city',
                    'search_date',
                ),
            ));
            ?>
        </div>
    </div>
</section>


<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
	echo $form->hiddenField($model,'filter');

	echo TbHtml::button('', array('submit'=>'#','class'=>'hide'));
?>
<?php $this->endWidget(); ?>
<?php

$js = '
    var lineChartData = {
        labels : ["January","February","March","April","May","June","July"],
        datasets : [
            {
                label: "My First dataset",
                fillColor : "rgba(220,220,220,0.2)",
                strokeColor : "rgba(220,220,220,1)",
                pointColor : "rgba(220,220,220,1)",
                pointStrokeColor : "#fff",
                pointHighlightFill : "#fff",
                pointHighlightStroke : "rgba(220,220,220,1)",
                data : [1,4,50,80,44,51,33]
            },
            {
                label: "M1y First dataset",
                fillColor : "rgba(220,220,220,0.2)",
                strokeColor : "rgba(220,220,220,1)",
                pointColor : "rgba(220,220,220,1)",
                pointStrokeColor : "#fff",
                pointHighlightFill : "#fff",
                pointHighlightStroke : "rgba(220,220,220,1)",
                data : [1,4,50,80,44,51,33]
            }
        ]
    }
    var ctx = document.getElementById("canvas").getContext("2d");
    window.myLine = new Chart(ctx).Line(lineChartData, {
        responsive: true
    });
';
Yii::app()->clientScript->registerScript('selectAll',$js,CClientScript::POS_READY);
?>



