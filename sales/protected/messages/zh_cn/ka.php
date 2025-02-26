<?php
return array(
    'Setting Type List'=>'配置列表',
    'project name'=>'名称',
    'z index'=>'层级',
    'z display'=>'是否显示',
    'yes'=>'是',
    'no'=>'否',
    'city code'=>'日报表城市编号',
    'link name'=>'沟通阶段',
    'link project name'=>'沟通阶段名称',
    'link num'=>'沟通阶段数值',
    'class type name'=>'工厂类别',
    'type'=>'类型',
    'reason'=>'理由',
    'stop'=>'暂停',
    'reject'=>'拒绝',
    'apply date'=>'录入日期',
    'customer no'=>'客户编号',
    'customer name'=>'客户公司',
    'contact user'=>'合同联系人/职位',
    'source name'=>'客户类型',
    'class name'=>'客户类别',
    'KAM'=>'KA销售',

    'head city'=>"客户总部",
    'talk city'=>"洽谈地区",
    'area city'=>"客户地区",
    'contact phone'=>"联系人手机",
    'contact email'=>"联系人邮箱",
    'contact dept'=>"联系人职位",
    'source name(A)'=>"客户来源",
    'level name'=>"客户分级",
    'busine name'=>"业务模式",
    'month amt'=>"可签约金额(月)",
    'quarter amt'=>"可签约金额(季)",
    'year amt'=>"可签约金额(年)",
    'sign date'=>"合约开始日期",
    'sign end date'=>"合约结束日期",
    'sign month'=>"合同周期(年)",
    'sign amt'=>"签约合同总金额",
    'sum amt'=>"合同累计金额",
    'support user'=>"区域支持者",
    'sign odds'=>"签约概率",
    'remark'=>"项目概述（<span class='text-red'>请备注门店总数/分布城市/集团子品牌/竞争对手份额及报价</span>）",
    'info date'=>"跟进时间",
    'info text'=>"跟进内容",
    'Flow Info'=>"操作记录",
    'Operator User'=>"操作人",
    'Operator Time'=>"操作时间",
    'Operator Text'=>"操作内容",

    'search type'=>'查询类型',
    'search year'=>'年份',
    'search quarter'=>'季度',
    'search month'=>'月份',
    'search day'=>'日期',
    'start date'=>'开始时间',
    'end date'=>'结束时间',
    'Total data'=>'全部数据',
    'Visiting stage'=>'拜访阶段',
    'Quotation stage'=>'报价阶段',
    'quantity'=>'数量',
    'Contract amt'=>'合同金额',
    'YTD'=>'YTD',
    'Month MTD'=>'月MTD',
    'Sales performance'=>'每月KA销售业绩',
    'Enquiry'=>'查询',
    ' year'=>'年',
    ' month'=>'月',
    'all total'=>'所有统计',
    'Download'=>'下载',

    'amount for next 90 days'=>'未来90天加权报价金额',
    'amount for this month'=>'本月可实现销售金额',
    'bot_remark:'=>'计算逻辑：',
    'bot_remark_1'=>'1、拜访阶段',
    'bot_remark_1_1'=>'数量 = 预估可成交日期为提交年份所有记录（提交年份为2024年，则统计所有预估可成交日期为2024年的记录）',
    'bot_remark_1_2'=>'合同金额 = 预估可成交金额',
    'bot_remark_2'=>'2、报价阶段',
    'bot_remark_2_1'=>'数量 = 预估可成交日期为提交年份且沟通阶段大于等于30%的所有记录',
    'bot_remark_2_2'=>'合同金额 = 预估可成交金额',
    'bot_remark_3_1'=>'3、转化率（报价/拜访）= 报价阶段合同金额 / 拜访阶段合同金额  * 100%',
    'bot_remark_3_2'=>'90天转化率（本月实际成交/90天加权） = 本月可实现销售金额 / 未来90天加权报价金额 * 100%',

    'bot_remark_4'=>'4、未来90天加权报价金额',
    'bot_remark_4_1'=>'数量 = 根据预估可成交日期栏位，且往前推3个月，当沟通阶段为100%，记录不统计',
    'bot_remark_4_2'=>'<span style="color:red;">例：预估可成交日期选了3月某一天，当统计表的月份分别为1月、2月和3月时，</span>未来90天加权都体现这个客户（沟通阶段不是100%）',
    'bot_remark_4_3'=>'合同金额计算逻辑：',
    'bot_remark_4_4'=>'当签约概率选择<50%或50%，数量和金额不统计',
    'bot_remark_4_5'=>'当签约概率选择51%-80%，统计金额= 预估可成交金额* 50%，',
    'bot_remark_4_6'=>'当签约概率选择>80%或100%，统计金额= 预估可成交金额* 100%',
    'bot_remark_5'=>'5、本月可实现销售金额',
    'bot_remark_5_1'=>'数量 = 根据预估可成交日期，假如统计表提交时间为2024年1月，预估可成交日期为24年1月，且沟通阶段不是100%，则统计数量',
    'bot_remark_5_2'=>'合同金额 = 当签约概率选择>80%或100%，则统计预估可成交金额，金额*100% ，其他比例不统计',

    'available amt'=>'预估可成交金额',
    'contact address'=>'联系人地址',
    'con user'=>'合同联系人/职位',
    'con phone'=>'联系人电话',
    'con email'=>'联系人邮箱',
    'work user'=>'业务联系人/职位',
    'work phone'=>'联系人电话',
    'work email'=>'联系人邮箱',
    'ava date'=>'月份',
    'ava amt'=>'预估金额',
    'ava rate'=>'签约概率',
    'ava fact amt'=>'实际金额',

    'available date'=>'预估可成交日期',
    'Conversion rate'=>'转化率',
    '90 Day rate'=>'90天转化率',
    //'YTD rate'=>'YTD转化率',
    'YTD amt rate'=>'金额转化（成交金额/拜访阶段金额）',
    'YTD num rate'=>'数量转化（成交数量/拜访数量）',
    'ava num'=>'门店数量',
    'ava city'=>'城市',
    'ava note'=>'备注(<span style="color:red;">请备注门店总数和金额详情等内容</span>)',
    '(Actual transactions this month/90 day weighted)'=>'（本月实际成交/90天加权）',

    'YTD Rpt'=>'YTD报表',
    'KA Type'=>'KA类别',
    'dup name'=>'需要替换的字符串',
    'dup value'=>'替换后的字符串',
    'Shift'=>'转移',
    'Shift Done'=>'转移成功',

    'employee name'=>'员工',
    'effect date'=>'生效日期',
    'indicator money'=>'个人年度指标',
    'indicator sales'=>'个人年度指标',
    'indicator sales rate'=>'个人年度指标',
    'indicator group'=>'团队年度指标',
    'indicator group rate'=>'团队年度指标',
    'city'=>'城市',
    'ava sum'=>'合同累积门店',

    'renewal date'=>'续约时间',
    'renewal num'=>'续约门店数量',
    'renewal city'=>'续约城市',
    'renewal note'=>'续约备注',
    'renewal amt'=>'续约金额',
    'renewal total amt'=>'续约累积金额',
    'renewal total num'=>'续约累积门店',
    'YTD for renewal'=>'YTD续约数据',
    ' renewal'=>'续约',
    'amt'=>'金额',
    'group'=>'分组',
    'KAM sale'=>'KAM',
    'entry date'=>'入职日期',
    'YTD Potential Data'=>'YTD潜客转化数据',
    'YTD QV rate'=>'YTD拜访-报价转化',
    'YTD SQ rate'=>'YTD报价-签约转化',
    'YTD SV rate'=>'YTD拜访-签约转化',
    'Quotation/Visit'=>'报价/拜访',
    'Sign/Visit'=>'签约/拜访',
    'Sign/Quotation'=>'签约/报价',
    ' month predict'=>'月预估',
    'YTD Potential predict'=>'QTD潜客预估',
    'predict for next 90 days'=>'未来90天预估',
    'success for next 90 days'=>'未来90天达成',
    '(Actual Sign this month/90 day weighted)'=>'（本月签约/90天加权）',
    'MTD personal data'=>'MTD个人达成数据',
    'YTD personal data'=>'YTD个人达成数据',
    ' month success'=>'月达成',
    ' success'=>'达成',
    'MTD Sign'=>'MTD签约',
    'MTD Indicator'=>'MTD指标',
    'MTD rate(sign/indicator)'=>'MTD达成率（签约/指标）',
    'Sign total'=>'签约数量',
    'YTD Sign total'=>'YTD签约数量',
    'YTD Sign amt'=>'YTD签约金额',
    'YTD Indicator'=>'YTD指标',
    'YTD rate(sign/indicator)'=>'YTD达成率（签约/指标）',
    'YTD group data'=>'YTD团队数据',
    'YTD rate'=>'YTD达成率',
    'YTD Group Amt'=>'YTD团队金额',
    'QTD Next month predict'=>'次月预估',
    'YTD All Indicator'=>'YTD因设指标',
    'YTD All rate'=>'YTD因设达成率',
    'YTD Now Indicator'=>'YTD现有指标',
    'YTD Now rate'=>'YTD现有达成率',
);
?>