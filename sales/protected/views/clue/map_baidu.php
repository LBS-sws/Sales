<style>
    .amap-sug-result{ z-index: 99999;}
</style>
<?php
//虽然文件名是百度但实际使用的是高德地图
$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY));
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'map_baidu',
    'header'=>Yii::t("clue","map pun"),
    'footer'=>array(),
    'show'=>false,
    'htmlOptions'=>array(
        "data-lng"=>$model->longitude,
        "data-lat"=>$model->latitude,
    )
));
?>
<script type="text/javascript">
    window._AMapSecurityConfig = {
        securityJsCode: "<?php echo Yii::app()->params['gaodePWD'];?>",
    };
</script>

<script type="text/javascript" src="https://webapi.amap.com/maps?v=2.0&plugin=AMap.AutoComplete&key=<?php echo Yii::app()->params['gaodeKEY'];?>"></script>
<div class="col-lg-12">
    <div class="form-group hide">
        <div class="input-group">
            <span class="input-group-addon" id="basic-addon1">请输入关键字</span>
            <input id='tipinput' type="text" autocomplete="off" class="form-control">
        </div>
    </div>
    <div id="map_div" style="height: 350px;"></div>
</div>

<?php
$readyOnly=$model->isReadonly()?'true':'false';
$js = <<<EOF
    var readyOnly={$readyOnly};
    var openMapObj='';
    var marker='', map = new AMap.Map("map_div", {
        resizeEnable: true,
        animateEnable: false,
        zoom: 16
    });
    map.plugin(["AMap.ToolBar"], function() {
		map.addControl(new AMap.ToolBar());
	});
    AMap.plugin(['AMap.PlaceSearch','AMap.AutoComplete'], function(){
        var auto = new AMap.AutoComplete({
            datatype: "poi",
            input: "tipinput"
        });
        var placeSearch = new AMap.PlaceSearch({
            map: map
        });  //构造地点查询类
        auto.on("select", select);//注册监听，当选中某条记录时会触发
        function select(e) {
            if(e.poi.location!=undefined){
                map.setCenter([e.poi.location.lng,e.poi.location.lat]); //更新中心位置
            }
            //placeSearch.setCity(e.poi.adcode);
            //placeSearch.search(e.poi.name);  //关键字查询查询
        }
    });
    $('body').on('click','.openMapBaiDu',function(){
        openMapObj = $(this);
        $('#map_baidu').data('lng',$(this).data('lng'));
        $('#map_baidu').data('lat',$(this).data('lat'));
        $('#map_baidu').data('search',$(this).data('search'));
        $('#map_baidu').modal('show');
    });
$('#map_baidu').on('shown.bs.modal',function(){
    map.resize();
    var lng = $(this).data('lng');
    var lat = $(this).data('lat');
    var searchBool = $(this).data('search');
    if(searchBool==1){
        readyOnly=false;
        $('#tipinput').parents('.form-group').eq(0).removeClass('hide');
    }else{
        readyOnly=true;
        $('#tipinput').parents('.form-group').eq(0).addClass('hide');
    }
    if(lng==''||lat==undefined){
        if(marker!=''){
            marker.setMap(null);
        }
    }else{
        addMarker(lng,lat,true);
    }
    
    if(!readyOnly){
        map.on('click', handleMapClick);
    }else{
        map.off('click', handleMapClick);
    }
});
    //地图点击事件
    function handleMapClick(e) {
        var lat = e.lnglat.lat;
        var lng = e.lnglat.lng;
        //$('#latitude').val(lat);
        //$('#longitude').val(lng);
        $('#map_baidu').data('lng',lng);
        $('#map_baidu').data('lat',lat);  
        if(openMapObj!=''){
            openMapObj.parents('div').eq(0).find('.map_lat').val(lat);
            openMapObj.parents('div').eq(0).find('.map_lng').val(lng);
        }
        addMarker(lng,lat,false);
    }
    
    // 实例化点标记
    function addMarker(lng,lat,centerBool) {
        if (!marker) {
            var position = new AMap.LngLat(lng,lat); //Marker 经纬度
            var icon = new AMap.Icon({
                size:[25,34],
                imageSize:[25,34],
                image:"//a.amap.com/jsapi_demos/static/demo-center/icons/poi-marker-red.png"
            });
            marker = new AMap.Marker({
                position: position,
            });
            marker.setIcon(icon);
            marker.setMap(map);
            if(!readyOnly){
                marker.setDraggable(true);//可拖动
            }else{
                marker.setDraggable(false);//可拖动
            }
            marker.on('dragend', function(e) {
                var lat = e.lnglat.lat;
                var lng = e.lnglat.lng;
                //$('#latitude').val(lat);
                //$('#longitude').val(lng);
                $('#map_baidu').data('lng',lng);
                $('#map_baidu').data('lat',lat);   
                if(openMapObj!=''){
                    openMapObj.parents('div').eq(0).find('.map_lat').val(lat);
                    openMapObj.parents('div').eq(0).find('.map_lng').val(lng);
                    openMapObj.prev('span').html(lng+'<br/>'+lat);  
                }
                //$('#mapSpan').html(lng+'<br/>'+lat);  
            });
        }else{
            marker.setPosition([lng,lat]); //更新点标记位置
        }
        //$('#mapSpan').html(lng+'<br/>'+lat);  
        if(openMapObj!=''){
            openMapObj.prev('span').html(lng+'<br/>'+lat);  
        }
        if(centerBool){
            map.setCenter([lng,lat]); //更新中心位置
        }
    }
EOF;
Yii::app()->clientScript->registerScript('mapJS',$js,CClientScript::POS_READY);
?>

<?php
$this->endWidget();
?>
