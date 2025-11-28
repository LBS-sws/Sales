<?php
/**
 * 操作
 */
?>
<style>
    .trace_div .trace_div_left{ width: 140px;}
    .trace_div .media{margin-top: 0px;overflow: visible;}
    .trace_div .media-body{ position: relative;padding-left: 15px;border-left: 1px solid #cccccc;overflow: visible;}
    .trace_div .media-body:after{ content: " ";position: absolute;top: 3px;left: -6px;width: 10px;height: 10px;border-radius: 50%;background: #fff;border: 1px solid #cccccc;}
    .trace_div .media-body.active:after{ background: #2878ff;border-color:#2878ff; }
</style>
<div  style="padding-top: 15px;">
    <div class="col-lg-11 col-lg-offset-1 trace_div">
        <?php
        $rows = CGetName::getContProRows($model->id);
        $html="";
        if($rows){
            foreach ($rows as $row){
                $html.=$this->renderPartial('//contHead/dv_trace_dl',array("row"=>$row),true);
            }
        }
        echo $html;
        ?>
    </div>
</div>

