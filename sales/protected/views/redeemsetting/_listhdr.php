<tr>
    <th></th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('gift_name').$this->drawOrderArrow('gift_name'),'#',$this->createOrderLink('gift-list','gift_name'))
        ;
        ?>
    </th>
<!--    <th>-->
<!--        --><?php //echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('city'),'#',$this->createOrderLink('gift-list','city'))
//        ;
//        ?>
<!--    </th>-->
    <th>
        <?php echo TbHtml::link($this->getLabelName('bonus_point').$this->drawOrderArrow('bonus_point'),'#',$this->createOrderLink('gift-list','bonus_point'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('inventory').$this->drawOrderArrow('inventory'),'#',$this->createOrderLink('gift-list','inventory'))
        ;
        ?>
    </th>
    <th width="10%"></th>
</tr>
