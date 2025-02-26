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

//客户资料库
    public static function marketCompany() {
        $model = new MarketCompanyList();
        return $model->countNotify();
    }

//地區跟進客戶資料
    public static function marketArea() {
        $model = new MarketAreaList();
        return $model->countNotify();
    }

//銷售跟進客戶資料
    public static function marketSales() {
        $model = new MarketSalesList();
        return $model->countNotify();
    }

//無意向客戶
    public static function marketReject() {
        $model = new MarketRejectList();
        return $model->countNotify();
    }

//已完成客戶
    public static function marketSuccess() {
        $model = new MarketSuccessList();
        return $model->countNotify();
    }
}

?>