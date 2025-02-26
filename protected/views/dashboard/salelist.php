<div class="box box-primary" >
    <div class="box-header with-border">
        <h3 class="box-title"><?php echo Yii::t('report','List of regional sales per capita order signing volume');?>(<?php echo Yii::t('report',date('F', strtotime(date('Y-m-01') ))); ?>)</h3>


        <!--            <div class="box-tools pull-right">-->
        <!--                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>-->
        <!--            </div>-->
    </div>
    <!-- /.box-header -->

    <div class="box-body">
        <div id='salelist' class="direct-chat-messages" style="height: 250px;">
            <div class="overlay">
                <i class="fa fa-refresh fa-spin"></i>
            </div>
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <small><?php echo Yii::t('report','Refresh data immediately when signing');?></small>
    </div>
    <!-- /.box-footer -->
</div>
<!-- /.box -->





<?php
$link = Yii::app()->createAbsoluteUrl("dashboard/salelist");
$paiming= Yii::t('report','ranking');
$city= Yii::t('report','city');
$quyu= Yii::t('report','quyu');
$sum= Yii::t('report','sum');
$renjun= Yii::t('report','renjun');
$js = <<<EOF
	$.ajax({
		type: 'GET',
		url: '$link',
		success: function(data) {
			if (data !== undefined && data.length != 0) {
				var line = '<table class="table table-bordered small">';
                line += '<tr><td><b>$paiming</b></td><td><b>$city</b></td><td><b>$quyu</b></td><td><b>$sum</b></td><td><b>$renjun</b></td></tr>';
				
				for (var i=0; i < data.length; i++) {
					line += '<tr>';
					style = '';
					switch(i) {
						case 0: style = 'style="color:#FF0000"'; break;
						case 1: style = 'style="color:#871F78"'; break;
						case 2: style = 'style="color:#0000FF"'; break;
					}
					rank = i+1;
					line += '<td '+style+'>'+rank+'</td><td '+style+'>'+data[i].city+'</td><td '+style+'>'+data[i].quyu+'</td><td '+style+'>'+data[i].people+'</td><td '+style+'>'+data[i].renjun+'</td>';
					line += '</tr>';
				}	
				
				line += '</table>';
				$('#salelist').html(line);
			}
		},
		error: function(xhr, status, error) { // if error occured
			var err = eval("(" + xhr.responseText + ")");
			console.log(err.Message);
		},
		dataType:'json'
	});
EOF;
Yii::app()->clientScript->registerScript('salelistDisplay',$js,CClientScript::POS_READY);

?>