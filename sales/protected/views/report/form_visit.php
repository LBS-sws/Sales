<?php
$this->pageTitle=Yii::app()->name . ' - Report';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'report-form',
    'action'=>Yii::app()->createUrl('report/fenxi'),
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('app','Visit Steps'); ?></strong>
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
                    'submit'=>Yii::app()->createUrl('report/fenxi')));
                ?>
            </div>
        </div></div>

    <div class="box box-info">
        <div class="box-body">
            <?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'name'); ?>
            <?php echo $form->hiddenField($model, 'fields'); ?>
            <?php echo $form->hiddenField($model, 'staffs'); ?>

            <?php if ($model->showField('city') && !Yii::app()->user->isSingleCity()): ?>
                <div class="form-group">
                    <?php echo $form->labelEx($model,'city',array('class'=>"col-sm-2 control-label")); ?>
                    <div class="col-sm-3">
                        <?php echo $form->dropDownList($model, 'city', General::getCityListWithNoDescendant(Yii::app()->user->city_allow()),
                            array('disabled'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
            <?php else: ?>
                <?php echo $form->hiddenField($model, 'city'); ?>
            <?php endif ?>

            <div class="form-group">
                <?php echo $form->labelEx($model,'start date',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <div class="input-group date">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <?php echo $form->textField($model, 'start_dt',
                            array('class'=>'form-control pull-right','readonly'=>($model->scenario=='view'),));
                        ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'end date',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <div class="input-group date">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <?php echo $form->textField($model, 'end_dt',
                            array('class'=>'form-control pull-right','readonly'=>($model->scenario=='view'),));
                        ?>
                    </div>
                </div>
            </div>




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
                <?php echo $form->labelEx($model,'sale',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3" style="width: 800px;">
                    <label  style="width: 75px" ><input name="Fruit" type="checkbox" value="" id="all"/><?php echo Yii::t('report','All');?> </label><br id="label"/>
                    <?php foreach ($saleman as $v) {?>
                        <label style="min-width: 75px" class="a"><input name="ReportVisitForm[sale][]" type="checkbox" value="<?php echo $v['name'];?>" /><?php echo $v['name_label'];?> </label>
                    <?php }?>
                </div>
            </div>


            <div class="form-group">
                <?php echo $form->labelEx($model,'bumen',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->dropDownList($model, 'bumen',
                        array(
                            'yes'=>Yii::t('report','Yes'),
                            'no'=>Yii::t('report','No'),
                        )
                    ); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $this->renderPartial('//site/lookup'); ?>

<?php
$url=Yii::app()->createUrl('report/city');
$js = <<<EOF
   
      $(document).on("change","#ReportVisitForm_city,#ReportVisitForm_start_dt,#ReportVisitForm_end_dt",function () {   
            var city=$("#ReportVisitForm_city").val();
            var start_dt=$("#ReportVisitForm_start_dt").val();
            var end_dt=$("#ReportVisitForm_end_dt").val();
      $.post('$url',{txt:city,start_dt:start_dt,end_dt:end_dt},function(result){     
            $("label").remove(".a");
            var result=$.parseJSON( result)
            var dataLen = result.length ; //返回数组的长度
            var i = 0 ; //声明数据从0开始循环   
            for (i;i<dataLen;i++){
            //循环一次 i加1                    
            var uId = result[i].name ;           
            var uName = result[i].name;         
            var name_label = result[i].name_label;         
            var txt1="<label style='min-width: 75px' class='a'><input name='ReportVisitForm[sale][]' type='checkbox' value='"+uName+"' /> "+name_label+"</label>";         
            $("#label").after(txt1);       
        }         
      });
 
    });
  
        $("#all").on('click',function() {  
              $("input[name='ReportVisitForm[sale][]']").prop("checked", this.checked);  
        });  
            
        $("input[name='ReportVisitForm[sale][]']").on('click',function() {  
              var subs = $("input[name='ReportVisitForm[sale][]']");  
              $("#all").prop("checked" ,subs.length == subs.filter(":checked").length ? true :false);  
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
    'ReportVisitForm_start_dt',
    'ReportVisitForm_end_dt',
));
Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

</div><!-- form -->

