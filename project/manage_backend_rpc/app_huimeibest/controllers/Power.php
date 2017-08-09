<?php
/**
 * Created by PhpStorm.
 * User: sa
 * Date: 6/13/16
 * Time: 11:30 AM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Login.php
 */
class Power extends CI_Controller
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

    //添加权限
    public function add()
    {
        //post提交 权限名字
        emptyCheck(array('name','remark'));
        $params = safeParams($_POST);
        if(empty($params['parent'])){
            $params['parent'] = "0";
        }
        $params['status'] = 1;
        $checkPower = $this->Common_model->getInfo('manage_power',array('name'=>$params['name'],'remark'=>$params['remark']));
        if($checkPower){display(array(),3,'已存在插入失败');}
        $powerId = $this->Common_model->insertInfo('manage_power',$params);
        display(array());
    }

    //权限列表
    public function all()
    {
        $page = getCurPage(); $perpage = 50;
        $offset = getPage($page,$perpage);
        $allcount = $this->Common_model->getInfoCount('manage_power',array());
        $allpage = ceil($allcount/$perpage);
        $powerFirst = $this->Common_model->getListInfo('manage_power',array('parent'=> '0','status'=>1),$offset,$perpage);
        foreach ($powerFirst as $k=>$v){
            $powerFirst[$k]['child'] = $this->Common_model->getInfoAll('manage_power',array('parent'=>(string)$v['_id'],'status'=>1));
        }
        if(empty($powerFirst)){display(array(),403,'暂无数据');}
        display(array('page'=>$allpage,'data'=>$powerFirst));
    }

    //删除权限
    public function del()
    {
        emptyCheck(array('id'));
        $params = safeParams($_POST);
        $secondPower = $result = $this->Common_model->getInfoAll('manage_power',array('parent'=>(string)$params['id'],'status'=>1));
        if(!empty($secondPower)){   display(false,3,'存在子权限，不能删除！');}
        $delRe = $this->Common_model->deleteOneRecord('manage_power',array("_id"=>getMdbId($params['id'])));
        if($delRe){
            display(array());
        }else{
            display(array(),3,'操作失败');
        }
    }

    //修改权限数据
    public function update()
    {
        emptyCheck(array('id'));
        $params = safeParams($_POST);
        $id = getMdbId($params['id']);
        unset($params['id']);
        $updateRe = $this->Common_model->updateRecord('manage_power',array('_id'=>$id),$params);
        if($updateRe){
            display(array());
        }
        else{
            display(array(),1,'修改失败');
        }
    }

    //获取单个权限
    public function one()
    {
        emptyCheck(array('id'));
        $params = safeParams($_POST);
        $result = $this->Common_model->getInfo('manage_power',array('_id'=>getMdbId($params['id'])));
        display($result);
    }

    //获取子级权限
    public function getSecondPower()
    {
        emptyCheck(array('id'));
        $params = safeParams($_POST);
        $result = $this->Common_model->getInfoAll('manage_power',array('parent'=>(string)$params['id'],'status'=>1));
        display($result);
    }

    //获取所有父类权限
    public function getParentPower()
    {
        $result = $this->Common_model->getInfoAll('manage_power',array('parent'=>"0",'status'=>1));
        display($result);
    }
}