<?php
$this->pageTitle=Yii::app()->name . ' - rankscore';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'rankscore-form',
    'action'=>Yii::app()->createUrl('rankscore/view'),
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('rank','Sales rank total score list'); ?></strong>
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
                <?php echo TbHtml::button(Yii::t('misc','Submit'), array(
                    'submit'=>Yii::app()->createUrl('rankscore/view')));
                ?>
            </div>
        </div></div>
    <div class="box box-info">
        <div class="box-body">
            <!--			--><?php //echo $form->hiddenField($model, 'id'); ?>
            <!--			--><?php //echo $form->hiddenField($model, 'name'); ?>
            <!--			--><?php //echo $form->hiddenField($model, 'fields'); ?>
            <!--			--><?php //echo $form->hiddenField($model, 'form'); ?>

            <div class="form-group">
                <?php echo $form->labelEx($model,'city',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->dropDownList($model, 'city',
                        $city,
                        array('disabled'=>($model->scenario=='view'))
                    ); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'season',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->dropDownList($model, 'season',
                        $season,
                        array('disabled'=>($model->scenario=='view'),'onclick'=>"javascript:doit(this);")
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'year',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->dropDownList($model, 'year',
                        $year,
                        array('disabled'=>($model->scenario=='view'),'onclick'=>"javascript:doit(this);")
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'month',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->dropDownList($model, 'month',
                        $months,
                        array('disabled'=>($model->scenario=='view'),'onclick'=>"javascript:doit(this);")
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'all',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->dropDownList($model, 'all',
                        $all,
                        array('disabled'=>($model->scenario=='view'),'onclick'=>"javascript:doit(this);")
                    ); ?>
                </div>
            </div>

        </div>
    </div>
</section>

<?php
$url=Yii::app()->createUrl('report/city');
$js = <<<EOF
$(document).ready(function(){

      $(document).on("change","#RankScoreForm_season",function () {   
            txt=$(this).find("option:selected").val();                  
            if(txt>0){           
              $("#RankScoreForm_year").attr("disabled","disabled");
              $("#RankScoreForm_month").attr("disabled","disabled");
              $("#RankScoreForm_all").attr("disabled","disabled");
            }
            if(txt==0){           
             $("#RankScoreForm_year").removeAttr("disabled","disabled");
              $("#RankScoreForm_month").removeAttr("disabled","disabled");
              $("#RankScoreForm_all").removeAttr("disabled","disabled");
            }
    });
          $(document).on("change","#RankScoreForm_year",function () {   
            txt=$(this).find("option:selected").val();                  
            if(txt>0){           
              $("#RankScoreForm_season").attr("disabled","disabled");
              $("#RankScoreForm_month").attr("disabled","disabled");
              $("#RankScoreForm_all").attr("disabled","disabled");
            }
            if(txt==0){           
             $("#RankScoreForm_season").removeAttr("disabled","disabled");
              $("#RankScoreForm_month").removeAttr("disabled","disabled");
              $("#RankScoreForm_all").removeAttr("disabled","disabled");
            }
    });
              $(document).on("change","#RankScoreForm_month",function () {   
            txt=$(this).find("option:selected").val();                  
            if(txt>0){           
              $("#RankScoreForm_season").attr("disabled","disabled");
              $("#RankScoreForm_year").attr("disabled","disabled");
              $("#RankScoreForm_all").attr("disabled","disabled");
            }
            if(txt==0){           
             $("#RankScoreForm_season").removeAttr("disabled","disabled");
              $("#RankScoreForm_year").removeAttr("disabled","disabled");
              $("#RankScoreForm_all").removeAttr("disabled","disabled");
            }
    });
            $(document).on("change","#RankScoreForm_all",function () {   
            txt=$(this).find("option:selected").val();                  
            if(txt>0){           
              $("#RankScoreForm_season").attr("disabled","disabled");
              $("#RankScoreForm_year").attr("disabled","disabled");
              $("#RankScoreForm_month").attr("disabled","disabled");
            }
            if(txt==0){           
             $("#RankScoreForm_season").removeAttr("disabled","disabled");
              $("#RankScoreForm_year").removeAttr("disabled","disabled");
              $("#RankScoreForm_month").removeAttr("disabled","disabled");
            }
    });
                  
});

EOF;
?>
<?php
Yii::app()->clientScript->registerScript('calculate',$js,CClientScript::POS_READY);
$js = Script::genLookupSearchEx();
Yii::app()->clientScript->registerScript('lookupSearch',$js,CClientScript::POS_READY);

$js = Script::genLookupButtonEx('btnStaff', 'staff', 'staffs', 'staffs_desc',
    array(),
    true
);
Yii::app()->clientScript->registerScript('lookupStaffs',$js,CClientScript::POS_READY);

$js = Script::genLookupSelect();
Yii::app()->clientScript->registerScript('lookupSelect',$js,CClientScript::POS_READY);

$js = Script::genDatePicker(array(
    'ReportXS01Form_start_dt',
    'ReportXS01Form_end_dt',
));
Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

</div><!-- form -->

