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
            <div style="width: 1000px;">
                <div style="display:inline;font-size: 30px;">第<?php echo $model->season;?>赛季 <?php echo $model->date;?>月- <?php echo $model->name;?></div>
                <div style="display:inline;float: right;"><img style="width: 40px;height: 40px;" src="<?php echo Yii::app()->baseUrl."/images/".$model->rank_name.".png";?>"> <?php echo $model->rank_name;?></div>
            </div>
            <table class="tftable" border="1" style="width: 1000px;">
                <tr><td colspan="2"></td><td style="background-color: #FFBD9D"><b>继承分数 </b></td><td style="background-color: #FFBD9D"><?php echo $model->last_score;?></td></tr>
                <?php if($model->initial_score!=0){?>
                <tr><th>初始数据</th><th></th><th>初始数据对应得分</th><th></th></tr>
                <tr><td>入职满1/3月销售 </td><td><?php echo $model->ruzhi_day;?></td><td>入职满1/3月销售得分</td><td><?php echo $model->ruzhi;?></td></tr>
                <tr><td>3/7/30天内上传洗手间5步曲</td><td><?php echo $model->score_xsj_day;?></td><td>3/7/30天内上传洗手间5步曲得分</td><td><?php echo $model->score_xsj;?></td></tr>
                <tr><td>3/7/30天内上传灭虫5步曲</td><td><?php echo $model->score_mc_day;?></td><td>3/7/30天内上传灭虫5步曲得分</td><td><?php echo $model->score_mc;?></td></tr>
                <tr><td>15天内上传第3步曲视频</td><td><?php echo $model->score_3bq_day;?></td><td>15天内上传第3步曲视频得分</td><td><?php echo $model->score_3bq;?></td></tr>
                <tr><td></td><td></td><td style="background-color: #FFBD9D"><b>初始战斗值：</b></td><td style="background-color: #FFBD9D"><b><?php echo $model->initial_score;?></b></td></tr>
                <?php }?>
                <tr><th><b>当月数据 </b></th><th></th><th><b>当月数据对应战斗值</b></th><th></th></tr>
                <tr><td>销售每月平均每天拜访数量</td><td><?php echo $model->visit['sum'];?>条</td><td>销售每月平均每天拜访数量得分</td><td><?php echo $model->visit['score'];?></td></tr>
                <tr><td>销售每月IA，IB签单单数 (A)</td><td><?php echo $model->ia['sum'];?>张</td><td>销售每月IA，IB签单得分</td><td><?php echo $model->ia['score'];?></td></tr>
                <tr><td>销售每月IA，IB签单金额</td><td><?php echo $model->ia['money'];?></td><td></td><td></td></tr>
                <tr><td>销售每月飘盈香/净化机器租赁/隔油池签单单数 (B)</td><td><?php echo $model->pyx['sum'];?>张</td><td>销售每月飘盈香签单得分</td><td><?php echo $model->pyx['score'];?></td></tr>
                <tr><td>销售每月飘盈香/净化机器租赁/隔油池签单金额</td><td><?php echo $model->pyx['money'];?></td><td></td><td></td></tr>
                <tr><td>销售每月产品（不包括洗地易）签单金額</td><td><?php echo $model->cp['money'];?></td>  <td>销售每月产品（不包括洗地易）签单得分</td><td><?php echo $model->cp['score'];?></td></tr>
                <tr><td>销售每月洗地易签单单数 (D)</td><td><?php echo $model->xdy['sum'];?>张</td><td>销售每月洗地易签单得分</td><td><?php echo $model->xdy['score'];?></td></tr>
                <tr><td>销售每月洗地易签单金额</td><td><?php echo $model->xdy['money'];?></td><td></td><td></td></tr>
                <tr><td>销售每月甲醛签单单数 (D)</td><td><?php echo $model->jq['sum'];?>张</td><td>销售每月甲醛签单得分</td><td><?php echo $model->jq['score'];?></td></tr>
                <tr><td>销售每月甲醛签单金额</td><td><?php echo $model->jq['money'];?></td><td></td><td></td></tr>
                <tr><td>每月销售龙虎榜销售排名</td><td><?php echo $model->lhmoney['sum'];?></td><td>每月销售龙虎榜销售得分</td><td><?php echo $model->lhmoney['score'];?></td></tr>
                <tr><td>每月销售龙虎榜城市人均签单量排名</td><td><?php echo $model->lhcity['sum'];?></td><td>每月销售龙虎榜城市人均签单量排名分数</td><td><?php echo $model->lhcity['score'];?></td></tr>
                <tr><td>每月销售龙虎榜城市人均签单金额排名</td><td><?php echo $model->lhsum['sum'];?></td><td>每月销售龙虎榜城市人均签单金额排名分数</td><td><?php echo $model->lhsum['score'];?></td></tr>
                <tr><td></td><td><b></b></td><td style="background-color: #FFBD9D"><b>当月所有战斗值</b></td><td style="background-color: #FFBD9D"><?php echo $model->now;?> </td></tr>
                <tr><th colspan="4">当月数据对应分数倍数（当月所有得分*以下倍数）</th></tr>
                <tr><td>地方销售人员/地方区域个数</td><td><?php echo $model->sales['sum'];?></td><td>地方销售人员/地方区域个数对应倍数</td><td><?php echo $model->sales['score'];?></td></tr>
                <tr><td>销售组别类型（餐饮组/商业组)</td><td><?php echo $model->food['name'];?></td><td>销售组别类型（餐饮组/商业组）对应倍数</td><td><?php echo $model->food['score'];?></td></tr>
                <tr><td>销售岗位级别对应倍数</td><td><?php echo $model->fjl;?></td><td>销售每月平均每天拜访数量对应倍数</td><td><?php echo $model->visit['coefficient'];?></td></tr>
                <tr><td>城市规模级别</td><td><?php echo $model->city_jb;?></td><td>城市规模级别对应倍数</td><td><?php echo $model->city_xs;?></td></tr>
                <tr><td></td><td></td><td style="background-color: #FFBD9D"><b>当月所有得分乘以倍数后（若当月所有得分为负数则不会乘以以上倍数）</b></td><td style="background-color: #FFBD9D"><b><?php echo $model->all_score;?></b> </td></tr>
                 <tr><td style="background-color: #FFBD9D"><b>当月得分对应段位</b></td><td style="background-color: #FFBD9D"><b><?php echo $model->rank_name;?></b></td><td style="background-color: #FFBD9D"><b>当前赛季战斗值</b></td><td style="background-color: #FFBD9D"><b><?php echo $model->now_score;?></b></td></tr>
            </table>
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


