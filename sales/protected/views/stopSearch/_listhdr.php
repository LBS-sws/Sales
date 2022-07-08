<tr>
    <th></th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('bold_service').$this->drawOrderArrow('d.bold_service'),'#',$this->createOrderLink('stopSearch-list','d.bold_service'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('back_date').$this->drawOrderArrow('d.back_date'),'#',$this->createOrderLink('stopSearch-list','d.back_date'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('status_dt').$this->drawOrderArrow('a.status_dt'),'#',$this->createOrderLink('stopSearch-list','a.status_dt'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('company_name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('stopSearch-list','b.name'))
        ;
        ?>
    </th>
    <?php if (!Yii::app()->user->isSingleCity()): ?>
        <th>
            <?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('j.name'),'#',$this->createOrderLink('stopSearch-list','j.name'))
            ;
            ?>
        </th>
    <?php endif ?>
    <th>
        <?php echo TbHtml::link($this->getLabelName('description').$this->drawOrderArrow('f.description'),'#',$this->createOrderLink('stopSearch-list','f.description'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('amt_paid').$this->drawOrderArrow('a.amt_paid'),'#',$this->createOrderLink('stopSearch-list','a.amt_paid'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('salesman').$this->drawOrderArrow('h.name'),'#',$this->createOrderLink('stopSearch-list','h.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('staff_id').$this->drawOrderArrow('d.staff_id'),'#',$this->createOrderLink('stopSearch-list','d.staff_id'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('back_name').$this->drawOrderArrow('g.type_name'),'#',$this->createOrderLink('stopSearch-list','g.type_name'))
        ;
        ?>
    </th>
</tr>
