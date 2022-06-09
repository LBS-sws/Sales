<?php

class Counter {

//終止客戶回訪
    public static function countShiftBack() {
        $model = new StopBackList();
        if(StopBackList::getEmployee($model)){
            return $model->countNotify();
        }else{
            return 0;
        }
    }

//再次回访列表
    public static function countShiftAgain() {
        $model = new StopAgainList();
        if(StopBackList::getEmployee($model)){
            return $model->countNotify();
        }else{
            return 0;
        }
    }

//轉移終止客戶回訪
    public static function countShiftOther() {
        $model = new StopOtherList();
        return $model->countNotify();
    }

//轉移終止客戶回訪
    public static function countShiftNone() {
        $model = new StopNoneList();
        return $model->countNotify();
    }
}

?>