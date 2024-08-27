<?php

class CABotForm extends KABotForm{

    protected $function_id='CN17';
    protected $table_pre='_ca_';
    public $file_key='cabot';

    public static function getBotHistoryRows($bot_id){
        $rows = Yii::app()->db->createCommand()->select("id,update_html,lcu,lcd")
            ->from("sal_ca_bot_history")
            ->where("bot_id=:bot_id",array(":bot_id"=>$bot_id))->order("lcd desc")->queryAll();
        return $rows;
    }

    protected function lenStr(){
        $code = strval($this->id);
        $this->customer_no = "DF";
        for($i = 0;$i < 5-strlen($code);$i++){
            $this->customer_no.="0";
        }
        $this->customer_no .= $code;
    }
}
