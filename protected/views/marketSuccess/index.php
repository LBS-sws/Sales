<?php
$this->pageTitle=Yii::app()->name . ' - Visit Type';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Market Success'); ?></strong>
	</h1>
</section>

<section class="content">
    <div class="box">
        <div class="box-body">
            <div class="btn-group" role="group">
                <?php
                if (Yii::app()->user->validRWFunction('MT04')){
                    echo TbHtml::button('<span class="fa fa-flag"></span> '.Yii::t('market','ready all'), array(
                            'data-toggle'=>'modal','submit'=>Yii::app()->createUrl('marketSuccess/readyAll'))
                    );
                }
                ?>
            </div>
        </div>
    </div>
	<?php
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('market','Company List'),
        'model'=>$model,
        'viewhdr'=>'//marketSuccess/_listhdr',
        'viewdtl'=>'//marketSuccess/_listdtl',
        'search'=>array(
            'number_no',
            'company_name',
            'person_phone',
            'allot_city',
            'employee_name',
        ),
    ));
	?>
</section>
<?php
	echo $form->hiddenField($model,'status_type');
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');

	echo CHtml::hiddenField('assign_id');
	echo CHtml::button('test_btn',array('submit'=>'','class'=>'hide'));
?>

<?php $this->endWidget(); ?>
<?php
$url = Yii::app()->createUrl('marketSuccess/index',array("pageNum"=>1));
$js = <<<EOF
function showdetail(id) {
	var icon = $('#btn_'+id).attr('class');
	if (icon.indexOf('plus') >= 0) {
		$('.detail_'+id).show();
		$('#btn_'+id).attr('class', 'fa fa-minus-square');
	} else {
		$('.detail_'+id).hide();
		$('#btn_'+id).attr('class', 'fa fa-plus-square');
	}
}

$('.click-td').on('click',function(e){
    var id = $(this).data('id');
    if($('#btn_'+id).length>=1){
        showdetail(id);
    }
    e.stopPropagation();
});


$('.che').on('click', function(e){
    e.stopPropagation();
});

$('body').on('click','#all',function() {
	var val = $(this).prop('checked');
	$('.che').children('input[type=checkbox]').prop('checked',val);
});

function totalCheckBoxList(){
    var list = [];
    $('input[type=checkbox]:checked').each(function(){
        var id = $(this).val();
        if(id!=''&&list.indexOf(id)==-1&&$(this).parent('td.che').length==1){
            list.push(id);
        }
    });
    list = list.join(',');
    $('#assign_id').val(list);
};

$('form').submit(function(){
    totalCheckBoxList();
});
EOF;
$js.= Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
	//Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>

