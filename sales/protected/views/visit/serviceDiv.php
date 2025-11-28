
<?php

$currcode = City::getCurrency($model->city);
$sign = Currency::getSign($currcode);
$freBool = isset($freBool)?$freBool:false;

foreach($model->serviceDefinition() as $gid=>$items) {
    $wareList=array();//洁具
    $wareOnList=array();//洁具(已选择)
    $deviceList=array();//设备
    $deviceOnList=array();//设备(已选择)
    $pestList=array();//标靶虫害
    $pestOnList=array();//标靶虫害(已选择)
    $methodList=array();//处理方式
    $methodOnList=array();//处理方式(已选择)
    $rmkHtml="";
    $amtOpen = $model->inAmtFiles($gid);
    $inputLeftID = get_class($model).'_service_';
    $inputLeftName = get_class($model).'[service]';
    $fieldvalue = isset($model->service['svc_'.$gid]) ? $model->service['svc_'.$gid] : '';

    $de_bool=2;//默認值專用 1：默認 2：空白
    $de_bool = in_array($items['name'],array('纸品','一次性售卖'))?2:1;

    $content = "<legend>".$items['name']."</legend>";
    $content.= "<div class='legend-div' data-id='{$gid}'>";//start:legend-div
    if($freBool){//服务频率
        $fieldid = get_class($model).'_service_svc_'.$gid."FreText";
        $fieldname = get_class($model).'[service][svc_'.$gid.'Fre';
        $content .= "<div class='form-group'>";
        $content .= TbHtml::label(Yii::t("clue","service fre"), false, array('class'=>"col-sm-2 control-label",'required'=>true));
        $content .= "<div class='col-sm-7'>";
        $fieldvalue = isset($model->service['svc_'.$gid.'FreType']) ? $model->service['svc_'.$gid.'FreType'] : '';
        $content .=TbHtml::hiddenField("{$fieldname}Type]",$fieldvalue,array("class"=>"serviceFreType"));
        $fieldvalue = isset($model->service['svc_'.$gid.'FreAmt']) ? $model->service['svc_'.$gid.'FreAmt'] : '';
        $content .=TbHtml::hiddenField("{$fieldname}Amt]",$fieldvalue,array("class"=>"serviceFreAmt"));
        $fieldvalue = isset($model->service['svc_'.$gid.'FreMonth']) ? $model->service['svc_'.$gid.'FreMonth'] : '';
        $content .=TbHtml::hiddenField("{$fieldname}Month]",$fieldvalue,array("class"=>"serviceFreMonth"));
        $fieldvalue = isset($model->service['svc_'.$gid.'FreSum']) ? $model->service['svc_'.$gid.'FreSum'] : '';
        $content .=TbHtml::hiddenField("{$fieldname}Sum]",$fieldvalue,array("class"=>"serviceFreSum"));
        $fieldvalue = isset($model->service['svc_'.$gid.'FreJson']) ? $model->service['svc_'.$gid.'FreJson'] : '';
        $content .=TbHtml::hiddenField("{$fieldname}Json]",$fieldvalue,array("class"=>"serviceFreJson"));
        $fieldvalue = isset($model->service['svc_'.$gid.'FreText']) ? $model->service['svc_'.$gid.'FreText'] : '';
        $content .=TbHtml::textField("{$fieldname}Text]",$fieldvalue,array("class"=>"form-control serviceFreText serviceFreOne",'data-fun'=>'settingOneFre',"readonly"=>$model->isReadOnly(),"placeholder"=>"请选择服务频次"));
        $content .= "</div>";
        $content .= "</div>";
    }
    if($items['type']=="annual"){//包含月金额
        $fieldvalue = isset($model->service['svc_'.$gid]) ? $model->service['svc_'.$gid] : '';
        $content .= "<div class='form-group'>";
        $content .= TbHtml::label(Yii::t('sales','Monthly Amount'),false, array('class'=>"col-sm-2 control-label"));
        $content .= "<div class='col-sm-2'>";
        $content .=TbHtml::numberField("{$inputLeftName}[svc_{$gid}]", $fieldvalue,
                array('size'=>8,'min'=>0,'id'=>"{$inputLeftID}svc_{$gid}",'readonly'=>($model->isReadOnly()||$model->ltNowDate),'class'=>'de_class ser_month_amt','de_type'=>'val','de_bool'=>$de_bool,
                    'placeholder'=>Yii::t('sales','Amount'),'prepend'=>'<span class="fa '.$sign.'"></span>',
                    'data-legend'=>$items["name"],'data-amt'=>$amtOpen,
                )
            );
        $content .="</div></div>";
    }

    $outContent = '';
    $col=0;
    foreach ($items['items'] as $fid=>$fv) {
        if(in_array($fv['type'],array('ware','device','pest','method'))){
            switch ($fv['type']){
                case "ware"://洁具
                    $wareList['svc_'.$fid]=$fv['name'];
                    if(isset($model->service['svc_'.$fid])&&$model->service['svc_'.$fid]!==''){
                        $wareOnList[]=array('item'=>'svc_'.$fid,'name'=>$fv['name'],'value'=>$model->service['svc_'.$fid],'remark'=>isset($model->service['svc_'.$fid.'_rmk'])?$model->service['svc_'.$fid.'_rmk']:'');
                    }
                    break;
                case "device"://设备
                    $deviceList['svc_'.$fid]=$fv['name'];
                    if(isset($model->service['svc_'.$fid])&&$model->service['svc_'.$fid]!==''){
                        $deviceOnList[]=array('item'=>'svc_'.$fid,'name'=>$fv['name'],'value'=>$model->service['svc_'.$fid],'remark'=>isset($model->service['svc_'.$fid.'_rmk'])?$model->service['svc_'.$fid.'_rmk']:'');
                    }
                    break;
                case "pest"://标靶虫害
                    $pestList['svc_'.$fid]=$fv['name'];
                    if(isset($model->service['svc_'.$fid])&&$model->service['svc_'.$fid]=="Y"){
                        $pestOnList[]='svc_'.$fid;
                    }
                    break;
                case "method"://处理方式
                    $methodList['svc_'.$fid]=$fv['name'];
                    if(isset($model->service['svc_'.$fid])&&$model->service['svc_'.$fid]=="Y"){
                        $methodOnList[]='svc_'.$fid;
                    }
                    break;
            }
            continue;
        }
        $amtOpen = $model->inAmtFiles($fid);
        if($fid=="H6"){
            $fv['name'].="(".Yii::t('sales','包含延长维保').")";
        }
        $fieldid = "{$inputLeftID}svc_{$fid}";
        $fieldname = "{$inputLeftName}[svc_{$fid}]";
        $fieldvalue = isset($model->service['svc_'.$fid])?$model->service['svc_'.$fid]:'';
        switch ($fv['type']) {
            case 'pct':
                $outContent.=empty($outContent)?"<div class='form-group'>":"";
                $outContent .= TbHtml::label($fv['name'], false, array('class'=>"col-sm-2 control-label"));
                $outContent .= '<div class="col-sm-2">';
                $outContent .= TbHtml::numberField($fieldname, $fieldvalue,
                    array('size'=>5,'min'=>0,'max'=>100,'id'=>$fieldid,'readonly'=>($model->isReadOnly()||$model->ltNowDate),'class'=>'de_class','de_type'=>'val','de_bool'=>$de_bool,
                        'placeholder'=>Yii::t('sales','Percentage'),'append'=>'<span>%</span>',
                        'data-legend'=>$items["name"],'data-amt'=>$amtOpen,
                    )
                );
                $outContent .= '</div>';
                $col+=4;
                break;
            case 'qty':
                $outContent.=empty($outContent)?"<div class='form-group'>":"";
                $outContent .= TbHtml::label($fv['name'], false, array('class'=>"col-sm-2 control-label"));
                $outContent .= '<div class="col-sm-2">';
                $outContent .= TbHtml::numberField($fieldname, $fieldvalue,
                    array('size'=>5,'min'=>0,'id'=>$fieldid,'readonly'=>($model->isReadOnly()||$model->ltNowDate),'class'=>'de_class','de_type'=>'val','de_bool'=>$de_bool,
                        'placeholder'=>Yii::t('sales','Qty'),
                        'data-legend'=>$items["name"],'data-amt'=>$amtOpen,
                    )
                );
                $outContent .= '</div>';
                $col+=4;
                break;
            case 'yearAmount':
            case 'install_amt':
            case 'annual':
            case 'amount':
                $outContent.=empty($outContent)?"<div class='form-group'>":"";
                $outContent .= TbHtml::label($fv['name'], false, array('class'=>"col-sm-2 control-label"));
                $classStrText=$amtOpen==1?"ser_year_amt de_class":"de_class";
                $classStrText.=$fv['type']=="yearAmount"?" ser_year_amt":"";
                $outContent .= '<div class="col-sm-2">';
                $outContent .= TbHtml::numberField($fieldname, $fieldvalue,
                    array('size'=>8,'min'=>0,'id'=>$fieldid,'readonly'=>($model->isReadOnly()||$model->ltNowDate),'class'=>$classStrText,'de_type'=>'val','de_bool'=>$de_bool,
                        'placeholder'=>Yii::t('sales','Amount'),'prepend'=>'<span class="fa '.$sign.'"></span>',
                        'data-legend'=>$items["name"],'data-amt'=>$amtOpen,
                    )
                );
                $outContent .= '</div>';
                $col+=4;
                break;
            case 'text':
                $outContent.=empty($outContent)?"<div class='form-group'>":"";
                $outContent .= TbHtml::label($fv['name'], false, array('class'=>"col-sm-2 control-label"));
                $outContent .= '<div class="col-sm-2">';
                $outContent .= TbHtml::textField($fieldname, $fieldvalue,
                    array('id'=>$fieldid,'readonly'=>($model->isReadOnly()||$model->ltNowDate),'class'=>'de_class','de_type'=>'val','de_bool'=>$de_bool,
                        'placeholder'=>Yii::t('sales','Text'),
                        'data-legend'=>$items["name"],'data-amt'=>$amtOpen,
                    )
                );
                $outContent .= '</div>';
                $col+=4;
                break;
            case 'select':
                $outContent.=empty($outContent)?"<div class='form-group'>":"";
                $outContent .= TbHtml::label($fv['name'], false, array('class'=>"col-sm-2 control-label"));
                if(key_exists("func",$fv)){
                    $fvList = call_user_func_array($fv["func"], array($fieldvalue));
                }elseif (key_exists("list",$fv)){
                    $fvList = $fv["list"];
                }else{
                    $fvList = array();
                }
                $outContent .= '<div class="col-sm-2">';
                $outContent .= TbHtml::dropDownList($fieldname, $fieldvalue,$fvList,
                    array('id'=>$fieldid,'readonly'=>($model->isReadOnly()||$model->ltNowDate),'class'=>'de_class','de_type'=>'val','de_bool'=>$de_bool,
                        'placeholder'=>Yii::t('sales','select'),
                        'data-legend'=>$items["name"],'data-amt'=>$amtOpen,
                    )
                );
                $outContent .= '</div>';
                $col+=4;
                break;
            case 'remark':
            case 'rmk':
                $rmkContent="";
                $rmkContent.="<div class='form-group'>";
                $rmkContent .= TbHtml::label($fv['name'], false, array('class'=>"col-sm-2 control-label"));
                $rmkContent .= '<div class="col-sm-7">';
                $rmkContent .= TbHtml::textArea($fieldname, $fieldvalue,
                    array('id'=>$fieldid,'rows'=>3,'cols'=>60,'maxlength'=>5000,'class'=>'de_class','de_type'=>'val','de_bool'=>$de_bool,
                        'placeholder'=>Yii::t('sales','Remarks'),
                        'readonly'=>($model->isReadOnly()),
                        'data-legend'=>$items["name"],'data-amt'=>$amtOpen,
                    )
                );
                $rmkContent.="</div></div>";
                if($fv["type"]=="remark"){
                    $rmkHtml.=$rmkContent;
                }else{
                    $outContent .= empty($outContent)?"":'</div>';
                    $content.=$outContent.$rmkContent;
                    $outContent="";
                    $col=0;
                }
                break;
            case 'checkbox':
                $outContent.=empty($outContent)?"<div class='form-group'>":"";
                $outContent .= TbHtml::label($fv['name'], false, array('class'=>"col-sm-2 control-label"));
                $outContent .= '<div class="col-sm-2">';
                $outContent .= TbHtml::checkBox($fieldname, ($fieldvalue=='Y'),
                    array('id'=>$fieldid,'disabled'=>($model->isReadOnly()||$model->ltNowDate),'class'=>'de_class','de_type'=>'checked','de_bool'=>$de_bool,
                        'uncheckValue'=>'N', 'value'=>'Y',
                        'data-legend'=>$items["name"],'data-amt'=>$amtOpen,
                    )
                );
                $outContent .= '</div>';
                $col+=4;
                break;
            default:
        }
        if($col>=12||(isset($fv["eol"])&&$fv["eol"])){
            $outContent .= empty($outContent)?"":'</div>';
            $content.=$outContent;
            $outContent="";
            $col=0;
        }
    }
    $outContent .= empty($outContent)?"":'</div>';
    $content.=$outContent;

    if(!empty($pestList)){
        $content .= "<div class='form-group'>";
        $content .= TbHtml::label(Yii::t("clue","input pest"), false, array('class'=>"col-sm-2 control-label"));
        $content .= "<div class='col-sm-7'>";
        $content .=TbHtml::dropDownList('pest_'.$gid,$pestOnList,$pestList,array(
            "readonly"=>$model->isReadOnly(),
            "data-name"=>$inputLeftName,
            "class"=>"select2 changePestMethod",
            'multiple'=>'multiple'
        ));
        $content .="</div>";
        $content .="<div class='changePestMethodDiv hide'>";
        if(!empty($pestOnList)){
            foreach ($pestOnList as $pestOnItem){
                $content.=TbHtml::checkBox("{$inputLeftName}[{$pestOnItem}]",true,array("value"=>"Y"));
            }
        }
        $content .="</div>";
        $content .="</div>";
    }
    if(!empty($wareList)){
        $content .= "<div class='form-group'>";
        $content .= TbHtml::label(Yii::t("clue","input ware"), false, array('class'=>"col-sm-2 control-label"));
        $content .= "<div class='col-sm-7'>";
        $clickOn=array();
        $wareHtml="";
        if(!empty($wareOnList)){
            foreach ($wareOnList as $wareOnRow){
                $clickOn[]=$wareOnRow["item"];
                $wareHtml .= TbHtml::label($wareOnRow["name"], false, array('class'=>"col-sm-2 control-label"));
                $wareHtml .= "<div class='col-sm-2' data-id='{$wareOnRow["item"]}'>";
                $wareHtml .= TbHtml::numberField($inputLeftName."[{$wareOnRow["item"]}]", $wareOnRow["value"],
                    array('size'=>5,'min'=>0,'id'=>$inputLeftID.$wareOnRow["item"],'readonly'=>($model->isReadOnly()||$model->ltNowDate),'class'=>'de_class','de_type'=>'val','de_bool'=>$de_bool,
                        'placeholder'=>"洁具数量",
                        'data-legend'=>$items["name"],'data-amt'=>$amtOpen,
                    )
                );
                $wareHtml .= TbHtml::textArea($inputLeftName."[{$wareOnRow["item"]}_rmk]", $wareOnRow["remark"],
                    array('rows'=>1,'id'=>$inputLeftID.$wareOnRow["item"]."_rmk",'readonly'=>($model->isReadOnly()||$model->ltNowDate),'class'=>'de_class','de_type'=>'val','de_bool'=>$de_bool,
                        'placeholder'=>"洁具备注",
                        'data-legend'=>$items["name"],'data-amt'=>0,
                    )
                );
                $wareHtml .="</div>";
            }
        }
        $content .=TbHtml::dropDownList('pest_'.$gid,$clickOn,$wareList,array(
            "readonly"=>$model->isReadOnly(),
            "class"=>"select2 changeWare",
            "data-name"=>$inputLeftName,
            'multiple'=>'multiple'
        ));
        $content .="</div>";
        $content .="</div>";
        $content.="<div class='form-group changeWareDiv'>".$wareHtml."</div>";
    }
    if(!empty($deviceList)){
        $content .= "<div class='form-group'>";
        $content .= TbHtml::label(Yii::t("clue","input device"), false, array('class'=>"col-sm-2 control-label"));
        $content .= "<div class='col-sm-7'>";
        $clickOn=array();
        $deviceHtml="";
        if(!empty($deviceOnList)){
            foreach ($deviceOnList as $deviceOnRow){
                $clickOn[]=$deviceOnRow["item"];
                $deviceHtml .= TbHtml::label($deviceOnRow["name"], false, array('class'=>"col-sm-2 control-label"));
                $deviceHtml .= "<div class='col-sm-2' data-id='{$deviceOnRow["item"]}'>";
                $deviceHtml .= TbHtml::numberField($inputLeftName."[{$deviceOnRow["item"]}]", $deviceOnRow["value"],
                    array('size'=>5,'min'=>0,'id'=>$inputLeftID.$deviceOnRow["item"],'readonly'=>($model->isReadOnly()||$model->ltNowDate),'class'=>'de_class','de_type'=>'val','de_bool'=>$de_bool,
                        'placeholder'=>"设备数量",
                        'data-legend'=>$items["name"],'data-amt'=>$amtOpen,
                    )
                );
                $deviceHtml .= TbHtml::textArea($inputLeftName."[{$deviceOnRow["item"]}_rmk]", $deviceOnRow["remark"],
                    array('rows'=>1,'id'=>$inputLeftID.$deviceOnRow["item"]."_rmk",'readonly'=>($model->isReadOnly()||$model->ltNowDate),'class'=>'de_class','de_type'=>'val','de_bool'=>$de_bool,
                        'placeholder'=>"设备备注",
                        'data-legend'=>$items["name"],'data-amt'=>0,
                    )
                );
                $deviceHtml .="</div>";
            }
        }
        $content .=TbHtml::dropDownList('pest_'.$gid,$clickOn,$deviceList,array(
            "readonly"=>$model->isReadOnly(),
            "class"=>"select2 changeDevice",
            "data-name"=>$inputLeftName,
            'multiple'=>'multiple'
        ));
        //$deviceOnList[]=array('item'=>'svc_'.$fid,'value'=>$model->service['svc_'.$fid],'remark'=>isset($model->service['svc_'.$fid.'_rmk'])?$model->service['svc_'.$fid.'_rmk']:'');
        $content .="</div>";
        $content .="</div>";
        $content.="<div class='form-group changeDeviceDiv'>".$deviceHtml."</div>";
    }
    if(!empty($methodList)){
        $content .= "<div class='form-group'>";
        $content .= TbHtml::label(Yii::t("clue","input method"), false, array('class'=>"col-sm-2 control-label"));
        $content .= "<div class='col-sm-7'>";
        $content .=TbHtml::dropDownList('pest_'.$gid,$methodOnList,$methodList,array(
            "readonly"=>$model->isReadOnly(),
            "class"=>"select2 changePestMethod",
            "data-name"=>$inputLeftName,
            'multiple'=>'multiple'
        ));
        $content .="</div>";
        $content .="<div class='changePestMethodDiv hide'>";
        if(!empty($methodOnList)){
            foreach ($methodOnList as $methodOnItem){
                $content.=TbHtml::checkBox("{$inputLeftName}[{$methodOnItem}]",true,array("value"=>"Y"));
            }
        }
        $content .="</div>";
        $content .="</div>";
    }
    $content.=$rmkHtml;
    $content .="</div>";//end:legend-div
    echo $content;
}
?>

    <?php
    $disable = $model->isReadOnly()?"true":"false";
    $js = <<<EOF
    $('.changePestMethod,.changeDevice,.changeWare').select2({
	    tags: false,
        multiple: true,
        allowClear: true,
        closeOnSelect: false,
        disabled: {$disable},
        templateSelection: function(state) {
            var rtn = $('<span style="color:black">'+state.text+'</span>');
            return rtn;
        }
    });

$('body').on('change','.changePestMethod',function(){
    var leftName = $(this).data('name');
    var dataList = $(this).val();
    var divObj = $(this).parents('.form-group').eq(0).children('.changePestMethodDiv').eq(0);
    divObj.html('');
    $.each(dataList,function(key,item){
        var itemHtml = $("<input type='checkbox' value='Y' checked='checked'>");
        itemHtml.attr({'name':leftName+'['+item+']'});
        divObj.append(itemHtml);
    });
});

$('body').on('change','.changeDevice',function(){
    var leftName = $(this).data('name');
    var dataList = $(this).val();
    var divObj = $(this).parents('.form-group').eq(0).next('.changeDeviceDiv');
    var changeObj=$(this);
    divObj.children('div').each(function(){
        if(dataList.indexOf($(this).data('id'))==-1){
            $(this).prev('label').remove();
            $(this).remove();
        }
    });
    $.each(dataList,function(key,item){
        var itemHtml = "<label class='col-sm-2 control-label'>";
        itemHtml+=changeObj.children('option[value="'+item+'"]').text();
        itemHtml+="</label>";
        itemHtml+="<div class='col-sm-2' data-id='"+item+"'>";
        itemHtml+='<input class="form-control" type="number" placeholder="设备数量" name="'+leftName+'['+item+']">';
        itemHtml+='<textarea class="form-control" placeholder="设备备注" rows="1" name="'+leftName+'['+item+'_rmk]"></textarea>';
        itemHtml+="</div>";
        if(divObj.children('div[data-id="'+item+'"]').length==0){
            divObj.append(itemHtml);
        }
    });
});

$('body').on('change','.changeWare',function(){
    var leftName = $(this).data('name');
    var dataList = $(this).val();
    var divObj = $(this).parents('.form-group').eq(0).next('.changeWareDiv');
    var changeObj=$(this);
    divObj.children('div').each(function(){
        if(dataList.indexOf($(this).data('id'))==-1){
            $(this).prev('label').remove();
            $(this).remove();
        }
    });
    $.each(dataList,function(key,item){
        var itemHtml = "<label class='col-sm-2 control-label'>";
        itemHtml+=changeObj.children('option[value="'+item+'"]').text();
        itemHtml+="</label>";
        itemHtml+="<div class='col-sm-2' data-id='"+item+"'>";
        itemHtml+='<input class="form-control" type="number" placeholder="洁具数量" name="'+leftName+'['+item+']">';
        itemHtml+='<textarea class="form-control" placeholder="洁具备注" rows="1" name="'+leftName+'['+item+'_rmk]"></textarea>';
        itemHtml+="</div>";
        if(divObj.children('div[data-id="'+item+'"]').length==0){
            divObj.append(itemHtml);
        }
    });
});
EOF;
    Yii::app()->clientScript->registerScript('changePestMethod',$js,CClientScript::POS_READY);
    ?>


