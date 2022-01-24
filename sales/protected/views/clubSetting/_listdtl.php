<tr class='clickable-row' data-href='<?php echo $this->getLink('HC10', 'clubSetting/edit', 'clubSetting/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HC10', 'clubSetting/edit', 'clubSetting/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['effect_date']; ?></td>
	<td><?php echo $this->record['explain_text']; ?></td>

    <?php
    $list = ClubSettingForm::settingList();
    foreach ($list as $key => $setting){
        echo "<td>";
        echo ClubSettingList::getSalesStrForList($this->record['set_json'][$key]);
        echo "</td>";
    }
    ?>
</tr>
