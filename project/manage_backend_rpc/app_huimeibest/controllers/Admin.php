<?php
/**
 * Created by PhpStorm.
 * User: sa
 * Date: 6/12/16
 * Time: 1:51 PM
 */

defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * User.php
 */
class Admin extends CI_Controller {

    /**
     * 构造方法
     *
     * @param  null
     * @access public
     * @return void
     */
    public function __construct(){
        parent::__construct();checkLogin1();checkUserPower();
    }

    //添加后台管理员
    public function add()
    {
        emptyCheck(array('mobile','pwd','name','status'));//密码限制6位
        $params = safeParams($_POST);
        if(strlen($params['pwd'])<6){display(false,3,'密码不能低于6位！');}
        $checkUser = $this->Common_model->getInfo('manage_admin',array('mobile'=>$params['mobile']));
        if($checkUser){ display(false,3,'手机号重复');}
        $data['name'] = $params['name'];
        $data['mobile'] = $params['mobile'];
        $data['sign'] = getRandomPass(46);
        $data['pwd'] = password($params['pwd'],$data['sign']);
        $data['actived_at'] = time();
        $data['role'] = (array)$params['roles'];
        $data['position'] = $params['position'];
        $data['branch'] = $params['branch'];
        $data['status'] = (int)$params['status'];
        $roleIds = $params['roleId'];//用户表里存放role   ids
        for ($i=0;$i<count($roleIds);$i++){
            $data['role'][] = addslashes(trim($roleIds[$i]));
        }
        $doAdd = $this->Common_model->insertInfo('manage_admin',$data);
        if($doAdd){
            display(array());
        }else{
            display(array(),0,'添加失败');
        }
    }

    //后台管理员列表
    public function all()
    {
        $page = getCurPage(); $perpage = 20;
        $offset = getPage($page,$perpage);
        $where = array();
        @$role = $_REQUEST['roleId'];
        $where = empty($role)?array() : array('role'=>$role);
        $allcount = $this->Common_model->getInfoCount('manage_admin',$where);
        $allpage = ceil($allcount/$perpage);
        $files = array('name','mobile','actived_at','role','position','branch','status');
        $result = $this->Common_model->getListInfo('manage_admin',$where,$offset,$perpage,array('_id'=>-1),$files);
        foreach ($result as $k=>$v){
            if(!empty($result[$k]['role'])){
                $rolesId = getMongoIds($result[$k]['role']);
                $files = array('name','status');
                $result[$k]['role'] = $this->Common_model->getInfoAll('manage_role',array('_id'=>array('$in'=>$rolesId)),null,$files);
            }
            unset($result[$k]['pwd']);
        }
        display(array('page'=>$allpage,'data'=>$result));
    }

    //删除管理员
    public function del()
    {
        emptyCheck(array('id'));
        $params = safeParams($_POST);
        $delRe = $this->Common_model->deleteOneRecord('manage_admin',array("_id"=>getMdbId($params['id'])));
        if($delRe){
            display(array());
        }else{
            display(array(),1,'操作失败');
        }
    }

    //信息修改
    public function update()
    {
        emptyCheck(array('id'));
        $params = safeParams($_POST);
        $id = getMdbId($params['id']);
        $adminUser = $this->Common_model->getInfo('manage_admin',array('_id'=>$id));
        if(empty($adminUser)){display(false,3,'用户不存在！');}
        unset($params['id']);
        $updateDate = $params;
        if(empty($params['pwd'])){
            unset($updateDate['pwd']);
        }else{
            $updateDate['sign'] = getRandomPass(46);
            $updateDate['pwd'] = password($params['pwd'],$updateDate['sign']);
        }
        $updateDate['role'] = (array)$params['roles'];
        $updateDate['status'] = (int)$updateDate['status'];
        $updateRe = $this->Common_model->updateRecord('manage_admin',array('_id'=>$id),$updateDate);
        if($updateRe){display(array());}
        else{display(array(),1,'修改失败');}
    }

    //单个管理员
    public function one()
    {
        //log_message('ERROR',json_encode($_REQUEST) );exit();
        emptyCheck(array('id'));
        $params = safeParams($_POST);
        $files = array('_id','actived_at','name','mobile','role','position','branch','status');
        $result = $this->Common_model->getInfo('manage_admin',array('_id'=>getMdbId($params['id'])),$files);
        if(!empty($result['role'])){
            $rolesId = getMongoIds($result['role']);
            $result['role'] = $this->Common_model->getInfoAll('manage_role',array('_id'=>array('$in'=>$rolesId)));
        }
        display($result);
    }

    //验证管理员手机号是否唯一
    public function checkOnlyMobile()
    {
        emptyCheck(array('mobile'));
        $params = safeParams($_POST);
        $re = $this->Common_model->getInfo('manage_admin',array('mobile'=>$params['mobile']));
        if($re){ display(false,3,'mobile已经存在');}
    }
}
