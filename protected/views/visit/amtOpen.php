<?php
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'amtOpenDialog',
    'header'=>Yii::t('sales','amt hint'),
    'show'=>false,
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','OK'), array('submit'=>Yii::app()->createUrl('visit/save'),'color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
    ),
));
?>
    <div id="amtHintDiv"></div>
<?php
$this->endWidget(); 
?>

