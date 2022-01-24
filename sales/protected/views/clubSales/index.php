<?php
$this->pageTitle=Yii::app()->name . ' - ClubSales';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'clubSales-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Club sales'); ?></strong>
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
    <div class="box box-info">
        <div class="box-body">
            <div class="form-group">
                <?php echo $form->labelEx($model,'year',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->dropDownList($model, 'year',ClubSalesList::getYearList(),
                        array("id"=>"year"));
                    ?>
                </div>
                <?php echo $form->labelEx($model,'month_type',array('class'=>"col-sm-1 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->dropDownList($model, 'month_type',ClubSalesList::getMothTypeList(),
                        array("id"=>"month_type"));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo TbHtml::label(Yii::t("club","count sales"),'',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">

                    <div class="input-group">
                        <?php echo TbHtml::textField("count_sales",count($model->salesList),array("readonly"=>true))
                        ?>
                        <span class="input-group-btn">
                        <?php echo TbHtml::button("查看",array("data-toggle"=>"modal","data-target"=>"#staffdialog"))
                        ?>
                        </span>
                    </div><!-- /input-group -->
                </div>
            </div>
            <div class="tabbable">
                <ul class="nav nav-tabs" role="menu">
                    <li class="active">
                        <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('clubSales/index',array('year'=>$model->year,'month_type'=>$model->month_type));?>" ><?php echo Yii::t('club','ALL'); ?></a>
                    </li>
                    <?php
                    if(!empty($model->clubSetting)){
                        foreach ($model->clubSetting as $item){
                            $label = Yii::t("club",$item["name"])."<span>（{$item['people']}）</span>";
                            echo '<li>';
                            echo TbHtml::link($label,"#tab_{$item["name"]}",array("data-toggle"=>"tab","tabindex"=>-1));
                            //echo "<span>{$item['people']}</span>";
                            echo '</li>';
                        }
                    }
                    ?>
                </ul>
                <div class="tab-content">
                    <?php
                    echo $model->printAllTable();
                    ?>
                </div>
            </div>
            <?php
            /*
            $this->widget('ext.layout.ListPageWidget', array(
                'title'=>Yii::t('epc','ClubSales List'),
                'model'=>$model,
                'viewhdr'=>'//clubSales/_listhdr',
                'viewdtl'=>'//clubSales/_listdtl',
                'gridsize'=>'24',
                'height'=>'600',
                'search'=>array(
                    'name'
                ),
                'hasNavBar'=>false,
                'hasPageBar'=>false,
                'hasSearchBar'=>false,
            ));
            */
            ?>
        </div>
    </div>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
?>
<?php $this->endWidget(); ?>
<div id="staffdialog" role="dialog" tabindex="-1" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button class="close" data-dismiss="modal" type="button">×</button>
                <h4 class="modal-title">销售列表</h4>
            </div>
            <div class="modal-body">
                <?php
                echo ClubSalesList::tableHtml("aaa",$model->salesList);
                ?>
            </div>
            <div class="modal-footer">
                <button id="btnDeleteData" data-dismiss="modal" class="btn btn-primary" name="yt3" type="button">关闭</button>
            </div>
        </div>
    </div>
</div>
<?php
$link = Yii::app()->createUrl('clubSales/index');
$js="
$('#year,#month_type').on('change',function(){
    var year = $('#year').val();
    var month_type = $('#month_type').val();
    window.location.href='{$link}?year='+year+'&month_type='+month_type;
});
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
