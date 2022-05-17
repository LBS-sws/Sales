<tr>
    <th></th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('bold_service').$this->drawOrderArrow('d.bold_service'),'#',$this->createOrderLink('stopBack-list','d.bold_service'))
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
        <?php echo TbHtml::link($this->getLabelName('service').$this->drawOrderArrow('a.service'),'#',$this->createOrderLink('stopBack-list','a.service'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('cont_info').$this->drawOrderArrow('a.cont_info'),'#',$this->createOrderLink('stopBack-list','a.cont_info'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('salesman').$this->drawOrderArrow('h.name'),'#',$this->createOrderLink('stopBack-list','h.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('status_dt').$this->drawOrderArrow('a.status_dt'),'#',$this->createOrderLink('stopBack-list','a.status_dt'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('shiftStatus').$this->drawOrderArrow('d.back_date'),'#',$this->createOrderLink('stopBack-list','d.back_date'))
        ;
        ?>
    </th>
</tr>
