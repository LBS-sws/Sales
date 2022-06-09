<tr>
    <th></th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('back_date').$this->drawOrderArrow('d.back_date'),'#',$this->createOrderLink('stopAgain-list','d.back_date'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('status_dt').$this->drawOrderArrow('a.status_dt'),'#',$this->createOrderLink('stopAgain-list','a.status_dt'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('company_name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('stopAgain-list','b.name'))
        ;
        ?>
    </th>
    <?php if (!Yii::app()->user->isSingleCity()): ?>
        <th>
            <?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('j.name'),'#',$this->createOrderLink('stopAgain-list','j.name'))
            ;
            ?>
        </th>
    <?php endif ?>
    <th>
        <?php echo TbHtml::link($this->getLabelName('description').$this->drawOrderArrow('f.description'),'#',$this->createOrderLink('stopAgain-list','f.description'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('amt_paid').$this->drawOrderArrow('a.amt_paid'),'#',$this->createOrderLink('stopAgain-list','a.amt_paid'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('salesman').$this->drawOrderArrow('h.name'),'#',$this->createOrderLink('stopAgain-list','h.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('back_name').$this->drawOrderArrow('g.type_name'),'#',$this->createOrderLink('stopAgain-list','g.type_name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('again_end_date').$this->drawOrderArrow('again_end_date'),'#',$this->createOrderLink('stopAgain-list','again_end_date'))
        ;
        ?>
    </th>
</tr>
