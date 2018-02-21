<?php
$this->pageTitle=Yii::app()->name . ' - Customer Type Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'code-form',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('quiz','video form'); ?></strong>
    </h1>
</section>

<section class="content">
    <div class="box"><div class="box-body">
            <div class="btn-group" role="group">
                <?php
                if ($model->scenario!='new' && $model->scenario!='view') {
                    echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
                        'submit'=>Yii::app()->createUrl('Salesvideo/new')));
                }
                ?>
                <?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                    'submit'=>Yii::app()->createUrl('Salesvideo/index')));
                ?>
             <!--   <?php /*if ($model->scenario!='view'): */?>
                    <?php /*echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
                        'submit'=>Yii::app()->createUrl('Salesvideo/save')));
                    */?>
                <?php /*endif */?>
                <?php /*if ($model->scenario=='edit'): */?>
                    <?php /*echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
                            'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
                    );
                    */?>
                --><?php /*endif */?>
            </div>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-body">
            <?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo $form->hiddenField($model, 'id'); ?>

           <div class="form-group">
                 <?php echo $form->labelEx($model,'video_info_date',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <div class="input-group date">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <?php echo $form->textField($model, 'video_info_date',
                            array('class'=>'form-control pull-right','readonly'=>($model->scenario=='view'),'id'=>'video_info_date',));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'seller_notes',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-8">
                    <?php echo $form->textArea($model,'seller_notes',
                        array('size'=>'20', 'maxlength'=>'50','cols'=>'30','rows'=>'6','id'=>'seller_notes',)
                    ); ?>
                </div>
            </div>
            <div class="form-froup">
                <div class="demo clearfix" style="width:600px;margin:120px auto">
                    <a onclick="showVideoUploadBox($('#btn_video'))" id="btn_video" class="item"><i class="icon_emot_photo_video icon_video"></i><span>视频</span></a>
                </div>
            </div>
        </div>
    </div>
</section>

<style type="text/css">
    a{cursor:pointer;text-decoration: none}
    body{background: #fff none repeat scroll 0 0; color: #333; font: 12px/1.5 "Microsoft YaHei","Helvetica Neue",Helvetica,STHeiTi,sans-serif; background-position: left top; background-repeat: repeat; background-attachment: scroll;}
    .clearfix:after{visibility:hidden; display:block; font-size:0; content:" "; clear:both; height:0}
    *:first-child+html .clearfix{zoom:1}
    ul,li{list-style: none;padding:0;margin:0}
    .arrow_layer{right: 20px;top: -15px;display: block;overflow: hidden;position: absolute;z-index: 1050}
    .arrow_layer i, .arrow_layer em{border-style: solid;border-width: 7px;display: inline-block;font-size: 0;height: 0;line-height: 0;overflow: hidden;vertical-align: top;width: 0;border-left-color: transparent;border-right-color: transparent;border-top-color: transparent;}
    .arrow_layer em{margin: 1px 0 0 -14px;}
    .arrow_top_bg{border-color: #cccccc;}
    .arrow_top{border-color: #fff;color: #fff;}
    /**********视频上传***********/
    .icon_video{background:url("/Sales/Sales/sales/images/images/icon_video.png") no-repeat scroll 0 0;width:22px;height: 14px;}
    .icon_emot_photo_video{display: inline-block;margin:0 4px 0 0}
    .video_box_outside{position: absolute;left:300px;top:200px;z-index: 1000;display: none}
    .video_box{padding: 27px 16px 20px 16px;width: 408px;background-color: #FFF;border: 1px solid #ccc;border-radius: 3px;box-shadow: 0 4px 20px 1px rgba(0, 0, 0, 0.2);position: relative}
    .video_box_outside .arrow_layer{left:26px}
    .video_notice{color: #808080;margin:0 0 15px}
    .video_textarea_upload{border: 1px solid #d9d9d9;box-shadow: 0 0 3px 0 rgba(0, 0, 0, 0.15) inset;height: 152px;padding: 7px 9px 9px 9px;position: relative;}
    .video_textarea_upload textarea{border: none;box-shadow: none;height: 85px;width: 100%;padding: 5px 0 0 2px;line-height: 18px;background-color: #fff;overflow-y: auto;font-size: 12px;}
    .video_upload_btn{border: 2px dashed #ccc;color: #ccc;display: inline-block;font-size: 40px;font-weight: 700;height: 58px;line-height: 52px;text-align: center;width: 58px;vertical-align: bottom;}
    .video_upload_btn:hover{border-color: #eb7350;}
    .video_upload_words{position: absolute;bottom:12px}
    #video_words_num{color:#808080;position: absolute;bottom: 12px;right:9px}
    .clicked{border-color: #eb7350;}
    .video_name_box{vertical-align: bottom;border:1px solid #d9d9d9;padding: 0 5px;box-shadow: none;text-align: left;height: 20px;line-height: 21px;border-radius: 2px;color: #eb7350;background-color: #f2f2f5;position: relative;width:165px;display:none}
    .video_name_box:hover{border-color: #cccccc;box-shadow: 0 1px 1px rgba(0, 0, 0, 0.15);}
    .video_name_box .photo_upload_close{right:3px;top:4.5px}
    #video_file_name{display: inline-block;max-width: 110px;vertical-align: top;color: #eb7350;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;word-wrap: normal;}
    .upload_video_area{text-align: center;margin:20px 0 0}
    .loading_bar{background: #f2f2f5 none repeat scroll 0 0;border-radius: 6px;display: inline-block;font-size: 0;height: 10px;overflow: hidden;text-align: left;width: 250px;}
    .loading_bar em{background: #fa7d3c none repeat scroll 0 0;display: inline-block;height: 10px;vertical-align: top;}
    .layer_point{margin: 60px 0 0;text-align: center;}
    .layer_point .point dd p{text-align: center;}
    #updesc,#upload_succ{padding: 12px 0 5px;font-size: 14px}
    .upload_know{margin:30px 0 0}
    #video_upload_cancel{color:#eb7350}
    .photo_upload_close{background:url("/Sales/Sales/sales/images/images/local_upload_close.png") no-repeat scroll 0 0;display: block;height: 11px;width: 11px;position: absolute;right:7px;top:8px}
    .send_succpic{display: none;height: 42px;left: 0;margin-top: -21px;overflow: hidden;position: absolute;text-align: center;top: 50%;width: 100%;}
    .send_succpic .txt{display: inline-block;font: 16px/22px Microsoft Yahei;margin: 0 0 0 10px;vertical-align: middle;}
    .send_succpic .icon_succB, .send_succpic .icon_errorB{vertical-align: middle;}
    .icon_succB{background-position: -375px 0;}
    .icon_succB, .icon_warnB, .icon_questionB, .icon_rederrorB{height: 42px;width: 38px;}
    .W_icon{background-image: url("/Sales/Sales/sales/images/images/icon.png");background-repeat: no-repeat;display: inline-block;}

    .disabled{background: #ffc09f none repeat scroll 0 0;}
    .disabled:hover{background: #ffc09f none repeat scroll 0 0;opacity:1;filter:alpha(opacity=100);cursor:default}
</style>


<!--<div class="demo clearfix" style="width:600px;margin:120px auto">
    <a onclick="showVideoUploadBox($('#btn_video'))" id="btn_video" class="item"><i class="icon_emot_photo_video icon_video"></i><span>视频</span></a>
</div>-->
<!---------视频上传--------->
<div class="video_box_outside" id="video_box_outside" tabindex="2001">
    <div class="video_box">
        <a class="photo_upload_close"href="javascript:void(0);"onclick="fadeout_div('#video_box_outside')"></a>
        <div id="video_upload_area">
            <div  class="video_notice">支持上传10M以内的视频</div>
            <div class="video_textarea_upload" id="video_textarea_upload">
                <!-- <textarea id="video_text" onkeyup="checkVideoText(this.value)" placeholder="点击此处输入正文..." autocomplete="off" maxlength="130"></textarea>-->
                <div class="video_upload_words">
                    <a class="video_upload_btn" id="video_upload_btn">+</a>
                    <a class="video_name_box" id="video_name_box" href="javascript:void(0)">
                        <img src="<?php echo Yii::app()->baseUrl;?>/images/images/icon_video.png" style="width:13.5px;height: 9px"/>
                        <em id="video_file_name"></em>
                        <span class="photo_upload_close" onclick="reupload_video()" href="javascript:void(0);"></span>
                    </a>
                </div>
                <span id="video_words_num">0</span>
            </div>
        </div>
        <div class="layer_point">
            <dl id="video_loading" class="point clearfix" style="display: none;padding: 0 0 60px">
                <dt style="" id="loading_bar">
                    <span class="loading_bar"><em id="percent" style="width: 27%;"></em></span>
                    <span id="percentnum" class="S_txt2 load_num">27%</span>
                </dt>
                <dd>
                    <p id="updesc">视频上传中...</p>
                    <p class="S_txt2"><label id="uploading_tip">上传速度取决于您的网速，请耐心等待。</label><a href="javascript:void(0);" id="video_upload_cancel" onclick="video_upload_cancel()">取消上传</a>
                    </p>
                </dd>
            </dl>
            <div id="video_success" style="display: none">
                <dl class="point clearfix">
                    <dt>
                        <span class="W_icon icon_succB"></span>
                    </dt>
                    <dd>
                        <p id="upload_succ">视频上传成功</p>
                    </dd>
                </dl>
                <div class="upload_know">
                    <a class="btn" href="javascript:void(0);" onclick="fadeout_div('#video_box_outside')">我知道了</a>
                </div>
            </div>
        </div>
        <div class="upload_video_area" id="upload_video_area">
            <a id="upload_video" class="btn disabled" href="javascript:void(0);">开始上传</a>
        </div>
        <div class="arrow_layer"><span class="arrow_top_area"><i class="arrow_top_bg"></i><em class="arrow_top"></em></span></div>
    </div>
    <div id="video_iput"></div>
</div>
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl;?>/js/js/plugins/plupload/plupload.full.min.js"></script>
<input id="baseUrl" type="hidden" value="<?php echo Yii::app()->baseUrl;?>"/>
<input id="ajaxUrl" type="hidden" value="<?php echo Yii::app()->createUrl('salesvideo/VideoAjax');?>"/>
<script type="text/javascript">
    var url=$("#baseUrl").val();
    var ajaxUrl=$("#ajaxUrl").val();
    var sellers_notes=$("#seller_notes").val();
    var uploader_video = new plupload.Uploader({//创建实例的构造方法
        runtimes: 'gears,html5,html4,silverlight,flash', //上传插件初始化选用那种方式的优先级顺序
        browse_button: ['video_upload_btn'], // 上传按钮
        url:url+"/index.php/salesvideo/VideoAjax", //远程上传地址
        flash_swf_url:url+'/js/js/plugins/plupload/Moxie.swf', //flash文件地址
        silverlight_xap_url: url+'/js/js/plugins/plupload/Moxie.xap', //silverlight文件地址
        filters: {
            max_file_size: '10mb', //最大上传文件大小（格式100b, 10kb, 10mb, 1gb）
            mime_types: [//允许文件上传类型
                {title: "files", extensions: "mpg,m4v,mp4,flv,3gp,mov,avi,rmvb,mkv,wmv"}
            ]
        },
        //       chunk_size: "5mb", //分片上传文件时，每片文件被切割成的大小，为数字时单位为字节。也可以使用一个带单位的字符串，如"200kb"。当该值为0时表示不使用分片上传功能
        multi_selection: false, //true:ctrl多文件上传, false 单文件上传
        init: {
            FilesAdded: function(up, files) { //文件上传前
                $("#video_name_box").css({"display": "inline-block"});
                $("#video_upload_btn").hide();
                $("#video_file_name").text(files[0].name);
                $("#upload_video").removeClass("disabled");
                $("#upload_video").click(function() {
                    uploader_video.start(); //调用实例对象的start()方法开始上传文件，当然你也可以在其他地方调用该方法
                });
                $("#video_iput").attr("file_id", files[0]['id']);
                //                    console.log(files);
            },
            UploadProgress: function(up, file) { //上传中，显示进度条
                $("#video_loading").show();
                $('#upload_video_area,#video_upload_area').hide();
                var percent = file.percent;
                $("#percent").css({"width": percent + "%"});
                $("#percentnum").text(percent + "%");
                $("#video_success").hide();

            },
            FileUploaded: function(up, file, info) { //文件上传成功的时候触发

                $("#video_loading").hide();
                $("#video_success").show();
                var data = eval("(" + info.response + ")");//解析返回的json数据
                console.log(info.response);
                $("#video_iput").html("<input type='hidden' id='video_file' value='" + data.pic + "'/><input type='hidden' id='video_name' value='" + data.name + "'/>");
            },
            Error: function(up, err) { //上传出错的时候触发
                alert(err.message);
            }
        }
    });
    uploader_video.init();

    function showVideoUploadBox(obj) { //显示上传弹出层
        var left = obj.offset().left;
        var top = obj.offset().top + 26;
        var z_index_init = 1000;
        if ($("#post_box").css("display") == 'block') {
            z_index_init = 3000;
        }
        $("#photo_upload_box_outside").css({"z-index": z_index_init});

        $("#video_box_outside").css({"left": left, "top": top, "z-index": z_index_init + 1}).show();
        $("#video_upload_area").show();
        $("#video_loading,#video_success").hide();
        reupload_video();
    }
    function reupload_video() { //重新上传
        $('#video_upload_btn').show();
        $('#video_name_box').hide();
        $("#upload_video_area").show().addClass("disabled");
        $("#video_text").val("");
        $("#video_words_num").text(0);
        $("#video_success").hide();
        $("#video_file,#video_name").val("");
    }

    function checkVideoText(value) {
        var length = value.length;
        var other = 130 - length;
        if (length > 0) {
            $("#video_words_num").html(other);
        } else {
            $("#video_words_num").html("<span class='red'>" + other + "</span>");
        }
    }
    function video_upload_cancel() {  //取消上传
        uploader_video.stop();
        $("#video_loading,#video_name_box").hide();
        $("#video_upload_area,#video_upload_btn").show();
        $("#upload_video_area").show();
        $("#upload_video").addClass("disabled");
        $("#video_text").val("");
        $("#video_words_num").text(0);
        $("#video_success").hide();
        $("#video_file,#video_name").val("");
        var file_id = $("#video_iput").attr("file_id");
        var obj_file = uploader_video.getFile(file_id);
        uploader_video.removeFile(obj_file);
    }
    function fadeout_div(id) {
        $(id).fadeOut();
    }
</script>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = "
$('#VideoForm_video_info_date').on('change',function() {
	showRenewDate();
});
function showRenewDate() {
	var sdate = $('#StaffForm_ctrt_start_dt').val();
	var period = $('#StaffForm_ctrt_period').val();
	if (IsDate(sdate) && IsNumeric(period)) {
		var d = new Date(sdate);
		d.setMonth(d.getMonth() + Number(period));
		$('#StaffForm_ctrt_renew_dt').val(formatDate(d));
	}
	if (period=='') $('#StaffForm_ctrt_renew_dt').val('');
}

function formatDate(val) {
	var day = '00'+val.getDate();
	var month = '00'+(val.getMonth()+1);
	var year = val.getFullYear();
	return year + '/' + month.slice(-2) + '/' +day.slice(-2);
}

function IsDate(val) {
	var d = new Date(val);
	return (!isNaN(d.valueOf()));
}

function IsNumeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}
";

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);

if ($model->scenario!='view') {
    $js = Script::genDatePicker(array(
        'VideoForm_video_info_date',
    ));
    Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>
</html>