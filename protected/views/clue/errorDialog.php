<!--验证提示-->
<?php
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'open-error-Dialog',
    'header'=>"验证信息",
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal'
        )),
    ),
    'show'=>false,
));
?>
<div id="open-error-div">

</div>
<?php $this->endWidget(); ?>
<?php
$js = <<<EOF
function showFormErrorHtml(html){
    $('#open-error-div').html(html);
    $('#open-error-Dialog').modal('show');
}

$("#open-error-Dialog").on('hidden.bs.modal',function(){
    $('.modal.fade').each(function(){
        if($(this).hasClass('in')){
            $('body').addClass('modal-open');
        }
    });
});
EOF;
Yii::app()->clientScript->registerScript('openErrorDialog',$js,CClientScript::POS_READY);
?>
