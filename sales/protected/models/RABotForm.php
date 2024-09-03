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

    public function copyToKA(){
        $suffix = Yii::app()->params['envSuffix'];
        echo "start:";
        echo "<br/>";
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_ra_bot")
            ->order("id asc")->queryAll();
        echo "copy count:".count($rows);
        echo "<br/>";
        if($rows){
            foreach ($rows as $row){
                echo "copy:".$row["customer_no"];
                $old_id = $row["id"];
                $row["customer_no"]="COPY_".$row["customer_no"];
                echo " to ".$row["customer_no"];
                $kaRow = Yii::app()->db->createCommand()->select("*")->from("sal_ka_bot")
                    ->where("customer_no=:customer_no",array(":customer_no"=>$row["customer_no"]))->queryRow();
                if($kaRow){
                    echo " error!<br/>";
                    continue;
                }
                echo " success!<br/>";
                unset($row["id"]);
                Yii::app()->db->createCommand()->insert("sal_ka_bot",$row);
                $add_id = Yii::app()->db->getLastInsertID();
                $avaRows = Yii::app()->db->createCommand()->select("*")->from("sal_ra_bot_ava")
                    ->where("bot_id=:bot_id",array(":bot_id"=>$old_id))->queryAll();
                if($avaRows){
                    foreach ($avaRows as $avaRow){
                        unset($avaRow['id']);
                        $avaRow["bot_id"] = $add_id;
                        Yii::app()->db->createCommand()->insert("sal_ka_bot_ava",$avaRow);
                    }
                }
                $hisRows = Yii::app()->db->createCommand()->select("*")->from("sal_ra_bot_history")
                    ->where("bot_id=:bot_id",array(":bot_id"=>$old_id))->queryAll();
                if($hisRows){
                    foreach ($hisRows as $hisRow){
                        unset($hisRow['id']);
                        $hisRow["bot_id"] = $add_id;
                        Yii::app()->db->createCommand()->insert("sal_ka_bot_history",$hisRow);
                    }
                }
                $infoRows = Yii::app()->db->createCommand()->select("*")->from("sal_ra_bot_info")
                    ->where("bot_id=:bot_id",array(":bot_id"=>$old_id))->queryAll();
                if($infoRows){
                    foreach ($infoRows as $infoRow){
                        unset($infoRow['id']);
                        $infoRow["bot_id"] = $add_id;
                        Yii::app()->db->createCommand()->insert("sal_ka_bot_info",$infoRow);
                    }
                }
                //复制附件
                $docRow = Yii::app()->db->createCommand()->select("*")->from("docman{$suffix}.dm_master")
                    ->where("doc_id=:doc_id and doc_type_code='RABOT'",array(":doc_id"=>$old_id))->queryRow();
                if($docRow){
                    $old_doc_id = $docRow["id"];
                    unset($docRow["id"]);
                    $docRow["doc_type_code"]="KABOT";
                    $docRow["doc_id"]=$add_id;
                    Yii::app()->db->createCommand()->insert("docman{$suffix}.dm_master",$docRow);
                    $add_doc_id = Yii::app()->db->getLastInsertID();

                    $fileRows = Yii::app()->db->createCommand()->select("*")->from("docman{$suffix}.dm_file")
                        ->where("mast_id=:mast_id",array(":mast_id"=>$old_doc_id))->queryAll();
                    if($fileRows){
                        foreach ($fileRows as $fileRow){
                            unset($fileRow["id"]);
                            $fileRow["mast_id"] =$add_doc_id;
                            Yii::app()->db->createCommand()->insert("docman{$suffix}.dm_file",$fileRow);
                        }
                    }
                }
            }
        }
        echo "<br/>";
        echo "success!";
    }
}
