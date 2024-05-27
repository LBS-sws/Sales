<?php

class RABotForm extends KABotForm{

    protected $function_id='CN16';
    protected $table_pre='_ra_';
    public $file_key='rabot';

    public static function getBotHistoryRows($bot_id){
        $rows = Yii::app()->db->createCommand()->select("id,update_html,lcu,lcd")
            ->from("sal_ra_bot_history")
            ->where("bot_id=:bot_id",array(":bot_id"=>$bot_id))->order("lcd desc")->queryAll();
        return $rows;
    }

    protected function lenStr(){
        $code = strval($this->id);
        $this->customer_no = "RKA";
        for($i = 0;$i < 5-strlen($code);$i++){
            $this->customer_no.="0";
        }
        $this->customer_no .= $code;
    }
}
