<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('effect_date').$this->drawOrderArrow('effect_date'),'#',$this->createOrderLink('clubSetting-list','effect_date'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('explain_text').$this->drawOrderArrow('explain_text'),'#',$this->createOrderLink('clubSetting-list','explain_text'))
			;
		?>
	</th>
    <?php
    $list = ClubSettingForm::settingList();
    foreach ($list as $setting){
        echo "<th>";
        echo TbHtml::link(Yii::t("club",$setting["name"]),'#');
        echo "</th>";
    }
    ?>
</tr>
