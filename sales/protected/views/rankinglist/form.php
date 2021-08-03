<?php
$this->pageTitle=Yii::app()->name . ' - Month Report';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'monthly-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('report','Sales ranking list'); ?></strong>
	</h1>
<!--
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Layout</a></li>
		<li class="active">Top Navigation</li>
	</ol>
-->
</section>
<div class="box"><div class="box-body">
        <div class="btn-group" role="group">
            <?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                'submit'=>Yii::app()->createUrl('rankinglist/index')));
            ?>
            <?php 
				$start = $model['scenario']['start_dt'].'-'.$model['scenario']['start_dt1'].'-01';
				$end = date('Y-m-t',strtotime($start));
				echo TbHtml::button('<span class="fa fa-download"></span> '.Yii::t('misc','Download'), array(
					'submit'=>Yii::app()->createUrl('rankinglist/download', array('start'=>$start, 'end'=>$end))));
            ?>
        </div>
</div></div>

<section class="content" >
    <div class="row"></div>
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo Yii::t('report','List of total amount of individual sales signing');?>(<?php echo $model['scenario']['start_dt']."/".$model['scenario']['start_dt1'];?>)</h3>
                </div>
                <div class="box-body">
                    <div id="salepeople" class="direct-chat-messages" style="height: 250px;">
                        <table class="table table-bordered small">
                            <tbody>
                            <tr><td><b><?php echo Yii::t('report','ranking');?></b></td><td><b><?php echo Yii::t('report','city');?></b></td><td><b><?php echo Yii::t('report','quyu');?></b></td><td><b><?php echo Yii::t('report','name');?></b></td><td><b><?php echo Yii::t('report','level');?></b></td><td><b><?php echo Yii::t('report','fuwumoney');?></b></td></tr>
                            <?php for ($i=0;$i<count($peopel);$i++){ ?>
                            <tr <?php if($i==0){ echo "style='color:#FF0000'";}if($i==1){ echo "style='color:#871F78'";}if($i==2){ echo "style='color:#0000FF'";}?>><td><?php echo $i+1;?></td><td><?php echo $peopel[$i]['city'];?></td><td><?php echo $peopel[$i]['quyu'];?></td><td><?php echo $peopel[$i]['name'];?></td><td> <img src="<?php echo Yii::app()->baseUrl."/images/".$peopel[$i]['level'].".png";?>" width="20px;"><?php echo $peopel[$i]['level'];?></td><td><?php echo $peopel[$i]['money'];?></td></tr>
                            <?php }?>
                            </tbody>
                        </table>
                    </div>
                </div>
<!--                <div class="box-footer">-->
<!--                    <small>每出现签单时即时刷新数据</small>-->
<!--                </div>-->
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo Yii::t('report','List of regional sales per capita order signing volume');?>(<?php echo $model['scenario']['start_dt']."/".$model['scenario']['start_dt1'];?>)</h3>
                </div>
                <div class="box-body">
                    <div id="salelist" class="direct-chat-messages" style="height: 250px;">
                        <table class="table table-bordered small">
                            <tbody>
                            <tr><td><b><?php echo Yii::t('report','ranking');?></b></td><td><b><?php echo Yii::t('report','city');?></b></td><td><b><?php echo Yii::t('report','quyu');?></b></td><td><b><?php echo Yii::t('report','sum');?></b></td><td><b><?php echo Yii::t('report','renjun');?></b></td></tr>
                            <?php for ($i=0;$i<count($list);$i++){ ?>
                                <tr <?php if($i==0){ echo "style='color:#FF0000'";}if($i==1){ echo "style='color:#871F78'";}if($i==2){ echo "style='color:#0000FF'";}?>><td><?php echo $i+1;?></td><td><?php echo $list[$i]['city'];?></td><td><?php echo $list[$i]['quyu'];?></td><td><?php echo $list[$i]['people'];?></td><td><?php echo $list[$i]['renjun'];?></td></tr>
                            <?php }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo Yii::t('report','List of regional sales per capita signed amount');?>(<?php echo $model['scenario']['start_dt']."/".$model['scenario']['start_dt1'];?>)</h3>
                </div>
                <div class="box-body">
                    <div id="salelists" class="direct-chat-messages" style="height: 250px;">
                        <table class="table table-bordered small">
                            <tbody>
                            <tr><td><b><?php echo Yii::t('report','ranking');?></b></td><td><b><?php echo Yii::t('report','city');?></b></td><td><b><?php echo Yii::t('report','quyu');?></b></td><td><b><?php echo Yii::t('report','sum');?></b></td><td><b><?php echo Yii::t('report','money');?></b></td></tr>
                            <?php for ($i=0;$i<count($lists);$i++){ ?>
                                <tr <?php if($i==0){ echo "style='color:#FF0000'";}if($i==1){ echo "style='color:#871F78'";}if($i==2){ echo "style='color:#0000FF'";}?>><td><?php echo $i+1;?></td><td><?php echo $lists[$i]['city'];?></td><td><?php echo $lists[$i]['quyu'];?></td><td><?php echo $lists[$i]['people'];?></td><td><?php echo $lists[$i]['money'];?></td></tr>
                            <?php }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo Yii::t('report','Sales ranking');?>(<?php echo $model['scenario']['start_dt']."/".$model['scenario']['start_dt1'];?>)</h3>
                </div>
                <div class="box-body">
                    <div id="salelists" class="direct-chat-messages" style="height: 250px;">
                        <table class="table table-bordered small">
                            <tbody>
                            <tr><td><b><?php echo Yii::t('report','ranking');?></b></td><td><b><?php echo Yii::t('report','level');?></b></td><td><b><?php echo Yii::t('report','name');?></b></td><td><b><?php echo Yii::t('report','rank');?></b></td><td><b><?php echo Yii::t('report','city');?></b></td><td><b><?php echo Yii::t('report','quyu');?></b></td></tr>
                            <?php for ($i=0;$i<count($rank);$i++){ ?>
                                <tr <?php if($i==0){ echo "style='color:#FF0000'";}if($i==1){ echo "style='color:#871F78'";}if($i==2){ echo "style='color:#0000FF'";}?>><td><?php echo $i+1;?></td><td> <img src="<?php echo Yii::app()->baseUrl."/images/".$rank[$i]['level'].".png";?>" width="20px;"><?php echo $rank[$i]['level'];?></td><td><?php echo $rank[$i]['name'];?></td><td><?php echo $rank[$i]['rank'];?></td><td><?php echo $rank[$i]['city'];?></td><td><?php echo $rank[$i]['quyu'];?></td></tr>
                            <?php }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
//	echo $form->hiddenField($model,'pageNum');
//	echo $form->hiddenField($model,'totalRow');
//	echo $form->hiddenField($model,'orderField');
//	echo $form->hiddenField($model,'orderType');
//?>
<?php $this->endWidget(); ?>

<?php
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>

