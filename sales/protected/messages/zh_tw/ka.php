<?php
return array(
    'Setting Type List'=>'配置列表',
    'project name'=>'名稱',
    'z index'=>'層級',
    'z display'=>'是否顯示',
    'yes'=>'是',
    'no'=>'否',
    'city code'=>'日報表城市編號',
    'link name'=>'溝通階段',
    'link project name'=>'溝通階段名稱',
    'link num'=>'溝通階段數值',
    'class type name'=>'工廠類別',
    'type'=>'類型',
    'reason'=>'理由',
    'stop'=>'暫停',
    'reject'=>'拒絕',
    'apply date'=>'錄入日期',
    'customer no'=>'客戶編號',
    'customer name'=>'客戶公司',
    'contact user'=>'合同聯系人/職位',
    'source name'=>'客戶類型',
    'class name'=>'客戶類別',
    'KAM'=>'KA銷售',

    'head city'=>"客戶總部",
    'talk city'=>"洽談地區",
    'area city'=>"客戶地區",
    'contact phone'=>"聯系人手機",
    'contact email'=>"聯系人郵箱",
    'contact dept'=>"聯系人職位",
    'source name(A)'=>"客戶來源",
    'level name'=>"客戶分級",
    'busine name'=>"業務模式",
    'month amt'=>"可簽約金額(月)",
    'quarter amt'=>"可簽約金額(季)",
    'year amt'=>"可簽約金額(年)",
    'sign date'=>"合同簽約日期",
    'sign month'=>"合同周期(年)",
    'sign amt'=>"簽約合同總金額",
    'sum amt'=>"合同累計金額",
    'support user'=>"區域支持者",
    'sign odds'=>"簽約概率",
    'remark'=>"項目概述（<span class='text-red'>請備註門店總數/分布城市/集團子品牌/競爭對手份額及報價</span>）",
    'info date'=>"跟進時間",
    'info text'=>"跟進內容",
    'Flow Info'=>"操作記錄",
    'Operator User'=>"操作人",
    'Operator Time'=>"操作時間",
    'Operator Text'=>"操作內容",

    'search type'=>'查詢類型',
    'search year'=>'年份',
    'search quarter'=>'季度',
    'search month'=>'月份',
    'search day'=>'日期',
    'start date'=>'開始時間',
    'end date'=>'結束時間',
    'Total data'=>'全部數據',
    'Visiting stage'=>'拜訪階段',
    'Quotation stage'=>'報價階段',
    'quantity'=>'數量',
    'Contract amt'=>'合同金額',
    'YTD'=>'YTD',
    'Month MTD'=>'月MTD',
    'Sales performance'=>'每月KA銷售業績',
    'Enquiry'=>'查詢',
    ' year'=>'年',
    ' month'=>'月',
    'all total'=>'所有統計',
    'Download'=>'下載',

    'amount for next 90 days'=>'未來90天加權報價金額',
    'amount for this month'=>'本月可實現銷售金額',
    'bot_remark:'=>'計算邏輯：',
    'bot_remark_1'=>'1、拜訪階段',
    'bot_remark_1_1'=>'數量 = 預估可成交日期為提交年份所有記錄（提交年份為2024年，則統計所有預估可成交日期為2024年的記錄）',
    'bot_remark_1_2'=>'合同金額 = 預估可成交金額',
    'bot_remark_2'=>'2、報價階段',
    'bot_remark_2_1'=>'數量 = 預估可成交日期為提交年份且溝通階段大於等於30%的所有記錄',
    'bot_remark_2_2'=>'合同金額 = 預估可成交金額',
    'bot_remark_3_1'=>'3、轉化率（報價/拜訪）= 報價階段合同金額 / 拜訪階段合同金額  * 100%',
    'bot_remark_3_2'=>'90天轉化率（本月實際成交/90天加權） = 本月可實現銷售金額 / 未來90天加權報價金額 * 100%',

    'bot_remark_4'=>'4、未來90天加權報價金額',
    'bot_remark_4_1'=>'數量 = 根據預估可成交日期欄位，且往前推3個月，當溝通階段為100%，記錄不統計',
    'bot_remark_4_2'=>'<span style="color:red;">例：預估可成交日期選了3月某一天，當統計表的月份分別為1月、2月和3月時，</span>未來90天加權都體現這個客戶（溝通階段不是100%）',
    'bot_remark_4_3'=>'合同金額計算邏輯：',
    'bot_remark_4_4'=>'當簽約概率選擇<50%或50%，數量和金額不統計',
    'bot_remark_4_5'=>'當簽約概率選擇51%-80%，統計金額= 預估可成交金額* 50%，',
    'bot_remark_4_6'=>'當簽約概率選擇>80%或100%，統計金額= 預估可成交金額* 100%',
    'bot_remark_5'=>'5、本月可實現銷售金額',
    'bot_remark_5_1'=>'數量 = 根據預估可成交日期，假如統計表提交時間為2024年1月，預估可成交日期為24年1月，且溝通階段不是100%，則統計數量',
    'bot_remark_5_2'=>'合同金額 = 當簽約概率選擇>80%或100%，則統計預估可成交金額，金額*100% ，其他比例不統計',

    'available amt'=>'預估可成交金額',
    'contact address'=>'聯系人地址',
    'con user'=>'合同聯系人/職位',
    'con phone'=>'聯系人電話',
    'con email'=>'聯系人郵箱',
    'work user'=>'業務聯系人/職位',
    'work phone'=>'聯系人電話',
    'work email'=>'聯系人郵箱',
    'ava date'=>'月份',
    'ava amt'=>'預估金額',
    'ava rate'=>'簽約概率',
    'ava fact amt'=>'實際金額',

    'available date'=>'預估可成交日期',
    'Conversion rate'=>'轉化率',
    '90 Day rate'=>'90天轉化率',
    'YTD rate'=>'YTD轉化率',
    'YTD amt rate'=>'金額轉化（成交金額/拜訪階段金額）',
    'YTD num rate'=>'數量轉化（成交數量/拜訪數量）',
    'Quotation/Visit'=>'報價/拜訪',
    'ava num'=>'門店數量',
    'ava city'=>'城市',
    'ava note'=>'備註(<span style="color:red;">請備註減少門店總數和金額詳情等內容</span>)',
    '(Actual transactions this month/90 day weighted)'=>'（本月實際成交/90天加權）',

    'YTD Rpt'=>'YTD报表',
    'KA Type'=>'KA類別',
    'dup name'=>'需要替換的字符串',
    'dup value'=>'替換後的字符串',
    'Shift'=>'轉移',
    'Shift Done'=>'轉移成功',

    'employee name'=>'員工',
    'effect date'=>'生效日期',
    'indicator money'=>'個人年度指標',
    'indicator sales'=>'個人年度指標',
    'indicator sales rate'=>'個人年度指標',
    'indicator group'=>'團隊年度指標',
    'indicator group rate'=>'團隊年度指標',
    'city'=>'城市',
);
?>