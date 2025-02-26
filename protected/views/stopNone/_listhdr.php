<tr>
    <th></th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('bold_service').$this->drawOrderArrow('d.bold_service'),'#',$this->createOrderLink('stopNone-list','d.bold_service'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('back_date').$this->drawOrderArrow('d.back_date'),'#',$this->createOrderLink('stopNone-list','d.back_date'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('status_dt').$this->drawOrderArrow('a.status_dt'),'#',$this->createOrderLink('stopNone-list','a.status_dt'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('company_name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('stopNone-list','b.name'))
        ;
        ?>
    </th>
    <?php if (!Yii::app()->user->isSingleCity()): ?>
        <th>
            <?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('j.name'),'#',$this->createOrderLink('stopNone-list','j.name'))
            ;
            ?>
        </th>
    <?php endif ?>
    <th>
        <?php echo TbHtml::link($this->getLabelName('description').$this->drawOrderArrow('f.description'),'#',$this->createOrderLink('stopNone-list','f.description'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('amt_paid').$this->drawOrderArrow('a.amt_paid'),'#',$this->createOrderLink('stopNone-list','a.amt_paid'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('salesman').$this->drawOrderArrow('h.name'),'#',$this->createOrderLink('stopNone-list','h.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('staff_id').$this->drawOrderArrow('d.staff_id'),'#',$this->createOrderLink('stopNone-list','d.staff_id'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('back_name').$this->drawOrderArrow('d.back_type'),'#',$this->createOrderLink('stopNone-list','d.back_type'))
        ;
        ?>
    </th>
</tr>
