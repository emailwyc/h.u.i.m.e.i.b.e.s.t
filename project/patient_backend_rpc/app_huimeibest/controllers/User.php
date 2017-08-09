<?php
/**
 * Created by PhpStorm.
 * User: sa
 * Date: 8/1/16
 * Time: 3:08 PM
 */
class User extends CI_Controller
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
        parent::__construct();
    }
    //收藏文章
    public function collectArticle()
    {
        //明确收藏文章量是否需要：不需要；  加入用户表中 记录收藏时间
    }

    //收藏文章列表
    public function getCollectList()
    {

    }

    //APP 启动时间更新 需要辨别登录用户和非登录用户
    //表名：pat_ran_record；数据结构设计：_id,device_id（未登录为设备id）,active_time,is_login(是否)，用户id
    public function runingLog()
    {
        //验证用户登陆状态
        $sInfo = getClientHeaders();
        $this->load->model('User_model');
        //未登录过
        if(empty($sInfo['token'])){
            if(empty($sInfo['id'])){display(false,3,'设备id不能为空');}
            $this->User_model->checkRunRecord($sInfo['id'],$sInfo['agent']);
        }
        //db查找Session
        $patInfo = $this->Common_model->getInfo('pat_user_session',array('session_token'=>$sInfo['token']));
        if(empty($patInfo)){
            $this->User_model->checkRunRecord($sInfo['id'],$sInfo['agent']);
        }else{//登陆过用户
            $this->User_model->checkRunRecord($sInfo['id'],$sInfo['agent'],(string)$patInfo['_id']);
        }
		display();
}
