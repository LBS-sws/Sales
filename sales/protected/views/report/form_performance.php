<?php
$this->pageTitle=Yii::app()->name . ' - Report';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'report-form',
    'action'=>Yii::app()->createUrl('report/performancelist'),
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('app','Sales lead'); ?></strong>
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
                    'submit'=>Yii::app()->createUrl('report/performancelist')));
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
                        array('disabled'=>($model->scenario=='view'),'id'=>'city')
                    ); ?>
                </div>
            </div>

            <button name="btnSignQc2" id="btnSignQc2" class="btn btn-default" type="button" style="margin-left: 280px;"><?php echo Yii::t('report','yingcang');?></button>
            <div class="form-group">
                <?php echo $form->labelEx($model,'sale',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3" style="width: 800px;" id="show" style="display: block">
                    <label  style="width: 75px" ><input name="Fruit" type="checkbox" value="" id="all" /><?php echo Yii::t('report','All');?> </label><br id="label"/>
                    <?php foreach ($saleman as $v) {?>
                        <label style="min-width: 75px" class="a" style="display: none"><input name="ReportVisitForm[sale][]" type="checkbox" value="<?php echo $v['user_id'];?>" <?php if(!empty($model['sale'])&&in_array($v['user_id'], $model['sale'])){echo "checked='checked'";}?>/><?php echo $v['name_label'];?> </label>
                    <?php }?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'sort',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->dropDownList($model, 'sort',
                        array(
                            'singular'=>Yii::t('report','singular'),
                            'money'=>Yii::t('report','money'),
                            'svc_A7'=>Yii::t('report','svc_A7'),
                            'svc_C7'=>Yii::t('report','svc_C7'),
                            'svc_E7'=>Yii::t('report','svc_E7'),
                            'svc_F4'=>Yii::t('report','svc_F4'),
                            'svc_D6'=>Yii::t('report','svc_D6'),
                            'svc_B6'=>Yii::t('report','svc_B6'),
                            'svc_G3'=>Yii::t('report','svc_G3'),
                            'svc_A7s'=>Yii::t('report','svc_A7s'),
                            'svc_C7s'=>Yii::t('report','svc_C7s'),
                            'svc_E7s'=>Yii::t('report','svc_E7s'),
                            'svc_F4s'=>Yii::t('report','svc_F4s'),
                            'svc_D6s'=>Yii::t('report','svc_D6s'),
                            'svc_B6s'=>Yii::t('report','svc_B6s'),
                            'svc_G3s'=>Yii::t('report','svc_G3s')
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
$(document).ready(function(){

      $(document).on("change","#city,#ReportVisitForm_end_dt,#ReportVisitForm_start_dt",function () {   
            var endDate=$("#ReportVisitForm_end_dt").val();  
            var startDate=$("#ReportVisitForm_start_dt").val();  
            var txt=$("#city").val();  
      $.post('$url',{txt:txt,start_dt:startDate,end_dt:endDate},function(result){    
            $("label").remove(".a");
            var result=$.parseJSON( result)
            var dataLen = result.length ; //返回数组的长度
            var i = 0 ; //声明数据从0开始循环   
            for (i;i<dataLen;i++){
            //循环一次 i加1                    
            var uId = result[i].user_id ;           
            var uName = result[i].user_id;         
            var name_label = result[i].name_label;         
            var txt1="<label style='min-width: 75px' class='a'><input name='ReportVisitForm[sale][]' type='checkbox' value='"+uId+"' /> "+name_label+"</label>";         
            $("#label").after(txt1);       
        }         
      });
 
    });
 $('#btnSignQc2').on('click',function(){
    if($("#show").css("display")=='none'){//如果show是隐藏的
        $("#show").css("display","block");//show的display属性设置为block（显示）
    }else{//如果show是显示的
        $("#show").css("display","none");//show的display属性设置为none（隐藏）
    }
});
  
        $("#all").on('click',function() {  
              $("input[name='ReportVisitForm[sale][]']").prop("checked", this.checked);  
        });     
            
        $("input[name='ReportVisitForm[sale][]']").on('click',function() {  
              var subs = $("input[name='ReportVisitForm[sale][]']");  
              $("#all").prop("checked" ,subs.length == subs.filter(":checked").length ? true :false);  
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
    'ReportVisitForm_start_dt',
    'ReportVisitForm_end_dt',
));
Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

</div><!-- form -->

