<?php
$js = <<<EOF
function settingBatchFre(textObj,freObj,serviceFreText){
    $('.ser_month_amt').val(freObj["fre_month"]);
    $('.ser_year_amt').val(freObj["fre_amt"]);
    $('.serviceFreType').val(freObj["fre_type"]);
    $('.serviceFreAmt').val(freObj["fre_amt"]);
    $('.serviceFreMonth').val(freObj["fre_month"]);
    $('.serviceFreSum').val(freObj["fre_sum"]);
    var freObj = JSON.stringify(freObj);
    $('.serviceFreJson').val(freObj);
    $('.serviceFreOne').val(serviceFreText);
}
function settingOneFre(textObj,freObj,serviceFreText){
    textObj.parents('.legend-div:first').find('.ser_month_amt').val(freObj["fre_month"]);
    textObj.parents('.legend-div:first').find('.ser_year_amt').val(freObj["fre_amt"]);
}
$("body").on("change",".serviceFreArea",function(){
    var trObj = $(this).parents('tr:first');
    var areaMin = trObj.find('.serviceFreArea_min:first').val();
    var areaMax = trObj.find('.serviceFreArea_max:first').val();
    var charStr = trObj.find('select:first').val();
    var serviceFreType = trObj.find('.serviceFreType:first').val();
    var serviceFreAmt = trObj.find('.serviceFreAmt:first').val();
    var serviceFreMonth = trObj.find('.serviceFreMonth:first').val();
    var serviceFreSum = trObj.find('.serviceFreSum:first').val();
    var serviceFreJson = trObj.find('.serviceFreJson:first').val();
    var serviceFreOne = trObj.find('.serviceFreText:first').val();
    trObj.find('.serviceFreArea_month:first').val(serviceFreMonth);
    trObj.find('.serviceFreArea_sum:first').text(serviceFreSum);
    $('.win_sse_store').each(function(){
        var areaNum = $(this).data('area');
        if(areaNum>=areaMin&&areaNum<=areaMax){
            var legendObj = $(this).next('.win_sse_form').find('.legend-div[data-id="'+charStr+'"]');
            if(legendObj.length>0){
                legendObj.find('.ser_month_amt').val(serviceFreMonth);
                legendObj.find('.ser_year_amt').val(serviceFreAmt);
                legendObj.find('.serviceFreType').val(serviceFreType);
                legendObj.find('.serviceFreAmt').val(serviceFreAmt);
                legendObj.find('.serviceFreMonth').val(serviceFreMonth);
                legendObj.find('.serviceFreSum').val(serviceFreSum);
                legendObj.find('.serviceFreJson').val(serviceFreJson);
                legendObj.find('.serviceFreOne').val(serviceFreOne);
            }
        }
    });
});
EOF;
Yii::app()->clientScript->registerScript('settingFre',$js,CClientScript::POS_READY);
?>