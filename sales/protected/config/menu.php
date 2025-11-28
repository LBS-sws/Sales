<?php
//HK01HK02HK03HK04HK05HA01HA02HA03HA04HA05HA06HA07HD01HD02HD03HD09HD01HD02HE01HE02HC01HC02HC03HC04HC05HC06HC07HC08
return array(

	'Data Entry'=>array(
		'access'=>'HK',
		'icon'=>'fa-edit',
		'items'=>array(
			'Sales Visit'=>array(
				'access'=>'HK01',
				'url'=>'/visit/index',
			),
/*
			'Sales orders'=>array(
				'access'=>'HK02',
				'url'=>'/Salesorder/index',
			),
*/
			'Five Steps'=>array(
			'access'=>'HK03',
				'url'=>'/fivestep/index',
			),
			'Sales Search Customer'=>array(
			'access'=>'HK06',
				'url'=>'/SalesSearchCustomer/index',
			),
			'Sales Search Count'=>array(
			'access'=>'HK07',
				'url'=>'/SalesSearchCount/index',
			),
            'Performance Objectives'=>array(
                'access'=>'HK04',
                'url'=>'/performance/index',
            ),
            'Sales Target'=>array(
                'access'=>'HK05',
                'url'=>'/Target/index',
            ),
		),
	),

    'Customer Manage'=>array( //线索管理
        'access'=>'CM',
		'icon'=>'fa-lightbulb-o',//fa-coffee
        'items'=>array(
            'Clue Box'=>array( //线索池
                'access'=>'CM01',
                'url'=>'clueBox/index',
            ),
            'Clue Head'=>array( //线索
                'access'=>'CM02',
                'url'=>'clueHead/index',
            ),
            'Clue Service'=>array( //商机
                'access'=>'CM03',
                'url'=>'clueService/index',
            ),
            'Store List'=>array( //门店列表
                'access'=>'CM04',
                'url'=>'clueStore/index',
            ),
            'Clue Report'=>array( //方案报价
                'access'=>'CM05',
                'url'=>'clueRpt/index',
            ),
            'Customer List'=>array( //客户列表
                'access'=>'CM10',
                'url'=>'clientHead/index',
            ),
        )
    ),


    'Contract Manage'=>array( //合同管理
        'access'=>'CT',
        'icon'=>'fa-archive',
        'items'=>array(
            'Contract List'=>array( //合同列表
                'access'=>'CT01',
                'url'=>'contHead/index',
            ),
            'Virtual List'=>array( //虚拟合同列表
                'access'=>'CT02',
                'url'=>'virtualHead/index',
            ),
            /*
             * CM06
             * CM07
            'Contract Update List'=>array( //变更合同列表
                'access'=>'CM09',
                'url'=>'contPro/index',
            ),
            'Virtual Update List'=>array( //变更虚拟合同列表
                'access'=>'CM08',
                'url'=>'virtualPro/index',
            ),
            */
        )
    ),
    'Call Service'=>array( //呼叫式服务
        'access'=>'CS',
        'icon'=>'fa-phone',
        'items'=>array(
            'Call History'=>array( //呼叫记录
                'access'=>'CS01',
                'url'=>'callService/index',
            ),
        )
    ),
    'Stop Customer'=>array( //終止客戶
        'access'=>'SC',
		'icon'=>'fa-superpowers',
        'items'=>array(
            'Stop Customer Back'=>array( //終止客戶回訪
                'access'=>'SC01',
                'url'=>'stopBack/index',
            ),
            'Stop Customer Again'=>array( //再次回訪列表
                'access'=>'SC07',
                'url'=>'stopAgain/index',
            ),
            'Stop Customer Other'=>array( //轉移終止客戶回訪
                'access'=>'SC02',
                'url'=>'stopOther/index',
            ),
            'Stop Customer None'=>array( //未回访客户查询
                'access'=>'SC06',
                'url'=>'stopNone/index',
            ),
            'Stop Customer Search'=>array( //已回访记录查询
                'access'=>'SC05',
                'url'=>'stopSearch/index',
            ),
            'Stop Customer Site'=>array(// 終止客戶回訪配置
                'access'=>'SC03',
                'url'=>'stopSite/index',
            ),
            'Stop Back Type'=>array(// 回訪客戶類型
                'access'=>'SC04',
                'url'=>'stopType/index',
            ),
        )
    ),

    'Class'=>array(
        'access'=>'HA',
		'icon'=>'fa-database',
        'items'=>array(
            'Visit Steps'=>array(
                'access'=>'HA01',
                'url'=>'report/visit',
            ),
            'Sale Staff'=>array(
                'access'=>'HA02',
                'url'=>'report/staff',
            ),
            'Sales transfer'=>array(
                'access'=>'HA03',
                    'url'=>'/shift/index',
            ),
            'Sales lead'=>array(
                'access'=>'HA04',
                'url'=>'/report/performance',
            ),
            'Sales Turnover'=>array(
                'access'=>'HA07',
                'url'=>'/report/turnover',
            ),
            'Sales ranking list'=>array(
                'access'=>'HA05',
                'url'=>'/rankinglist/index',
            ),
            'Commission integral'=>array(
                'access'=>'HA06',
                'url'=>'/integral/index',
            ),
            'Signed conversion rate'=>array( //签单转化率
                'access'=>'HA08',
                'url'=>'/signedRate/index',
            ),
        ),
    ),

    'Sales segment query'=>array(
        'access'=>'HD',
        'icon'=>'fa-edit',
        'items'=>array(
/*
            'Sales segment details query111'=>array(
                'access'=>'HD09',
                'url'=>'/rankzhixing/index',
            ),
*/
            'Sales segment details query'=>array(
                'access'=>'HD01',
                'url'=>'/rank/index',
            ),
            'Sales history segment query'=>array(
                'access'=>'HD02',
                'url'=>'/rankhistory/index',
            ),
            'Total rank score'=>array(
                'access'=>'HD03',
                'url'=>'/rankscore/index',
            ),
            'Club sales'=>array(
                'access'=>'HD04',
                'url'=>'/clubSales/index',
            ),
            'Club recommend'=>array(
                'access'=>'HD05',
                'url'=>'/clubRec/index',
            ),
        ),
    ),

	'Report'=>array(
		'access'=>'HB',
		'icon'=>'fa-file-text-o',
		'items'=>array(
            'Five Steps'=>array(
                'access'=>'HB02',
                'url'=>'report/five',
            ),
			'Report Manager'=>array(
				'access'=>'HB01',
				'url'=>'/queue/index',
			),
		),
	),
    'Redeem'=>array(
        'access'=>'HE',
        'icon'=>'fa-diamond',
        'items'=>array(
            'Redeem index'=>array(
                'access'=>'HE01',
                'url'=>'redeem/index',
            ),
            'Redeem list'=>array(
                'access'=>'HE02',
                'url'=>'/rgapply/index',
            ),
        ),
    ),

    'KA Manager'=>array( //KA业务管理
        'access'=>'KA',
        'icon'=>'fa-trophy',
        'items'=>array(
            'KA Bot'=>array(//KA項目
                'access'=>'KA01',
                'url'=>'KABot/index',
            ),
            'KA Statistic'=>array(//KA項目統計
                'access'=>'KA02',
                'url'=>'KAStatistic/index',
            ),
            'KA Link'=>array(//当前沟通阶段
                'access'=>'KA04',
                'url'=>'KALink/index',
            ),
            'KA Area'=>array(//客户地区
                'access'=>'KA05',
                'url'=>'KAArea/index',
            ),
            'KA Source'=>array(//客户来源（2024年1月29日改为客户类型）
                'access'=>'KA03',
                'url'=>'KASource/index',
            ),
            'KA Source(A)'=>array(//客户来源（2024年1月29日新加客户来源）
                'access'=>'KA11',
                'url'=>'KASra/index',
            ),
            'KA Class'=>array(//客户类别
                'access'=>'KA10',
                'url'=>'KAClass/index',
            ),
            'KA Level'=>array(//客户分级
                'access'=>'KA09',
                'url'=>'KALevel/index',
            ),
            /*
            'KA Reject'=>array(//拒绝、暂停理由
                'access'=>'KA06',
                'url'=>'KAType/index',
            ),*/
            'KA Busine'=>array(//业务模式
                'access'=>'KA07',
                'url'=>'KABusine/index',
            ),
            'KA Sales'=>array(//销售模式
                'access'=>'KA08',
                'url'=>'KASales/index',
            ),
            'Customer duplicate'=>array(//客户名称查重设置
                'access'=>'KA12',
                'url'=>'KADup/index',
            ),
            'Indicator setting'=>array(//个人年度指标设置
                'access'=>'KA13',
                'url'=>'KAIndicator/index',
            ),
        ),
    ),

    /*
    'RA Manager'=>array( //RKA业务管理
        'access'=>'RA',
        'icon'=>'fa-trophy',
        'items'=>array(
            'RA Bot'=>array(//RKA項目
                'access'=>'RA01',
                'url'=>'RABot/index',
            ),
            'RA Statistic'=>array(//RKA項目統計
                'access'=>'RA02',
                'url'=>'RAStatistic/index',
            ),
        ),
    ),
    */

    'CA Manager'=>array( //地方业务管理
        'access'=>'CA',
        'icon'=>'fa-trophy',
        'items'=>array(
            'CA Bot'=>array(//地方业务項目
                'access'=>'CA01',
                'url'=>'CABot/index',
            ),
            'CA Statistic'=>array(//地方业务項目統計
                'access'=>'CA02',
                'url'=>'CAStatistic/index',
            ),
        ),
    ),

    'Market'=>array( //市场营销
        'access'=>'MT',
        'icon'=>'fa-glass',
        'items'=>array(
            'Market Company'=>array(//客户资料库
                'access'=>'MT01',
                'url'=>'marketCompany/index',
            ),
            'Market Area'=>array(//地區跟進客戶資料
                'access'=>'MT02',
                'url'=>'marketArea/index',
            ),
            'Market Sales'=>array(//銷售跟進客戶資料
                'access'=>'MT03',
                'url'=>'marketSales/index',
            ),
            'Market Reject'=>array(//無意向客戶
                'access'=>'MT04',
                'url'=>'marketReject/index',
            ),
            'Market Success'=>array(//已完成客戶
                'access'=>'MT05',
                'url'=>'marketSuccess/index',
            ),
            'Market State set'=>array(//跟進狀態設置
                'access'=>'MT06',
                'url'=>'marketState/index',
            ),
        ),
    ),
	'System Setting'=>array(
		'access'=>'HC',
		'icon'=>'fa-gear',
		'items'=>array(
			'Visit Type'=>array(
				'access'=>'HC01',
				'url'=>'/visittype/index',
				'tag'=>'@',
			),
			'Visit Objective'=>array(
				'access'=>'HC02',
				'url'=>'/visitobj/index',
				'tag'=>'@',
			),
			'Customer Type'=>array(
				'access'=>'HC03',
				'url'=>'/custtype/index',
				'tag'=>'@',
			),
			'Customer District'=>array(
				'access'=>'HC04',
				'url'=>'/district/index',
				'tag'=>'@',
			),
            'Sales Rank'=>array(
                'access'=>'HC05',
                'url'=>'/level/index',
                'tag'=>'@',
            ),
            'Sales segment coefficient'=>array(
                'access'=>'HC06',
                'url'=>'/coefficient/index',
                'tag'=>'@',
            ),
            'Sales points other settings'=>array(
                'access'=>'HC07',
                'url'=>'/points/index',
                'tag'=>'@',
            ),
            'Redeem prize list'=>array(
                'access'=>'HC08',
                'url'=>'/redeemsetting/index',
                'tag'=>'@',
            ),
            'Attribute category'=>array(
                'access'=>'HC09',
                'url'=>'/attrType/index',
                'tag'=>'@',
            ),
            'Club setting'=>array(
                'access'=>'HC10',
                'url'=>'/clubSetting/index',
                'tag'=>'@',
            ),
            'sales min setting'=>array(
                'access'=>'HC11',
                'url'=>'/salesMin/edit',
                'tag'=>'@',
            ),
            'main lbs setting'=>array(//主体公司
                'access'=>'HC12',
                'url'=>'/mainLbs/index',
                'tag'=>'@',
            ),
            'sales group setting'=>array(//人员组织架构
                'access'=>'HC13',
                'url'=>'/salesGroup/edit',
                'tag'=>'@',
            ),
            'seal setting'=>array(//印章设置
                'access'=>'HC14',
                'url'=>'/seal/index',
                'tag'=>'@',
            ),
            'pay type setting'=>array(//付款方式设置
                'access'=>'HC15',
                'url'=>'/payType/index',
                'tag'=>'@',
            ),
            'service type setting'=>array(//服务项目设置
                'access'=>'HC16',
                'url'=>'/serviceType/index',
                'tag'=>'@',
            ),
            'cont type setting'=>array(//合同类型设置
                'access'=>'HC17',
                'url'=>'/contType/index',
                'tag'=>'@',
            ),
            'S/T remark setting'=>array(//暂停/终止原因配置
                'access'=>'HC18',
                'url'=>'/contSTSet/index',
                'tag'=>'@',
            ),
            'yewudalei setting'=>array(//业务大类配置
                'access'=>'HC19',
                'url'=>'/yewudaleiSet/index',
                'tag'=>'@',
            ),
            'menu setting'=>array(//通用配置
                'access'=>'HC21',
                'url'=>'/setMenu/index',
                'tag'=>'@',
            ),
            /*
            'cont area setting'=>array(//区域关联配置
                'access'=>'HC20',
                'url'=>'/contAreaSet/index',
                'tag'=>'@',
            ),
            */
		),
	),
    'Import'=>array(
        'access'=>'XF',
        'icon'=>'fa-bolt',
        'items'=>array(
            'Import'=>array(
                'access'=>'XF03',
                'url'=>'/import/clue',
            ),
            'U Import'=>array(
                'access'=>'XF02',
                'url'=>'/import/index',
            ),
            'Import Manager'=>array(
                'access'=>'XF01',
                'url'=>'/iqueue/index',
            ),
        ),
    ),
    'CRM Curl Notes'=>array(//CRM同步记录
        'access'=>'ZC',
        'icon'=>'fa-exchange',
        'items'=>array(
            'LBS To U'=>array(//LBS发送给派单系统
                'access'=>'ZC01',
                'url'=>'/curlNotes/index',
            ),
            'MH To LBS'=>array(//门户发送给LBS
                'access'=>'ZC02',
                'url'=>'/curlReceive/index',
            ),
        ),
    ),

);
