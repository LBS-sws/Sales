<?php
$this->pageTitle=Yii::app()->name . ' - Clue Store Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    .select2-container.select2-container-disabled .select2-choice {
        background-color: #ddd;
        border-color: #a8a8a8;
    }
    select.readonly{ pointer-events: none;}
    select[readonly]{pointer-events: none;}
    .store_detail .form-group{ margin-bottom: 5px;}
    .select2.select2-container{ width: 100%!important;}
    .select2-container--default .select2-selection--multiple .select2-selection__choice{ padding: 0px 7px;}
    .select2-container .select2-selection--single{ height: 34px;}

    @media (min-width: 1200px){
        .col-lg-right{ padding-right: 0px;}
        .col-lg-left{ padding-left: 0px;}
    }
</style>
<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('clue','store form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php
        $type = CGetName::getSessionByStore();
        switch ($type){
            case 1:
                echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('clueStore/storeList',array("clue_id"=>$model->clue_id))));
                break;
            case 2:
                echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('clueStore/index')));
                break;
            case 4://客户列表
                echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('clueStore/storeList',array("clue_id"=>$model->clue_id))));
                break;
            case 5://客户详情
                echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('clientHead/view',array("index"=>$model->clue_id))));
                break;
            default:
                echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('clueHead/view',array("index"=>$model->clue_id))));
        }
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('clueStore/save',array("type"=>$type))));
			?>
<?php endif ?>
<?php if ($model->scenario=='edit'): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
                <div class="btn-group pull-right" role="group">
                    <?php if ($model->scenario!='new'): ?>
                        <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('clue','Clue Store History'), array(
                                'data-toggle'=>'modal','data-target'=>'#clueHistoryDialog',)
                        );
                        ?>
                    <?php endif ?>
                    <?php if (Yii::app()->user->validRWFunction('CM04')): ?>
                        <?php echo TbHtml::button('<span class="fa fa-edit"></span> '.Yii::t('clue','update'), array(
                            'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                            'submit'=>Yii::app()->createUrl('clueStore/edit',array("index"=>$model->id))));
                        ?>
                    <?php endif ?>
                    <?php if ($model->scenario=='view' && Yii::app()->user->validRWFunction('CM04')): ?>
                        <?php echo TbHtml::button('<span class="fa fa-share"></span> 转移门店', array(
                            'color'=>TbHtml::BUTTON_COLOR_WARNING,
                            'data-toggle'=>'modal','data-target'=>'#transferStoreDialog'));
                        ?>
                    <?php endif ?>
                </div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
            <?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'clue_id'); ?>

            <div class="store_detail">
                <?php $this->renderPartial('//clueStore/storeForm',array("model"=>$model,"form"=>$form)); ?>
            </div>
		</div>
	</div>
</section>
<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//clueStore/bindingContDialog',array("model"=>$model)); ?>
<?php $this->renderPartial('//clue/historylist',array("model"=>$model,"type"=>2)); ?>
<?php
$js = <<<EOF
EOF;
Yii::app()->clientScript->registerScript('select2_1',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


<section class="content" style="padding-top: 0px;">
    <div class="box box-info">
        <div class="box-body">
            <?php
            $tabs=array();
            //合约信息
            $tabs[] = array(
                'label'=>Yii::t('clue',"client contract"),
                'content'=>$this->renderPartial('//clueStore/dv_contract',array("model"=>$model),true),
                'active'=>true,
                "id"=>"store_dv_contract"
            );
            //联系人
            $tabs[] = array(
                'label'=>Yii::t('clue',"client person"),
                'content'=>$this->renderPartial('//clueStore/dv_person',array("model"=>$model),true),
                'active'=>false,
                "id"=>"store_dv_person"
            );
            echo TbHtml::tabbableTabs($tabs);
            ?>
        </div>
    </div>
</section>
<?php
$this->renderPartial('//clue/openForm');
?>
<?php
$this->renderPartial('//clue/map_baidu',array(
    "model"=>$model,
));
?>
<?php
$this->renderPartial('//clueStore/transferStoreDialog',array("model"=>$model));
?>
