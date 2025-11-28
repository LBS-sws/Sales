<div class="fre_month_div" data-num=":NUM:">
    <div class="form-group">
        <?php echo TbHtml::label("频次月份","open_fre_month:NUM:",array('class'=>"col-lg-2 control-label",'required'=>true)); ?>

        <div class="col-lg-10">
            <div class="btn-group" role="group" >
                <?php
                for ($i=1;$i<=12;$i++){
                    echo TbHtml::button("{$i}月",array(
                        "data-id"=>"open_fre_month:NUM:",
                        "data-val"=>$i,
                        "class"=>"btn btn-check btn-default",
                    ));
                }
                ?>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?php echo TbHtml::label("服务次数","open_fre_num:NUM:",array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
        <div class="col-lg-5">
            <div class="input-group">
                <div class="input-group-btn" style="width: 80px;">
                    <?php
                    echo TbHtml::dropDownList("open_fre_type_sum:NUM:","",array("3"=>"每月"),array("id"=>"open_fre_type_sum:NUM:",'class'=>'open_fre_type_sum'));
                    ?>
                </div><!-- /btn-group -->
                <?php
                echo TbHtml::numberField("open_fre_num:NUM:","",array("id"=>"open_fre_num:NUM:",'class'=>'open_fre_num','placeholder'=>'次数'));
                ?>
            </div><!-- /input-group -->
        </div>
    </div>
    <div class="form-group">
        <?php echo TbHtml::label("服务金额","open_fre_num:NUM:",array('class'=>"col-lg-2 control-label",'required'=>true)); ?>
        <div class="col-lg-5">
            <div class="input-group">
                <div class="input-group-btn" style="width: 80px;">
                    <?php
                    echo TbHtml::dropDownList("open_fre_type_amt:NUM:","",array("3"=>"每月"),array("id"=>"open_fre_type_amt:NUM:",'class'=>'open_fre_type_amt'));
                    ?>
                </div><!-- /btn-group -->
                <?php
                echo TbHtml::numberField("open_fre_amt:NUM:","",array("id"=>"open_fre_amt:NUM:",'class'=>'open_fre_amt','placeholder'=>'金额'));
                ?>
            </div><!-- /input-group -->
        </div>
    </div>
    <button type="button" class="close hide" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>