<!--验证提示-->
<?php
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'open-look-Dialog',
    'header'=>"预览 ("."<span></span>".")",
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal'
        )),
    ),
    'show'=>false,
    'size'=>TbHtml::MODAL_SIZE_LARGE." modal-lg-90",
));
?>
<style>
    .modal-lg.modal-lg-90{ width: 96%;}
</style>
<div style="height:550px">
    <iframe id="lookIframe" src="" frameborder="0" style="width: 100%; height: 100%;" tabindex="0" allowtransparency="true"></iframe>
</div>

<?php $this->endWidget(); ?>
<?php
$url = Yii::app()->createUrl('lookFile/show');
$downUrl = Yii::app()->createUrl('lookFile/down');
$lookUrl = Yii::app()->params['fileLookUrl'];
$js = <<<EOF
    $('body').on('click','.lookFile',function(){
        window.open('{$lookUrl}/onlinePreview?url='+$(this).data('file'));
        //var url = "{$url}?index="+$(this).data('id')+"&tableName="+$(this).data('table');
        //$('#open-look-Dialog').find('.modal-title>span').text($(this).data('name'));
        //$('#open-look-Dialog').modal('show');
        //$('#lookIframe').attr('src',url);
    });
    $('body').on('click','.lookDownFile',function(){
        var url = "{$downUrl}?index="+$(this).data('id')+"&tableName="+$(this).data('table');
        window.open(url);
    });
EOF;
Yii::app()->clientScript->registerScript('openLookFileDialog',$js,CClientScript::POS_READY);
?>
