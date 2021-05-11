<?php
$this->pageTitle=Yii::app()->name . ' - Performance Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'Performance-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('sales','Rank sales history Form'); ?></strong>
	</h1>
</section>

<section class="content">

	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
<!--		--><?php //
//			if ($model->scenario!='new' && $model->scenario!='view') {
//				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
//					'submit'=>Yii::app()->createUrl('custtype/new')));
//			}
//		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('rankhistory/index_s')));
		?>

	</div>
	</div></div>

	<div class="box box-info">
        <style>
            .lists{
                overflow-y:hidden;
                overflow-x:auto;
                white-space: nowrap;
            }
            .cardlist{
                display: inline-table;
                vertical-align: top;
                width:230px;
                text-align: center;
            }
        </style>
		<div class="box-body" style="overflow:auto; ">
            <div> <h3>销售历程- <?php echo $model['rank'][0]['employee_name'];?></h3>
                <h5 ><?php echo $model['lic'];?></h5>
            </div>

			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
            <div class="lists">
                <?php if(sizeof($model['rank'])>=1){?>
                <?php  foreach ($model['rank'] as $a){?>
                    <div class="cardlist">
                        <!--                <img src="../images/--><?php //echo $a['rank'].'.png'?><!--">-->
                        <img src="<?php echo Yii::app()->baseUrl."/images/".$a['rank'].".png";?>">
                        <h2 style="margin-top: 0px;">第<?php echo $a['season'];?>赛季</h2>
                        <p><?php echo $a['rank'];?></p>
                        <span><?php $b=$a['month'];if(date('m', strtotime("$b"))%2==1){$b=date('Y-m', strtotime("$b +1 month"));}echo date('Y-m', strtotime("$b -1 month")).'至'.date('Y-m', strtotime("$b"));?></span>
                    </div>
                <?php }?>
                <?php }else{
                    echo "无数据";
                }?>
            </div>

		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = Script::genDeleteData(Yii::app()->createUrl('target/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


