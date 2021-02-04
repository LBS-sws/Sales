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
		<strong><?php echo Yii::t('sales','Rank sales Form'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('rank/index_s')));
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
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>

            <style type="text/css">
                .tftable {font-size:12px;color:#333333;width:100%;border-width: 1px;border-color: #729ea5;border-collapse: collapse;}
                .tftable th {font-size:12px;background-color:#acc8cc;border-width: 1px;padding: 8px;border-style: solid;border-color: #729ea5;text-align:left;}
                .tftable tr {background-color:#ffffff;}
                .tftable td {font-size:12px;border-width: 1px;padding: 8px;border-style: solid;border-color: #729ea5;}
                .tftable tr:hover {background-color:#ffff99;}
            </style>
            <h3>第<?php echo $model->season;?>赛季 - <?php echo $model->name;?></h3>
            <table class="tftable" border="1" style="width: 1000px;" ">
                <tr><th>当前段位：</th><th><?php echo $model->rank_name;?></th><th>当前赛季累计到上月分数</th><th><?php echo $model->rank;?></th></tr>
                <tr><td><b>当月数据 ：</b></td><td></td><td><b>当月数据对应得分 ：</b></td><td></td></tr>
                <tr><td>销售每月平均每天拜访数量</td><td><?php echo $model->visit['sum'];?>条</td><td>销售每月平均每天拜访数量得分</td><td><?php echo $model->visit['score'];?></td></tr>
                <tr><td>销售每月IA，IB签单单数 (A)</td><td><?php echo $model->ia['sum'];?>张</td><td>销售每月IA，IB签单得分</td><td><?php echo $model->ia['score'];?></td></tr>
                <tr><td>销售每月IA，IB签单金额</td><td><?php echo $model->ia['money'];?></td><td></td><td></td></tr>
                <tr><td>销售每月飘盈香签单单数 (B)</td><td><?php echo $model->pyx['sum'];?>张</td><td>销售每月飘盈香签单得分</td><td><?php echo $model->pyx['score'];?></td></tr>
                <tr><td>销售每月飘盈香签单金额</td><td><?php echo $model->pyx['money'];?></td><td></td><td></td></tr>
                <tr><td>销售每月产品（不包括洗地易）签单单数 (C)</td><td><?php echo $model->cp['sum'];?>张</td><td>销售每月产品（不包括洗地易）签单得分</td><td><?php echo $model->cp['score'];?></td></tr>
                <tr><td>销售每月产品（不包括洗地易）签单得分</td><td><?php echo $model->cp['money'];?></td><td></td><td></td></tr>
                <tr><td>销售每月洗地易/甲醛签单单数 (D)</td><td><?php echo $model->jq['sum'];?>张</td><td>销售每月洗地易/甲醛签单得分</td><td><?php echo $model->jq['score'];?></td></tr>
                <tr><td>销售每月洗地易/甲醛签单金额</td><td><?php echo $model->jq['money'];?></td><td></td><td></td></tr>
                <tr><td>每月销售龙虎榜销售排名</td><td><?php echo $model->lhmoney['sum'];?></td><td>每月销售龙虎榜销售得分</td><td><?php echo $model->lhmoney['score'];?></td></tr>
                <tr><td>每月销售龙虎榜城市人均签单量排名</td><td><?php echo $model->lhcity['sum'];?></td><td>每月销售龙虎榜城市人均签单量排名分数</td><td><?php echo $model->lhcity['score'];?></td></tr>
                <tr><td>每月销售龙虎榜城市人均签单金额排名</td><td><?php echo $model->lhsum['sum'];?></td><td>每月销售龙虎榜城市人均签单金额排名分数</td><td><?php echo $model->lhsum['score'];?></td></tr>
                <tr><td><b>当月所有得分</b></td><td><b><?php echo $model->now;?> </b></td><td></td><td></td></tr>
                <tr><th colspan="4">当月数据对应分数倍数（当月所有得分*以下倍数）：</th></tr>
                <tr><td>地方销售人员/整体区比例</td><td><?php echo $model->sales['sum'];?></td><td>地方销售人员/整体区比例对应倍数</td><td><?php echo $model->sales['score'];?></td></tr>
                <tr><td>销售组别类型（餐饮组/商业组)</td><td><?php echo $model->food['name'];?></td><td>销售组别类型（餐饮组/商业组）对应倍数</td><td><?php echo $model->food['score'];?></td></tr>
                <tr><td>销售岗位级别对应倍数</td><td><?php echo $model->fjl;?></td><td>销售每月平均每天拜访数量对应倍数</td><td><?php echo $model->visit['coefficient'];?></td></tr>
                <tr><td><b>当前赛季累计到今月应得分数</b></td><td><b><?php echo $model->all;?></b> </td><td><b>当月得分对应段位</b></td><td><b><?php echo $model->rank_name;?></b></td></tr>
            </table>



            <!--			<div class="form-group">-->
<!--				--><?php //echo $form->labelEx($model,'employee_name',array('class'=>"col-sm-2 control-label")); ?>
<!--				<div class="col-sm-2">-->
<!--					--><?php //echo $form->textField($model, 'employee_name',
//						array('size'=>10,'maxlength'=>10,'readonly'=>('readonly'))
//					); ?>
<!--				</div>-->
<!--			</div>-->
<!---->
<!--            <div class="form-group">-->
<!--                --><?php //echo $form->labelEx($model,'sale_day',array('class'=>"col-sm-2 control-label")); ?>
<!--                <div class="col-sm-2">-->
<!--                    --><?php //echo $form->textField($model, 'sale_day',
//                        array('size'=>10,'maxlength'=>10,)
//                    ); ?>
<!--                </div>-->
<!--            </div>-->
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


