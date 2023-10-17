<?php
$this->pageTitle=Yii::app()->name . ' - SignedRate Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'SignedRate-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    .click-th,.click-tr,.td_detail{ cursor: pointer;}
    .click-tr>.fa:before{ content: "\f062";}
    .click-tr.show-tr>.fa:before{ content: "\f063";}
    .table-fixed{ table-layout: fixed;}
    .radio-inline,select{ opacity: 0.6;pointer-events: none;}
    .form-group{ margin-bottom: 0px;}
    .table-fixed>thead>tr>th,.table-fixed>tfoot>tr>td,.table-fixed>tbody>tr>td{ text-align: center;vertical-align: middle;font-size: 12px;border-color: #333;}
    .table-fixed>tfoot>tr>td,.table-fixed>tbody>tr>td{ text-align: right;}
    .table-fixed>thead>tr>th.header-width{ height: 0px;padding: 0px;overflow: hidden;border-width: 0px;line-height: 0px;}
</style>

<section class="content-header">
	<h1>
        <strong><?php echo Yii::t('app','Signed conversion rate'); ?></strong>
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
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('signedRate/index')));
		?>
	</div>
            <div class="btn-group pull-right" role="group">
                <?php echo TbHtml::button('<span class="fa fa-download"></span> '.Yii::t('dialog','Download'), array(
                    'submit'=>Yii::app()->createUrl('signedRate/downExcel')));
                ?>
            </div>
	</div></div>

    <div class="box">
        <div id="yw0" class="tabbable">
            <div class="box-info" >
                <div class="box-body" >
                    <div class="col-lg-5">
                        <div class="form-group">
                            <?php echo $form->labelEx($model,'search_type',array('class'=>"col-lg-5 control-label")); ?>
                            <div class="col-lg-7">
                                <?php echo $form->inlineRadioButtonList($model, 'search_type',SignedRateForm::getSelectType(),
                                    array('readonly'=>true,'id'=>'search_type')
                                ); ?>
                            </div>
                        </div>
                        <div id="search_div">
                            <div data-id="1" <?php if ($model->search_type!=1){ echo "style='display:none'"; } ?>>
                                <div class="form-group">
                                    <?php echo $form->labelEx($model,'search_year',array('class'=>"col-lg-5 control-label")); ?>
                                    <div class="col-lg-5">
                                        <?php echo $form->dropDownList($model, 'search_year',SignedRateForm::getSelectYear(),
                                            array('readonly'=>true,'id'=>'year_one')
                                        ); ?>
                                    </div>
                                </div>
                            </div>
                            <div data-id="2" <?php if ($model->search_type!=2){ echo "style='display:none'"; } ?>>
                                <div class="form-group">
                                    <?php echo $form->labelEx($model,'search_year',array('class'=>"col-lg-5 control-label")); ?>
                                    <div class="col-lg-5">
                                        <?php echo $form->dropDownList($model, 'search_year',SignedRateForm::getSelectYear(),
                                            array('readonly'=>true,'id'=>'year_two')
                                        ); ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo $form->labelEx($model,'search_month',array('class'=>"col-lg-5 control-label")); ?>
                                    <div class="col-lg-5">
                                        <?php echo $form->dropDownList($model, 'search_month',SignedRateForm::getSelectMonth(),
                                            array('readonly'=>true)
                                        ); ?>
                                    </div>
                                </div>
                            </div>
                            <div data-id="3" <?php if ($model->search_type!=3){ echo "style='display:none'"; } ?>>
                                <div class="form-group">
                                    <?php echo $form->labelEx($model,'search_start_date',array('class'=>"col-lg-5 control-label")); ?>
                                    <div class="col-lg-5">
                                        <?php echo $form->textField($model, 'search_start_date',
                                            array('readonly'=>true,'prepend'=>"<span class='fa fa-calendar'></span>")
                                        ); ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo $form->labelEx($model,'search_end_date',array('class'=>"col-lg-5 control-label")); ?>
                                    <div class="col-lg-5">
                                        <?php echo $form->textField($model, 'search_end_date',
                                            array('readonly'=>true,'prepend'=>"<span class='fa fa-calendar'></span>")
                                        ); ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo $form->labelEx($model,'day_num',array('class'=>"col-lg-5 control-label")); ?>
                                    <div class="col-lg-5">
                                        <?php echo $form->textField($model, 'day_num',
                                            array('readonly'=>true,'append'=>Yii::t("summary","day"))
                                        ); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12" style="padding-top: 15px;">
                        <div class="row panel panel-default" style="border-color: #333">
                            <!-- Default panel contents -->
                            <div class="panel-heading">
                                <h3 style="margin-top:10px;">
                                    <?php echo Yii::t('app','Signed conversion rate'); ?>
                                    <small>(<?php echo $model->start_date." ~ ".$model->end_date?>)</small>
                                </h3>
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <?php echo $model->signedRateHtml();?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</section>

<!--詳情彈窗-->
<div class="modal fade" tabindex="-1" role="dialog" id="detailDialog">
    <div class="modal-dialog" role="document" style="width: 80%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Modal title</h4>
            </div>
            <div class="modal-body">
                <p>加载中....</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php
$js="
    $('.click-th').click(function(){
        var contNum = 7;
        var startNum=contNum;
        var endNum = $(this).attr('colspan');
        $(this).prevAll('.click-th').each(function(){
            var colspan = $(this).attr('colspan');
            startNum += parseInt(colspan,10);
        });
        endNum = parseInt(endNum,10)+startNum;
        if($(this).hasClass('active')){
            $(this).children('span').text($(this).data('text'));
            $(this).removeClass('active');
            $('#signedRate>thead>tr').eq(0).children().slice(startNum,endNum).each(function(){
                var width = $(this).data('width')+'px';
                $(this).width(width);
            });
            $('#signedRate>thead>tr').eq(2).children().slice(startNum-contNum,endNum-contNum).each(function(){
                $(this).children('span').text($(this).data('text'));
            });
            $('#signedRate>tbody>tr').each(function(){
                $(this).children().slice(startNum,endNum).each(function(){
                    $(this).children('span').text($(this).data('text'));
                });
            });
        }else{
            $(this).data('text',$(this).text());
            $(this).children('span').text('.');
            $(this).addClass('active');
            $('#signedRate>thead>tr').eq(0).children().slice(startNum,endNum).each(function(){
                var width = '15px';
                $(this).width(width);
            });
            $('#signedRate>thead>tr').eq(2).children().slice(startNum-contNum,endNum-contNum).each(function(){
                $(this).data('text',$(this).text());
                $(this).children('span').text('');
            });
            $('#signedRate>tbody>tr').each(function(){
                $(this).children().slice(startNum,endNum).each(function(){
                    $(this).data('text',$(this).text());
                    $(this).children('span').text('');
                });
            });
        }
    });
    
    $('.click-tr').click(function(){
        var show = $(this).hasClass('show-tr');
        if(show){
            $(this).removeClass('show-tr');
        }else{
            $(this).addClass('show-tr');
        }
        $(this).prevAll('tr').each(function(){
            if($(this).hasClass('tr-end')||$(this).children('td:first').hasClass('click-tr')){
                return false;
            }else{
                if(show){
                    $(this).show();
                }else{
                    $(this).hide();
                }
            }
        });
    });
";
$ajaxUrl = Yii::app()->createUrl('signedRate/ajaxDetail');
$js.= "
$('.td_detail').on('click',function(){
    var tdOne = $(this).parent('tr').children('td').eq(0).text();
    $('#detailDialog').find('.modal-title').text($(this).data('title')+' - '+tdOne);
    $('#detailDialog').find('.modal-body').html('<p>加载中....</p>');
    $('#detailDialog').modal('show');
    $.ajax({
        type: 'GET',
        url: '{$ajaxUrl}',
        data: {
            'city':$(this).data('city'),
            'type':$(this).data('type'),
            'startDate':'{$model->start_date}',
            'endDate':'{$model->end_date}'
        },
        dataType: 'json',
        success: function(data) {
            $('#detailDialog').find('.modal-body').html(data['html']);
        },
        error: function(data) { // if error occured
            alert('Error occured.please try again');
        }
    });
});
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

$language = Yii::app()->language;
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


