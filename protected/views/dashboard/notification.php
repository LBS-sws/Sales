		<div class="box box-primary direct-chat direct-chat-primary">
			<div class="box-header with-border">
				<h3 class="box-title"><?php echo Yii::t('sales','Notifications'); ?></h3>

				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
				</div>
			</div>
            <!-- /.box-header -->
<?php
	$notification = new Notification();
	$time_range = 7*24;
	$records = $notification->getNewMessageByTime($time_range);
	echo TbHtml::hiddenField('notify_last_id',(empty($records) ? 0 : $records[0]['id']));
?>
            <div class="box-body" style="height: 270px;">
				<div id='notify' class="direct-chat-messages">
<?php
	foreach ($records as $record) {
		$line = '<div class="direct-chat-msg">';
		$line .= '<div class="direct-chat-info clearfix">';
		$line .= '<span class="direct-chat-timestamp pull-left">'.$record['lcd'].'</span>';
		$line .= "</div>";
		$line .= '<img class="direct-chat-img" src="'.Yii::app()->baseUrl."/images/cmpy_small.jpg".'" alt="Message User Image">';
        $line .= '<div class="direct-chat-text">'.$record['message'].'</div>';
		$line .= "</div>\n";
		echo $line;
	}
?>
				</div>
			</div>
			<!-- /.box-body -->

            <div class="box-footer">
				<small><?php
							//echo str_replace('{hr}', $time_range, Yii::t('sales','Latest notifications within {hr} hours'));
							echo str_replace('{hr}', $time_range/24, Yii::t('sales','Latest notifications within {hr} days'));
						?>
				</small>
			</div>
			<!-- /.box-footer -->
		</div>
		<!-- /.box -->

<?php
$link = Yii::app()->createAbsoluteUrl("dashboard/notify");
$imgpath = Yii::app()->baseUrl."/images/cmpy_small.jpg";
$js = <<<EOF
function refreshNotification() {
	var id = $('#notify_last_id').val();
	var data = "id="+id;
	var imgpath = '$imgpath';
	$.ajax({
		type: 'GET',
		url: '$link',
		data: data,
		success: function(data) {
			if (data !== undefined && data.length != 0) {
				var line = '';
				for (var i=0; i < data.length; i++) {
					line += '<div class="direct-chat-msg"><div class="direct-chat-info clearfix">';
					line += '<span class="direct-chat-timestamp pull-left">'+data[i].lcd+'</span>';
					line += '</div>';
					line += '<img class="direct-chat-img" src="'+imgpath+'" alt="Message User Image">';
					line += '<div class="direct-chat-text">'+data[i].message+'</div>';
					line += '</div>';
				}	
				
				var original = $('#notify').html();
				if (line != '') {
					$('#notify_last_id').val(data[0].id);
					$('#notify').html(line+original);
				};
			}
		},
		error: function(xhr, status, error) { // if error occured
			var err = eval("(" + xhr.responseText + ")");
			console.log(err.Message);
		},
		complete: function() {
			setTimeout(refreshNotification, 60000);
		},
		dataType:'json'
	});
}
EOF;
Yii::app()->clientScript->registerScript('notificationRefresh',$js,CClientScript::POS_HEAD);

$js = "setTimeout(refreshNotification, 60000);";
Yii::app()->clientScript->registerScript('autoRefresh',$js,CClientScript::POS_READY);
?>