<?php
/**
 * Created by PhpStorm.
 * User: sa
 * Date: 7/27/16
 * Time: 10:13 AM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class PatSysMsg extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();checkLogin1();checkUserPower();
    }

    public function add()
    {
        emptyCheck(array('content','isPush','pubdate'));
        $params = safeParams($_POST);
        $insertData = $params;
        $insertData['created_at'] = time();
        $insertData['pubdate'] = (int)$params['pubdate'];
        $insertData['isPush'] = (int)$params['isPush'];
		$msgId = $this->Common_model->insertInfo('pat_system_msg',$insertData);
        if(!$msgId){ display(false,'3','数据插入失败');}
        $pushContent = '系统消息：'.mb_substr($params['content'],0,15,'utf-8' ).'……';
        if($insertData['isPush']===1){
            $extras = array('_id'=>(string)$msgId,'content'=>mb_substr($params['content'],0,15,'utf-8' ),'pubdate'=>$params['pubdate'],'type'=>'system_msg');
        $pushMsg = $this->Common_model->jPush($pushContent,$extras);
            if(!$pushMsg){display(false,3,'推送失败!');}
        }
        display();
    }

    //系统消息列表
    public function all()
    {
        $page = getCurPage(); $perpage = 20;
        $offset = getPage($page,$perpage);
        $allcount = $this->Common_model->getInfoCount('pat_system_msg',array());
        $allpage = ceil($allcount/$perpage);
        $result = $this->Common_model->getListInfo('pat_system_msg',array(),$offset,$perpage,array('created_at'=>-1));
        foreach ($result as $k =>$v){
            $result[$k]['pubdate'] = date('Y-m-d H:i:s',$v['pubdate']);
        }
        if(empty($result)){display(array(),403,'暂无数据');}
        display(array('page'=>$allpage,'data'=>$result));
    }

    //删除系统消息
    public function del()
    {
        emptyCheck(array('id'));
        $params = safeParams($_POST);
        $delRe = $this->Common_model->deleteOneRecord('pat_system_msg',array("_id"=>getMdbId($params['id'])));
        if($delRe){
            display(array());
        }else{
            display(array(),3,'操作失败');
        }
    }

    //修改系统消息数据
    public function update()
    {
        emptyCheck(array('id','content'));
        $params = safeParams($_POST);
        $id = getMdbId($params['id']);
        if(isset($params['isPush'])){
            $params['idPush'] = (int)$params['idPush'];
        }
        unset($params['id']);
        $pushContent = '系统消息：'.mb_substr($params['content'],0,15,'utf-8' ).'……';
        if($params['isPush']===1){
            $extras = array('_id'=>(string)$msgId,'content'=>mb_substr($params['content'],0,15,'utf-8' ),'pubdate'=>$params['pubdate'],'type'=>'system_msg');
            $pushMsg = $this->Common_model->jPush($pushContent,$extras);
            if(!$pushMsg){display(false,3,'推送失败!');}
        }
        $updateRe = $this->Common_model->updateRecord('pat_system_msg',array('_id'=>$id),$params);
        if($updateRe){display(array());}
        else{display(array(),1,'修改失败');}
    }

    //获取单个系统消息
    public function one()
    {
        emptyCheck(array('id'));
        $params = safeParams($_POST);
        $result = $this->Common_model->getInfo('pat_system_msg',array('_id'=>getMdbId($params['id'])));
        display($result);
    }
}
