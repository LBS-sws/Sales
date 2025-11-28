<?php
//2024年9月28日09:28:46

class CurlNotesApi{
    protected $_key="";
    public $operation_type;
    public $data=array();
    public $outData=array();
    public $printBool=false;

    public $expr_date;
    public $status_type;
    public $min_url;
    public $info_type;
    public $info_url;
    public $data_content;
    public $out_content;
    public $message;
    public $uid;

    public $weChatHint=true;
    public $timerOut=0;//最大时间，0则不限制
    public static $maxCount=10;//最大数量

    //数据发送给客户接口(新增)
    public function sendDataSetByAddClient(){
        $this->status_type="P";
        $this->message="待处理";
        $this->info_type="client";
        $this->min_url="/crmapi/project.Project/batchAddProjects";
    }

    //数据发送给客户接口(修改)
    public function sendDataSetByUpdateClient(){
        $this->status_type="P";
        $this->message="待处理";
        $this->info_type="client";
        $this->min_url="/crmapi/project.project/batchEditProjects";
    }

    //数据发送给客户联系人(新增)
    public function sendDataSetByAddClientPerson(){
        $this->status_type="P";
        $this->message="待处理";
        $this->info_type="clientPerson";
        $this->min_url="/crmapi/project.Project/batchAddProjectContacts";
    }

    //数据发送给客户联系人(修改)
    public function sendDataSetByUpdateClientPerson(){
        $this->status_type="P";
        $this->message="待处理";
        $this->info_type="clientPerson";
        $this->min_url="/crmapi/project.Project/batchEditProjectContacts";
    }

    //数据发送给客户管辖区域(新增)
    public function sendDataSetByAddClientArea(){
        $this->status_type="P";
        $this->message="待处理";
        $this->info_type="clientArea";
        $this->min_url="/crmapi/project.Project/batchAddProjectRegions";
    }

    //数据发送给客户管辖区域(修改)
    public function sendDataSetByUpdateClientArea(){
        $this->status_type="P";
        $this->message="待处理";
        $this->info_type="clientArea";
        $this->min_url="/crmapi/project.Project/batchEditProjectRegions";
    }

    //数据发送给客户负责人(新增)
    public function sendDataSetByAddClientStaff(){
        $this->status_type="P";
        $this->message="待处理";
        $this->info_type="clientStaff";
        $this->min_url="/crmapi/project.Project/batchAddProjectStaffs";
    }

    //数据发送给客户负责人(修改)
    public function sendDataSetByUpdateClientStaff(){
        $this->status_type="P";
        $this->info_type="clientStaff";
        $this->min_url="/crmapi/project.Project/batchEditProjectStaffs";
    }

    //数据发送给门店(新增)
    public function sendDataSetByAddStore(){
        $this->status_type="P";
        $this->message="待处理";
        $this->info_type="store";
        $this->min_url="/crmapi/customer.Customer/batchAddCustomers";
    }

    //数据发送给门店(修改)
    public function sendDataSetByUpdateStore(){
        $this->status_type="P";
        $this->message="待处理";
        $this->info_type="store";
        $this->min_url="/crmapi/customer.Customer/batchEditCustomers";
    }

    //数据发送给门店联系人(新增)
    public function sendDataSetByAddStorePerson(){
        $this->status_type="P";
        $this->message="待处理";
        $this->info_type="storePerson";
        $this->min_url="/crmapi/customer.CustomerContact/batchAddCustomerContaccts";
    }

    //数据发送给门店联系人(修改)
    public function sendDataSetByUpdateStorePerson(){
        $this->status_type="P";
        $this->message="待处理";
        $this->info_type="storePerson";
        $this->min_url="/crmapi/customer.CustomerContact/batchEditCustomerContaccts";
    }

    //数据发送给虚拟合约(新增)
    public function sendDataSetByAddContract(){
        $this->timerOut=0;
        $this->status_type="P";
        $this->message="待处理";
        $this->info_type="contVir";
        $this->min_url="/crmapi/customer.ServiceContract/batchAddContract";
    }

    //数据发送给虚拟合约(修改)
    public function sendDataSetByUpdateContract(){
        $this->timerOut=0;
        $this->status_type="P";
        $this->message="待处理";
        $this->info_type="contVir";
        $this->min_url="/crmapi/customer.ServiceContract/batchEditContract";
    }

    //数据发送给合约附件(新增)
    public function sendDataSetByAddContractFile(){
        $this->timerOut=0;
        $this->status_type="P";
        $this->message="待处理";
        $this->info_type="contFile";
        $this->min_url="/crmapi/customer.ServiceContract/batchAddContractFile";
    }

    //获取合约的剩余次数
    public function sendSurplusDataSetByUID(){
        $this->status_type="P";
        $this->min_url="/crmapi/customer.ServiceContract/batchGetContractSurplus";
    }
}
