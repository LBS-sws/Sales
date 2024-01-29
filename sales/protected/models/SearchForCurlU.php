<?php

/**
 * curl查询U系统
 * User: Administrator
 * Date: 2024/1/29 0029
 * Time: 10:54
 */
class SearchForCurlU
{

    //获取U系统的服务单数据(城市分类)
    public static function getCurlServiceForCity($startDay,$endDay,$city_allow=""){
        $list = SystemU::getUServiceMoney($startDay,$endDay,$city_allow);
        return isset($list["data"])?$list["data"]:array();
    }

    //获取U系统的服务单数据(城市的月份分类)
    public static function getCurlServiceForMonth($startDay,$endDay,$city_allow=""){
        $list = SystemU::getUServiceMoneyToMonth($startDay,$endDay,$city_allow);
        return isset($list["data"])?$list["data"]:array();
    }

    //获取U系统的服务单数据(城市的周一分类)
    public static function getCurlServiceForWeek($startDay,$endDay,$city_allow=""){
        $list = SystemU::getUServiceMoneyToWeek($startDay,$endDay,$city_allow);
        return isset($list["data"])?$list["data"]:array();
    }

    //获取U系统的技术员金额（技术员已分离）
    public static function getCurlTechnicianMoney($startDay,$endDay,$city_allow=""){
        $list = SystemU::getTechnicianMoney($startDay,$endDay,$city_allow);
        return isset($list["data"])?$list["data"]:array();
    }

    //获取U系统的INV数据(账单详情)
    public static function getCurlInvDetail($startDay,$endDay,$city_allow=""){
        $list = SystemU::getInvDataDetail($startDay,$endDay,$city_allow);
        return isset($list["data"])?$list["data"]:array();
    }

    //获取U系统的INV数据(城市分类)
    public static function getCurlInvForCity($startDay,$endDay,$city_allow=""){
        $list = SystemU::getInvDataCityAmount($startDay,$endDay,$city_allow);
        return isset($list["data"])?$list["data"]:array();
    }

    //获取U系统的INV数据(城市的月份分类)
    public static function getCurlInvForMonth($startDay,$endDay,$city_allow=""){
        $list = SystemU::getInvDataCityMonth($startDay,$endDay,$city_allow);
        return isset($list["data"])?$list["data"]:array();
    }

    //获取U系统的INV数据(城市的周一分类)
    public static function getCurlInvForWeek($startDay,$endDay,$city_allow=""){
        $list = SystemU::getInvDataCityWeek($startDay,$endDay,$city_allow);
        return isset($list["data"])?$list["data"]:array();
    }

    //获取U系统的技术员金额列表（需要分开多个技术员）
    public static function getCurlTechnicianDetail($startDay,$endDay,$city_allow=""){
        $list = SystemU::getTechnicianDetail($startDay,$endDay,$city_allow);
        return isset($list["data"])?$list["data"]:array();
    }
}