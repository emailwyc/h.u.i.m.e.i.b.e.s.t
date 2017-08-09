<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        checkLogin1();
        checkUserPower();
    }

    //客户模糊搜索
    public function search()
    {
        $page = getCurPage();
        $perpage = 300;
        $offset = getPage($page, $perpage);
        $safePostData=safeParams($_POST);
        $where = getWhereParams();
        //按照开始结束时间搜索
        if (!empty($where)) {
            $searchK = array('name');
            $searchV = $safePostData['name'];
            $where = $this->getDateWhere($where);
            unset($where['name']);
            $result = $this->Common_model->searchKeysLikes('client', $where, array(), $searchK, $searchV, $offset, $perpage, array("develop_date" => -1));
        } else {
            $result = $this->Common_model->getListInfo('client', array(), $offset, $perpage, array("develop_date" => -1), array(), true);
        }
        display($result);
    }

    //客户删除
    public function del()
    {
        $id = getMdbId(addslashes(trim($_GET['id'])));
        $delRe = $this->Common_model->deleteOneRecord('client', array("_id" => $id));
        if ($delRe) {
            display(array());
        } else {
            display(array(), 1, '操作失败');
        }
    }

    //客户添加
    public function add()
    {
        emptyCheck(array('manager','name','job','cellphone','email','address'));
        $safePostData=safeParams($_POST);
        $insertData = array(
            "creator_id" => $safePostData['creator_id'],
            "company_type" => $safePostData['company_type'],
            "judgement"=>$safePostData['judgement'],
            "manager" => $safePostData['manager'],
            "status" => $safePostData['status'],
            "develop_date" => $safePostData['develop_date'],
            "product" => $safePostData['product'],
            "type" => $safePostData['type'],
            "company" => $safePostData['company'],
            "name" => $safePostData['name'],
            "job" => $safePostData['job'],
            "count" => $safePostData['count'],
            "price" => $safePostData['price'],
            "total" => "",
            "estimate_count" => $safePostData['estimate_count'],
            "client_source" => $safePostData['client_source'],
            "track" => $safePostData['track'],
            "schedule_contract_date" => $safePostData['schedule_contract_date'],
            "cellphone" => $safePostData['cellphone'],
            "email" => $safePostData['email'],
            "address" => $safePostData['address'],
            "section_id" => $safePostData['section_id'],
        );
        $insertId = $this->Common_model->insertInfo('client', $insertData);
        if ($insertId) {
            display(array('insertId' => $insertId));
        } else {
            display(array(), 3, "插入数据失败");
        }
    }

    //客户修改
    public function edit()
    {
        emptyCheck(array('manager','name','job','cellphone','email','address'));
        $clientInfo = $this->Common_model->getInfo('client', array('_id' => getMdbId1(addslashes(trim($_POST['_id'])))));
        if (empty($clientInfo)) {
            display(array(), 3, "未找到该客户，请检查!");
        }
        $safePostData=safeParams($_POST);
        $updateData = array(
            "judgement"=>$safePostData['judgement'],
            "manager" => $safePostData['manager'],
            "company_type" => $safePostData['company_type'],
            "status" => $safePostData['status'],
            "develop_date" => $safePostData['develop_date'],
            "type" => $safePostData['type'],
            "product" => $safePostData['product'],
            "company" => $safePostData['company'],
            "name" => $safePostData['name'],
            "job" => $safePostData['job'],
            "count" => $safePostData['count'],
            "price" => $safePostData['price'],
            "total" => "",
            "estimate_count" => $safePostData['estimate_count'],
            "client_source" => $safePostData['client_source'],
            "track" => $safePostData['track'],
            "schedule_contract_date" => $safePostData['schedule_contract_date'],
            "cellphone" => $safePostData['cellphone'],
            "email" => $safePostData['email'],
            "address" => $safePostData['address'],
            "section_id" => $safePostData['section_id'],
        );
        $this->Common_model->updateRecord('client', array('_id' => $clientInfo['_id']), $updateData);
        display(array());
    }
    //客户添加
    public function addPatientClient()
    {
        $safePostData=safeParams($_POST);
        $insertData = array(
            "creator_id" => $safePostData['creator_id'],
            "patient" => $safePostData['patient'],
            "illness" => $safePostData['illness'],
            "manager" => $safePostData['manager'],
            "status" => $safePostData['status'],
            "develop_date" => $safePostData['develop_date'],
            "product" => $safePostData['product'],
            "type" => $safePostData['type'],
            "company" => $safePostData['company'],
            "name" => $safePostData['name'],
            "job" => $safePostData['job'],
            "count" => $safePostData['count'],
            "price" => $safePostData['price'],
            "total" => "",
            "estimate_count" => $safePostData['estimate_count'],
            "client_source" => $safePostData['client_source'],
            "track" => $safePostData['track'],
            "schedule_contract_date" => $safePostData['schedule_contract_date'],
            "cellphone" => $safePostData['cellphone'],
            "email" => $safePostData['email'],
            "address" => $safePostData['address'],
            "section_id" => $safePostData['section_id'],
        );
        $insertId = $this->Common_model->insertInfo('client', $insertData);
        if ($insertId) {
            display(array('insertId' => $insertId));
        } else {
            display(array(), 3, "插入数据失败");
        }
    }

    //客户修改
    public function editPatientClient()
    {
        $clientInfo = $this->Common_model->getInfo('client', array('_id' => getMdbId1(addslashes(trim($_POST['_id'])))));
        if (empty($clientInfo)) {
            display(array(), 3, "未找到该客户，请检查!");
        }
        $safePostData=safeParams($_POST);
        $updateData = array(
            "manager" => $safePostData['manager'],
            "patient" => $safePostData['patient'],
            "illness" => $safePostData['illness'],
            "status" => $safePostData['status'],
            "develop_date" => $safePostData['develop_date'],
            "type" => $safePostData['type'],
            "product" => $safePostData['product'],
            "company" => $safePostData['company'],
            "name" => $safePostData['name'],
            "job" => $safePostData['job'],
            "count" => $safePostData['count'],
            "price" => $safePostData['price'],
            "total" => "",
            "estimate_count" => $safePostData['estimate_count'],
            "client_source" => $safePostData['client_source'],
            "track" => $safePostData['track'],
            "schedule_contract_date" => $safePostData['schedule_contract_date'],
            "cellphone" => $safePostData['cellphone'],
            "email" => $safePostData['email'],
            "address" => $safePostData['address'],
            "section_id" => $safePostData['section_id'],
        );
        $this->Common_model->updateRecord('client', array('_id' => $clientInfo['_id']), $updateData);
        display(array());
    }

    //获取单个客户信息
    public function getClient()
    {
        emptyCheck(array('_id'));
        $where = array('_id' => getMdbId1($_REQUEST['_id']));
        $result = $this->Common_model->getInfo('client', $where);
        display($result);
    }

    //添加客户跟进记录
    public function addActivity()
    {
        //section_id  1:交流中心 2:转诊中心 3:业务事业部
        emptyCheck(array('client'));
        $safeParams = safeParams($_POST);
        $clientInfo = $this->Common_model->getInfo('client',array('_id'=>getMdbId($safeParams['client'])),array('section_id'));
        $activityData = $safeParams;
        $activityData['client']=(string)$safeParams['client'];
        $activityData['section_id']=(int)$clientInfo['section_id'];
        $activityData['date']=empty($safeParams['date'])?time():strtotime($safeParams['date']);
        $activityData['content']=(string)$safeParams['content'];
        $activityData['follow_up_date']=empty($safeParams['follow_up_date'])?time():strtotime($safeParams['follow_up_date']);
        $activityId = $this->Common_model->insertInfo('client_activity', $activityData);
        display(array('insertId' => $activityId));
    }

    public function editClientField()
    {
        $clientInfo = $this->Common_model->getInfo('client', array('_id' => getMdbId1(addslashes(trim($_POST['_id'])))));
        if (empty($clientInfo)) {
            display(array(), 3, "未找到该客户，请检查!");
        }
        $updateData=safeParams($_POST);
        unset($updateData["_id"]);
        $this->Common_model->updateRecord('client', array('_id' => $clientInfo['_id']), $updateData);
        display(array());
    }

    //获取跟进记录
    public function getActivityList()
    {
        $page = getCurPage(); $perpage = 20;
        $offset = getPage($page,$perpage);
        $where = getWhereParams();
        if(isset($where['section_id'])){
            $where['section_id'] = (int)$where['section_id'];
        }
        $where = $this->getDateWhere($where);
        if($where['content']===''){
            unset($where['content']);
            $allcount = $this->Common_model->getInfoCount('client_activity',$where);
            $allpage = ceil($allcount/$perpage);
            $result = $this->Common_model->getListInfo('client_activity',$where,$offset,$perpage,array('date'=>-1),array(),false);
        }else{
            $allcount = $this->Common_model->getInfoCount('client_activity',$where,array('content'), $where['content']);
            $allpage = ceil($allcount/$perpage);
            $likeWhere = $where;
            unset($likeWhere['content']);
            $result = $this->Common_model->searchKeysLikes('client_activity', $likeWhere, array(), array('content'), $where['content'], $offset, $perpage, array("date" => -1));
        }
        $client = getFieldArr($result,'client',1);
        $clientInfo = $this->Common_model->getInfoAll('client',array('_id'=>array('$in'=>$client)),'',array('manager','status','company'));
        $clientInfo = ArrKeyFromId($clientInfo);
        //获取项目数据拼接返回
        foreach ($result as $k=>$val){
            if(isset($clientInfo[$val['client']])){
                $result[$k] = array_merge($clientInfo[$val['client']],$val);
            }
            $result[$k]['date'] = date('Y-m-d',$val['date']);
            $result[$k]['follow_up_date'] = !empty($val['follow_up_date'])?date('Y-m-d',$val['follow_up_date']):'';
        }
        display(array('page'=>$allpage,'data'=>$result));
    }

    //删除跟进记录
    public function deleteActivity()
    {
        $id = getMdbId(addslashes(trim($_GET['id'])));
        $delRe = $this->Common_model->deleteOneRecord('client_activity', array("_id" => $id));
        if ($delRe) {
            display(array());
        } else {
            display(array(), 1, '操作失败');
        }
    }

    //时间条件处理
    public function getDateWhere($where)
    {
        //判断开始时间小于结束时间
        //两个时间同时存在，只有一个存在的情况
        if(!empty($where['develop_begin_date'])){$where['develop_date'] = array('$gte'=>strtotime($where['develop_begin_date']),'$lte'=>time());}
        if(!empty($where['develop_end_date']) && !empty($where['develop_end_date'])){
            $where['develop_begin_date'] = strtotime($where['develop_begin_date']);
            $where['develop_end_date'] = strtotime($where['develop_end_date']);
            if($where['develop_begin_date']>$where['develop_end_date']){display(false,3,'开始时间大于结束时间！');}
            $where['develop_date'] = array('$gte'=>$where['develop_begin_date'],'$lte'=>$where['develop_end_date']);}
        unset($where['develop_begin_date'],$where['develop_end_date']);
        return $where;
    }
}
