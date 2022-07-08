<tr>
    <th></th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('bold_service').$this->drawOrderArrow('d.bold_service'),'#',$this->createOrderLink('stopBack-list','d.bold_service'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('back_date').$this->drawOrderArrow('d.back_date'),'#',$this->createOrderLink('stopBack-list','d.back_date'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('status_dt').$this->drawOrderArrow('a.status_dt'),'#',$this->createOrderLink('stopBack-list','a.status_dt'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('company_name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('stopBack-list','b.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('description').$this->drawOrderArrow('f.description'),'#',$this->createOrderLink('stopBack-list','f.description'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('amt_paid').$this->drawOrderArrow('a.amt_paid'),'#',$this->createOrderLink('stopBack-list','a.amt_paid'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('salesman').$this->drawOrderArrow('h.name'),'#',$this->createOrderLink('stopBack-list','h.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('staff_id').$this->drawOrderArrow('d.staff_id'),'#',$this->createOrderLink('stopBack-list','d.staff_id'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('back_name').$this->drawOrderArrow('g.type_name'),'#',$this->createOrderLink('stopBack-list','g.type_name'))
        ;
        ?>
    </th>
</tr>
