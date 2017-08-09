<?php
/**
 * Created by PhpStorm.
 * User: sa
 * Date: 6/13/16
 * Time: 1:27 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Patient extends CI_Controller {

    public function __construct()
    {
        parent::__construct();checkLogin1();checkUserPower();
    }
    
    //医生端患者列表
    public function all()
    {
        $page = getCurPage(); $perpage = 20;
        $offset = getPage($page,$perpage);
        $allcount = $this->Common_model->getInfoCount('patient',array());
        $allpage = ceil($allcount/$perpage);
        $result = $this->Common_model->getListInfo('patient',array(),$offset,$perpage,array('_id'=>-1));
        if(empty($result)){display(array(),403,'暂无数据');}
        display(array('page'=>$allpage,'date'=>$result));
    }

    //患者APP 患者列表（weibo,QQ,weixin）
    public function chatAll()
    {
        $page = getCurPage(); $perpage = 20;
        $offset = getPage($page,$perpage);
        $where = getWhereParams();
        $allcount = $this->Common_model->getInfoCount('pat_app_record',$where);
        $allpage = ceil($allcount/$perpage);
        $result = $this->Common_model->getListInfo('pat_app_record',array(),$offset,$perpage,array('active_time'=>-1),array(),false);
        $user_ids = getFieldArr($result,'user_id',1);
        $userInfo = $this->Common_model->getInfoAll('pat_user',array('_id'=>array('$in'=>$user_ids)));
        foreach ($result as $key => $val){
            foreach ($userInfo as $k => $v){
                if($val['user_id'] == (string)$v['_id']){
                    $result[$key] = array_merge($userInfo[$k],$result[$key]);
                }
                $result[$key]['run_app_time'] = date('Y-m-d H:s:i',$val['active_time']);
                $result[$key]['actived_at'] = date('Y-m-d H:s:i',$v['actived_at']);
            }
        }
        if(empty($result)){display(array(),403,'暂无数据');}
        display(array('page'=>$allpage,'data'=>$result));
    }
    
    public function chatAll1()
    {
        $page = getCurPage(); $perpage = 20;
        $offset = getPage($page,$perpage);
        $where = getWhereParams();
        $allcount = $this->Common_model->getInfoCount('pat_user',$where);
        $allpage = ceil($allcount/$perpage);
        $result = $this->Common_model->getListInfo('pat_user',array(),$offset,$perpage,array('actived_at'=>-1),array(),false);
        for ($i=0 ;$i<count($result);$i++){
            $patId[] = $result[$i]['_id'];
            if(!empty($result[$i]['device_id'])){
                $device_id[] = $result[$i]['device_id'];
            }
        }
        if(!empty($patId)){
            $userSession = $this->Common_model->getInfoAll('pat_user_session',array('_id'=>array('$in'=>$patId)),array('dev_type'));
        }if(!empty($device_id)){
        $runAppTimes = $this->Common_model->getInfoAll('pat_app_record',array('device_id'=>array('$in'=>$device_id)),
            array('device_id','active_time'));
    }
        foreach($result as $k=>$v){
            foreach($userSession as $key=>$val){
                if($val['_id']==$v['_id']){
                    $result[$k]['dev_type'] = $val['dev_type'];
                }
            }
            foreach($runAppTimes as $key=>$val){
                if($val['device_id']==$v['device_id']){
                    $result[$k]['run_app_time'] = date('Y-m-d H:s:i', $val['active_time']);
                }
            }
        }
        if(empty($result)){display(array(),403,'暂无数据');}
        display(array('page'=>$allpage,'data'=>$result));
    }
}
