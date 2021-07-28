<?php
$this->pageTitle=Yii::app()->name . ' - Five Steps Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'fivestep-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
'htmlOptions'=>array('enctype'=>'multipart/form-data'),
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('sales','Five Steps Form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
/*
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('fivestep/new')));
			}
*/
		?>
		<?php 
//			echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
//				'submit'=>Yii::app()->createUrl('fivestep/index'))); 
			echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'id'=>'btnBack')); 
		?>
<?php if (!$model->isReadOnly()): ?>
			<?php 
//				echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
//					'id'=>'btnSave', 'submit'=>Yii::app()->createUrl('fivestep/save'))); 
				echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
					'id'=>'btnSave')); 
			?>
<?php endif ?>
<?php if ($model->scenario!='new' && $model->scenario!='view'
			&& ($model->isManagerRight() || $model->username==Yii::app()->user->id) && empty($model->mgr_score_user) && empty($model->dir_score_user)
	   ): 
?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php 
				echo $form->hiddenField($model, 'scenario'); 
				echo $form->hiddenField($model, 'username');
				echo $form->hiddenField($model, 'id');
				echo $form->hiddenField($model, 'filename');
				echo $form->hiddenField($model, 'filetype');
				echo $form->hiddenField($model, 'status');
				echo $form->hiddenField($model, 'city');
				echo TbHtml::button('dummyButton', array('style'=>'display:none','disabled'=>true,'submit'=>'#',))
			?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'rec_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php echo $form->textField($model, 'rec_dt', array('class'=>'form-control pull-right',	'readonly'=>true,)); ?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'staff_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textField($model, 'staff_name', array('readonly'=>true)); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'post_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-4">
					<?php echo $form->textField($model, 'post_name', array('readonly'=>true)); ?>
				</div>

				<?php echo $form->labelEx($model,'dept_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-4">
					<?php echo $form->textField($model, 'dept_name', array('readonly'=>true)); ?>
				</div>
			</div>


			<div class="form-group">
				<?php echo $form->labelEx($model,'step',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-1">
					<?php
						$typelist = $model->getStepList();
						$scoreright = ($model->isManagerRight() || $model->isDirectorRight()); 
						if ($model->isReadOnly() ||  $model->username!=Yii::app()->user->id || !empty($model->mgr_score) || !empty($model->dir_score)) {
							echo $form->hiddenField($model, 'step');
							echo TbHtml::textField('step', $typelist[$model->step], array('readonly'=>true));
						} else {
							echo $form->dropDownList($model, 'step', $typelist); 
						}
					?>
				</div>
<!--                <div class="form-group">-->
                    <?php echo $form->labelEx($model,'five_type',array('class'=>"col-sm-2 control-label")); ?>
                    <div class="col-sm-1">
                        <?php
                        $typelist = array(
                            0=>Yii::t('misc','Insecticidal'),
                            1=>Yii::t('misc','Restroom'),
                            2=>Yii::t('misc','Third'),
                            3=>Yii::t('misc','Air Purifier'),
                        );
                        $scoreright = ($model->isManagerRight() || $model->isDirectorRight());
                        if ($model->isReadOnly() ||  $model->username!=Yii::app()->user->id || !empty($model->mgr_score) || !empty($model->dir_score)) {
                            echo $form->hiddenField($model, 'five_type');
                            echo TbHtml::textField('five_type', $typelist[$model->five_type], array('readonly'=>true));
                        } else {
                            echo $form->dropDownList($model, 'five_type', $typelist);
                        }
                        ?>
                    </div>
<!--                </div>-->
				<div class="col-sm-5">
					<div class="box box-solid bg-gray"><div id="stepdescdiv" class="box-body"></div></div>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'sup_score',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php
						echo $form->numberField($model, 'sup_score', 
							array('size'=>5,'min'=>1,'max'=>100,
                                'readonly'=>($model->isReadOnly() ||  $model->username==Yii::app()->user->id || !$model->isSuperRight() || $model->isIncompetent($model)),
							)
						); 
						echo $form->hiddenField($model, 'sup_score_user');
						echo $form->hiddenField($model, 'sup_score_dt');
					?>
				</div>
                <?php
                if(Yii::app()->user->validFunction('CN05')){
                    echo TbHtml::button(Yii::t('misc','Asked redo'), array('name'=>'btnSignQc','id'=>'btnSignQc',));
                }

                ?>
<?php if (!empty($model->sup_score_user)): ?>
				<div class="col-sm-4">
					<?php
						$user = User::model()->find('username=?',array($model->sup_score_user));
						$x = $user->disp_name.' ('.$model->sup_score_dt.')';
						echo TbHtml::label($x,'FivestepForm_sup_score');
					?>
				</div>
<?php endif ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'sup_remarks',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'sup_remarks', 
						array('rows'=>3,'cols'=>60,'maxlength'=>5000,
                            'readonly'=>($model->isReadOnly() ||  $model->username==Yii::app()->user->id || !$model->isSuperRight() ||$model->isIncompetent($model)),
					)); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'mgr_score',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php
						echo $form->numberField($model, 'mgr_score', 
							array('size'=>5,'min'=>1,'max'=>100,
                                'readonly'=>($model->isReadOnly() ||  $model->username==Yii::app()->user->id || !$model->isManagerRight() || $model->isIncompetent($model)),
							)
						); 
						echo $form->hiddenField($model, 'mgr_score_user');
						echo $form->hiddenField($model, 'mgr_score_dt');
					?>
				</div>
                <?php
                if(Yii::app()->user->validFunction('CN01')){
                    echo TbHtml::button(Yii::t('misc','Asked redo'), array('name'=>'btnSignQc2','id'=>'btnSignQc2',));
                }

                ?>
<?php if (!empty($model->mgr_score_user)): ?>
				<div class="col-sm-4">
					<?php
						$user = User::model()->find('username=?',array($model->mgr_score_user));
						$x = $user->disp_name.' ('.$model->mgr_score_dt.')';
						echo TbHtml::label($x,'FivestepForm_mgr_score');
					?>
				</div>
<?php endif ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'mgr_remarks',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'mgr_remarks', 
						array('rows'=>3,'cols'=>60,'maxlength'=>5000,
                            'readonly'=>($model->isReadOnly() ||  $model->username==Yii::app()->user->id || !$model->isManagerRight() || $model->isIncompetent($model)),
					)); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'dir_score',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php
						echo $form->numberField($model, 'dir_score', 
							array('size'=>5,'min'=>1,'max'=>100,
                                'readonly'=>($model->isReadOnly() ||  $model->username==Yii::app()->user->id || !$model->isDirectorRight() || $model->isIncompetent($model)),
							)
						); 
						echo $form->hiddenField($model, 'dir_score_user');
						echo $form->hiddenField($model, 'dir_score_dt');
					?>
				</div>
                <?php
                if(Yii::app()->user->validFunction('CN02')){
                    echo TbHtml::button(Yii::t('misc','Asked redo'), array('name'=>'btnSignQc3','id'=>'btnSignQc3',));
                }

                ?>
<?php if (!empty($model->dir_score_user)): ?>
				<div class="col-sm-4">
					<?php
						$user = User::model()->find('username=?',array($model->dir_score_user));
						$x = $user->disp_name.' ('.$model->dir_score_dt.')';
						echo TbHtml::label($x,'FivestepForm_dir_score');
					?>
				</div>
<?php endif ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'dir_remarks',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'dir_remarks', 
						array('rows'=>3,'cols'=>60,'maxlength'=>5000,
							'readonly'=>($model->isReadOnly() ||  $model->username==Yii::app()->user->id || !$model->isDirectorRight() || $model->isIncompetent($model)),
					)); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'filename',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2" id="mediafile">
					<?php
						if (empty($model->filename)) {
							echo $form->fileField($model, 'filename', array('accept'=>'video/*, audio/*'));
						}
					?>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-7">
					<video width='300' controls id='video_here'>
						<?php
/*
							if (!empty($model->filename)) {
								$content = $model->getMediaFile();
								$mediatype = $model->filetype;
								echo "<source src='$content' type='$mediatype'>";
							}
*/
						?>
						Your browser does not support HTML5 video.
					</video>
				</div>
			</div>
			
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
	$this->widget('bootstrap.widgets.TbModal', array(
		'id'=>'uploadProgress',
		'content'=>'<p class="text-center">'.Yii::t('sales','File being upload').' ... <span id="progress_pct">0%</span></p>',
		'size'=>' modal-sm',
		'show'=>false,
		'backdrop'=>'static',
		'keyboard'=>false,
	));

	$this->widget('bootstrap.widgets.TbModal', array(
		'id'=>'dialogMessage',
		'header'=>'<span id="dialog_title"></span>',
		'content'=>'<p id="dialog_msg"></p>',
		'footer'=>array(
					TbHtml::button(Yii::t('dialog','OK'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
				),
		'show'=>false,
	));
?>

<?php
$js = Script::genDeleteData(Yii::app()->createUrl('fivestep/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);

$js = <<<EOF
$(document).on('change','#FivestepForm_filename',function() {
	var fileUrl = URL.createObjectURL(this.files[0]);
	$('#video_here').attr('src',fileUrl);
});
EOF;
Yii::app()->clientScript->registerScript('video',$js,CClientScript::POS_READY);

if (!empty($model->filename)) {
	$link = Yii::app()->createAbsoluteUrl("fivestep");
	$index = $model->id;
	$msg = Yii::t('sales','Loading...');
	$link2 = TbHtml::link(Yii::t('sales','Download Media'), Yii::app()->createUrl('fivestep/downloadmedia',array('index'=>$model->id)));
	$js = <<<EOF
$(document).ready(function() {
	var data = "index=$index";
	var link = "$link"+"/showmedia";
	$.ajax({
		type: 'GET',
		url: link,
		data: data,
		beforeSend: function() {
			$("#mediafile").html('$msg');
		},
		success: function(data) {
			$("#video_here").html(data);
			$("#mediafile").html('$link2');
		},
		error: function(data) { // if error occured
			alert("Error occured.please try again");
		},
		dataType:'html'
	});
});
EOF;
	Yii::app()->clientScript->registerScript('mediaview',$js,CClientScript::POS_READY);
}

$link = Yii::app()->createUrl('fivestep/ajaxsave');
$link2 = Yii::app()->createAbsoluteUrl('fivestep/edit');
$title_v = Yii::t('dialog','Validation Message');
$title_e = Yii::t('dialog','Error');
$msg_err = Yii::t('sales','Upload Error. (Please be reminded that file should not be larger than 30MB)');
$js = <<<EOF
$('#btnSave').on('click', function() {
if($("#FivestepForm_sup_score").val() == "-1" && $("#FivestepForm_sup_remarks").val()==""){
alert("请输入销售经理意见");
   throw SyntaxError(); //如果验证不通过，则不执行后面
    }
    if($("#FivestepForm_mgr_score").val() == "-1" && $("#FivestepForm_mgr_remarks").val()==""){
alert("请输入总经理意见");
   throw SyntaxError(); //如果验证不通过，则不执行后面
    }
if($("#FivestepForm_dir_score").val() == "-1" && $("#FivestepForm_dir_remarks").val()==""){
alert("请输入总监意见");
   throw SyntaxError(); //如果验证不通过，则不执行后面
    }
//	var form = document.getElementById('fivestep-form');
	var form = $('form')[0];
	var formdata = new FormData(form);
	$.ajax({
		type: 'POST',
		url: '$link',
		data: formdata,
		dataType: 'json',
		mimeType: 'multipart/form-data',
		contentType: false,
		processData: false,
		xhr: function() {
			var xhr = new window.XMLHttpRequest();
			xhr.upload.addEventListener("progress", function(evt) {
				if (evt.lengthComputable) {
					var percentComplete = evt.loaded / evt.total;
					percentComplete = parseInt(percentComplete * 100);

					$('#progress_pct').html(percentComplete+'%');
//					if (percentComplete === 100) {
//					}
				}
			}, false);
			return xhr;
		},
		beforeSend: function() {
			var type = $('#FivestepForm_scenario').val();
			if (type=='new') $('#uploadProgress').modal('show');
		},
		success: function(result) {
			$('#uploadProgress').modal('hide');
			switch (result.code) {
				case 1:
					window.location = '$link2'+'?index='+result.id;
					break;
				case 2:
					$('#dialog_title').html('$title_v');
					$('#dialog_msg').html(result.message);
					$('#dialogMessage').modal('show');
					break;
				case 3:
					$('#dialog_title').html('$title_e');
					$('#dialog_msg').html(result.message);
					$('#dialogMessage').modal('show');
					break;
				default:
				alert("$msg_err");
			}
		},
		error: function(result) { // if error occured
			alert("$msg_err");
		}
	});
});
EOF;
Yii::app()->clientScript->registerScript('ajaxsave',$js,CClientScript::POS_READY);

$step_desc = json_encode($model->getStepDesc());
$txt1 = Yii::t('sales','媒体');
$js = <<<EOF
var obj = JSON.parse('$step_desc');
var choice = $('#FivestepForm_step').val();
var content = '<dl><dt>'+obj[choice].desc+'</dt><dd><ul><li>$txt1: '+obj[choice].type+'</li><li>'+obj[choice].detail+'</li></ul></dd></dl>';
$('#stepdescdiv').html(content);

$('#FivestepForm_step').on('change', function() {
	var obj = JSON.parse('$step_desc');
	var choice = $(this).val();
	var content = '<dl><dt>'+obj[choice].desc+'</dt><dd><ul><li>$txt1: '+obj[choice].type+'</li><li>'+obj[choice].detail+'</li></ul></dd></dl>';
	$('#stepdescdiv').html(content);
});
EOF;
Yii::app()->clientScript->registerScript('stepdesc',$js,CClientScript::POS_READY);

$link3 = Yii::app()->createAbsoluteUrl('fivestep/index');
$js = <<<EOF
$('#btnBack').on('click', function() {
	window.location = '$link3';
});

$('#btnSignQc').on('click',function(){
$("#FivestepForm_sup_score").val("-1");
});
$('#btnSignQc2').on('click',function(){
$("#FivestepForm_mgr_score").val("-1");
});
$('#btnSignQc3').on('click',function(){
$("#FivestepForm_dir_score").val("-1");
});
EOF;
Yii::app()->clientScript->registerScript('backurl',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


