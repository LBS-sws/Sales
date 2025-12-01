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
		<strong><?php echo Yii::t('code','Performance Form'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('performance/index')));
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('performance/save')));
			?>
<?php endif ?>
<?php if ($model->scenario=='edit'): ?>
<!--	--><?php //echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
//			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
//		);
//	?>
<?php endif ?>
	</div>
	</div></div>
    <div class="box">
        <div class="box-body">
            <div class="btn-group text-info" role="group">
                <p><b>注：</b></p>
                <p style="text-indent: 15px;"><?php echo Yii::t('dialog','Zhu1'); ?></p>
                <p style="text-indent: 15px;"><?php echo Yii::t('dialog','Zhu2'); ?></p>
            </div>
        </div>
    </div>
	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'year',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->textField($model, 'year',
						array('size'=>10,'maxlength'=>10,'readonly'=>('readonly'))
					); ?>
				</div>
			</div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'month',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'month',
                        array('size'=>10,'maxlength'=>10,'readonly'=>('readonly'))
                    ); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'sum',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'sum',
                        array('size'=>4,'min'=>0,'readonly'=>($model->getReadonly()))
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'sums',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'sums',
                        array('size'=>4,'min'=>0,'readonly'=>($model->getReadonly()))
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'spanning',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'spanning',
                        array('size'=>4,'min'=>0,'readonly'=>($model->getReadonly()))
                    ); ?>
                </div>
                <div style="color: red"><?php echo Yii::t('report','li'); ?></div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'otherspanning',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'otherspanning',
                        array('size'=>4,'min'=>0,'readonly'=>($model->getReadonly()))
                    ); ?>
                </div>
            </div>

            <div class="form-group">
                    <?php echo $form->labelEx($model,'business_spanning',array('class'=>"col-sm-2 control-label")); ?>
                    <div class="col-sm-2">
                        <?php echo $form->numberField($model, 'business_spanning',
                            array('size'=>4,'min'=>0,'readonly'=>($model->getReadonly()))
                        ); ?>
                    </div>
                    <div style="color: red"><?php echo Yii::t('report','li'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo $form->labelEx($model,'business_otherspanning',array('class'=>"col-sm-2 control-label")); ?>
                    <div class="col-sm-2">
                        <?php echo $form->numberField($model, 'business_otherspanning',
                            array('size'=>4,'min'=>0,'readonly'=>($model->getReadonly()))
                        ); ?>
                    </div>
                </div>

                <div class="form-group">
                        <?php echo $form->labelEx($model,'restaurant_spanning',array('class'=>"col-sm-2 control-label")); ?>
                        <div class="col-sm-2">
                            <?php echo $form->numberField($model, 'restaurant_spanning',
                                array('size'=>4,'min'=>0,'readonly'=>($model->getReadonly()))
                            ); ?>
                        </div>
                        <div style="color: red"><?php echo Yii::t('report','li'); ?></div>
                    </div>
                    <div class="form-group">
                        <?php echo $form->labelEx($model,'restaurant_otherspanning',array('class'=>"col-sm-2 control-label")); ?>
                        <div class="col-sm-2">
                            <?php echo $form->numberField($model, 'restaurant_otherspanning',
                                array('size'=>4,'min'=>0,'readonly'=>($model->getReadonly()))
                            ); ?>
                        </div>
            </div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = Script::genDeleteData(Yii::app()->createUrl('custtype/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


