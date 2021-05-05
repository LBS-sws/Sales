<?php
$this->pageTitle=Yii::app()->name . ' - RankScore Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'RankScore-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('rank','RankScore Form'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('rankscore/index')));
		?>
	</div>
	</div></div>

	<div class="box box-info">
        <div class="box-body" style=" overflow-x:auto; overflow-y:auto;">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>

            <style type="text/css">
                .tftable {font-size:12px;color:#333333;width:100%;border-width: 1px;border-color: #729ea5;border-collapse: collapse;}
                .tftable th {font-size:12px;background-color:#acc8cc;border-width: 1px;padding: 8px;border-style: solid;border-color: #729ea5;text-align:left;}
                .tftable tr {background-color:#ffffff;}
                .tftable td {font-size:12px;border-width: 1px;padding: 8px;border-style: solid;border-color: #729ea5;}
                .tftable tr:hover {background-color:#ffff99;}
            </style>
            <h3><?php echo $model->season;?>  <?php echo $model->message;?></h3>
            <?php if($model->ranking){ ?>
            <table class="tftable" border="1" style="width: 1000px;">
                <tr><th>排名</th><th>地区</th><th>员工</th><th>战斗值</th><th>最新段位</th></tr>
                <?php $i=1; foreach ($model->ranking as $a){?>
                <tr><td><?php echo $i;?></td><td><?php echo $a['city'];?></td><td><?php echo $a['name'];?></td><td><?php echo $a['rank'];?></td><td><?php echo $a['level'];?></td></tr>
                <?php $i=$i+1;}
                ?>
            </table>
        <?php  }else{
                echo '无数据';
            }?>
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


