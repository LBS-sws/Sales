<style>
    #changeFreMonthHtml .btn-check{ width: 70px;text-align: center;}
    .fre_month_div{ margin: 0px -5px 5px -5px;padding: 0px 5px 0px 5px;border: 1px dashed #d2d6de;position: relative}
    .fre_month_div>.close{ position: absolute;right: 5px;top: 5px;z-index: 10;}
    .fre_month_div>.form-group:first-child{ padding-top: 5px;}
</style>
<?php
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_DEFAULT));
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','OK'), array('id'=>'serviceFreBtn_OK','data-fun'=>'','color'=>TbHtml::BUTTON_COLOR_PRIMARY));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'serviceFreDialog',
					'header'=>"选择服务频次",
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>

<div class="form-group">
    <?php echo TbHtml::label("服务频次","open_fre_type",array('class'=>"col-lg-2 control-label",'required'=>true)); ?>

    <div class="col-lg-10">
        <div class="btn-group" role="group" >
            <button type="button" data-val="1" class="btn btn-radio btn-default">每月</button>
            <button type="button" data-val="4" class="btn btn-radio btn-default">每周</button>
            <button type="button" data-val="2" class="btn btn-radio btn-default">自定义</button>
            <button type="button" data-val="3" class="btn btn-radio btn-default">呼叫式</button>
        </div>
    </div>
</div>
<?php
$html=$this->renderPartial('//cont/serviceFre_month',array("num"=>0),true);
echo TbHtml::hiddenField("open_fre_html",$html,array("id"=>"open_fre_html"));
?>
<div id="changeFreMonthHtml">
</div>
<div class="form-group hide">
    <div class="col-lg-12">
        <?php
        echo TbHtml::button("增加",array('id'=>'open_fre_month_add','class'=>'btn-block','color'=>TbHtml::BUTTON_COLOR_DEFAULT))
        ?>
    </div>
</div>

<?php
	$this->endWidget();
?>
<?php
$js = <<<EOF

var openServiceFreObj;

    $('body').on('focus','.serviceFreText',function(){
        if($(this).attr("readonly")=='readonly'){
            openServiceFreObj='';
            return false;
        }
        openServiceFreObj=$(this);
        openServiceFreObj.blur();
        $('#serviceFreBtn_OK').data('fun',$(this).data('fun'));
        $('#serviceFreDialog').modal('show');
    });

$('.btn-radio').click(function(){
    var val = $(this).data('val');
    var open_fre_html = $('#open_fre_html').val();
    $(this).removeClass('btn-default').addClass('btn-primary');
    $(this).siblings('.btn-radio').removeClass('btn-primary');
    switch(val){
        case 4:
            open_fre_html = open_fre_html.replace(/:NUM:/g, "0");
            $('#changeFreMonthHtml').html(open_fre_html);
            $('#changeFreMonthHtml').find('.btn-check').removeClass('btn-default').addClass('btn-primary');
            $('#open_fre_month_add').parents('.form-group').addClass('hide');
            $('#changeFreMonthHtml').find('.open_fre_type_sum').html('<option value="4">每周</option>');
            $('#changeFreMonthHtml').find('.open_fre_type_amt').html('<option value="4">每周</option>');
            break;
        case 1:
            open_fre_html = open_fre_html.replace(/:NUM:/g, "0");
            $('#changeFreMonthHtml').html(open_fre_html);
            $('#changeFreMonthHtml').find('.btn-check').removeClass('btn-default').addClass('btn-primary');
            $('#open_fre_month_add').parents('.form-group').addClass('hide');
            $('#changeFreMonthHtml').find('.open_fre_type_sum').html('<option value="3">每月</option><option value="4">每周</option>');
            break;
        case 2:
            open_fre_html = open_fre_html.replace(/:NUM:/g, "0");
            $('#changeFreMonthHtml').html(open_fre_html);
            $('#open_fre_month_add').parents('.form-group').removeClass('hide');
            $('#open_fre_month_add').data('num',0);
            //$('#changeFreMonthHtml').find('.open_fre_type_sum').html('<option value="3">每月</option>');
            break;
        case 3:
            open_fre_html = open_fre_html.replace(/:NUM:/g, "0");
            $('#changeFreMonthHtml').html(open_fre_html);
            $('#open_fre_amt0').prev('span').text("每次");
            $('#changeFreMonthHtml').find('.form-group').eq(0).remove();
            $('#changeFreMonthHtml').find('.form-group').eq(0).remove();
            $('#open_fre_month_add').parents('.form-group').addClass('hide');
            $('#changeFreMonthHtml').find('.open_fre_type_sum').html('<option value="1">每次</option>');
            $('#changeFreMonthHtml').find('.open_fre_type_amt').html('<option value="1">每次</option>');
            break;
    }
});

$('#open_fre_month_add').click(function(){
    var open_fre_html = $('#open_fre_html').val();
    var num = $('#open_fre_month_add').data('num');
    num++;
    $('#open_fre_month_add').data('num',num);
    open_fre_html = open_fre_html.replace(/:NUM:/g, num);
    $('#changeFreMonthHtml').append(open_fre_html);
    $('#changeFreMonthHtml').children('.fre_month_div').eq(-1).children('.close').removeClass('hide');
});
$('#changeFreMonthHtml').on('click','.close',function(){
    $(this).parent('.fre_month_div').remove();
});
$('#changeFreMonthHtml').on('click','.btn-check',function(){
    var topVal =$('#serviceFreDialog').find('.btn-radio.btn-primary').data('val');
    if(topVal!=1&&topVal!=4){
        if($(this).hasClass('btn-default')){
            $(this).removeClass('btn-default').addClass('btn-primary');
        }else{
            $(this).removeClass('btn-primary').addClass('btn-default');
        }
    }
});

$('#serviceFreBtn_OK').click(function(){
    var freObj ={};
    var serviceFreText = [];
    var errorBool=false;
    var funExpr=$(this).data('fun');
    if($('#serviceFreDialog').find('.btn-radio.btn-primary').length==1){
        freObj['fre_amt']=0;
        freObj['fre_month']=0;
        freObj['fre_sum']=0;
        freObj['fre_type']=$('#serviceFreDialog').find('.btn-radio.btn-primary').data('val');
    }else{
        errorBool=true;
        showFormErrorHtml('请选择服务频次');
        return false;
    }
    freObj['fre_list']=[];
    var contMonth=[];
    $('#serviceFreDialog').find('.fre_month_div').each(function(){
        var monthObj={};
        var monthStr=[];
        monthObj['month']=[];
        $(this).find(".btn-check.btn-primary").each(function(){
            monthObj['month'].push($(this).data('val'));
            var monthVal = $(this).data('val');
            if(contMonth.indexOf(monthVal)>-1){
                errorBool=true;
                showFormErrorHtml('选择的月份不能重复:'+monthVal+'月');
                return false;
            }else{
                contMonth.push(monthVal);
                monthStr.push(monthVal);
            }
        });
        if(monthObj['month'].length==0&&freObj['fre_type']!=3){
            errorBool=true;
            showFormErrorHtml('请选择频次月份');
            return false;
        }
        monthObj['fre_num']=$(this).find('.open_fre_num').length<1?1:$(this).find('.open_fre_num').val();//open_fre_num
        monthObj['type_sum']=$(this).find('.open_fre_num').length<1?1:$(this).find('.open_fre_type_sum').val();//open_fre_type_sum
        monthObj['fre_amt']=$(this).find('.open_fre_amt').val();//open_fre_amt
        monthObj['type_amt']=$(this).find('.open_fre_type_amt').val();//open_fre_type_amt
        monthObj['type_sum'] = parseInt(monthObj['type_sum'],10);
        monthObj['type_amt'] = parseInt(monthObj['type_amt'],10);
        if(monthObj['fre_num']==''){
            errorBool=true;
            showFormErrorHtml('服务次数不能为空');
            return false;
        }else{
            monthObj['fre_num']=parseInt(monthObj['fre_num'],10);
        }
        if(monthObj['fre_amt']==''){
            errorBool=true;
            showFormErrorHtml('服务金额不能为空');
            return false;
        }else{
            monthObj['fre_amt']=parseFloat(monthObj['fre_amt']);
        }
        var monthLength=monthStr.length==0?1:monthStr.length;
        var one_fre_amt=0;//总金额
        var type_amt_str="";//总金额
        var one_fre_sum=0;//总次数
        var type_sum_str="";//总次数
        switch(monthObj['type_sum']){//次数类型
            case 1://每次
                one_fre_sum=monthLength*monthObj['fre_num'];//总次数=月数量*每次次数
                freObj['fre_sum']+=one_fre_sum;
                type_sum_str="每次";
                break;
            case 2://共计
                one_fre_sum=monthObj['fre_num'];//总次数
                freObj['fre_sum']+=one_fre_sum;
                type_sum_str="共计";
                break;
            case 3://每月
                one_fre_sum=monthLength*monthObj['fre_num'];//总次数=月数量*每月次数
                freObj['fre_sum']+=one_fre_sum;
                type_sum_str="每月";
                break;
            case 4://每周
                one_fre_sum=52*monthObj['fre_num'];//总次数=52*每周次数
                freObj['fre_sum']+=one_fre_sum;
                type_sum_str="每周";
                break;
        }
        switch(monthObj['type_amt']){//金额类型
            case 1://每次
                one_fre_amt=one_fre_sum*monthObj['fre_amt'];//总金额=次数*每次金额
                freObj['fre_amt']+=one_fre_amt;
                type_amt_str="每次";
                break;
            case 2://共计
                one_fre_amt=monthObj['fre_amt'];//总金额
                freObj['fre_amt']+=one_fre_amt;
                type_amt_str="共计";
                break;
            case 3://每月
                one_fre_amt=monthLength*monthObj['fre_amt'];//总金额=月数量*每月金额
                freObj['fre_amt']+=one_fre_amt;
                type_amt_str="每月";
                break;
            case 4://每周
                one_fre_amt=52*monthObj['fre_amt'];//总金额=52*每周金额
                freObj['fre_amt']+=one_fre_amt;
                type_amt_str="每周";
                break;
        }
        
        if(freObj['fre_type']!=3){
            monthStr=monthStr.join('、');
            monthStr+="月,";
            monthStr+=type_sum_str+"服务"+monthObj['fre_num']+"次";
            monthStr+=",";
        }else{
            monthStr="呼叫式,";
        }
        monthStr+=type_amt_str+"金额"+monthObj['fre_amt']+";";
        serviceFreText.push(monthStr);
        monthObj['fre_num'] = freObj['fre_type']==3?0:monthObj['fre_num'];
        freObj['fre_list'].push(monthObj);
    });
    if(errorBool===false){
        freObj['fre_month'] = contMonth.length==0?freObj['fre_amt']:freObj['fre_amt']/contMonth.length;
        freObj['fre_month'] = parseFloat(freObj['fre_month'].toFixed(2));
        serviceFreText = serviceFreText.join(' ');
        openServiceFreObj.val(serviceFreText);
        openServiceFreObj.siblings('.serviceFreAmt').val(freObj['fre_amt']);
        openServiceFreObj.siblings('.serviceFreMonth').val(freObj['fre_month']);
        freObj['fre_sum'] = freObj['fre_type']==3?0:freObj['fre_sum'];
        openServiceFreObj.siblings('.serviceFreSum').val(freObj['fre_sum']);
        openServiceFreObj.siblings('.serviceFreType').val(freObj['fre_type']);
        freObjStr = JSON.stringify(freObj);
        openServiceFreObj.siblings('.serviceFreJson').val(freObjStr);
        $('#serviceFreDialog').modal('hide');
        if(funExpr!=''){
            eval(funExpr + '(openServiceFreObj,freObj,serviceFreText)');
        }else{
            openServiceFreObj.trigger('change');
        }
    }
});
EOF;
Yii::app()->clientScript->registerScript('openServiceFreDialog',$js,CClientScript::POS_READY);
?>
