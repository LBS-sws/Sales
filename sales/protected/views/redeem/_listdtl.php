
<tr class='clickable-row' data-href=''>
    <td>

    </td>

    <td class="integral_name"><?php echo $this->record['gift_name']; ?></td>
    <td class="integral_name"><?php echo $this->record['city']; ?></td>
    <td class="integral_num"><?php echo $this->record['bonus_point']; ?></td>
    <td><?php echo $this->record['inventory']; ?></td>
    <td>
        <?php
        echo TbHtml::button('<span class="fa  fa-cube"></span> '.Yii::t('redeem','Exchange'), array(
                'class'=>'btnIntegralApply','data-id'=>$this->record['id'])
        );
        ?>
    </td>
</tr>


<script>
    $(function () {
        $(".btnIntegralApply").on("click",function () {
            var $tr = $(this).parents("tr:first");
            $("#gift_type").val($(this).data("id"));
            $("#gift_name").val($tr.find(".integral_name:first").text());
            $("#bonus_point").val($tr.find(".integral_num:first").text());
            $('#integralApply').modal('show');
            return false;
        })
    })
</script>