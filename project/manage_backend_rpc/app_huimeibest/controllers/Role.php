<?php
/**
 * Created by PhpStorm.
 * User: sa
 * Date: 6/12/16
 * Time: 5:30 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Login.php
 */
class Role extends CI_Controller
{

    /**
     * 构造方法
     *
     * @param  null
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();checkLogin1();checkUserPower();
    }

    //添加角色
    public function add()
    {
        //post提交 角色名字  权限列表 order/all   角色名需要唯一？创建索引
        emptyCheck(array('name','powers'));
        $params = safeParams($_POST);
        $insertData = array(
            'name'=>$params['name'],
            'powers'=>$params['powers'],
            'status'=>1);
        $userId= $this->Common_model->insertInfo('manage_role',$insertData);
        if(empty($userId)){display(array(),3,'插入失败');}
        display(array());
    }

    //角色列表
    public function all()
    {
        $page = getCurPage(); $perpage = 20;
        $offset = getPage($page,$perpage);
        $allcount = $this->Common_model->getInfoCount('manage_role',array());
        //查询出对应的权限名字返回
        $allpage = ceil($allcount/$perpage);
        $result = $this->Common_model->getListInfo('manage_role',array(),$offset,$perpage,null,null,true);
        foreach($result as $k=>$v){
            if(!empty($v['powers'])){
                $powers = getMongoIds($v['powers']);
                $result[$k]['powers'] = $this->Common_model->getInfoAll('manage_power',array('_id'=>array('$in'=>$powers)));
            }
        }
        display(array('page'=>$allpage,'data'=>$result));
    }

    //删除角色
    public function del()
    {
        emptyCheck(array('id'));
        $params = safeParams($_REQUEST);
        $delRe = $this->Common_model->deleteOneRecord('manage_role',array("_id"=>getMdbId($params['id'])));
        if($delRe){
            display(array());
        }else{
            display(array(),1,'操作失败');
        }
    }

    //修改角色数据
    public function update()
    {
        emptyCheck(array('id','status'));
        $params = safeParams($_POST);
        $updateDate = array(
            'name'=>$params['name'],
            'powers'=>(array)$params['powers'],
            'status'=>(int)$params['status']);
        $updateRe = $this->Common_model->updateRecord('manage_role',array('_id'=>getMdbId($params['id'])),$updateDate);
        if($updateRe){
            display(array());
        }
        else{
            display(array(),1,'修改失败');
        }
    }

    //单个角色
    public function one()
    {
        emptyCheck(array('id'));
        $params = safeParams($_REQUEST);
        $result = $this->Common_model->getInfo('manage_role',array('_id'=>getMdbId($params['id'])));
            if(!empty($result['powers'])){
                $powers = getMongoIds($result['powers']);
                $result['powers'] = $this->Common_model->getInfoAll('manage_power',array('_id'=>array('$in'=>$powers)));
        }
        if(empty($result)){display(false,3,'查询对象不存在');}
        display($result);
    }

    //赋予角色权限
    public function addPowerToRole()
    {
        emptyCheck(array('id','powers'));
        $params = safeParams($_POST);
        $powers = $params['powers'];
        for ($i=0;$i<count($powers);$i++){
            $doUpdate = $this->Common_model->updateSetRecord('manage_role',array('_id'=>getMdbId($params['id'])),array('$addToSet'=>array('powers'=>$powers[$i])));
        }
        if($doUpdate){
            display(array());
        }else{display(array(),403,'添加失败');}
    }

    //角色列表
    public function listAll()
    {
        $rolePower = array('name');
        $result = $this->Common_model->getInfoAll('manage_role',array('status'=>1),$rolePower);
        display($result);
    }
}
