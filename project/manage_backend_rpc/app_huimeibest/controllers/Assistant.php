<?php
/**
 * Created by PhpStorm.
 * User: sa
 * Date: 6/8/16
 * Time: 11:57 AM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
class Assistant extends CI_Controller {

    /**
     * 构造方法
     *
     * @param  null
     * @access public
     * @return void
     */
    public function __construct(){
        parent::__construct();checkLogin1();checkUserPower();
        //$this->load->model('Assistant_model');
    }
    //医生助手添加
    public function add()
    {
        $date['name'] = addslashes(trim($_POST['name']));
        $date['mobile'] = addslashes(trim($_POST['mobile']));
        if(preg_match('/^1[3|4|5|7|8][0-9]\d{4,8}$/',$date['mobile'])){
            $doAdd = $this->Common_model->insertInfo('doctor_assistant',$date);
            if($doAdd){
                display(array());
            }
        }else{
            display(array(),1,'核对手机换哦');
        }
    }

    //删除
    public function del()
    {
        $id = getMdbId(addslashes(trim($_GET['id'])));
        $delRe = $this->Common_model->deleteOneRecord('doctor_assistant',array("_id"=>$id));
        if($delRe){
            display(array());
        }else{
            display(array(),1,'操作失败');
        }
    }

    //修改
    public function update()
    {
        $id = getMdbId(addslashes(trim($_POST['id'])));
        $updateDate['name'] = addslashes(trim($_POST['name']));
        $updateDate['mobile'] = addslashes(trim($_POST['mobile']));
        if(preg_match('/^1[3|4|5|7|8][0-9]\d{8}$/',$updateDate['mobile'])){
            $updateRe = $this->Common_model->updateRecord('doctor_assistant',array('_id'=>$id),$updateDate);
            if($updateRe){
                display(array());
            }
        }else{
            display(array(),1,'核对手机号哦');
        }
    }

    //患者列表
    public function all()
    {
        $date = $this->Common_model->getInfoAll('doctor_assistant',array(),array('_id'=>-1));
        if(empty($date)){
            display(array(),0,'暂无数据');
        }else{
            display($date);
        }
    }
}
