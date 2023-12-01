<?php

return array(
//	array('code'=>'XA05','function'=>'Counter::countApprReq','color'=>'bg-yellow'),  /* 例子 */
    array('code'=>'SC01','function'=>'Counter::countShiftBack','color'=>'bg-maroon'),
    array('code'=>'SC07','function'=>'Counter::countShiftAgain','color'=>'bg-purple'),
    array('code'=>'SC02','function'=>'Counter::countShiftOther','color'=>'bg-yellow'),
    array('code'=>'SC06','function'=>'Counter::countShiftNone','color'=>'bg-green'),

    array('code'=>'MT01','function'=>'Counter::marketCompany','color'=>'bg-maroon'),//客户资料库
    array('code'=>'MT02','function'=>'Counter::marketArea','color'=>'bg-maroon'),//地區跟進客戶資料
    array('code'=>'MT03','function'=>'Counter::marketSales','color'=>'bg-yellow'),//銷售跟進客戶資料
    array('code'=>'MT04','function'=>'Counter::marketReject','color'=>'bg-yellow'),//無意向客戶
    array('code'=>'MT05','function'=>'Counter::marketSuccess','color'=>'bg-yellow'),//已完成客戶
);

?>