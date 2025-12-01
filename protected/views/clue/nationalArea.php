<style>
    .national-div{ position: absolute;top: 0px;left: 0px;width: 600px;display: none;background: #fff;}
    .national-div .list-group{ height: 200px;overflow: scroll;border: 1px solid #d2d6de;margin: 0px;}
    .national-div .list-group-item{ padding: 5px 15px;border-radius: 0px;}
    .national-search-div{ position: absolute;top: 0px;left: 0px;width: 600px;display: none;background: #fff;}
    .national-search-div .list-group{ max-height: 200px;overflow: scroll;border: 1px solid #d2d6de;margin: 0px;}
    .national-search-div .list-group-item{ padding: 5px 15px;border-radius: 0px;}
</style>
<div class="national-div" id="national-div">
    <div class="col-xs-4">
        <div class="row">
            <ul class="list-group" id="national-one">
                <?php
                $html="";
                $lists=CGetName::getNationalListByType(1,0);
                foreach ($lists as $list){
                    $html.="<li class='list-group-item' data-id='{$list["id"]}'>".$list["area_name"]."</li>";
                }
                echo $html;
                ?>
            </ul>
        </div>
    </div>
    <div class="col-xs-4">
        <div class="row">
            <ul class="list-group" id="national-two">
            </ul>
        </div>
    </div>
    <div class="col-xs-4">
        <div class="row">
            <ul class="list-group" id="national-three">
            </ul>
        </div>
    </div>
</div>
<div class="national-search-div" id="national-search-div">
    <ul class="list-group" id="national-search-ul">
    </ul>
</div>
<?php
$nationalUrl = Yii::app()->createUrl('clueHead/ajaxNational');
$nationalSearchUrl = Yii::app()->createUrl('clueHead/ajaxNationalSearch');
$js = <<<EOF
var nationalObj='';
$('body').on('keyup','.nationalClick',function(){
    if(nationalObj!=''){
        var search=$(this).val();
        var city=$(this).data('city');
        if(search==''&&(city==undefined||city=='')){
            search = $(this).data('city_name');
        }
        var top=nationalObj.offset().top+nationalObj.outerHeight();
        var left=nationalObj.offset().left;
        var width=nationalObj.outerWidth();
        var clue_type=nationalObj.data('clue');
        $('#national-search-div').css({
            top:top,
            left:left,
            display:'block',
            width:width,
            zIndex:99999
        });
        $.ajax({
            type: "POST", // 请求类型
            url: "{$nationalSearchUrl}", // 服务器端点URL
            data: {search:search,city:city,clue_type:clue_type}, // 发送到服务器的数据
            dataType: "JSON", // 
            success: function(response) {
                $('#national-search-ul').html('');
                $.each(response,function(index,item){
                    var liObj=$('<li class="list-group-item"></li>');
                    liObj.text(item['tree_names']);
                    liObj.attr('data-id',item['id']);
                    liObj.attr('data-ids',item['parent_ids']);
                    liObj.attr('data-name',item['tree_names']);
                    $('#national-search-ul').append(liObj);
                });
            },
            error: function(xhr, status, error) {
            }
        });
    }
});
$('body').on('blur','.nationalClick',function(){
    if($(this).val()!=''&&$(this).data('name')!=''&&$(this).data('name')!=undefined&&$(this).val()!=$(this).data('name')){
        $(this).val($(this).data('name'));
    }
});
$('body').on('focus','.nationalClick',function(){
    if($(this).attr("readonly")=='readonly'){
        nationalObj='';
        return false;
    }
    nationalObj=$(this);
    var clue=$(this).data('clue');
    if(clue!=2){
        $(this).trigger('keyup');
        return false;
    }
    var top=nationalObj.offset().top-200;
    var left=nationalObj.offset().left;
    var width="600px";
    var parent_ids = $(this).data('ids');
    var lists=parent_ids==''||parent_ids==undefined?[0,110000000000,110100000000,110108000000]:parent_ids.split(',');
    if(lists.length<4){
        for(var i=lists.length;i<4;i++){
            lists[i]=0;
        }
    }
    if($('body').outerWidth()<600){
        left=0;
        width="100%";
    }
    $('#national-div').css({
        top:top,
        left:left,
        display:'block',
        width:width,
        zIndex:99999
    });
    $('#national-one>li').removeClass('active');
    $('#national-two>li').removeClass('active');
    $('#national-three>li').removeClass('active');
    if($('#national-three>li[data-id='+lists[3]+']').length<1){
        var clickObj = $('#national-one>li[data-id='+lists[1]+']').eq(0);
        ajaxNationalTwo(clickObj,lists[1],lists[2]);
        clickObj = $('#national-two>li[data-id='+lists[2]+']').eq(0);
        ajaxNationalThree(clickObj,lists[2],lists[3]);
    }else{
        $('#national-one>li[data-id='+lists[1]+']').eq(0).addClass('active');   
        $('#national-two>li[data-id='+lists[2]+']').eq(0).addClass('active');
        $('#national-three>li[data-id='+lists[3]+']').eq(0).addClass('active');
        //scrollChange();
    }
});

function scrollChange(){
    var top=0;
    if($('#national-one>li.active').length==1){
        top = $('#national-one>li.active').position().top; 
        $('#national-one').scrollTop(top); 
    }
    if($('#national-two>li.active').length==1){
        top = $('#national-two>li.active').position().top; 
        $('#national-two').scrollTop(top); 
    }
    if($('#national-three>li.active').length==1){
        top = $('#national-three>li.active').position().top; 
        $('#national-three').scrollTop(top); 
    }
}
$('#national-div').on('click',function(e){
    e.stopPropagation();
});
$('body').on('click',function(e){
    if(nationalObj==''||e.target!=nationalObj.get(0)){
        $('#national-div').hide();
        $('#national-search-div').hide();
    }
});
$('#national-one').on('click','.list-group-item',function(){
    if($(this).hasClass('active')){
        return false;
    }
    var clickObj = $(this);
    $('#national-one>li').removeClass('active');
    $('#national-two>li').removeClass('active');
    $('#national-three>li').removeClass('active');
    var id =$(this).data('id');
    ajaxNationalTwo(clickObj,id,0);
});

function ajaxNationalTwo(clickObj,id,num){
    $.ajax({
        type: "POST", // 请求类型
        url: "{$nationalUrl}", // 服务器端点URL
        data: {id:id,type:2}, // 发送到服务器的数据
        dataType: "JSON", // 
        success: function(response) {
            clickObj.addClass('active');
            $('#national-two').html('');
            $.each(response,function(index,item){
                var liObj=$('<li class="list-group-item"></li>');
                liObj.text(item['area_name']);
                liObj.attr('data-id',item['id']);
                liObj.attr('data-name',item['tree_names']);
                $('#national-two').append(liObj);
            });
            if(num!=0){
                $('#national-two>li[data-id='+num+']').eq(0).addClass('active');
            }else{
                $('#national-two>li').eq(0).trigger('click');
            }
        },
        error: function(xhr, status, error) {
        }
    });
}

$('#national-two').on('click','.list-group-item',function(){
    if($(this).hasClass('active')){
        return false;
    }
    var clickObj = $(this);
    $('#national-two>li').removeClass('active');
    $('#national-three>li').removeClass('active');
    var id =$(this).data('id');
    ajaxNationalThree(clickObj,id,0);
});

function ajaxNationalThree(clickObj,id,num){
    $.ajax({
        type: "POST", // 请求类型
        url: "{$nationalUrl}", // 服务器端点URL
        data: {id:id,type:3}, // 发送到服务器的数据
        dataType: "JSON", // 
        success: function(response) {
            clickObj.addClass('active');
            $('#national-three').html('');
            $.each(response,function(index,item){
                var liObj=$('<li class="list-group-item"></li>');
                liObj.text(item['area_name']);
                liObj.attr('data-id',item['id']);
                liObj.attr('data-ids',item['parent_ids']);
                liObj.attr('data-name',item['tree_names']);
                $('#national-three').append(liObj);
            });
            if(num!=0){
                $('#national-three>li[data-id='+num+']').eq(0).addClass('active');
            }else{
                $('#national-three>li').eq(0).trigger('click');
            }
            scrollChange();
        },
        error: function(xhr, status, error) {
        }
    });
}

$('#national-three').on('click','.list-group-item',function(){
    $('#national-three>li').removeClass('active');
    $(this).addClass('active');
    nationalObj.val($(this).data('name'));
    nationalObj.data('ids',$(this).data('ids'));
    nationalObj.data('name',$(this).data('name'));
    nationalObj.prev('input').val($(this).data('id'));
    nationalObj.trigger('change');
    //$('#national-div').css('display','none');
});

$('#national-search-ul').on('click','.list-group-item',function(){
    nationalObj.val($(this).data('name'));
    nationalObj.data('ids',$(this).data('ids'));
    nationalObj.data('name',$(this).data('name'));
    nationalObj.prev('input').val($(this).data('id'));
    nationalObj.trigger('change');
});
EOF;
Yii::app()->clientScript->registerScript('nationalJS',$js,CClientScript::POS_READY);
?>