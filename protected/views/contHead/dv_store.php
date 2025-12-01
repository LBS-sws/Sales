<?php
/**
 * 门店
 */
?>
<div  style="padding-top: 15px;">
    <div class="btn-group">
        <?php
        if(in_array($model->cont_status,array(10,30))){
            echo TbHtml::button(Yii::t('clue','add cont store'), array(
                'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
                'submit'=>Yii::app()->createUrl('contPro/new',array("cont_id"=>$model->id,"type"=>"NA"))
            ));
            if(Yii::app()->user->validRWFunction('CS01')) {
                echo TbHtml::button(Yii::t('clue', 'call service'), array(
                    'color' => TbHtml::BUTTON_COLOR_PRIMARY,
                    'data-url' => Yii::app()->createUrl('callService/new', array("cont_id" => $model->id)),
                    'id' => "newCallService"
                ));
            }
        }
        ?>
    </div>
    <div class="table-responsive" style="width: 100%;">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th width="1%"><?php echo TbHtml::checkBox("allBox",false,array("class"=>"allBox"))?></th>
                <th><?php echo Yii::t('clue',"store code"); ?></th>
                <th><?php echo Yii::t('clue',"store name"); ?></th>
                <th><?php echo Yii::t('clue',"trade type"); ?></th>
                <th><?php echo Yii::t('clue',"district"); ?></th>
                <th><?php echo Yii::t('clue',"address"); ?></th>
                <th><?php echo Yii::t('clue',"client person"); ?></th>
                <th><?php echo Yii::t('clue',"status"); ?></th>
                <th style="width: 185px;"><?php echo Yii::t('clue',"virtual code"); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $callShow=false;
            $list = CGetName::getClueStoreRowsByContID($model->id);
            if($list){
                $html ="";
                foreach ($list as $key=>$row){
                    $person = $row["cust_person"];
                    $person.= !empty($row["cust_person_role"])?" ({$row["cust_person_role"]})":"";
                    $person.= !empty($row["cust_tel"])?" {$row["cust_tel"]}":"";
                    $virLists =CGetName::getContractVirRowsByContIDAndStoreID($row["cont_id"],$row["id"]);
                    $checkID=array();
                    $html.="<tr>";
                    $html.="<td>";
                    if($virLists){
                        foreach ($virLists as $virList){
                            if($virList["service_fre_type"]==3&&in_array($virList["vir_status"],array(10,30))){
                                $callShow=true;
                                if(!in_array($row["id"],$checkID)){
                                    $checkID[]=$row["id"];
                                }
                            }
                        }
                    }
                    if(empty($checkID)){
                        $html.="&nbsp;";
                    }else{
                        $html.=TbHtml::checkBox("checkOne",false,array("data-val"=>implode(",",$checkID),"class"=>"checkOne"));
                    }
                    $html.="</td>";
                    $html.="<td>".$row["store_code"]."</td>";
                    $html.="<td>".$row["store_name"]."</td>";
                    $html.="<td>".CGetName::getCustClassStrByKey($row["cust_class"])."</td>";
                    $html.="<td>".CGetName::getDistrictStrByKey($row["district"])."</td>";
                    $html.="<td>".$row["address"]."</td>";
                    $html.="<td>".$person."</td>";
                    $html.="<td>".CGetName::getClueStoreStatusByKey($row["store_status"])."</td>";
                    $html.="<td>";
                    $html.="<ul class=\"list-unstyled\">";
                    if($virLists){
                        foreach ($virLists as $virList){
                            $url=Yii::app()->createUrl('virtualHead/detail',array("index"=>$virList['id']));
                            $html.="<li>";
                            $html.=TbHtml::link($virList["vir_code"],$url,array(
                                "target"=>"_blank"
                            ));
                            $html.="</li>";
                        }
                    }
                    $html.="</ul>";
                    $html.="</td>";
                    $html.="</tr>";
                }
                echo $html;
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$js = <<<EOF
$(".allBox").click(function(){
    var bool = $(this).is(':checked');
    $('.checkOne').prop('checked',bool);
});
$("#newCallService").click(function(){
    var url=$(this).data('url');
    var ids=[];
    $(".checkOne:checked").each(function(){
        ids.push($(this).data('val'));
    });
    ids=ids.join(',');
    if(ids==""){
        showFormErrorHtml('请至少选择一个门店');
    }else{
        url+="&store_ids="+ids;
        jQuery.yii.submitForm(this,url,{});
    }
});
EOF;
if($callShow===false){
    $js.= <<<EOF
$("#newCallService").hide();
$(".allBox").hide();
EOF;
}
Yii::app()->clientScript->registerScript('callShow',$js,CClientScript::POS_READY);
?>