<?php
$this->pageTitle=Yii::app()->name . ' - Sales Count';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'onlySales-list',
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
            <div class="col-sm-12">
                <div class="form-group">
                    <?php
                    echo $form->dropDownList($model,"year",$model->getYearAll(),array('class'=>"changeSelect"));
                    ?>
                </div>
                <div class="form-group">
                    <?php
                    echo $form->dropDownList($model,"month",$model->getMonthAll(),array('class'=>"changeSelect"));
                    ?>
                </div>
            </div>
        </div>
        <div class="box-body">
            <ul class="nav nav-tabs" role="menu">
                <li class="">
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('SalesSearchCount/index');?>" ><?php echo Yii::t('sales','sales search list'); ?></a>
                </li>
                <li class="">
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('SalesSearchCount/onlySales');?>" ><?php echo Yii::t('sales','only sales statistics'); ?></a>
                </li>
                <li class="">
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('SalesSearchCount/allSales');?>" ><?php echo Yii::t('sales','all sales statistics'); ?></a>
                </li>
                <li class="active">
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('SalesSearchCount/allCity');?>" ><?php echo Yii::t('sales','city statistics'); ?></a>
                </li>
            </ul>
            <div class="box">
                <div class="box-body">
                    <div class="col-sm-10 col-sm-offset-1">
                        <canvas id="canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $this->endWidget(); ?>
<?php
$js = '
    var lineChartData = {
        labels : '.json_encode($chartData['labelX']).',
        datasets : [
            {
                label: "My First dataset",
                fillColor : "rgba(220,220,220,0.2)",
                strokeColor : "rgba(60,141,188,1)",
                pointColor : "rgba(60,141,188,1)",
                pointStrokeColor : "#fff",
                pointHighlightFill : "#fff",
                pointHighlightStroke : "rgba(220,220,220,1)",
                data : '.json_encode($chartData['labelY']).'
            }
        ]
    }
    var options= {
        responsive: true,
        scaleLabel: "<%=value%>次",
    }
    var ctx = document.getElementById("canvas").getContext("2d");
    window.myLine = new Chart(ctx).Line(lineChartData,options);
    
    $(".changeSelect").change(function(){
        $("form:first").submit();
    });
';
Yii::app()->clientScript->registerScript('selectAll',$js,CClientScript::POS_READY);
?>



