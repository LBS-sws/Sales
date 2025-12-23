<?php
$this->pageTitle=Yii::app()->name . ' - Clue Head Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
    'htmlOptions'=>array('enctype'=>'multipart/form-data'),
)); ?>
<style>
    .select2-container.select2-container-disabled .select2-choice {
        background-color: #ddd;
        border-color: #a8a8a8;
    }
    select.readonly{ pointer-events: none;}
    select[readonly]{pointer-events: none;}
    .select2.select2-container{ width: 100%!important;}
    .select2-container--default .select2-selection--multiple .select2-selection__choice{ padding: 0px 7px;}
    .select2-container .select2-selection--single{ height: 34px;}

    @media (min-width: 1200px){
        .col-lg-right{ padding-right: 0px;}
        .col-lg-left{ padding-left: 0px;}
    }
    .cust-search-div{ position: absolute;top: 0px;left: 0px;width: 800px;display: none;background: #fff;}
    .cust-search-div .list-group{ max-height: 200px;overflow: scroll;border: 1px solid #d2d6de;margin: 0px;}
    .cust-search-div .list-group-item{ padding: 5px 15px;border-radius: 0px;}
    .bat_phone_div_click.open{ }
    .bat_phone_div_click{ border-top:1px solid #d2d6de;padding-bottom:4px;}
    .bat_phone_div_click span{ display:inline-block;border:1px solid #d2d6de; padding:7px 12px;}
</style>
<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('clue','Clue Form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
                    'name'=>'btnAdd','id'=>'btnAdd','data-toggle'=>'modal','data-target'=>'#clueDialog'));
			}
		?>
		<?php
        if($model->scenario=="new"){
            $backUrl = Yii::app()->createUrl('clueHead/index');
        }else{
            $backUrl = Yii::app()->createUrl('clueHead/view',array("index"=>$model->id));
        }
        echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>$backUrl));
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('clueHead/save'))); 
			?>
<?php endif ?>
<?php if ($model->scenario=='edit'&&$model->lcu==Yii::app()->user->id): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
            <?php if ($model->scenario!='new'): ?>
                <div class="btn-group pull-right" role="group">
                    <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('clue','Clue History'), array(
                            'data-toggle'=>'modal','data-target'=>'#clueHistoryDialog',)
                    );
                    ?>
                </div>
            <?php endif ?>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
            <?php
            $this->renderPartial("//clue/clue_form",array("form"=>$form,"model"=>$model));
            ?>
		</div>
	</div>
</section>

<div class="cust-search-div" id="cust-search-div">
</div>
<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//clue/historylist',array("model"=>$model)); ?>
<?php
$js = Script::genDeleteData(Yii::app()->createUrl('clueHead/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = <<<EOF
function formatState(state) {
	var rtn = $('<span style="color:black">'+state.text+'</span>');
	return rtn;
}
EOF;
$js.= Script::genDatePicker(array(
    'entry_date',
));
if ($model->getScenario()=="new"){
$ajaxUrlName=Yii::app()->createUrl('clueHead/AjaxCustName');
$ajaxChangeUrlName=Yii::app()->createUrl('clueHead/AjaxChangeCustName');
$js.= <<<EOF
$('#cust_name').blur(function(){
    var thisObj = $(this);
	$.ajax({
		type: 'GET',
		url: '{$ajaxUrlName}?city='+$('#clue_city').val()+'&cust_name='+$(this).val(),
		dataType:'json',
		success: function(data) {
            thisObj.next('span').remove();
            thisObj.next('div').remove();
            if(data.state==1){
                thisObj.parents('.form-group').eq(0).addClass('has-error has-feedback');
                thisObj.after('<div class="text-danger">'+data.html+'</div>');
                thisObj.after('<span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>');
            }else{
                thisObj.parents('.form-group').eq(0).removeClass('has-error has-feedback');
            }
            $('#cust_class').html(data);
		},
		error: function(data) { // if error occured
			var x = 1;
		}
	});
});

$('body').on('click',function(e){
    if(e.target!=$('#cust_name').get(0)){
        $('#cust-search-div').hide();
    }
});
$('#cust_name').on('keyup click',function(){
    var thisObj = $(this);
    if($(this).val()==''){
        return false;
    }
	$.ajax({
		type: 'GET',
		url: '{$ajaxChangeUrlName}?cust_name='+$(this).val(),
		dataType:'json',
		success: function(data) {
		    if(data.state==1){
                var top=thisObj.offset().top+thisObj.outerHeight();
                var left=thisObj.offset().left;
                var width=thisObj.outerWidth();
                $('#cust-search-div').css({
                    top:top,
                    left:left,
                    display:'block',
                    width:width,
                    zIndex:99999
                });
                $('#cust-search-div').html(data.html);
		    }
		},
		error: function(data) { // if error occured
			var x = 1;
		}
	});
});
EOF;
}
Yii::app()->clientScript->registerScript('formatState',$js,CClientScript::POS_READY);
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>
<?php
echo TbHtml::button("",array("submit"=>"","class"=>"hide"));
$this->renderPartial('//clue/select_clue',array("actionUrl"=>Yii::app()->createUrl('clueHead/new')));
?>

<?php
$this->renderPartial('//clue/map_baidu',array(
    "model"=>$model,
));
?>
<?php
$this->renderPartial('//clue/nationalArea');
?>

